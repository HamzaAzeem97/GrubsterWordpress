<?php
/**
 * A subscribe widget - uses MailChimp
 */
class CheerUp_Widgets_Subscribe extends WP_Widget
{
	/**
	 * Setup the widget
	 * 
	 * @see WP_Widget::__construct()
	 */
	public function __construct()
	{
		parent::__construct(
			'bunyad-widget-subscribe',
			esc_html_x('Cheerup - Subscribe (MailChimp)', 'Admin', 'cheerup'),
			array('description' => esc_html_x('Add a subscribe widget.', 'Admin', 'cheerup'), 'classname' => 'widget-subscribe')
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
		
		?>

		<?php echo $args['before_widget']; ?>

			<?php if (!empty($title)): ?>
				
				<?php
					echo $args['before_title'] . wp_kses_post($title) . $args['after_title']; // before_title/after_title are built-in WordPress sanitized
				?>
				
			<?php endif; ?>
			
			<form method="post" action="<?php echo esc_url($instance['submit_url']); ?>" class="form" target="_blank">
				<div class="fields">
					<p class="message">
						<?php echo esc_html($instance['label']); ?>
					</p>
					
					<p>
						<input type="email" name="EMAIL" placeholder="<?php esc_attr_e('Your email address..', 'cheerup'); ?>" required>
					</p>
					
					<p>
						<input type="submit" value="<?php echo esc_attr($instance['submit_label']); ?>">
					</p>
				</div>
			</form>

		
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
			
			// Pasted whole form? Capture the URL
			if ($key == 'submit_url') {
				
				if (preg_match('/action=\"([^\"]+)\"/', $val, $match)) {
					$val = $match[1];	
				}
			}
			
			// Filter disallowed html 			
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
			'label' => esc_html__('Enter your email address below to subscribe to my newsletter', 'cheerup'),
			'submit_label' => esc_html__('Subscribe', 'cheerup'),
			'submit_url' => '',
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
			<label for="<?php echo esc_attr($this->get_field_id('label')); ?>"><?php echo esc_html_x('Message/Label:', 'Admin', 'cheerup'); ?></label>
			<textarea class="widefat" id="<?php echo esc_attr($this->get_field_id('label')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('label')); ?>" rows="2"><?php echo esc_textarea($label); ?></textarea>
		</p>
		
		<div>
			<label for="<?php echo esc_attr($this->get_field_id('submit_url')); ?>"><?php echo esc_html_x('MailChimp Form Submit URL:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('submit_url')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('submit_url')); ?>" type="text" value="<?php echo esc_attr($submit_url); ?>" />
		</div>
	
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('submit_label')); ?>"><?php echo esc_html_x('Button Label:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('submit_label')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('submit_label')); ?>" type="text" value="<?php echo esc_attr($submit_label); ?>" />
		</p>	
	
	
		<?php
	}
}