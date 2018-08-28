<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Entity\EntityLegalDocument.
 */

namespace Drupal\entity_legal\Entity;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;
use Drupal\entity_legal\Form\EntityLegalDocumentAcceptanceForm;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the entity legal document entity.
 *
 * @ConfigEntityType(
 *   id = "entity_legal_document",
 *   label = @Translation("Legal document"),
 *   handlers = {
 *     "access" = "Drupal\entity_legal\EntityLegalDocumentAccessControlHandler",
 *     "list_builder" = "Drupal\entity_legal\EntityLegalDocumentListBuilder",
 *     "form" = {
 *       "add" = "Drupal\entity_legal\Form\EntityLegalDocumentForm",
 *       "edit" = "Drupal\entity_legal\Form\EntityLegalDocumentForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteForm"
 *     }
 *   },
 *   config_prefix = "document",
 *   admin_permission = "administer entity legal",
 *   bundle_of = "entity_legal_document_version",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "delete-form" = "/admin/structure/legal/manage/{entity_legal_document}/delete",
 *     "edit-form" = "/admin/structure/legal/manage/{entity_legal_document}",
 *     "collection" = "/admin/structure/legal",
 *     "canonical" = "/legal/document/{entity_legal_document}",
 *   }
 * )
 */
class EntityLegalDocument extends ConfigEntityBundleBase implements EntityLegalDocumentInterface {

  /**
   * The legal document ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable label of the legal document.
   *
   * @var string
   */
  protected $label;

  /**
   * The current published version of this legal document.
   *
   * @var string
   */
  protected $published_version;

  /**
   * Require new users to accept this document on signup.
   *
   * @var bool
   */
  protected $require_signup = FALSE;

  /**
   * Require existing users to accept this document.
   *
   * @var bool
   */
  protected $require_existing = FALSE;

  /**
   * Am array of additional data related to the legal document.
   *
   * @var array
   */
  protected $settings = [];

  /**
   * {@inheritdoc}
   */
  public function delete() {
    if (!$this->isNew()) {
      // Delete all associated versions.
      $versions = $this->getAllVersions();
      foreach ($versions as $version) {
        $version->delete();
      }
    }

    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceForm() {
    $form = new EntityLegalDocumentAcceptanceForm($this);

    return \Drupal::formBuilder()->getForm($form);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllVersions() {
    $query = \Drupal::entityQuery(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->condition('document_name', $this->id());
    $results = $query->execute();
    if (!empty($results)) {
      return \Drupal::entityTypeManager()
        ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
        ->loadMultiple($results);
    }
    else {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPublishedVersion() {
    $published_version = FALSE;

    \Drupal::moduleHandler()
      ->alter('entity_legal_published_version', $this->published_version, $this);

    if (!empty($this->published_version)) {
      $published_version = \Drupal::entityTypeManager()
        ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
        ->load($this->published_version);
    }

    return $published_version;
  }

  /**
   * {@inheritdoc}
   */
  public function setPublishedVersion(EntityLegalDocumentVersionInterface $version_entity) {
    // If the version entity is not of this bundle, fail.
    if ($version_entity->bundle() != $this->id()) {
      return FALSE;
    }

    $this->published_version = $version_entity->id();

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceLabel() {
    $label = '';
    $published_version = $this->getPublishedVersion();

    if ($published_version) {
      $label = $published_version->get('acceptance_label')->value;
    }

    $token_service = \Drupal::service('token');
    $label = $token_service->replace($label, [ENTITY_LEGAL_DOCUMENT_ENTITY_NAME => $this]);

    return Xss::filter($label);
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'canonical', array $options = []) {
    // Unless language was already provided, avoid setting an explicit language.
    $options += ['language' => NULL];
    return parent::toUrl($rel, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function url($rel = 'canonical', $options = array()) {
    // Do not remove this override: the default value of $rel is different.
    return parent::url($rel, $options);
  }

  /**
   * {@inheritdoc}
   */
  public function userMustAgree($new_user = FALSE, AccountInterface $account = NULL) {
    // User cannot agree unless there is a published version.
    if (!$this->getPublishedVersion()) {
      return FALSE;
    }

    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    if ($new_user) {
      return !empty($this->require_signup);
    }
    else {
      return !empty($this->require_existing) && $account->hasPermission($this->getPermissionExistingUser());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function userHasAgreed(AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    return count($this->getAcceptances($account)) > 0;
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptances(AccountInterface $account = NULL, $published = TRUE) {
    $acceptances = array();
    $versions = array();

    if ($published) {
      $versions[] = $this->getPublishedVersion();
    }
    else {
      $versions = $this->getAllVersions();
    }

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $version */
    foreach ($versions as $version) {
      $acceptances += $version->getAcceptances($account);
    }

    return $acceptances;
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionView() {
    return 'legal view ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionExistingUser() {
    return 'legal re-accept ' . $this->id();
  }

  /**
   * {@inheritdoc}
   */
  public function getAcceptanceDeliveryMethod($new_user = FALSE) {
    $setting_group = $new_user ? 'new_users' : 'existing_users';

    return isset($this->get('settings')[$setting_group]['require_method']) ? $this->get('settings')[$setting_group]['require_method'] : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function save() {
    $status = parent::save();

    if ($status == SAVED_NEW) {
      // Add or remove the body field, as needed.
      $field = FieldConfig::loadByName('entity_legal_document_version', $this->id(), 'entity_legal_document_text');
      if (empty($field)) {
        FieldConfig::create([
          'field_storage' => FieldStorageConfig::loadByName('entity_legal_document_version', 'entity_legal_document_text'),
          'bundle'        => $this->id(),
          'label'         => 'Document text',
          'settings'      => ['display_summary' => FALSE],
        ])->save();

        // Assign widget settings for the 'default' form mode.
        entity_get_form_display('entity_legal_document_version', $this->id(), 'default')
          ->setComponent('entity_legal_document_text', [
            'type' => 'text_textarea_with_summary',
          ])
          ->save();

        // Assign display settings for 'default' view mode.
        entity_get_display('entity_legal_document_version', $this->id(), 'default')
          ->setComponent('entity_legal_document_text', [
            'label' => 'hidden',
            'type'  => 'text_default',
          ])
          ->save();
      }
    }

    else {
      Cache::invalidateTags(["entity_legal_document:{$this->id()}"]);
    }

    return $status;
  }

}
