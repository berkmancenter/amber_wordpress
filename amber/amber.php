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
class Amber {

	/**
	 * Lookup a URL using the AmberStorage class, while caching for the duration of the page load
	 */
	private static function lookup_url($url) {
	  // $status = amber_get_status();
	  // return _amber_build_link_attributes($status->get_summary($url));
		return array("data-cache" => '/amber/cache/banana 34534', 
			'data-amber-behavior' => 'up hover:0');
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
}

include_once dirname( __FILE__ ) . '/amber-install.php';

add_filter ( 'the_content', array('Amber', 'filter'));
register_activation_hook( __FILE__, array('AmberInstall','activate'));
register_deactivation_hook( __FILE__, array('AmberInstall','deactivate'));
register_uninstall_hook( __FILE__, array('AmberInstall','uninstall'));

add_action( 'wp_enqueue_scripts', array('Amber', 'register_plugin_assets') );


?>