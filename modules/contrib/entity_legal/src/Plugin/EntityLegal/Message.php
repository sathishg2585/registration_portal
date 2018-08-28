<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Plugin\EntityLegal\Message.
 */

namespace Drupal\entity_legal\Plugin\EntityLegal;

use Drupal\entity_legal\EntityLegalPluginBase;
use Drupal\Core\Link;

/**
 * Method class for alerting existing users via Drupal set message.
 *
 * @EntityLegal(
 *   id = "message",
 *   label = @Translation("Drupal warning message, prompting the user, until accepted"),
 *   type = "existing_users",
 * )
 */
class Message extends EntityLegalPluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute(&$context = []) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach ($this->documents as $document) {
      $message = t('Please accept the @document_name.', [
        '@document_name' => Link::createFromRoute($document->getPublishedVersion()->label(), 'entity.entity_legal_document.canonical', [
          'entity_legal_document' => $document->id(),
        ])->toString(),
      ]);

      drupal_set_message($message, 'warning', FALSE);
    }
  }

}
