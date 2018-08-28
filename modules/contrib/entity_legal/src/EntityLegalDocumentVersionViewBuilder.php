<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentVersionViewBuilder.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Class EntityLegalDocumentVersionViewBuilder.
 *
 * @package Drupal\entity_legal
 */
class EntityLegalDocumentVersionViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
    parent::buildComponents($build, $entities, $displays, $view_mode);

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $entity */
    foreach ($entities as $id => $entity) {
      // Get acceptance form or information for the current user.
      $document = $entity->getDocument();

      $account = \Drupal::currentUser();
      if ($document->userMustAgree() && $account->isAuthenticated()) {
        $build[$id]['acceptance'] = $document->getAcceptanceForm();
        $build[$id]['acceptance']['#weight'] = 99;
      }
    }
  }

}
