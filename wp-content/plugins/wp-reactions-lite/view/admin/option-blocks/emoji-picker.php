<?php
use WP_Reactions\Lite\Helper;
use WP_Reactions\Lite\Configuration;
?>
<div class="option-wrap">
    <p class="drag-and-drop"><span class="dashicons dashicons-move"></span> <?php _e('DRAG & DROP TO ARRANGE', 'wpreactions-lite'); ?></p>
    <div class="picked-emojis">
        <?php foreach (Configuration::$current_options['emojis'] as $emoji => $emoji_id) { ?>
            <div class="picked-emoji" data-emoji_id="<?php echo $emoji_id; ?>">
                <div class="emoji-svg-holder" title="<?php echo Configuration::$emoji_names[$emoji_id]; ?>"
                 style="background-image: url('<?php echo Helper::getAsset("emojis/svg/{$emoji_id}.svg"); ?>')"></div>
            </div>
        <?php } ?>
    </div>
</div>
