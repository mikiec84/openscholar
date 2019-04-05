<?php

namespace Drupal\os_theme_preview\Theme;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\os_theme_preview\HelperInterface;
use Drupal\vsite\Plugin\VsiteContextManagerInterface;

/**
 * Sets the preview theme.
 */
class Negotiator implements ThemeNegotiatorInterface {

  /**
   * Preview theme name.
   *
   * @var \Drupal\os_theme_preview\ThemePreview|null
   */
  protected $previewedTheme;

  /**
   * Helper service.
   *
   * @var \Drupal\os_theme_preview\HelperInterface
   */
  protected $helper;

  /**
   * Vsite context manager service.
   *
   * @var \Drupal\vsite\Plugin\VsiteContextManagerInterface
   */
  protected $vsiteContextManager;

  /**
   * Negotiator constructor.
   *
   * @param \Drupal\os_theme_preview\HelperInterface $helper
   *   Helper service.
   * @param \Drupal\vsite\Plugin\VsiteContextManagerInterface $vsite_context_manager
   *   Vsite context manager service.
   */
  public function __construct(HelperInterface $helper, VsiteContextManagerInterface $vsite_context_manager) {
    $this->helper = $helper;
    $this->vsiteContextManager = $vsite_context_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match): bool {
    // Also consider current active vsite while applying the theme.
    $this->previewedTheme = $this->helper->getPreviewedThemeData();
    /** @var \Drupal\group\Entity\GroupInterface|null $group */
    $group = $this->vsiteContextManager->getActiveVsite();

    if (!$this->previewedTheme) {
      return FALSE;
    }

    if (!$group) {
      return ($this->previewedTheme->getVsiteId() === 0);
    }

    return ($this->previewedTheme->getVsiteId() === (int) $group->id());
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match): ?string {
    return $this->previewedTheme->getName();
  }

}
