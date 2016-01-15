<?php

interface iAmberAvailability {
  public function get_status(array $urls, $country);
  public function report_status($url, $data);
}

class AmberNetClerkAvailability implements iAmberAvailability {

	public function __construct(array $options) {
	    $this->serverUrl = isset($options['server_url']) ? $options['server_url'] : 'http://netclerk.dev.berkmancenter.org/laapi';
	}

	public function get_status(array $urls, $country) {
		$response = $this->query_status_from_netclerk($urls, $country);
		return $this->parseResponse($response);		
	}

	/**
	 * Report the results of a status check we performed to NetClerk
	 * @param  string $url  URL being checked
	 * @param  array $data  results from the HTTP request that checked the URL
	 * @return None
	 */
	public function report_status($url, $data) {

		$fields = array(
			'data' => array(
				'type' => 'requests',
				'attributes' => array(
					'url' => $url,
					'dns_ip' => "",
					'request_ip' => $data['info']['primary_ip'],
					'request_headers' => isset($data['info']['request_header']) ? $data['info']['request_header'] : "",
					'redirect_headers' => "",
					'response_status' => $data['info']['http_code'],
					'response_headers_time' => $data['info']['starttransfer_time'] * 1000,
					'response_headers' => $this->stringify_headers($data['headers']),
					'response_content_time' => $data['info']['total_time'] * 1000,
					'response_content' => $data['body'],
					))
			);		
		$fields_string = http_build_query($fields);
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $fields_string,
			// CURLOPT_PROXY => 'localhost:8889',
			// CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
		);
		AmberNetworkUtils::open_single_url($this->serverUrl . "/requests", $options);		
	}

	/**
	 * Query the NetClerk server for the status of the URLs in a particular country
	 * @param  array  $urls    array of URLs to query
	 * @param  string $country two-character ISO code for the user's country
	 * @return string 			body of the response from the NetClerk server
	 */
	public function query_status_from_netclerk(array $urls, $country) {

		$fields = array(
			'country' => $country,
			'url' => $urls
			);

		$fields_string = http_build_query($fields);
		/* http_build_query represents arrays as "urls[0]=foo&urls[1]=bar", 
		   but we need "urls[]=foo&urls[]=bar" */
		$fields_string = preg_replace('/%5B[0-9]+%5D/', '%5B%5D', $fields_string);
		$options = array(
			CURLOPT_POST => true,
			CURLOPT_POSTFIELDS => $fields_string,
          // CURLOPT_PROXY => 'localhost:8889',
          // CURLOPT_PROXYTYPE => CURLPROXY_SOCKS5,
		);
		$result = AmberNetworkUtils::open_single_url($this->serverUrl . "/statuses", $options);

		if (($result !== FALSE) && isset($result['body'])) {
			return $result['body'];
		} else {
			return FALSE;
		}
	}

	/**
	 * Parse the response from the NetClerk server and extract data to be passed 
	 * to the client-side code that's querying the web server for the availability 
	 * information 
	 * @param  string $response JSON string returned from NetClerk
	 * @return string 			object array with URLs and availability status
	 */
	public function parseResponse($response) {
		$result = array('data' => array());
		if (is_string($response)) {
			if (($data = json_decode($response)) != NULL) {
				$statuses = $data->data;
				foreach ($statuses as $key => $value) {
					if ($value->type == 'statuses') {
						$result['data'][] = array(
							'url' => $value->attributes->url,
							'available' => $value->attributes->available,
							);
					}
				}
			}
		} 
		return $result;
	}

	private function stringify_headers($data) {
		$headers = array();
		foreach ($data as $key => $value) {
			$headers[] = $key . ": " . $value;
		}
		return implode("\r\n", $headers);
	}

}	

