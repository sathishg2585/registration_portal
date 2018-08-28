<?php

namespace Drupal\Tests\webform_node_element\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\webform\Entity\Webform;

/**
 * Example of webform browser test.
 *
 * @group webform_node_element
 */
class WebformNodeElementFunctionalTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['node', 'filter', 'webform', 'webform_node_element'];

  /**
   * Test.
   */
  public function setup() {
    parent::setup();
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Basic Article']);
  }

  /**
   * Test that the webform node element is displayed on the form.
   */
  public function testGet() {

    $node_title = "Webform Node Element Test";

    $node = $this->drupalCreateNode([
      'type' => 'article',
      'title' => $node_title,
    ]);

    // Create webform.
    /** @var \Drupal\webform\WebformInterface $webform */
    $webform = Webform::create(['id' => 'webform_test']);

    $elements = [
      'webform_node_element_test' => [
      [
        '#type' => 'webform_node_element',
        '#webform_node_element_nid' => $node->id(),
      ],
      ],
    ];
    $webform->setElements($elements);
    $webform->save();

    $url = $webform->toUrl()->toString();
    $html = $this->drupalGet($url);
    $this->assertText($node_title);
  }

}
