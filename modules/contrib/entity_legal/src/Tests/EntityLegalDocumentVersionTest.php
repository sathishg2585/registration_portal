<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Tests\EntityLegalDocumentVersionTest.
 */

namespace Drupal\entity_legal\Tests;

/**
 * Tests admin functionality for the legal document version entity.
 *
 * @group Entity Legal
 */
class EntityLegalDocumentVersionTest extends EntityLegalTestBase {

  /**
   * Test the overview page contains a list of entities.
   */
  public function testAdminOverviewUi() {
    // Create a document.
    $document = $this->createDocument();

    // Create 3 documents versions.
    $versions = [];
    for ($i = 0; $i < 3; $i++) {
      $version = $this->createDocumentVersion($document);
      $versions[] = $version;
    }
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id());

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $version */
    foreach ($versions as $version) {
      $this->assertRaw($version->label(), 'Legal document version found on overview page');
      $this->assertLinkByHref('/admin/structure/legal/manage/' . $document->id() . '/manage/' . $version->id(), 0, 'Edit link for legal document version appears on overview');
    }
  }

  /**
   * Test the functionality of the create form.
   */
  public function testCreateForm() {
    $document = $this->createDocument();

    $test_label = $this->randomMachineName();
    $document_text = $this->randomMachineName();
    $acceptance_label = $this->randomMachineName();

    $this->drupalLogin($this->adminUser);
    $this->drupalPostForm('admin/structure/legal/manage/' . $document->id() . '/add', [
      'label'                                => $test_label,
      'entity_legal_document_text[0][value]' => $document_text,
      'acceptance_label'                     => $acceptance_label,
    ], 'Save');

    // Load a reset version of the entity.
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    $document = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_ENTITY_NAME, $document->id());
    $versions = $document->getAllVersions();
    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $created_version */
    $created_version = reset($versions);

    $this->assertTrue(!empty($created_version), 'Document version was successfully created');

    $this->drupalGet('admin/structure/legal/manage/' . $document->id());
    $this->assertText($test_label, 'Document version found on document page');

    if ($created_version) {
      $this->assertEqual($test_label, $created_version->label(), 'Label was saved correctly');
      $this->assertEqual($acceptance_label, $created_version->get('acceptance_label')->value, 'Acceptance label saved correctly');
      $this->assertEqual($document_text, $created_version->get('entity_legal_document_text')[0]->value, 'Document text is correct');
      $this->assertEqual($document->id(), $created_version->bundle(), 'Corresponding document is set correctly');
      $this->assertEqual($document->getPublishedVersion()->id(), $created_version->id(), 'Published version set on document');
    }
  }

  /**
   * Test the functionality of the edit form.
   */
  public function testEditForm() {
    $document = $this->createDocument();
    $version = $this->createDocumentVersion($document);

    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/structure/legal/manage/' . $document->id() . '/manage/' . $version->id());

    // Test field default values.
    $this->assertFieldByName('label', $version->label(), 'Label is set correctly for version');
    $this->assertFieldByName('entity_legal_document_text[0][value]', $version->get('entity_legal_document_text')[0]->value, 'Document text is set correctly for version');
    $this->assertFieldByName('acceptance_label', $version->get('acceptance_label')->value, 'Acceptance label is set correctly for version');

    // Test that changing values saves correctly.
    $new_label = $this->randomMachineName();
    $new_text = $this->randomMachineName();
    $new_acceptance_label = $this->randomMachineName();

    $this->drupalPostForm('admin/structure/legal/manage/' . $document->id() . '/manage/' . $version->id(), [
      'label'                                => $new_label,
      'entity_legal_document_text[0][value]' => $new_text,
      'acceptance_label'                     => $new_acceptance_label,
    ], 'Save');

    /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $version */
    $version = $this->getUncachedEntity(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, $version->id());
    $this->assertEqual($new_label, $version->label(), 'Label was saved correctly');
    $this->assertEqual($new_text, $version->get('entity_legal_document_text')[0]->value, 'Document tex was saved correctly');
    $this->assertEqual($new_acceptance_label, $version->get('acceptance_label')->value, 'Acceptance label was saved correctly');
  }

}
