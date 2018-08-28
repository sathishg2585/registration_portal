<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalPluginManager.
 */

namespace Drupal\entity_legal;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Class EntityLegalPluginManager.
 *
 * @package Drupal\entity_legal
 */
class EntityLegalPluginManager extends DefaultPluginManager {

  /**
   * {@inheritdoc}
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityLegal', $namespaces, $module_handler, 'Drupal\entity_legal\EntityLegalPluginInterface', 'Drupal\entity_legal\Annotation\EntityLegal');
    $this->alterInfo('entity_legal_methods');
    $this->setCacheBackend($cache_backend, 'entity_legal_methods');
  }

}
