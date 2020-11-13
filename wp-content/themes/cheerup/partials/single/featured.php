<?php 
/**
 * Partial: Featured image/video/gallery part of single post
 */

$props = isset($props) ? $props : [];
$props = array_replace([
	'context' => is_single() ? 'single' : 'large'
], $props);

if (is_single() && Bunyad::posts()->meta('featured_disable')) {
	return;
}

// For large posts, the setting that controls crops is different.
$featured_crop = $props['context'] == 'single' ? 'single_featured_crop' : 'post_large_featured_crop';

?>
	
	<div class="featured">
	
		<?php if (get_post_format() == 'gallery' && !Bunyad::amp()->active()): // get gallery template ?>
		
			<?php get_template_part('partials/gallery-format'); ?>
			
		<?php elseif (Bunyad::posts()->meta('featured_video')): // featured video available? ?>
		
			<div class="featured-vid">
				<?php echo apply_filters('bunyad_featured_video', esc_html(Bunyad::posts()->meta('featured_video'))); ?>
			</div>
			
		<?php elseif (has_post_thumbnail()): ?>
		
			<?php
				/**
				 * Normal featured image when no post format
				 */
				$caption = get_post(get_post_thumbnail_id())->post_excerpt;
				$url     = get_permalink();
				
				// On single page? Link to image
				if (is_single()):
					$url = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full'); 
					$url = $url[0];
				endif;
				
				// Whether to use an uncropped (original aspect ratio) image.
				if (!Bunyad::options()->get($featured_crop)) {
					$image = Bunyad::helpers()->relative_width() > 67 ? 'cheerup-full' : 'cheerup-main-uc';
				}
				else {
					$image = Bunyad::helpers()->relative_width() > 67 ? 'cheerup-main-full' : 'cheerup-main';
				}

				Bunyad::media()->the_image(
					$image,
					[
						'link'     => $url,
						'bg_image' => false,
					]
				);
			
			?>
			
		<?php endif; // normal featured image ?>
		
	</div>
