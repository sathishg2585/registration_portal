<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalPluginBase.
 */

namespace Drupal\entity_legal;

use Drupal\Component\Plugin\PluginBase;

/**
 * Class ResponsiveMenusPluginBase.
 *
 * @package Drupal\responsive_menus
 */
abstract class EntityLegalPluginBase extends PluginBase implements EntityLegalPluginInterface {

  /**
   * The legal documents that implement this plugin.
   *
   * @var array
   */
  protected $documents = [];

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->documents = $this->getDocumentsForMethod();
  }

  /**
   * Get all Entity Legal Documents for this plugin.
   *
   * @return array
   *   All published entity legal documents required.
   */
  public function getDocumentsForMethod() {
    // Entity Legal administrators should never be forced to accept documents.
    if (\Drupal::currentUser()->hasPermission('administer entity legal')) {
      return [];
    }

    // Ensure the correct user context for this plugin.
    if ($this->pluginDefinition['type'] == 'existing_users' && \Drupal::currentUser()->isAnonymous()) {
      return [];
    }

    // Get all active documents that must be agreed to.
    $properties = ['require_existing' => 1];
    if ($this->pluginDefinition['type'] == 'new_users') {
      $properties = ['require_signup' => 1];
    }
    $documents = \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)
      ->loadByProperties($properties);

    // Remove any documents from the array set that don't use the given
    // acceptance method.
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach ($documents as $name => $document) {
      $agreed = !$document->userMustAgree($this->pluginDefinition['type'] == 'new_users') || $document->userHasAgreed();
      $is_method = $document->getAcceptanceDeliveryMethod($this->pluginDefinition['type'] == 'new_users') == $this->pluginId;

      if ($agreed || !$is_method) {
        unset($documents[$name]);
      }
    }

    return $documents;
  }

}
