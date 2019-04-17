<?php

namespace Drupal\cp_appearance\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\cp_appearance\ThemeSelectorBuilderInterface;
use Ds\Map;

/**
 * Flavor selection form.
 */
class FlavorForm implements FormInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The theme for which the form will be created.
   *
   * @var \Drupal\Core\Extension\Extension
   */
  protected $theme;

  /**
   * Available flavors of the theme.
   *
   * Flavor name and its information mapping.
   *
   * @var \Ds\Map
   */
  protected $flavors;

  /**
   * Theme selector builder service.
   *
   * @var \Drupal\cp_appearance\ThemeSelectorBuilderInterface
   */
  protected $themeSelectorBuilder;

  /**
   * Creates a new FlavorForm object.
   *
   * @param \Drupal\Core\Extension\Extension $theme
   *   The theme for which the form will be created.
   * @param \Ds\Map $flavors
   *   Available flavors of the theme.
   * @param \Drupal\cp_appearance\ThemeSelectorBuilderInterface $theme_selector_builder
   *   Theme selector builder service.
   */
  public function __construct(Extension $theme, Map $flavors, ThemeSelectorBuilderInterface $theme_selector_builder) {
    $this->theme = $theme;
    $this->flavors = $flavors;
    $this->themeSelectorBuilder = $theme_selector_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return "cp_appearance_{$this->theme->getName()}_flavor_form";
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $options = [
      '_none' => $this->t('None'),
    ];

    /** @var \Drupal\Core\Extension\Extension $flavor */
    foreach ($this->flavors->values() as $flavor) {
      $options[$flavor->getName()] = $flavor->info['name'];
    }

    $form["options_{$this->theme->getName()}"] = [
      '#type' => 'select',
      '#title' => $this->t('Flavors'),
      '#options' => $options,
      '#ajax' => [
        'callback' => '::updatePreview',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {}

  /**
   * Flavor option change handler.
   *
   * Updates preview based on the selection.
   *
   * @ingroup forms
   */
  public function updatePreview(array &$form, FormStateInterface $form_state): AjaxResponse {
    $response = new AjaxResponse();
    /** @var string $selection */
    $selection = $form_state->getValue("options_{$this->theme->getName()}");

    if ($selection !== '_none') {
      /** @var \Drupal\Core\Extension\Extension $flavor */
      $flavor = $this->flavors->get($selection);
      /** @var array $info */
      $info = $flavor->info;
      /** @var string|null $screenshot_uri */
      $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($flavor);
    }
    else {
      /** @var array $info */
      $info = $this->theme->info;
      /** @var string|null $screenshot_uri */
      $screenshot_uri = $this->themeSelectorBuilder->getScreenshotUri($this->theme);
    }

    $response->addCommand(new ReplaceCommand("#theme-selector-{$this->theme->getName()} .theme-screenshot img", [
      '#theme' => 'image',
      '#uri' => $screenshot_uri ?? '',
      '#alt' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
      '#title' => $this->t('Screenshot for @theme theme', ['@theme' => $info['name']]),
      '#attributes' => ['class' => ['screenshot']],
    ]));

    return $response;
  }

}