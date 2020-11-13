<?php
/**
 * Theme related configuration and modifiers for Visual Composer page builder
 */
class CheerUp_VisualComposer
{
	public $official = false;
	
	public function __construct()
	{
		add_action('vc_before_init', array($this, 'vc_setup'));
		add_action('vc_after_init', array($this, 'after_setup'));
		
		add_filter('vc_shortcodes_css_class', array($this, 'custom_classes'), 10, 3);
		
		// Row filters
		add_filter('vc_theme_after_vc_row', array($this, 'after_vc_row'), 10, 2);
		add_filter('vc_theme_after_vc_row_inner', array($this, 'after_vc_row'), 10, 2);
		
		// Add correct markup for widgets
		add_filter('vc_shortcode_output', array($this, 'fix_widget_titles'), 10, 2);
		add_filter('wpb_widget_title', array($this, 'vc_widget_titles'), 10, 2);
		
		Bunyad::registry()->layout = array(
			'row_depth' => 0,
			'row_open'  => false,
			'row_cols'  => array(),
			'row_parent_open' => false
		);
		
		// Fix page template for visual composer
		add_action('save_post', array($this, 'set_page_template'));
		
		// Load pre-made templates
		add_filter('vc_load_default_templates', array($this, 'load_templates'));

		// Disable Autoptimize - vc_is_page_editable() isn't reliable at this point.
		if (!empty($_GET['vc_editable'])) {
			add_filter('autoptimize_filter_noptimize', '__return_true');
		}
	}
	
	/**
	 * Action callback: Setup at VC init
	 */
	public function vc_setup()
	{
		add_filter('template_include',  array($this, 'set_template_front'));

		// Set as theme and disable update nag - use local updates
		vc_set_as_theme(true);
		
		// Using official copy?
		if (function_exists('vc_license')) {
			$license = vc_license();
			
			if (method_exists($license, 'isActivated')) {
				$this->official = $license->isActivated();
			}
		}
		
		/* Disables product activation as a side effect
		if (function_exists('vc_manager')) {
			$vc = vc_manager();
			$not_update_page = (empty($_GET['page']) OR $_GET['page'] != 'vc-updater');
			
			if (!$this->official && method_exists($vc, 'disableUpdater') && $not_update_page) {
				//$vc->disableUpdater(true);
			}
		}*/
		
		// Set shortcode directory
		vc_set_shortcodes_templates_dir(
			locate_template('partials/blocks/vc-templates')
		);
		
		// Register blocks
		add_action('init', array($this, 'register_blocks'), 12);
		
		// Remove un-necessary unless official copy
		if (!$this->official) {
			// Remove non-supported blocks
			add_action('admin_init', array($this, 'remove_unsupported'));
		
			// Remove un-needed menu items
			add_action('admin_menu', array($this, 'remove_admin_menu'), 99);
		}
		
		if (is_admin()) {
			// Activation cta
			add_action('gettext', array($this, 'vc_cta'), 99, 3);
			
			// Remove confusing welcome on theme plugin activation
			remove_action('admin_init', 'vc_page_welcome_redirect');
		}
		
		// No edit with visual composer link in admin bar
		remove_action('admin_bar_menu', array(vc_frontend_editor(), 'adminBarEditLink'), 1000);
	}
	
	/**
	 * Action callback:  Run after VC is setup
	 */
	public function after_setup()
	{
		remove_action('wp_head', array(visual_composer(), 'addMetaData'));
		// wpb-js-composer class is needed by accordions and so on
		//remove_action('body_class', array(visual_composer(), 'bodyClass'));

		// Remove some auto-update feature unless official activated
		if (!$this->official && function_exists('vc_manager')) {
			$updater = vc_manager()->updater();
			
			if (method_exists($updater, 'updateManager')) {
				$update_manager = $updater->updateManager();
			}
			
			if (is_object($update_manager)) {
				remove_filter('pre_set_site_transient_update_plugins', array($update_manager, 'check_update'));
				
				if (function_exists('vc_plugin_name')) {
					remove_action('in_plugin_update_message-' . vc_plugin_name(), array($update_manager, 'addUpgradeMessageLink'));
				}
			}
		}

		// This is an anti-pattern to have a recurring non-dismissable notification. Won't pass theme review.
		// So we'll disable this.
		if (function_exists('vc_license') && $object = vc_license()) {
			if (is_object($object)) {
				remove_action('admin_notices', [$object, 'adminNoticeLicenseActivation']);
			}
		}
	}
	
	/**
	 * Filter callback: Modify default WP widget titles output from VC
	 * 
	 * @param unknown_type $content
	 */
	public function fix_widget_titles($content, $shortcode)
	{
		// only work on vc_wp_* shortcodes - ignore the rest
		if (is_object($shortcode) && method_exists($shortcode, 'settings') && !strstr($shortcode->settings('base'), 'vc_wp_')) {
			return $content;
		}
		
		return preg_replace('#<h2 class="widgettitle">(.+?)</h2>#', '<h5 class="widget-title"><span>\\1</span></h5>', $content);
	}
	
