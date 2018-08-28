<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EventSubscriber\EntityLegalSubscriber.
 */

namespace Drupal\entity_legal\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class EntityLegalSubscriber.
 *
 * @package Drupal\entity_legal\EventSubscriber
 */
class EntityLegalSubscriber implements EventSubscriberInterface {

  /**
   * Request event callback.
   *
   * @param GetResponseEvent $event
   *   The request event.
   */
  public function checkRedirect(GetResponseEvent $event) {
    $context = ['event' => $event];

    // Execute Redirect method plugin.
    \Drupal::service('plugin.manager.entity_legal')
      ->createInstance('redirect')
      ->execute($context);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkRedirect'];

    return $events;
  }

}
