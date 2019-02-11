<?php

namespace Drupal\cp_settings\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * CP settings annotation.
 *
 * @ingroup plugin_api
 *
 * @Annotation
 */
class CpSetting extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The setting title.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * Setting group.
   *
   * @var \Drupal\cp_settings\Annotation\Group
   */
  public $group;

}
