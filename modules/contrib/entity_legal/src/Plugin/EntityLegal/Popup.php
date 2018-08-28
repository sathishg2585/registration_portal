<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Plugin\EntityLegal\Popup.
 */

namespace Drupal\entity_legal\Plugin\EntityLegal;

use Drupal\entity_legal\EntityLegalPluginBase;

/**
 * Method class for alerting existing users via a jQuery UI popup window.
 *
 * @EntityLegal(
 *   id = "popup",
 *   label = @Translation("Popup on all pages until accepted"),
 *   type = "existing_users",
 * )
 */
class Popup extends EntityLegalPluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute(&$context = []) {
    if (!empty($this->documents)) {
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = \Drupal::service('renderer');
      $view_builder = \Drupal::entityTypeManager()
        ->getViewBuilder(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME);

      /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
      foreach ($this->documents as $document) {
        $context['attachments']['#cache']['tags'][] = "entity_legal_document:{$document->id()}";

        $document_markup = $view_builder->view($document->getPublishedVersion());
        $context['attachments']['#attached']['library'][] = 'entity_legal/popup';
        $context['attachments']['#attached']['drupalSettings']['entityLegalPopup'] = [
          [
            'popupTitle'   => $document->getPublishedVersion()->label(),
            'popupContent' => $renderer->renderPlain($document_markup),
          ],
        ];
      }
    }
  }

}
