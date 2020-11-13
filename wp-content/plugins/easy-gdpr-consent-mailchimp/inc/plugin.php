<?php

namespace Sphere\EGCF;

/**
 * The Plugin Bootstrap and Setup
 */
class Plugin 
{
	
	/**
	 * Plugin version
	 */
	const VERSION = '1.0.1';

	public static $instance;
	public $dir_path;
	public $dir_url;
	public $plugin_file;

	/**
	 * @var ConsentLog
	 */
	public $consent_log;

	/**
	 * @var GeoLocation
	 */
	public $geolocation;

	/**
	 * @var Forms
	 */
	public $forms;

	/**
	 * Set it hooks on init
	 */
	public function init() 
	{
		$this->dir_path = plugin_dir_path($this->plugin_file);
		$this->dir_url  = plugin_dir_url($this->plugin_file);

		/**
		 * Admin only requires
		 */
		if (is_admin()) {

			require_once $this->dir_path . 'inc/admin.php';

			$admin = new Admin;
			$admin->init();

			// We don't want CMB2 in AJAX requests
			if (!wp_doing_ajax()) {
				require_once $this->dir_path . 'vendor/webdevstudios/cmb2/init.php';
			}

			// Path bug fix for cmb2 in composer
			add_filter('cmb2_meta_box_url', function() {
				return $this->dir_url . 'vendor/webdevstudios/cmb2';
			});
		}

		/**
		 * Setup and init common requires
		 */
		require_once $this->dir_path . 'inc/consent-log.php';
		require_once $this->dir_path . 'inc/geo-location.php';
		require_once $this->dir_path . 'inc/forms.php';

		$this->consent_log = new ConsentLog;
		$this->consent_log->init();

		$this->geolocation = new GeoLocation;
		$this->geolocation->init();

		// Init forms
		$this->forms = new Forms($this->consent_log, $this->geolocation);
		$this->forms->init();
		
		$this->register_hooks();
	}

	/**
	 * Setup hooks actions
	 */
	public function register_hooks()
	{
		add_action('wp_enqueue_scripts', array($this, 'register_assets'));

		// Setup Custom Post Type
		add_action('init', array($this, 'register_cpt'));

		// Translations
		add_action('plugins_loaded', array($this, 'load_textdomain'));
	}

	/**
	 * Register custom post type
	 */
	public function register_cpt()
	{
		$labels = array(
			'new_item'     => esc_html__('GDPR', 'sphere-egcf'),
			'add_new_item' => esc_html__('GDPR Consent Form', 'sphere-egcf'),
		);

		$args = array(
			'label'                 => esc_html__('GDPR Consent Forms', 'sphere-egcf'),
			'description'           => esc_html__('EGCF Popup Form', 'sphere-egcf' ),
			'labels'                => $labels,
			'supports'              => array('title'),
			'hierarchical'          => false,
			// Important to keep Yoast away
			'public'                => false,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 75,
			'show_in_admin_bar'     => false,
			'show_in_nav_menus'     => false,
			'can_export'            => true,
			'has_archive'           => false,
			'exclude_from_search'   => true,
			'publicly_queryable'    => false,
			'rewrite'               => false,
			'capability_type'       => 'post',
			'show_in_rest'          => false,
		);

		register_post_type('egcf_forms', $args);
	}

	/**
	 * Register the JS and CSS
	 */
	public function register_assets()
	{
		wp_enqueue_script(
			'egcf-scripts', 
			$this->dir_url . 'js/main.js', 
			array('jquery'),
			Plugin::VERSION
		);

		wp_enqueue_style(
			'egcf-style',
			$this->dir_url . 'css/main.css',
			array(),
			Plugin::VERSION
		);

		wp_localize_script(
			'egcf-scripts', 
			'EGCF_Plugin',
			array('ajax_url' => admin_url('admin-ajax.php'))
		);
	}

	/**
	 * Setup translations
	 */
	public function load_textdomain()
	{
		load_plugin_textdomain(
			'gdpr-consent-forms',
			false,
			$this->dir_path . '/languages/'
		);
	}

	/**
	 * @return \Sphere\EGC\Plugin
	 */
	public static function get_instance()
	{
		if (self::$instance == null) {
			self::$instance = new static();
		}

		return self::$instance;
	}
}