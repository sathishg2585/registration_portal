<?php

namespace Drupal\webform_node_element\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * Event dispatched prior to each webform_node_element being rendered.
 *
 * This gives other modules a chance to dynamically set the nid and display
 * mode of the node being rendered.
 */
class WebformNodeElementPreRender extends Event {
  const PRERENDER = 'webform_node_element.pre_render';

  protected $elementID = NULL;
  protected $nid = NULL;
  protected $displayMode = NULL;

  /**
   * Constructor.
   */
  public function __construct($element_id, $nid, $display_mode) {
    $this->setNid($nid);
    $this->elementID = $element_id;
    $this->setDisplayMode($display_mode);
  }

  /**
   * Set the nid of the node to display.
   *
   * @param int $nid
   *   The nid of the node to display.
   */
  public function setNid($nid) {
    $this->nid = $nid;
  }

  /**
   * Get the nid of the node to display.
   *
   * @return int
   *   The nid of the node to display.
   */
  public function getNid() {
    return $this->nid;
  }

  /**
   * Set the display mode to use to render the node.
   *
   * @param string $display_mode
   *   The machine name of the display mode to use.
   */
  public function setDisplayMode($display_mode) {
    $this->displayMode = $display_mode;
  }

  /**
   * Get the display mode to use to render the node.
   *
   * @return string
   *   The machine name of the display mode to use.
   */
  public function getDisplayMode() {
    return $this->displayMode;
  }

  /**
   * Get the element id that is being rendered.
   *
   * @return string
   *   The id of the element being rendered.
   */
  public function getElementId() {
    return $this->elementId;
  }

}
