<?php

namespace Drupal\dynamic_page_module\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a Dynamic Page Module form.
 */
final class LeadershipForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'dynamic_page_module_leadership';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['leadership'] = [
      '#type' => 'multivalue',
      '#title' => $this->t('Leadership'),
      'name' => [
        '#type' => 'textfield',
        '#title' => $this->t('Name'),
        '#required' => TRUE,
      ],
      'designation' => [
        '#type' => 'textfield',
        '#title' => $this->t('Designation'),
        '#required' => TRUE,
      ],
      'linkedin_url' => [
        '#type' => 'textfield',
        '#title' => $this->t('LinkedIn Profile'),
        '#required' => TRUE,
      ],
      'profile_image' => [
        '#type' => 'file',
        '#title' => $this->t('Profile Image'),
        '#required' => TRUE,
      ],
    ];
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Save'),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    try {
      $connection = Database::getConnection();
      $field = $form_state->getValues()['leadership'];
      foreach ($field as $field_data) {
        $fields['name'] = $field_data['name'];
        $fields['designation'] = $field_data['designation'];
        $fields['linkedin_url'] = $field_data['linkedin_url'];
        $fields['profile_image'] = $field_data['profile_image'];
        $connection->insert('leaders')
        ->fields($fields)
        ->execute();
      }
      $this->messenger()->addStatus($this->t('Your data has been submitted.'));
    }
    catch (\Exception $ex) {
      \Drupal::logger('dynamic_page_module')->error($ex->getMessage());
    }
  }

}
