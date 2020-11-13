<?php

/**
 * Custom WordPress menu related functionality is handled by this class
 */
class Bunyad_Menus
{
	public $fields = array();
	
	public function __construct() 
	{
		// low priority init, give theme a chance to register hooks
		add_action('init', array($this, 'init'), 50);
	}
	
	public function init()
	{
		$this->fields = apply_filters('bunyad_custom_menu_fields', array());

		// have custom fields?
		if (count($this->fields)) {
			
			add_filter('wp_setup_nav_menu_item', array($this, 'setup_menu_fields'));
			
			// only needed for admin
			if (is_admin()) {
				add_action('wp_update_nav_menu_item', array( $this, 'save_menu_fields'), 10, 3);
				add_filter('wp_edit_nav_menu_walker', array($this, 'walker_menu_fields'));
			}
			
			// front-side walker
			add_filter('wp_nav_menu_args', array($this, 'walker_front'));
		}
	}
	
	/**
	 * Setup custom walker for editing the menu
	 */
	public function walker_menu_fields($walker, $menu_id = null)
	{
		Bunyad::factory('menu-walker-edit');
		
		return 'Bunyad_MenuWalkerEdit'; 
	}
	
	/**
	 * Load the correct walker on demand when needed for the frontend menu
	 */
	public function walker_front($args) 
	{
		// Initialize new object for menu walker
		if ($args['walker'] == 'Bunyad_MenuWalker') {

			$is_amp = method_exists('Bunyad', 'amp') && Bunyad::amp()->active();

			// Set walker, but not in AMP mode
			$args['walker'] = !$is_amp ? Bunyad::factory('menu-walker', true) : '';
		}
		
		return $args;
	} 
	
	/**
	 * Load custom fields to the menu
	 */
	public function setup_menu_fields($menu_item)
	{
		foreach ($this->fields as $key => $field) {
			$menu_item->{$key} = get_post_meta($menu_item->ID, '_menu_item_' . $key, true);
		}
		
		return $menu_item;
	}
	
	/**
	 * Save menu custom fields
	*/
	public function save_menu_fields($menu_id, $menu_item_db_id, $args) 
	{	
		foreach ($this->fields as $key => $field) 
		{
			// add / update meta
			if (!empty($_POST['menu-item-' . $key]) && is_array($_POST['menu-item-' . $key])) {
				
				if (!isset($_POST['menu-item-' . $key][$menu_item_db_id])) {
					$_POST['menu-item-' . $key][$menu_item_db_id] = null;
				}
				
				update_post_meta($menu_item_db_id, '_menu_item_' . $key, $_POST['menu-item-' . $key][$menu_item_db_id]);
			}
		}
	}
		
}