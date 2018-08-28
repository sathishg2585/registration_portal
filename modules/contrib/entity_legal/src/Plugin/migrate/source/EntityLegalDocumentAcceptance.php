<?php

/**
 * @file
 * Contains Drupal\entity_legal\Plugin\migrate\source\EntityLegalDocumentAcceptance.
 */

namespace Drupal\entity_legal\Plugin\migrate\source;

use Drupal\migrate_drupal\Plugin\migrate\source\d7\FieldableEntity;

/**
 * Drupal entity legal document acceptance migration source plugin.
 *
 * @MigrateSource(
 *   id = "entity_legal_document_acceptance",
 *   source_provider = "entity_legal"
 * )
 */
class EntityLegalDocumentAcceptance extends FieldableEntity {

  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('entity_legal_document_acceptance', 'a')->fields('a');
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'aid'                   => $this->t('The entity ID of this agreement.'),
      'document_version_name' => $this->t('The name of the document version this acceptance is bound to.'),
      'uid'                   => $this->t('The author of the acceptance.'),
      'acceptance_date'       => $this->t('The date the document was accepted.'),
      'data'                  => $this->t('A dump of user data to help verify acceptances.'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['aid']['type'] = 'integer';
    return $ids;
  }

}
