<?php
/**
 * The Instagram widget class.
 */
class Bunyad_Instagram_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'null-instagram-feed', // Kept to preserve old widgets from "WP Instagram Widget"
			esc_html__( 'Instagram', 'bunyad-instagram-widget' ),
			array(
				'classname'   => 'bunyad-instagram-feed',
				'description' => esc_html__( 'Displays your latest Instagram photos', 'bunyad-instagram-widget' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	/**
	 * @inheritDoc
	 */
	public function widget( $args, $instance ) {

		$title    = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$username = empty( $instance['username'] ) ? '' : $instance['username'];
		$limit    = empty( $instance['number'] ) ? 9 : intval( $instance['number'] );
		$size     = empty( $instance['size'] ) ? 'large' : $instance['size'];
		$target   = empty( $instance['target'] ) ? '_self' : $instance['target'];
		$link     = empty( $instance['link'] ) ? '' : $instance['link'];
		$access_token = empty( $instance['access_token'] ) ? '' : $instance['access_token'];

		// @ is allowed in username but need not be used below.
		$username = str_replace( '@', '', $username );

		echo $args['before_widget'];

		if ( ! empty( $title ) ) { 
			echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title'];
		}

		do_action( 'wpiw_before_widget', $instance );

		if ( '' !== $username ) {

			$media_array = $this->get_from_instagram( $username, $access_token, $limit );

			if ( is_wp_error( $media_array ) ) {

				echo wp_kses_post( $media_array->get_error_message() );
				
				// Guide the logged in admin what to do about it.
				if ( ! $access_token && current_user_can( 'edit_theme_options' ) ) {
					echo sprintf(
						' ' . esc_html__( 'You most likely need to use an %sAccess Token%s.', 'bunyad-instagram-widget' ),
						'<a href="https://cheerup.theme-sphere.com/documentation/#bunyad-instagram-token" target="_blank">',
						'</a>'
					);
				}

			} 
			else {

				// Make sure it's an array.
				$media_array = (array) $media_array;

				// Filter for images only?
				if ( $images_only = apply_filters( 'wpiw_images_only', false ) ) {
					$media_array = array_filter( $media_array, array( $this, 'images_only' ) );
				}

				$media_array = apply_filters( 'wpiw_media_array', $media_array, $instance );

				// Slice list down to required limit.
				$media_array = array_slice( $media_array, 0, $limit );

				// Filters for custom classes.

				// instagram-pics class kept for legacy.
				$ulclass  = apply_filters( 'wpiw_list_class', 'spc-insta-media instagram-pics instagram-size-' . $size );
				$liclass  = apply_filters( 'wpiw_item_class', 'spc-insta-item' );
				$aclass   = apply_filters( 'wpiw_a_class', 'spc-insta-image-wrap' );
				$imgclass = apply_filters( 'wpiw_img_class', 'spc-insta-image' );
				$template_part = apply_filters( 'wpiw_template_part', 'parts/wp-instagram-widget.php' );

				?>
				
				<ul class="<?php echo esc_attr( $ulclass ); ?>">
				<?php
				
				foreach ( $media_array as $item ) {

					$item['link'] = preg_replace( '/^https?\:/i', '', $item['link'] );

					// copy the else line into a new file (parts/wp-instagram-widget.php) within your theme and customise accordingly.
					if ( locate_template( $template_part ) !== '' ) {
						include locate_template( $template_part );
					} 
					else {
						echo '<li class="' . esc_attr( $liclass ) . '"><a href="' . esc_url( $item['link'] ) . '" target="' . esc_attr( $target ) . '"  class="' . esc_attr( $aclass ) . '"><img src="' . esc_url( $item[$size] ) . '"  alt="' . esc_attr( $item['description'] ) . '" title="' . esc_attr( $item['description'] ) . '"  class="' . esc_attr( $imgclass ) . '"/></a></li>';
					}
				}

				?>
				</ul>
				
				<?php
			}
		}

		$linkclass  = apply_filters( 'wpiw_link_class', 'spc-insta-link clear' );
		$linkaclass = apply_filters( 'wpiw_linka_class', '' );
		$url = '//instagram.com/' . $username;
		
		if ( '' !== $link ) {
			?>
			<p class="<?php echo esc_attr( $linkclass ); ?>">
				<a href="<?php echo trailingslashit( esc_url( $url ) ); ?>" rel="me" target="<?php echo esc_attr( $target ); ?>" 
					class="<?php echo esc_attr( $linkaclass ); ?>">
					<i class="tsi tsi-instagram"></i> <?php echo wp_kses_post( $link ); ?>
				</a>
			</p>
			<?php
		}

		do_action( 'wpiw_after_widget', $instance );

		echo $args['after_widget'];
	}

	/**
	 * @inheritDoc
	 */
	public function form( $instance ) {

		$instance = wp_parse_args( (array) $instance, array(
			'title' => '',
			'access_token' => '',
			'username' => '',
			'size' => 'large',
			'link' => esc_html__( 'Follow Me!', 'bunyad-instagram-widget' ),
			'number' => 6,
			'target' => '_self',
		) );

		$title    = $instance['title'];
		$username = $instance['username'];
		$number   = absint( $instance['number'] );
		$size     = $instance['size']; 
		$target   = $instance['target'];
		$link     = $instance['link'];

		?>

		
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_html_e( 'Title', 'bunyad-instagram-widget' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></label></p>
		
		<div>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>">
					<?php esc_html_e( 'Username', 'bunyad-instagram-widget' ); ?>: 
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'username' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'username' ) ); ?>" type="text" value="<?php echo esc_attr( $username ); ?>" />
					</label>
			</p>
			<!-- <p class="description">When using Access Token, the token must be generated when logged into the account with this username.</p> -->
		</div>

		<p><label for="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>"><?php esc_html_e( 'Number of photos', 'bunyad-instagram-widget' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'number' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'number' ) ); ?>" type="number" value="<?php echo esc_attr( $number ); ?>" /></label></p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>"><?php esc_html_e( 'Photo size', 'bunyad-instagram-widget' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'size' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'size' ) ); ?>" class="widefat">
				<option value="thumbnail" <?php selected( 'thumbnail', $size ); ?>><?php esc_html_e( 'Thumbnail', 'bunyad-instagram-widget' ); ?></option>
				<option value="small" <?php selected( 'small', $size ); ?>><?php esc_html_e( 'Small', 'bunyad-instagram-widget' ); ?></option>
				<option value="large" <?php selected( 'large', $size ); ?>><?php esc_html_e( 'Large', 'bunyad-instagram-widget' ); ?></option>
			</select>
		</p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>"><?php esc_html_e( 'Open links in', 'bunyad-instagram-widget' ); ?>:</label>
			<select id="<?php echo esc_attr( $this->get_field_id( 'target' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'target' ) ); ?>" class="widefat">
				<option value="_self" <?php selected( '_self', $target ); ?>><?php esc_html_e( 'Current window (_self)', 'bunyad-instagram-widget' ); ?></option>
				<option value="_blank" <?php selected( '_blank', $target ); ?>><?php esc_html_e( 'New window (_blank)', 'bunyad-instagram-widget' ); ?></option>
			</select>
		</p>
		<p><label for="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>"><?php esc_html_e( 'Link text', 'bunyad-instagram-widget' ); ?>: <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'link' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'link' ) ); ?>" type="text" value="<?php echo esc_attr( $link ); ?>" /></label></p>

		<div>
			<p>
				<label for="<?php echo esc_attr( $this->get_field_id( 'access_token' ) ); ?>">
					<?php esc_html_e( 'Access Token (Optional)', 'bunyad-instagram-widget' ); ?>: 
					<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'access_token' ) ); ?>" 
						name="<?php echo esc_attr( $this->get_field_name( 'access_token' ) ); ?>" type="text" value="<?php echo esc_attr( $instance['access_token'] ); ?>" />
				</label>
			</p>
			<p class="description">
				If the feed is not displaying, you will need an access token for Instagram API. Read 
				<a href="https://cheerup.theme-sphere.com/documentation/#bunyad-instagram-token" target="_blank">&raquo; How to get Access token</a>.
			</p>
		</div>

		<?php

	}

	/**
	 * Save the widget data.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance['title']    = sanitize_text_field( $new_instance['title'] );
		$instance['username'] = sanitize_text_field( $new_instance['username'] );
		$instance['number']   = ! absint( $new_instance['number'] ) ? 9 : intval( $new_instance['number'] );
		$instance['size']     = ! empty( $new_instance['size'] ) ? $new_instance['size'] : 'large';
		$instance['target']   = $new_instance['target'];
		$instance['link']     = sanitize_text_field( $new_instance['link'] );

		$instance['access_token'] = sanitize_text_field( $new_instance['access_token'] );

		// Delete any errors for throttle.
		delete_transient( $this->get_throttle_id() );

		// Registering a shutdown handler as Customizer has a limitation where two options
		// cannot be updated on widget save handler.
		add_action( 'shutdown', function() {
			
			// Expire the current cache.
			update_option( 
				$this->get_storage_id(),
				array( 'refresh_in' => time() - 3600 ) 
			);
		} );

		// For non-token: Request maybe cached if persistent object cache exists.
		wp_cache_delete( 'bunyad-instagram-' . $instance['username'] );

		return $instance;
	}

	/**
	 * Scrape from instagram while handling errors gracefully to prevent a flood.
	 * 
	 * @param string $username
	 * @return WP_Error|array
	 */
	public function get_from_instagram( $username, $access_token, $count = 6 ) {

		$username = trim( strtolower( $username ) );

		// Return errors, or previously cached data, if any throttled errors exist.
		if ( true !== ( $cache_or_errors = $this->_throttle_errors( $username ) ) ) {
			return $cache_or_errors;
		}

		$result = apply_filters( 
			'bunyad_instagram_cached_data', 
			get_option( $this->get_storage_id() ),
			$username
		);

		// No cached results or expired cache.
		if ( false === $result || $result['refresh_in'] < time() ) {

			// Use Instagram API if there's a token, or else scrape.
			if ( ! empty( $access_token ) ) {
				$result = $this->media_api( $access_token, $count );
			}
			else {
				$result = $this->media_scrape( $username );
			}
			
			if ( !is_wp_error( $result ) ) {

				$cache_time = apply_filters( 'null_instagram_cache_time', HOUR_IN_SECONDS * 12 );

				// Lower cache time for empty results - possibly private or empty accounts.
				// Still cached for 10 minutes to prevent excessive requests.
				if ( empty( $result ) ) {
					$cache_time = apply_filters( 'null_instagram_cache_time_empty',  600 );
				}

				$instagram_data = array(
					'refresh_in' => time() + $cache_time,
					'data'       => $result,
				);

				update_option( $this->get_storage_id(), $instagram_data, false );
				$result = $instagram_data;
			}
		}

		// On error, set a throttle transient and extend data transient.
		if ( is_wp_error( $result ) ) {
			set_transient( $this->get_throttle_id(), $result, 600 );
			return $this->_throttle_errors( $username );
		}

		// Possibly empty or private account.
		if ( empty( $result['data'] ) ) {
			return new WP_Error( 'no_images', esc_html__( 'Instagram did not return any images.', 'bunyad-instagram-widget' ) );
		}

		return $result['data'];
	}

	/**
	 * If errors' been registered (a 10 mins default throttle), deliver previously 
	 * successful results (if ever recorded), or return stored WP_Error object.
	 */
	public function _throttle_errors( $username ) {

		// Throttles can be disabled via filter.
		if ( true === apply_filters( 'bunyad_instagram_disable_throttle', false ) ) {
			return true;
		}

		$throttled_errors = get_transient( $this->get_throttle_id() );
		if ( ! $throttled_errors ) {
			return true;
		}

		$previous_results = get_option( $this->get_storage_id() );

		return !empty( $previous_results['data'] ) ? $previous_results['data'] : $throttled_errors;
	}

	/**
	 * Get media from Instagram API.
	 * 
	 * @param string $username
	 * @param string $access_token
	 * @return array|WP_Error
	 */
	public function media_api( $access_token, $count = 6 ) {

		$url = 'https://api.instagram.com/v1/users/self/media/recent?' . http_build_query( array(
			'count'        => $count,
			'access_token' => $access_token
		) );
		
		$remote = wp_remote_get( $url );
		$response_code = wp_remote_retrieve_response_code( $remote );
		$response_data = wp_remote_retrieve_body( $remote );
		
		if ( is_wp_error( $remote ) ) {
			return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'bunyad-instagram-widget' ) );
		}

		if ( 200 !== $response_code ) {

			// Invalid token error would be returned as 400.
			if ( 400 === $response_code && strstr( $response_data, 'OAuthAccessTokenException' ) ) {
				return new WP_Error( 
					'invalid_token', 
					sprintf( esc_html__( 'The provided Access Token is invalid.', 'bunyad-instagram-widget' ) )
				);
			}

			return new WP_Error( 
				'invalid_response', 
				sprintf( 
					esc_html__( 'Instagram did not return a 200. Got %s', 'bunyad-instagram-widget' ), 
					$response_code
				)
			);
		}

		$data = json_decode( $response_data );
		if ( ! is_object( $data ) || ! isset( $data->data ) ) {
			return new WP_Error( 'bad_json', esc_html__( 'Instagram API did not return an object.', 'bunyad-instagram-widget' ) );
		}

		$media_data = array();
		foreach ( $data->data as $key => $media ) {

			if ( empty( $media->images ) ) {
				continue;
			}

			$images = $media->images;

			$media_data[] = array(
				'description' => isset( $media->caption->text ) ? $media->caption->text : esc_html__( 'Instagram Image', 'bunyad-instagram-widget' ),
				'link'        => isset( $media->link ) ? $media->link : '',
				'time'        => isset( $media->created_time ) ? $media->created_time : '',
				'comments'    => isset( $media->comments->count ) ? intval( $media->comments->count ) : '',
				'likes'       => isset( $media->likes->count ) ? intval( $media->likes->count ) : '',
				'type'        => isset( $media->type ) ? $media->type : 'image',

				// Images
				'thumbnail'   => isset( $images->thumbnail->url ) ? $images->thumbnail->url : '',
				'small'       => isset( $images->low_resolution->url ) ? $images->low_resolution->url : '',
				'large'       => isset( $images->standard_resolution->url ) ? $images->standard_resolution->url : '',

				// For backward compatibility only. Original is not available in the API.
				'original'    => isset( $images->standard_resolution->url ) ? $images->standard_resolution->url : '',
			);
		}

		return $media_data;
	}

	/**
	 * Scrape content off live instagram site. 
	 * 
	 * Originally based on https://gist.github.com/cosmocatalano/4544576 and wp-instagram-widget by Scott.
	 * 
	 * @param $username
	 * @return array|WP_Error
	 */
	public function media_scrape( $username ) {

		$url = 'https://instagram.com/' . $username;

		// Remote requests returns are cached for the duration of script at least.
		// Or longer if persistent cache is present. Prevents dual requests.
		if ( $cache = wp_cache_get( 'bunyad-instagram-' . $username ) ) {
			$remote = $cache;
		}
		else {
			$remote = wp_safe_remote_get( $url , array(
				'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1',
				'headers'    => array(
					'Accept-language' => 'en-US,en;q=0.9',
				),
			) );

			// Cache with 10 mins expiry (if persistent cache).
			wp_cache_set( 'bunyad-instagram-' . $username, $remote, '', 600 );
		}

		if ( is_wp_error( $remote ) ) {
			return new WP_Error( 'site_down', esc_html__( 'Unable to communicate with Instagram.', 'bunyad-instagram-widget' ) );
		}

		if ( 200 !== wp_remote_retrieve_response_code( $remote ) ) {
			return new WP_Error( 'invalid_response', esc_html__( 'Instagram did not return a 200.', 'bunyad-instagram-widget' ) );
		}

		$shards      = explode( 'window._sharedData = ', $remote['body'] );
		$insta_json  = explode( ';</script>', $shards[1] );
		$insta_array = json_decode( $insta_json[0], true );

		if ( ! $insta_array ) {
			return new WP_Error( 'bad_json', esc_html__( 'Instagram has returned invalid data.', 'bunyad-instagram-widget' ) );
		}

		if ( isset( $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
			$images = $insta_array['entry_data']['ProfilePage'][0]['graphql']['user']['edge_owner_to_timeline_media']['edges'];
		} 
		else {
			return new WP_Error( 'bad_json_2', esc_html__( 'Instagram has returned invalid data.', 'bunyad-instagram-widget' ) );
		}

		if ( ! is_array( $images ) ) {
			return new WP_Error( 'bad_array', esc_html__( 'Instagram has returned invalid data.', 'bunyad-instagram-widget' ) );
		}

		$instagram = array();

		foreach ( $images as $image ) {
			if ( true === $image['node']['is_video'] ) {
				$type = 'video';
			} else {
				$type = 'image';
			}

			$caption = __( 'Instagram Image', 'bunyad-instagram-widget' );
			if ( ! empty( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
				$caption = wp_kses( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
			}

			$instagram[] = array(
				'description' => $caption,
				'link'        => trailingslashit( '//instagram.com/p/' . $image['node']['shortcode'] ),
				'time'        => $image['node']['taken_at_timestamp'],
				'comments'    => $image['node']['edge_media_to_comment']['count'],
				'likes'       => $image['node']['edge_liked_by']['count'],
				'thumbnail'   => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ),
				'small'       => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ),
				'large'       => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][4]['src'] ),
				'original'    => preg_replace( '/^https?\:/i', '', $image['node']['display_url'] ),
				'type'        => $type,
			);
		}

		return $instagram;
	}

	/**
	 * Gets the id we use for storage and caches.
	 * 
	 * @return string
	 */
	public function get_storage_id() {
		return 'bunyad-instagram-cache-' . $this->id;
	}

	/**
	 * Get throttle transient ID.
	 */
	public function get_throttle_id() {
		return 'bunyad-instagram-throttle-' . $this->id;
	}
	
	/**
	 * Filter callback to remove non-image media.
	 */
	public function images_only( $media_item ) {

		if ( 'image' === $media_item['type'] ) {
			return true;
		}

		return false;
	}
}
