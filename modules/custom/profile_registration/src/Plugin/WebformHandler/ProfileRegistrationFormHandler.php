<?php
namespace Drupal\profile_registration\Plugin\WebformHandler;

use Drupal\Core\Form;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Utility\WebformYaml;
use Drupal\webform\Plugin\WebformHandlerBase;
use Drupal\webform\WebformSubmissionInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Webform submission debug handler.
 *
 * @WebformHandler(
 *   id = "profile_registration_webform",
 *   label = @Translation("Profile Registration"),
 *   category = @Translation("Profile Registration"),
 *   description = @Translation("Profile Registration Webform Submission."),
 *   cardinality = \Drupal\webform\Plugin\WebformHandlerInterface::CARDINALITY_SINGLE,
 *   results = \Drupal\webform\Plugin\WebformHandlerInterface::RESULTS_PROCESSED,
 *   submission = \Drupal\webform\Plugin\WebformHandlerInterface::SUBMISSION_OPTIONAL,
 * )
 */

class ProfileRegistrationFormHandler extends WebformHandlerBase {
  
  /**
   * {@inheritdoc}
  public function preCreate(array $values) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}
  public function postCreate(WebformSubmissionInterface $webform_submission) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}
  public function postLoad(WebformSubmissionInterface $webform_submission) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}
  public function preDelete(WebformSubmissionInterface $webform_submission) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}
  public function postDelete(WebformSubmissionInterface $webform_submission) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}
  public function preSave(WebformSubmissionInterface $webform_submission) {
    $this->displayMessage(__FUNCTION__);
  }
   */
  /**
   * {@inheritdoc}

  public function postSave(WebformSubmissionInterface $webform_submission, $update = TRUE) {
  }
   */

  /**
   * {@inheritdoc}
  
  public function validateForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $submitted_values = $webform_submission->getData();
  } */

  public function submitForm(array &$form, FormStateInterface $form_state, WebformSubmissionInterface $webform_submission) {
    $submitted_values = $webform_submission->getData();
    //echo '---'.$webform_submission->serial();
    //echo '---'.$webform_submission->id();
    if ($webform_submission->isDraft()) {
      // DRAFTS
    }
    else {
      // SUBMITTED
      if (isset($submitted_values['page_2_hidden']) && $submitted_values['page_2_hidden'] == 'page-2') {
        $webform_submission->setData($submitted_values);
        $webform_submission->save();
        $contentid = $webform_submission->id();
        $contenttype = 'registration';
        $date = date('Y-m-d\TH:i:s\Z', $webform_submission->getChangedTime());
        $name_title = $submitted_values['name_title'];
        $first_name = $submitted_values['first_name'];
        $middle_name = $submitted_values['middle_name'];
        $last_name = $submitted_values['last_name'];
        $dob_month = $submitted_values['dob_month'];
        $dob_date = $submitted_values['dob_date'];
        $dob_year = $submitted_values['dob_year'];
        $gender = $submitted_values['gender'];
        $race = $submitted_values['race'];
        $citizenship = $submitted_values['citizenship'];
        $sso = $submitted_values['sso'];
        $ssn = $submitted_values['ssn'];
        $passport = $submitted_values['passport'];
        $issues_by = $submitted_values['issues_by'];
        $document_verified = $submitted_values['document_verified'];
        $created = date('Y-m-d\TH:i:s\Z', $webform_submission->getCreatedTime());
        
        $data = array(
          'contentid' => $contentid, 
          'contenttype' => $contenttype, 
          'date' => $date, 
          'json' => array(
            'name_title' => $name_title, 
            'first_name' => $first_name, 
            'middle_name' => $middle_name,
            'last_name' => $last_name,
            'dob' => $dob_date.'-'.$dob_month.'-'.$dob_year,
            'gender' => $gender,
            'race' => $race,
            'citizenship' => $citizenship,
            'sso' => $sso,
            'ssn' => $ssn,
            'passport' => $passport,
            'issues_by' => $issues_by,
            'document_verified' => $document_verified
          )
        );
        
        $data_string = json_encode($data);
        $parameters = [];
        $endpoint = '';
        $response = \Drupal::service('profile_registration.tibcosqlapi')->request('PUT', $endpoint , $parameters, $data_string);
        if (strtolower(substr($response, 0, 7)) == 'success') {
          \Drupal::logger('Webform Tibco API')->info('Webform "#{id}" was successfully submitted to Tibco. Tibco Response: {response} ', array('id' => $contentid, 'response' => (string) $response));
          $submitted_values['json_flag'] = 1;
          $webform_submission->setData($submitted_values);
          $webform_submission->save();
        }
      }
    }
  }
}   
?>