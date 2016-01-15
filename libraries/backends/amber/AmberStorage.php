<?php

require_once dirname( __FILE__ ) . '/../../AmberInterfaces.php';

/**
 * The metadata is of the form:
 *
 * {
 *  "id" : ID,
 *  "url" : URL,
 *  "type" : MIME-TYPE,
 *  "cache":
 *   {
 *     "amber" : {
 *        "date" : "2014-02-11T10:22:46Z",
 *        "location" : "/AMBER_PREFIX/cache/ID",
 *      },
 *      [...additional cache sources go here...]
 *    }
 *  "status" :
 *   {
 *      "amber" : {
 *        "default" : "up",
 *        "IR" : "down"
 *      }.
 *      [...additional link checker results go here...]
 *    }
 * }
 *
 * TODO: Resolve distinction between this (AmberStorage) as a single implementation of the storage functionality,
 * and its role as managing references to all stored data. As designed, references to other copies of the stored
 * data are mixed in with the metadata for THIS copy of the stored data (on disk, locally).
 *
 *
 */

class AmberStorage implements iAmberStorage {

  /* The default ISO8601 date string formatter doesn't include the colons in the time-zone component, which
     is incompatible with javascript's date.parse() function in at least some implementations (Safari, definitely) */
  var $ISO8601_FORMAT = 'Y-m-d\TH:i:sP';

  function __construct($file_root = '/private/tmp/amber/cache') {
    $this->file_root = $file_root;
    $this->url_prefix = 'amber';
    $this->name = 'amber'; // Used to identify the metadata that belongs to this implementation of iAmberStorage
  }

  public function provider_id() {
    return 0;
  }

  public function get($id) {
    $result = NULL;
    if ($path = $this->get_cache_item_path($id)) {
      if (file_exists($path)) {
        $result = file_get_contents($path);
      }
    }
    return $result;
  }

  public function get_asset($id, $path) {
    $result = NULL;
    if ($path = $this->get_cache_item_path($id, $path)) {
      if (file_exists($path)) {
        $result = file_get_contents($path);
      }
    }
    return $result;
  }

  /**
   * Define the relative path for the asset, based on the asset filename, body, and
   * headers provided.  
   */
  public function build_asset_path($asset) {
    $url = md5($asset['url']);
    $ext = "";
    /* Add .css extension if Content-Type is text/css */
    $ct = isset($asset['headers']['Content-Type']) ? $asset['headers']['Content-Type'] : "";
    if ((strpos($ct,'text/css') !== FALSE)) {
      $ext = ".css";
    } else {
      $extension_candidate = substr($asset['url'], strrpos($asset['url'], '.'));
      /* Additional heuristic to see if this is really an extension that the browser needs to parse the html 
         Could also create a mapping of content-types to filename extensions */

      if (strrpos($extension_candidate, '?') !== FALSE) {
        $extension_candidate = substr($extension_candidate, 0, strrpos($extension_candidate, '?'));
      }
      if ((strlen($extension_candidate) < 5) && (substr($extension_candidate,-1,1) != "/"))
        $ext = $extension_candidate;      
    }
    return $url . $ext;
  }

  /**
   * Lookup metadata for a cached item based on ID or URL
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

  public function get_id($url) {
    return $this->url_hash($url);
  }

  /**
   * Save a file to the cache
   * @param $url string original location of the file that we're saving
   * @param $root resource the file to be saved
   * @param array $headers HTTP headers returned along with the original file
   * @param array $assets any additional assets that should be saved (e.g. CSS, javascript)
   * @return bool success or failure
   */
  function save($url, $root, array $headers = array(), array $assets = array()) {
    $id = $this->url_hash($url);
    $cache_metadata = $this->get_cache_metadata($id);
    $dir = join(DIRECTORY_SEPARATOR, array($this->file_root, $id));
    if (empty($cache_metadata)) {
      if (!file_exists($dir)) {
        if (!mkdir($dir, 0777, true)) {
          error_log(join(":", array(__FILE__, __METHOD__, "Could not create directory for saving file", $dir)));
          return false;
        }
      }
      $cache_metadata = array(
        'id' => $id,
        'url' => $url,
        'type' => isset($headers['Content-Type']) ? $headers['Content-Type'] : 'application/octet-stream',
        'cache' => array (
          $this->name => array()
        )
      );
    }

    $cache_metadata['cache'][$this->name]['date'] = date($this->ISO8601_FORMAT);
    $cache_metadata['cache'][$this->name]['location'] = join("/", array($this->url_prefix, 'cache', $id, ""));
    $cache_metadata['status'][$this->name]['default'] = "up";

    // Save metadata
    $this->save_cache_metadata($id, $cache_metadata);

    // Save root file
    $filename = join(DIRECTORY_SEPARATOR,array($dir,$id));
    if (file_put_contents($filename, $root) === FALSE) {
      error_log(join(":", array(__FILE__, __METHOD__, "Could not save cache file", $dir)));
      return false;
    }

    if (!empty($assets)) {
      $this->save_assets($id, $assets);
    }

    return true;
  }


  /**
   *  Delete the entire contents of the cache
   */
  public function delete_all() {
    if ($this->file_root) {
      $this->rrmdir($this->file_root);
    }
    return TRUE;
  }

  public function delete($cache_metadata) {
    $id = $cache_metadata['id'];
    $path = $this->get_cache_item_path($id);
    if ($path) {
      $dir = dirname($path);
      if ($this->is_within_cache_directory($dir)) {
        $this->rrmdir($dir);
      }
    }
    return TRUE;
  }

