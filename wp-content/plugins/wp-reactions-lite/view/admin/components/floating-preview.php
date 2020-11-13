<?php
use WP_Reactions\Lite\Helper;
global $wpra_lite;
$source = 'global';
extract($data);
?>
<div class="floating-preview" <?php if (isset($_GET['behavior'])) {echo 'style="display: block"';} ?>>
	<div class="floating-preview-button" data-source="<?php echo $source; ?>">
		<img src="<?php echo Helper::getAsset( 'images/eye.png' ); ?>">
	</div>
	<div class="floating-preview-holder">
		<div class="floating-preview-loading">
			<div class="wpra-spinner"></div>
			<div><?php _e( 'Getting preview...' ); ?></div>
		</div>
		<span class="floating-preview-close">&times;</span>
		<div class="floating-preview-result"></div>
	</div>
	<div class="text-live-preview">
		Preview
	</div>
</div>