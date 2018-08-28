<?php

/**
 * @file
 * Contains \Drupal\profile_registration\Service\TibcoSQLApi.
 */

namespace Drupal\profile_registration\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Drupal\Core\Cache\CacheBackendInterface;


class TibcoSQLApi {
	
  protected $client;
  public $config;

  public function __construct(ConfigFactoryInterface $config_factory) {
    $config = $config_factory->get('profile_registration.settings');	
    $this->client = \Drupal::httpClient();
  }
  /**
   * Makes a request to the Oasis API.
   *
   * @param string $method
   *   The REST method to use when making the request.
   * @param string $path
   *   The API path to request.
   * @param array $parameters
   *   Associative array of parameters to send in the request body.
   * @param bool $batch
   *   TRUE if this request should be added to pending batch operations.
   *
   * @return object
   *
   * @throws OasisAPIException
   */
  public function request($method, $endpoint, $parameters = NULL, $data = NULL) {
    $path = 'http://potapld00001lb.stg-tfayd.com:9090/registration';
    if ($method == 'PUT') {
      $options = [
        'headers' => [
          'Content-Type' => 'application/json',
        ],	
        'body' => $data
      ];
      $options['proxy'] = [
        'http'  => '', // Use this proxy with "http"
        'https' => '', // Use this proxy with "https",
        'no' => ['']    // Don't use a proxy with these
      ];
      $options['verify'] = false;
      try {
        $response = $this->client->request($method, $path, $options);
        $data = $response->getBody();
        if(strtolower(substr($data, 0, 7)) == 'success') {
          return $data;
        }
        else {
          \Drupal::logger('Tibco API')->error('An error occurred while connecting Tibco API. "{message}"', array(
            'message' => $e->getMessage()));
          return $e->getMessage();
        }
        /*
        echo "<br/>Code - ".$code = $response->getStatusCode(); // 200
        echo "<br/>Reason - ".$reason = $response->getReasonPhrase(); // OK
        echo "<br/>Body - ".$body = $response->getBody();
        echo "<br/>String Body - ".$stringBody = (string) $body;
        echo "<br/>TenBytes - ".$tenBytes = $body->read(10);
        echo "<br/>RemainingBytes - ".$remainingBytes = $body->getContents();
        */
      }
      catch (RequestException $e) {
        \Drupal::logger('Tibco API')->error('An error occurred while connecting Tibco API. "{message}"', array(
          'message' => $e->getMessage()));
        return $e->getMessage();
      }
    }
    else {
      echo "Only PUT Method is applicable for POC";
    }
  }
  
  public function request_curl($method, $url, $parameters = NULL, $data = NULL) {
    $url = 'http://potapld00001lb.stg-tfayd.com:9090/registration';
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array (
    "Content-Type: application/json"
    ));
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    if ($method == 'PUT') {
      curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($ch, CURLOPT_PROXY, "");
    $result = curl_exec($ch);curl_error($ch);print_r($this->client);
    curl_close($ch);
    return ($result);
  }
}