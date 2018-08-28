<?php

/**
 * @file
 * Builds placeholder replacement tokens for taxonomy terms and vocabularies.
 */

use Drupal\Core\Render\BubbleableMetadata;

/**
 * Implements hook_token_info().
 */
function entity_legal_token_info() {
  $types[ENTITY_LEGAL_DOCUMENT_ENTITY_NAME] = [
    'name'        => t('Legal document'),
    'description' => t('Tokens related to the "Legal document" entities.'),
    'needs-data'  => 'entity_legal_document',
  ];

  $types[ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME] = [
    'name'        => t('Legal document version'),
    'description' => t('Tokens related to the "Legal document version" entities.'),
    'needs-data'  => 'entity_legal_document_version',
  ];

  $types[ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME] = [
    'name'        => t('Legal document acceptance'),
    'description' => t('Tokens related to the "Legal document acceptance" entities.'),
    'needs-data'  => 'entity_legal_document_acceptance',
  ];

  // Legal document related tokens.
  $document['label'] = [
    'name'        => t('Label'),
    'description' => t('The human readable label.'),
  ];

  $document['published-version'] = [
    'name'        => t('Published version'),
    'description' => t('The current published version.'),
    'type'        => ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME,
  ];

  // Legal document version related tokens.
  $document_version['acceptance-label'] = [
    'name'        => t('Acceptance label'),
    'description' => t('The acceptance label.'),
  ];

  $document_version['changed'] = [
    'name'        => t('Date changed'),
    'description' => t('The date the legal document version was changed.'),
    'type'        => 'date',
  ];

  $document_version['created'] = [
    'name'        => t('Date created'),
    'description' => t('The date the legal document version was created.'),
    'type'        => 'date',
  ];

  $document_version['label'] = [
    'name'        => t('Label'),
    'description' => t('The human readable label.'),
  ];

  // Legal document acceptance related tokens.
  $document_acceptance['acceptance-date'] = [
    'name'        => t('Date accepted'),
    'description' => t('The date the legal document was accepted.'),
    'type'        => 'date',
  ];

  $document_acceptance['aid'] = [
    'name'        => t('Legal document acceptance ID'),
    'description' => t('The document acceptance ID.'),
  ];

  $document_acceptance['author'] = [
    'name'        => t('Author'),
    'description' => t('The author of the document acceptance.'),
    'type'        => 'user',
  ];

  $document_acceptance['document-version'] = [
    'name'        => t('Document version'),
    'description' => t('The document version accepted.'),
    'type'        => ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME,
  ];

  return [
    'types'  => $types,
    'tokens' => [
      ENTITY_LEGAL_DOCUMENT_ENTITY_NAME            => $document,
      ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME    => $document_version,
      ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME => $document_acceptance,
    ],
  ];
}

/**
 * Implements hook_tokens().
 */
function entity_legal_tokens($type, $tokens, array $data, array $options, BubbleableMetadata $bubbleable_metadata) {
  $token_service = \Drupal::service('token');

  $replacements = [];
  switch ($type) {
    case ENTITY_LEGAL_DOCUMENT_ENTITY_NAME:
      if (isset($data[ENTITY_LEGAL_DOCUMENT_ENTITY_NAME])) {
        /** @var \Drupal\entity_legal\EntityLegalDocumentInterface $entity */
        $entity = $data[ENTITY_LEGAL_DOCUMENT_ENTITY_NAME];
        foreach ($tokens as $name => $original) {
          switch ($name) {
            case 'label':
              $replacements[$original] = $entity->label();
              break;

            case 'published-version':
              $replacements[$original] = $entity->getPublishedVersion()
                ->label();
              break;
          }
        }

        if ($published_version_tokens = $token_service->findWithPrefix($tokens, 'published-version')) {
          $pubished_version = $entity->getPublishedVersion();
          $replacements += $token_service->generate(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, $published_version_tokens, [ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME => $pubished_version], $options, $bubbleable_metadata);
        }
      }
      break;

    case ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME:
      if (isset($data[ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME])) {
        /** @var \Drupal\entity_legal\EntityLegalDocumentVersionInterface $entity */
        $entity = $data[ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME];
        foreach ($tokens as $name => $original) {
          switch ($name) {
            case 'acceptance-label':
              $replacements[$original] = $entity->get('acceptance_label')->value;
              break;

            case 'changed':
              $replacements[$original] = $entity->getFormattedDate('changed');
              break;

            case 'created':
              $replacements[$original] = $entity->getFormattedDate('created');
              break;

            case 'label':
              $replacements[$original] = $entity->label();
              break;
          }
        }

        if ($changed_tokens = $token_service->findWithPrefix($tokens, 'changed')) {
          $replacements += $token_service->generate('date', $changed_tokens, ['date' => $entity->getChangedTime()], $options, $bubbleable_metadata);
        }

        if ($created_tokens = $token_service->findWithPrefix($tokens, 'created')) {
          $replacements += $token_service->generate('date', $created_tokens, ['date' => $entity->getCreatedTime()], $options, $bubbleable_metadata);
        }
      }
      break;

    case ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME:
      if (isset($data[ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME])) {
        /** @var \Drupal\entity_legal\EntityLegalDocumentAcceptanceInterface $entity */
        $entity = $data[ENTITY_LEGAL_DOCUMENT_ACCEPTANCE_ENTITY_NAME];

        foreach ($tokens as $name => $original) {
          switch ($name) {
            case 'acceptance-date':
              $replacements[$original] = \Drupal::service('date.formatter')
                ->format($entity->get('acceptance_date')->value);
              break;

            case 'aid':
              $replacements[$original] = $entity->id();
              break;

            case 'author':
              /** @var \Drupal\Core\Field\EntityReferenceFieldItemListInterface $item */
              $item = $entity->get('uid');
              $replacements[$original] = $item->referencedEntities()[0]->label();
              break;

            case 'document-version':
              $replacements[$original] = $entity->getDocumentVersion()
                ->label();
              break;
          }
        }

        if ($accepted_date_tokens = $token_service->findWithPrefix($tokens, 'acceptance-date')) {
          $replacements += $token_service->generate('date', $accepted_date_tokens, ['date' => $entity->get('acceptance_date')->value], $options, $bubbleable_metadata);
        }

        if ($document_version_tokens = $token_service->findWithPrefix($tokens, 'document-version')) {
          $document_version = $entity->getDocumentVersion();
          $replacements += $token_service->generate(ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME, $document_version_tokens, [ENTITY_LEGAL_DOCUMENT_VERSION_ENTITY_NAME => $document_version], $options, $bubbleable_metadata);
        }
      }
      break;
  }

  return $replacements;
}
