<?php

namespace Drupal\dynamic_page_module\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configuration form for Best Anchor.
 */
class BestAnchorForm extends ConfigFormBase {

  /**
   * Configuration settings.
   *
   * @var string
   */
  const SETTINGS = 'dynamic_page_module.settings';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dynamic_page_module_anchor_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);
    $form['label'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'user',
      '#title' => $this->t('Best Anchor of this week'),
      '#placeholder' => $this->t('Enter best anchor.'),
      '#required' => TRUE,
      '#default_value' => \Drupal::entityTypeManager()->getStorage('user')->load($config->get('anchor') ?? ''),
      '#selection_settings' => [
        'filter' => [
          'role' => ['news_anchor'],
        ],
      ],
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $this->config(static::SETTINGS)
      ->set('anchor', $form_state->getValue('label'))
      ->save();

    parent::submitForm($form, $form_state);
  }

}
