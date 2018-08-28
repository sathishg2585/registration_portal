<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Plugin\EntityLegal\Redirect.
 */

namespace Drupal\entity_legal\Plugin\EntityLegal;

use Drupal\Core\Routing\TrustedRedirectResponse;
use Drupal\entity_legal\EntityLegalPluginBase;

/**
 * Method class for redirecting existing users to accept a legal document.
 *
 * @EntityLegal(
 *   id = "redirect",
 *   label = @Translation("Redirect every page load to legal document until accepted"),
 *   type = "existing_users",
 * )
 */
class Redirect extends EntityLegalPluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute(&$context = []) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach ($this->documents as $document) {
      /** @var \Drupal\Core\Url $entity_url */
      $entity_url = $document->toUrl();

      // Only redirect if the legal document isn't currently being viewed.
      $current_route = \Drupal::routeMatch();
      if ($current_route->getRouteName() == $entity_url->getRouteName()) {
        return FALSE;
      }

      drupal_set_message(t('You must accept this agreement before continuing.'), 'warning');

      $entity_url->setOption('query', \Drupal::service('redirect.destination')->getAsArray());
      $entity_url->setAbsolute(TRUE);

      /** @var \Symfony\Component\HttpKernel\Event\GetResponseEvent $event */
      $event = &$context['event'];
      $response = new TrustedRedirectResponse($entity_url->toString());
      $event->setResponse($response);
    }
  }

}
