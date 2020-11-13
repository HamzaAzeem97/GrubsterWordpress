<?php
/**
 * Demo Importer - Requires Bunyad Demo Import plugin
 * 
 * @see Bunyad_Demo_Import
 */
class Bunyad_Theme_Admin_Import
{
	public $demos = [];
	public $admin_page;
	public $importer;
	
	public function __construct()
	{
		
		add_filter('bunyad_import_demos', [$this, 'import_source']);
		add_filter('pt-ocdi/importer_options', [$this, 'importer_options']);
		add_action('tgmpa_register', [$this, 'register_plugins']);
		
		// Demo configs
		$this->demos = [
			'miranda' => [
				'demo_name'                    => "Miranda/Life",
				'demo_description'             => 'Miranda CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/miranda/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/miranda.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/miranda.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/miranda-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/miranda-customizer.dat',
			],

			'lifestyle' => [
				'demo_name'                    => "Lifestyle",
				'demo_description'             => 'Lifestyle CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/lifestyle/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/lifestyle.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/lifestyle.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/lifestyle-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/lifestyle-customizer.dat',
			],

			'fashion' => [
				'demo_name'                    => "Fashion",
				'demo_description'             => 'Fashion CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/fashion/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/fashion.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/fashion.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/fashion-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/fashion-customizer.dat',
			],
				
			'bold' => [
				'demo_name'                    => "Bold Blog",
				'demo_description'             => 'Bold Blog CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/bold/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/bold.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/bold.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/bold-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/bold-customizer.dat',
			],
			
			'rovella' => [
				'demo_name'                    => "Rovella",
				'demo_description'             => 'Rovella CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/rovella/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/rovella.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/rovella.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/rovella-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/rovella-customizer.dat',
			],
				
			'general' => [
				'demo_name'                    => "General",
				'demo_description'             => 'General Purpose CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/general.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/general.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/general-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/general-customizer.dat',
			],
				
			'magazine' => [
				'demo_name'                    => "Magazine",
				'demo_description'             => 'Magazine CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/magazine/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/magazine.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/magazine.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/magazine-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/magazine-customizer.dat',
			],

			'beauty' => [
				'demo_name'                    => "Beauty",
				'demo_description'             => 'Beauty CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/beauty/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/beauty.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/beauty.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/beauty-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/beauty-customizer.dat',
			],
				
			'trendy' => [
				'demo_name'                    => "Trendy",
				'demo_description'             => 'Trendy CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/trendy/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/trendy.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/trendy.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/trendy-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/trendy-customizer.dat',
			],

			'fitness' => [
				'demo_name'                    => "Fitness",
				'demo_description'             => 'Fitness CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/fitness/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/fitness.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/fitness.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/fitness-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/fitness-customizer.dat',
			],

			'mom' => [
				'demo_name'                    => "Mom/Parents",
				'demo_description'             => 'Mom / Parenting Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/mom/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/mom.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/mom.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/mom-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/mom-customizer.dat',
			],

			'default' => [
				'demo_name'                    => "Default",
				'demo_description'             => 'General 2020 demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/default/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/default.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/default.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/default-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/default-customizer.dat',
			],
				
			'travel' => [
				'demo_name'                    => "Travel",
				'demo_description'             => 'Travel CheerUp Demo.',
				'demo_url'                     => 'https://cheerup.theme-sphere.com/travel/',
				'demo_image'                   => get_template_directory_uri() . '/inc/demos/travel.jpg',
				'local_import_file'            => get_template_directory() . '/inc/demos/travel.xml',
				'local_import_widget_file'     => get_template_directory() . '/inc/demos/travel-widgets.json',
				'local_import_customizer_file' => get_template_directory() . '/inc/demos/travel-customizer.dat',
			],

		];
		
		// Disable thumbnail creation to be done at the end
		add_filter('pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false');
		add_action('bunyad_import_done', [$this, 'update_options'], 10);
		add_action('bunyad_import_done', [$this, 'post_import'], 10, 2);
		
		// Register an informational section on customizer
		add_action('customize_register', [$this, 'customizer_info'], 12);
	}

	public function import_source() {
		return $this->demos;
	}
	
	public function importer_options($options) {
		//$options['aggressive_url_search'] = true;
		return $options;
	}
	
