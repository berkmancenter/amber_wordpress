<?php

interface iAmberFetcher {
  /**
   * Fetch the URL and associated assets and save them with the selected storage service
   * Returns an array of metadata associated with the object:
   *   - id : Amber ID for the object
   *   - url : URL that was saved
   *   - type : Content-type for the URL that was saved (from http header)
   *   - date : Date/time the URL was saved
   *   - location : URL that can be used to retrieve the cached content
   *   - size : Size in bytes of the cached content
   *   - provider : ID of the provider where the cached content was saved
   *   - provider_id : ID of the cached content at that provider
   */
  public function fetch($url);
}

interface iAmberStorage {
  function provider_id();
  function get($id);
  function get_asset($id, $path);
  function build_asset_path($asset);
  function get_metadata($key);
  function get_id($url);
  function save($url, $root, array $headers = array(), array $assets = array());
  function delete_all();
  function delete($cache_metadata);
}

