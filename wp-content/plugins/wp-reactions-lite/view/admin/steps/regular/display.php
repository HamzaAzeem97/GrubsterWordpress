<?php
use WP_Reactions\Lite\Helper;
?>
<?php
Helper::getOptionBlock( 'emoji-picker' );
?>

<div class="row half-divide">
	<?php
	Helper::getOptionBlock( 'animation-state' );
	Helper::getOptionBlock( 'emoji-size' );
	?>
</div>
<?php
Helper::getOptionBlock( 'live-counts' );
Helper::getOptionBlock( 'placement' );
?>

