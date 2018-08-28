<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Tests\EntityLegalDocumentTest.
 */

namespace Drupal\entity_legal\Tests;

/**
 * Tests admin functionality for the legal document entity.
 *
 * @group Entity Legal
 */
class EntityLegalDocumentTest extends EntityLegalTestBase {

  /**
   * Test the overview page contains a list of entities.
   */
  public function testAdminOverviewUi() {
    // Create 3 legal documents.
    $documents = [];
    for ($i = 0; $i < 3; $i++) {
      $documents[] = $this->createDocument();
    }
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal');

    /** @var \Drupal\entity_legal\Entity\EntityLegalDocument $document */
    foreach ($documents as $document) {
      $this->assertRaw($document->label(), 'Legal document found on overview page');
      $this->assertLinkByHref('/admin/structure/legal/manage/' . $document->id(), 0, 'Edit link for legal document appears on overview');
    }

    $this->assertLinkByHref('/admin/structure/legal/add', 0, 'Add document link found');
  }

  /**
   * Test the functionality of the create form.
   */
  public function testCreateForm() {
    $test_label = $this->randomMachineName();
    $test_id = $this->randomMachineName();

    $this->drupalLogin($this->adminUser);
    $this->drupalPostForm('admin/structure/legal/add', [
      'label'                                    => $test_label,
      'id'                                       => $test_id,
      'settings[new_users][require]'             => 1,
      'settings[new_users][require_method]'      => 'form_inline',
      'settings[existing_users][require]'        => 1,
      'settings[existing_users][require_method]' => 'redirect',
    ], 'Save');

    // Load a reset version of the entity.
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $created_document */
    $created_document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $test_id);

    $this->assertTrue(!empty($created_document), 'Document was successfully created');

    if ($created_document) {
      $this->assertEqual($test_label, $created_document->label(), 'Label was saved correctly');
      $this->assertEqual($test_id, $created_document->id(), 'ID was saved correctly');
      $this->assertEqual(1, $created_document->get('require_signup'), 'Signup requirement was saved correctly');
      $this->assertEqual(1, $created_document->get('require_existing'), 'Existing user requirement was saved correctly');
      $this->assertEqual('form_inline', $created_document->get('settings')['new_users']['require_method'], 'Existing user requirement was saved correctly');
      $this->assertEqual('redirect', $created_document->get('settings')['existing_users']['require_method'], 'Existing user requirement was saved correctly');
    }
  }

  /**
   * Test the functionality of the edit form.
   */
  public function testEditForm() {
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users'      => [
        'require_method' => 'form_inline',
      ],
      'existing_users' => [
        'require_method' => 'redirect',
      ],
    ]);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id());

    // Test field default values.
    $this->assertFieldByName('label', $document->label(), 'Label is set correctly for document');
    $this->assertFieldByName('settings[new_users][require]', 1, 'Require new users set correctly');
    $this->assertFieldByName('settings[new_users][require_method]', 'form_inline', 'Require existing users set correctly');
    $this->assertFieldByName('settings[existing_users][require]', 1, 'Require existing users set correctly');
    $this->assertFieldByName('settings[existing_users][require_method]', 'redirect', 'Require existing users set correctly');

    // Test that changing values saves correctly.
    $new_label = $this->randomMachineName();
    $this->drupalPostForm('admin/structure/legal/manage/' . $document->id(), [
      'label'                                    => $new_label,
      'settings[new_users][require]'             => FALSE,
      'settings[new_users][require_method]'      => 'form_link',
      'settings[existing_users][require]'        => FALSE,
      'settings[existing_users][require_method]' => 'popup',
    ], 'Save');

    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    $document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $document->id());

    $this->assertEqual($new_label, $document->label(), 'Label was saved correctly');
    $this->assertEqual(0, $document->get('require_signup'), 'Signup requirement was saved correctly');
    $this->assertEqual(0, $document->get('require_existing'), 'Existing user requirement was saved correctly');
    $this->assertEqual('form_link', $document->get('settings')['new_users']['require_method'], 'Form link method was saved correctly');
    $this->assertEqual('popup', $document->get('settings')['existing_users']['require_method'], 'Popup require method was saved correctly');
  }

  /**
   * Test the functionality of the delete form.
   */
  public function testDeleteForm() {
    $document = $this->createDocument();

    $document_name = $document->id();

    // Log in and check for existence of the created document.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal');
    $this->assertRaw($document_name, 'Document found in overview list');

    // Delete the document.
    $this->drupalPostForm('admin/structure/legal/manage/' . $document_name . '/delete', [], 'Delete');

    // Ensure document no longer exists on the overview page.
    $this->assertUrl('admin/structure/legal', [], 'Returned to overview page after deletion');
    $this->assertNoText($document_name, 'Document not found in overview list');
  }

}
