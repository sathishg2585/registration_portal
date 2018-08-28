<?php

/**
 * @file
 * Contains Drupal\entity_legal\Plugin\migrate\destination\EntityLegalDocumentVersion.
 */

namespace Drupal\entity_legal\Plugin\migrate\destination;

use Drupal\migrate\Plugin\migrate\destination\EntityContentBase;

/**
 * Drupal entity legal document version migration destination plugin.
 *
 * @MigrateDestination(
 *   id = "entity_legal_document_version",
 *   provider = "entity_legal"
 * )
 */
class EntityLegalDocumentVersion extends EntityContentBase {

  /**
   * {@inheritdoc}
   */
  protected static function getEntityTypeId($plugin_id) {
    return 'entity_legal_document_version';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['name']['type'] = 'string';
    return $ids;
  }

}
