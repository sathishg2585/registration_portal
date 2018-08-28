<?php

namespace Drupal\webform_node_element\Plugin\WebformElement;

use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Plugin\WebformElement\WebformMarkupBase;

/**
 * Provides a 'webform_node_element' element.
 *
 * @WebformElement(
 *   id = "webform_node_element",
 *   label = @Translation("Node"),
 *   description = @Translation("Provides an element that renders a node"),
 *   category = @Translation("Markup elements"),
 *   states_wrapper = TRUE,
 * )
 */
class WebformNodeElement extends WebformMarkupBase {

  /**
   * {@inheritdoc}
   */
  public function getDefaultProperties() {
    return parent::getDefaultProperties() + [
      'webform_node_element_nid' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $form['node_element'] = [
      '#title' => $this->t('Node Details'),
      '#type' => 'fieldset',
    ];
    $form['node_element']['webform_node_element_nid'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Node ID'),
      '#description' => $this->t('The ID of the node to render. The node will be displayed using the "webform_element" display mode. You can leave this empty and write an EventHandler to change the node or display mode dynamically.'),
    ];
    return $form;
  }

}
