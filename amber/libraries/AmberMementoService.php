<?php

interface iAmberMementoService {
  public function getMemento($url, $date);
}

class AmberMementoService implements iAmberMementoService {

	public function __construct(array $options) {
	    $this->serverUrl = isset($options['server_url']) ? $options['server_url'] : 'http://timetravel.mementoweb.org/timegate/';
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

		/* Be forgiving of trailing slashes (or lack thereof) in server URL */
		$query_url = implode("/",array(trim($this->serverUrl,"/"), $url));

		$result = AmberNetworkUtils::open_single_url($query_url, $options, FALSE);

		if (($result !== FALSE) && isset($result['headers']['Location'])) {
			return array(
				'url' => $result['headers']['Location'],
				);
		} else {
			return array();
		}
	}
}