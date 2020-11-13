<?php

namespace WP_Reactions\Lite;

class AjaxHandler
{
    function __construct()
    {
        $ajax_actions = array(
            array(
                'name' => 'wpra_save_options',
                'func' => array($this, 'save_options'),
                'admin_only' => true,
            ),
            array(
                'name' => 'wpra_preview',
                'func' => array($this, 'preview'),
                'admin_only' => true,
            ),
            array(
                'name' => 'wpra_reset_options',
                'func' => array($this, 'reset_option'),
                'admin_only' => true,
            ),
            array(
                'name' => 'wpra_react',
                'func' => array($this, 'react'),
                'admin_only' => false,
            ),
            array(
                'name' => 'wpra_submit_feedback',
                'func' => array($this, 'submit_feedback'),
                'admin_only' => true,
            ),
            array(
                'name' => 'wpra_get_doc_links',
                'func' => array($this, 'get_doc_links'),
                'admin_only' => false,
            )
        );
        $this->register_ajax_actions($ajax_actions);
    }

    static function init()
    {
        $instance = new self();
        return $instance;
    }

    function register_ajax_actions($ajax_actions)
    {
        foreach ($ajax_actions as $action) {
            $admin_only = false;
            if (isset($action['admin_only'])) {
                $admin_only = $action['admin_only'];
            }
            $this->register_ajax_action($action['name'], $action['func'], $admin_only);
        }
    }

    function register_ajax_action($name, $func, $admin_only = true)
    {
        add_action('wp_ajax_' . $name, $func);
        if (!$admin_only) {
            add_action('wp_ajax_nopriv_' . $name, $func);
        }
    }

    function save_options()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $received = json_decode(stripslashes($_POST['options']), true);

        if (isset($_POST['single']) and $_POST['single'] == 1) {
            $save = Configuration::$current_options;
            foreach ($received as $opt => $val) {
                $save[$opt] = $val;
            }
        } else {
            $save = $received;
        }

        if (update_option(WPRA_LITE_OPTIONS, json_encode($save))) {
            echo "1";
        } else {
            echo "0";
        }

        wp_die();
    }

    function preview()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_POST['options'])) {
            $options = json_decode(stripslashes($_POST['options']), true);
        } else {
            $options = Configuration::$default_options;
        }
        $options['post_id'] = 'ajax_preview_lite';
        echo Shortcode::build($options);
        wp_die();
    }

    function reset_option()
    {
        if (!current_user_can('manage_options')) {
            return;
        }
        $defaults = Configuration::$default_options;
        $defaults['activation'] = 'true';
        $res = update_option(WPRA_LITE_OPTIONS, json_encode($defaults, true));
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
        wp_die();
    }

    function submit_feedback()
    {
        $email = sanitize_email($_POST['email']);
        $message = sanitize_text_field($_POST['message']);
        $raiting = sanitize_text_field($_POST['raiting']);

        $resp = wp_remote_post(Configuration::FEEDBACK_API,
            [
                'method' => 'POST',
                'body' => [
                    'email' => $email,
                    'message' => $message,
                    'raiting' => $raiting,
                    'secure' => 'daEFZIqbUpouTLibklIVhqyg8XDKHNOW',
                ],
            ]
        );

        $status_code = wp_remote_retrieve_response_code($resp);

        if (is_wp_error($resp) and $status_code != 200) {
            $result['status'] = 'error';
            $result['message'] = 'Something went wrong';
        } else {
            $result['status'] = 'success';
            $result['message_title'] = __('Thank you for your Feedback!', 'wpreactions-lite');
            $result['message'] = __('Your message has been received and we are working on it!', 'wpreactions-lite');
        }
        echo json_encode($result);
        wp_die();
    }

    function react()
    {
        global $wpdb;
        $is_valid_request = check_ajax_referer('wpra-react-action', 'checker', false);

        if (!$is_valid_request) {
            echo "Invalid request";
            wp_die();
        }

        $reacted_to = sanitize_text_field($_POST['reacted_to']);
        $post_id = sanitize_text_field($_POST['post_id']);
        $react_id = '';
        $is_already_reacted = 0;
        $tbl = Configuration::$tbl_reacted_users;

        if (isset($_COOKIE['react_id'])) {
            $react_id = $_COOKIE['react_id'];
            $is_already_reacted = $wpdb->get_var(
                $wpdb->prepare("SELECT count(*) FROM {$tbl} WHERE bind_id = %s and react_id = %s", $post_id, $react_id)
            );
        }
        if ($react_id != '' and $is_already_reacted != 0) {
            $res = $wpdb->update(
                Configuration::$tbl_reacted_users,
                array(
                    'bind_id' => $post_id,
                    'react_id' => $react_id,
                    'reacted_to' => $reacted_to,
                    'reacted_date' => date('Y-m-d H:i:s'),
                ),
                array('react_id' => $react_id, 'bind_id' => $post_id)
            );
        } else {
            if ($react_id == '') {
                $react_id = uniqid();
                setcookie('react_id', $react_id, time() + (86400 * 365), "/"); // 86400 = 1 day
            }

            $res = $wpdb->insert(
                Configuration::$tbl_reacted_users,
                array(
                    'bind_id' => $post_id,
                    'react_id' => $react_id,
                    'reacted_to' => $reacted_to,
                    'reacted_date' => date('Y-m-d H:i:s'),
                )
            );
        }
        if ($res !== false) {
            echo 'success';
        } else {
            echo 'error';
        }
        wp_die();
    } // end of react
}
