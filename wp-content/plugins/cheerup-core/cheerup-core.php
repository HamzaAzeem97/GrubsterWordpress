<?php
/**
 * CheerUp Core
 *
 * Plugin Name:       CheerUp Core
 * Description:       Elements and core functionality for CheerUp Theme.
 * Version:           1.0.0
 * Author:            ThemeSphere
 * Author URI:        http://theme-sphere.com
 * License:           ThemeForest Split
 * License URI:       http://themeforest.net/licenses/standard
 * Text Domain:       cheerup
 * Domain Path:       /languages
 * Requires PHP:      5.6
 */

defined('WPINC') || exit;

class CheerUp_Core 
{
	const VERSION    = '1.0.0';
	const THEME_SLUG = 'cheerup';

	protected static $instance;

	/**
	 * Path to plugin folder
	 */
	public $path;
	public $path_url;

	public function __construct()
	{
		$this->path = plugin_dir_path(__FILE__);

		// URL for the plugin dir
		$this->path_url = plugin_dir_url(__FILE__);
	}
	
	/**
	 * Set it up
	 */
	public function init()
	{		
		$lib_path   = $this->path . 'lib/';
		
		/**
		 * When one of our themes isn't active, use shims
		 */
		if (!class_exists('Bunyad_Core')) {
			require_once $this->path . 'lib/bunyad.php';
			require_once $this->path . 'inc/bunyad.php';

			// Set path to local as theme isn't active
			Bunyad::$fallback_path = $lib_path;

			Bunyad::options()->set_config(array(
				'theme_prefix' => self::THEME_SLUG,
				'meta_prefix'  => '_bunyad'
			));
		}
		else {

			// If we're here, there's a ThemeSphere theme active. All ThemeSphere themes have
			// their own core plugins. Theme's own core plugin should be used instead.
			if (Bunyad::options()->get_config('theme_name') !== self::THEME_SLUG) {
				return;
			}
		}

		// Outdated Bunyad from an old theme? Cannot continue.
		if (!property_exists('Bunyad', 'fallback_path')) {
			return;
		}

		// Set local fallback for some components not packaged with theme.
		Bunyad::$fallback_path = $lib_path;

		/**
		 * Setup filters and data
		 */

		// Admin related actions
		add_action('admin_init', array($this, 'admin_init'));

		// User profile fields
		add_filter('user_contactmethods', array($this, 'add_profile_fields'));

		// Register assets
		// add_action('wp_enqueue_scripts', array($this, 'register_assets'));
		add_action('admin_enqueue_scripts', array($this, 'admin_assets'));

		// Setup blocks
		$this->setup_blocks();

		// Setup widgets - hook will be handled by Bunyad_Widgets.
		$this->setup_widgets();

		// Optimizations
		require_once $this->path . 'inc/optimize.php';

		// Social Share
		require_once $this->path . 'inc/social-share.php';

		// Classic Editor features
		if (is_admin()) {
			require_once $this->path . 'inc/editor.php';
		}

		// Init menu helper classes
		Bunyad::menus();
		add_filter('bunyad_custom_menu_fields', array($this, 'custom_menu_fields'));

		// Translation: To be loaded via theme. Uncomment below to do a local translate.
		// load_plugin_textdomain(
		// 	'cheerup',
		// 	false,
		// 	basename($this->path) . '/languages'
		// );

		// Set metaboxes options directory path. Otherwise metabox files default to theme.
		add_filter('bunyad_metabox_options_dir', function() {
			return $this->path . 'metaboxes/options';
		});
	}

	/**
	 * Admin side only
	 */
	public function admin_init()
	{
		// Set active metaboxes
		Bunyad::options()->set_config('meta_boxes', array(
			// Enabled metaboxes and prefs - id is prefixed with _bunyad_ in init() method of lib/admin/meta-boxes.php
			array(
				'id'       => 'post-options', 
				'title'    => esc_html_x('Post Options', 'Admin: Meta', 'cheerup'), 
				'priority' => 'high', 
				'page'     => array('post'),
				'file'     => $this->path . 'metaboxes/post-options.php',
			),

			array(
				'id'       => 'page-options', 
				'title'    => esc_html_x('Page Options', 'Admin: Meta', 'cheerup'),
				'priority' => 'high', 
				'page'     => array('page'),
				'file'     => $this->path . 'metaboxes/page-options.php',
			),
		));

		Bunyad::factory('admin/meta-boxes');
	}

