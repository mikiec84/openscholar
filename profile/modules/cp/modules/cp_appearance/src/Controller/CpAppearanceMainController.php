<?php

namespace Drupal\cp_appearance\Controller;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\cp_appearance\AppearanceSettingsBuilderInterface;
use Drupal\cp_appearance\Form\ThemeForm;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for the cp_users page.
 *
 * Also invokes the modals.
 */
class CpAppearanceMainController extends ControllerBase {

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * Appearance settings builder.
   *
   * @var \Drupal\cp_appearance\AppearanceSettingsBuilderInterface
   */
  protected $appearanceSettingsBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('theme_handler'),
      $container->get('config.factory'),
      $container->get('cp_appearance.appearance_settings_builder')
    );
  }

  /**
   * CpAppearanceMainController constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\cp_appearance\AppearanceSettingsBuilderInterface $appearance_settings_builder
   *   Appearance settings builder service.
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ConfigFactoryInterface $config_factory, AppearanceSettingsBuilderInterface $appearance_settings_builder) {
    $this->themeHandler = $theme_handler;
    $this->configFactory = $config_factory;
    $this->appearanceSettingsBuilder = $appearance_settings_builder;
  }

  /**
   * Entry point for cp/users.
   */
  public function main(): array {
    /** @var \Drupal\Core\Extension\Extension[] $themes */
    $themes = $this->appearanceSettingsBuilder->getThemes();

    // Use for simple dropdown for now.
    $basic_theme_options = [];
    foreach ($themes as $theme) {
      $basic_theme_options[$theme->getName()] = $theme->info['name'];
    }

    // There are two possible theme groups.
    $theme_groups = ['featured' => $themes, 'basic' => []];
    $theme_group_titles = [
      'featured' => $this->formatPlural(count($theme_groups['featured']), 'Featured theme', 'Featured themes'),
    ];

    uasort($theme_groups['featured'], 'system_sort_themes');
    $this->moduleHandler()->alter('cp_appearance_themes_page', $theme_groups);

    $build = [];
    $build[] = [
      '#theme' => 'cp_appearance_themes_page',
      '#theme_groups' => $theme_groups,
      '#theme_group_titles' => $theme_group_titles,
    ];

    $build[] = $this->formBuilder()->getForm(ThemeForm::class, $basic_theme_options);

    return $build;
  }

  /**
   * Set a theme as default.
   *
   * @param string $theme
   *   The theme name.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   A request object containing a theme name.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirects back to the appearance admin page.
   */
  public function setTheme($theme, Request $request): RedirectResponse {
    $config = $this->configFactory->getEditable('system.theme');
    $themes = $this->themeHandler->listInfo();

    // Check if the specified theme is one recognized by the system.
    // Or try to install the theme.
    if (isset($themes[$theme])) {
      $config->set('default', $theme)->save();

      $this->messenger()->addStatus($this->t('%theme is now your theme.', ['%theme' => $themes[$theme]->info['name']]));
    }
    else {
      $this->messenger()->addError($this->t('The %theme theme was not found.', ['%theme' => $theme]));
    }

    return $this->redirect('cp.appearance');
  }

}