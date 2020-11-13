<?php
use WP_Reactions\Lite\Main;

/*
Plugin Name: WP Reactions Lite
Description: The #1 Emoji Reactions Plugin for Wordpress. Engage your users with Lottie animated emoji reactions and increase social sharing with mobile and desktop sharing pop-ups and surprise button reveals. Put your emojis anywhere you want to get a reaction.
Plugin URI: https://wpreactions.com
Version: 1.2.5
Requires at least: 4.4
Requires PHP: 5.3
Author: WP Reactions, LLC
Text Domain: wpreactions-lite
*/

define( 'WPRA_LITE_VERSION', '1.2.5' );
define( 'WPRA_LITE_DB_VERSION', 2);
define( 'WPRA_LITE_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'WPRA_LITE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPRA_LITE_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
define( 'WPRA_LITE_OPTIONS', 'wpra_lite_options' );

//spl_autoload_register(function ($class_name) {
//	if ( false !== strpos( $class_name, 'WP_Reactions' ) ) {
//		$class_file = str_replace( 'WP_Reactions\\Lite\\', '', $class_name ) . '.class.php';
//		$class_file = str_replace( '\\', DIRECTORY_SEPARATOR, $class_file );
//		require_once(WPRA_LITE_PLUGIN_PATH . 'includes/' . $class_file);
//	}
//});

require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Helper.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Configuration.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/PluginExtension.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Activation.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Field.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Checkbox.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Radio.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/RadioItem.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Select.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Switcher.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/FieldManager/Text.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/UpdateHandler.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/AdminPages.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Shortcode.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Metaboxes.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/AjaxHandler.class.php' );
require_once( WPRA_LITE_PLUGIN_PATH . 'includes/Main.class.php' );

// init plugin
global $wpra_lite;
$wpra_lite = new Main();

