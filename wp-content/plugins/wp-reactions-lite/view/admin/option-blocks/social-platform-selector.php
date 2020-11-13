<?php
use WP_Reactions\Lite\Helper;
use WP_Reactions\Lite\FieldManager\Checkbox;
?>
<div class="option-wrap social-platforms">
    <div class="option-header">
        <h4>
            <span><?php _e( 'Social Share Buttons', 'wpreactions' ); ?></span>
            <?php Helper::tooltip('social-platform-selector'); ?>
        </h4>
        <small><?php _e( 'Choose social media platforms and personalize button text.', 'wpreactions' ); ?></small>
    </div>
    <div class="row mb-0 mb-md-3 mt-3">
        <?php
        ( new Checkbox)
            ->addCheckbox(
                'social_platforms_facebook',
                $options['social_platforms']['facebook'],
                '',
                'true',
                false,
                array(
                    'icon'        => 'facebook.svg',
                    'id'          => 'social_labels_facebook',
                    'value'       => $options['social_labels']['facebook'],
                    'placeholder' => __( 'Facebook', 'wpreactions' ),
                )
            )
            ->addCheckbox(
                'social_platforms_twitter',
                $options['social_platforms']['twitter'],
                '',
                'true',
                false,
                array(
                    'icon'        => 'twitter.svg',
                    'id'          => 'social_labels_twitter',
                    'value'       => $options['social_labels']['twitter'],
                    'placeholder' => __( 'Tweeter', 'wpreactions' ),
                )
            )
            ->addCheckbox(
                'social_platforms_email',
                $options['social_platforms']['email'],
                '',
                'true',
                false,
                array(
                    'icon'        => 'email.svg',
                    'id'          => 'social_labels_email',
                    'value'       => $options['social_labels']['email'],
                    'placeholder' => __( 'Email', 'wpreactions' ),
                )
            )
            ->addClasses( 'col-md-4 checkbox-input' )
            ->build();
        ?>
    </div>
    <div class="pro-social-platforms pt-3">
        <div class="option-header">
            <h4>
                <span><?php _e( 'Social Share Buttons', 'wpreactions' ); ?></span>
<!--                --><?php //Helper::tooltip('social-platform-selector'); ?>
                <span class="wpra-pro-badge">PRO</span>
            </h4>
        </div>
        <div class="row mb-md-3 mt-0 mt-md-3 ">
            <?php
            ( new Checkbox )
                ->addCheckbox(
                    'social_platforms_linkedin',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'linkedin.svg',
                        'id'          => 'social_labels_linkedin',
                        'value'       => '',
                        'placeholder' => __( 'Linkedin', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addCheckbox(
                    'social_platforms_pinterest',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'pinterest.svg',
                        'id'          => 'social_labels_pinterest',
                        'value'       => '',
                        'placeholder' => __( 'Pinterest', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addCheckbox(
                    'social_platforms_gmail',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'gmail.svg',
                        'id'          => 'social_labels_gmail',
                        'value'       => '',
                        'placeholder' => __( 'Gmail', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addClasses( 'col-md-4 checkbox-input' )
                ->setDisabled(true)
                ->build();
            ?>
        </div>
        <div class="row">
            <?php
            ( new Checkbox )
                ->addCheckbox(
                    'social_platforms_messenger',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'messenger.svg',
                        'id'          => 'social_labels_messenger',
                        'value'       => '',
                        'placeholder' => __( 'Messenger', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addCheckbox(
                    'social_platforms_reddit',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'reddit.svg',
                        'id'          => 'social_labels_reddit',
                        'value'       => '',
                        'placeholder' => __( 'Reddit', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addCheckbox(
                    'social_platforms_whatsapp',
                    '',
                    '',
                    'true',
                    false,
                    array(
                        'icon'        => 'whatsapp.svg',
                        'id'          => 'social_labels_whatsapp',
                        'value'       => '',
                        'placeholder' => __( 'Whatsapp', 'wpreactions' ),
                        'disabled' => true,
                    )
                )
                ->addClasses( 'col-md-4 checkbox-input' )
                ->setDisabled(true)
                ->build();
            ?>
        </div>
    </div>
</div>
