<?php
/**
 * Call to action widget
 */
class CheerUp_Widgets_Cta extends WP_Widget
{
	/**
	 * Setup the widget
	 * 
	 * @see WP_Widget::__construct()
	 */
	public function __construct()
	{
		parent::__construct(
			'bunyad-widget-cta',
			esc_html_x('Cheerup - Call To Action Boxes', 'Admin', 'cheerup'),
			array('description' => esc_html_x('Add an image with button overlay to promote content.', 'Admin', 'cheerup'), 'classname' => 'widget-cta')
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
		
		?>

		<?php echo $args['before_widget']; ?>
		
			<?php if (!empty($instance['title'])): ?>
				
				<?php
					echo $args['before_title'] . wp_kses_post($instance['title']) . $args['after_title']; // before_title/after_title are built-in WordPress sanitized
				?>
				
			<?php endif; ?>
		
		<div class="boxes">
		<?php foreach ((array) $instance['boxes'] as $key => $box): ?>
		
			<div class="cta-box">
				<a href="<?php echo esc_url($box['link']); ?>">
					<img src="<?php echo esc_url($box['image']); ?>" alt="<?php echo esc_attr($box['heading']); ?>" />
					
					<span class="label"><?php echo esc_html($box['heading']); ?></span>
				</a>
			</div>
			
		<?php endforeach; ?>
		</div>
		
		<?php echo $args['after_widget']; ?>
		
		<?php
	}

	
	/**
	 * Save widget
	 * 
	 * Strip out all HTML using wp_kses
	 * 
	 * @see wp_kses_post()
	 */
	public function update($new, $old)
	{
		foreach ($new as $key => $val) {

			// Filter disallowed html
			if (is_array($val)) {
				
				foreach ($val as $k => $v) {
					$new[$key][$k] = wp_kses_post_deep($v);
					
					// Remove group if heading is empty
					if (array_key_exists('heading', $v) && empty($v['heading'])) {
						unset($new[$key][$k]);
					}
				}
			}
			else {
				$new[$key] = wp_kses_post($val);
			}
		}
		
		return $new;
	}
	
	/**
	 * The widget form
	 */
	public function form($instance)
	{
		// Default fields to show
		$default_array = array('heading' => '', 'image' => '', 'link' => '');
		
		$defaults = array('boxes' => array_fill_keys(range(0, 2), $default_array), 'title' => '');
		$instance = array_merge($defaults, (array) $instance);
		
		extract($instance);
		
		$count = 0;
		
		// Editing the widget, add one extra group
		if (is_integer($this->number) && count($boxes)) {
			$max = max(array_keys($boxes));
			$key = $max + 1;
			
			$boxes[$key] = $default_array;
			
			// reset it
			reset($boxes);
			
		}
		else {
			// User may have saved with no boxes
			$boxes = $defaults['boxes'];
		}
		
		?>
		
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html_x('Title: (Optional)', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>		
	
		
		<div class="bunyad-cta-groups">
		
		<?php foreach ($boxes as $key => $value): $count++; ?>
		
		
		<fieldset class="bunyad-cta-group">
			<legend><?php echo esc_html_x('CTA Box', 'Admin', 'cheerup'); ?></legend>
			<p>
				<label><?php echo esc_html_x('Heading: (Required)', 'Admin', 'cheerup'); ?></label>
				<input class="widefat" name="<?php echo esc_attr($this->get_field_name('boxes') .'['. $key . '][heading]'); 
					?>" type="text" value="<?php echo esc_attr($value['heading']); ?>" />
					
			</p>
					
			<p>
				<label><?php echo esc_html_x('Image URL:', 'Admin', 'cheerup'); ?></label>
				<input class="widefat" name="<?php echo esc_attr($this->get_field_name('boxes') .'['. $key . '][image]'); 
					?>" type="text" value="<?php echo esc_attr($value['image']); ?>" />
					
			</p>
			
			<p>
				<label><?php echo esc_html_x('Link To:', 'Admin', 'cheerup'); ?></label>
				<input class="widefat" name="<?php echo esc_attr($this->get_field_name('boxes') .'['. $key . '][link]'); 
					?>" type="text" value="<?php echo esc_attr($value['link']); ?>" />
					
			</p>
		
		</fieldset>
		
		<?php endforeach; ?>
		
		</div>
		
		<p class="small"><strong><?php echo esc_html_x('Note:', 'Admin', 'cheerup'); ?></strong>
			<?php echo esc_html_x('Save to add one more CTA box. Leaving the Heading field empty for any CTA box will remove it.', 'Admin', 'cheerup'); ?></p>
		
		<script>
		jQuery(function($) { 
			$('.bunyad-cta-groups').sortable();
		});
		</script>
	
		<?php
	}
}