	/**
	 * Register a few extra plugins with TGMPA
	 */
	public function register_plugins()
	{	
		tgmpa([
			[
				'name'     => esc_html_x('Bunyad Demo Import', 'Admin', 'cheerup'),
				'slug'     => 'bunyad-demo-import',
				'required' => false,
				'version'  => '1.0.3',
				'source'   => get_template_directory() . '/lib/vendor/plugins/bunyad-demo-import.zip', // The plugin source
			],
		
			[
				'name'             => esc_html_x('Regenerate Thumbnails', 'Admin', 'cheerup'),
				'slug'             => 'regenerate-thumbnails',
				'required'         => false,
				'force_activation' => (!empty($_GET['importer']) ? true : false)  // Activate when user clicks install & activate button from importer.
			],
		], ['is_automatic' => true]);
	}

	/**
	 * Filter callback Post Process: Update Options
	 */
	protected function update_options($demo_id)
	{
		// Refresh options with the updated values by the importer.
		Bunyad::options()->init();
		Bunyad::options()
			->set('installed_demo', $demo_id)
			->update();
	}
	
	/**
	 * Post-import
	 * 
	 * @param string $demo_id
	 * @param OCDI_WXR_Importer $import
	 */
	public function post_import($demo_id, $import)
	{
		$menus_data = include get_theme_file_path('inc/demos/menus-data.php');

		// Import menus.
		if ($menus_data && class_exists('Bunyad_Demo_Import_Menus')) {
			$menus = new Bunyad_Demo_Import_Menus($menus_data);
			$menus->import();
		}

		// Unpublish hello world post.
		$hello = get_page_by_title('Hello world!', OBJECT, 'post');
		if ($hello && $hello->ID === 1) {
			wp_update_post([
				'ID'          => $hello->ID,
				'post_status' => 'draft'
			]);
		}

		// For magazine, we set homepage from imported content
		if ($demo_id == 'magazine' || $demo_id == 'lifestyle') {
			
			$home = get_page_by_title('Homepage');

			if (is_object($home)) {
				update_option('show_on_front', 'page');
				update_option('page_on_front', $home->ID);
				
				// Visual Composer home changes
				$this->post_process_vc($home->ID, $import);
			}

			// Process VC content of all homes.
			$other_homes = ['Homepage 2', 'Homepage 3', 'Homepage 4', 'Homepage 5'];
			foreach ($other_homes as $home) {
				$home = get_page_by_title($home);
				if (!$home) {
					continue;
				}

				$this->post_process_vc($home->ID, $import);
			}
		}
		
	}
	
	/**
	 * Remap Visual Composer block categories
	 * 
	 * @param integer  $page_id
	 * @param OCDI_WXR_Importer $import
	 */
	public function post_process_vc($page_id, $import) 
	{
		$import_data = $import->get_importer_data();
		$mapping     = $import_data['mapping'];
		
		// Get page content
		$page    = get_post($page_id);
		$content = $page->post_content;
		
		// Find all instances of cat="1" and replace as necessary
		preg_match_all('/cat="(\d+)"/', $content, $match);
		foreach ($match[1] as $key => $cat) {
			$new_id = $mapping['term_id'][$cat];
			
			if (empty($new_id)) {
				continue;
			}
			
			$content = str_replace($match[0][$key], 'cat="'. $new_id .'"', $content);
		}
		
		// Update the home
		wp_update_post([
			'ID'           => $page_id,
			'post_content' => $content
		]);
	}
	
	/**
	 * Customizer information
	 */
	public function customizer_info($wp_customizer)
	{
				
		/* @var $wp_customizer WP_Customize_Manager */
		$control = $wp_customizer->get_control('bunyad_import_info');
		
		// Plugin active
		if (class_exists('Bunyad_Demo_Import')) {
			$control->text = sprintf(
				esc_html_x('You can import demo settings or full demo content from %1$s this page %2$s.', 'Admin', 'cheerup'), 
				'<a href="' . esc_url(admin_url('themes.php?page=bunyad-demo-import')) .'">',
				'</a>'
			);
			
			return;
		}
		
		// Prompt for plugin activation
		$control->text = sprintf(
			esc_html_x('Please install and activate the required plugin "Bunyad Demo Import" from %1$sthis page%2$s.', 'Admin', 'cheerup'), 
			'<a href="' . esc_url(admin_url('themes.php?page=tgmpa-install-plugins')) .'">',
			'</a>'
		);
	}	
}

// init and make available in Bunyad::get('admin_import')
Bunyad::register('admin_import', [
	'class' => 'Bunyad_Theme_Admin_Import',
	'init'  => true
]);