<?php

namespace Drupal\webform_encrypt;

use Drupal\Core\Entity\EntityInterface;
use Drupal\webform\WebformSubmissionStorage;
use Drupal\encrypt\Entity\EncryptionProfile;

/**
 * Alter webform submission storage definitions.
 */
class WebformEncryptSubmissionStorage extends WebformSubmissionStorage {

  /**
   * Helper function to recursively encrypt fields.
   *
   * @param array $data
   *   The current form data array.
   * @param object $webform
   *   The webform we are encrypting..
   */
  public function encryptElements(array $data, $webform) {
    // Load the configuration.
    $config = $webform->getThirdPartySetting('webform_encrypt', 'element');

    foreach ($data as $element_name => $value) {
      $encryption_profile = isset($config[$element_name]) ? EncryptionProfile::load($config[$element_name]['encrypt_profile']) : FALSE;
      // If the value is an array and we have a encryption profile.
      if ($encryption_profile) {
        if (is_array($value)) {
          $this->encryptChildren($data[$element_name], $encryption_profile);
        }
        else {
          $encrypted_value = \Drupal::service('encryption')
            ->encrypt($value, $encryption_profile);
          // Save the encrypted data value.
          $data[$element_name] = $encrypted_value;
        }
      }
    }
    return $data;
  }

  /**
   * Helper function to recursively encrypt children of fields.
   *
   * @param array $data
   *   Element data by reference.
   * @param object $encryption_profile
   *   The encryption profile to be used on this element.
   */
  public function encryptChildren(array &$data, $encryption_profile) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $this->encryptChildren($data[$key], $encryption_profile);
      }
      else {
        $encrypted_value = \Drupal::service('encryption')
          ->encrypt($value, $encryption_profile);
        $data[$key] = $encrypted_value;
      }
    }
  }

  /**
   * Decrypts a string.
   *
   * @param string $string
   *   The string to be decrypted.
   * @param string $encryption_profile
   *   The encryption profile to be used to decrypt the string.
   * @param bool $check_permissions
   *   Flag that controls permissions check.
   *
   * @return string
   *   The decrypted value.
   */
  protected function decrypt($string, $encryption_profile, $check_permissions = TRUE) {
    if ($check_permissions && !\Drupal::currentUser()->hasPermission('view encrypted values')) {
      return '[Value Encrypted]';
    }

    $decrypted_value = \Drupal::service('encryption')->decrypt($string, $encryption_profile);
    if ($decrypted_value === FALSE) {
      return $string;
    }

    return $decrypted_value;
  }

  /**
   * Helper function to recursively decrypt fields.
   *
   * @param object $webform_submission
   *   The webform submission to work on.
   */
  public function decryptElements($webform_submission) {
    // Load webform.
    $webform = $webform_submission->getWebform();
    // Load submission data.
    $data = $webform_submission->getData();
    // Load the configuration.
    $config = $webform->getThirdPartySetting('webform_encrypt', 'element');
    foreach ($data as $element_name => $value) {
      $encryption_profile = isset($config[$element_name]) ? EncryptionProfile::load($config[$element_name]['encrypt_profile']) : FALSE;
      if ($encryption_profile) {
        if (is_array($value)) {
          $this->decryptChildren($data[$element_name], $encryption_profile);
        }
        else {
          $decrypted_value = $this->decrypt($value, $encryption_profile);
          // Save the decrypted data value.
          $data[$element_name] = $decrypted_value;
        }
      }
    }
    return $data;
  }

  /**
   * Helper function to recursively decrypt children of fields.
   *
   * @param array $data
   *   Element data by reference.
   * @param object $encryption_profile
   *   The encryption profile to be used on this element.
   */
  public function decryptChildren(array &$data, $encryption_profile) {
    foreach ($data as $key => $value) {
      if (is_array($value)) {
        $this->decryptChildren($data[$key], $encryption_profile);
      }
      else {
        $decrypted_value = $this->decrypt($value, $encryption_profile);
        $data[$key] = $decrypted_value;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function doPreSave(EntityInterface $entity) {
    /** @var \Drupal\webform\WebformSubmissionInterface $entity */
    $id = parent::doPreSave($entity);

    $data_original = $entity->getData();

    $webform = $entity->getWebform();

    $encrypted_data = $this->encryptElements($data_original, $webform);
    $entity->setData($encrypted_data);

    $this->invokeWebformElements('preSave', $entity);
    $this->invokeWebformHandlers('preSave', $entity);
    return $id;
  }

  /**
   * {@inheritdoc}
   */
  protected function loadData(array &$webform_submissions) {
    parent::loadData($webform_submissions);

    foreach ($webform_submissions as &$webform_submission) {
      $data = $this->decryptElements($webform_submission);
      $webform_submission->setData($data);
      $webform_submission->setOriginalData($data);
    }
  }

}
