<?php
/**
 * Plugin Name: Amber
 * Plugin URI: http://amberlink.org
 * Description: Amber keeps links working on blogs and websites.
 * Version: 1.4.4
 * Author: Berkman Center for Internet & Society
 * Author URI: https://cyber.law.harvard.edu
 * License: GPL3
 */

define("AMBER_ACTION_NONE",0);
define("AMBER_ACTION_HOVER",1);
define("AMBER_ACTION_POPUP",2);
define("AMBER_ACTION_CACHE",3);
define("AMBER_STATUS_UP","up");
define("AMBER_STATUS_DOWN","down");
define("AMBER_VAR_LAST_CHECK_RUN","amber_last_check_run");
define("AMBER_EXTERNAL_AVAILABILITY_NONE",0);
define("AMBER_EXTERNAL_AVAILABILITY_NETCLERK",1);
define("AMBER_REPORT_AVAILABILITY_NONE",0);
define("AMBER_REPORT_AVAILABILITY_NETCLERK",1);
define("AMBER_BACKEND_LOCAL",0);
define("AMBER_BACKEND_PERMA",1);
define("AMBER_BACKEND_INTERNET_ARCHIVE",2);
define("AMBER_BACKEND_AMAZON_S3",3);


class Amber {

	private static $amber_status;
	private static $amber_checker;
	private static $amber_fetcher;
	private static $amber_fetchers;
	private static $amber_storage;
	private static $amber_availability;
	private static $amber_memento_service;

	public static function get_option($key, $default = "")
	{
		$options = get_option('amber_options');
		return isset($options[$key]) ? $options[$key] : $default;
	}

	/**
	 * Get an initialized iAmberStorage implementation, and cache it in a static variable
	 * Used to retrieve the currently selected iAmberStorage implementation that's being
	 * used to save new captures
	 * @return iAmberStorage
	 */
	public static function get_storage() {
		if (!Amber::$amber_storage) {
		    $storage_id = Amber::get_option('amber_backend', 0);
		    Amber::$amber_storage = Amber::get_storage_instance($storage_id);
		}
	  	return Amber::$amber_storage;
	}

	public static function set_storage($storage) {
		Amber::$amber_storage = $storage;
	}

	/**
	 * Get an AmberStorage module of the same type that was used to store a particular capture
	 * @param  string $id of an item
	 * @return iAmberStorage
	 */
	public static function get_storage_for_item($id) {
	  	$item = Amber::get_status()->get_cache_by_id($id, array(AMBER_BACKEND_LOCAL, AMBER_BACKEND_AMAZON_S3));
	  	return ($item) ? Amber::get_storage_instance($item['provider']) : null;
	}

	/**
	 * Select and initialize an implementation of iAmberStorage based on the backend ID
	 * @param  int $backend ID of the storage backend to use
	 * @return iAmberStorage
	 */
	public static function get_storage_instance($backend) {
	  	switch ($backend) {
		    case AMBER_BACKEND_LOCAL:
		    	$base_dir = wp_upload_dir();
		    	$subdir = Amber::get_option('amber_storage_location', 'amber');
		    	$file_path = join(DIRECTORY_SEPARATOR, array($base_dir['basedir'], $subdir));
		    	Amber::secure_local_storage($file_path);
				$storage = new AmberStorage($file_path);
				break;
	    	case AMBER_BACKEND_PERMA:
				$storage = new PermaStorage(array());
				break;
		    case AMBER_BACKEND_INTERNET_ARCHIVE:
		    	$storage = new InternetArchiveStorage(array());
		    	break;
	        case AMBER_BACKEND_AMAZON_S3:
				require_once("vendor/aws/aws-autoloader.php");
				$storage = new AmazonS3Storage(array(
					'access_key' => Amber::get_option('amber_aws_access_key',''),
					'secret_key' => Amber::get_option('amber_aws_secret_key',''),
					'bucket' => Amber::get_option('amber_aws_bucket',''),
					'region' => Amber::get_option('amber_aws_region','us-east-1'),
				));
				break;
		}
	 	return $storage;
	}

	/**
	 * Return an initialized AmberChecker module
	 * @return IAmberChecker
	 */
	public static function get_checker() {
		if (!Amber::$amber_checker) {
			Amber::$amber_checker = new AmberChecker();
		}
	 	return Amber::$amber_checker;
	}

	public static function set_checker($checker) {
		Amber::$amber_checker = $checker;
	}

	/**
	 * Return an initialized AmberStatus module
	 * @return IAmberStatus
	 */
	public static function get_status() {
		global $wpdb;

		if (!Amber::$amber_status) {
	    	Amber::$amber_status = new AmberStatus(new AmberWPDB($wpdb), $wpdb->prefix);
		}
		return Amber::$amber_status;
	}

	public static function set_status($status) {
		Amber::$amber_status = $status;
	}

	/**
	 * Return an initialized AmberFetcher module
	 * @return IAmberFetcher
	 */
	public static function get_fetcher() {
		if (!Amber::$amber_fetcher) {
			Amber::$amber_fetcher = Amber::get_fetcher_instance(Amber::get_option('amber_backend', 0));
    	}
	  	return Amber::$amber_fetcher;
	}

	public static function set_fetcher($fetcher) {
		Amber::$amber_fetcher = $fetcher;
	}

