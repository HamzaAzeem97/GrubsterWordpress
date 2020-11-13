<?php
/**
 * Prepare blocks arrays used in the builders.
 */
class CheerUp_Blocks
{
	public $cached;

	public function get_blocks($process = true)
	{
		if ($process) {

			/**
			 * Get categories list for dropdown options 
			 */
			$categories = get_terms('category', array(
				'hide_empty' => 0,
				'hide_if_empty' => false,
				'hierarchical' => 1, 
				'order_by' => 'name' 
			));
		
			$categories = array_merge(
				array(esc_html_x('-- None / Not Limited --', 'Admin', 'cheerup') => ''), 
				$this->_recurse_terms_array(0, $categories)
			);
		}
		else {
			$categories = array();
		}

		// Not processing and have cache?
		if (!$process && $this->cached) {
			return $this->cached;
		}
		
		/**
		 * The default options generally shared between blocks 
		 */
		$common = array(
	
			'posts' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('Number of Posts', 'Admin', 'cheerup'),
				'value'  => 5,
				'param_name' => 'posts',
				'admin_label' => false,
			),
			
			'sort_by' => array(
				'type' => 'dropdown',
				'heading' => esc_html_x('Sort By', 'Admin', 'cheerup'),
				'value'  => array(
					esc_html_x('Published Date', 'Admin', 'cheerup') => '',
					esc_html_x('Modified Date', 'Admin', 'cheerup') => 'modified',
					esc_html_x('Random', 'Admin', 'cheerup') => 'random',
					esc_html_x('Comment Count', 'Admin', 'cheerup') => 'comments',
					esc_html_x('Alphabetical', 'Admin', 'cheerup') => 'alphabetical',
					esc_html_x('Most Liked', 'Admin', 'cheerup') => 'liked'
				),
				'param_name' => 'sort_by',
				'admin_label' => false,
			),
			
			'sort_order' => array(
				'type' => 'dropdown',
				'heading' => esc_html_x('Sort Order', 'Admin', 'cheerup'),
				'value'  => array(
					esc_html_x('Descending - Higher to lower (Latest First)', 'Admin', 'cheerup') => 'desc',
					esc_html_x('Ascending - Lower to Higher (Oldest First)', 'Admin', 'cheerup')  => 'asc',
				),
				'param_name' => 'sort_order',
				'admin_label' => false,
			),
			
			'heading' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('Heading  (Optional)', 'Admin', 'cheerup'),
				'description' => esc_html_x('By default, the main selected category\'s name is used as the title.', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'heading',
				'admin_label' => true,
			),
			
			'heading_type' => array(
				'type' => 'dropdown',
				'heading' => esc_html_x('Heading Type', 'Admin', 'cheerup'),
				'description' => esc_html_x('Use Small Headings for 1/3 columns. Default headings are good for full-width and half column blocks.', 'Admin', 'cheerup'),
				'value'  => array(
					esc_html_x('Magazine Block', 'Admin', 'cheerup') => 'modern',
					esc_html_x('Magazine Block - Simple', 'Admin', 'cheerup') => 'head-c',
					esc_html_x('Blog Style', 'Admin', 'cheerup') => 'blog',
					esc_html_x('Disabled', 'Admin', 'cheerup') => 'none',
				),
				'param_name' => 'heading_type',
			),

			'css_heading_color' => array(
				'type' => 'colorpicker',
				'heading' => esc_html_x('Heading Color', 'Admin', 'cheerup'),
				'description' => '',
				'value'  => '',
				'param_name' => 'css_heading_color',
			),
			
			'view_all' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('View All Text (Optional)', 'Admin', 'cheerup'),
				'description' => esc_html_x('If not empty, this text will show with heading link.', 'Admin', 'cheerup'),
				'value' => '',
				'param_name' => 'view_all',
			),
			
			'link' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('Heading Link (Optional)', 'Admin', 'cheerup'),
				'description' => esc_html_x('By default, the main selected category\'s link is used.', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'link',
			),
				
			'offset' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('Advanced: Offset', 'Admin', 'cheerup'),
				'description' => esc_html_x('An offset can be used to skip first X posts from the results.', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'offset',
			),
				
