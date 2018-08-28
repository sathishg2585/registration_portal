<?php

namespace Drupal\webform_node_element\Element;

use Drupal\Core\Render\Element\RenderElement;
use Drupal\webform_node_element\Event\WebformNodeElementPreRender;

/**
 * Provides a render element to display a node.
 *
 * @FormElement("webform_node_element")
 */
class WebformNodeElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#pre_render' => [[$class, "preRenderWebformNodeElement"]],
      '#webform_node_element_nid' => NULL,
    ];
  }

  /**
   * Add the rendered node to the element.
   *
   * @param array $element
   *   An associative array containing the properties of the element.
   *
   * @return array
   *   The $element.
   */
  public static function preRenderWebformNodeElement(array $element) {
    $element['#markup'] = "";

    $nid = $element['#webform_node_element_nid'];
    $element_id = $element['#webform_id'];

    // Allow event subscribers to set the nid and display mode.
    $dispatcher = \Drupal::service('event_dispatcher');
    $event = new WebformNodeElementPreRender($element_id, $nid, 'webform_element');
    $dispatcher->dispatch(WebformNodeElementPreRender::PRERENDER, $event);

    $nid = $event->getNid();
    // This guarantees the nid is valid.
    $nid = reset(\Drupal::entityQuery('node')->condition('nid', $nid)->execute());

    $display_mode = $event->getDisplayMode();

    if ($nid && $display_mode) {
      $node = \Drupal::entityManager()->getStorage('node')->load($nid);
      if ($node->access('view')) {
        $view_builder = \Drupal::entityManager()->getViewBuilder('node');

        if ($node && $view_builder) {
          if ($render_array = $view_builder->view($node, $display_mode)) {
            $element['#markup'] = \Drupal::service('renderer')->renderRoot($render_array);
          }
        }
      }
    }
    else {
      \Drupal::logger('webform_node_element')->notice('webform_node_element @element_id not rendered because nid was set to "@nid" and display_mode was set to "@display_mode"',
        [
          '@element_id' => $element_id,
          '@nid' => $nid,
          '@display_mode' => $display_mode,
        ]);
    }

    return $element;
  }

}
