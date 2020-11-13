<?php
/**
 * Blocks & Listings AJAX handlers
 */
class CheerUp_BlocksAjax
{
	public function __construct() 
	{
		
		add_action('wp_footer', array($this, 'update_data'));
		add_action('wp_enqueue_scripts', array($this, 'register_script'), 11);
		
		// Load bunyad blocks data
		if (!empty($_GET['_bunyad_act'])) {
			$this->ajax();
		}
	}
	
	/**
	 * Custom AJAX Implementation that supports cache plugins
	 */
	public function ajax() 
	{
		global $wp_rewrite;
		
		// Remove GET parameters from REQUEST_URL to prevent un-necessary vars
		$remove = array('_bunyad_act', 'block_id');
		
		if ($wp_rewrite->using_permalinks()) {
			$remove = array_merge($remove, array('page_id', 'paged'));
		}

		if (!$_GET['page_id']) {
			$_GET['page_id'] = 'home';
		}
		
		$_SERVER['REQUEST_URI'] = remove_query_arg($remove);

		// Emulate
		define('DOING_AJAX', true);
		
		// Render block 
		if ($_GET['_bunyad_act'] == 'block') {
			
			// Don't auto-redirect
			remove_action('template_redirect', 'redirect_canonical');
			
			// Using on template_redirect to ensure everything's loaded
			add_action('template_redirect', array($this, 'process_block'), 20);
		}

		// WP Core 404 filter
		add_filter('pre_handle_404', array($this, 'prevent_404'));
	}

	/**
	 * WordPress pagination on non-static home can result in 404 errors
	 */
	public function prevent_404($value) 
	{
		if (is_front_page()) {
			return true;
		}

		return $value;
	}
	
	public function register_script()
	{
		// Add custom ajax handler URL
		// Link to current page as WordPress pagination can get messed up if using root url
		wp_localize_script('cheerup-theme', 'Bunyad', array('custom_ajax_url' =>  remove_query_arg(array('page_id', 'paged')) ));
	}
	

	/**
	 * Process AJAX request to render the block
	 */
	public function process_block()
	{	
		if (empty($_GET['page_id']) OR empty($_GET['block_id'])) {
			wp_die('0');
		}

		/**
		 * Sets Layout
		 * 
		 * Bunyad_Core does it after header hook but we need it for realative_width calculation
		 * much before that happens.
		 */
		$layout = Bunyad::posts()->meta('layout_style', $_GET['page_id']);

		// Widgetized home is ALWAYS full-width
		if ($_GET['page_id'] == 'home' && Bunyad::options()->home_widgets && is_active_sidebar('cheerup-home')) {
			$layout = 'full';
		}

		if ($layout) {
			Bunyad::core()->set_sidebar(($layout == 'full' ? 'none' : $layout));
		}
		
		// Get blocks for specified page id
		$blocks   = $this->get_blocks();
		$block_id = $_GET['block_id'];
		
		// Block data missing?
		if (empty($blocks[ $block_id ])) {
			wp_die('0');
		}
		
		// Set page manually as paged is in URL regardless of front or not
		if (!empty($_GET['paged'])) {
			set_query_var(
				(is_front_page() ? 'page' : 'paged'),
				$_GET['paged']
			);
		}
		
		// Set required vars for block
		$atts = $blocks[ $block_id ];
		$atts['_block_id'] = $block_id;

		$tag  = $atts['_block_tag'];
		
		// For VC Support - See inc/block.php
		if (!empty($atts['_col_relative_width'])) {
			Bunyad::registry()->layout = array_merge(
				(array) Bunyad::registry()->layout, 
				array('col_relative_width' => $atts['_col_relative_width'])
			); 
		}

		echo Bunyad::get('cheerup_shortcodes')->render_block($atts, $tag);
		
		wp_die();
	}
	
	/**
	 * Get blocks data
	 */
	public function get_blocks() 
	{
		
		$blocks = get_option('_bunyad_blocks_data');
		return $blocks;
	}
	
	/**
	 * Store the in-memory data created by blocks, only needed for AJAX
	 * 
	 * @see Bunyad_Theme_Block::process()
	 */
	public function update_data() 
	{
	
		$blocks_data = Bunyad::registry()->blocks_data;
		if (empty($blocks_data)) {
			return;
		}
		
		// Store data. Disable autoload.
		update_option('_bunyad_blocks_data', $blocks_data, false);
	}
}

// init and make available in Bunyad::get('cheerup_blocks_ajax')
Bunyad::register('cheerup_blocks_ajax', array(
	'class' => 'CheerUp_BlocksAjax',
	'init' => true
));