	/**
	 * Filter callback: Modify default VC widgets title output
	 * 
	 * @param string $output
	 * @param array $params
	 */
	public function vc_widget_titles($output, $params = array()) 
	{
		if (empty($params['title'])) {
			return $output;
		}

		$output = '<div class="block-head-b"><h5 class="wpb_heading title ' . $params['extraclass'] . '">' . $params['title'] . '</h3></div>';
		
		return $output;
	}
	
	/**
	 * Action callback: Set proper page template on save for Visual Composer pages
	 * 
	 * @param integer $post_id
	 */
	public function set_page_template($post_id)
	{
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
		
		// Only if can edit page
		if (!current_user_can('edit_page', $post_id)) {
			return;
		}

		// Perhaps a partial request in Gutenberg.
		if (empty($_POST['post_content'])) {
			return;
		}
		
		// Has visual composer content? wpb_vc_js_status=true would be limiting
		if (!empty($_POST['post_type']) && $_POST['post_type'] == 'page' && strstr($_POST['post_content'], '[vc_row')) {
			
			// set page template
			update_post_meta($post_id, '_wp_page_template', 'page-blocks.php');
		}
	}

	/**
	 * Filter callback: Set page template from VC frontend
	 */
	public function set_template_front($template) 
	{
		if (function_exists('vc_is_inline') && vc_is_inline()) {
			
			$post = get_post();

			if (is_page()) {
				$current = basename($template);

				if ($current !== 'page-blocks.php') {
					$template = locate_template('page-blocks.php');
				}
			}
		}

		return $template;
	}
	
	/**
	 * Register blocks with Visual Composer
	 */
	public function register_blocks()
	{		
		// Modifications are done both in front and backend
		$this->modify_blocks();

		// Only register custom blocks in admin or when front-end editor is called
		if (!is_admin() && !vc_is_frontend_editor() && !vc_is_page_editable()) {
			return;
		}

		// Map all the blocks
		$blocks = Bunyad::get('cheerup_blocks')->get_blocks();

		foreach ($blocks as $key => $block) {
			vc_map($block);
		}		
	}

	/**
	 * Modify default Visual Composer elements to behave correctly
	 */
	public function modify_blocks()
	{
		// change weight of VC row to be highest in order
		vc_map_update('vc_row', array('weight' => 50));
		
		// Main Sidebar + sticky
		vc_add_param('vc_widget_sidebar', array(
			'type' => 'checkbox',
			'heading' => esc_html_x('Is Sidebar?', 'Admin', 'cheerup'),
			'param_name' => 'is_sidebar',
			'value' => array(
				esc_html_x('Yes', 'Admin', 'cheerup') => 1
			),
			'std' => 1
		));
		
		vc_add_param('vc_widget_sidebar', array(
			'type' => 'checkbox',
			'heading' => esc_html_x('Sticky Sidebar?', 'Admin', 'cheerup'),
			'param_name' => 'is_sticky',
			'value' => array(
				esc_html_x('Yes', 'Admin', 'cheerup') => 1
			),
			'std' => 1
		));

		// Add font option to text
		vc_add_param('vc_column_text', array(
			'type' => 'checkbox',
			'heading' => esc_html_x('Use fonts and style same as Single/Post?', 'Admin', 'cheerup'),
			'param_name' => 'single_style',
			'value' => array(
				esc_html_x('Yes, Use Single/Post styles.', 'Admin', 'cheerup') => 1
			),
			'std' => 0,
			'weight' => 10,
		));
	}
	
	/**
	 * Remove unsupported elements from Visual Composer
	 */
	public function remove_unsupported()
	{	
		// Remove elements
		vc_remove_element('vc_posts_slider');
		vc_remove_element('vc_gallery');

		// Remove params
		//vc_remove_param('vc_column', 'offset');
	}
	
	/**
	 * Remove unsupported menu items
	 */
	public function remove_admin_menu()
	{
		remove_submenu_page(VC_PAGE_MAIN_SLUG, 'edit.php?post_type=' . rawurlencode( Vc_Grid_Item_Editor::postType() ), '');
	}
	
	/**
	 * Add a translated message
	 */
	public function vc_cta($translated, $text, $domain)
	{
		if ($domain !== 'js_composer') {
			return $translated;
		}
		
		if (strstr($text, 'automatic updates and unlock premium support')) {
			$translated = 'WPBakery Page Builder plugin in this theme has updates & support offered by ThemeSphere. But if you like the plugin and want support from WPBakery instead, please %sbuy a copy%s.';
		}
		
		if (strstr($text, 'In order to receive all benefits of')) {
			$translated = 'WPBakery Page Builder plugin in this theme has updates & support offered by %1$sThemeSphere%2$s. But if you like the plugin and want premium support & template library from WPBakery, please buy a copy and then Activate below.';
		}
			
		return $translated;
	}
	
