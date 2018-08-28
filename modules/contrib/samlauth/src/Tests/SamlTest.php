<?php

namespace Drupal\samlauth\Tests;

use Drupal\Core\Url;
use Drupal\simpletest\WebTestBase;
use Drupal\Component\Serialization\Yaml;

/**
 * Tests SAML authentication.
 *
 * @group samlauth
 */
class SamlTest extends WebTestBase {

  // We don't need a strict schema. There *isn't* one.
  protected $strictConfigSchema = FALSE;
  public static $modules = array('samlauth');

  public static function getInfo() {
    return array(
      'name' => 'Tests SAML authentication',
      'description' => 'Functional tests for the samlauth module functionality.',
      'group' => 'samlauth',
    );
  }

  public function setUp() {
    parent::setUp();

    // Import testshib config.
    $config = drupal_get_path('module', 'samlauth') . '/test_resources/samlauth.authentication.yml';;
    $config = file_get_contents($config);
    $config = Yaml::decode($config);
    \Drupal::configFactory()->getEditable('samlauth.authentication')->setData($config)->save();
  }

  public function testAdminPage() {
    // Test that the administration page is present.
    // These aren't very good tests, but the form and config systems are already
    // thoroughly tested, so we're just checking the basics here.
    $web_user = $this->drupalCreateUser(['configure saml']);
    $this->drupalLogin($web_user);
    $this->drupalGet('admin/config/people/saml');
    $this->assertText('SAML Requirements', 'SAML Requirements fieldset present');
    $this->assertText('Service Provider Configuration', 'SP fieldset present');
    $this->assertText('Identity Provider Configuration', 'iDP fieldset present');
    $this->assertText('User Info and Syncing', 'User Info and Syncing fieldset present');
    $this->assertText('Security Options', 'Security options fieldset present');
  }

  public function testMetadata() {
    $web_user = $this->drupalCreateUser(['view sp metadata']);
    $this->drupalLogin($web_user);

    // Test that we get metadata.
    $this->drupalGet('saml/metadata');
    $this->assertResponse(200, 'SP metadata is accessible');
    $this->assertRaw('entityID="samlauth"', 'Entity ID found in the metadata');
  }

  /**
   * Test login without mapping or user creation.
   */
  public function testLoginNotAllowed() {
    // Ensure that this test is run as an anonymous user.
    if ($this->loggedInUser) {
      $this->drupalLogout();
    }

    // Since the SP is properly configured (done in setUp()), this should be a
    // redirect.
    $this->drupalGet('saml/login');
    $url = Url::fromUri('https://idp.testshib.org:443/idp/Authn/UserPassword');
    $this->assertUrl($url, [], 'Correct iDP page loaded');
    $this->assertResponse(200, 'iDP page loaded successfully');

    // Submit the login form with the testshib credentials.
    $this->drupalPost('https://idp.testshib.org/idp/Authn/UserPassword', '*', array(
      'j_username' => 'myself',
      'j_password' => 'myself'
    ));

    // When mapping and creation aren't enabled, users are taken to user/login.
    // @todo the return url tests aren't quite working yet. missing state on the iDP? might need to store JSESSIONID and _idp_authn_lc_key cookies?
//    $url = Url::fromRoute('user.page');
//    $this->assertUrl($url, [], 'User was redirected to user/login after iDP authentication.');
//    $this->assertText('No existing user account matches the SAML ID provided', 'Error message was displayed to the user.');
  }

  /**
   * Test that Drupal login is not allowed for SAML users when configured.
   *
   * This test relies on implicit behavior. The drupal_saml_login option is disabled by default.
   */
  public function testDrupalLoginNotAllowed() {
    // Create a user.
    $saml_user = $this->createUser();

    // Manually set the SAML ID (this would normally be done by mapping or creating saml users)
    \Drupal::service('user.data')->set('samlauth', $saml_user->id(), 'saml_id', '12345');

    $edit = array(
      'name' => $saml_user->getUsername(),
      'pass' => $saml_user->pass_raw,
    );
    $this->drupalPostForm('user/login', $edit, t('Log in'));

    $this->assert(!$this->drupalUserIsLoggedIn($saml_user), 'SAML user is not logged in.');
    $this->assertText('SAML users must sign in with SSO', 'Error is displayed to the user.');
  }
}
