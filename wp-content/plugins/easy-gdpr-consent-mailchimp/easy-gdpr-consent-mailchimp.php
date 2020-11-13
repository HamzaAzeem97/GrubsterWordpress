<?php
/**
 * Easy GDPR Consent - MailChimp
 * 
 * @package           Sphere\EGCF
 *
 * Plugin Name:       Easy GDPR Consent - MailChimp
 * Description:       Add a GDPR consent popup to your MailChimp forms without any design changes.
 * Version:           1.0.1
 * Author:            asadkn
 * Author URI:        https://profiles.wordpress.org/asadkn/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sphere-egcf
 * Domain Path:       /languages
 * Requires PHP:      5.4
 */

defined('WPINC') || exit;

if (version_compare( phpversion(), '5.4', '<')) {
		
	/**
	 * Display an admin error notice when PHP is older the version 5.4
	 * Hook it to the 'admin_notices' action.
	 */
	function egcf_old_php_admin_error_notice() {
		
		$message = sprintf(esc_html__(
			'The %2$sEasy GDPR Form Consent%3$s plugin requires %2$sPHP 5.4+%3$s to run properly. Please contact your web hosting company and ask them to update the PHP version of your site.%4$s Your current version of PHP: %2$s%1$s%3$s', 'sphere-egcf'), 
			phpversion(), 
			'<strong>', 
			'</strong>', 
			'<br>'
		);

		printf('<div class="notice notice-error"><p>%1$s</p></div>', wp_kses_post($message));
	}
	
	add_action('admin_notices', 'egcf_old_php_admin_error_notice');
	
	// bail
	return;
}

/**
 * Launch the plugin
 */
require_once 'inc/plugin.php';

$plugin = \Sphere\EGCF\Plugin::get_instance();
$plugin->plugin_file = __FILE__;
$plugin->init();

/**
 * Register activation and deactivation hooks
 */

register_activation_hook(__FILE__, function() {
	// Noop
});

register_deactivation_hook(__FILE__, function() {
	wp_clear_scheduled_hook('egcf_geoip_updater');
});