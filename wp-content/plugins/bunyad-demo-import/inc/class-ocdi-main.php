<?php
/**
 * Main One Click Demo Import plugin class/file.
 *
 * @package ocdi
 */

// Include files.
require PT_OCDI_PATH . 'inc/class-ocdi-helpers.php';
require PT_OCDI_PATH . 'inc/class-ocdi-importer.php';
require PT_OCDI_PATH . 'inc/class-ocdi-widget-importer.php';
require PT_OCDI_PATH . 'inc/class-ocdi-customizer-importer.php';
require PT_OCDI_PATH . 'inc/class-ocdi-logger.php';

/**
 * One Click Demo Import class, so we don't have to worry about namespaces.
 */
class Bunyad_Demo_Import {

	/**
	 * @var $instance the reference to *Singleton* instance of this class
	 */
	private static $instance;

	/**
	 * Private variables used throughout the plugin.
	 */
	public $importer, 
		$import_files, 
		$logger, 
		$log_file_path, 
		$selected_index, 
		$selected_import_files, 
		$microtime, 
		$frontend_error_messages, 
		$ajax_call_number;
	
	// + Bunyad
	public 	$admin_page, $demos; 


	/**
	 * Returns the *Singleton* instance of this class.
	 *
	 * @return Bunyad_Demo_Import the *Singleton* instance.
	 */
	public static function getInstance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}


	/**
	 * Class construct function, to initiate the plugin.
	 * Protected constructor to prevent creating a new instance of the
	 * *Singleton* via the `new` operator from outside of this class.
	 */
	protected function __construct() {

		// Actions
		add_action( 'after_setup_theme', array( $this, 'setup_plugin_with_filter_data' ) );
		add_action( 'plugins_loaded', array( $this, 'load_textdomain' ) );
		
		// + Bunyad
		
		// Import AJAX handler
		add_action('wp_ajax_bunyad_import_demo', array($this, 'import'));
		
		// Register scripts
		add_action('admin_enqueue_scripts', array( $this, 'register_assets'));
		
		// Add Menu page
		add_action('admin_menu', array($this, 'menu_setup'));
		
	}
	
	// + Bunyad
	
	/**
	 * Importer function
	 */
	public function import() 
	{	
		do_action('bunyad_import_pre_import');
		
		// 350MB should be sufficient
		@ini_set('memory_limit', '350M');
		
		// Check for regenerate thumbnails
		if ($_POST['import_type'] == 'full' && !class_exists('RegenerateThumbnails')) {	 
				
			ob_start();
			?>
			<div class="notice error">
				<p>  
				<?php 
					echo esc_html_x('The importer needs to install Regenerate Thumbnails plugin to continue.', 'Admin', 'pt-ocdi'); ?>
				</p>
				
				<form method="post" action="<?php echo esc_url(admin_url('admin.php?page=tgmpa-install-plugins&importer=1')); ?>" target="_blank">
					<input type="hidden" name="tgmpa-page" value="tgmpa-install-plugins" />
					<input type="hidden" name="plugin_status" value="all" />
					<input type="hidden" name="plugin[]" value="regenerate-thumbnails" />
					<input type="hidden" name="action" value="tgmpa-bulk-install" />
					<?php wp_nonce_field('bulk-plugins'); ?>
					<p><input type="submit" class="button" value="<?php echo esc_attr__('Install'); ?>" /></p>
				</form>

			</div>
			<?php
			
			wp_send_json(array('message' => ob_get_clean()));
			return;
		}

		// Check for XMLReader availability or a FATAL error maybe caused.
		if (!class_exists('XMLReader')) {
			ob_start();
			?>
			<div class="notice error">
				<p>  
				<?php 
					echo esc_html('Missing XMLReader. Installed PHP is missing an essential feature for any importer to work. Please contact your webhost to enable xmlreader in PHP.', 'pt-ocdi'); ?>
				</p>
			</div>
			<?php

			wp_send_json(array('message' => ob_get_clean()));
			return;
		}

		// Verify if the AJAX call is valid (checks nonce and current_user_can).
		OCDI_Helpers::verify_ajax_call();

		// Is this a new AJAX call to continue the previous import?
		$use_existing_importer_data = $this->get_importer_data();

		if (!$use_existing_importer_data) {

			// Set the AJAX call number.
			$this->ajax_call_number = empty( $this->ajax_call_number ) ? 0 : $this->ajax_call_number;

			// Error messages displayed on front page.
			$this->frontend_error_messages = '';

			// Create a date and time string to use for demo and log file names.
			$demo_import_start_time = date(apply_filters( 'pt-ocdi/date_format_for_file_names', 'Y-m-d__H-i-s' ));

			// Define log file path.
			$this->log_file_path = OCDI_Helpers::get_log_path($demo_import_start_time);

			// Get selected file index or set it to 0.
			$this->selected_index = empty($_POST['demo_id']) ? 0 : $_POST['demo_id'];
		}
		
		$demo = $this->demos[ $this->selected_index ];
		
		/**
		 * Import content
		 */
		if ($_POST['import_type'] == 'full') {
			
			// Import content
			$this->frontend_error_messages .= $this->import_content($demo['local_import_file']);
			
			// Import widgets
			$this->import_widgets($demo['local_import_widget_file']);
		}

		/**
		 * Import customizer settings
		 */
		$this->import_customizer($demo['local_import_customizer_file']);

		// Display final messages (success or error messages).
		if (empty($this->frontend_error_messages)) {
			$response['message'] = '<div class="notice notice-success"><p>All done! Please deactivate and delete the "Bunyad Demo Import" plugin now.</p></div>';
			
			if ($_POST['import_type'] == 'full') {
				
				ob_start();
				?>
				
				<div class="notice notice-success">
					<p><?php echo esc_html__('Import is successful! Just two more steps:', 'pt-ocdi'); ?></p>
					<ol>
						<li><a href="<?php echo admin_url('tools.php?page=regenerate-thumbnails'); ?>" target="_blank"><?php echo esc_html__('Run Re-generate Thumbnails.', 'pt-ocdi'); ?></a></li>
						<li>Once all thumbnails are regenerated, "Bunyad Demo Import" and "Regenerate Thumbnails" plugins aren't needed anymore. De-activate and remove them.</li>
					</ol>
				</div>
				
				<?php
				$response['message'] = apply_filters('bunyad_import_success_message', ob_get_clean());
			}
			
		}
		else {
			$response['message'] = $this->frontend_error_messages . '<br>';
			$response['message'] .= sprintf(
				esc_html__( '%1$sThe demo import has finished, but there were some import errors.%2$sMore details about the errors can be found in this %3$s%5$slog file%6$s%4$s%7$s', 'pt-ocdi'),
				'<div class="notice  notice-error"><p>',
				'<br>',
				'<strong>',
				'</strong>',
				'<a href="' . OCDI_Helpers::get_log_url($this->log_file_path) .'" target="_blank">',
				'</a>',
				'</p></div>'
			);
		}

		do_action('bunyad_import_done', $this->selected_index, $this->importer);
		
		wp_send_json($response);
	}

	/**
	 * Enqueue admin scripts (JS and CSS)
	 *
	 * @param string $hook holds info on which admin page you are currently loading.
	 */
	public function register_assets($hook) {

		// Enqueue the scripts only on the plugin page.
		if ($this->admin_page === $hook) {
			wp_enqueue_script('bunyad-import', PT_OCDI_URL . 'assets/js/main.js', array('jquery'), PT_OCDI_VERSION);

			wp_localize_script('bunyad-import', 'Bunyad_Import',
				array(
					'ajax_url'     => admin_url('admin-ajax.php'),
					'ajax_nonce'   => wp_create_nonce('ocdi-ajax-verification'),
				)
			);
			
			wp_enqueue_style('bunyad-import-css', PT_OCDI_URL . 'assets/css/main.css', array(), PT_OCDI_VERSION);
		}
	}
	
	/**
	 * Add the menu option
	 */
	public function menu_setup()
	{
		$this->admin_page = add_submenu_page(
			'themes.php', esc_html__('Demo Import', 'pt-ocdi'), esc_html__('Import Demos', 'pt-ocdi'), 'import', 'bunyad-demo-import', array($this, 'admin_page')
		);
	}
	
	/**
	 * Admin page output - can be overridden by the theme using bunyad_import_admin_page hook
	 */
	public function admin_page()
	{
		ob_start();
		do_action('bunyad_import_admin_page');
		$content = ob_get_clean();
		
		if (!empty($content)) {
			echo $content;
			return;
		}
		
		?>
		
		<div class="wrap bunyad-import">
			<h1><?php echo esc_html_x('Import Theme Demos', 'Admin', 'pt-ocdi'); ?></h1>
			
			<div class="notice notice-large intro-text">
				<h3>Using Importer:</h3>
				<p>We has several demos that can let you get quickly started with your setup. There are two type of imports available.</p>
				
				<ol>
					<li><p><strong>Settings Only</strong>: This will only import customizer settings but it will not import posts, menus, pages etc.</p></li>
					<li><p><strong>Full Content</strong>: Will import posts, menus, pages, images but it should only be used on fresh or test installs. It requires about 1-5 minutes to complete.</p></li>
				</ol>
				<p>
					<strong><?php echo esc_html_x('NOTE:', 'Admin', 'pt-ocdi'); ?></strong>
					DO NOT use "Full Content" option if your already have existing posts on your site. You cannot undo an import - create a backup if you really wish to use it on an existing site.
				</p> 
			</div>
			
			<div class="ajax-response"></div>
			
			<div class="theme-browser">
			<?php foreach ($this->demos as $id => $demo): ?>
			
				<div class="theme">
					<a class="theme-screenshot" href="<?php echo esc_url($demo['demo_url']); ?>" target="_blank">
						<img src="<?php echo esc_url($demo['demo_image']); ?>" />
					</a>
					
					<div class="theme-id-container">
						<h3 class="theme-name"><?php echo esc_html($demo['demo_name']); ?></h3>
						<div class="theme-actions">
							<select name="import_type">
								<option value="settings"><?php echo esc_html_x('Settings Only', 'Admin', 'pt-ocdi'); ?></option>
								<option value="full"><?php echo esc_html_x('Full Content', 'Admin', 'pt-ocdi'); ?></option>
							</select>
							<a class="button import" data-id="<?php echo esc_attr($id); ?>">Import</a>
						</div>
					</div>
				</div>
			
			<?php endforeach; ?>
			</div>
		</div>
		
		<?php
		
	}
	
	// / Bunyad

	/**
	 * Private clone method to prevent cloning of the instance of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __clone() {}


	/**
	 * Private unserialize method to prevent unserializing of the *Singleton* instance.
	 *
	 * @return void
	 */
	private function __wakeup() {}


	/**
	 * Import content from an WP XML file.
	 *
	 * @param string $import_file_path path to the import file.
	 */
	public function import_content( $import_file_path ) {

		$this->microtime = microtime( true );

		// This should be replaced with multiple AJAX calls (import in smaller chunks)
		// so that it would not come to the Internal Error, because of the PHP script timeout.
		// Also this function has no effect when PHP is running in safe mode
		// http://php.net/manual/en/function.set-time-limit.php.
		// Increase PHP max execution time.
		set_time_limit( apply_filters( 'pt-ocdi/set_time_limit_for_demo_data_import', 300 ) );

		// Disable import of authors.
		add_filter( 'wxr_importer.pre_process.user', '__return_false' );

		// Check, if we need to send another AJAX request and set the importing author to the current user.
		add_filter( 'wxr_importer.pre_process.post', array( $this, 'new_ajax_request_maybe' ) );

		// Disables generation of multiple image sizes (thumbnails) in the content import step.
		if ( ! apply_filters( 'pt-ocdi/regenerate_thumbnails_in_content_import', true ) ) {
			add_filter( 'intermediate_image_sizes_advanced',
				function() {
					return null;
				}
			);
		}
		
		//register_shutdown_function(array($this, 'shutdown_save_state'));

		// Import content.
		if ( ! empty( $import_file_path ) ) {
			ob_start();
				$this->importer->import( $import_file_path );
			$message = ob_get_clean();

			// Add this message to log file.
			$log_added = OCDI_Helpers::append_to_file(
				$message . PHP_EOL . esc_html__( 'Max execution time after content import = ' , 'pt-ocdi' ) . ini_get( 'max_execution_time' ),
				$this->log_file_path,
				esc_html__( 'Importing content' , 'pt-ocdi' )
			);
		}

		// Delete content importer data for current import from DB.
		delete_transient( 'ocdi_importer_data' );

		// Return any error messages for the front page output (errors, critical, alert and emergency level messages only).
		return $this->logger->error_output;
	}

	/**
	 * Save current state on unexpected shutdowns
	 */
	public function shutdown_save_state()
	{
		$transient = get_transient('ocdi_importer_data');
		if (!empty($transient)) {
			$this->set_importer_data();
		}
	}

	/**
	 * Import widgets from WIE or JSON file.
	 *
	 * @param string $widget_import_file_path path to the widget import file.
	 */
	public function import_widgets( $widget_import_file_path ) {

		// Widget import results.
		$results = array();

		// Create an instance of the Widget Importer.
		$widget_importer = new OCDI_Widget_Importer();

		// Import widgets.
		if ( ! empty( $widget_import_file_path ) ) {

			// Import widgets and return result.
			$results = $widget_importer->import_widgets( $widget_import_file_path );
		}

		// Check for errors.
		if ( is_wp_error( $results ) ) {

			// Write error to log file and send an AJAX response with the error.
			OCDI_Helpers::log_error_and_send_ajax_response(
				$results->get_error_message(),
				$this->log_file_path,
				esc_html__( 'Importing widgets', 'pt-ocdi' )
			);
		}

		ob_start();
			$widget_importer->format_results_for_log( $results );
		$message = ob_get_clean();

		// Add this message to log file.
		$log_added = OCDI_Helpers::append_to_file(
			$message,
			$this->log_file_path,
			esc_html__( 'Importing widgets' , 'pt-ocdi' )
		);
	}


	/**
	 * Import customizer from a DAT file, generated by the Customizer Export/Import plugin.
	 *
	 * @param string $customizer_import_file_path path to the customizer import file.
	 */
	public function import_customizer( $customizer_import_file_path ) {

		// Try to import the customizer settings.
		$results = OCDI_Customizer_Importer::import_customizer_options( $customizer_import_file_path );

		// Check for errors.
		if ( is_wp_error( $results ) ) {

			// Write error to log file and send an AJAX response with the error.
			OCDI_Helpers::log_error_and_send_ajax_response(
				$results->get_error_message(),
				$this->log_file_path,
				esc_html__( 'Importing customizer settings', 'pt-ocdi' )
			);
		}

		// Add this message to log file.
		$log_added = OCDI_Helpers::append_to_file(
			esc_html__( 'Customizer settings import finished!', 'pt-ocdi' ),
			$this->log_file_path,
			esc_html__( 'Importing customizer settings' , 'pt-ocdi' )
		);
	}


	/**
	 * Setup other things in the passed wp action.
	 *
	 * @param string $action the action name to be executed.
	 * @param array  $selected_import with information about the selected import.
	 */
	private function do_import_action( $action, $selected_import ) {

		ob_start();
			do_action( $action, $selected_import );
		$message = ob_get_clean();

		// Add this message to log file.
		$log_added = OCDI_Helpers::append_to_file(
			$message,
			$this->log_file_path,
			$action
		);
	}


	/**
	 * Check if we need to create a new AJAX request, so that server does not timeout.
	 *
	 * @param array $data current post data.
	 * @return array
	 */
	public function new_ajax_request_maybe( $data ) {
		$time = microtime( true ) - $this->microtime;
		
		// + Bunyad
		
		// Check if ini_get is enabled, if not default to 25s
		if (!function_exists('ini_get')) {
			$exec_time = 25;
		}
		else {
			// 75% of execution time, start a new AJAX request
			$exec_time = ini_get('max_execution_time') * 0.75;
		}
		
		// / Bunyad

		// We should make a new ajax call, if the time is right.
		if ( $time > apply_filters( 'pt-ocdi/time_for_one_ajax_call', $exec_time ) ) {
			$this->ajax_call_number++;
			$this->set_importer_data();

			$response = array(
				'status'  => 'newAJAX',
				'message' => 'Time for new AJAX request!: ' . $time,
			);

			// Add any output to the log file and clear the buffers.
			$message = ob_get_clean();

			// Add message to log file.
			$log_added = OCDI_Helpers::append_to_file(
				__( 'Completed AJAX call number: ' , 'pt-ocdi' ) . $this->ajax_call_number . PHP_EOL . $message,
				$this->log_file_path,
				''
			);

			wp_send_json( $response );
		}

		// Set importing author to the current user.
		// Fixes the [WARNING] Could not find the author for ... log warning messages.
		$current_user_obj    = wp_get_current_user();
		$data['post_author'] = $current_user_obj->user_login;

		return $data;
	}

	/**
	 * Set current state of the content importer, so we can continue the import with new AJAX request.
	 */
	private function set_importer_data() {
		$data = array(
			'frontend_error_messages' => $this->frontend_error_messages,
			'ajax_call_number'        => $this->ajax_call_number,
			'log_file_path'           => $this->log_file_path,
			'selected_index'          => $this->selected_index,
			'selected_import_files'   => $this->selected_import_files,
		);

		$data = array_merge( $data, $this->importer->get_importer_data() );

		set_transient( 'ocdi_importer_data', $data, 0.5 * HOUR_IN_SECONDS );
	}

	/**
	 * Get content importer data, so we can continue the import with this new AJAX request.
	 */
	public function get_importer_data() {
		if ( $data = get_transient( 'ocdi_importer_data' ) ) {
			$this->frontend_error_messages                = empty( $data['frontend_error_messages'] ) ? '' : $data['frontend_error_messages'];
			$this->ajax_call_number                       = empty( $data['ajax_call_number'] ) ? 1 : $data['ajax_call_number'];
			$this->log_file_path                          = empty( $data['log_file_path'] ) ? '' : $data['log_file_path'];
			$this->selected_index                         = empty( $data['selected_index'] ) ? 0 : $data['selected_index'];
			$this->selected_import_files                  = empty( $data['selected_import_files'] ) ? array() : $data['selected_import_files'];
			$this->importer->set_importer_data( $data );

			return true;
		}
		return false;
	}

	/**
	 * Load the plugin textdomain, so that translations can be made.
	 */
	public function load_textdomain() {
		load_plugin_textdomain( 'pt-ocdi', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}


	/**
	 * Get data from filters, after the theme has loaded and instantiate the importer.
	 */
	public function setup_plugin_with_filter_data() {

		// Get info of import data files and filter it.
		$this->import_files = OCDI_Helpers::validate_import_file_info( apply_filters( 'pt-ocdi/import_files', array() ) );

		// Importer options array.
		$importer_options = apply_filters( 'pt-ocdi/importer_options', array(
			'fetch_attachments' => true,
		) );

		// Logger options for the logger used in the importer.
		$logger_options = apply_filters( 'pt-ocdi/logger_options', array(
			'logger_min_level' => 'warning',
		) );

		// Configure logger instance and set it to the importer.
		$this->logger            = new OCDI_Logger();
		$this->logger->min_level = $logger_options['logger_min_level'];

		// Create importer instance with proper parameters.
		$this->importer = new OCDI_Importer( $importer_options, $this->logger );
		
		// Setup demos
		$this->demos = apply_filters('bunyad_import_demos', array());
	}
}
