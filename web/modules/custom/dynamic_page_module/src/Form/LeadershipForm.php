<?php

namespace Drupal\dynamic_page_module\Form;

use Drupal\Core\Database\Database;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

/**
 * Provides a Dynamic Page Module form.
 */
class LeadershipForm extends FormBase {

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
        '#type' => 'managed_file',
        '#title' => $this->t('Profile Image'),
        '#name' => 'profile_image',
        '#upload_location' => 'public://',
        '#upload_validators' => [
          'file_validate_extensions' => ['png jpg jpeg gif'],
        ],
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
      $field = $form_state->getValues()['leadership'];
      $connection = Database::getConnection();
      $field = $form_state->getValues()['leadership'];
      foreach ($field as $field_data) {
        $image_file = File::load($field_data['profile_image'][0]);
        $file_usage = \Drupal::service('file.usage');
        if (gettype($image_file) == 'object') {
          $image_file->setPermanent();
          $image_file->save();
          $file_usage->add($image_file, 'dynamic_page_module', 'file', $field_data['profile_image'][0]);
        }
        $fields = [
          'name' => $field_data['name'],
          'designation' => $field_data['designation'],
          'linkedin_url' => $field_data['linkedin_url'],
          'profile_image' => $field_data['profile_image'][0],
        ];
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
