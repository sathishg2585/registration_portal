<?php

namespace Drupal\webform_node_element_example\EventSubscriber;

use Drupal\webform_node_element\Event\WebformNodeElementPreRender;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class ExampleEventSubscriber.
 *
 * This class shows how to use events to modify the node and display mode that
 * the webform_node_element displays.
 *
 * @package Drupal\webform_node_element_example
 */
class ExampleEventSubscriber implements EventSubscriberInterface {

  /**
   * Set the events that we want to subscribe to.
   *
   * Tell Drupal that we're interested in subscribing to the
   * webform_node_element prerender event.
   *
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[WebformNodeElementPreRender::PRERENDER][] = ['onWebformNodeElementPreRender', 800];
    return $events;
  }

  /**
   * This is called whenever the webform_node_element prerender event occurs.
   *
   * For example purposes we tell the element to display node 1 using the
   * 'teaser' display mode.
   *
   * @param \WebformNodeElementPreRender $event
   *   The event that triggered this callback.
   */
  public function onWebformNodeElementPreRender(WebformNodeElementPreRender $event) {
    $event->setNid(1);
    $event->setDisplayMode('teaser');
  }

}
