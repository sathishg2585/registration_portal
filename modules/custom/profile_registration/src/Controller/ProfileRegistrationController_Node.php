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

class ProfileRegistrationController extends ControllerBase{

  public function json_cron() {
    $flag = 0;
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
            $values = array(
              'first_name' => $record['first_name'],		  
              'last_name' => $record['last_name'],
              'sso' => $record['sso'],
              'passport' => $record['passport'],
              'ssn' => $record['ssn'],
              'document_verified' => $record['document_verified'],
              'updated' => $record['updated'],
            );
            $json_data_string = json_encode($values);
            $sids[] = $sid;
            $query = \Drupal::entityQuery('node');
            $query->condition('status', 1);
            $query->condition('title', $record['sso']);
            $query->condition('type', 'profile_json');
            $profile_json_node = $query->execute();
            if (isset($profile_json_node) && !empty($profile_json_node)){
              //echo "~Update~";print_r($record);print_r($profile_json_node);//exit;
              $nid  =  reset($profile_json_node);
              $node = Node::load($nid);
              $node->set('field_date', date('Y-m-d',$record['updated']));
              $node->set('field_json_data', $json_data_string);
              $node->save();
            }
            else {
              //echo "~Create~";print_r($profile_json_node);exit;
              $node = Node::create(['type' => 'profile_json']);
              $node->set('title', $record['sso']);
              $node->set('field_date', $record['updated']);
              $node->set('field_json_data', $json_data_string);
              $node->enforceIsNew();
              $node->save();
            }
            $flag++;
          }
        }
      }
      
      if (count($sids) > 0){
        $database = \Drupal::service('database');
        $update = $database->update('webform_submission_data');	
        $update->fields(['value' => 1,]);	
        $update->condition('webform_id', 'registration', '=');
        $update->condition('sid', $sids, 'IN');
        $update->condition('name', 'json_flag', '=');
        $update->execute();
      } 
    }
  }
}