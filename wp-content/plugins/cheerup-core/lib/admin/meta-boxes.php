<?php
/**
 * Metaboxes: Not to be used in themes
 */
class Bunyad_Admin_MetaBoxes
{
	private $prefix;
	private $option_prefix = '_bunyad_';  // _ underscore hides in custom fields

	private $cache = array();
	
	public function __construct()
	{
		add_action('add_meta_boxes', array($this, 'init'));
		add_action('admin_enqueue_scripts', array($this, 'add_assets'));
		add_action('save_post', array($this, 'save'));
		
		// Add metabox id prefix 
		$this->prefix = Bunyad::options()->get_config('theme_prefix') . '_';

		// Set meta options prefix if exists
		if (Bunyad::options()->get_config('meta_prefix')) {
			$this->option_prefix = Bunyad::options()->get_config('meta_prefix') . '_';
		}
	}
	
	/**
	 * Setup metaboxes
	 */
	public function init()
	{
		// get theme meta configs
		$meta = Bunyad::options()->get_config('meta_boxes');
		if (!is_array($meta)) {
			return;
		}
		
		// set some nifty defaults
		$defaults = array('page' => 'post', 'priority' => 'high', 'context' => 'normal');
		
		// add metaboxes
		foreach ($meta as $box) 
		{
			// add defaults
			$box = array_merge($defaults, $box);
			
			// prefix it
			$box['id']    = $this->prefix . $box['id'];			
			
			// fix screen
			$box['pages'] = is_array($box['page']) ? $box['page'] : array('post');
			
			foreach ($box['pages'] as $screen) {
				add_meta_box(
					$box['id'], 
					$box['title'], 
					array($this, 'render'),
					$screen,
					$box['context'],
					$box['priority'],
					array(
						'id' => $box['id'],
						'file' => !empty($box['file']) ? $box['file'] : ''
					)
				);
			}
		}
	}
	
	public function add_assets()
	{
		// nothing yet
	}
	
	public function get_box($box_id)
	{
		$meta = (array) Bunyad::options()->get_config('meta_boxes');
		foreach ($meta as $box) {
			if ($this->prefix . $box['id'] == $box_id) {
				return $box;
			}
		}
		
		return array();
	}
	
	/**
	 * Render the metabox - used via callback
	 * 
	 * @param array $post
	 * @param array $args
	 */
	public function render($post = null, $args = null)
	{
		if (!$args['id']) {
			return false;
		}
		
		// Add nonce for security
		if (!isset($this->cache['nonce'])) {
			wp_nonce_field('meta_save', '_nonce_' . $this->prefix . 'meta', false);
		}
		
		Bunyad::factory('admin/option-renderer'); // load 
		
		// Metabox file defined?
		$file = $args['args']['file'];

		if (empty($file)) {
			$file = sanitize_file_name(str_replace($this->prefix, '', $args['id'])) . '.php';
			$file = locate_template('admin/meta/' . $file);
		}

		$meta = Bunyad::factory('admin/meta-renderer', true); /* @var $meta Bunyad_Admin_MetaRenderer */

		// Render the template
		$meta->set_prefix($this->option_prefix)->template(
			array(),
			$file,
			Bunyad::posts()->meta(null, $post->ID), // populate all existing meta
			array(
				'post' => $post, 
				'box' => $this->get_box($args['id']), 
				'box_id' => $args['id']
			)
		);
	}
	
	/**
	 * Save custom post meta
	 * 
	 * @param integer $post_id
	 */
	public function save($post_id)
	{
		// Just an auto-save
		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
			return;
		}
	
		// Security checks
		if ((isset($_POST['_nonce_' . $this->prefix . 'meta']) && !wp_verify_nonce($_POST['_nonce_' . $this->prefix . 'meta'], 'meta_save')) 
			OR !current_user_can('edit_post', $post_id)) {
			return false;
		}
		
		// Load meta-box fields
		$options = [];
		if (!empty($_POST['bunyad_meta_box'])) {

			$path = apply_filters('bunyad_metabox_options_dir', get_theme_file_path('admin/meta/options'));
			$file = trailingslashit($path) . sanitize_file_name($_POST['bunyad_meta_box']) . '.php';
			
			if (!empty($file)) {
				include $file;
				
				$options = $this->_build_meta_map($options);
			}
		}
		
		// Save all meta data with the right prefix
		foreach ($_POST as $key => $value) {
			
			// Not a meta value with our desired prefix? Skip.
			if (strpos($key, $this->option_prefix) !== 0) {
				continue;
			}

			// Current value
			$meta = get_post_meta($post_id, $key, true);

			if ($value == '_default') {
				$value = '';
			}

			// The string "0" is valid value, but false/integer 0 aren't.
			$is_empty    = ($value == '');
			$is_default  = false;

			// Check if value matches default in options array. The default is in key 'value'.
			if (!empty($options[$key]) && array_key_exists('value', $options[$key])) {
				$is_default = ($value == $options[$key]['value']);
			}
			
			// Add or update metadata
			if (!$is_default && !$is_empty && $meta != $value) {
				
				// allowed_html available for this value in options?
				if (!empty($options[$key]) && array_key_exists('allowed_html', $options[$key])) {
					$filtered_value = addslashes(
						wp_kses(stripslashes($value), $options[$key]['allowed_html'])
					);
				}
				else {
					// Default filtered values
					$filtered_value = (current_user_can('unfiltered_html') ? $value : wp_filter_post_kses($value));
				}
				
				// filtered_value is expected to have slashes
				update_post_meta($post_id, $key, $filtered_value);

			}
			else {

				// get_post_meta() returns '' when it can't find a record.
				$meta_exists = ($meta !== '');

				// Remove empty or default values
				if ($meta_exists && ($is_empty OR $is_default)) {
					delete_post_meta($post_id, $key);
				}
			}
		}
	}
	
	/**
	 * Build meta options array using field name as key with the prefix
	 * 
	 * @param array $options
	 */
	public function _build_meta_map($options)
	{
		$map = array();
		
		foreach ($options as $option) {
			$map[ $this->option_prefix . $option['name'] ] = $option;
		}
		
		return $map;
	}
}