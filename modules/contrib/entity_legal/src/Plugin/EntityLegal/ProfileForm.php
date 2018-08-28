<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Plugin\EntityLegal\ProfileForm.
 */

namespace Drupal\entity_legal\Plugin\EntityLegal;

use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_legal\EntityLegalPluginBase;

/**
 * Method class for displaying a checkbox on the user register form.
 *
 * @EntityLegal(
 *   id = "form_link",
 *   label = @Translation("Checkbox on signup form"),
 *   type = "new_users",
 * )
 */
class ProfileForm extends EntityLegalPluginBase {

  /**
   * {@inheritdoc}
   */
  public function execute(&$context = []) {
    if (!empty($this->documents)) {
      $context['form']['actions']['submit']['#submit'][] = [get_class($this), 'submitForm'];

      /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
      foreach ($this->documents as $document) {
        $context['form']['#cache']['tags'][] = "entity_legal_document:{$document->id()}";

        $context['form']["legal_{$document->id()}"] = [
          '#type'          => 'checkbox',
          '#title'         => $document->getAcceptanceLabel(),
          '#default_value' => $document->userHasAgreed(),
          '#required'      => TRUE,
        ];

        $context['form']['#entity_legal'] = $this;
      }
    }
  }

  /**
   * Submit handler for user register form.
   */
  public static function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $document */
    foreach ($form['#entity_legal']->documents as $document) {
      if (!empty($form_state->getValue(['legal_' . $document->id()]))) {
        $published_version = $document->getPublishedVersion();
        $acceptance = \Drupal::entityTypeManager()
          ->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
          ->create([
            'uid'                   => $form_state->getValue('uid'),
            'document_version_name' => $published_version->id(),
          ]);
        $acceptance->save();
      }
    }
  }

}
