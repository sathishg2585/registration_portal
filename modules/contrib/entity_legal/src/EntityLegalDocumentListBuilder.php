<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentListBuilder.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Link;

/**
 * Defines a class to build a listing of entity legal document entities.
 *
 * @see \Drupal\entity_legal\Entity\EntityLegalDocument
 */
class EntityLegalDocumentListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Label');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $label = Link::createFromRoute($entity->label(), 'entity.entity_legal_document.canonical', [
      'entity_legal_document' => $entity->id(),
    ])->toString();
    $row['label'] = $this->t('@label <small>(Machine name: @id)</small>', [
      '@label' => $label,
      '@id'    => $entity->id(),
    ]);
    return $row + parent::buildRow($entity);
  }

}
