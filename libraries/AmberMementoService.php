<?php

interface iAmberMementoService {
  public function getMemento($url, $date);
}

class AmberMementoService implements iAmberMementoService {

	public function __construct(array $options) {
		$this->serverUrl = isset($options['server_url']) ? $options['server_url'] : 'http://timetravel.mementoweb.org/timegate/';
		$this->customTimegate = isset($options['custom_timegate']) ? $options['custom_timegate'] : false;
		if($this->customTimegate) {
			if(isset($options['token'])) {
				$this->token = $options['token'];
			}
			else {
				$this->serverUrl = rtrim($this->serverUrl, '/');
				$registrations = $options['hash'];
				$registrations = json_decode($registrations, true);
				$this->token = '';
				if (array_key_exists($this->serverUrl, $registrations)) {
					$this->token = $registrations[$this->serverUrl];
				}
			}
			$this->siteUrl = $options['site_url'];
			if (strpos($this->siteUrl, 'http') < 0) {
				$this->siteUrl = "http://".$this->siteUrl;
			}
		}
	}

	private function getArchiveDate($url) {
		$path = parse_url($url, PHP_URL_PATH);
		if ((preg_match("/\d{14}/", $path, $matches) === 1) && (count($matches) === 1)) {
			// $date_string = date_parse_from_format("YmdGis", $matches[0]);
			/* The default ISO8601 date string formatter doesn't include the colons in the time-zone component, which
			   is incompatible with javascript's date.parse() function in at least some implementations (Safari, definitely).
			   So, roll our own here. */
  			$dt = DateTime::createFromFormat('YmdGis', $matches[0]);
  			return $dt->format('Y-m-d\TH:i:s+00:00');
		} else {
			return "";
		}
	}

	/**
	 * Query the Timegate server for a memento for this URL and date
	 * @param  string $url    URL to query
	 * @param  string $date   preferred date for the memento
	 * @return string 		  JSON structure with memento location and date (if any)
	 */
	public function getMemento($url, $date) {

		$header = array('Accept-Datetime: ' . gmdate(DATE_RFC1123, strtotime($date)));
		$options = array(
			CURLOPT_NOBODY => true, // Just doing a HEAD request
			CURLOPT_HTTPHEADER => $header,
		);
		$query_url = $this->serverUrl;
		if($this->customTimegate) {
			$query_url = rtrim($this->serverUrl, '/') . '/timegate';
		}

		/* Be forgiving of trailing slashes (or lack thereof) in server URL */
		$query_url = implode("/",array(trim($query_url,"/"), $url));

		$result = AmberNetworkUtils::open_single_url($query_url, $options, FALSE);

		if (($result !== FALSE) && isset($result['headers']['Location'])) {
			$url = $result['headers']['Location'];			
			return array(
				'url' => $url,
				'date' => $this->getArchiveDate($url),
				);
		} else {
			return false;
		}
	}

	private function makeAuthenticatedRequest($path, $fields = array()) {
		$fields['timestamp'] = time();
		$fields['verifier'] = sha1($fields['timestamp'] . $this->token);
		$fields['origin'] = $this->siteUrl;
		return AmberNetworkUtils::make_post_call($this->serverUrl . $path, $fields);
	}

	public function registerSite($url, $email, $hash) {
		if(!$this->customTimegate) {
			return false;
		}
		$url = rtrim($url, '/');
		if (strpos($url, 'http') < 0) {
			$url = "http://".$url;
		}
		if($this->token != '') {
			$success = true;
			$message = '';
		}
		else {
			$fields = array(
				'node_url' => $url,
				'contact_email' => $email
			);
			list($header, $response) = AmberNetworkUtils::make_post_call($this->serverUrl . '/register', $fields);
			if ($header['http_code'] == 200 || $header['http_code'] == 412) {
				$success = $response->success;
				$message = $response->message;
				if ($response->success) {
					// TODO: Multiple custom backends in future can be used, by making below deregister user configurable.
					$registrations = $hash;
					$registrations = json_decode($registrations, true);
					foreach($registrations as $url => $token) {
						$temp_server = new AmberMementoService(array(
							'server_url' => $url,
							'custom_timegate' => true,
							'token' => $token
						));
						$temp_server->deRegisterSite();
						unset($registrations[$url]);
					}
					$registrations[$this->serverUrl] = $response->data->hash;
					$this->token = $response->data->hash;
					$hash = json_encode($registrations);
				}
			} else {
				$success = false;
				$message = 'Couldn\'t connect to custom timegate URL provided. Please recheck it\'s value.';
			}
		}
		return array($success, $message, $hash);
	}

	public function deRegisterSite() {
		if(!$this->customTimegate) {
			return false;
		}
		if($this->token == '') {
			return true;
		}
		$response = $this->makeAuthenticatedRequest('/deregister');
		return $response;
	}

	public function notifyAddToTimegate($url, $cache_id, $timestamp) {
		if(!$this->customTimegate) {
			return false;
		}
		$fields = [
			"url"		=> $url,
			"cache_id"	=> $cache_id,
			"timestamp"	=> $timestamp
		];
		$response = $this->makeAuthenticatedRequest('/add', $fields);
		return $response;
	}

	public function notifyRemoveToTimegate($cache_id) {
		if(!$this->customTimegate) {
			return false;
		}
		$fields = [
			"cache_id" => $cache_id
		];
		$response = $this->makeAuthenticatedRequest('/remove', $fields);
		return $response;
	}

	public function notifyRemoveAllToTimegate() {
		if(!$this->customTimegate) {
			return false;
		}
		$response = $this->makeAuthenticatedRequest('/removeall');
		return $response;
	}

}