	/**
	 * Get a list of alternate fetchers that could be used
	 * @return array of IAmberFetcher
	 */
	public static function get_alternate_fetchers() {
		if (!Amber::$amber_fetchers) {
		    Amber::$amber_fetchers = array();
		    $backends = Amber::get_option('amber_alternate_backends', array());
		    foreach ($backends as $value) {
		    	Amber::$amber_fetchers[] = Amber::get_fetcher_instance($value);
		    }
    	}
	  	return Amber::$amber_fetchers;
	}

	/**
	 * Select and initialize an implementation of iAmberFetcher based on the backend ID
	 * @param  int $backend ID of the storage engine to use
	 * @return iAmberFetcher
	 */
	public static function get_fetcher_instance($backend) {

		switch ($backend) {
	    	case AMBER_BACKEND_LOCAL:
	    		$fetcher = new AmberFetcher(Amber::get_storage_instance($backend), array(
		      		'amber_max_file' => Amber::get_option('amber_max_file',1000),
	    	  		'header_text' => "You are viewing a snapshot of <a style='font-weight:bold !important; color:white !important' href='{{url}}'>{{url}}</a> created on {{date}}",
	      			'amber_excluded_formats' => Amber::get_option("amber_excluded_formats",false) ? explode(",", Amber::get_option("amber_excluded_formats","")) : array(),
    			));
		        break;
	      	case AMBER_BACKEND_PERMA:
		        $fetcher = new PermaFetcher(Amber::get_storage_instance($backend), array(
		          	'perma_api_key' => Amber::get_option('amber_perma_api_key',''),
		          	'perma_archive_url' => Amber::get_option('amber_perma_server_url', 'http://perma.cc'),
		          	'perma_api_url' => Amber::get_option('amber_perma_api_server_url', 'https://api.perma.cc',''),
		        ));
		        error_log(Amber::get_option('amber_perma_server_api_url', 'https://api.perma.cc',''));
		        break;
	      	case AMBER_BACKEND_INTERNET_ARCHIVE:
	        	$fetcher = new InternetArchiveFetcher(Amber::get_storage_instance($backend), array());
	        	break;
	    	case AMBER_BACKEND_AMAZON_S3:
	    		$fetcher = new AmberFetcher(Amber::get_storage_instance($backend), array(
		      		'amber_max_file' => 5000000, /* Max size for S3 file */
	    	  		'header_text' => "You are viewing a snapshot of <a style='font-weight:bold !important; color:white !important' href='{{url}}'>{{url}}</a> created on {{date}}",
	      			'amber_excluded_formats' => Amber::get_option("amber_excluded_formats",false) ? explode(",", Amber::get_option("amber_excluded_formats","")) : array(),
    			));
		        break;
		    default:
		    	$fetcher = null;
		}

  		return $fetcher;
	}

	/**
	 * Return an initialized AmberAvailabilityLookup module
	 * @return IAmberAvailabilityLookup
	 */
	public static function get_availability() {
		if (!Amber::$amber_availability) {
	    	Amber::$amber_availability = new AmberNetClerkAvailability(array());
		}
		return Amber::$amber_availability;
	}

	public static function set_availability_lookup($availability_lookup) {
		Amber::$amber_availability_lookup = $availability_lookup;
	}

	/**
	 * Return an initialized AmberMementoService
	 * @return IAmberMementoService
	 */
	public static function get_memento_service() {
		if (!Amber::$amber_memento_service) {
	    	Amber::$amber_memento_service = new AmberMementoService(array(
	    		'server_url' => Amber::get_option('amber_timegate','http://timetravel.mementoweb.org/timegate/')));
		}
		return Amber::$amber_memento_service;
	}

	public static function set_memento_service($memento_service) {
		Amber::$amber_memento_service = $memento_service;
	}

	public static function get_behavior($status, $country = false)
	{

	  $result = $status ? "up" : "down";
	  $c = $country ? "country_" : "";
	  if ($status) {
	    $action = Amber::get_option("amber_${c}available_action", AMBER_ACTION_NONE);
	    switch ($action) {
	      case AMBER_ACTION_NONE:
	        $result = NULL;
	        break;
	      case AMBER_ACTION_HOVER:
	        $result .= " hover:";
	        $result .= Amber::get_option("amber_${c}available_action_hover", 2);
	        break;
	      case AMBER_ACTION_POPUP:
	        $result .= " popup";
	        break;
	      }
	  } else {
	    $action = Amber::get_option("amber_${c}unavailable_action", AMBER_ACTION_NONE);
	    switch ($action) {
	      case AMBER_ACTION_NONE:
	        $result = NULL;
	        break;
	      case AMBER_ACTION_HOVER:
	        $result .= " hover:";
	        $result .= Amber::get_option("amber_${c}unavailable_action_hover", 2);
	        break;
	      case AMBER_ACTION_POPUP:
	        $result .= " popup";
	        break;
	      case AMBER_ACTION_CACHE:
	        $result .= " cache";
	        break;
	      }
	  }
	  return $result;
	}

