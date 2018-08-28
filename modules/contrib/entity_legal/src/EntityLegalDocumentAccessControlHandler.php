<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentAccessControlHandler.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the entity legal document entity type.
 *
 * @see \Drupal\entity_legal\Entity\EntityLegalDocument.
 */
class EntityLegalDocumentAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    // Allow all users who can administer the module to do anything.
    if ($account->hasPermission('administer entity legal')) {
      return AccessResult::allowed();
    }

    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $entity */
    if ($operation == 'view' && $account->hasPermission($entity->getPermissionView())) {
      return AccessResult::allowed();
    }

    return parent::checkAccess($entity, $operation, $account);
  }

}
