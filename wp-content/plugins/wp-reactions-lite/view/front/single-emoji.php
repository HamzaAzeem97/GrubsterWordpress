<?php
use WP_Reactions\Lite\Helper;
global $wpra_lite;
$count_color = $data['options']['count_color'];
$animation = $data['options']['animation'];
$show_count = $data['options']['show_count'];
?>
<div class="<?php echo $data['emoji']; ?> wpra-reaction <?php if ( $data['already'] == $data['emoji'] ) { echo 'active ';} ?>"
    data-count="<?php if ( $data['start_count'] > 0 ) {echo $data['start_count'];} ?>"
    data-emoji_id="<?php echo $data['emoji_id']; ?>">
    <div class="wpra-plus-one">+1</div>
    <?php if ($show_count == 'true') { ?>
	<div style="background-color: <?php echo $count_color; ?>" class="wpra-arrow-badge arrow-bottom-left <?php if ( $data['start_count'] == 0 ) {echo 'hide-count';} ?>">
		<span style="border-top-color: <?php echo $count_color; ?>" class="tail"></span>
		<span style="color: <?php echo $data['options']['count_text_color']; ?>" class="count-num"><?php echo $data['start_count_fmt']; ?></span>
	</div>
    <?php }
    if ($animation == 'false') { ?>
        <div class="wpra-reaction-static-holder" style="background-image: url('<?php echo Helper::getAsset("emojis/svg/{$data['emoji_id']}.svg"); ?>')"></div>
        <?php
    }
    ?>
</div>