			'cat' => array(
				'type' => 'dropdown',
				'heading' => esc_html_x('From Category', 'Admin', 'cheerup'),
				'description' => esc_html_x('Posts will be limited to this category', 'Admin', 'cheerup'),
				'value'  => $categories,
				'param_name' => 'cat',
				'admin_label' => true,
				'group' => esc_html_x('Refine Posts', 'Admin', 'cheerup'),
			),
				
			'terms' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('From Multiple Categories', 'Admin', 'cheerup'),
				'description' => esc_html_x('If you need posts from more categories. Enter cat slugs separated by commas. Example: beauty,world-news', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'terms',
				'group' => esc_html_x('Refine Posts', 'Admin', 'cheerup'),
			),
				
			'tags' => array(
				'type' => 'textfield',
				'heading' => esc_html_x('Posts From Tags', 'Admin', 'cheerup'),
				'description' => esc_html_x('A single or multiple tags. Enter tag slugs separated by commas. Example: food,sports', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'tags',
				'group' => esc_html_x('Refine Posts', 'Admin', 'cheerup'),
			),

			
			'post_format' => array(
				'type' => 'dropdown',
				'heading' => esc_html_x('Post Format', 'Admin', 'cheerup'),
				'description' => '',
				'value'  => array(
					esc_html_x('All', 'Admin', 'cheerup') => '',
					esc_html_x('Video', 'Admin', 'cheerup') => 'video',
					esc_html_x('Gallery', 'Admin', 'cheerup') => 'gallery',
					esc_html_x('Image', 'Admin', 'cheerup') => 'image',
				),
				'param_name' => 'post_format',
				'group' => esc_html_x('Refine Posts', 'Admin', 'cheerup'),
			),
				
			'post_type' => array(
				'type' => 'posttypes',
				'heading' => esc_html_x('Advanced: Post Types', 'Admin', 'cheerup'),
				'description' => esc_html_x('Use this feature if Custom Post Types are needed.', 'Admin', 'cheerup'),
				'value'  => '',
				'param_name' => 'post_type',
				'group' => esc_html_x('Refine Posts', 'Admin', 'cheerup'),
			),

			'css' => array(
				'type' => 'css_editor',
				'heading' => esc_html_x('Design', 'Admin', 'cheerup'),
				'param_name' => 'css',
				'group' => esc_html_x('Design', 'Admin', 'cheerup'),
			),
		);
		
		$common = apply_filters('bunyad_vc_map_common', $common);
		
		// Pagination types for the blocks
		$pagination_types = array(
			'load-more' => esc_html_x('Load More (AJAX)', 'Admin', 'cheerup'),
			'numbers' => esc_html_x('Page Numbers (AJAX)', 'Admin', 'cheerup'),
			''  => esc_html_x('Older / Newer (Only if one or last block)', 'Admin', 'cheerup'),
		);
				
		/**
		 * Highlights block
		 */
		$blocks['highlights'] = array(
			'name' => esc_html_x('Highlights Block', 'Admin', 'cheerup'),
			'base' => 'highlights',
			'description' => esc_html_x('Run-down of news from a category.', 'Admin', 'cheerup'),
			'class' => 'sphere-icon',
			'icon' => 'tsb-highlights',
			'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
			'weight' => 1,
			'params' => $common,
		);
		
		
		/**
		 * News Grid block
		 */
		$blocks['news_grid'] = array(
			'name' => esc_html_x('News Grid Block', 'Admin', 'cheerup'),
			'base' => 'news_grid',
			'description' => esc_html_x('News in a compact grid.', 'Admin', 'cheerup'),
			'class' => 'sphere-icon',
			'icon' => 'tsb-news-grid',
			'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
			'weight' => 1,
			'params' => $common,
		);
		
		
		/**
		 * Slider Block
		 */
		$slider_styles = array(
			'stylish' => esc_html_x('Stylish (3 images)', 'Admin', 'cheerup'),
			'default' => esc_html_x('Classic Slider (3 Images)', 'Admin', 'cheerup'),
			'beauty'  => esc_html_x('Beauty (Single Image)', 'Admin', 'cheerup'),
			'fashion' => esc_html_x('Fashion (Single Image)', 'Admin', 'cheerup'),
			'trendy'  => esc_html_x('Trendy (2 Images)', 'Admin', 'cheerup'),
			'large'    => esc_html_x('Large Full Width', 'Admin', 'cheerup'),
			'carousel' => esc_html_x('Carousel (3 Small Posts)', 'Admin', 'cheerup'),
			'bold'     => esc_html_x('Bold Full Width', 'Admin', 'cheerup'),
		);

