<?php
use WP_Reactions\Lite\Helper;

global $wpra_lite;
?>
<div class="wpreactions primary-color-blue wpra-support-page">
    <!-- top bar -->
    <?php
    Helper::getTemplate(
        'view/admin/components/top-bar',
        array(
            "section_title" => "",
            "logo" => Helper::getAsset('images/wpj_logo.png'),
        )
    );
    ?>
    <div class="wpra-option-heading d-flex align-items-center justify-content-between heading-left">
        <div>
            <h4>
                <span><?php _e('Product Support', 'wpreactions-lite'); ?></span>
            </h4>
            <span><?php _e('We offer a few levels of customer support to make sure you are covered.', 'wpreactions-lite'); ?></span>
        </div>
        <a href="https://wpreactions.com/pricing" target="_blank" class="btn-blue"><i class="qa qa-star"></i><?php _e('Go Pro', 'wpreactions-lite'); ?></a>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="option-wrap">
                <h5 class="fw-700 mb-3"><span class="dashicons dashicons-format-aside"></span><?php _e('Visit our Docs', 'wpreactions-lite'); ?>
                </h5>
                <p><?php _e('Our documentation is extensive and well written for your convenience. All of the features and options
                    in this plugin are covered in our documentation section on our website. Here are some popular
                    topics.', 'wpreactions-lite'); ?> </p>
                <div id="doc-links" class="pro-features-list">
                    <?php $wpra_lite->make_doc_links(); ?>
                </div>
                <div class="mt-4">
                    <a class="btn btn-blue fw-500 w-100" href="https://wpreactions.com/documentation" target="_blank">
                        <?php _e('Visit Plugin Documentation', 'wpreactions-lite'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="wpra-banner-big-left option-wrap p-3 text-center">
                <span class="dashicons dashicons-wordpress-alt"
                      style="width: auto;height: auto;font-size: 60px;"></span>
                <h2 class="font-weight-bold"><?php _e('Wordpress Forum Support', 'wpreactions-lite'); ?></h2>
                <p class="text-muted"><a href="https://wpreactions.com/lite?page=directory" target="_blank"><?php _e('Find us in the directory', 'wpreactions-lite'); ?></a></p>
            </div>
            <div class="wpra-banner-big-left option-wrap p-3 text-center">
                <h2 class="font-weight-bold mt-4"><?php _e('Get Premium Support', 'wpreactions-lite'); ?></h2>
                <p><?php _e('Upgrade today and get Lottie animated emojis, premium support, automatic updates and tons of awesome features.', 'wpreactions-lite') ?></p>
                <a href="https://wpreactions.com/pricing" target="_blank" class="btn btn-lg btn-purple mb-4"><?php _e('Upgrade to PRO', 'wpreactions-lite'); ?></a>
            </div>
        </div>
    </div>
</div>
