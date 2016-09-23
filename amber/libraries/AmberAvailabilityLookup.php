<?php

interface iAmberAvailabilityLookup {
  public function getStatus(array $urls, $country);
}

class AmberNetClerkAvailabilityLookup implements iAmberAvailabilityLookup {

	public function __construct(array $options) {
	    $this->serverUrl = isset($options['server_url']) ? $options['server_url'] : 'http://netclerk.dev.berkmancenter.org/laapi';
	}

	public function getStatus(array $urls, $country) {
		$response = $this->queryStatusFromNetClerk($urls, $country);
		return $this->parseResponse($response);		
	}

	/**
	 * Query the NetClerk server for the status of the URLs in a particular country
	 * @param  array  $urls    array of URLs to query
	 * @param  string $country two-character ISO code for the user's country
	 * @return string 			body of the response from the NetClerk server
	 */
	public function queryStatusFromNetClerk(array $urls, $country) {

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
		$result = AmberNetworkUtils::open_single_url($this->serverUrl, $options);

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


}	

