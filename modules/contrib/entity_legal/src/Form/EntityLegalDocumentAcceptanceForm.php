<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Form\EntityLegalDocumentAcceptanceForm.
 */

namespace Drupal\entity_legal\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\entity_legal\EntityLegalDocumentInterface;

/**
 * Provides a confirmation form for deleting a custom block entity.
 */
class EntityLegalDocumentAcceptanceForm extends FormBase {

  /**
   * The Entity Legal Document used by this form.
   *
   * @var \Drupal\entity_legal\EntityLegalDocumentInterface
   */
  protected $document;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityLegalDocumentInterface $document) {
    $this->document = $document;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'entity_legal_document_acceptance_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $has_agreed = $this->document->userHasAgreed();

    $form['agree'] = [
      '#title'         => $this->document->getAcceptanceLabel(),
      '#type'          => 'checkbox',
      '#required'      => TRUE,
      '#default_value' => $has_agreed,
      '#disabled'      => $has_agreed,
    ];

    if ($has_agreed) {
      $user = \Drupal::currentUser();
      $acceptances = $this->document->getAcceptances($user);
      // @TODO
      //    if (!empty($acceptances)) {
      //      $form['agree']['#description'] = render(entity_view(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME, $acceptances));
      //    }
    }

    $form['submit'] = [
      '#value'  => t('Submit'),
      '#type'   => 'submit',
      '#access' => !$has_agreed,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $published_version = $this->document->getPublishedVersion();
    \Drupal::entityTypeManager()
      ->getStorage(ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME)
      ->create([
        'document_version_name' => $published_version->id(),
      ])
      ->save();
  }

}
