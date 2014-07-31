<?php
/**
 * Plugin Name: Amber
 * Plugin URI: https://github.com/berkmancenter/robustness_wordpress
 * Description: Enables the preservation of content to which your website links.
 * Version: 0.1
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

class Amber {

	private static function get_option($key, $default)
	{
		$options = get_option('amber_options');
		return isset($options[$key]) ? $options[$key] : $default;
	}

	/**
	 * Get an initialized AmberStorage module
	 * @return AmberStorage
	 */
	private static function get_storage() {
    	// $file_path = join(DIRECTORY_SEPARATOR, array(DRUPAL_ROOT, variable_get('amber_storage_location', 'sites/default/files/amber')));

    	$base_dir = wp_upload_dir();
    	$subdir = Amber::get_option('amber_storage_location', 'amber');
    	$file_path = join(DIRECTORY_SEPARATOR, array($base_dir['basedir'], $subdir));
    	$storage = new AmberStorage($file_path);
	  	return $storage;
	}

	/**
	 * Return an initialized AmberChecker module
	 * @return IAmberChecker
	 */
	private static function get_checker() {
	    $checker = new AmberChecker();
	 	return $checker;
	}

	/**
	 * Return an initialized AmberStatus module
	 * @return IAmberStatus
	 */
	private static function get_status() {
		global $wpdb;

	    $status = new AmberStatus(new AmberWPDB($wpdb), $wpdb->prefix);
		return $status;
	}

	/**
	 * Return an initialized AmberFetcher module
	 * @return IAmberFetcher
	 */
	private static function get_fetcher() {
    	$fetcher = new AmberFetcher(Amber::get_storage(), array(
		      		'amber_max_file' => Amber::get_option('amber_max_file',1000),
	    	  		'header_text' => "This is a cached page",
	      			'excluded_content_types' => Amber::get_option("amber_excluded_formats",false) ? explode(PHP_EOL, Amber::get_option("amber_excluded_formats","")) : array(),
    	));
	  	return $fetcher;
	}


	private function get_behavior($status, $country = false)
	{
	  $result = $status ? "up" : "down";
	  $options = get_option('amber_options');
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
	private function build_link_attributes($summaries) {
	  $result = array();
	  // Assume that we only have one cache of the data. This would need to change if we start tracking multiple caches
	  if (isset($summaries['default']['location'],$summaries['default']['date'],$summaries['default']['size']) &&
	      ($summaries['default']['size'] > 0)) {
	    $result['data-cache'] = join(" ",array(join("/", array(get_site_url(),$summaries['default']['location'])), date('c',$summaries['default']['date'])));
	  } else {
	    return $result;
	  }
	  $default_status = isset($summaries['default']['status']) ? $summaries['default']['status'] : null;
	  // Add default behavior
	  if (!is_null($default_status)) {
	    $behavior = Amber::get_behavior($default_status);
	    if ($behavior) {
	      $result['data-amber-behavior'] = $behavior;
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
	  return Amber::build_link_attributes($status->get_summary($url));
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

	/* 
	 * Add our CSS and Javascript to every page
	 */
	public static function register_plugin_assets() {
		wp_register_style('amber', plugins_url('amber/css/amber.css'));
		wp_enqueue_style('amber');
		wp_register_script('amber', plugins_url('amber/js/amber.js'));
		wp_enqueue_script('amber');
	}

	/**
	 * Amber filter process callback.
	 *
	 * Find all external links and annotate then with data-* attributes describing the information in the cache.
	 * Note: This treats all absolute URLs as external links.
	 */
	public static function filter($text) {
	  if (true) /* It's enabled! */ {
	    $re = '/href=["\'](http[^\v()<>{}\[\]]+)[\'"]/';
	    $text = preg_replace_callback($re, 'Amber::filter_callback', $text);
	  }
	  return $text;
	}

	public static function add_post_row_actions( $actions, WP_Post $post ) {
    	$actions['amber-cache'] = "<a href='#'>Cache now</a>";
    	return $actions;
	}

	public static function add_page_row_actions( $actions, WP_Post $post ) {
    	$actions['amber-cache'] = "<a href='#'>Cache now</a>";
    	return $actions;
	}

	private static function cache_link($item, $force = false) {

		$checker = Amber::get_checker();
		$status =  Amber::get_status();
		$fetcher = Amber::get_fetcher();

		/* Check whether the site is up */
		$last_check = $status->get_check($item);
		if (($update = $checker->check(empty($last_check) ? array('url' => $item) : $last_check, $force)) !== false) {

			/* There's an updated check result to save */
			$status->save_check($update);

			/* Now cache the item if we should */
			$existing_cache = $status->get_cache($item);
	  		$options = get_option('amber_options');
	  		$strategy = isset($options["amber_update_strategy"]) ? $options["amber_update_strategy"] : 0;
			if ($update['status'] && (!$strategy || !$existing_cache)) {
				$cache_metadata = array();
				try {
					$cache_metadata = $fetcher->fetch($item);
				} catch (RuntimeException $re) {
				  // watchdog('amber', "Did not cache: @url: @message", array('@url' => $item, '@message' => $re->getMessage()), WATCHDOG_NOTICE);
					$update['message'] = $re->getMessage();
					$status->save_check($update);        
					return false;
				}
				if ($cache_metadata) {
					$status->save_cache($cache_metadata);
				  	/* Clear caches that could contain HTML with versions of the links that don't contain data- attributes */
				  	/* TODO: Ideally we would clear the cache only once per cron job */
				  	// cache_clear_all('*', 'cache_filter',TRUE);
				  	// cache_clear_all('*', 'cache_field',TRUE);
				  	// amber_disk_space_purge();
				  	// watchdog('amber', "Cached: @url in @seconds seconds", array('@url' => $item, '@seconds' => time() - $start), WATCHDOG_DEBUG);
				  	return true;
				}
			}
		} else {
			return false;
		}

	}

	private static function cache_links($links, $immediately = false) {
	    foreach ($links as $url) {
			if (Amber::cache_link($url, $immediately)) {
				// drupal_set_message(t("Sucessfully cached: @url.", array('@url' => $url)), 'status');
			} else {
				// drupal_set_message(t("Could not cache: @url.", array('@url' => $url)), 'warning');
			}
		}
	}

	public static function extract_links($post_id) {
        $post = get_post($post_id);
        $text = $post->post_content;
	 	$re = '/href=["\'](http[^\v()<>{}\[\]"\']+)[\'"]/';
  		$count = preg_match_all($re, $text, $matches);
  		if ($count) {
  			 Amber::cache_links($matches[1],true);
  		}
	}

	/**
	 * Retrieve an item from the cache for display
	 * @param $id string identifying the item to return
	 * @return null|string
	 */
	private static function retrieve_cache_item($id) {
	  $storage = Amber::get_storage();
	  $data = $storage->get($id);
	  $metadata = $storage->get_metadata($id);
	  $status = Amber::get_status();
	  $status->save_view($id);
	  return ($data && $metadata) ? array('data' => $data, 'metadata' => $metadata) : NULL;
	}

	/**
	 * Retrieve an asset from the cache for display
	 * @param $id string identifying the item to return
	 * @return null|string
	 */
	private function retrieve_cache_asset($cache_id, $asset_id) {
	  $storage =  Amber::get_storage();
	  // $d = $storage->get_asset($id, join('/',$args) . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
	  $d = $storage->get_asset($cache_id, $asset_id );
	  if ($d) {
	    $data['data'] = $d;
	    // Set the mime-type for certain files
	    $last_element = $asset_id;
	    $extension = substr($last_element, strrpos($last_element, '.') + 1);
	    switch ($extension) {
	      case "css" : $data['metadata']['type'] = 'text/css'; break;
	      case "jpg" : $data['metadata']['type'] = 'image/jpeg'; break;      
	      case "svg" : $data['metadata']['type'] = 'image/svg+xml'; break;      
	    }
	  }
	  return (isset($data)) ? $data : NULL;
	}


	/* Rewrite rules to direct requests for cached content */
	public static function add_rewrite_rules() {

		// TODO : Make this work, and do not rely on editing .htaccess

		// add_rewrite_rule(
		//     '^.*amber/cache/([a-f0-9]+)/?$',
		//     "/index.php?amber_cache=$matches[1]",
		//     "index.php",
		//     'top'
		// );
		// 		flush_rewrite_rules();

//  		print "<pre>";
// global $wp_rewrite;
// print_r($wp_rewrite);
// 		print "</pre>";

		// =====================
		// TODO : Move this to Activation ONLY
		// =====================	
		// flush_rewrite_rules();
		// die();
	}

	/* Ensure that parameters passed by add_rewrite_rules() are accessible */
	public static function custom_query_vars($vars) {
		$vars[] = 'amber_cache';
		$vars[] = 'amber_asset';
		return $vars;
	}

	public static function display_cached_content ($wp) {

		$cache_id = !empty($wp->query_vars['amber_cache']) ? $wp->query_vars['amber_cache'] : "";
		$asset_id = !empty($wp->query_vars['amber_asset']) ? $wp->query_vars['amber_asset'] : "";
		$asset_id = rtrim($asset_id,"/"); /* Get rid of stray characters on the end */

		if (!empty($cache_id)) {
			if (empty($asset_id)) {
				/* This is the root item */
				$data = Amber::retrieve_cache_item($cache_id);
			    if (isset($data['metadata']['type'])) {
			    	header('Content-Type', $data['metadata']['type']);
			    }
		    	print $data['data'];
				die();
			} else {
				/* This is an asset */
				$data = Amber::retrieve_cache_asset($cache_id, $asset_id);
			    if (isset($data['metadata']['type'])) {
			    	header('Content-Type', $data['metadata']['type']);
				}
		    	print($data['data']);
				die();
			}
		}
	}

	public static function add_header()
	{
		// header( 'X-bogus: sfasdfas' );
	}

}

include_once dirname( __FILE__ ) . '/amber-install.php';
include_once dirname( __FILE__ ) . '/amber-settings.php';
include_once dirname( __FILE__ ) . '/libraries/AmberStatus.php';
include_once dirname( __FILE__ ) . '/libraries/AmberStorage.php';
include_once dirname( __FILE__ ) . '/libraries/AmberFetcher.php';
include_once dirname( __FILE__ ) . '/libraries/AmberChecker.php';
include_once dirname( __FILE__ ) . '/libraries/AmberDB.php';

/* The filter to lookup and rewrite links with amber data- attributes */
add_filter ( 'the_content', array('Amber', 'filter'));

/* Activate, deactivate, and uninstall hooks */
register_activation_hook( __FILE__, array('AmberInstall','activate'));
register_deactivation_hook( __FILE__, array('AmberInstall','deactivate'));
register_uninstall_hook( __FILE__, array('AmberInstall','uninstall'));

/* Add CSS and Javascript to all pages */
add_action( 'wp_enqueue_scripts', array('Amber', 'register_plugin_assets') );

/* Scan content for links whenever it's saved */
add_action( 'save_post', array('Amber', 'extract_links') );

/* Add actions and filters for loading cache content */
add_action( 'init', array('Amber', 'add_rewrite_rules') );
add_filter( 'query_vars', array('Amber', 'custom_query_vars') );
add_action( 'parse_request', array('Amber', 'display_cached_content') );
add_action( 'send_headers', array('Amber', 'add_header') );


/* Add "Cache Now" link to links */
add_filter( 'post_row_actions', array('Amber', 'add_post_row_actions'), 10, 2 );
add_filter( 'page_row_actions', array('Amber', 'add_page_row_actions'), 10, 2 );


?>