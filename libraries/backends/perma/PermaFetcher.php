<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';
require_once dirname( __FILE__ ) . '/../../AmberNetworkUtils.php';

class PermaFetcher implements iAmberFetcher {

  function __construct(iAmberStorage $storage, array $options) {
    $this->apiKey = isset($options['perma_api_key']) ? $options['perma_api_key'] : "";
    $this->apiUrl = isset($options['perma_api_url']) ? $options['perma_api_url'] : "https://api.perma.cc";
    $this->archiveUrl = isset($options['perma_archive_url']) ? $options['perma_archive_url'] : "https://perma.cc";
  }

  /**
   * Fetch the URL and associated assets and pass it on to the designated Storage service
   * @param $url
   * @return
   */
	public function fetch($url) {
    if (!$url) {
      throw new RuntimeException("Empty URL");
    }
    if (!$this->apiKey) {
      throw new InvalidArgumentException("Missing required API key for accessing Perma");      
    }

    $api_endpoint = $this->apiUrl . '/v1/archives/?api_key=' . $this->apiKey;

    $curl_options = array(
      CURLOPT_POST => TRUE,
      CURLOPT_POSTFIELDS => json_encode(array('url' => $url)),
      CURLOPT_HTTPHEADER => array("Content-type: application/json"),
      CURLOPT_FOLLOWLOCATION => TRUE,
    );

    $perma_result = AmberNetworkUtils::open_single_url($api_endpoint, $curl_options);

    /* Make sure that we got a valid response from Perma */
    if (($perma_result === FALSE) || ($perma_result['body'] === FALSE)) {
      $message = "";
      if (isset($perma_result['info']['http_code'])) {
        $message = "HTTP response code=" . $perma_result['info']['http_code'];
      }
      throw new RuntimeException(join(":",array("Error submitting URL to Perma", $message)));
    }

    $json_result = json_decode($perma_result['body'], true);
    if (!isset($json_result['guid'])) {
      throw new RuntimeException("Perma response did not include GUID");  
    }
    $result = array (
        'id' => md5($json_result['url']),
        'url' => $json_result['url'],
        'type' => '',
        'date' => strtotime($json_result['creation_timestamp']),
        'location' => join("/", array($this->archiveUrl, $json_result['guid'])),
        'size' => 0,
        'provider' => 1, /* Perma */
        'provider_id' => $json_result['guid'],
      );
    return $result;
	}

}