	/**
	 * Build the data- attributes to be added to the anchor tag, given saved metadata
	 * @param array $summaries array dictionary from the Amber Status module
	 * @return array attributes to be added to the link
	 */
	private static function build_link_attributes($summaries) {
	  $result = array();
	  // Assume that we only have one cache of the data. This would need to change if we start tracking multiple caches
	  if (isset($summaries['default']['location'],$summaries['default']['date'])) {
		if (strpos($summaries['default']['location'], "http") === 0) {
			$result['data-versionurl'] = $summaries['default']['location'];
		} else {
		    $result['data-versionurl'] = join("/", array(get_site_url(),$summaries['default']['location']));
		}
	    $result['data-versiondate'] = date('c',$summaries['default']['date']);
	  } else {
	    return $result;
	  }
	  $default_status = isset($summaries['default']['status']) ? $summaries['default']['status'] : null;
	  // Add default behavior
	  if (!is_null($default_status)) {
	    $behavior = Amber::get_behavior($default_status);
	    if ($behavior) {
	      	$result['data-amber-behavior'] = $behavior;
	    } else {
    		$result['data-amber-behavior'] = "";
	    }
	  }
	  // See if we have country-specific behavior
	  if ($country = Amber::get_option('amber_country_id','')) {
	    $country_status = isset($summaries[$country]['status']) ? $summaries[$country]['status'] : $default_status;
	    if (!is_null($country_status)) {
	      $country_behavior = Amber::get_behavior($country_status, true);
	      // Add country-specific behavior only if it is different than the default behavior
	      if ($country_behavior && ($country_behavior != $result['data-amber-behavior'])) {
	        $result['data-amber-behavior'] .= ",${country} ${country_behavior}";
	      }
	    }
	  }

	  return $result;
	}


	/**
	 * Lookup a URL using the AmberStorage class, while caching for the duration of the page load
	 */
	private static function lookup_url($url) {
	  $status = Amber::get_status();
	  return Amber::build_link_attributes($status->get_summary($url, array(Amber::get_option('amber_backend', 0))));
	}

	/**
	 * Callback function for updating href's with data-* attrbutes, after they've been identified with a regular expression
	 * @param $matches
	 * @return string
	 */
	private static function filter_callback($matches) {
	  $data = Amber::lookup_url($matches[1]);
	  $result = $matches[0];
	  foreach ($data as $key => $value) {
	    $result .= " $key=\"$value\"" ;
	  }
	  return $result;
	}

	/**
	 * Amber filter process callback.
	 *
	 * Find all external links and annotate then with data-* attributes describing the information in the cache.
	 * Note: This treats all absolute URLs as external links.
	 */
	public static function filter($text) {
	  /* Only annotate links if there are some actions defined */
	  if (Amber::action_type_enabled(array(AMBER_ACTION_HOVER, AMBER_ACTION_POPUP, AMBER_ACTION_CACHE))) {
	    $re = '/href=["\'](http[^\v()<>{}\[\]]+?)[\'"]/i';
	    $text = preg_replace_callback($re, 'Amber::filter_callback', $text);
	  }
	  return $text;
	}

	/*
	 * Add our CSS and Javascript to every page
	 */
	public static function register_plugin_assets() {
		if (Amber::action_type_enabled(array(AMBER_ACTION_HOVER, AMBER_ACTION_POPUP))) {
			wp_register_style('amber', plugins_url('css/amber.css', __FILE__));
			wp_enqueue_style('amber');
		}
		if (Amber::action_type_enabled(array(AMBER_ACTION_HOVER, AMBER_ACTION_POPUP, AMBER_ACTION_CACHE))) {
			wp_register_script('amber', plugins_url('js/amber.js', __FILE__));
			wp_enqueue_script('amber');
			wp_localize_script('amber', 'amber_config', array(
				'lookup_availability' => (AMBER_EXTERNAL_AVAILABILITY_NETCLERK == Amber::get_option('amber_external_availability', AMBER_EXTERNAL_AVAILABILITY_NONE)),
				'site_name' => get_bloginfo( 'name' )
				));
		}
	}

	/* Are any of a list of actions enabled in the settings? */
	private static function action_type_enabled($actions) {
		$result = false;
		$options = array('amber_available_action', 'amber_unavailable_action', 'amber_country_available_action', 'amber_country_unavailable_action');
		foreach ($options as $option) {
			if (in_array(Amber::get_option($option, AMBER_ACTION_NONE), $actions)) {
				$result = true;
			}
		}
		return $result;
	}

	public static function cron_add_schedule($schedules)
	{
	 	$schedules['fiveminutes'] = array(
	 		'interval' => 300,
	 		'display' => __( 'Every five minutes' )
	 	);
	 	return $schedules;
	}

	/**
	 * Periodic cron job
	 */
	public static function cron_event_hook() {
		Amber::dequeue_link();
	    update_option(AMBER_VAR_LAST_CHECK_RUN, time());
	}

	/**
	 * If the total disk space usage is over the configured limit, delete enough items to bring it under
	 */
	public static function disk_space_purge() {
	  $status = Amber::get_status();
	  $purge = $status->get_items_to_purge(Amber::get_option('amber_max_disk',1000) * 1024 * 1024);
	  if ($purge) {
	    $storage = Amber::get_storage();
	    foreach ($purge as $item) {
	      $storage->delete($item['id']);
	      $status->delete($item['id']);
	    }
	  }
	}

