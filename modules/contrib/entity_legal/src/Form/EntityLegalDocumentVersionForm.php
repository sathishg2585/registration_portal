<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Form\EntityLegalDocumentVersionForm.
 */

namespace Drupal\entity_legal\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EntityLegalDocumentVersionForm.
 *
 * @package Drupal\entity_legal
 */
class EntityLegalDocumentVersionForm extends ContentEntityForm {

  /**
   * The entity being used by this form.
   *
   * @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form['label'] = [
      '#title'         => t('Title'),
      '#type'          => 'textfield',
      '#default_value' => $this->entity->label(),
      '#required'      => TRUE,
    ];

    $form['name'] = [
      '#type'          => 'machine_name',
      '#title'         => t('Machine-readable name'),
      '#required'      => TRUE,
      '#default_value' => !$this->entity->isNew() ? $this->entity->id() : $this->entity->getDefaultName($this->entity),
      '#machine_name'  => [
        'exists' => '\Drupal\entity_legal\Entity\EntityLegalDocumentVersion::load',
      ],
      '#disabled'      => !$this->entity->isNew(),
      '#maxlength'     => 64,
    ];

    $form['acceptance_label'] = [
      '#title'       => t('Acceptance label'),
      '#type'        => 'textfield',
      '#description' => t('e.g. I agree to the terms and conditions, use tokens to provide a link to the document.'),
      '#weight'      => 50,
    ];

    if (isset($this->entity->get('acceptance_label')->value)) {
      $form['acceptance_label']['#default_value'] = $this->entity->get('acceptance_label')->value;
    }
    else {
      $form['acceptance_label']['#default_value'] = $this->t('I agree to the <a href="@token_url">@document_label</a> document', [
        '@token_url'      => '[entity_legal_document:url]',
        '@document_label' => $this->entity->getDocument()->label(),
      ])->render();
    }

    $form['token_help'] = [
      '#theme'       => 'token_tree_link',
      '#token_types' => ['entity_legal_document'],
      '#weight'      => 51,
    ];

    return parent::form($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $this->entity->save();

    // Set this as the published version.
    $document = $this->entity->getDocument();
    if (!$document->getPublishedVersion()) {
      $document->setPublishedVersion($this->entity);
      $document->save();
    }

    $form_state->setRedirect('entity.entity_legal_document.edit_form', ['entity_legal_document' => $this->entity->bundle()]);
  }

}
