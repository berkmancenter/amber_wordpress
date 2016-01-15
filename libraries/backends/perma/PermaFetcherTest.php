<?php

require_once("PermaFetcher.php");
require_once("PermaStorage.php");

class PermaAmberFetcherTest extends \PHPUnit_Framework_TestCase {

	protected function setUp() {
	  date_default_timezone_set('UTC');
	}

    /**
     * @expectedException        InvalidArgumentException
     * @expectedExceptionMessage Missing required API key for accessing Perma
     */
	public function testBadConfiguration()
	{
		$fetcher = new PermaFetcher(new PermaStorage(array()), array());
		$result = $fetcher->fetch("http://www.google.com");
	}

    /**
     * @expectedException        RuntimeException
     * @expectedExceptionMessage Empty URL
     */
	public function testMissingURL()
	{
		$fetcher = new PermaFetcher(new PermaStorage(array()), array());
		$result = $fetcher->fetch("");
	}

    /**
     * @expectedException        RuntimeException
	 * @group ExternalInterfaces
     */
	public function testBadAPIKeyTriggersPHPError()
	{
		$fetcher = new PermaFetcher(new PermaStorage(array()), array('perma_api_key' => "bogus"));
		$result = $fetcher->fetch("http://www.google.com");
	}

    /**
	 * @group ExternalInterfaces
     */
	public function testGoodAPIKeyReturnsExpectedResponse()
	{
		$apikey = getenv("PERMA_API_KEY");
		if ($apikey) {			
			$fetcher = new PermaFetcher(new PermaStorage(array()), array('perma_api_key' => $apikey));	  
			$result = $fetcher->fetch("http://www.google.com");
			$this->assertEquals($result['url'],"http://www.google.com");
			$this->assertNotEmpty($result['id']);
			$this->assertNotEmpty($result['date']);
			$this->assertNotEmpty($result['location']);
		} else {
			$this->markTestSkipped('No Perma API key provided for testing use of valid API key');
		}
	}

}
