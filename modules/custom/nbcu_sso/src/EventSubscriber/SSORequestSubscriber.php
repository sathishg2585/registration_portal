<?php
/**
 * @file
 * Contains \Drupal\nbcu_sso\EventSubscriber\Getorg.
 */

namespace Drupal\nbcu_sso\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Routing\RouteMatch;
use Drupal\samlauth\Event\SamlauthEvents;
use Drupal\samlauth\Event\SamlauthUserSyncEvent;
use Psr\Log\LoggerInterface;
/**
 * Event Subscriber MyEventSubscriber.
 */
class SSORequestSubscriber implements EventSubscriberInterface {

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
  
  /* public function __construct(ConfigFactoryInterface $config_factory, LoggerInterface $logger) {
    $this->logger = $logger;
    $this->config = $config_factory->get('samlauth.authentication');
  }*/
  /**
   * {@inheritdoc}
   */
   static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = array('checkForLogin', 300);
	$events[SamlauthEvents::USER_SYNC][] = array('samlCusomAttribute');
    return $events;
  }

  /**
   * This method is called whenever the KernelEvents::REQUEST event is
   * dispatched.
   *
   * @param GetResponseEvent $event
   */
  public function checkForLogin(GetResponseEvent $response) {

    $routeMatch = RouteMatch::createFromRequest($response->getRequest());
    $route_name = $routeMatch->getRouteName();
    $is_anonymous = \Drupal::currentUser()->isAnonymous();
    $current_path = \Drupal::service('path.current')->getPath();
    if ($is_anonymous && ($route_name == 'system.403' || $route_name == 'system.404') && $current_path != '/saml/acs' && $current_path != '/saml/error') {
      $login_uri = \Drupal::url('samlauth.saml_controller_login');
      $returnResponse = new RedirectResponse($login_uri, Response::HTTP_FOUND);
      $returnResponse->send();
    }
  }
  
  /**
   * Performs actions to synchronize users with Factory data on login.
   *
   * @param \Drupal\samlauth\Event\SamlauthUserSyncEvent $event
   *   The event.
   */
  public function samlCusomAttribute(SamlauthUserSyncEvent $event) {
    // If the account is new, we are in the middle of a user save operation;
    // the current user name is 'samlauth_AUTHNAME' (as set by externalauth) and
    // e-mail is not set yet.
    $account = $event->getAccount();
    
    if ($account->isNew()) {
      $account->addRole('sg_member');
      $first_name = $this->getAttributeByConfig('FirstName', $event);
      $last_name = $this->getAttributeByConfig('LastName', $event);
      $account->set('field_user_first_name', $first_name);
      $account->set('field_user_last_name', $last_name);	  
      $event->markAccountChanged();
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
  public function getAttributeByConfig($attribute_name, SamlauthUserSyncEvent $event) {
    $attributes = $event->getAttributes();
    //$attribute_name = $this->config->get($config_key);
    return $attribute_name && !empty($attributes[$attribute_name][0]) ? $attributes[$attribute_name][0] : NULL;
  }  
}