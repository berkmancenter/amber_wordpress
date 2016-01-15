<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';

class PermaStorage implements iAmberStorage {

  public function __construct(array $options) {
  }

  public function provider_id() {
    return 1;
  }

  public function get($id) {
    throw new Exception("Not implemented for PermaStorage");
  }

  public function get_asset($id, $path) {
    throw new Exception("Not implemented for PermaStorage");    
  }

  public function build_asset_path($asset) {
    throw new Exception("Not implemented for PermaStorage");    
  }
  
  public function get_metadata($key) {
    throw new Exception("Not implemented for PermaStorage");    
  }
  
  public function get_id($url) {
    throw new Exception("Not implemented for PermaStorage");    
  }
  
  public function save($url, $root, array $headers = array(), array $assets = array()) {
    throw new Exception("Not implemented for PermaStorage");    
  }
  
  /* We do not attempt to delete Perma captures */
  public function delete_all() {
    return TRUE;
  }

  /* We do not attempt to delete Perma captures */
  public function delete($cache_metadata) {
    return TRUE;
  }

}
