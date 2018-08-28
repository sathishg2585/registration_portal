<?php

/**
 * @file
 * Contains \Drupal\entity_legal\Annotation\EntityLegal.
 */

namespace Drupal\entity_legal\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class EntityLegal.
 *
 * Plugin Namespace: Plugin\EntityLegal
 *
 * @package Drupal\entity_legal\Annotation
 *
 * @Annotation
 */
class EntityLegal extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the Entity Legal method plugin.
   *
   * @ingroup plugin_translatable
   *
   * @var \Drupal\Core\Annotation\Translation
   */
  public $label;

  /**
   * The method type; "new_users" or "existing_users".
   *
   * @var string
   */
  public $type;

}
