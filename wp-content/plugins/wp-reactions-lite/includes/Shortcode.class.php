<?php
namespace WP_Reactions\Lite;

/*
Class for Shortcode Handling
*/

class Shortcode
{
    static function singlefy(array $options)
    {
        $shortcode = array();
        foreach ($options as $option => $value) {
            if (is_array($value)) {
                foreach ($value as $sub_option => $sub_value) {
                    $shortcode[$option . '_' . $sub_option] = $sub_value;
                }
            } else {
                $shortcode[$option] = $value;
            }
        }

        return $shortcode;
    }

    static function build($options)
    {
        $content = null;
        $shortcode = self::singlefy($options);
        return self::output($shortcode, $content);
    }

    static function output($atts, $content = null)
    {
        global $wpra_lite, $post, $wpdb;

        $global_defaults = Configuration::$default_options;
        $defaults = self::singlefy($global_defaults);

        $post_related = array(
            'post_id' => '',
            'start_counts' => '',
        );

        $defaults = array_merge($defaults, $post_related);
        $a = shortcode_atts($defaults, $atts);

        $post_id = $a['post_id'] == '' ? $post->ID : $a['post_id'];
        $start_counts = empty($a['start_counts']) ?
            $wpra_lite->getFakeCounts($post_id) :
            $wpra_lite->parseFakeCounts($a['start_counts']);

        $tbl = Configuration::$tbl_reacted_users;

        $already = '';
        if (isset($_COOKIE['react_id'])) {
            $already = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT reacted_to FROM {$tbl} WHERE bind_id = %s and react_id = %s",
                    $post_id, $_COOKIE['react_id']
                )
            );
        }

        if ($a['show_count'] == 'true') {
            // if no start counts passed then make them zero
            $reacts_db = $wpdb->get_results(
                "select reacted_to, count(*) as count from {$tbl} where bind_id = '{$post_id}' group by reacted_to",
                ARRAY_A
            );

            foreach ($reacts_db as $react_db) {
                $start_counts[$react_db['reacted_to']] += intval($react_db['count']);
            }
            $show_count = 'true';
        } else {
            $show_count = 'false';
        }


        $title_styles = "color: {$a['title_color']};";
        $title_styles .= "font-size: {$a['title_size']};";
        $title_styles .= "font-weight: {$a['title_weight']};";
        if ($a['show_title'] == 'false') {
            $title_styles .= 'display: none';
        }

        $reactions_styles = "border-color: {$a['border_color']};";
        $reactions_styles .= "border-width: {$a['border_width']};";
        $reactions_styles .= "border-radius: {$a['border_radius']};";
        $reactions_styles .= "border-style: {$a['border_style']};";

        if ($a['bgcolor_trans'] == 'true') {
            $reactions_styles .= 'background: transparent;';
        } else {
            $reactions_styles .= "background: {$a['bgcolor']};";
        }

        if ($a['shadow'] == 'false') {
            $reactions_styles .= 'box-shadow: none;';
        }

        $flex_aligns = array(
            'left' => 'flex-start',
            'right' => 'flex-end',
            'center' => 'center',
        );

        $wrap_styles = "justify-content: {$flex_aligns[$a['align']]};";

        $share_platforms_out = '';
        if ($a['enable_share_buttons'] != 'false') {
            $social_wrap_class = ' wpra-share-buttons-' . $a['social_button_type'];
            $social_icon_color = '#ffffff';
            $social_btn_style = "border-radius: {$a['social_border_radius']};";
            ob_start();
            ?>
            <div class="wpra-share-wrap <?php echo $social_wrap_class; ?>"
                 style="<?php if ($a['enable_share_buttons'] == 'always') {
                     echo 'display: flex;';
                 } ?>">
                <?php
                $social_colors = array(
                    'facebook' => '#3b5998',
                    'twitter' => '#00acee',
                    'email' => '#424242',
                );

                $i = 0;
                foreach (Configuration::$default_options['social_platforms'] as $platform => $status) {
                    if ($a['social_platforms_' . $platform] == 'true') {
                        $label = empty($a['social_labels_' . $platform]) ? Configuration::$default_options['social_labels'][$platform] : $a['social_labels_' . $platform];
                        if ($a['social_button_type'] == 'bordered') {
                            $social_icon_color = $social_colors[$platform];
                        }
                        ?>
                        <a class="share-btn share-btn-<?php echo $platform; ?>"
                           style="<?php echo $social_btn_style; ?>">
                            <span class="share-btn-icon">
                                <?php Helper::getSocialIcon($platform, $social_icon_color); ?>
                            </span>
                            <span><?php echo $label; ?></span>
                        </a>
                        <?php
                    }
                    $i++;
                }
                ?>
            </div> <!-- end of share buttons -->
            <?php
            $share_platforms_out = ob_get_clean();
        }

        ob_start(); ?>
        <div class="wpra-reactions-wrap wpra-plugin-container" style="<?php echo $wrap_styles; ?>;">
            <div class="wpra-reactions-container"
                 data-ver="<?php echo WPRA_LITE_VERSION; ?>"
                 data-post_id="<?php echo $post_id; ?>"
                 data-show_count="<?php echo $show_count; ?>"
                 data-enable_share="<?php echo $a['enable_share_buttons']; ?>"
                 data-behavior="<?php echo $a['behavior']; ?>"
                 data-animation="<?php echo $a['animation']; ?>"
                 data-share_url="<?php echo get_permalink($post); ?>"
                 data-secure="<?php echo wp_create_nonce('wpra-react-action'); ?>">
                <div class="wpra-call-to-action" style="<?php echo $title_styles; ?>"><?php echo $a['title_text']; ?></div>
                <div class="wpra-reactions wpra-static-emojis size-<?php echo $a['size']; ?>" style="<?php echo $reactions_styles; ?>">
                    <?php
                        foreach (Configuration::$default_options['emojis'] as $emoji => $default_id) {
                            if ($a['emojis_' . $emoji] != -1) {
                                Helper::getTemplate('/view/front/single-emoji', array(
                                    'options' => $a,
                                    'emoji' => $emoji,
                                    'emoji_id' => $a['emojis_' . $emoji],
                                    'already' => $already,
                                    'start_count' => $start_counts[$emoji],
                                    'start_count_fmt' => Helper::formatCount($start_counts[$emoji]),
                                ));
                            }
                        }
                    ?>
                </div>
                {share_platforms}
            </div> <!-- end of reactions container -->
        </div> <!-- end of reactions wrap -->
        <?php
        $reactions_out = ob_get_clean();
        return str_replace('{share_platforms}', $share_platforms_out, $reactions_out);
    }

} // end of class
