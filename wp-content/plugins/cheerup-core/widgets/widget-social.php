<?php
/**
 * Social Icons Widget
 */
class CheerUp_Widgets_Social extends WP_Widget 
{

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() 
	{
		parent::__construct(
			'bunyad-widget-social',
			esc_html_x('CheerUp - Social Icons', 'Admin', 'cheerup'),
			array('description' => esc_html_x('Social icons widget.', 'Admin', 'cheerup'),  'classname' => 'widget-social')
		);
	}
	
	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget($args, $instance) 
	{
		// No icons to show?
		if (empty($instance['social'])) {
			return;
		}
		
		$title = apply_filters('widget_title', $instance['title']);

		
		echo $args['before_widget'];
		
		?>
		
			<?php if (!empty($title)): ?>
				
				<?php
					echo $args['before_title'] . esc_html($title) . $args['after_title']; // before_title/after_title are built-in WordPress sanitized
				?>
				
			<?php endif; ?>
		
			<div class="social-icons">
				
				<?php 
				
				/**
				 * Show Social icons
				 */
				$services = Bunyad::get('social')->get_services();
				$links    = Bunyad::options()->social_profiles;
				
				foreach ( (array) $instance['social'] as $icon):
					$social = $services[$icon];
					$url    = !empty($links[$icon]) ? $links[$icon] : '#';
				?>
					<a href="<?php echo esc_url($url); ?>" class="social-link" target="_blank"><i class="<?php echo esc_attr($social['icon']); ?>"></i>
						<span class="visuallyhidden"><?php echo esc_html($social['label']); ?></span></a>
				
				<?php
				endforeach;
				?>
				
			</div>
		
		<?php

		echo $args['after_widget'];
	}
	
	/**
	 * Save widget.
	 * 
	 * Strip out all HTML using wp_kses
	 * 
	 * @see wp_kses_post()
	 */
	public function update($new, $old)
	{
		foreach ($new as $key => $val) {

			// Social just needs intval
			if ($key == 'social') {
				
				array_walk($val, 'intval');
				$new[$key] = $val;

				continue;
			}
			
			// Filter disallowed html 			
			$new[$key] = wp_kses_post_deep($val);
		}
		
		return $new;
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form($instance)
	{
		$defaults = array('title' => '', 'social' => array());
		$instance = array_merge($defaults, (array) $instance);
		
		extract($instance);
				
		$icons = array(
			'facebook'  => esc_html_x('Facebook', 'Admin', 'cheerup'),
			'twitter'   => esc_html_x('Twitter', 'Admin', 'cheerup'),
			'gplus'     => esc_html_x('Google Plus', 'Admin', 'cheerup'),
			'instagram' => esc_html_x('Instagram', 'Admin', 'cheerup'),
			'pinterest' => esc_html_x('Pinterest', 'Admin', 'cheerup'),
			'vimeo'     => esc_html_x('Vimeo', 'Admin', 'cheerup'),	
			'tumblr'    => esc_html_x('Tumblr', 'Admin', 'cheerup'),
			'rss'       => esc_html_x('RSS', 'Admin', 'cheerup'),
			'bloglovin' => esc_html_x('BlogLovin', 'Admin', 'cheerup'),
			'youtube'   => esc_html_x('Youtube', 'Admin', 'cheerup'),
			'dribbble'  => esc_html_x('Dribbble', 'Admin', 'cheerup'),
			'linkedin'  => esc_html_x('LinkedIn', 'Admin', 'cheerup'),
			'flickr'    => esc_html_x('Flickr', 'Admin', 'cheerup'),
			'soundcloud' => esc_html_x('SoundCloud', 'Admin', 'cheerup'),
			'lastfm'     => esc_html_x('Last.fm', 'Admin', 'cheerup'),
			'vk'         => esc_html_x('VKontakte', 'Admin', 'cheerup'),
			'steam'      => esc_html_x('Steam', 'Admin', 'cheerup'),
		);
		
		?>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html_x('Title:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<div>
			<label for="<?php echo esc_attr($this->get_field_id('social')); ?>"><?php echo esc_html_x('Social Icons:', 'Admin', 'cheerup'); ?></label>
			
			<?php foreach ($icons as $icon => $label): ?>
			
				<p>
					<label>
						<input class="widefat" type="checkbox" name="<?php echo esc_attr($this->get_field_name('social')); ?>[]" value="<?php echo esc_attr($icon); ?>"<?php 
						echo (in_array($icon, $social) ? ' checked' : ''); ?> /> 
					<?php echo esc_html($label); ?></label>
				</p>
			
			<?php endforeach; ?>
			
			<p class="bunyad-note"><strong><?php echo esc_html_x('Note:', 'Admin', 'cheerup'); ?></strong>
				<?php echo esc_html_x('Configure URLs from Customize > General Settings > Social Media Links.', 'Admin', 'cheerup'); ?></p>
			
		</div>
	
	
		<?php
	}
}
