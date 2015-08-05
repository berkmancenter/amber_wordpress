<?php

require_once("InternetArchiveFetcher.php");
require_once("InternetArchiveStorage.php");

class InternetArchiveFetcherTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	  date_default_timezone_set('UTC');
	}

	/**
	 * @group ExternalInterfaces
	 */
	public function testBasicFetch()
	{
		$fetcher = new InternetArchiveFetcher(new InternetArchiveStorage(array()), array());	  
		$result = $fetcher->fetch("http://www.google.com");
		$this->assertEquals($result['url'],"http://www.google.com");
		$this->assertNotEmpty($result['id']);
		$this->assertNotEmpty($result['date']);
		$this->assertNotEmpty($result['location']);
	}

}