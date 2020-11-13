<?php

namespace WP_Reactions\Lite;

class Main extends PluginExtension
{

    private $DB;

    function __construct()
    {
        global $wpdb;
        $this->DB = $wpdb;

        register_activation_hook(WPRA_LITE_PLUGIN_PATH . 'wp-reactions-lite.php', array($this, "activation"));

        add_filter('the_content', array($this, 'content_adder'));
        add_action('init', array($this, 'wp_init'));
        add_filter('plugin_action_links_' . WPRA_LITE_PLUGIN_BASENAME, array($this, 'action_links'));

        add_action('plugins_loaded', function () {
            load_plugin_textdomain(
                'wpreactions-lite',
                false,
                dirname(WPRA_LITE_PLUGIN_BASENAME) . '/languages/'
            );
        });

        // init necessary plugin components
        Configuration::init();
        UpdateHandler::init();
        AjaxHandler::init();
        AdminPages::init();
        Metaboxes::render();
    }

    // run necessary actions on plugin activation
    function activation()
    {
        if (class_exists('WP_Reactions\Pro\Main')) {
            deactivate_plugins('wp-reactions-lite/wp-reactions-lite.php');
            $message = '<p style="color:red;margin-bottom: 10px;font-size: 16px;">' . __('Sorry. You have WP Reactions Pro plugin activated. Please disable it first and activate Lite plugin', 'wpreactions-lite') . '</p>';
            $message .= '<a href="' . admin_url('plugins.php') . '">' . __('Back to Plugins', 'wpreactions-lite') . '</a>';
            wp_die($message);
        }
        Activation::start();
    }

    function wp_init()
    {
        $this->loadPluginAssets();
    }

    function loadPluginAssets()
    {
        $hooks = array(
            'toplevel_page_wpra-global-options',
            'wp-reactions_page_wpra-support',
            'wp-reactions_page_wpra-pro',
        );

        $this->enqueueMedia($hooks);

        // add plugin styles
        $this->addAdminAsset('style', 'wpra_admin_bootstrap_css', Helper::getAsset('vendor/bootstrap/css/bootstrap.min.css'), $hooks);
        $this->addAdminAsset('style', 'wpra_admin_css', Helper::getAsset('css/admin.css', true), $hooks);
        $this->addAdminAsset('style', 'wpra_front_css', Helper::getAsset('css/front.css'), $hooks);
        $this->addAdminAsset('style', 'wpra_g_fonts', 'https://fonts.googleapis.com/css?family=Open+Sans:300,400,500,600,700');
        $this->addAdminAsset('style', 'wpra_common_css', Helper::getAsset('css/common.css?t=' . time()));
        $this->addAdminAsset('style', 'wpra_post_css', Helper::getAsset('css/post.css?t=' . time()));
        $this->addAdminAsset('style', 'wpra_minicolor_css', Helper::getAsset('vendor/minicolor/jquery.minicolors.css'), $hooks);

        // add plugin scripts

        $this->addAdminAsset(
            'script',
            'wpra_minicolor_js',
            Helper::getAsset('vendor/minicolor/jquery.minicolors.min.js'),
            $hooks,
            array('jquery'),
            array(),
            true
        );
        $this->addAdminAsset(
            'script',
            'wpra_front_js',
            Helper::getAsset('js/front.js', true),
            $hooks,
            array('jquery'),
            array(),
            true
        );
        $this->addAdminAsset(
            'script',
            'jquery.ui.touch-punch.min',
            Helper::getAsset('vendor/jquery.ui.touch-punch.min.js'),
            $hooks
        );

        $this->addAdminAsset(
            'script',
            'wpra_lottie',
            Helper::getAsset('vendor/lottie/lottie.min.js'),
            $hooks,
            array('jquery'),
            array(),
            true
        );

        $this->addAdminAsset(
            'script',
            'wpra_post_js',
            Helper::getAsset('js/post.js'),
            array('post-new.php', 'post.php')
        );

        $locals = array(
            'object' => 'wpra',
            'vars' => array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'msg_options_updated' => __('Options updated successfully', 'wpreactions-lite'),
                'msg_options_updating' => __('Updating options...', 'wpreactions-lite'),
                'msg_getting_preview' => __('Getting preview...', 'wpreactions-lite'),
                'msg_resetting_options' => __('Resetting to factory settings...', 'wpreactions-lite'),
                'msg_reset_done' => __('Factory settings have been successfully updated...', 'wpreactions-lite'),
                'msg_reset_confirm' => __('Are you sure you want to reset to our factory settings?', 'wpreactions-lite'),
                'default_options' => Configuration::$default_options,
                'current_options' => Configuration::$current_options,
                'global_lp' => Helper::getAdminPage('global'),
                'global_prev_step' => __('Previous Step', 'wpreactions-lite'),
                'global_next_step' => __('Next Step', 'wpreactions-lite'),
                'global_go_back' => __('Go Back', 'wpreactions-lite'),
                'global_start_over' => __('Start Over', 'wpreactions-lite'),
                'emojis_path' => WPRA_LITE_PLUGIN_URL . 'assets/emojis/',
                'version' => WPRA_LITE_VERSION,
            )
        );

