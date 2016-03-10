<?php

require_once("AmberStorage.php");

class AmberStorageTest extends PHPUnit_Framework_TestCase {

  protected function setUp() {
    date_default_timezone_set('UTC');
  }

  protected function tearDown() {
    $storage = new AmberStorage($this->get_storage_path());
    $storage->delete_all();
  }

  public function provider() {
    $storage_path = $this->get_storage_path();
    if (!file_exists($storage_path))
      mkdir($storage_path,0777);

    $storage = new AmberStorage($storage_path);
    $file = tmpfile();
    fwrite($file,"I am a temporary file");
    rewind($file);
    return array(array($storage, $file));
  }

  /**
   * @dataProvider provider
   */
  public function testLookupURL(IAmberStorage $storage, $file) {
    $storage->save("www.example.com",$file);
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(isset($metadata['cache']['amber']['date']));
    $this->assertTrue(isset($metadata['cache']['amber']['location']));

  }

  /**
   * @dataProvider provider
   */
  public function testLookupBogusURL(IAmberStorage $storage, $file) {
    $storage->save("www.example.com",$file);
    $metadata = $storage->get_metadata("www.pancakes.com");
    $this->assertTrue(empty($metadata));
  }

  /**
   * @dataProvider provider
   */
  public function testSaveTwice(IAmberStorage $storage, $file) {
    $storage->save("www.example.com",$file);
    $metadata = $storage->get_metadata("www.example.com");
    rewind($file);
    $storage->save("www.example.com",$file);
    $metadata2 = $storage->get_metadata("www.example.com");
    $this->assertTrue($metadata2['cache']['amber']['date'] >= $metadata['cache']['amber']['date']);
    $this->assertTrue($metadata2['cache']['amber']['location'] == $metadata['cache']['amber']['location']);
  }

  /**
   * @dataProvider provider
   */
  public function testRetrieve(iAmberStorage $storage, $file) {
    $storage->save("www.example.com",$file);
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertFalse(empty($metadata['id']));
    $data = $storage->get($metadata['id']);
    $this->assertSame($data,"I am a temporary file");
  }

  /**
   * @dataProvider provider
   */
  public function testBogusRetrieve(iAmberStorage $storage, $file) {
    $data = $storage->get("xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
    $this->assertNull($data);
  }

  /**
   * @dataProvider provider
   */
  public function testClearCache(iAmberStorage $storage, $file) {
    $storage->save("www.example.com",$file);
    $storage->delete_all();
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(empty($metadata));
  }

  /**
   * @dataProvider provider
   */
  public function testSaveNoAssets(iAmberStorage $storage, $file) {
    $storage->save("www.example.com", $file, array(), array());
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(isset($metadata['cache']['amber']['location']) && $metadata['cache']['amber']['location']);
    $path = join(DIRECTORY_SEPARATOR,array($this->get_storage_path(),$metadata['id']));
    $this->assertTrue(file_exists($this->get_storage_path()));
    $this->assertTrue(file_exists($path));
  }

  /**
   * @dataProvider provider
   */
  public function testSaveOneAsset(iAmberStorage $storage, $file) {
    $assets = array(array('url' => 'http://www.example.com/man/is/free.jpg', 'body' => "I am a temporary file"));

    $storage->save("www.example.com", "I am a temporary file", array(), $assets);
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(isset($metadata['cache']['amber']['location']) && $metadata['cache']['amber']['location']);
    $path = join(DIRECTORY_SEPARATOR,array($this->get_storage_path(),$metadata['id']));
    $this->assertTrue(file_exists($path));
    $this->assertTrue(file_exists(join(DIRECTORY_SEPARATOR,array($path,'assets',$storage->build_asset_path($assets[0])))));
  }

  /**
   * @dataProvider provider
   */
  public function testSaveOneAssetQueryStringName(iAmberStorage $storage, $file) {
    $assets = array(array('url' => 'http://www.example.com/man/is/free/?t=js&amp;bv=&amp;os=&amp;tz=&amp;lg=&amp;rv=&amp;rsv=&amp;pw=%2F&amp;cb=1438832272', 'body' => "I am a man"));

    $storage->save("www.example.com", "yayayaya", array(), $assets);
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(isset($metadata['cache']['amber']['location']) && $metadata['cache']['amber']['location']);
    $path = join(DIRECTORY_SEPARATOR,array($this->get_storage_path(),$metadata['id']));
    $this->assertTrue(file_exists($path));
    $this->assertTrue(file_exists(join(DIRECTORY_SEPARATOR,array($path,'assets',$storage->build_asset_path($assets[0])))));
  }


  /**
   * @dataProvider provider
   */
  public function testSaveOneAssetQueryStringNameTwo(iAmberStorage $storage, $file) {
    $assets = array(array('url' => 'http://www.example.com/traffic/?t=px&bv=JavaScript+Disabled&os=&tz=default&lg=&rv=&rsv=&pw=%2F&cb=1382655937', 'body' => $file));

    $storage->save("www.example.com", "oogle boogle", array(), $assets);
    $metadata = $storage->get_metadata("www.example.com");
    $this->assertTrue(isset($metadata['cache']['amber']['location']) && $metadata['cache']['amber']['location']);
    $path = join(DIRECTORY_SEPARATOR,array($this->get_storage_path(),$metadata['id']));
    $this->assertTrue(file_exists($path));
    $this->assertTrue(file_exists(join(DIRECTORY_SEPARATOR,array($path,'assets',$storage->build_asset_path($assets[0])))));

  }

  /**
   * @dataProvider provider
   */
  public function testAssetPathExtension(iAmberStorage $storage, $file) {
    $path = "/foo/bar/logo.svg";
    $hash = md5($path);
    $asset = array("url" => $path, 'headers' => array('Content-Type' => 'text/css'));
    $url = $storage->build_asset_path($asset);
    $this->assertSame($hash . ".css",$url);
  }

  /**
   * @dataProvider provider
   */
  public function testAssetPathExtension2(iAmberStorage $storage, $file) {
    $path = "https://pages.github.com/css/../images/download@2x.png";
    $hash = md5($path);
    $asset = array("url" => $path);
    $url = $storage->build_asset_path($asset);
    $this->assertSame($hash . ".png",$url);
  }

  protected function get_storage_path() {
    return join(DIRECTORY_SEPARATOR,array(realpath(sys_get_temp_dir()),"amber"));
  }
}
 