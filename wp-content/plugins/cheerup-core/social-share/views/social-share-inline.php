<?php
/**
 * Inline Social Share Buttons for Archives
 */

// External options
$options = array_merge(
	array(
		'heart' => Bunyad::options()->posts_likes,  
		'share' => 1
	),
	(!empty($options) ? $options : array())
);

$services = Bunyad::get('cheerup_social')->share_services();
if (strstr($services['pinterest']['icon'], 'tsi')) {
	$services['pinterest']['icon'] = 'tsi tsi-pinterest-p';
}

$active = apply_filters(
	'bunyad_social_share_inline_active',
	array('facebook', 'twitter', 'pinterest', 'email')
);

?>

<?php if ($options['share']): ?>	
	
	<ul class="social-share">

		<?php if ($options['heart'] && is_object(Bunyad::get('likes'))): ?>
			<li><?php Bunyad::get('likes')->heart_link(); ?></li>
		<?php endif; ?>


		<?php 
			// Output all the services icons.
			foreach ($active as $key): 
				
				if (!isset($services[$key])) {
					continue;
				}

				$service = $services[$key];
		?>
		
			<li>
				<a href="<?php echo esc_url($service['url']); ?>" class="<?php echo esc_attr($service['icon']); ?>" target="_blank" title="<?php 
					echo esc_attr($service['label'])?>"></a>
			</li>
				
		<?php endforeach; ?>

		<?php 
		/**
		 * A filter to programmatically add more share links
		 * 
		 * @param string 'inline' value denotes its displayed inline
		 */
		do_action('bunyad_post_social_icons', 'inline'); 
		?>

	</ul>

<?php endif; ?>