	/**
	 * Register assets
	 */
	public function admin_assets($hook)
	{
		wp_enqueue_style('cheerup-core-admin', $this->path_url . '/css/admin/common.css', array(), self::VERSION);
	}

	/**
	 * Setup Widgets
	 */
	public function setup_widgets()
	{
		/** @var Bunyad_Widgets $widgets  */
		$widgets = Bunyad::get('widgets');

		if (!is_object($widgets)) {
			return;
		}

		// Configure the object.
		$widgets->path   = $this->path;
		$widgets->prefix = 'CheerUp_Widgets_';
		$widgets->active = [
			'about', 'posts', 'cta', 'ads', 'social', 'subscribe', 'social-follow', 'twitter', 'slider'
		];
	}

	/**
	 * Setup blocks
	 */
	public function setup_blocks()
	{
		require_once $this->path . 'inc/shortcodes.php';
		require_once $this->path . 'inc/block.php';
		require_once $this->path . 'inc/blocks.php';

		// AJAX pagination handler for blocks
		require_once $this->path . 'inc/blocks-ajax.php';

		if (class_exists('Vc_Manager')) {
			require_once $this->path . 'inc/visual-composer.php';
		}

		// Default attributes shared amongst blocks
		$attribs = apply_filters('bunyad_default_block_attribs', array(
			'posts'   => 4,
			'offset'  => '',
			'heading' => '',
			'heading_type' => '',
			'css_heading_color' => '',
			'link'    => '',
			'cat'     => '',
			'cats'    => '', 
			'tags'    => '',
			'terms'   => '',
			'pagination' => '',
			'pagination_type' => '',
			'view_all'   => '',
			'taxonomy'   => '',
			'sort_order' => '',
			'sort_by'    => '',
			'post_format' => '',
			'post_type'   => '',
			'filters' => false,
			'css'     => '',
		));
		
		$attribs_blog = array_merge(
			$attribs,
			array(
				'type' => '', 
				'show_excerpt' => 1,
				'show_footer'  => '',
				'excerpt_length' => '',
				'grid_style'     => '',
			)
		);

		$attribs_slider = array_merge(
			$attribs,
			array(
				'type'            => '',
				'slider_parallax' => 0,
				'slider_width'    => '',
				'slider_static'   => 0,
				'css_grid_gap'    => ''
			)
		);
		
		/**
		 * Setup loop blocks - aliases for blog shortcode
		 */
		$listings = array(
			'loop-classic',
			'loop-default', // legacy
			'loop-1st-large',
			'loop-1st-large-list',
			'loop-1st-overlay',
			'loop-1st-overlay-list',
			'loop-1-2',
			'loop-1-2-list',
			'loop-1-2-overlay',
			'loop-1-2-overlay-list',
			'loop-list',
			'loop-grid',
			'loop-grid-3'
		);
		
		$loop_blocks = array();
		$loop_params = array(
			'render'  => locate_template('partials/blocks/blog.php'), 
			'attribs' => $attribs_blog,
		);
		
		foreach ($listings as $block) {
			$loop_blocks[$block] = $loop_params;
		}
		
		
		/**
		 * Register all the blocks
		 */
		Bunyad::get('cheerup_shortcodes')->add(array_merge($loop_blocks, array(
			'blog' => array(
				'render'  => locate_template('partials/blocks/blog.php'), 
				'attribs' => $attribs_blog,
			),

			'ts_slider' => array(
				'render'  => locate_template('partials/blocks/slider.php'),
				'attribs' => $attribs_slider,
			),
				
			'highlights' => array(
				'render'  => locate_template('partials/blocks/highlights.php'), 
				'attribs' => $attribs
			),
			
			'news_grid' => array(
				'render'  => locate_template('partials/blocks/news-grid.php'), 
				'attribs' => $attribs
			),
				
			'ts_ads' => array(
				'render'  => locate_template('partials/blocks/ts-ads.php'),
				'attribs' => array(
					'code'  => '', 
					'title' => '',
					'css'   => '',
				)
			)
		)));
	}