	private static function cache_link($item, $force = false) {

		$checker = Amber::get_checker();
		$status =  Amber::get_status();
		$fetcher = Amber::get_fetcher();
		$availability = Amber::get_availability();

		/* Check whether the site is up */
		$last_check = $status->get_check($item);
		if (($update = $checker->check(empty($last_check) ? array('url' => $item) : $last_check, $force)) !== false) {
			/* There's an updated check result to save */
			$status->save_check($update);
			if ($availability && isset($update['details']) && (Amber::get_option('amber_report_availability', AMBER_REPORT_AVAILABILITY_NONE) == AMBER_REPORT_AVAILABILITY_NETCLERK)) {
				$availability->report_status($item, $update['details']);
			}

			/* Now cache the item if we should */
	  		$strategy = Amber::get_option('amber_update_strategy', 0);
			if ($update['status'] && (!$strategy || !$status->has_cache($item))) {

				/* Save the item to the primary storage location */
				$result = Amber::fetch_item($item, $fetcher, $status);

				/* Save the item to any alternate storage locations */
				foreach (Amber::get_alternate_fetchers() as $alternate_fetcher) {
					Amber::fetch_item($item, $alternate_fetcher, $status);
				}

				if ($result) {
				  	Amber::disk_space_purge();
				  	return true;
				}
			}
		} else {
			return false;
		}
	}

	/**
	 * Fetch an item and save the metadata
	 * @param  string $item    url to fetch
	 * @param  iAmberFetcher $fetcher instance of a fetcher to use to get the item
	 * @param  iAmberStatus  $status  instance of a status to use to save the item
	 * @return boolean  TRUE if succesfully cached
	 */
	private static function fetch_item($item, $fetcher, $status) {
		$cache_metadata = array();
		try {
			$cache_metadata = $fetcher->fetch($item);
		} catch (RuntimeException $re) {
			$update['message'] = $re->getMessage();
			$update['url'] = $item;
			$status->save_check($update);
			return false;
		}
		if ($cache_metadata) {
			$status->save_cache($cache_metadata);
			return true;
		} else {
			return false;
		}
	}


	/* Pull an item off the "queue", and save it to the cache.
	*/
	public static function dequeue_link() {
		global $wpdb;
		$prefix = $wpdb->prefix;
		$row = $wpdb->get_row(
			"SELECT c.url FROM ${prefix}amber_queue c WHERE c.locked is NULL ORDER BY created ASC LIMIT 1",
			ARRAY_A);
	  	if ($row and $row['url']) {
	  		$wpdb->query($wpdb->prepare(
	  			"UPDATE ${prefix}amber_queue SET locked = %d WHERE url = %s",
	  			array(time(), $row['url'])
	  			));
		    Amber::cache_link($row['url']);
  			$wpdb->query($wpdb->prepare(
	  			"DELETE from ${prefix}amber_queue where url = %s",
	  			array($row['url'])
	  			));
  			return $row['url'];
  		} else {
  			return "";
  		}
	}

	/**
	 * Add links that need to be checked to our queue to be checked at some point in the future
	 * Do not insert or update if the link already exists in the queue
	 */
	private static function enqueue_check_links($links)
	{
		global $wpdb;
		$prefix = $wpdb->prefix;
		foreach ($links as $link) {
			$query = $wpdb->prepare(
				"INSERT IGNORE INTO ${prefix}amber_queue (id, url, created) VALUES(%s, %s, %d)",
				array(md5($link), $link, time()));
			$wpdb->query($query);
		}
	}

	private static function cache_links($links, $immediately = false) {
		$result = array();
		if ($immediately) {
		    foreach ($links as $url) {
		    	$result[$url] = Amber::cache_link($url, true);
			}
		} else {
			Amber::enqueue_check_links($links);
		}
		return $result;
	}

	/**
	 * Filter links that are candidates for caching to exclude local links, or links to URLs on the blacklist
	 * @param $links array of links to check
	 * @param $blacklist array of regular expressions to exclude
	 */
	private static function filter_regexp_excluded_links($links)
	{
		$blacklist = preg_split("/[,\s]+/",Amber::get_option("amber_excluded_sites",""));
		if (!$blacklist) {
		  return $links;
		}
		$default_error_logging_level = error_reporting();
		$result = array('cache' => array(), 'excluded' => array());
		foreach ($links as $link) {
			$exclude = FALSE;
			foreach ($blacklist as $blacklistitem) {
				$blacklistitem = trim($blacklistitem);
				if ($blacklistitem) {
					$blacklistitem = preg_replace("/https?:\\/\\//i", "", $blacklistitem);
					$blacklistitem = str_replace("@", "\@", $blacklistitem);
					$blacklistitem = '@' . $blacklistitem . '@';
					$cleanedlink = preg_replace("/https?:\\/\\//i", "", $link);

					/* Hide warning messages from preg_match() that can be generated by
					   invalid user-entered regular expressions. */
					error_reporting(E_ALL ^ E_WARNING);
					$match_result = preg_match($blacklistitem, $cleanedlink);
					error_reporting($default_error_logging_level);

					if ($match_result === FALSE) {
						// Log compilation error explicitly, since we'd disabled warnings
						error_log("filter_regexp_excluded_links: Error processing excluded list regular expression for " . $blacklistitem);
					    $error = error_get_last();
					    error_log($error["message"]);
					} else if ($match_result) {
						$exclude = TRUE;
					} else {
						// If match_result === 0, meaning there was no match, so do nothing
					}
				}
			}
			if ($exclude) {
			  	$result['excluded'][] = $link;
			} else {
		    	$result['cache'][] = $link;
			}
		}
		return $result;
	}

	public static function extract_links($post_id, $cache_immediately = false) {
		$result = array();
        $post = get_post($post_id);
        $text = $post->post_content;
	 	$re = '/href=["\'](http[^\v()<>{}\[\]"\']+)[\'"]/i';
  		$count = preg_match_all($re, $text, $matches);
		$links = Amber::filter_regexp_excluded_links($matches[1]);
  		if ($count) {
  			 $result = Amber::cache_links($links['cache'],$cache_immediately);
  		}
  		foreach ($links['excluded'] as $key) {
  			$result[$key] = "";
  		}
  		return $result;
	}

