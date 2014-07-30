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

	/**
	 * Get an initialized AmberStorage module
	 * @return AmberStorage
	 */
	// private static function amber_get_storage() {
	//  	$storage = &drupal_static(__FUNCTION__);
	//  	if (!isset($storage)) {
	//     	$file_path = join(DIRECTORY_SEPARATOR,
	//       	array(DRUPAL_ROOT, variable_get('amber_storage_location', 'sites/default/files/amber')));
	//     	$storage = new AmberStorage($file_path);
	//   	}
	//   	return $storage;
	// }

	/**
	 * Return an initialized AmberFetcher module
	 * @return IAmberFetcher
	 */
	private static function amber_get_fetcher() {
    	$fetcher = new AmberFetcher(amber_get_storage(), array(
		      		'amber_max_file' => get_option('amber_max_file',1000),
	    	  		'header_text' => "This is a cached page",
	      			'excluded_content_types' => explode(PHP_EOL,get_option("amber_excluded_formats","")),
    	));
	  	return $fetcher;
	}

	/**
	 * Return an initialized AmberChecker module
	 * @return IAmberChecker
	 */
	private static function amber_get_checker() {
	    $checker = new AmberChecker();
	 	return $checker;
	}

	/**
	 * Return an initialized AmberStatus module
	 * @return IAmberStatus
	 */
	private static function amber_get_status() {
		global $wpdb;

	    $status = new AmberStatus(new AmberWPDB($wpdb), $wpdb->prefix);
		return $status;
	}

	private function get_behavior($status, $country = false)
	{
	  $result = $status ? "up" : "down";
	  $options = get_option('amber_options');
	  $c = $country ? "country_" : "";
	  if ($status) {
	    $action = isset($options["amber_${c}available_action"]) ? $options["amber_${c}available_action"] : AMBER_ACTION_NONE;
	    switch ($action) {
	      case AMBER_ACTION_NONE:
	        $result = NULL;
	        break;
	      case AMBER_ACTION_HOVER:
	        $result .= " hover:"; 
	        $result .= isset($options["amber_${c}available_action_hover"]) ? $options["amber_${c}available_action_hover"] : 2;
	        break;
	      case AMBER_ACTION_POPUP:
	        $result .= " popup";
	        break;
	      }
	  } else {
	    $action = isset($options["amber_${c}unavailable_action"]) ? $options["amber_${c}unavailable_action"] : AMBER_ACTION_NONE;
	    switch ($action) {
	      case AMBER_ACTION_NONE:
	        $result = NULL;
	        break;
	      case AMBER_ACTION_HOVER:
	        $result .= " hover:";
	        $result .= isset($options["amber_${c}unavailable_action_hover"]) ? $options["amber_${c}unavailable_action_hover"] : 2;
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
	    $result['data-cache'] = join(" ",array(get_site_url() . $summaries['default']['location'], date('c',$summaries['default']['date'])));
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
	  if ($country = get_option('amber_country_id','')) {
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
	  $status = Amber::amber_get_status();
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
  		var_dump($matches);
  		exit(2);
	}

}

include_once dirname( __FILE__ ) . '/amber-install.php';
include_once dirname( __FILE__ ) . '/amber-settings.php';
include_once dirname( __FILE__ ) . '/libraries/AmberStatus.php';
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

/* Add "Cache Now" link to links */
add_filter( 'post_row_actions', array('Amber', 'add_post_row_actions'), 10, 2 );
add_filter( 'page_row_actions', array('Amber', 'add_page_row_actions'), 10, 2 );


?>