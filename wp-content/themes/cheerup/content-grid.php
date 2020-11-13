<?php
/**
 * Grid posts style used for several loops
 */

extract(array(
	'show_excerpt'    => true,
	'show_footer'     => true,
	
	// Social only valid if show_footer is also true.
	'show_social'     => true,
	'show_read_more'  => Bunyad::options()->post_grid_read_more,

	'excerpt_length'  => Bunyad::options()->post_excerpt_grid,
	'grid_cols'       => 2,
	'title_size'      => '',
	'classes'         => get_post_class('grid-post'),
	'read_more_style' => 'read-more-' . Bunyad::options()->read_more_style,
	'align'           => Bunyad::options()->post_grid_align

), EXTR_SKIP);

$classes = array_merge(
	$classes,
	array(
		$show_excerpt ? 'has-excerpt' : 'no-excerpt', 
		'grid-post-c' . $grid_cols,
		$title_size ? 'title-' . $title_size : '',
	)
);

$meta_args = [];

// Match meta alignment to specified.
if ($align) {
	$meta_args['align'] = $align;
}

/**
 * Custom ratio - when a custom ratio is available, most of the images will fallback
 * to uncropped large or cheerup-full.
 */
$media_ratio   = Bunyad::helpers()->get_ratio('post_grid_ratio_c' . $grid_cols, 'post_grid_ratio');
$image_options = ['ratio' => $media_ratio];
$image         = 'cheerup-grid';

// 1 or 2 columns at max/1170px width.
if ($grid_cols !== 3 && Bunyad::helpers()->relative_width() == 100) {
	$image = 'cheerup-main';
}

// Masonry style.
if (Bunyad::options()->post_grid_masonry) {

	$image = 'cheerup-masonry';

	// Force bg images for masonry.
	$image_options['bg_image'] = true;
	
	// 1 or 2 columns at max/1170px width.
	if ($grid_cols !== 3 && Bunyad::helpers()->relative_width() == 100) {
		$image = 'cheerup-full';
	}
}

?>

<article <?php
	// hreview has to be first class because of rich snippet classes limit 
	Bunyad::markup()->attribs('grid-post-wrapper', array(
		'id'     => 'post-' . get_the_ID(),
		'class'  => $classes
	)); ?>>
	
	<div class="post-header cf">
			
		<div class="post-thumb">
		
			<?php
				Bunyad::media()->the_image($image, $image_options); 
			?>

			<?php get_template_part('partials/post-format'); ?>
			
			<?php Bunyad::helpers()->meta_cat_label(); ?>
			
		</div>
		
		<div class="meta-title">
		
			<?php Bunyad::helpers()->post_meta('grid', $meta_args); ?>
		
		</div>
		
	</div><!-- .post-header -->

	<?php if (!empty($show_excerpt)): ?>
	<div class="post-content post-excerpt cf">
		<?php

		// Excerpts or main content?
		echo Bunyad::posts()->excerpt(null, $excerpt_length, ['add_more' => false]);

		?>
			
	</div><!-- .post-content -->
	<?php endif; ?>
	
	<?php if ($show_read_more): ?>
		
		<a href="<?php the_permalink(); ?>" class="read-more-link <?php echo esc_attr($read_more_style); ?>">
			<?php echo esc_html(Bunyad::posts()->more_text); ?>
		</a>

	<?php endif; ?>

	<?php if ($show_footer && $show_social): ?>
	<div class="post-footer">
		
		<?php if (class_exists('CheerUp_Core')): ?>
			<?php 
				// See plugins/cheerup-core/social-share/views/social-share-inline.php
				Bunyad::get('cheerup_social')->render('social-share-inline');
			?>
		<?php endif;?>

	</div>
	<?php endif; ?>
	
		
</article>
