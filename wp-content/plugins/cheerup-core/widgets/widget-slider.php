<?php
/**
 * Widget to show posts in a list format in sidebar
 */
class CheerUp_Widgets_Slider extends WP_Widget
{
	/**
	 * Setup the widget
	 * 
	 * @see WP_Widget::__construct()
	 */
	public function __construct()
	{
		parent::__construct(
			'bunyad-slider-widget',
			esc_html_x('Cheerup - Posts Slider', 'Admin', 'cheerup'),
			array('description' => esc_html_x('Show a posts slider.', 'Admin', 'cheerup'), 'classname' => 'widget-slider')
		);
	}
	
	/**
	 * Widget output 
	 * 
	 * @see WP_Widget::widget()
	 */
	public function widget($args, $instance)
	{
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		
		// Setup the query
		$query_args  = array('posts_per_page' => $instance['number'], 'ignore_sticky_posts' => 1);
		
		// Popular by comments
		if ($instance['type'] == 'popular') {
			$query_args = array_merge($query_args, array('orderby' => 'comment_count'));
		}
		
		// Most liked
		if ($instance['type'] == 'liked') {
			$query_args = array_merge($query_args, array(
		 		'meta_key' => '_sphere_user_likes_count', 'orderby' => 'meta_value_num'
			));
		}
		
		// Most Viewed (WP-PostViews plugin)
		if ($instance['type'] == 'views') {
			$query_args = array_merge($query_args, array(
				'meta_key' => 'views', 
				'orderby' => 'meta_value_num', 
				'order' => 'DESC'
			));
		}
		
		// Limited by tag?
		if (!empty($instance['limit_tag'])) {
			$query_args = array_merge($query_args, array('tag' => $instance['limit_tag']));
		}
		
		// Limited by category?
		if (!empty($instance['limit_cat'])) {
			$query_args = array_merge($query_args, array('cat' => $instance['limit_cat']));
		}
		
		$query = new WP_Query(apply_filters('bunyad_widget_slider_query_args', $query_args));		
		
		if (!Bunyad::media()) {
			return;
		}

		?>

		<?php echo $args['before_widget']; ?>
		
			<?php if (!empty($title)): ?>
				
				<?php
					echo $args['before_title'] . esc_html($title) . $args['after_title']; // before_title/after_title are built-in WordPress sanitized
				?>
				
			<?php endif; ?>
			
			<div class="slides">
			
				<?php while ($query->have_posts()): $query->the_post(); ?>
			
					<div class="item">
						<?php
							Bunyad::media()->the_image('cheerup-widget-slider');
						?>
						
						<div class="content cf">
						
							<?php 
								Bunyad::helpers()->post_meta(
									'widget-slider', 
									[
										'items_above'   => ['cat'],
										'items_below'   => ['date'],
										'title_class'   => 'post-title',
										'align'         => 'center'
									]
								); 
							
							?>
							
						</div>
						
					</div>
					
				<?php endwhile; wp_reset_postdata(); ?>
			</div>
		
		<?php echo $args['after_widget']; ?>
		
		<?php
	}
	
	/**
	 * Save widget
	 * 
	 * Strip out all HTML using wp_kses
	 * 
	 * @see wp_filter_post_kses()
	 */
	public function update($new, $old)
	{
		foreach ($new as $key => $val) {
			$new[$key] = wp_kses_post_deep($val);
		}
		
		return $new;
	}
	
	/**
	 * The widget form
	 */
	public function form($instance)
	{
		$defaults = array(
			'title' => '', 
			'type' => '', 
			'number' => 4,
			'limit_tag' => '', 
			'limit_cat' => ''
		);
		
		$instance = array_merge($defaults, (array) $instance);
		extract($instance);
		
		?>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html_x('Title:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('type')); ?>"><?php echo esc_html_x('Sorting:', 'Admin', 'cheerup'); ?></label>
			
			<select id="<?php echo esc_attr($this->get_field_id('type')); ?>" name="<?php echo esc_attr($this->get_field_name('type')); ?>" class="widefat">
				<option value="" <?php selected($type, ''); ?>><?php echo esc_html_x('Latest Posts', 'Admin', 'cheerup') ?></option>
				<option value="popular" <?php selected($type, 'popular'); ?>><?php echo esc_html_x('Most Commented', 'Admin', 'cheerup'); ?></option>
				<option value="liked" <?php selected($type, 'liked'); ?>><?php echo esc_html_x('Most Liked', 'Admin', 'cheerup'); ?></option>
				
				<?php if (function_exists('get_most_viewed')): ?>
					<option value="views" <?php selected($type, 'views'); ?>><?php echo esc_html_x('By Views (WP-PostViews Plugin)', 'Admin', 'cheerup'); ?></option>
				<?php endif; ?>
				
			</select>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('number')); ?>"><?php echo esc_html_x('Number of posts to show:', 'Admin', 'cheerup'); ?></label>
			<input id="<?php echo esc_attr($this->get_field_id('number')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('number')); ?>" type="text" value="<?php echo esc_attr($number); ?>" size="3" />
		</p>		
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit_tag')); ?>"><?php echo esc_html_x('From this Tag (Optional):', 'Admin', 'cheerup'); ?></label>
			
			<input id="<?php echo esc_attr($this->get_field_id('limit_tag')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('limit_tag')); ?>" type="text" class="widefat" value="<?php echo esc_attr($limit_tag); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('limit_cat')); ?>"><?php echo esc_html_x('Limit to Category (Optional):', 'Admin', 'cheerup'); ?></label>
			<?php wp_dropdown_categories(array(
					'show_option_all' => esc_html_x('-- Not Limited --', 'Admin', 'cheerup'), 
					'hierarchical' => 1,
					'hide_empty' => 0,
					'order_by' => 'name', 
					'class' => 'widefat', 
					'name' => $this->get_field_name('limit_cat'), 
					'selected' => $limit_cat
			)); ?>
		</p>	
	
	
		<?php
	}
}