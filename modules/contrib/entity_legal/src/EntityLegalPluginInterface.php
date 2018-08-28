<?php

/**
 * @file
 * Contains \Drupal\entity_legal\EntityLegalPluginInterface.
 */

namespace Drupal\entity_legal;

use Drupal\Component\Plugin\PluginInspectionInterface;


/**
 * Interface ResponsiveMenusInterface.
 *
 * @package Drupal\responsive_menus
 */
interface EntityLegalPluginInterface extends PluginInspectionInterface {

  /**
   * Execute callback for Entity Legal method plugin.
   *
   * @param array $context
   *   Contextual information for plugin to execute on.
   */
  public function execute(&$context = []);

}
