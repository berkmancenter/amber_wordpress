<?php

require_once 'AmberDB.php';

interface iAmberStatus {
  public function get_check($url);
  public function has_cache($url);
  public function get_cache_by_id($id, $provider_types);
  public function get_summary($url, $preferred_providers);
  public function get_cache_size();
  public function save_check(array $data);
  public function save_cache(array $data);
  public function get_urls_to_check();
  public function save_view($id);
  public function get_items_to_purge($max_size);
  public function delete_all();
  public function delete($id);
}

class AmberStatus implements iAmberStatus {

  public function __construct(iAmberDB $db, $table_prefix = "") {
    $this->db = $db;
    $this->table_prefix = $table_prefix;
  }

  /**
   * Get information for a URL about it's most recent check
   * @param $url string to lookup
   * @param $source string with the name of the source of the check (e.g. 'amber', 'herdict')
   * @return array|mixed
   */
  public function get_check($url) {
    $prefix = $this->table_prefix;
    $result = $this->db->select("SELECT * FROM ${prefix}amber_check WHERE url = %s", array($url));
    return $result;
  }

  /**
   * Is there a cache for this URL?
   * @param  string  $url The URL to lookup
   * @return boolean      True if there is a cache, False if not
   */
  public function has_cache($url) {
    $prefix = $this->table_prefix;
    $result = $this->db->selectAll("SELECT * FROM ${prefix}amber_cache WHERE url = %s", array($url));
    return count($result) > 0;     
  }

  /**
   * Get information about a cache held by the system from one of the given provider types.
   * If there are multiple caches within the list of given provider types, one will be returned
   * at random. (In common usage, however, this should not arise)
   * @param  string $id      ID of the item to lookup
   * @param  array  $provider_types array of provider IDs to search within
   * @return asociative array of cache information
   */
  public function get_cache_by_id($id, $provider_types) {
    $prefix = $this->table_prefix;
    $provider_string = implode(', ', array_fill(0, count($provider_types), '%s'));
    $result = $this->db->select(
      "SELECT * FROM ${prefix}amber_cache WHERE id = %s AND provider in (" . $provider_string . ")", 
      array_merge(array($id), $provider_types));
    return $result;
  }

  /**
   * Get summary information about a cache (suitable for annotating a link) based on
   * a URL. In case there are multiple caches for the URL, provide a list of one or
   * more "preferred" cache providers to set as the default cache
   * @param  string $url                 URL to find a cache for
   * @param  array $preferred_providers  list of cache provider IDs
   * @return associative array           where the 'default' key points at the summary
   */
  public function get_summary($url, $preferred_providers ) {
    $prefix = $this->table_prefix;
    $query_result = $this->db->selectAll(' SELECT ca.location, ca.date, ch.status, ca.size, ca.provider ' .
                                " FROM ${prefix}amber_cache ca, ${prefix}amber_check ch " .
                                ' WHERE ca.url = %s AND ca.id = ch.id', 
                                array($url));
    $result = array();
    /* See if we can a result from one of our preferred providers */
    foreach ($query_result as $key => $value) {
      if (isset($value['provider']) && in_array($value['provider'], $preferred_providers)) {
        $result['default'] = $value;
      }
    }
    /* if we couldn't find one matching a preferred provider, take the first one */
    if (!isset($result['default']) && (count($query_result) > 0)) {
      $result['default'] = $query_result[0];
    }
    return $result;
  }

  /**
   * Save status information to the database
   * @param array $data
   * @return false on failure
   */
  public function save_check(array $data) {
    $prefix = $this->table_prefix;

    foreach (array('last_checked', 'next_check', 'status', 'url') as $key) {
      if (!array_key_exists($key,$data)) {
        error_log(join(":", array(__FILE__, __METHOD__, "Missing required key when updating status check", $key)));
        return false;
      }
    }
    if (!isset($data['message'])) {
      $data['message'] = "";
    }

    if (!isset($data['id'])) {
      $data['id'] = md5($data['url']);
      //TODO: Remove duplication of this with AmberStorage
    }
    $result = $this->db->select("SELECT COUNT(id) as count FROM ${prefix}amber_check WHERE id = %s", array($data['id']));
    $params = array($data['url'], $data['status'], $data['last_checked'], $data['next_check'], 
                    $data['message'], $data['id']);
    if ($result['count']) {
      $updateQuery = "UPDATE ${prefix}amber_check " .
                     'SET ' .
                     'url = %s, ' .
                     'status = %d, ' .
                     'last_checked = %d, ' .
                     'next_check = %d, ' .
                     'message = %s ' .
                     'WHERE id = %s';
      $this->db->update($updateQuery, $params);
    } else {
      $updateQuery = "INSERT into ${prefix}amber_check " .
                     '(url, status, last_checked, next_check, message, id) ' .
                     'VALUES(%s, %d, %d, %d, %s, %s)';
      $this->db->insert($updateQuery, $params);
    }
    return true;
  }