	/**
	 * Retrieve an item from the cache for display
	 * @param $id string identifying the item to return
	 * @return null|string
	 */
	private static function retrieve_cache_item($id) {
	  $storage = Amber::get_storage_for_item($id);
	  if (is_null($storage)) {
	  	return NULL;
	  }
	  $data = $storage->get($id);
	  $metadata = $storage->get_metadata($id);
	  return ($data && $metadata) ? array('data' => $data, 'metadata' => $metadata) : NULL;
	}

	/**
	 * Retrieve an asset from the cache for display
	 * @param $id string identifying the item to return
	 * @return null|string
	 */
	private static function retrieve_cache_asset($cache_id, $asset_id) {
	  $storage = Amber::get_storage_for_item($cache_id);
	  if (is_null($storage)) {
	  	return NULL;
	  }
	  $d = $storage->get_asset($cache_id, $asset_id );
	  if ($d) {
	    $data['data'] = $d;
	    // Set the mime-type for certain files
	    $last_element = $asset_id;
	    $extension = substr($last_element, strrpos($last_element, '.') + 1);
	    switch ($extension) {
	      case "css" : $data['metadata']['type'] = 'text/css'; break;
	      case "jpg" : $data['metadata']['type'] = 'image/jpeg'; break;
	      case "png" : $data['metadata']['type'] = 'image/png'; break;
	      case "svg" : $data['metadata']['type'] = 'image/svg+xml'; break;
	      case "js" : $data['metadata']['type'] = 'application/javascript'; break;
	    }
	  }
	  return (isset($data)) ? $data : NULL;
	}


	/* Rewrite rules to direct requests for cached content */
	public static function add_rewrite_rules() {
		add_rewrite_rule('^.*amber/cache/([a-f0-9]+)/?$', '/index.php?amber_cache=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/?$', '/index.php?amber_cacheframe=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/assets/(.*)/?$', '/index.php?amber_cacheframe=$1&amber_asset=$2', "top");
		add_rewrite_rule('^.*amber/logcacheview?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_logcacheview&$1', "top");
		add_rewrite_rule('^.*amber/status?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_status&$1', "top");
		add_rewrite_rule('^.*amber/memento?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_memento&$1', "top");
	}

	/**
	 * Ensure that parameters passed by add_rewrite_rules() are accessible
	 */
	public static function custom_query_vars($vars) {
		$vars[] = 'amber_cache';
		$vars[] = 'amber_cacheframe';
		$vars[] = 'amber_asset';
		$vars[] = 'amber_sort';
		$vars[] = 'amber_dir';
		$vars[] = 'amber_page';
		$vars[] = 'url';
		$vars[] = 'country';
		$vars[] = 'date';
		return $vars;
	}

	/**
	 * Convert a string representation of a date into RFC1123 format
	 */
	public static function format_memento_date($date_string) {
		/* The default ISO8601 date string formatter doesn't include the colons in the time-zone component, which
		 is incompatible with javascript's date.parse() function in at least some implementations (Safari, definitely) */
		$ISO8601_FORMAT = 'Y-m-d\TH:i:sP';
		$dt = DateTime::createFromFormat($ISO8601_FORMAT, $date_string);
		$result = $dt->format(DateTime::RFC1123);
		return $result;
	}


	/**
	 * Request handling function to display cached content and assets
	 */
	public static function display_cached_content ($wp) {

		$cache_frame_id = !empty($wp->query_vars['amber_cacheframe']) ? $wp->query_vars['amber_cacheframe'] : "";
		$cache_id = !empty($wp->query_vars['amber_cache']) ? $wp->query_vars['amber_cache'] : "";
		$asset_id = !empty($wp->query_vars['amber_asset']) ? $wp->query_vars['amber_asset'] : "";
		$asset_id = rtrim($asset_id,"/"); /* Get rid of stray characters on the end */

		/* Displaying the cache frame page with an iframe referencing the cached item */
		if (!empty($cache_id)) {
			status_header( 200 ); /* This must be set BEFORE any content is printed */
			$data = Amber::retrieve_cache_item($cache_id);
			 /* If the document is a PDF, serve it directly rather than in an iframe. Browsers
			    will not render PDFs within sandboxed iframes. */
			if (isset($data['metadata']['type']) && ($data['metadata']['type'] == 'application/pdf')) {
				print $data['data'];
			 	$status = Amber::get_status();
				$status->save_view($cache_id);
				die();
			}
  			$uri = $_SERVER["REQUEST_URI"];
			$iframe_url = "";
			if ($uri && (strrpos($uri,"/") == (strlen($uri) - 1))) {
				$iframe_url = "../";
			}
			$iframe_url .= "../" . "cacheframe/${cache_id}/";
			print <<<EOF
<!DOCTYPE html>
<html style="height: 100%">
<head>
<title>Amber</title>
</head>
<body style="margin:0; padding: 0; height: 100%">
<iframe
sandbox="allow-scripts allow-forms allow-popups allow-pointer-lock"
security="restricted"
style="border:0 none transparent; background-color:transparent; width:100%; height:100%;"
src="${iframe_url}"></iframe>
</body>
</html>
EOF;
			die();
		}
		if (!empty($cache_frame_id)) {
			/* Check to make sure the cached item is being displayed in an iframe that
			   protects the user from XSS attacks from malicious javascript that might
			   exist in a snapshot */
			if (!Amber::validate_cache_referrer()) {
				status_header(403);
				print "To protect against XSS attacks, Amber requires that all snapshots be displayed within an iframe. " .
					  "It appears as if you are trying to display a snapshot or associated asset outside of an iframe.";
				die();
			} else {
				status_header( 200 ); /* This must be set BEFORE any content is printed */
				if (empty($asset_id)) {
					/* This is the root item */
					$data = Amber::retrieve_cache_item($cache_frame_id);
					$status = Amber::get_status();
					$status->save_view($cache_frame_id);
			    	print $data['data'];
			    	die();
				} else {
					/* This is an asset */
					$data = Amber::retrieve_cache_asset($cache_frame_id, $asset_id);
			    	print($data['data']);
			    	die();
				}
			}
		}
	}

