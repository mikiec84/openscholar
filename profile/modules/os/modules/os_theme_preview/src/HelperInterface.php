<?php

namespace Drupal\os_theme_preview;

/**
 * The contract for theme preview helper.
 *
 * Most probably this is going to be renamed to something better, once the scope
 * becomes more clear.
 */
interface HelperInterface {

  /**
   * Starts the preview mode.
   *
   * @param string $theme
   *   The name of the theme to preview.
   * @param int $vsite_id
   *   The id of vsite where the preview would be activated.
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function startPreviewMode($theme, $vsite_id): void;

  /**
   * Returns the data of the theme being previewed.
   *
   * @return \Drupal\os_theme_preview\ThemePreview|null
   *   The data if currently in preview mode, otherwise NULL.
   */
  public function getPreviewedThemeData(): ?ThemePreview;

  /**
   * Stops preview mode.
   *
   * @throws \Drupal\os_theme_preview\ThemePreviewException
   */
  public function stopPreviewMode(): void;

}
