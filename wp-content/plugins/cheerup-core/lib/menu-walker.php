<?php

class Bunyad_MenuWalker extends Walker_Nav_Menu
{
	public $in_mega_menu = false;
	public $current_item;
	
	/**
	 * Stores mega menu inner-data
	 */
	public $last_lvl;
	public $sub_items = array();
	
	public function start_el(&$output, $item, $depth = 0, $args = array(), $id = 0) 
	{
		
		// Add category class to menu items
		if ($item->object == 'category') {
			$item->classes = array_merge((array) $item->classes, array('menu-cat-' . $item->object_id)); 
		}
		
		// Add mega-menu class
		if (!empty($item->mega_menu)) {
			$item->classes = array_merge((array) $item->classes, array('menu-item-has-children item-mega-menu'));
		}
		
		parent::start_el($item_output, $item, $depth, $args, $id);

		// Reset at beginning
		if ($depth == 0) {
			$this->in_mega_menu = false;
			$this->current_item = null;
			$this->last_lvl     = '';
			$this->sub_items    = array();
		}
		
		// DEBUG:  $depth .' -- ' . $item->title . "\n\n";
		
		// Is it a mega menu parent?
		if ($item->mega_menu) {
			$this->in_mega_menu = true;
			$this->current_item = $item;
		}
		
		// In mega menu
		if ($this->in_mega_menu && $depth > 0) {
			$this->last_lvl .= $item_output;
			
			// Store the sub-item object - only needs to be captured here since it's not using output
			$this->sub_items[] = $item;
			
			return;
		}
		
		$output .= $item_output;
	}
	
	public function end_el(&$output, $item, $depth = 0, $args = array()) 
	{	
		$item_output = '';
		parent::end_el($item_output, $item, $depth, $args);
		
		if ($this->in_mega_menu && $depth > 0) {
			$this->last_lvl .= $item_output;
			return;
		}
		
		/**
		 * Attach mega-menu at end of element for items that don't have sub-items
		 * 
		 * Note: Top-level elements have depth 0 at end_el.
		 */
		if ($this->in_mega_menu && $depth == 0 && empty($this->sub_items)) {
			$output .= apply_filters('bunyad_mega_menu_end_lvl',
					array(
						'sub_menu'  => '', 
						'item'      => $item, 
						'sub_items' => null,
						'args'      => $args
					)
			);
		}
				
		$output .= $item_output;
	}
	
	public function start_lvl(&$output, $depth = 0, $args = array()) 
	{
		$item_output = '';
		parent::start_lvl($item_output, $depth, $args);

		if ($this->in_mega_menu) {
			
			// mega-menu item level greater than 2 - start a default WordPress start_lvl
			if ($depth >= 1) {
				$this->last_lvl .= $item_output;
			}
			
			return;
		}
		
		$output .= $item_output;
	}
	
	public function end_lvl(&$output, $depth = 0, $args = array())
	{	
		$item_output = '';
		parent::end_lvl($item_output, $depth, $args);

		// Processing sub-levels of mega-menu
		if ($this->in_mega_menu) {
			
			// End of mega-menu parent - at top-level!
			if ($depth == 0) {
				
				$output .= apply_filters('bunyad_mega_menu_end_lvl', 
					array(
						'sub_menu'  => $this->last_lvl, 
						'item'      => $this->current_item, 
						'sub_items' => $this->sub_items,
						'args'      => $args
					)
				);
				
				// unset
				$this->last_lvl = '';
				
				return;
			}
			
			$this->last_lvl .= $item_output;
			return;
		}

		$output .= $item_output;
	}
}