	/**
	 * Store VC Row info
	 * 
	 * @param array $atts
	 * @param string|null $content
	 */
	public function after_vc_row($atts, $content = null)
	{
		$layout = Bunyad::registry()->layout;
									
		$layout['row_open'] = false;

		// Reduce depth each time a row is closed - at depth=0 is the parent row
		$layout['row_depth']--;
		
		$row_id = $layout['row_depth'];
		
		// Inner row closed, update relative widths to the last column of currently open row
		if ($row_id) {
			$layout['col_relative_width'] = $layout[$row_id]['col_relative_width'];
		}
				
		if ($layout['row_parent_open'] && $layout['row_depth'] == 0) {
			$layout['row_parent_open'] = false;
			$layout['row_cols'] = array();
			$layout['row_depth'] = 0;
		}
				
		Bunyad::registry()->layout = $layout;
	}

	
	/**
	 * Filter callback: Change default classes for vc_row and vc_column to be 
	 * compatible with the CSS classes used in the theme. 
	 * 
	 * @param string $classes
	 * @param string $tag
	 * @param array $atts
	 */
	public function custom_classes($classes, $tag, $atts = array())
	{
		$layout = Bunyad::registry()->layout;
		
		if ($tag == 'vc_row' OR $tag == 'vc_row_inner') {
			
			// increase depth if inside a parent row
			if ($layout['row_parent_open']) {
				$layout['row_depth']++;
			}
			
			// parent row
			if ($tag == 'vc_row') {
				$layout['row_parent_open'] = true;
				$layout['row_depth']++;
			}
			
			$layout['row_open'] = true;
			Bunyad::registry()->layout = $layout;

		}
		
		// Front-end editing in process?
		if (vc_is_frontend_editor() OR vc_is_page_editable()) {
			
			if ($tag == 'vc_row' OR $tag == 'vc_row_inner') {
				$classes = str_replace('vc_row-fluid', 'ts-row blocks', $classes);
			}
		}
		else {
			// Replacemenets for rows - add block too
			if ($tag == 'vc_row' OR $tag == 'vc_row_inner') {
				$classes = trim(str_replace('vc_row-fluid', '', $classes));
				$classes = preg_replace('/(^|\s)vc_row(\s|$)/', '\\1ts-row blocks cf\\2', $classes);
			}
		}
		
		/**
		 * Change column classes and store column info
		 */
		if ($tag == 'vc_column' OR $tag == 'vc_column_inner') {
			
			/**
			 * Change the class
			 */
			preg_match('/vc_col-sm-(\d{1,2})/', $classes, $matches);
			
			// Change the class
			$classes = str_replace($matches[0], $matches[0] . ' col-' . $matches[1], $classes);
			

			/**
			 * Store current column width - relative to a parent if any
			 */

			// A row is open?
			if ($layout['row_open']) {
				
				// Set column width relative to the top-parent column - in grid format
				$layout['col_width'] = $matches[1];
				
				$row_id = $layout['row_depth'];
				
				// Column of top-level row?
				if ($layout['row_depth'] == 1) {
					
					$layout['col_relative_width'] = ($layout['col_width'] / 12);
				}
				else {
					// Column of a row_inner
					
					// Add to current row columns
					if ($layout['row_open']) {
						array_push($layout['row_cols'], $matches[1]);
					}

					
					// Calculate relative to the parent column
					$layout['col_relative_width'] = ($layout[$row_id - 1]['col_parent_width'] / 12) * ($layout['col_width'] / 12);
				}
									
				// Save top-level column width for inner rows
				$layout[$row_id]['col_parent_width']   = $layout['col_width'];
				$layout[$row_id]['col_relative_width'] = $layout['col_relative_width'];
				
				// Save layout array in registry
				Bunyad::registry()->layout = $layout;
			}
			
		}
		
		return $classes;
	}

	/**
	 * Load premade layouts for visual composer
	 */
	public function load_templates($data)
	{
		$file = get_theme_file_path('inc/vc-templates.php');
		if (!$file) {
			return $data;
		}

		$templates = include $file; // get_theme_file_path() above
		
		return array_merge($templates, (array) $data);
	}

	/**
	 * Helper: Decode textarea content
	 */
	public function textarea_decode($content)
	{
		// VC stores it encoded so it has to be decoded
		return rawurldecode(base64_decode($content));
	}
	
}

/**
 * Compat functions to turn VC after_ functions - for rows - into proper filters
 */

if (!function_exists('vc_theme_after_vc_row')) {
	function vc_theme_after_vc_row($atts, $content = null) {
		$content = apply_filters('vc_theme_after_vc_row', $content, $atts);
		
		if (!empty($content)) {
			return $content;
		}
	}
}

if (!function_exists('vc_theme_after_vc_row_inner')) {
	function vc_theme_after_vc_row_inner($atts, $content = null) {
		$content = apply_filters('vc_theme_after_vc_row_inner', $content, $atts);
		
		if (!empty($content)) {
			return $content;
		}
	}
}

// generic Class for Widgets mapping as VC elements
if (!class_exists('Bunyad_VC_Widget')) {
	class Bunyad_VC_Widget extends WPBakeryShortCode {}
}

// init and make available in Bunyad::get('cheerup_vc')
Bunyad::register('cheerup_vc', array(
	'class' => 'CheerUp_VisualComposer',
	'init' => true
));
