<?php
require_once ("AmberChecker.php");
class AmberCheckerTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    date_default_timezone_set('UTC');
  }

  protected function tearDown() {}

  public function provider() {
    $checker = new AmberChecker();
    return array(array($checker));
  }

  /**
   * @dataProvider provider
   */
  public function testNextCheckFirst(IAmberChecker$checker) {
    $now = new DateTime();
    $result = $checker->next_check_date(null, null, 100, true);
    $this->assertTrue($result > $now->getTimeStamp(), "Get a next check date");
  }

  /**
   * @dataProvider provider
   */
  public function testNextCheckDifferent(IAmberChecker$checker) {
    $now = new DateTime();
    $result = $checker->next_check_date(false, 100, 200, true);
    $this->assertSame($now->getTimeStamp() + 24 * 60 * 60, $result);
  }

  /**
   * @dataProvider provider
   */
  public function testNextCheckSame(IAmberChecker$checker) {
    $now = new DateTime();
    $result = $checker->next_check_date(false, 1000, 2000, false);
    $this->assertSame($now->getTimeStamp() + 24 * 60 * 60 + 1000, $result);
    $now = new DateTime();
    $result = $checker->next_check_date(true, 1000, 2000, true);
    $this->assertSame($now->getTimeStamp() + 24 * 60 * 60 + 1000, $result);
  }

  /**
   * @dataProvider provider
   */
  public function testNextCheckMoreThan30(IAmberChecker$checker) {
    $now = new DateTime();
    $result = $checker->next_check_date(false, 1000, 100 + 45 * 24 * 60 * 60, false);
    $this->assertSame($now->getTimeStamp() + 24 * 60 * 60 * 30, $result);
    $now = new DateTime();
    $result = $checker->next_check_date(true, 1000, 100 + 45 * 24 * 60 * 60, true);
    $this->assertSame($now->getTimeStamp() + 24 * 60 * 60 * 30, $result);
  }
}

