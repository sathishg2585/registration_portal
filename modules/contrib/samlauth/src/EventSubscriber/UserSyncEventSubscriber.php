<?php

namespace Drupal\samlauth\EventSubscriber;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Egulias\EmailValidator\EmailValidator;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber that synchronizes user properties on a user_sync event.
 *
 * This is basic module functionality, partially driven by config options. It's
 * split out into an event subscriber so that the logic is easier to tweak for
 * individual sites. (Set message or not? Completely break off login if an
 * account with the same name is found, or continue with a non-renamed account?
 * etc.)
 */
class UserSyncEventSubscriber implements EventSubscriberInterface {

  /**
   * The EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The typed data manager.
   *
   * @var \Drupal\Core\TypedData\TypedDataManagerInterface
   */
  protected $typedDataManager;

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * A logger instance.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * A configuration object containing samlauth settings.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Construct a new SamlauthUserSyncSubscriber.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The EntityTypeManager service.
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typed_data_manager
   *   The typed data manager.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, TypedDataManagerInterface $typed_data_manager, EmailValidator $email_validator, LoggerInterface $logger) {
    $this->entityTypeManager = $entity_type_manager;
    $this->emailValidator = $email_validator;
    $this->logger = $logger;
    $this->typedDataManager = $typed_data_manager;
    $this->config = $config_factory->get('samlauth.authentication');
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[SamlauthEvents::USER_SYNC][] = ['onUserSync'];
    return $events;
  }

  /**
   * Performs actions to synchronize users with Factory data on login.
   *
   * @param \Drupal\samlauth\Event\SamlauthUserSyncEvent $event
   *   The event.
   */
  public function onUserSync(SamlauthUserSyncEvent $event) {
    // If the account is new, we are in the middle of a user save operation;
    // the current user name is 'samlauth_AUTHNAME' (as set by externalauth) and
    // e-mail is not set yet.
    $account = $event->getAccount();
    $fatal_errors = [];

    // Synchronize username.
    if ($account->isNew() || $this->config->get('sync_name')) {
      // Get value from the SAML attribute whose name is configured in the
      // samlauth module.
      $name = $this->getAttributeByConfig('user_name_attribute', $event);
      if ($name && $name != $account->getAccountName()) {
        // Validate the username. This shouldn't be necessary to mitigate
        // attacks; assuming our SAML setup is correct, noone can insert fake
        // data here. It protects against SAML attribute misconfigurations.
        // Invalid names will cancel the login / account creation. The code is
        // copied from user_validate_name().
        $definition = BaseFieldDefinition::create('string')->addConstraint('UserName', []);
        $data = \Drupal::typedDataManager()->create($definition);
        $data->setValue($name);
        $violations = $data->validate();
        if ($violations) {
          foreach ($violations as $violation) {
            $fatal_errors[] = $violation->getMessage();
          }
        }

        // Check if the username is not already taken by someone else. For new
        // accounts this can happen if the 'map existing users' setting is off.
        if (!$fatal_errors) {
          $account_search = $this->entityTypeManager->getStorage('user')->loadByProperties(array('name' => $name));
          $existing_account = reset($account_search);
          if (!$existing_account || $account->id() == $existing_account->id()) {
            $account->setUsername($name);
            $event->markAccountChanged();
          }
          else {
            $error = 'An account with the username @username already exists.';
            if ($account->isNew()) {
              $fatal_errors[] = t($error, ['@username' => $name]);
            }
            else {
              // We continue and keep the old name. A DSM should be OK here
              // since login only happens interactively. (And we're ignoring
              // the law of dependency injection for this.)
              $error = "Error updating user name from SAML attribute: $error";
              $this->logger->error($error, ['@username' => $name]);
              drupal_set_message(t($error, ['@username' => $name]), 'error');
            }
          }
        }
      }
    }

    // Synchronize e-mail.
    if ($account->isNew() || $this->config->get('sync_mail')) {
      $mail = $this->getAttributeByConfig('user_mail_attribute', $event);
      if ($mail) {
        if ($mail != $account->getEmail()) {
          // Invalid e-mail cancels the login / account creation just like name.
          if ($this->emailValidator->isValid($mail)) {

            $account->setEmail($mail);
            if ($account->isNew()) {
              // externalauth sets init to a non e-mail value so we will fix it.
              $account->set('init', $mail);
            }
            $event->markAccountChanged();
          }
          else {
            $fatal_errors[] = t('Invalid e-mail address @mail', ['@mail' => $mail]);
          }
        }
      }
      elseif ($account->isNew()) {
        // We won't allow new accounts with empty e-mail.
        $fatal_errors[] = t('Email address is not provided in SAML attribute.');
      }
    }

    if ($fatal_errors) {
      // Cancel the whole login process and/or account creation.
      throw new \RuntimeException('Error(s) encountered during SAML attribute synchronization: ' . join(' // ', $fatal_errors));
    }
  }

  /**
   * Returns value from a SAML attribute whose name is configured in our module.
   *
   * This is suitable for single-value attributes. (Most values are.)
   *
   * @param string $config_key
   *   A key in the module's configuration, containing the name of a SAML
   *   attribute.
   * @param \Drupal\samlauth\Event\SamlauthUserSyncEvent $event
   *   The event, which holds the attributes from the SAML response.
   *
   * @return mixed|null
   *   The SAML attribute value; NULL if the attribute value was not found.
   */
  public function getAttributeByConfig($config_key, SamlauthUserSyncEvent $event) {
    $attributes = $event->getAttributes();
    $attribute_name = $this->config->get($config_key);
    return $attribute_name && !empty($attributes[$attribute_name][0]) ? $attributes[$attribute_name][0] : NULL;
  }

}
