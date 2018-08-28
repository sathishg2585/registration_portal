<?php

/**
 * @file
 * Contains \Drupal\nbcu_sso\Form\NbcuSsoSettingsForm.
 */

namespace Drupal\nbcu_sso\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form builder for the nbcu_sso basic settings form.
 */
class NbcuSsoSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'nbcu_sso_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['nbcu_sso.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('nbcu_sso.settings');

    $form['basic'] = array(
      '#type' => 'fieldset',
      '#title' => $this->t('Settings to Reset/Inactive User session from Drupal'),
      '#collapsible' => TRUE,
    );
    $cron_run_interval = array (
      0 => t('Never'),
      86400 => t('1 day'),
      604800 => t('1 week'),
    );	
    $form['basic']['cron_run_interval'] = array(
      '#type' => 'select',
      '#title' => $this->t('Run cron every'),
      '#default_value' => $config->get('cron_run_interval') ? $config->get('cron_run_interval') : 0,
      '#options' => $cron_run_interval,
      '#required' => TRUE,	  
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('nbcu_sso.settings');
    $config->set('cron_run_interval', $form_state->getValue('cron_run_interval'));
    $config->save();
  }
}
