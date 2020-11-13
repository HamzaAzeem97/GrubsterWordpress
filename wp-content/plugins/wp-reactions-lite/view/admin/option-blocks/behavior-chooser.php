<?php

use WP_Reactions\Lite\Helper;
use WP_Reactions\Lite\Configuration;
use WP_Reactions\Lite\Shortcode;
use WP_Reactions\Lite\FieldManager\Switcher;

$tooltip1 = $tooltip2 = '';
extract($data);
$is_regular = (Configuration::$current_options['activation'] == 'true' and Configuration::$current_options['behavior'] == 'regular');

?>
<div class="wpe-behaviors">
    <div class="row">
        <div class="col-md-6">
            <div class="option-wrap">
                <div class="wpra-features">
                    <div class="wpra-features-item border-bottom-1">
                        <div class="wpra-features-item-title">
                            <h3><?php _e('Classic Reactions Lite', 'wpreactions-lite'); ?></h3>
                            <div class="wpra-behavior-chooser">
                                <?php
                                (new Switcher())
                                    ->setId('regular')
                                    ->setName('global_behavior')
                                    ->setValue($is_regular)
                                    ->setChecked(true)
                                    ->build();
                                ?>
                            </div>
                        </div>
                        <p> <?php _e('Switch on to activate your Emoji Reactions sitewide. One-click activation deploys your reactions on all pages and posts after your content. To customize and override factory settings, click the customize now button to get started.', 'wpreactions-lite'); ?> </p>
                    </div>
                    <div class="wpra-features-item border-bottom-1">
                        <div class="wpra-features-item-title">
                            <h3> <?php _e('Classic Reactions', 'wpreactions-lite'); ?>
                                <div class="wpra-pro-badge">PRO</div>
                            </h3>
                            <div class="wpra-behavior-chooser">
                                <?php
                                //		                    Helper::tooltip( $tooltip1 );
                                (new Switcher())
                                    ->setId('pro_feature_1')
                                    ->setName('pro_feature_1')
                                    ->setValue(true)
                                    ->setChecked(false)
                                    ->setDisabled(true)
                                    ->build();
                                ?>
                            </div>
                        </div>
                        <p> <?php _e('Get 200 JoyPixels 3.1 Premium Licensed Emojis. All Lottie Animated and SVG Emojis and 9 Social Platforms. WooCommerce page integration and Custom Posts types. Generate and manage Shortcode easily and paste your reactions anywhere. Set your own user counts, Get statistics on emotional data and much more. This is a pro feature.', 'wpreactions-lite'); ?> </p>
                    </div>
                    <div class="wpra-features-item">
                        <div class="wpra-features-item-title">
                            <h3> <?php _e('Reaction Button', 'wpreactions-lite'); ?>
                                <div class="wpra-pro-badge">PRO</div>
                            </h3>
                            <div class="wpra-behavior-chooser">
                                <?php
                                //		                    Helper::tooltip( $tooltip1 );
                                (new Switcher())
                                    ->setId('pro_feature_1')
                                    ->setName('pro_feature_1')
                                    ->setValue(true)
                                    ->setChecked(false)
                                    ->setDisabled(true)
                                    ->build();
                                ?>
                            </div>
                        </div>
                        <p><?php _e('The ultimate button for creating user engagement, social sharing and improving SEO on pages and posts. The Reaction Button shares all of the core features of Reactions Pro including Joy Pixels 3.1 Animated Emojis and a social popup that will keep your audience sharing. You get to create your button any way you like and your emojis will do the rest. This is a pro feature.', 'wpreactions-lite'); ?> </p>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="option-wrap">
                <?php
                $active_class = ($options['activation'] == 'true') ? 'p-active' : '';
                ?>
                <div class="primary-color-blue wpra-light-activation-title <?php echo $active_class; ?>">
                    <span><?php _e('Your Reactions are live!', 'wpreactions-lite'); ?></span>
                    <span><?php _e('Your Reactions are not showing', 'wpreactions-lite'); ?></span>
                    <div class="reset-button-holder">
                        <span class="wpra-reset-options">
                            <span class="dashicons dashicons-image-rotate"></span>
                            <span><?php _e('Reset', 'wpreactions-lite'); ?></span>
                        </span>
                        <?php Helper::tooltip('reset-button'); ?>
                    </div>
                </div>
                <div class="wpra-behavior-preview" style="min-height: 410px;">
                    <?php
                    $behavior_preview = $options;
                    $behavior_preview['post_id'] = 'preview_lite_classic';
                    echo Shortcode::build($behavior_preview);
                    ?>
                </div>
            </div>
            <button id="customize"
                    class="btn btn-open-blue btn-lg w-100" <?php Helper::is_disabled(Configuration::$current_options['activation']); ?>>
                <span class="dashicons dashicons-admin-customizer"></span>
                <?php _e('Customize Now', 'wpreactions-lite'); ?>
            </button>
        </div>
    </div>
</div>