  /**
   * Return an MD5 hash for a normalized form of the URL to be used as a cached document id
   * @param string $url to be hashed
   * @return string MD5 hash of the url
   */
  protected function url_hash($url) {
    //TODO: Normalize URLs (consider: https://github.com/glenscott/url-normalizer)
    return md5($url);
  }

  /**
   * Validate that a path points to a file within our cache directory. Used to ensure that calls to this module
   * cannot retrieve arbitrary files from the file system.
   * If the file does not exist, return TRUE
   * @param $path string to be validated
   * @return bool
   */
  private function is_within_cache_directory($path) {
    if (!realpath($path)) {
      // File does not exist.
      return TRUE;
    }
    if (strpos(realpath($path),realpath($this->file_root)) !== 0) {
      /* File is outside root directory for cache files */
      error_log(join(":", array(__FILE__, __METHOD__, "Attempt to access file outside file root", realpath($path), realpath($this->file_root))));
      return FALSE;
    } else {
      return TRUE;
    }
  }

  /**
   * Get the path to the metadata for a cached item
   * @param $id string
   * @return string path to the file that contains the metadata
   */
  protected function get_cache_item_metadata_path($id) {
    $path = join(DIRECTORY_SEPARATOR, array($this->file_root, $id, "${id}.json"));
    return ($this->is_within_cache_directory($path)) ? $path : NULL;
  }

  /**
   * Get the path to the root cached item or asset
   * @param $id string
   * @param $asset_path string with the path to the asset
   * @return string path to the file that contains the root cached item
   */
  protected function get_cache_item_path($id, $asset_path = NULL) {
    if ($asset_path) {
      $path = join(DIRECTORY_SEPARATOR, array_merge(array($this->file_root, $id, "assets"), explode('/',$asset_path)));
    } else {
      $path = join(DIRECTORY_SEPARATOR, array($this->file_root, $id, $id));
    }
    return ($this->is_within_cache_directory($path)) ? $path : NULL;
  }

  /**
   * Get the metadata for a cached document as a dictionary
   * @param string $id cached document id
   * @return array metadata
   */
  protected function get_cache_metadata($id) {
    $path = realpath($this->get_cache_item_metadata_path($id));
    if (($path === false) || !file_exists($path)) {
      /* File does not exist. Do not log an error, since there are many cases in which this is expected */
      return array();
    }
    if (!$this->is_within_cache_directory($path)) {
      /* File is outside root directory for cache files */
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

  /**
   * Save metadata for a cached item
   * @param $id string of the item
   * @param $metadata array with the metadata to save
   * @return bool
   */
  protected function save_cache_metadata($id, $metadata) {
    $path = $this->get_cache_item_metadata_path($id);
    $file = fopen($path,'w');
    if (!$file) {
      error_log(join(":", array(__FILE__, __METHOD__, "Could not open metadata file for saving", $path)));
      return false;
    }
    // JSON_UNESCAPED_SLASHES is only defined if PHP >= 5.4
    if (fwrite($file,json_encode($metadata, defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0)) === FALSE) {
      error_log(join(":", array(__FILE__, __METHOD__, "Could not write metadata file", $path)));
      return false;
    }
    if (!fclose($file)) {
      error_log(join(":", array(__FILE__, __METHOD__, "Could not close metadata file", $path)));
      return false;
    }
    return true;
  }

  /**
   * Save a list of assets for an ID to a directory within the cache file system. Filename of the asset
   * is an MD5 hash of the URL, with the file extension added back
   * @param $id string of the item for which the assets are being saved
   * @param array $assets with the url and an open resource for each asset to be saved
   * @return bool
   */
  private function save_assets($id, array $assets) {
    $base_asset_path = join(DIRECTORY_SEPARATOR, array($this->file_root, $id, "assets"));
    if (!$this->is_within_cache_directory($base_asset_path)) {
      return false;
    }
    foreach ($assets as $asset) {
      if (empty($asset['url'])) {
        error_log(join(":", array(__FILE__, __METHOD__, "Could not save asset with no URL specified", $id)));
        continue;
      }
      $asset_path = join(DIRECTORY_SEPARATOR,array($base_asset_path, $this->build_asset_path($asset)));
      if (empty($asset_path)) {
        error_log(join(":", array(__FILE__, __METHOD__, "Could not generate asset path to save asset ", $id, $asset['url'])));
        continue;
      }
      if (!file_exists(dirname($asset_path))) {
        if (!mkdir(dirname($asset_path), 0777, true)) {
          error_log(join(":", array(__FILE__, __METHOD__, "Could not create asset directory for asset", $asset_path, $asset['url'])));
          continue;
        }
      }
      if (empty($asset['body'])) {
        error_log(join(":", array(__FILE__, __METHOD__, "Could not save asset with empty content", $id, $asset_path, $asset['url'])));
        continue;
      }
      if (file_put_contents($asset_path, $asset['body']) === FALSE) {
        error_log(join(":", array(__FILE__, __METHOD__, "Could not save asset", $id, $asset_path, $asset['url'])));
        continue;
      }
    }
    return true;
  }



  /**
   * Recursively delete a directory
   * Credit: http://stackoverflow.com/a/3338133
   * @param $dir
   * @param $delete_dir boolean whether to delete the top-level directory, as opposed to just all files and directories
   *        within it
   */
  private function rrmdir($dir, $delete_dir = TRUE) {
     if (is_dir($dir)) {
       $objects = scandir($dir);
       foreach ($objects as $object) {
         if ($object != "." && $object != "..") {
           if (filetype($dir."/".$object) == "dir") $this->rrmdir($dir."/".$object); else unlink($dir."/".$object);
         }
       }
       reset($objects);
       if ($delete_dir)
        rmdir($dir);
     }
   }

}
