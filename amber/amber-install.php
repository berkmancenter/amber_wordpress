<?php
class AmberInstall {

	private static function get_tables() {
		$tables = array();
		$tables['amber_check'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) DEFAULT '' NOT NULL,
		  status int,
		  last_checked datetime,
		  next_check datetime,
		  name tinytext NOT NULL,
		  message VARCHAR(2000),
		  PRIMARY KEY id (id)
		)";
		$tables['amber_cache'] =  "(
		  id VARCHAR(32) NOT NULL,
		  url VARCHAR(2000) DEFAULT '' NOT NULL,
		  location VARCHAR(2000) DEFAULT '' NOT NULL,
		  date datetime,
		  type VARCHAR(200) DEFAULT '' NOT NULL,
		  size int,
		  PRIMARY KEY id (id)
		)";
		$tables['amber_activity'] =  "(
		  id VARCHAR(32) NOT NULL,
		  date datetime,
		  views int DEFAULT 0 NOT NULL,
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
	}

	public static function deactivate() {
		// Nothing to do here at the moment
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