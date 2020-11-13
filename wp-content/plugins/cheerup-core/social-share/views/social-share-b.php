<?php
/**
 * Partial template for Alternate Social share buttons on single page
 */

$services = Bunyad::get('cheerup_social')->share_services();
$active   = apply_filters(
	'bunyad_social_share_b_active',
	array('facebook', 'twitter', 'pinterest', 'linkedin', 'tumblr', 'email')
);

?>

<?php if (is_single() && Bunyad::options()->single_share): ?>
	
	<div class="post-share-b cf<?php echo (Bunyad::amp() && Bunyad::amp()->active() ? ' all' : ''); ?>">
		
		<?php 
			foreach ($active as $key): 
				
				if (!isset($services[$key])) {
					continue;
				}

				$service = $services[$key];
		?>
		
			<a href="<?php echo esc_url($service['url']); ?>" class="cf service <?php echo esc_attr($key); ?>" target="_blank" title="<?php echo esc_attr($service['label'])?>">
				<i class="<?php echo esc_attr($service['icon']); ?>"></i>
				<span class="label"><?php echo esc_html($service['label']); ?></span>
			</a>
				
		<?php endforeach; ?>
		
		<?php if (count($active) > 2): ?>
			<a href="#" class="show-more"><i class="tsi tsi-plus"></i></a>
		<?php endif; ?>
		
	</div>
	
<?php endif; ?>