	/**
	 * When displaying cached content, set the Content-Type header for the
     * content item or asset
	 */
	public static function filter_cached_content_headers($headers)
	{
		global $wp;
		$cache_frame_id = !empty($wp->query_vars['amber_cacheframe']) ? $wp->query_vars['amber_cacheframe'] : "";
		$cache_id = !empty($wp->query_vars['amber_cache']) ? $wp->query_vars['amber_cache'] : "";
		$asset_id = !empty($wp->query_vars['amber_asset']) ? $wp->query_vars['amber_asset'] : "";
		$asset_id = rtrim($asset_id,"/"); /* Get rid of stray characters on the end */

		if (!empty($cache_frame_id)) {
			if (empty($asset_id)) {
				/* This is the root item */
				$data = Amber::retrieve_cache_item($cache_frame_id);
			    if (isset($data['metadata']['type'])) {
					$headers['Content-Type'] = $data['metadata']['type'];
			    }
			} else {
				/* This is an asset */
				$data = Amber::retrieve_cache_asset($cache_frame_id, $asset_id);
			    if (isset($data['metadata']['type'])) {
					$headers['Content-Type'] = $data['metadata']['type'];
				}
			}
		}
		if ((!empty($cache_id) || !empty($cache_frame_id)) && empty($asset_id)) {
			if (!isset($data)) {
				$data = Amber::retrieve_cache_item($cache_id);
			}
			// Add Memento header to cache iframe and cache item
		    if (isset($data['metadata']['cache']['amber']['date'])) {
		    	$memento_date = Amber::format_memento_date($data['metadata']['cache']['amber']['date']);
		    	$headers['Memento-Datetime'] = $memento_date;
		    }
		    // PDFs are rendered immediately, not displayed within iframes,
		    // so set the content-type appropriately
			if (isset($data['metadata']['type']) && ($data['metadata']['type'] == 'application/pdf')) {
				$headers['Content-Type'] = $data['metadata']['type'];
			}
		}
		return $headers;
	}

	/**
	 * Called when displaying cached content that's expected to be in an iframe or
	 * referenced from a page that is itself enclosed within an iframe.
	 * Validates that (a) the iframe that's enclosing the cached content is being served
	 * from the expected URL on the same server; or (b) the cached asset is being served
	 * by a page at the expected URL on the same server. We know that requests from this
	 * URL provide the proper sandbox attributes for the iframe to prevent XSS
	 * attacks.
	 * @return true if access is allowed
	 */
	public static function validate_cache_referrer() {
		$result = FALSE;
		if (!function_exists('getallheaders')) {
			function getallheaders() {
				$headers = array();
				foreach ($_SERVER as $name => $value) {
					if (substr($name, 0, 5) == 'HTTP_') {
						$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
					}
				}
				return $headers;
			}
		}
		$headers = getallheaders();
		if (isset($headers['Referer'])) {
			/* Option 1: The Referer URL should be the same as the current URL, except with
			'cache' instead of 'cacheframe' in the URL */
			$referer_uri = $headers['Referer'];
			$requested_uri = $_SERVER['REQUEST_URI'];

			/* The value that should be in the HTTP Referer header */
			$expected_referer = str_replace("/cacheframe/", "/cache/", $requested_uri);
			if (substr($referer_uri, -strlen($expected_referer)) == $expected_referer) {
				$result = TRUE;
			}
			else {
				/* Option 2: This is an asset and the requested URL should be the same as the
				   referring URL + "/assets/SNAPSHOT_ID.FILENAME_EXTENSION" */
				$cutoff = strrpos($requested_uri, "/assets/");
				$expected_asset_referrer = substr($requested_uri, 0, $cutoff + 1);
				if (substr($referer_uri, -strlen($expected_asset_referrer)) == $expected_asset_referrer) {
					$result = TRUE;
				}
			}
		}
		return $result;
	}

	/* Respond to an ajax call to cache links on a specific page immediately
	 */
	public static function ajax_cache_now() {
		check_ajax_referer( 'amber_cache_now' );
	    update_option(AMBER_VAR_LAST_CHECK_RUN, time());
		$id = $_POST['id'];
		if ($id) {
			$links = Amber::extract_links($id, true);
		}
		$cached = array();
		$failed = array();
		foreach ($links as $key => $value) {
			if ($value) {
				$cached[] = $key;
			} else {
				$failed[] = $key;
			}
		}
		print json_encode(array('cached' => $cached, 'failed' => $failed));
		die();
	}

