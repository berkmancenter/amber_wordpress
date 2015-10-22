<?php
require_once ("AmberNetworkUtils.php");
require_once ("AmberAvailability.php");
class AmberNetClerkAvailabilityTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    date_default_timezone_set('UTC');
  }

  public function provider() {
    $lookup = new AmberNetClerkAvailability(array());
    return array(array($lookup));
  }

  /**
   * @dataProvider provider
   */
  public function testParseBadResult(iAmberAvailability $lookup) {
    $result = $lookup->parseResponse(FALSE);
    $this->assertEquals(array('data' => array()), $result);
  }

  /**
   * @dataProvider provider
   */
  public function testSingleURLResult(iAmberAvailability $lookup) {
    // Sample result from querying http://netclerk.dev.berkmancenter.org/laapi/statuses?country=BR&url=https://twitter.com
    $result = $lookup->parseResponse('{"data":[{"type":"statuses","id":"1210908","attributes":{"url":"https://twitter.com","country":"BR","code":200,"probability":1.0,"available":true,"created":"2015-09-07T00:00:00.000Z"}}]}');
    $this->assertEquals(1, sizeof($result['data']));
    $this->assertEquals("https://twitter.com", $result['data'][0]['url']);
    $this->assertTrue($result['data'][0]['available']);
  }

  /**
   * @dataProvider provider
   */
  public function testMultipleURLResult(iAmberAvailability $lookup) {
    // Sample result from querying http://netclerk.dev.berkmancenter.org/laapi/statuses?country=BR&url[]=http://www.bbc.co.uk/news&url[]=https://twitter.com
    $result = $lookup->parseResponse('{"data":[{"type":"statuses","id":"1210908","attributes":{"url":"https://twitter.com","country":"BR","code":200,"probability":1.0,"available":true,"created":"2015-09-07T00:00:00.000Z"}},{"type":"statuses","id":"1211092","attributes":{"url":"http://www.bbc.co.uk/news","country":"BR","code":200,"probability":0.0,"available":false,"created":"2015-09-07T00:00:00.000Z"}}]}');
    $this->assertEquals(2, sizeof($result['data']));
    $this->assertEquals("https://twitter.com", $result['data'][0]['url']);
    $this->assertTrue($result['data'][0]['available']);
    $this->assertEquals("http://www.bbc.co.uk/news", $result['data'][1]['url']);
    $this->assertFalse($result['data'][1]['available']);
  }


}

