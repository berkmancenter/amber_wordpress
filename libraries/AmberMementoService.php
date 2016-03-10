<?php

interface iAmberMementoService {
  public function getMemento($url, $date);
}

class AmberMementoService implements iAmberMementoService {

	public function __construct(array $options) {
	    $this->serverUrl = isset($options['server_url']) ? $options['server_url'] : 'http://timetravel.mementoweb.org/timegate/';
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

		/* Be forgiving of trailing slashes (or lack thereof) in server URL */
		$query_url = implode("/",array(trim($this->serverUrl,"/"), $url));

		$result = AmberNetworkUtils::open_single_url($query_url, $options, FALSE);

		if (($result !== FALSE) && isset($result['headers']['Location'])) {
			$url = $result['headers']['Location'];			
			return array(
				'url' => $url,
				'date' => $this->getArchiveDate($url),
				);
		} else {
			return array();
		}
	}


}