		$feat_grids = [
			'grid-a'  => esc_html_x('Grid 1: 1 Large + 4 small', 'Admin', 'cheerup'),
			'grid-b'  => esc_html_x('Grid 2: 1 Large + 2 small', 'Admin', 'cheerup'),
			'grid-c'  => esc_html_x('Grid 3: 1 Large + 2 medium', 'Admin', 'cheerup'),
			'grid-d'  => esc_html_x('Grid 4: 2 Columns', 'Admin', 'cheerup'),
			'grid-e'  => esc_html_x('Grid 5: 3 Columns', 'Admin', 'cheerup'),
			'grid-f'  => esc_html_x('Grid 6: 4 Columns', 'Admin', 'cheerup'),
			'grid-g'  => esc_html_x('Grid 7: 5 Columns', 'Admin', 'cheerup'),
		];

		$slider_styles += $feat_grids;
		$is_feat_grid   = [
			'element' => 'type',
			'value'   => array_keys($feat_grids)
		];

		$slider = array_merge(array(
			'type' => array(
				'type'        => 'dropdown',
				'heading'     => esc_html_x('Slider Style', 'Admin', 'cheerup'),
				'description' => '',
				'value'       => array_flip($slider_styles),
				'param_name'  => 'type',
			),
			'slider_width' => array(
				'param_name'  => 'slider_width',
				'heading'     => esc_html_x('Featured Width', 'Admin', 'cheerup'),
				'description' => '',
				'type'        => 'dropdown',
				'value'       => array_flip([
					'container' => esc_html_x('Container', 'Admin', 'cheerup'),
					'viewport'  => esc_html_x('Full Browser Width', 'Admin', 'cheerup'),
				]),
				'std'         => 'container',
				'dependency'  => $is_feat_grid,
			),
			'slider_static' => array(
				'param_name'  => 'slider_static',
				'heading'     => esc_html_x('Static Grid (No Slider)?', 'Admin', 'cheerup'),
				'description' => esc_html_x('Disable slides, make grid static, and use a different look on mobile.', 'Admin', 'cheerup'),
				'type'        => 'checkbox',
				'value'       => array(
					esc_html_x('Yes', 'Admin', 'cheerup') => 1
				),
				'std'         => 0,
				'dependency'  => $is_feat_grid,
			),
			'css_grid_gap' => [
				'type'       => 'textfield',
				'heading'    => esc_html_x('Grid Gap', 'Admin', 'cheerup'),
				'value'      => '',
				'param_name' => 'css_grid_gap',
				'admin_label' => false,
				'dependency'  => $is_feat_grid,
			],
			'slider_parallax' => array(
				'param_name'  => 'slider_parallax',
				'heading'     => esc_html_x('Enable Parallax Effect?', 'Admin', 'cheerup'),
				'description' => '',
				'type'        => 'checkbox',
				'value'       => array(
					esc_html_x('Yes', 'Admin', 'cheerup') => 1
				),
				'std'         => 0,
			),

		), $common);

		// Default to 6 posts and remove pagination
		$slider['posts']['value'] = 6;
		unset(
			$slider['pagination'], 
			$slider['pagination_type']
		);

		$blocks['ts_slider'] = array(
			'name' => esc_html_x('Slider/Featured Block', 'Admin', 'cheerup'),
			'base' => 'ts_slider',
			'description' => esc_html_x('Use featured slider.', 'Admin', 'cheerup'),
			'class' => 'sphere-icon',
			'icon' => 'tsb-slider',
			'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
			'weight' => 1,
			'params' => $slider,
		);

