<?php

if (null != getenv("AWS_LIBRARY_PATH")) {
    require_once(getenv("AWS_LIBRARY_PATH"));
}
require_once("AmazonS3Storage.php");   

/**
  * @group ExternalInterfaces
  */
class AmazonS3StorageTest extends AmberStorageTest {

    protected static $bucket;
    protected static $access_key;
    protected static $secret_key;

    static public function setUpBeforeClass() {
        date_default_timezone_set('UTC');
        self::$bucket = uniqid("ambertest");
    }

    private function is_environment_setup() {
        return (getenv("AWS_ACCESS_KEY_ID") && getenv("AWS_SECRET_ACCESS_KEY") && getenv("AWS_LIBRARY_PATH"));
    }

    protected function get_storage_path() {
        return "s3://" . self::$bucket;
    }

    protected function setUp() {        
    if (!$this->is_environment_setup()) {
        $this->markTestSkipped('AWS credentials not provided');
      } else {
        /* Do this to recreate the bucket */
        $storage = new AmazonS3Storage(array(
            'access_key' => getenv("AWS_ACCESS_KEY_ID"),
            'secret_key' => getenv("AWS_SECRET_ACCESS_KEY"),
            'bucket' => self::$bucket,
            'region' => 'us-east-1',
          ));
      }
    }

    protected function tearDown() { }

    public function provider() {
        if ($this->is_environment_setup()) {
            $storage = new AmazonS3Storage(array(
                'access_key' => getenv("AWS_ACCESS_KEY_ID"),
                'secret_key' => getenv("AWS_SECRET_ACCESS_KEY"),
                'bucket' => self::$bucket,
                'region' => 'us-east-1',
              ));
            $file = tmpfile();
            fwrite($file,"I am a temporary file");
            rewind($file);
            return array(array($storage, $file));
        } else {
            return array(array($this->getMockBuilder('iAmberStorage')->getMock(), ""));
        }
    }
  
}