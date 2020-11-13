<?php
namespace WP_Reactions\Lite;

class Configuration {
	static public $default_options = array(
		"activation"           => "false",
		"behavior"             => "regular",
		"show_count"           => "true",
		"count_color"          => "#ff0015",
		"count_text_color"     => "#FFFFFF",
		"enable_share_buttons" => "onclick",
		"social_platforms"     => array(
			"facebook"  => "true",
			"twitter"   => "true",
			"email"     => "true",
		),
		"animation"            => "true",
		"show_title"           => "true",
		"title_text"           => "What’s your Reaction?",
		"title_size"           => "25px",
		"title_weight"         => "600",
		"title_color"          => "#000000",
		"emojis"               => array(
			'reaction1' => 1,
			'reaction2' => 2,
			'reaction3' => 3,
			'reaction4' => 4,
			'reaction5' => 5,
			'reaction6' => 6,
			'reaction7' => 7,
		),
		"bgcolor"              => "#FFFFFF",
		"bgcolor_trans"        => "false",
		"social_labels"        => array(
			"facebook"  => "Facebook",
			"twitter"   => "Twitter",
			"email"     => "Email",
		),
		"display_where"        => "both",
		"content_position"     => "after",
		"size"                 => "medium",
		"align"                => "center",
		"shadow"               => "true",
		"social_style_buttons" => "false",
		"social"               => array(
			"border_radius" => "30px",
			"border_color"  => "#303030",
			"text_color"    => "#303030",
			"bg_color"      => "#FFFFFF",
			"button_type"   => "bordered",
		),
		"border_radius"        => "50px",
		"border_color"         => "#FFFFFF",
		"border_width"         => "0px",
		"border_style"         => "solid",
	);
	static public $current_options = array();
	static public $tbl_reacted_users;
	static public $top_menu_items = array();
	static public $doc_links = array();
	static public $emoji_names = array();
	static public $scg_random_words = array();
	const MAX_EMOJIS = 7;
	const FEEDBACK_API = 'https://wpreactions.com/api/v1/submit_feedback';
	const DOC_LINKS_API = 'https://wpreactions.com/api/v1/doc_links';

	public static function init() {
		global $wpdb;

		self::$tbl_reacted_users = $wpdb->prefix . 'wpreactions_reacted_users';
		self::$current_options   = json_decode( get_option( WPRA_LITE_OPTIONS ), true );
		self::$top_menu_items    = array(
			array(
				'name'   => 'Dashboard',
				'link'   => Helper::getAdminPage( 'global' ),
				'icon'   => 'dashicons dashicons-dashboard',
				'target' => '',
			),
            array(
                'name'   => 'Support',
                'link'   => Helper::getAdminPage( 'support' ),
                'icon'   => 'dashicons dashicons-sos',
                'target' => '',
            ),
            array(
                'name'   => 'Pro',
                'link'   => Helper::getAdminPage( 'pro' ),
                'icon'   => 'dashicons dashicons-star-filled',
                'target' => '',
            ),
            array(
                'name'   => 'Feedback',
                'link'   => '#toggle-feedback-form',
                'icon'   => 'dashicons dashicons-testimonial',
                'target' => '',
            ),
		);
		self::$doc_links = array(
            array(
                'name' => 'WP Reactions Lite Introduction',
                'url'  => 'https://wpreactions.com/documentation/wp-reactions-lite/',
            ),
            array(
                'name' => 'Choosing emoji reaction sizes',
                'url'  => 'https://wpreactions.com/documentation/wp-reactions-lite/global-activation-step-1-setup/',
            ),
            array(
                'name' => 'Setting up fake user counts',
                'url'  => 'https://wpreactions.com/documentation/global-activation/on-page-options/',
            ),
            array(
                'name' => 'Personalize your social media buttons',
                'url'  => 'https://wpreactions.com/documentation/wp-reactions-lite/global-activation-step-3-social-media/',
            ),
            array(
                'name' => 'Accessing page analytics',
                'url'  => 'https://wpreactions.com/documentation/global-activation/on-page-options/',
            ),
            array(
                'name' => 'Setting up Overhead Badges',
                'url'  => 'https://wpreactions.com/documentation/wp-reactions-lite/global-activation-step-1-setup/'
            )
        );
		self::$emoji_names = array(
			"Unused",
			"Eye Blink",
			"Goofy Love",
			"Cool Guy",
			"Loud Laugh",
			"Wow",
			"Sleepy",
			"Disappointment",
		);
		self::$scg_random_words  = array(
			'Your emojis are set!',
		);
	}

    public static function syncNewOptions() {
        foreach (self::$default_options as $default_option => $option_value) {
            if (is_array($option_value)) {
                foreach ($option_value as $sub_option => $sub_option_value){
                    if (!array_key_exists($sub_option, self::$current_options[$default_option])) {
                        self::$current_options[$default_option][$sub_option] = $sub_option_value;
                    }
                }
            } else {
                if (!array_key_exists($default_option, self::$current_options)) {
                    self::$current_options[$default_option] = $option_value;
                }
            }
        }
        update_option(WPRA_LITE_OPTIONS, json_encode(self::$current_options));
    }

} // end of Configuration class
