<?php
namespace WP_Reactions\Lite;

/*
 * This Class for firing when Plugin first time activated
 */

class Activation {

	public function __construct() {
		global $wpdb;
		// if no wpj options then create defaults
		if ( ! get_option( WPRA_LITE_OPTIONS ) ) {
			update_option( WPRA_LITE_OPTIONS, json_encode(Configuration::$default_options, true));
		}

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE IF NOT EXISTS " . Configuration::$tbl_reacted_users . " (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                bind_id varchar(100) NOT NULL,
                react_id varchar(30) NOT NULL,
                reacted_to varchar(10) NOT NULL,
                reacted_date DATETIME NOT NULL,
                PRIMARY KEY  (id)
        ) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	static function start() {
		$instance = new self();
		return $instance;
	}
}
