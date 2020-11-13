<?php


namespace WP_Reactions\Lite;


class UpdateHandler {

	public static function init() {
		new self();
	}

	public function __construct() {
		$this->update();
	}

	function update()
	{
		global $wpdb;
		$db_version = get_option('wpra_lite_db_version', 1000);
		$plugin_version = get_option('wpra_lite_version', 1000);

		// ----------------------------- HANDLE DATABASE RELATED UPDATES ----------------------------------- //

		if (version_compare(2, $db_version, '>')) {
			Configuration::$current_options['emojis']['reaction7'] = 7;
			update_option( WPRA_LITE_OPTIONS, json_encode(Configuration::$current_options, true));
		}

		// update latest version and db versions
		update_option('wpra_lite_db_version', WPRA_LITE_DB_VERSION);
		update_option('wpra_lite_version', WPRA_LITE_VERSION);
	}

}