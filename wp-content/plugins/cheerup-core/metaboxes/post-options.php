<?php
/**
 * Meta box for post options
 */

include apply_filters('bunyad_metabox_options_dir', get_theme_file_path('admin/meta/options')) . '/post.php';

$options = $this->options(
	apply_filters('bunyad_metabox_post_options', $options)
);

?>

<div class="bunyad-meta cf">

	<input type="hidden" name="bunyad_meta_box" value="post">

<?php foreach ($options as $element): ?> 
	
	<div class="option <?php echo esc_attr($element['name']); ?>">
		<span class="label"><?php echo esc_html(isset($element['label_left']) ? $element['label_left'] : $element['label']); ?></span>
		<span class="field">

			<?php echo $this->render($element); // XSS ok. Bunyad_Admin_OptionRenderer::render() ?>
		
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
	var _global = '<?php echo esc_js(Bunyad::options()->post_layout_template); ?>';

	$('[name=_bunyad_layout_template]').on('change', function() {

		var current = $(this).filter(':checked').val();
		if (current == 'magazine' || (!current && _global == 'magazine')) {
			$('._bunyad_sub_title').show();
		}
		else {
			$('._bunyad_sub_title').hide();
		}

		return;
	})
	 .trigger('change');
		
});
</script>