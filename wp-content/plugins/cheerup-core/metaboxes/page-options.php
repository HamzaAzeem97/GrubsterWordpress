<?php
/**
 * Render options metabox for pages
 */

include apply_filters('bunyad_metabox_options_dir', get_theme_file_path('admin/meta/options')) . '/page.php';

$options = $this->options(
	apply_filters('bunyad_metabox_page_options', $options)
);

?>

<div class="bunyad-meta cf">

<?php foreach ($options as $element): ?>
	
	<div class="option <?php echo esc_attr($element['name']); ?>">
		<span class="label"><?php echo esc_html($element['label']); ?></span>
		<span class="field">
			<?php echo $this->render($element); // Bunyad_Admin_OptionRenderer::render() ?>
		
			<?php if (!empty($element['desc'])): ?>
			
			<p class="description"><?php echo esc_html($element['desc']); ?></p>
		
			<?php endif;?>
		
		</span>		
	</div>
	
<?php endforeach; ?>

</div>

<script>
/**
 * Conditional show/hide 
 */
jQuery(function($) {
	$('._bunyad_featured_slider select').on('change', function() {

		var depend_default = '._bunyad_slider_number, ._bunyad_slider_tags, ._bunyad_slider_type, ._bunyad_slider_post_ids';

		// hide all dependents
		$(depend_default).hide();
		
		if ($(this).val() == 1) {
			$(depend_default).show();
		}

		return;
	});

	// on-load
	$('._bunyad_featured_slider select').trigger('change');
		
});
</script>