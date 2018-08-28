<?php

/**
 * @file
 * Contains Drupal\entity_legal\Plugin\migrate\source\EntityLegalDocument.
 */

namespace Drupal\entity_legal\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\DrupalSqlBase;

/**
 * Drupal entity legal document migration source plugin.
 *
 * @MigrateSource(
 *   id = "entity_legal_document",
 *   source_provider = "entity_legal"
 * )
 */
class EntityLegalDocument extends DrupalSqlBase {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('entity_legal_document', 'd')->fields('d');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'name'              => $this->t('The legal document ID.'),
      'label'             => $this->t('The human-readable label of the legal document.'),
      'published_version' => $this->t('The current published version of this legal document.'),
      'require_signup'    => $this->t('Require new users to accept this document on signup.'),
      'require_existing'  => $this->t('Require existing users to accept this document.'),
      'settings'          => $this->t('An array of additional data related to the legal document.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