	/**
	 * Filter callback: Custom menu fields.
	 *
	 * Required for both back-end and front-end.
	 *
	 * @see Bunyad_Menus::init()
	 */
	public function custom_menu_fields($fields)
	{
		$fields = array(
			'mega_menu' => array(
				'label' => esc_html_x('Mega Menu', 'Admin', 'cheerup'),
				'element' => array(
					'type' => 'select',
					'class' => 'widefat',
					'options' => array(
						0 => esc_html_x('Disabled', 'Admin', 'cheerup'),
						'category' => esc_html_x('Enabled', 'Admin', 'cheerup'),
					)
				),
				'parent_only' => true,
				//'locations' => array('cheerup-main'),
			)
		);
	
		return $fields;
	}

    /**
	 * Filter callback: Add theme-specific profile fields
	 */
	public function add_profile_fields($fields)
	{
		$fields = array_merge((array) $fields, array(
			'bunyad_facebook'  => esc_html_x('Facebook URL', 'Admin', 'cheerup'),	
			'bunyad_twitter'   => esc_html_x('Twitter URL', 'Admin', 'cheerup'),
			'bunyad_gplus'     => esc_html_x('Google+ URL', 'Admin', 'cheerup'),
			'bunyad_instagram' => esc_html_x('Instagram URL', 'Admin', 'cheerup'),
			'bunyad_pinterest' => esc_html_x('Pinterest URL', 'Admin', 'cheerup'),
			'bunyad_bloglovin' => esc_html_x('BlogLovin URL', 'Admin', 'cheerup'),
			'bunyad_dribble'   => esc_html_x('Dribble URL', 'Admin', 'cheerup'),
			'bunyad_linkedin'  => esc_html_x('LinkedIn URL', 'Admin', 'cheerup'),
			'bunyad_tumblr'    => esc_html_x('Tumblr URL', 'Admin', 'cheerup'),
		));
		
		return $fields;
	}

	/**
	 * Singleton instance
	 * 
	 * @return CheerUp_Core
	 */
	public static function instance() 
	{
		if (!isset(self::$instance)) {
			self::$instance = new self;
		}
		
		return self::$instance;
	}
}

/**
 * Add notice and bail if there's an incompatible plugin active.
 * 
 * Note: Needed for outdated libs in ContentBerg Core.
 */
add_action('after_setup_theme', function() {
	$core_plugins = [
		'contentberg-core' => 'ContentBerg_Core',
	];

	$conflict = false;
	foreach ($core_plugins as $plugin => $class) {	

		// Check but don't auto-load this class.
		if (class_exists($class, false)) {
		
			add_action('admin_notices', function() use ($plugin) {

				// Path to plugin/plugin.php file.
				$plugin_file = $plugin . '/' . $plugin . '.php';

				include_once ABSPATH . 'wp-admin/includes/plugin.php';
				$plugin_full_path = WP_PLUGIN_DIR . '/' . $plugin_file;

				if (file_exists($plugin_full_path)) {
					$plugin_data = get_plugin_data($plugin_full_path);
				}
				else {
					$plugin_data = ['Name' => $plugin];
				}

				$message = sprintf(
					'Plugin %1$s is incompatible with current theme\'s Core Plugin. Please deactivate.',
					'<strong>' . esc_html($plugin_data['Name']) . '</strong>'
				);

				printf(
					'<div class="notice notice-error"><h3>Important:</h3><p>%1$s</p></div>',
					wp_kses_post($message)
				);
			});

			$conflict = true;
		}
	}

	if ($conflict) {
		return;
	}

	/**
	 * Initialize the plugin at correct hook.
	 */
	$cheerup = CheerUp_Core::instance();
	add_action('after_setup_theme', array($cheerup, 'init'));

}, 1);
