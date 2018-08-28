<?php

/**
 * @file
 * Common test class file.
 */

namespace Drupal\entity_legal\Tests;

use Drupal\Core\Session\AccountInterface;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;
use Drupal\simpletest\WebTestBase;

/**
 * Common Simpletest class for all legal tests.
 */
abstract class EntityLegalTestBase extends WebTestBase {

  /**
   * The administrative user to use for tests.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['block', 'entity_legal', 'field_ui', 'token'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'administer entity legal',
      'administer permissions',
      'administer user form display',
      'administer users',
    ]);

    // Ensure relevant blocks present if profile isn't 'standard'.
    if ($this->profile !== 'standard') {
      $this->drupalPlaceBlock('local_actions_block');
      $this->drupalPlaceBlock('page_title_block');
    }
  }

  /**
   * {@inheritdoc}
   *
   * Ensures generated names are lower case.
   */
  protected function randomMachineName($length = 8) {
    return strtolower(parent::randomMachineName($length));
  }

  /**
   * Get an entity bypassing static and db cache.
   *
   * @param string $entity_type
   *   The type of entity to get.
   * @param string $entity_id
   *   The ID or name to load the entity using.
   *
   * @return EntityLegalDocument
   *   The retrieved entity.
   */
  public function getUncachedEntity($entity_type, $entity_id) {
    $controller = \Drupal::entityTypeManager()->getStorage($entity_type);
    $controller->resetCache([$entity_id]);
    return $controller->load($entity_id);
  }

  /**
   * Create a random legal document entity.
   *
   * @param bool $require_signup
   *   Whether or not to require new users to agree.
   * @param bool $require_existing
   *   Whether or not to require existing users to agree.
   * @param array $settings
   *   Additional settings to pass through to the document.
   *
   * @return EntityLegalDocumentInterface
   *   The created legal document.
   */
  protected function createDocument($require_signup = FALSE, $require_existing = FALSE, $settings = []) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME)
      ->create([
        'id'               => $this->randomMachineName(32),
        'label'            => $this->randomMachineName(),
        'require_signup'   => (int) $require_signup,
        'require_existing' => (int) $require_existing,
        'settings'         => $settings,
      ]);
    $entity->save();

    // Reset permissions cache to make new document permissions available.
    $this->checkPermissions([
      $entity->getPermissionView(),
      $entity->getPermissionExistingUser(),
    ]);

    return $entity;
  }

  /**
   * Create a document version.
   *
   * @param EntityLegalDocumentInterface $document
   *   The document to add the version to.
   * @param bool $save_as_default
   *   Whether to save the version as the default for the document.
   *
   * @return EntityLegalDocumentVersionInterface
   *   The created legal document version.
   */
  protected function createDocumentVersion(EntityLegalDocumentInterface $document, $save_as_default = FALSE) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $entity */
    $entity = \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->create([
        'label'                      => $this->randomMachineName(),
        'name'                       => $this->randomMachineName(64),
        'document_name'              => $document->id(),
        'acceptance_label'           => 'I agree to the <a href="[entity_legal_document:url]">document</a>',
        'entity_legal_document_text' => [['value' => $this->randomMachineName()]],
      ]);
    $entity->save();

    if ($save_as_default) {
      $document->setPublishedVersion($entity);
      $document->save();
    }

    return $entity;
  }

  /**
   * Create an account that is able to view and re-accept a given document.
   *
   * @param EntityLegalDocumentInterface $document
   *   The legal document the user is able to view and accept.
   *
   * @return AccountInterface
   *   The user.
   */
  protected function createUserWithAcceptancePermissions(EntityLegalDocumentInterface $document) {
    $account = $this->drupalCreateUser([
      $document->getPermissionView(),
      $document->getPermissionExistingUser(),
    ]);

    return $account;
  }

}
