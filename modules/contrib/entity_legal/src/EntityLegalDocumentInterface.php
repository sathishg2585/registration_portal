<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalDocumentInterface.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Interface EntityLegalDocumentInterface.
 *
 * @package Drupal\entity_legal
 */
interface EntityLegalDocumentInterface extends ConfigEntityInterface {

  /**
   * Get the acceptance delivery method for a given user type.
   *
   * @param bool $new_user
   *   Get the method for new signups or existing accounts.
   *
   * @return string
   *   The acceptance delivery method.
   */
  public function getAcceptanceDeliveryMethod($new_user = FALSE);

  /**
   * Get an acceptance form for this legal document.
   *
   * @return array
   *   The drupal acceptance form.
   */
  public function getAcceptanceForm();

  /**
   * Get the label to be shown on the acceptance checkbox.
   *
   * @return string
   *   The label to be shown on the acceptance checkbox.
   */
  public function getAcceptanceLabel();

  /**
   * Get the acceptances for this entity legal document revision.
   *
   * @param AccountInterface|NULL $account
   *   The Drupal user account to check for, or get all acceptances if FALSE.
   * @param bool $published
   *   Get acceptances only for the currently published version.
   *
   * @return array
   *   The acceptance entities keyed by acceptance id.
   */
  public function getAcceptances(AccountInterface $account = NULL, $published = TRUE);

  /**
   * Get all versions of this legal document entity.
   *
   * @return array
   *   All versions of this legal document entity.
   */
  public function getAllVersions();

  /**
   * Get the permission name for any user viewing this agreement.
   *
   * @return string
   *   The user permission, used with user_access.
   */
  public function getPermissionView();

  /**
   * Get the permission name for new users accepting this document.
   *
   * @return string
   *   The user permission, used with user_access.
   */
  public function getPermissionExistingUser();

  /**
   * Get the current published version of this document.
   *
   * @return bool|EntityLegalDocumentVersionInterface
   *   The current legal document version or FALSE if none found.
   */
  public function getPublishedVersion();

  /**
   * Set the published document version.
   *
   * @param EntityLegalDocumentVersionInterface $version_entity
   *   The legal document version to set as the published version.
   *
   * @return bool
   *   Whether or not the published version was set successfully.
   */
  public function setPublishedVersion(EntityLegalDocumentVersionInterface $version_entity);

  /**
   * Check if the given user has agreed to the current version of this document.
   *
   * @param AccountInterface|NULL $account
   *   The Drupal user account to check. Default logged in user if not provided.
   *
   * @return bool
   *   Whether or not the user has agreed to the current version.
   */
  public function userHasAgreed(AccountInterface $account = NULL);

  /**
   * Checks to see if a given user can agree to this document.
   *
   * @param bool $new_user
   *   Whether or not the user to check is a new user signup or not.
   * @param AccountInterface|NULL $account
   *   The user account to check the access permissions of. Defaults to the
   *   current user if none is provided.
   *
   * @return bool
   *   Can a user agree to this document.
   */
  public function userMustAgree($new_user = FALSE, AccountInterface $account = NULL);

}
