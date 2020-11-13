<?php
/*
Plugin Name: Bunyad Demo Import
Plugin URI: http://theme-sphere.com
Description: Modified (fork) version of "One Click Demo Import"
Version: 1.0.3
Author: ThemeSphere & ProteusThemes
Author URI: http://theme-sphere.com
License: GPL3
License URI: http://www.gnu.org/licenses/gpl.html
Text Domain: pt-ocdi
*/

/**
 * This fork was created due to underlying difference between our idea of
 * how the UI should function vs the one in the original plugin.
 * 
 * Main Changes:
 *  - Different UI in class-ocdi-main
 *  - A different AJAX implementation to handle failures better
 *  - Improvements to new_ajax_request_maybe()
 */

// Exit if accessed directly
if (!defined('ABSPATH')) exit;

if (!function_exists('bunyad_demo_import_init')) {
	
	function bunyad_demo_import_init() {
		/**
		 * PHP version 5.3.2 test
		 */
		if (version_compare( phpversion(), '5.4.0', '<')) {
		
			/**
			 * Display an admin error notice when PHP is older the version 5.4.0
			 * Hook it to the 'admin_notices' action.
			 */
			function bunyad_old_php_admin_error_notice() {
				$message = sprintf( esc_html__( 'The %2$sBunyad Demo Import%3$s plugin requires %2$sPHP 5.3.2+%3$s to run properly. Please contact your web hosting company and ask them to update the PHP version of your site to at least PHP 5.4.0.%4$s Your current version of PHP: %2$s%1$s%3$s', 'pt-ocdi' ), phpversion(), '<strong>', '</strong>', '<br>' );
				printf('<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post($message));
			}
			add_action('admin_notices', 'bunyad_old_php_admin_error_notice');
		}
		else if (is_admin() && (class_exists('PT_One_Click_Demo_Import') OR defined('PT_OCDI_PATH'))) {
			
			function bunyad_clash_error_notice() {
				echo '<div class="notice notice-error"><p>Please de-activate the plugin "One Click Demo Import" as there is a conflict between the plugins.</p></div>';
			}
			
			add_action('admin_notices', 'bunyad_clash_error_notice');
		}
		else {
		
			// Current version of the plugin.
			define('PT_OCDI_VERSION', '1.2.0');
		
			// Path/URL to root of this plugin, with trailing slash.
			define('PT_OCDI_PATH', plugin_dir_path( __FILE__ ));
			define('PT_OCDI_URL', plugin_dir_url( __FILE__ ));
		
			// Require main plugin file.
			require PT_OCDI_PATH . 'inc/class-ocdi-main.php';
			require PT_OCDI_PATH . 'inc/menus.php';
		
			Bunyad_Demo_Import::getInstance();
		}
	}
}

add_action('plugins_loaded', 'bunyad_demo_import_init');
