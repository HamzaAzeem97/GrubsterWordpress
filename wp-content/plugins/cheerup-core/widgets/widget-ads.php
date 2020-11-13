<?php
/**
 * An ad widget wrapper
 */
class CheerUp_Widgets_Ads extends WP_Widget
{
	/**
	 * Setup the widget
	 * 
	 * @see WP_Widget::__construct()
	 */
	public function __construct()
	{
		parent::__construct(
			'bunyad-widget-ads',
			esc_html_x('Cheerup - Advertisement', 'Admin', 'cheerup'),
			array('description' => esc_html_x('Add advertisement code to your sidebar.', 'Admin', 'cheerup'), 'classname' => 'widget-a-wrap')
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
		$title = apply_filters('widget_title', esc_html($instance['title']));
		
		if (empty($instance['ad_code'])) {
			return;
		}
		
		?>

		<?php echo $args['before_widget']; ?>

			<?php if (!empty($title)): ?>
				
				<?php
					echo $args['before_title'] . wp_kses_post($title) . $args['after_title']; // before_title/after_title are built-in WordPress sanitized
				?>
				
			<?php endif; ?>
			
			<div class="the-wrap">
				<?php echo do_shortcode($instance['ad_code']); // It's an ad code - we shouldn't be escaping it ?>
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
			
			// Filter disallowed html - Removed as Adsense code would get stripped here 			
			// $new[$key] = wp_kses_post($val);
		}
		
		return $new;
	}
	
	/**
	 * The widget form
	 */
	public function form($instance)
	{
		$defaults = array('title' => '', 'ad_code' => '');
		$instance = array_merge($defaults, (array) $instance);
		
		extract($instance);

		?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('ad_code')); ?>"><?php echo esc_html_x('Ad Code:', 'Admin', 'cheerup'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('ad_code')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('ad_code')); ?>" rows="5"><?php echo esc_textarea($ad_code); ?></textarea>
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html_x('Title: (Optional)', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>		
	
	
		<?php
	}
}