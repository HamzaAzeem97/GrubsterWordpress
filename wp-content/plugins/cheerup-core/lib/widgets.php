<?php
/**
 * Initialize widgets from the plugin with a fallback path to theme if set.
 * 
 * @package Bunyad
 */
class Bunyad_Widgets
{
	public $active  = [];
	public $path    = '';
	public $prefix  = 'Bunyad_';

	public function __construct()
	{
		add_action('widgets_init', array($this, 'setup'));
	}
	
	/**
	 * Initialize the widgets
	 */
	public function setup() 
	{
		$widgets = apply_filters('bunyad_active_widgets', $this->active);

		// Activate widgets
		foreach ($widgets as $widget) {

			$file = $this->path . 'widgets/widget-'. sanitize_file_name($widget) .'.php';
			
			// Try from theme if widget file doesn't exist in specified path.
			if (!file_exists($file)) {
				$file = locate_template('inc/widgets/widget-'. sanitize_file_name($widget) .'.php');
			}

			$class =  $this->prefix. implode('', array_map('ucfirst', explode('-', $widget)));
			
			// Skip if already included or if file is missing.
			if (class_exists($class) OR !file_exists($file)) {
				continue;
			}
			
			// Include the widget class
			require_once $file;  // Escaped above.
			
			if (!class_exists($class)) {
				continue;
			}
			
			/**
			 * Use register widget method of the Bunyad_XYZ_Widget class if available.
			 * Fallback to register_widget.
			 * 
			 * @see register_widget()
			 */
			if (method_exists($class, 'register_widget')) {
				$caller = new $class;
				$caller->register_widget(); 
			}
			else {
				register_widget($class);
			}
		}
	}
}