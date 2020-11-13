<?php
/**
 * Plugin Name: Bunyad Widget for Instagram
 * Description: For showing your latest Instagram photos.
 * Plugin URI: https://theme-sphere.com
 * Author: ThemeSphere
 * Author URI: https://theme-sphere.com
 * Version: 1.1.3
 * Requires PHP: 5.4
 * Text Domain: bunyad-instagram-widget
 * Domain Path: /assets/languages/
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 */

// Initialize.
function bunyad_instagram_widget_setup() {

	// Define some constants.
	define('WP_INSTAGRAM_WIDGET_JS_URL', plugins_url('/assets/js', __FILE__));
	define('WP_INSTAGRAM_WIDGET_CSS_URL', plugins_url('/assets/css', __FILE__));
	define('WP_INSTAGRAM_WIDGET_IMAGES_URL', plugins_url('/assets/images', __FILE__));
	define('WP_INSTAGRAM_WIDGET_PATH', dirname(__FILE__));
	define('WP_INSTAGRAM_WIDGET_BASE', plugin_basename(__FILE__));
	define('WP_INSTAGRAM_WIDGET_FILE', __FILE__);

	// Load language files.
	load_plugin_textdomain('bunyad-instagram-widget', false, dirname(WP_INSTAGRAM_WIDGET_BASE) . '/languages/');
}

// Register the widget.
function bunyad_instagram_widget_register() {
	require_once trailingslashit(WP_INSTAGRAM_WIDGET_PATH) . '/instagram-widget.php';
	register_widget('Bunyad_Instagram_Widget');
}

/**
 * An init is used to first check for conflicts.
 * 
 * Check for conflict at plugins_loaded (as WP Instagram Plugin may not be loaded before this point).
 */
add_action('plugins_loaded', function() {

	if (function_exists('wpiw_init') || class_exists('null_instagram_widget')) {
		add_action('admin_notices', function() {
			?>
			<div class="notice notice-error">
				<p><?php echo esc_html('Bunyad Widget for Instagram conflicts with the plugin "WP Instagram Widget". Please disable plugin named "WP Instagram Widget".'); ?></p>
			</div>
			<?php
		});

		return;
	}

	bunyad_instagram_widget_setup();
	add_action('widgets_init', 'bunyad_instagram_widget_register');

});