		/**
		 * Blog/Listing block
		 */	
		
		// Blog listing types
		$listings = array(
			''     => esc_html_x('Default (Category Loop from Customizer)', 'Admin', 'cheerup'),
			'loop-classic' => esc_html_x('Classic Large Posts', 'Admin', 'cheerup'),
			'loop-1st-large' => esc_html_x('One Large Post + Grid', 'Admin', 'cheerup'),
			'loop-1st-large-list' => esc_html_x('One Large Post + List', 'Admin', 'cheerup'),
			'loop-1st-overlay' => esc_html_x('One Overlay Post + Grid', 'Admin', 'cheerup'),
			'loop-1st-overlay-list' => esc_html_x('One Overlay Post + List', 'Admin', 'cheerup'),
				
			'loop-1-2' => esc_html_x('Mixed: Large Post + 2 Grid ', 'Admin', 'cheerup'),
			'loop-1-2-list' => esc_html_x('Mixed: Large Post + 2 List ', 'Admin', 'cheerup'),

			'loop-1-2-overlay' => esc_html_x('Mixed: Overlay Post + 2 Grid ', 'Admin', 'cheerup'),
			'loop-1-2-overlay-list' => esc_html_x('Mixed: Overlay Post + 2 List ', 'Admin', 'cheerup'),
				
			'loop-list' => esc_html_x('List Posts', 'Admin', 'cheerup'),
			'loop-grid' => esc_html_x('Grid Posts', 'Admin', 'cheerup'),
			'loop-grid-3' => esc_html_x('Grid Posts (3 Columns)', 'Admin', 'cheerup'),
		);
		

		// Block settings
		$blog = array_merge(
			array(
				'type' => array(
					'type' => 'dropdown',
					'heading' => esc_html_x('Listing Type', 'Admin', 'cheerup'),
					'description' => '',
					'value'  => array_flip($listings),
					'param_name' => 'type',
				),
				
				'show_excerpt' => array(
					'type' => 'checkbox',
					'heading' => esc_html_x('Show Excerpts?', 'Admin', 'cheerup'),
					'param_name' => 'show_excerpt',
					'value' => array(
						esc_html_x('Yes', 'Admin', 'cheerup') => 1
					),
					'std' => 1,
					'dependency' => array(
						'element' => 'type',
						'value'   => array_values(array_diff(
							array_keys($listings), 
							array('loop-1st-overlay', 'loop-1st-overlay-list', 'loop-1-2-overlay', 'loop-1-2-overlay-list')
						)),
					)
				),

				'show_footer' => array(
					'type' => 'checkbox',
					'heading' => esc_html_x('Show Posts Footer?', 'Admin', 'cheerup'),
					'description' => esc_html_x('Enable to show Social icons or Read More depending on grid post style chosen in customizer.', 'Admin', 'cheerup'),
					'param_name' => 'show_footer',
					'value' => array(
						esc_html_x('Yes', 'Admin', 'cheerup') => 1,
					),
					'std' => 1,
					'dependency' => array(
						'element' => 'type',
						'value'   => array('loop-grid', 'loop-grid-3', 'loop-1st-large'),
					)
				),

				'grid_style' => array(
					'type' => 'dropdown',
					'heading' => esc_html_x('Grid Posts Style', 'Admin', 'cheerup'),
					'description' => '',
					'param_name' => 'grid_style',
					'value' => array_flip(array(
						''       => esc_html_x('Default', 'Admin', 'cheerup'),
						'grid'   => esc_html_x('Style 1', 'Admin', 'cheerup'),
						'grid-b' => esc_html_x('Style 2', 'Admin', 'cheerup')
					)),
					'dependency' => array(
						'element' => 'type',
						'value'   => array('loop-grid', 'loop-grid-3'),
					)
				),

				'pagination' => array(
					'type' => 'dropdown',
					'heading' => esc_html_x('Pagination', 'Admin', 'cheerup'),
					'value'  => array(
						esc_html_x('Disabled', 'Admin', 'cheerup') => '',
						esc_html_x('Enabled', 'Admin', 'cheerup') => '1',
					),
					'param_name' => 'pagination',
				),
				
				'pagination_type' => array(
					'type' => 'dropdown',
					'heading' => esc_html_x('Pagination Type', 'Admin', 'cheerup'),
					'value'   => array_flip($pagination_types),
					'param_name' => 'pagination_type',
					'dependency' => array(
						'element' => 'pagination',
						'value'   => array('1')
					),
					'std' => 'load-more'
				),
			), 
			$common
		);

