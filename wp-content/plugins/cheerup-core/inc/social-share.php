<?php
/**
 * Social sharing buttons
 */
class CheerUp_SocialShare
{
	/**
	 * Get an array of sharing services with links
	 */
	public function share_services($post_id = '') 
	{
		if (empty($post_id)) {
			$post_id = get_the_ID();
		}
		
		// Post and media URL
		$url   = rawurlencode(get_permalink($post_id));
		$media = rawurlencode(
			wp_get_attachment_url(get_post_thumbnail_id($post_id))
		);

		$the_title = get_post_field('post_title', $post_id, 'raw');
		$title     = rawurlencode($the_title);
		
		// Social Services
		$services = array(
			'facebook' => array(
				'label' => esc_html__('Share on Facebook', 'cheerup'),
				'icon'  => 'tsi tsi-facebook',
				'url'   => 'https://www.facebook.com/sharer.php?u=' . $url,
			),
				
			'twitter' => array(
				'label' => esc_html__('Share on Twitter', 'cheerup'), 
				'icon'  => 'tsi tsi-twitter',
				'url'   => 'https://twitter.com/intent/tweet?url=' . $url . '&text=' . $title,
			),

			'pinterest' => array(
				'label' => esc_html__('Pinterest', 'cheerup'), 
				'icon'  => 'tsi tsi-pinterest',
				'url'   => 'https://pinterest.com/pin/create/button/?url='. $url . '&media=' . $media . '&description=' . $title,
				'key'   => 'sf_instagram_id',
			),
			
			'linkedin' => array(
				'label' => esc_html__('LinkedIn', 'cheerup'), 
				'icon'  => 'tsi tsi-linkedin',
				'url'   => 'https://www.linkedin.com/shareArticle?mini=true&url=' . $url,
			),
				
			'tumblr' => array(
				'label' => esc_html__('Tumblr', 'cheerup'), 
				'icon'  => 'tsi tsi-tumblr',
				'url'   => 'https://www.tumblr.com/share/link?url=' . $url . '&name=' . $title,
			),

			'vk'     => array(
				'label' => esc_html__('VKontakte', 'cheerup'),
				'icon'  => 'tsi tsi-vk',
				'url'   => 'https://vk.com/share.php?url='. $url .'&title=' . $title,
			),
				
			'email'  => array(
				'label' => esc_html__('Email', 'cheerup'),
				'icon'  => 'tsi tsi-envelope-o',

				// rawurlencode to preserve + properly
				'url'   => 'mailto:?subject='. $title .'&body=' . $url,
			),
		);
		
		return apply_filters('bunyad_social_share_services', $services);
	}

	/**
	 * Render social sharing
	 */
	public function render($type = '')
	{
		include CheerUp_Core::instance()->path . 'social-share/views/' . sanitize_file_name($type) . '.php';
	}
}

// init and make available in Bunyad::get('cheerup_social')
Bunyad::register('cheerup_social', array(
	'class' => 'CheerUp_SocialShare',
	'init' => true
));