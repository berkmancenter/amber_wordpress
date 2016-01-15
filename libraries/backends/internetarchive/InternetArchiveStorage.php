<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';

class InternetArchiveStorage implements iAmberStorage {

  public function __construct(array $options) {
  }

  public function provider_id() {
    return 2;
  }

  public function get($id) {
  	throw new Exception("Not implemented for InternetArchiveStorage");
  }

  public function get_asset($id, $path) {
  	throw new Exception("Not implemented for InternetArchiveStorage");  	
  }

  public function build_asset_path($asset) {
    throw new Exception("Not implemented for InternetArchiveStorage");    
  }
  
  public function get_metadata($key) {
  	throw new Exception("Not implemented for InternetArchiveStorage");  	
  }
  
  public function get_id($url) {
  	throw new Exception("Not implemented for InternetArchiveStorage");  	
  }
  
  public function save($url, $root, array $headers = array(), array $assets = array()) {
  	throw new Exception("Not implemented for InternetArchiveStorage");  	
  }
  
  /* We cannot delete Internet Archive captures */
  public function delete_all() {
    return TRUE;
  }

  /* We cannot delete Internet Archive captures */
  public function delete($cache_metadata) {
    return TRUE;
  }

}
