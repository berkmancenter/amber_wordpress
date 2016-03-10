<?php

global $amber_db_version;
$amber_db_version = '1.4';

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
		  PRIMARY KEY  (id)
		)";
		$tables['amber_cache'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) DEFAULT '' NOT NULL,
		  location VARCHAR(2000) DEFAULT '' NOT NULL,
		  date int,
		  type VARCHAR(200) DEFAULT '' NOT NULL,
		  size int,
		  provider int,
		  provider_id VARCHAR(2000) DEFAULT '' NOT NULL,
		  PRIMARY KEY  (id,provider)
		)";
		$tables['amber_activity'] =  "(
		  id VARCHAR(32) NOT NULL,
		  date int,
		  views int DEFAULT 0 NOT NULL,
		  PRIMARY KEY  (id)
		)";
		$tables['amber_queue'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) NOT NULL,
		  created int,
		  locked int,
		  PRIMARY KEY  (id)
		)";
		return $tables;
	}

	private static function install_tables($tables) {
		global $wpdb;

		$charset_collate = '';
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}

		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/* Delete existing index on amber_cache table if it exists */
		$cache_table_name = $wpdb->prefix . "amber_cache";
		if ($wpdb->get_var("SHOW TABLES LIKE '$cache_table_name'") == $cache_table_name) {
			$wpdb->query("DROP INDEX `PRIMARY` ON $cache_table_name");
		}

		foreach ($tables as $name => $table) {
			$table_name = $wpdb->prefix . $name;
			$sql = "CREATE TABLE $table_name $table $charset_collate";
			dbDelta( $sql );
		}
	}

	public static function activate($networkwide) {
		AmberInstall::iterate_over_sites("activate_site");
	}

	public static function activate_site() {
		global $amber_db_version;

		AmberInstall::install_tables( AmberInstall::get_tables() );

		add_option( 'amber_db_version', $amber_db_version );

		$options = get_option('amber_options');
		if (empty($options)) {
			/* Set default options */
			$options =  array(
				'amber_post_types' => 'post,page',
	            'amber_max_file' => 5000,
	            'amber_max_disk' => 1000,
	            'amber_available_action' => AMBER_ACTION_NONE,
	            'amber_unavailable_action' => AMBER_ACTION_HOVER,
	            'amber_available_action_hover' => 2,
	            'amber_unavailable_action_hover' => 2,
	            'amber_storage_location' => 'amber',
	            'amber_update_strategy' => 0,
	            'amber_timegate' => 'http://timetravel.mementoweb.org/timegate/',
	            'amber_country_id' => '',
	            'amber_excluded_sites' => parse_url(home_url(), PHP_URL_HOST),
				'amber_backend' => AMBER_BACKEND_LOCAL,
				'amber_perma_server_url' => 'http://perma.cc',
				'amber_perma_api_server_url' => 'https://api.perma.cc',
				'amber_aws_region' => 'us-east-1',
	            );

			update_option('amber_options', $options);
		}

		/* The hook name needs to be a string, it can't be a reference to a class function */
		if ( ! wp_next_scheduled( 'amber_cron_event_hook' ) ) {
			wp_schedule_event( time(), 'fiveminutes', 'amber_cron_event_hook' );
		}

		add_rewrite_rule('^.*amber/cache/([a-f0-9]+)/?$', '/index.php?amber_cache=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/?$', '/index.php?amber_cacheframe=$1', "top");
		add_rewrite_rule('^.*amber/cacheframe/([a-f0-9]+)/assets/(.*)/?$', '/index.php?amber_cacheframe=$1&amber_asset=$2', "top");
		add_rewrite_rule('^.*amber/logcacheview?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_logcacheview&$1', "top");
		add_rewrite_rule('^.*amber/status?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_status&$1', "top");
		add_rewrite_rule('^.*amber/memento?(.*)/?$', '/wp-admin/admin-ajax.php?action=amber_memento&$1', "top");
		flush_rewrite_rules();
	}

	public static function upgrade() {
		AmberInstall::iterate_over_sites("upgrade_site");
	}

	public static function upgrade_site() {
		global $amber_db_version;
		global $wpdb;

		$installed_db_version = get_option( "amber_db_version" );
		if ( $installed_db_version != $amber_db_version ) {
			/* Upgrade from 1.0-1.3 => 1.4 */

			/* Delete existing index on amber_cache table if it exists */
			$cache_table_name = $wpdb->prefix . "amber_cache";
			if ($wpdb->get_var("SHOW TABLES LIKE '$cache_table_name'") == $cache_table_name) {
				$wpdb->query("DROP INDEX `PRIMARY` ON $cache_table_name");
			}

			$tables = array('amber_cache' =>  "(
							  id VARCHAR(32) NOT NULL,
							  url VARCHAR(2000) DEFAULT '' NOT NULL,
							  location VARCHAR(2000) DEFAULT '' NOT NULL,
							  date int,
							  type VARCHAR(200) DEFAULT '' NOT NULL,
							  size int,
							  provider int,
							  provider_id VARCHAR(2000) DEFAULT '' NOT NULL,
							  PRIMARY KEY  (id,provider)
							)");
			AmberInstall::install_tables( $tables );

			$options = get_option( 'amber_options' );
			$options['amber_backend'] = AMBER_BACKEND_LOCAL;
			$options['amber_perma_server_url'] = "http://perma.cc";
			$options['amber_perma_api_server_url'] = "https://api.perma.cc";
			$options['amber_aws_region'] = "us-east-1";
			update_option( 'amber_options', $options );
			update_option( 'amber_db_version', $amber_db_version );
		}
	}

	public static function deactivate() {
		AmberInstall::iterate_over_sites("deactivate_site");
	}

	public static function deactivate_site() {
		wp_clear_scheduled_hook( 'amber_cron_event_hook' );
	}

	public static function iterate_over_sites($function_name) {
		global $wpdb;
	    if (function_exists('is_multisite') && is_multisite()) {
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