        $this->addAdminAsset(
            'script',
            'wpra_admin_js',
            Helper::getAsset('js/admin.js', true),
            $hooks,
            array('jquery', 'wpra_front_js'),
            $locals
        );

        // add plugin frontend styles
        $this->addFrontAsset('style', 'wpra_front_css', Helper::getAsset('css/front.css', true));
        $this->addFrontAsset('style', 'wpra_common_css', Helper::getAsset('css/common.css', true));

        $vars = array(
            'object' => 'wpra',
            'vars' => array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'emojis_path' => WPRA_LITE_PLUGIN_URL . 'assets/emojis/',
                'version' => WPRA_LITE_OPTIONS,
            )
        );
        $this->addFrontAsset('script', 'wpra_front_js', Helper::getAsset('js/front.js'), array('jquery'), $vars);
        $this->addFrontAsset('script', 'wpra_lottie', Helper::getAsset('vendor/lottie/lottie.min.js'));

        $this->flushAssets();
    }

    function isEmojisShown()
    {
        global $post;

        $screens = array('page', 'post');
        $current_screen = get_post_type($post);

        if (!is_singular($screens)) {
            return false;
        }

        if (Configuration::$current_options['activation'] == 'false') {
            return false;
        }

        $allow_emojis = get_post_meta($post->ID, '_wpra_show_emojis', true);
        $user_screen = Configuration::$current_options['display_where'];

        if (!empty($allow_emojis) and $allow_emojis == 'true') {
            return true;
        }

        if (!empty($allow_emojis) and $allow_emojis == 'false') {
            return false;
        }

        if (($user_screen == 'both' or $user_screen == $current_screen)) {
            return true;
        }

        return false;
    }

    function content_adder($content)
    {

        // add emojis if only we are in main query and looping
        if (!(in_the_loop() and is_main_query())) {
            return $content;
        }

        if (!$this->isEmojisShown()) {
            return $content;
        }

        $result = '';

        $reactions = Shortcode::build(Configuration::$current_options);
	    $reactions = str_replace(["\r", "\n", "\r\n"], '', $reactions);

	    $before = '';
        $after = '';

        if (Configuration::$current_options['content_position'] == 'before') {
            $before = $reactions;
        } else if (Configuration::$current_options['content_position'] == 'after') {
            $after = $reactions;
        } else {
            $before = $reactions;
            $after = $reactions;
        }

        $result .= $before . $content . $after;
        return $result;
    }

    function getActiveEmojis()
    {
        $emojis = [];
        foreach (Configuration::$current_options['emojis'] as $emoji => $id) {
            if ($id != -1) {
                $emojis[$emoji] = $id;
            }
        }
        return $emojis;
    }

    function getFakeCounts($post_id)
    {
        $fake_counts = get_post_meta($post_id, '_wpra_start_counts', true);
        if (is_array($fake_counts) and !empty($fake_counts)) {
            // This is added to transition from 6 emoji to 7.
            // It will be removed for later versions
            if (count($fake_counts) < 7) {
                $fake_counts['reaction7'] = 0;
            }
            return array_map('intval', $fake_counts);
        } else {
            $fake_counts = array();
            foreach (Configuration::$default_options['emojis'] as $emoji => $id) {
                $fake_counts[$emoji] = 0;
            }

            return $fake_counts;
        }
    }

    function parseFakeCounts($counts_text)
    {
        $named = [];
        $counts = explode(',', $counts_text);
        $i = 0;
        foreach (Configuration::$default_options['emojis'] as $emoji => $id) {
            if (isset($counts[$i]) and !empty($counts[$i])) {
                $named[$emoji] = intval($counts[$i]);
            } else {
                $named[$emoji] = 0;
            }
            $i++;
        }

        return $named;
    }

    function make_doc_links()
    {
        ob_start();
        foreach (Configuration::$doc_links as $link) { ?>
            <li>
                <span class="dashicons dashicons-yes"></span>
                <a target="_blank" href="<?php echo $link['url']; ?>"><?php echo $link['name']; ?></a>
                <span class="dashicons dashicons-external"></span>
            </li>
            <?php
        }
        $out = '<ul>' . ob_get_clean() . '</ul>';
        echo $out;
    }

    function action_links($links)
    {
        $links[] = '<a href="' . Helper::getAdminPage('global') . '">' . __('Settings', 'wpreactions-lite') . '</a>';
        $links[] = '<a href="' . Helper::getAdminPage('support') . '">' . __('Support', 'wpreactions-lite') . '</a>';
        return $links;
    }

} // end of WP Emoji class
