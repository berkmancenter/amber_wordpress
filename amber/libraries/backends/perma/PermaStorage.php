<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';

class PermaStorage implements iAmberStorage {

  function __construct(array $options) {
  }

  function get($id) {
  	throw new Exception("Not implemented for PermaStorage");
  }

  function get_asset($id, $path) {
  	throw new Exception("Not implemented for PermaStorage");  	
  }

  function get_metadata($key) {
  	throw new Exception("Not implemented for PermaStorage");  	
  }
  
  function get_id($url) {
  	throw new Exception("Not implemented for PermaStorage");  	
  }
  
  function save($url, $root, array $headers = array(), array $assets = array()) {
  	throw new Exception("Not implemented for PermaStorage");  	
  }
  
  /* We do not attempt to delete Perma captures */
  function delete_all() {
  	return TRUE;
  }

  /* We do not attempt to delete Perma captures */
  function delete($cache_metadata) {
    return TRUE;
  }

}