	/* Respond to an ajax call from the dashboard as part of the
	   "Cache all links" process. Returning an empty string signifies
	   that all links have been cached.
	 */
	public static function ajax_cache() {
		check_ajax_referer( 'amber_dashboard' );
	    update_option(AMBER_VAR_LAST_CHECK_RUN, time());
		$url = Amber::dequeue_link();
		print $url;
		die();
	}

	/* Respond to an ajax call from the dashboard to kick off the
	   scanning process by identifying all pages and posts that
	   need to be scanned, and placing them in transient storage
	   to be worked through by ajax_scan().
	 */
	public static function ajax_scan_start() {
		check_ajax_referer( 'amber_dashboard' );
		$amber_types = explode(',',Amber::get_option('amber_post_types','post,page'));
		$post_ids = get_posts(array(
		    'numberposts'   => -1, // get all posts.
		    'fields'        => 'ids', // Only get post IDs
		    'post_type'     => $amber_types,
		));
		set_transient('amber_scan_posts', $post_ids, 24*60*60);
		print count($post_ids);
		die();
	}

	/* Scan pages and posts for links to be queued for caching.
 	   Return the number of items left to be scanned.
	 */
	public static function ajax_scan() {
		/* Maximum number of pages and posts to process in each
		   request. This is used for both pages AND posts, so the
		   maximum per request is actually twice this, depending
		   on the mix of content on the site */
		check_ajax_referer( 'amber_dashboard' );
		$batch_size = 10;
		$number_remaining = 0;
		$transients = array('amber_scan_posts');
		foreach ($transients as $t) {
			$ids = get_transient($t);
			if ($ids !== FALSE && is_array($ids)) {
				$i = $batch_size;
				while ((count($ids) > 0) && ($i-- > 0)) {
					$id = array_shift($ids);
					Amber::extract_links($id);
				}
				set_transient($t, $ids, 24*60*60);
				$number_remaining += count($ids);
			}
		}
		print $number_remaining;
		die();
	}

	public static function add_meta_boxes()
	{
		$screens = explode(',',Amber::get_option('amber_post_types','post,page'));
		foreach ( $screens as $screen ) {
			add_meta_box(
				'amber_sectionid',
				'Amber',
				array('Amber', 'display_meta_boxes'),
				$screen,
				'side'
			);
		}
	}

	public static function display_meta_boxes($post)
	{
		submit_button("Preserve links now", "small", "cache_now");
		wp_nonce_field( 'amber_cache_now', '_wpnonce_amber' );
		print '
<div id="cache-status"></div>
<script type="text/javascript" >
jQuery(document).ready(function($) {
';
		print "var data = { 'action': 'amber_cache_now', 'id': '$post->ID', '_wpnonce': $('#_wpnonce_amber').val() };";
		print '
	$("input#cache_now").click(function(){
		$("div#cache-status").html("Preserving links...")
		$.post(ajaxurl, data, function(response) {
			if (response) {
				var cached = response.cached.join("<br/>");
				var failed = response.failed.join("<br/>");
				var result = "";
				if (cached) {
					result += "<p><strong>These links were preserved successfully</strong><br/>" + cached + "</p>";
				}
				if (failed) {
					result += "<p><strong>These links were not preserved</strong><br/>" + failed + "</p>";
				}
				if (!result) {
					result = "No links found";
				}
				$("div#cache-status").html(result);
			}
		}, "json");
		return false;
	});});
</script>
';
	}

	public static function admin_notices() {
		global $wp_rewrite;

		if (!$wp_rewrite->using_mod_rewrite_permalinks()) {
			print '
<div class="error">
	<p>Permalinks must be enabled (set to something other than "Default") for Amber to work properly.
	Enable Permalinks <a href="'. get_site_url() . '/wp-admin/options-permalink.php">here</a></p>
</div>';
		}

		if (!function_exists('curl_init')) {
			print '
<div class="error">
	<p>The PHP cURL extension must be installed for Amber to work properly. Ask your web host to install it, or follow the instructions <a href="https://secure.php.net/manual/en/curl.installation.php" target="_blank">here</a>.</p>
</div>';
		}

	}

 	public static function ajax_log_cache_view() {
		if ( isset( $_GET['cache'] ) ) {
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Expires: 0");

    		$status = Amber::get_status();
    		if ( $status->save_view_for_external_cache_location ($_GET['cache'] ) ) {
    			status_header( 200 );
    		} else {
    			status_header( 404 );
    		}
    	} else {
    		status_header( 400 );
    	}
		die();
	}

	/* Handle Ajax request and query NetClerk to get availability information for a URL */
 	public static function ajax_get_url_status() {
 		if (isset($_REQUEST['url']) && isset($_REQUEST['country'])) {
			header("Content-Type: application/json");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Expires: 0");

    		$lookup = Amber::get_availability();
		    $lookup_result = $lookup->get_status( $_REQUEST['url'], $_REQUEST['country'] );
		    if ($lookup_result === FALSE || !isset($lookup_result['data'])) {
		    	status_header( 500 );
		    	die();
		    }
    		foreach ( $lookup_result['data'] as $key => $value ) {
      			$lookup_result['data'][$key]['behavior'] =  Amber::get_behavior($lookup_result['data'][$key]['available']);
			}
    		print(json_encode($lookup_result, JSON_UNESCAPED_SLASHES));
   			status_header( 200 );
    	} else {
    		status_header( 400 );
    	}
		die();
	}

