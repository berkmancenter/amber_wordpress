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

	public static function activate() {

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
            );

		update_option('amber_options', $options);

		/* The hook name needs to be string, it can't be a reference to a 
		 *  class function */
		error_log("wp_schedule_event in amber-install.php");
		wp_schedule_event( time(), 'fiveminutes', 'amber_cron_event_hook' );
	}

	public static function deactivate() {
		wp_clear_scheduled_hook( 'amber_cron_event_hook' );
	}

	public static function uninstall() {
		global $wpdb;
		$tables = AmberInstall::get_tables();
		foreach ($tables as $name => $table) {
			$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}${name}" );
		}
	}
}

?>