  /**
   * Save metadata about a cache entry to the database
   * @param array $data
   * @return false on failure
   */
  public function save_cache(array $data) {
    $prefix = $this->table_prefix;

    foreach (array('id', 'url', 'location', 'date', 'type', 'size', 'provider', 'provider_id') as $key) {
      if (!array_key_exists($key,$data)) {
        error_log(join(":", array(__FILE__, __METHOD__, "Missing required key when updating cache", $key)));
        return false;
      }
    }
    $result = $this->db->select("SELECT COUNT(id) as count FROM ${prefix}amber_cache WHERE id = %s AND provider = %d", 
                                array($data['id'], $data['provider']));
    $params = array($data['url'], $data['location'], $data['date'], $data['type'], 
                    $data['size'], $data['provider'], $data['provider_id'], $data['id']);
    if ($result['count']) {
      $updateQuery = "UPDATE ${prefix}amber_cache " .
                                        'SET ' .
                                        'url = %s, ' .
                                        'location = %s, ' .
                                        'date = %d, ' .
                                        'type = %s, ' .
                                        'size = %d, ' .
                                        'provider = %d, ' .
                                        'provider_id = %s ' .
                                        'WHERE id = %s ' .
                                        'AND provider = %d ';
      $params[] = $data['provider'];
      $this->db->update($updateQuery, $params);
    } else {
      $updateQuery = "INSERT into ${prefix}amber_cache " .
                                        '(url, location, date, type, size,provider, provider_id, id) ' .
                                        'VALUES(%s, %s, %d, %s, %d, %d, %s, %s)';
      $this->db->insert($updateQuery, $params);
    }
    return true;
  }

  /**
   * Get a list of URLs which are overdue for checking.
   */
  public function get_urls_to_check() {
    $prefix = $this->table_prefix;

    $result = array();
    $rows = $this->db->selectAll("SELECT url FROM ${prefix}amber_check WHERE next_check < %d ORDER BY next_check ASC", 
                                    array(time()));
    if ($result === FALSE) {
      error_log(join(":", array(__FILE__, __METHOD__, "Error retrieving URLs to check from database")));
      return array();
    } else {
      foreach ($rows as $row) {
        $result[] = $row['url'];
      }
    }
    return $result;
  }

  /**
   * Save the fact that a user viewed an externally hosted cache, based on the 
   * URL of the cache (e.g. http://perma.cc/xxx)
   * If the storage provider is the native, locally hosted one, do NOT record
   * the view, since in that case we get more accurate data by tracking actual
   * requests for the cache page itself
   * @param  string $location URL of the eternally hosted cache
   * @return boolean           true if the cache was found, false otherwise
   */
  public function save_view_for_external_cache_location($location) {
    $prefix = $this->table_prefix;

    $result = $this->db->select("SELECT id, provider FROM ${prefix}amber_cache WHERE location = %s", array($location));
    if ($result['id']) {
      if ($result['provider'] != 0) {
        $this->save_view($result['id']);
      }
      return true;
    } else {
      return false;
    }
  }

  public function save_view($id) {
    $prefix = $this->table_prefix;

    $result = $this->db->select("SELECT COUNT(id) as count FROM ${prefix}amber_activity WHERE id = %s", array($id));
    $params = array(time(), $id);
    if ($result['count']) {
      $updateQuery = "UPDATE ${prefix}amber_activity " .
                                        'SET views = views + 1, ' .
                                        'date = %d ' .
                                        'WHERE id = %s';
      $this->db->update($updateQuery, $params);
    } else {
      $updateQuery = "INSERT into ${prefix}amber_activity " .
                                        '(date, views, id) ' .
                                        'VALUES(%d, 1, %s)';
      $this->db->insert($updateQuery, $params);
    }
  }

  /**
   * Get total disk space usage of the cache
   * @return string
   */
  public function get_cache_size() {
    $prefix = $this->table_prefix;

    $result = $this->db->select("SELECT sum(size) as sum FROM ${prefix}amber_cache");
    return $result['sum'];
  }

  /**
   * Identify the cached items that must be deleted to keep the total disk usage below the desired maximum
   * @param $max_size
   */
  public function get_items_to_purge($max_disk) {
    $prefix = $this->table_prefix;

    $result = array();
    $current_size = $this->get_cache_size();
    if ($current_size > $max_disk) {
      
      /* Sqlite and Mysql have different names for a function we need */
      if ($this->db->db_type() == "sqlite")
        $max_function = "max";
      else
        $max_function = "greatest";
        
      $rows = $this->db->selectAll("SELECT cc.id, cc.url, size FROM ${prefix}amber_cache cc " .
                                   "LEFT JOIN ${prefix}amber_activity ca ON cc.id = ca.id " .
                                   "ORDER BY ${max_function}(IFNULL(ca.date,0),cc.date) ASC");
      $size_needed = $current_size - $max_disk;
      foreach ($rows as $row) {
        $size_needed = $size_needed - $row['size'];
        $result[] = array('id' => $row['id'], 'url' => $row['url']);
        if ($size_needed < 0) {
          break;
        }
      }
    }
    return $result;
  }

  /**
   * Delete all status information. Do NOT delete activity data.
   */
  public function delete_all() {
    $prefix = $this->table_prefix;

    $this->db->delete("DELETE FROM ${prefix}amber_cache");
    $this->db->delete("DELETE FROM ${prefix}amber_check");
  }

  /**
   * Delete an item from the cache table, and from the check table
   * if all caches have been deleted. Do NOT delete activity data.
   * @param $id
   */
  public function delete($id, $provider = 0) {
    $prefix = $this->table_prefix;

    $this->db->delete("DELETE FROM ${prefix}amber_cache WHERE id = %s AND provider = %d", array($id, $provider));    
    $this->db->delete("DELETE FROM ${prefix}amber_check WHERE id = %s AND %s not in (select id from ${prefix}amber_cache where id = %s)", array($id, $id, $id));
  }

} 