	/* Handle Ajax request and query a Timegate server to find Mementos for a URL */
 	public static function ajax_get_memento() {
 		if (isset($_REQUEST['url'], $_REQUEST['date'])) {

			header("Content-Type: application/json");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header("Expires: 0");
			$date = str_replace(" ", "+", $_REQUEST['date']);
    		$memento_service = Amber::get_memento_service();
		    $lookup_result = $memento_service->getMemento( $_REQUEST['url'], $date );
    		print(json_encode($lookup_result, JSON_UNESCAPED_SLASHES));
   			status_header( 200 );
    	} else {
    		status_header( 400 );
    	}
		die();
	}

	/* Ensure that the directory on the local file system that contains cached content
	   has an htaccess file to prevent the content from being loaded directly (to
	   protect against XSS attacks from malicious saved javascript) */
	public static function secure_local_storage($dir) {
		$filename = join(DIRECTORY_SEPARATOR,array($dir,'.htaccess'));
		if (!file_exists($filename)) {
			$htaccess = <<<EOF
<Files "*">
  Order Deny,Allow
  Deny from all
</Files>
EOF;
			file_put_contents($filename, $htaccess);
		}
	}

}

include_once dirname( __FILE__ ) . '/amber-install.php';
include_once dirname( __FILE__ ) . '/amber-settings.php';
include_once dirname( __FILE__ ) . '/amber-dashboard.php';
include_once dirname( __FILE__ ) . '/libraries/AmberStatus.php';
include_once dirname( __FILE__ ) . '/libraries/AmberInterfaces.php';
include_once dirname( __FILE__ ) . '/libraries/AmberNetworkUtils.php';
include_once dirname( __FILE__ ) . '/libraries/AmberMementoService.php';
include_once dirname( __FILE__ ) . '/libraries/backends/amber/AmberStorage.php';
include_once dirname( __FILE__ ) . '/libraries/backends/amber/AmberFetcher.php';
include_once dirname( __FILE__ ) . '/libraries/backends/internetarchive/InternetArchiveStorage.php';
include_once dirname( __FILE__ ) . '/libraries/backends/internetarchive/InternetArchiveFetcher.php';
include_once dirname( __FILE__ ) . '/libraries/backends/perma/PermaStorage.php';
include_once dirname( __FILE__ ) . '/libraries/backends/perma/PermaFetcher.php';
if (version_compare(PHP_VERSION, "5.3.3") >= 0) {
	include_once dirname( __FILE__ ) . '/libraries/backends/aws/AmazonS3Storage.php';
}
include_once dirname( __FILE__ ) . '/libraries/AmberChecker.php';
include_once dirname( __FILE__ ) . '/libraries/AmberAvailability.php';
include_once dirname( __FILE__ ) . '/libraries/AmberDB.php';

/* The filter to lookup and rewrite links with amber data- attributes */
add_filter ( 'the_content', array('Amber', 'filter'));

/* Activate, deactivate, and uninstall hooks */
register_activation_hook( __FILE__, array('AmberInstall','activate'));
register_deactivation_hook( __FILE__, array('AmberInstall','deactivate'));
register_uninstall_hook( __FILE__, array('AmberInstall','uninstall'));

/* Check to see if the plugin has been updated */
add_action( 'plugins_loaded', array('AmberInstall', 'upgrade') );

/* Warn if permalinks are not enabled */
add_action( 'admin_notices', array('Amber', 'admin_notices') );

/* Add CSS and Javascript to all pages */
add_action( 'wp_enqueue_scripts', array('Amber', 'register_plugin_assets') );

/* Scan content for links whenever it's saved */
add_action( 'save_post', array('Amber', 'extract_links') );

/* Add actions and filters for loading cache content */
add_action( 'init', array('Amber', 'add_rewrite_rules') );
add_filter( 'query_vars', array('Amber', 'custom_query_vars') );
add_action( 'parse_query', array('Amber', 'display_cached_content') );
add_filter( 'wp_headers', array('Amber', 'filter_cached_content_headers') );

/* Add "Cache Now" link to edit pages */
add_action( 'add_meta_boxes', array('Amber', 'add_meta_boxes') );

/* Setup cron */
add_action( 'amber_cron_event_hook', array('Amber', 'cron_event_hook') );
add_filter( 'cron_schedules', array('Amber', 'cron_add_schedule') );
if ( ! wp_next_scheduled( 'amber_cron_event_hook' ) ) {
	wp_schedule_event( time(), 'fiveminutes', 'amber_cron_event_hook' );
}

/* Setup ajax methods for batch caching and scanning */
add_action( 'wp_ajax_amber_cache', array('Amber', 'ajax_cache') );
add_action( 'wp_ajax_amber_cache_now', array('Amber', 'ajax_cache_now') );
add_action( 'wp_ajax_amber_scan_start', array('Amber', 'ajax_scan_start') );
add_action( 'wp_ajax_amber_scan', array('Amber', 'ajax_scan') );
add_action( 'wp_ajax_nopriv_amber_logcacheview', array('Amber', 'ajax_log_cache_view') );
add_action( 'wp_ajax_nopriv_amber_status', array('Amber', 'ajax_get_url_status') );
add_action( 'wp_ajax_amber_status', array('Amber', 'ajax_get_url_status') );
add_action( 'wp_ajax_nopriv_amber_memento', array('Amber', 'ajax_get_memento') );
add_action( 'wp_ajax_amber_memento', array('Amber', 'ajax_get_memento') );

?>
