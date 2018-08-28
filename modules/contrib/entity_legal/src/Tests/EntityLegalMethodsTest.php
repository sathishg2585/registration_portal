<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Tests\EntityLegalMethodsTest.
 */

namespace Drupal\entity_legal\Tests;

use Drupal\Core\Url;

/**
 * Tests methods of encouraging users to accept legal documents.
 *
 * @group Entity Legal
 */
class EntityLegalMethodsTest extends EntityLegalTestBase {

  /**
   * Drupal message method test.
   */
  public function testMessageMethod() {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'message',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $acceptance_message = format_string('Please accept the @title', [
      '@title' => $document->getPublishedVersion()->label(),
    ]);
    /** @var \Drupal\Core\Url $document_url */
    $document_url = $document->toUrl();
    $document_path = $document_url->toString();

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    $this->assertText($acceptance_message, 'Document message found');
    $this->assertLinkByHref($document_path, 0, 'Link to document found');

    $this->clickLink($document->getPublishedVersion()->label());
    $this->assertFieldByName('agree', NULL, 'I agree checkbox found');

    $this->drupalPostForm($document_path, ['agree' => TRUE], 'Submit');

    // @TODO - Assert checkbox is disabled and acceptance date displayed.
    // $this->assertNoLinkByHref($document_path, 0, 'Link to document not found');
    $this->assertNoText($acceptance_message, 'Document message not found');

    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('');

    $acceptance_message_2 = format_string('Please accept the @title', [
      '@title' => $document->getPublishedVersion()->label(),
    ]);
    $this->assertText($acceptance_message_2, 'Document message found');
    $this->assertLinkByHref($document_path, 0, 'Link to document found');
  }

  /**
   * JQuery UI dialog method test.
   */
  public function testPopupMethod() {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'popup',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    // Check for the presence of the legal document in the js settings array.
    $js_settings = $this->getDrupalSettings();
    $this->assertTrue(isset($js_settings['entityLegalPopup']), 'Popup javascript settings found');
    $this->assertEqual($document->getPublishedVersion()
      ->label(), $js_settings['entityLegalPopup'][0]['popupTitle'], 'Popup title is correct');

    // Visit the document to agree as SimpleTest cannot properly submit using
    // the unprocessed markup from within the JS array.
    /** @var \Drupal\Core\Url $document_url */
    $document_url = $document->toUrl();
    $document_path = $document_url->toString();
    $this->drupalPostForm($document_path, ['agree' => TRUE], 'Submit');

    // Ensure the popup is no longer present.
    $js_settings = $this->getDrupalSettings();
    $this->assertFalse(isset($js_settings['entityLegalPopup']), 'Popup javascript settings not found');

    // Create a new version.
    $this->createDocumentVersion($document, TRUE);

    // Visit the home page and ensure that the user must re-accept.
    $this->drupalGet('');
    $js_settings = $this->getDrupalSettings();
    $this->assertTrue(isset($js_settings['entityLegalPopup']), 'Popup javascript settings found');
    $this->assertEqual($document->getPublishedVersion()
      ->label(), $js_settings['entityLegalPopup'][0]['popupTitle'], 'Popup title is correct');
  }

  /**
   * User signup form with link method test.
   */
  public function testSignupFormLinkMethod() {
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users' => [
        'require_method' => 'form_link',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('user/register');
    $this->assertFieldByName('legal_' . $document->id(), NULL, 'Agree checkbox found');

    /** @var \Drupal\Core\Url $document_url */
    $document_url = $document->toUrl();
    $document_path = $document_url->toString();

    $this->assertLinkByHref($document_path, 0, 'Link to document found');

    // Ensure the field extra field is available for re-ordering.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/accounts/form-display');
    $this->assertText('legal_' . $document->id());
  }

  /**
   * User signup form with inline method test.
   */
  public function testProfileFormInlineMethod() {
    $document = $this->createDocument(TRUE, TRUE, [
      'new_users' => [
        'require_method' => 'form_inline',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $this->drupalGet('user/register');
    $this->assertFieldByName('legal_' . $document->id(), NULL, 'Agree checkbox found');

    $this->assertRaw('<div class="clearfix text-formatted field field--name-entity-legal-document-text', 'Document markup found on register page');

    // Ensure the field extra field is available for re-ordering.
    $this->drupalLogin($this->adminUser);
    $this->drupalGet('admin/config/people/accounts/form-display');
    $this->assertText('legal_' . $document->id());
  }

  /**
   * Redirection method test.
   */
  public function testRedirectMethod() {
    $document = $this->createDocument(TRUE, TRUE, [
      'existing_users' => [
        'require_method' => 'redirect',
      ],
    ]);
    $this->createDocumentVersion($document, TRUE);

    $account = $this->createUserWithAcceptancePermissions($document);
    $this->drupalLogin($account);

    /** @var \Drupal\Core\Url $document_url */
    $document_url = $document->toUrl();
    $document_url->setAbsolute();
    $document_path = $document_url->toString();

    /** @var \Drupal\user\UserInterface $user */
    $user = \Drupal::entityTypeManager()->getStorage('user')->load($account->id());

    $this->assertUrl($document_path, [
      'query' => [
        'destination' => $user->toUrl()->toString(),
      ],
    ], 'User was redirected to legal document after login');

    $this->drupalGet('');

    $this->assertUrl($document_path, [
      'query' => [
        'destination' => $user->toUrl()->toString(),
      ],
    ], 'User was forcefully redirected to legal document after navigation');

    $this->drupalPostForm(NULL, ['agree' => TRUE], 'Submit');

    $this->drupalGet('');

    $this->assertUrl($user->toUrl()->setAbsolute()->toString(), [], 'User is free to navigate the site after acceptance');
  }

}
