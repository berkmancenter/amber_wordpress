<?php

require_once 'AmberChecker.php';
require_once 'AmberFetcher.php';
require_once 'AmberStorage.php';
require_once 'AmberStatus.php';

$config = array();
$config['database'] = "/var/lib/amber/amber.db";
$config['cache'] = "/usr/local/nginx/html/amber/cache";
date_default_timezone_set('UTC');

function amber_log($str) {
  print date("r") . " -- " . $str . "\n";
}

function main($argc, $argv) {
  global $config;
  $options = getopt("",array("action::", "db::", "cache::", "url::", "ini::", "help"));
  if (isset($options["ini"])) {
    $ini_config = read_config($options["ini"]);
    if (!empty($ini_config)) {
      $config = array_merge($config, $ini_config);
    }
  }
  if (isset($options["db"])) {
    $config['database'] = $options["db"];
  }
  if (isset($options["cache"])) {
    $config['cache'] = $options["cache"];
  }
  if (isset($options["help"])) {
    usage();
    return;
  }
  if (!isset($options["action"])) {
    $options["action"] = "dequeue";
  }

  switch ($options["action"]) {
    case false:
    case "dequeue":
      disk_space_purge();
      update_last_checked();
      dequeue();
      break;
    case "check":
      schedule_checks();
      break;
    case "cache":
      if ($options["url"]) {
        cache($options["url"]);
      } else {
        "Error: Provide URL to cache";
      }
      break;
    case "help":
    default:
      usage();
      break;
  }
}

function usage() {
  print "Usage: $argc [--action=dequeue|cache|check|help] [--db=path_to_database] [--cache=path_to_cache] [--ini=path_to_config_file] [--url=url_to_cache]\n";
}

function read_config($location) {
  if (!file_exists($location)) {
    error_log(sprintf("Could not load ini file at (%s): %s", $location, "File does not exist"));  
    return array();
  }
  try {
    $ini_config = parse_ini_file($location);  
  } catch (RuntimeException $re) {
    error_log(sprintf("Could not load ini file at (%s): %s", $location, $re->getMessage()));
  }
  return $ini_config;
}

/* Download a single URL and save it to the cache */
function cache($url) {
  global $config;

  $fetcher = get_fetcher();
  $status = get_status();
  $checker = get_checker();
  amber_log("Checking ${url}");
  $last_check = $status->get_check($url);
  if (($update = $checker->check(empty($last_check) ? array('url' => $url) : $last_check, true)) !== false) {
    $status->save_check($update);

    /* Now cache the item if we should */
    $existing_cache = $status->get_cache($url);
    if ($update['status'] && ((isset($config['amber_update_strategy']) && $config['amber_update_strategy']) || !$existing_cache)) {
      amber_log("Caching ${url}");
      try {
        $cache_metadata = $fetcher->fetch($url);
      } catch (RuntimeException $re) {
        error_log(sprintf("Did not cache (%s): %s", $url, $re->getMessage()));
        $update['message'] = $re->getMessage();
        $status->save_check($update);        
        return;
      }
      if ($cache_metadata) {
        $status->save_cache($cache_metadata);
      }
    }
  }
}

/* Pull an item off the "queue", and save it to the cache.
   Note that if this is run in parallel, it's possible that the same item could be processed multiple times
   To run until the queue is empty use the shell command: while php AmberRunner.php dequeue; do true ; done
*/
function dequeue() {
  $db_connection = get_database();
  $result = $db_connection->query('SELECT c.url FROM amber_queue c WHERE c.lock is NULL ORDER BY created ASC LIMIT 1');
  $row = $result->fetch();
  $result->closeCursor();
  if ($row and $row['url']) {
    $update_query = $db_connection->prepare('UPDATE amber_queue SET lock = :time WHERE url = :url');
    $update_query->execute(array('url' => $row['url'], 'time' => time()));
    cache($row['url']);
    $update_query = $db_connection->prepare('DELETE from amber_queue where url = :url');
    $update_query->execute(array('url' => $row['url']));
    // TODO: Need to determine behavior on failure
    exit(0);
  } else {
    // print "No more items to cache\n";
    exit(1);
  }
}

/* Find all items that are due to be checked, and put them on the queue for checking */
function schedule_checks() {
  $db_connection = get_database();
  $status_service = get_status();
  $urls = $status_service->get_urls_to_check();
  foreach ($urls as $url) {
    $insert_query = $db_connection->prepare('INSERT OR IGNORE INTO amber_queue (url, created) VALUES(:url, :created)');
    $insert_query->execute(array('url' => $url, 'created' => time()));
  }
  amber_log("Scheduled " . count($urls) . " urls for checking");
}

function disk_space_purge() {
  global $config;

  $status = get_status();
  $max_size = isset($config['amber_max_disk']) ? $config['amber_max_disk'] : 1000;
  $purge = $status->get_items_to_purge($max_size * 1024 * 1024);
  if ($purge) {
    $storage = get_storage();
    foreach ($purge as $item) {
      $storage->clear_cache_item($item['id']);
      $status->delete($item['id']);
      amber_log ("Deleting to stay under disk space limits: " . $item['url']);
    }
  }
}

function update_last_checked() {
    global $config;
    $db_connection = get_database();
    $insert_query = $db_connection->prepare("INSERT OR REPLACE INTO amber_variables (name, value) VALUES('last_run', :time)");
    $insert_query->execute(array('time' => time()));
}

function get_database() {
  global $config;
  try {
    $db_connection = new PDO('sqlite:' . $config['database']);
  } catch (PDOException $e) {
    print "Error: Cannot open database: " . $e->getMessage();
    exit(1);
  }
  return $db_connection;
}

function get_storage() {
  global $config;
  return new AmberStorage($config['cache']);
}

function get_fetcher() {
  global $config;
  return new AmberFetcher(get_storage(), $config);
}

function get_checker() {
  return new AmberChecker();
}

function get_status() {
  global $config;
  try {
    $db_connection = new PDO('sqlite:' . $config['database']);
  } catch (PDOException $e) {
    print "Error: Cannot open database: " . $e->getMessage();
    return null;
  }
  return new AmberStatus(new AmberPDO($db_connection));
}

main($argc,$argv);


?>