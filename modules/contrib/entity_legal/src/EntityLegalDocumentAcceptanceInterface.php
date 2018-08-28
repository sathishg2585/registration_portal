<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentAcceptanceInterface.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;

/**
 * Provides an interface defining a entity legal document acceptance entity.
 */
interface EntityLegalDocumentAcceptanceInterface extends ContentEntityInterface {

  /**
   * Get the document version this acceptance belongs to.
   *
   * @return EntityLegalDocumentVersionInterface
   *   The version of the document corresponding to this acceptance.
   */
  public function getDocumentVersion();

}
