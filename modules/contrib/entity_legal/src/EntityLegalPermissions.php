<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalPermissions.
 */

namespace Drupal\entity_legal;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_legal\Entity\EntityLegalDocument;

/**
 * Provides dynamic permissions for nodes of different types.
 */
class EntityLegalPermissions {

  use StringTranslationTrait;

  /**
   * Returns an array of entity legal document permissions.
   *
   * @return array
   *   The entity legal document permissions.
   *
   * @see \Drupal\user\PermissionHandlerInterface::getPermissions()
   */
  public function permissions() {
    $perms = [];

    /** @var \Drupal\entity_legal\Entity\EntityLegalDocument $document */
    foreach (EntityLegalDocument::loadMultiple() as $document) {
      $perms[$document->getPermissionView()] = [
        'title'       => $this->t('View "@name"', array(
          '@name' => $document->id(),
        )),
        'description' => $this->t('Allow users to view the legal document.'),
      ];

      $perms[$document->getPermissionExistingUser()] = [
        'title'       => $this->t('Re-accept "@name"', array(
          '@name' => $document->id(),
        )),
        'description' => $this->t('Existing user roles that must re-accept the legal document.'),
      ];
    }

    return $perms;
  }

}
