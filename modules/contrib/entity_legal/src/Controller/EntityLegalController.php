<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Controller\EntityLegalController.
 */

namespace Drupal\entity_legal\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\entity_legal\EntityLegalDocumentInterface;
use Drupal\entity_legal\EntityLegalDocumentVersionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class EntityLegalController.
 *
 * @package Drupal\entity_legal\Controller
 */
class EntityLegalController extends ControllerBase {

  /**
   * The entity legal document version storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityLegalDocumentVersionStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager')
        ->getStorage(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
    );
  }

  /**
   * EntityLegalController constructor.
   *
   * @param EntityStorageInterface $entity_legal_document_version_storage
   *   The custom block storage.
   */
  public function __construct(EntityStorageInterface $entity_legal_document_version_storage) {
    $this->entityLegalDocumentVersionStorage = $entity_legal_document_version_storage;
  }

  /**
   * Page title callback for the Entity Legal Document edit form.
   *
   * @param EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   */
  public function documentEditPageTitle(EntityLegalDocumentInterface $entity_legal_document) {
    return $this->t('Edit %label', ['%label' => $entity_legal_document->label()]);
  }

  /**
   * Page callback for the Entity Legal Document.
   *
   * @param EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   * @param EntityLegalDocumentVersionInterface|NULL $entity_legal_document_version
   *   The Entity Legal Document version entity.
   */
  public function documentPage(EntityLegalDocumentInterface $entity_legal_document, EntityLegalDocumentVersionInterface $entity_legal_document_version = NULL) {
    if (is_null($entity_legal_document_version)) {
      $entity_legal_document_version = $entity_legal_document->getPublishedVersion();
      if (!$entity_legal_document_version) {
        throw new NotFoundHttpException();
      }
    }

    // If specified version is unpublished, display a message.
    if ($entity_legal_document_version->id() != $entity_legal_document->getPublishedVersion()->id()) {
      drupal_set_message('You are viewing an unpublished version of this legal document.', 'warning');
    }

    return \Drupal::entityTypeManager()
      ->getViewBuilder(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME)
      ->view($entity_legal_document_version);
  }

  /**
   * Page title callback for the Entity Legal Document.
   *
   * @param EntityLegalDocumentInterface $entity_legal_document
   *   The Entity Legal Document entity.
   * @param EntityLegalDocumentVersionInterface|NULL $entity_legal_document_version
   *   The Entity Legal Document version entity.
   */
  public function documentPageTitle(EntityLegalDocumentInterface $entity_legal_document, EntityLegalDocumentVersionInterface $entity_legal_document_version = NULL) {
    if (is_null($entity_legal_document_version)) {
      $entity_legal_document_version = $entity_legal_document->getPublishedVersion();
    }

    return $entity_legal_document_version->label();
  }

  /**
   * Page callback for the Entity Legal Document Version form.
   *
   * @param EntityLegalDocumentInterface $entity_legal_document
   *   The entity legal document.
   * @param Request $request
   *   The current request object.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function documentVersionForm(EntityLegalDocumentInterface $entity_legal_document, Request $request) {
    $entity_legal_document_version = $this->entityLegalDocumentVersionStorage->create([
      'document_name' => $entity_legal_document->id(),
    ]);
    return $this->entityFormBuilder()->getForm($entity_legal_document_version);
  }

  /**
   * Page title callback for the Entity Legal Document Version add form.
   *
   * @param EntityLegalDocumentInterface $entity_legal_document
   *   The entity legal document.
   *
   * @return string
   *   The page title.
   */
  public function documentVersionAddFormTitle(EntityLegalDocumentInterface $entity_legal_document) {
    return $this->t('Add %type legal document version', ['%type' => $entity_legal_document->label()]);
  }

  /**
   * Page title callback for the Entity Legal Document Version edit form.
   *
   * @param string $entity_legal_document
   *   The entity legal document id.
   * @param EntityLegalDocumentVersionInterface $entity_legal_document_version
   *   The Entity Legal Document version entity.
   *
   * @return string
   *   The page title.
   */
  public function documentVersionEditFormTitle($entity_legal_document, EntityLegalDocumentVersionInterface $entity_legal_document_version) {
    return $this->t('Edit %label', ['%label' => $entity_legal_document_version->label()]);
  }

}
