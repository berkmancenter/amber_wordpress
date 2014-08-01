<?php

require_once 'AmberDB.php';

interface iAmberStatus {
  public function get_check($url, $source = 'amber');
  public function get_cache($url, $source = 'amber');
  public function get_summary($url);
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
  public function get_check($url, $source = 'amber') {
    return $this->get_item($url, 'amber_check');
  }

  public function get_cache($url, $source = 'amber') {
    return $this->get_item($url, 'amber_cache');
  }

  private function get_item($url, $table) {
    $prefix = $this->table_prefix;
    $result = $this->db->select("SELECT * FROM ${prefix}${table} WHERE url = %s", array($url));
    return $result;
  }

  public function get_summary($url) {
    $prefix = $this->table_prefix;
    $result = $this->db->select(' SELECT ca.location, ca.date, ch.status, ca.size ' .
                                " FROM ${prefix}amber_cache ca, ${prefix}amber_check ch " .
                                ' WHERE ca.url = %s AND ca.id = ch.id', 
                                array($url));
    return array('default' => $result);
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

    foreach (array('id', 'url', 'location', 'date', 'type', 'size') as $key) {
      if (!array_key_exists($key,$data)) {
        error_log(join(":", array(__FILE__, __METHOD__, "Missing required key when updating cache", $key)));
        return false;
      }
    }
    $result = $this->db->select("SELECT COUNT(id) as count FROM ${prefix}amber_cache WHERE id = %s", array($data['id']));
    $params = array($data['url'], $data['location'], $data['date'], $data['type'], 
                    $data['size'], $data['id']);
    if ($result['count']) {
      $updateQuery = "UPDATE ${prefix}amber_cache " .
                                        'SET ' .
                                        'url = %s, ' .
                                        'location = %s, ' .
                                        'date = %d, ' .
                                        'type = %s, ' .
                                        'size = %d ' .
                                        'WHERE id = %s';
      $this->db->update($updateQuery, $params);
    } else {
      $updateQuery = "INSERT into ${prefix}amber_cache " .
                                        '(url, location, date, type, size, id) ' .
                                        'VALUES(%s, %s, %d, %s, %d, %s)';
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

  public function save_view($id) {
    $prefix = $this->table_prefix;

    $result = $this->db->select("SELECT COUNT(id) as count FROM ${prefix}amber_activity WHERE id = %s", array($id));
    $params = array(time(), $id);
    if ($result['count']) {
      $updateQuery = 'UPDATE ${prefix}amber_activity ' .
                                        'SET views = views + 1, ' .
                                        'date = %d ' .
                                        'WHERE id = %s';
      $this->db->update($updateQuery, $params);
    } else {
      $updateQuery = 'INSERT into ${prefix}amber_activity ' .
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
      if ($this->db->original_db()->getAttribute(PDO::ATTR_DRIVER_NAME) == "sqlite")
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
   * Delete an item from the cache and check tables. Do NOT delete activity data.
   * @param $id
   */
  public function delete($id) {
    $prefix = $this->table_prefix;

    foreach (array('amber_cache', 'amber_check') as $table) {
      $this->db->delete("DELETE FROM ${prefix}${table} WHERE id = %s", array($id));
    }
  }


} 