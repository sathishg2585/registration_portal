<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Plugin\EntityLegal\ProfileFormEmbedded.
 */

namespace Drupal\entity_legal\Plugin\EntityLegal;

/**
 * Method class for displaying a checkbox on the user register form.
 *
 * @EntityLegal(
 *   id = "form_inline",
 *   label = @Translation("Checkbox on signup form with embedded document"),
 *   type = "new_users",
 * )
 */
class ProfileFormEmbedded extends ProfileForm {

  /**
   * {@inheritdoc}
   */
  public function execute(&$context = []) {
    parent::execute($context);

    if (!empty($this->documents)) {
      /** @var \Drupal\Core\Render\RendererInterface $renderer */
      $renderer = \Drupal::service('renderer');
      $view_builder = \Drupal::entityTypeManager()
        ->getViewBuilder(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME);

      /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
      foreach ($this->documents as $document) {
        $document_markup = $view_builder->view($document->getPublishedVersion());
        $context['form']["legal_{$document->id()}"]['#prefix'] = $renderer->renderPlain($document_markup);
      }
    }
  }

}
