<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';
require_once dirname( __FILE__ ) . '/../amber/AmberStorage.php';

class AmazonS3Storage extends AmberStorage implements iAmberStorage  {

	public function __construct($options) {
		if (!class_exists("Aws\S3\S3Client")) {
			return NULL;
		}
		$bucket = $options['bucket'];
	  	parent::__construct('s3://' . $bucket);
	  	$this->aws = new Aws\S3\S3Client(array(
			'version' => 'latest',
			'region' => $options['region'],
	  		'credentials' => array(
	  			'key' => $options['access_key'],
	  			'secret' => $options['secret_key'],
			)));
		$this->aws->registerStreamWrapper();   

		/* Create bucket for storage, if it does not already exist,
		   and confirm that we have write access to the selected bucket.
		   We suppress warning messages here, knowing that if there is
		   a problem the PutObject/DeleteObject calls will cause
		   an exception to be thrown */
		if (!$this->aws->doesBucketExist($bucket)) {
			@mkdir($this->file_root);
		}
		@$this->aws->PutObject(array("Bucket" => $bucket,
								  "Key" => "credentials_test", 
								  "Body" => "It works"));
		@$this->aws->DeleteObject(array("Bucket" => $bucket,
								     "Key" => "credentials_test"));
	}

	public function provider_id() {
	  	return 3;
	}

	/**
	* Lookup metadata for a cached item based on ID or URL
	* Overridden, since we don't need some of the checks in AmberStorage
	* to prevent access outside the cache directory are unecessary
	* @param $key string URL or an MD5 hash
	* @return array
	*/
	public function get_metadata($key) {
		/* Check if it's an ID */
		if (strlen($key) == 32 && ctype_xdigit($key)) {
		  return $this->get_cache_metadata($key);
		} else {
		  return $this->get_cache_metadata($this->url_hash($key));
	   }
	}

	/**
	* Get the metadata for a cached document as a dictionary
	* Overridden, since we don't need some of the checks in AmberStorage
	* to prevent access outside the cache directory are unecessary
	* @param string $id cached document id
	* @return array metadata
	*/
	protected function get_cache_metadata($id) {
	  $path = $this->get_cache_item_metadata_path($id);
	  if (($path === false) || !file_exists($path)) {
	    /* File does not exist. Do not log an error, since there are many cases in which this is expected */
	    return array();
	  }
	  try {
	    $file = file_get_contents($path);
	  } catch (Exception $e) {
	    error_log(join(":", array(__FILE__, __METHOD__, "Could not read file", $path)));
	    return array();
	  }
	  $result = json_decode($file,true);
	  if (null == $result) {
	    $result = array();
	    error_log(join(":", array(__FILE__, __METHOD__, "Could not parse file", $path)));
	  };
	  return $result;
	}


}
