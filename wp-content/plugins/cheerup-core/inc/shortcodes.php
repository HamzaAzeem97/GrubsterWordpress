<?php
/**
 * Setup shortcodes and blocks
 */
class CheerUp_Shortcodes
{
	public $blocks = array();
	
	/**
	 * Add a special kind of shortcode that's handled by an included php file
	 * 
	 * @param array|string $shortcodes
	 */
	public function add($shortcodes)
	{
		$shortcodes   = (array) $shortcodes;
		$this->blocks = array_merge($this->blocks, $shortcodes);
		
		foreach ($shortcodes as $tag => $shortcode) {
			add_shortcode($tag, array($this, '_render'));
		}
		
		return $this;
	}
	
	/**
	 * Callback: Render a shortcode
	 */
	public function _render($atts = array(), $content = '', $tag)
	{
		$block = $this->blocks[$tag];
		
		// Extract attributes
		if (isset($block['attribs']) && is_array($block['attribs'])) {
			$atts = shortcode_atts($block['attribs'], $atts);
		}
		
		$atts['block_file'] = $block['render'];
		
		// save the current block in registry
		if (class_exists('Bunyad_Registry')) {
			Bunyad::registry()
				->set('block', $block)
				->set('block_atts', $atts);
		}
		
		return $this->render_block($atts, $tag);
	}

	/**
	 * Render the shortcode block
	 *
	 * @param array  $atts
	 * @param string $tag
	 * @return void
	 */
	public function render_block($atts, $tag)
	{
		$block = $this->blocks[$tag];

		if (!$block) {
			return false;
		}

		// Block file
		$block_file = $block['render'];
			
		// No file?
		if (!is_array($block) OR !file_exists($block_file)) {
			return false;
		}

		// get file content
		ob_start();

		$block = new CheerUp_Block($atts, $tag);
		$query = $block->process()->query;

		extract($atts, EXTR_SKIP);

		include apply_filters('bunyad_block_file', $block_file, $block);
		
		$block_content = ob_get_clean();
		
		return do_shortcode($block_content);
	}
}

// init and make available in Bunyad::get('cheerup_shortcodes')
Bunyad::register('cheerup_shortcodes', array(
	'class' => 'CheerUp_Shortcodes',
	'init' => true
));