<?php
class AmberInstall {

	private static function get_tables() {
		$tables = array();
		$tables['amber_check'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) DEFAULT '' NOT NULL,
		  status int,
		  last_checked int,
		  next_check int,
		  message VARCHAR(2000),
		  PRIMARY KEY id (id)
		)";
		$tables['amber_cache'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) DEFAULT '' NOT NULL,
		  location VARCHAR(2000) DEFAULT '' NOT NULL,
		  date int,
		  type VARCHAR(200) DEFAULT '' NOT NULL,
		  size int,
		  PRIMARY KEY id (id)
		)";
		$tables['amber_activity'] =  "(
		  id VARCHAR(32) NOT NULL,
		  date int,
		  views int DEFAULT 0 NOT NULL,
		  PRIMARY KEY id (id)
		)";
		$tables['amber_queue'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) NOT NULL,
		  created int,
		  locked int,
		  PRIMARY KEY id (id)
		)";
		return $tables;
	}

	public static function activate($networkwide) {
		AmberInstall::iterate_over_sites("activate_site");
	}

	public static function activate_site() {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}

		$tables = AmberInstall::get_tables();

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		foreach ($tables as $name => $table) {
			$table_name = $wpdb->prefix . $name;
			$sql = "CREATE TABLE $table_name $table $charset_collate";
			dbDelta( $sql );
		}

		$options = get_option('amber_options');
		if (empty($options)) {	
			/* Set default options */
			$options =  array(
	            'amber_max_file' => 1000,
	            'amber_max_disk' => 1000,
	            'amber_available_action' => AMBER_ACTION_NONE,
	            'amber_unavailable_action' => AMBER_ACTION_HOVER,
	            'amber_available_action_hover' => 2,
	            'amber_unavailable_action_hover' => 2,
	            'amber_storage_location' => 'amber',
	            'amber_update_strategy' => 0,
	            'amber_country_id' => '',
	            'amber_excluded_sites' => parse_url(home_url(), PHP_URL_HOST),
	            );

			update_option('amber_options', $options);			
		}

		/* The hook name needs to be string, it can't be a reference to a 
		 *  class function */
		error_log("wp_schedule_event in amber-install.php");
		wp_schedule_event( time(), 'fiveminutes', 'amber_cron_event_hook' );
		
		add_rewrite_rule('^.*amber/cache/([a-f0-9]+)/?$', '/index.php?amber_cache=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/?$', '/index.php?amber_cacheframe=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/assets/(.*)/?$', '/index.php?amber_cacheframe=$1&amber_asset=$2', "top");
		flush_rewrite_rules();
	}

	public static function deactivate() {
		AmberInstall::iterate_over_sites("deactivate_site");
	}

	public static function deactivate_site() {
		wp_clear_scheduled_hook( 'amber_cron_event_hook' );
	}

	public static function iterate_over_sites($function_name) {
		global $wpdb;
	    if (function_exists('is_multisite') && is_multisite() && $networkwide) {
	        // check if it is a network activation - if so, run the activation function for each blog id
            $old_blog = $wpdb->blogid;
            // Get all blog ids
            $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
            foreach ($blogids as $blog_id) {
                switch_to_blog($blog_id);
                call_user_func("AmberInstall::" . $function_name);
            }
            switch_to_blog($old_blog);
	    } else {
			call_user_func("AmberInstall::" . $function_name);
	    }		
	}

	public static function uninstall() {
		AmberInstall::iterate_over_sites("uninstall_site");
	}

	public static function uninstall_site() {
		global $wpdb;
		$tables = AmberInstall::get_tables();
		foreach ($tables as $name => $table) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}${name}" );
		}
	}
}

?>