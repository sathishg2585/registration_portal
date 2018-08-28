<?php
namespace Drupal\profile_registration\Controller;
use Drupal\node\Entity\Node;
use Drupal\Core\Url;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\profile_registration\Service\TibcoSQLApi;

class ProfileRegistrationController extends ControllerBase{

  public $tibcosqlApi;
  public $config;
  /**
   *
   *   The configuration factory.
   */
  public function __construct(TibcoSQLApi $tibcosqlApi) {
    $this->tibcosqlApi = $tibcosqlApi;
    $config = \Drupal::config('profile_registration.settings');
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('profile_registration.tibcosqlapi'),
      $container->get('config.factory')
    );
  }

  public function testpush() {
    $data = array("audience" => array("android_channel" => "cde71ff7-68b3-4ff2-995d-ba0f2fa8e175"), "device_types" => array("ios","android"), "notification" => array("alert" => "Sample Test 123"));
    //$data = array("audience" => array("tag" => "rsn_tag_bayareacalifornia", "group" => "sports-rsn"), "device_types" => "all", "notification" => array("alert" => "TAG Group Test"));
    /*
    {
      "contentid": 1009,
      "contenttype": "registration",  
      "date": "2018-06-30T10:00:00Z",
      "json": {
        "ssn": "21212109", 
        "sso": "1212121", 
        "updated": "1531146574",
        "passport": "21212121",
        "last_name": "Kumar Updated",
        "first_name": "Sathish Updated", 
        "document_verified": "1"
      }
    }
    */
    $data = array(
                'contentid' => 9898, 
                'contenttype' => 'registration', 
                'date' => '2018-07-12T10:00:00Z', 
                'json' => array(
                  'ssn' => '21212109', 
                  'sso' => '1212121', 
                  'updated' => '1531146574',
                  'passport' => '21212121',
                  'last_name' => 'Kumar Updated',
                  'first_name' => 'Sathish Updated', 
                  'document_verified' => '1'
                )
            );
    $data_string = json_encode($data);
    $parameters = [];
    $endpoint = '';
    $response = $this->tibcosqlApi->request('PUT', $endpoint , $parameters, $data_string);
    echo ($response);
    exit;
    return array();
  }

  public function json_cron() {
    $flag = $registeration_flag = 0;
    $database = \Drupal::service('database');
    $select = $database->select('webform_submission_data', 'wsd');
    $select->fields('wsd', array('sid', 'name', 'value'));	
    $select->fields('ws', array('token', 'changed'));
    $select->addJoin('LEFT','webform_submission','ws','wsd.sid=ws.sid');
    $select->condition('wsd.webform_id', 'registration', '=');    
    $select->condition('wsd.name', array('first_name', 'last_name', 'sso', 'passport','ssn', 'document_verified', 'json_flag'), 'IN');
    $executed = $select->execute();
	
    // Get all the results.
    $results = $executed->fetchAll(\PDO::FETCH_ASSOC);	
    $records = array();
    if (count($results) > 0) {
      foreach($results as $result){
        $records[$result['sid']][$result['name']] = $result['value'];
        $records[$result['sid']]['token'] = $result['token'];
        $records[$result['sid']]['updated'] = $result['changed'];
      }
    }
    $data = array();
    $sids = array();
    if (count($records) > 0){
      foreach($records as $sid => $record){
        $json_data_string = '';
        if (empty($record['json_flag']) || $record['json_flag'] == 0 ){  
          if (!empty($record['first_name']) && (!empty($record['passport']) || !empty($record['ssn']))) {
            $data[$record['sso']] = array(
              'first_name' => $record['first_name'],		  
              'last_name' => $record['last_name'],
              'sso' => $record['sso'],
              'passport' => $record['passport'],
              'ssn' => $record['ssn'],
              'document_verified' => $record['document_verified'],
              'updated' => $record['updated'],
            );
            $sids[] = $sid;
            $flag++;
          }
        }
      }
      if ($flag > 0) {
        $json_data_string = json_encode($data);
        $previous_day = date('Ymd');
        $database = \Drupal::service('database');
        $select = $database->select('registration', 'reg');
        $select->fields('reg', array('name'));
        $select->condition('reg.date', $previous_day, '=');
        $registration = $select->execute();
        $registration_results = $registration->fetchAll(\PDO::FETCH_ASSOC);
        if (isset($registration_results) && !empty($registration_results)){
          echo "UPDATE";
          $registeration_flag++;
        }
        else {
          echo "INSERT";
          $database = \Drupal::service('database');
          $insert = $database->insert('registration')
            ->fields([
              'name' => 'Registration',
              'date' => date('Y-m-d H:i:s'),
              'json_data' => $json_data_string,
            ]);
          $insert->execute();
          $registeration_flag++;
        }
      }
      if ($registeration_flag > 0 && count($sids) > 0){
        $database = \Drupal::service('database');
        $update = $database->update('webform_submission_data');	
        $update->fields(['value' => 1,]);	
        $update->condition('webform_id', 'registration', '=');
        $update->condition('sid', $sids, 'IN');
        $update->condition('name', 'json_flag', '=');
        //$update->execute();
      }
      exit;
    }
  }
}