		$blog['posts']['value'] = 6;
		
		$blocks['blog'] = array(
			'name' => sprintf(esc_html_x('Posts (%s Layouts)', 'Admin', 'cheerup'), count($listings) - 1),
			'description' => esc_html_x('For blog/category style listings. Multiple listing styles.', 'Admin', 'cheerup'),
			'base' => 'blog',
			'icon' => 'tsb-post-listings',
			'class' => 'sphere-icon',
			'weight' => 1,
			'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
			'params' => $blog,
		);
		
		foreach ($listings as $id => $text) {
			
			// Skip the default.
			if (empty($id)) {
				continue;
			}
			
			$params = $blog;
			$params['type'] = array_merge($params['type'], array(
				'value' => $id,
				'type'  => 'hidden'
			));

			// Excerpt length only applies to these 3
			if (in_array($id, array('loop-list', 'loop-grid', 'loop-grid-3'))) {
				$params['excerpt_length'] = array(
					'type' => 'textfield',
					'heading' => esc_html_x('Excerpt Length', 'Admin', 'cheerup'),
					'description' => esc_html_x('Leave empty for default.', 'Admin', 'cheerup'),
					'value'  => '',
					'param_name' => 'excerpt_length',
					'dependency' => array(
						'element' => 'show_excerpt',
						'value'   => array('1')
					),
				);
			}
			
			// Shortcodes are registered as loop_grid_ - using Hyphens in shortcodes is dangerous
			// $block_id = str_replace('-', '_', $id);

			$blocks[$id] = array(
				'name' => $text,
				'description' => '',
				'base' => $id,
				'icon' => 'tsb-' . $id . '',
				'class' => 'sphere-icon',
				'weight' => 1,
				'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
				'params' => $params,
			);
		}
		
		/**
		 * Ads block
		 */
		$blocks['ts_ads'] = array(
			'name' => esc_html_x('Advertisement Block', 'Admin', 'cheerup'),
			'description' => esc_html_x('Advertisement code block.', 'Admin', 'cheerup'),
			'base' => 'ts_ads',
			'icon' => 'icon-wpb-wp',
			'category' => esc_html_x('Home Blocks', 'Admin', 'cheerup'),
			'weight' => 0,
			'params' => array(
				'code' => array(
					'type' => 'textarea_raw_html',
					'heading' => esc_html_x('Ad Code', 'Admin', 'cheerup'),
					'description' => esc_html_x('Enter your ad code here.', 'Admin', 'cheerup'),
					'param_name' => 'code',
				),
				'css' => array(
					'type' => 'css_editor',
					'heading' => esc_html_x('Design', 'Admin', 'cheerup'),
					'param_name' => 'css',
					'group' => esc_html_x('Design', 'Admin', 'cheerup'),
				),
			),
		);

		$this->cached = $blocks;

		return $blocks;
	}
	
	/**
	 * Create category drop-down via recursion on parent-child relationship
	 * 
	 * @param integer  $parent
	 * @param object   $terms
	 * @param integer  $depth
	 */
	public function _recurse_terms_array($parent, $terms, $depth = 0)
	{	
		$the_terms = array();
			
		$output = array();
		foreach ($terms as $term) {
			
			// add tab to children
			if ($term->parent == $parent) {
				$output[str_repeat(" - ", $depth) . $term->name] = $term->term_id;			
				$output = array_merge($output, $this->_recurse_terms_array($term->term_id, $terms, $depth+1));
			}
		}
		
		return $output;
	}
}

// init and make available in Bunyad::get('cheerup_blocks')
Bunyad::register('cheerup_blocks', array(
	'class' => 'CheerUp_Blocks',
	'init' => true
));
