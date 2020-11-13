<?php
/**
 * Latest tweets widget
 */
class CheerUp_Widgets_Twitter extends WP_Widget
{
	
	/**
	 * Setup the Widget
	 * 
	 * @see WP_Widget::__construct()
	 */
	public function __construct()
	{		
		parent::__construct(
			'bunyad-widget-twitter',
			esc_html_x('Cheerup - Twitter', 'Admin', 'cheerup'),
			array('description' => 'Show your latest tweets.', 'classname' => 'widget-twitter')
		);
	}
	
	/**
	 * Register the widget if the plugin is active
	 */
	public function register_widget() 
	{
		// Vendor libs needed from Sphere Core
		if (!defined('SPHERE_LIBS_VENDOR')) {
			return;
		}	
		
		register_widget(__CLASS__);
	}
	
	/**
	 * Output the widget
	 * 
	 * @see WP_Widget::widget()
	 */
	public function widget($args, $instance)
	{
		extract($args);
		extract($instance, EXTR_SKIP);
		
		$title = apply_filters('widget_title', $instance['title']);
		
		// Get tweet data
		$data = (array) $this->get_data($instance);
		
		?>
			
		<?php echo $before_widget; ?>

		<?php echo $before_title . $title . $after_title; ?>

		<?php 
			if (empty($instance['access_token'])) {
				echo '<p>Please configure this widget.</p>';
				return;
			}
		?>
		
		<div class="tweets">				

			<?php if (!empty($data)): ?>
	
				<ul>
				<?php 
					foreach ($data as $tweet): 
					
						// Match and convert URL patterns
						$tweet['text'] = preg_replace_callback('/((https?|s?ftp|ssh)\:\/\/[^"\s\<\>]*[^.,;\'">\:\s\<\>\)\]\!])/', array($this, '_convert_link'), $tweet['text']);
					
						// Convert mentions to user links
						$tweet['text'] = preg_replace_callback('/\B@(?P<user>[_a-z0-9]+)/i', array($this, '_convert_link'), $tweet['text']);
						
						// Convert hashtags to search
						$tweet['text'] = preg_replace_callback('/\B#(?P<search>[_a-z0-9]+)/i', array($this, '_convert_link'), $tweet['text']);
					
						// Relative time
						$time = human_time_diff(strtotime($tweet['created_at']));
				?>
				
					<li class="tweet">
						<p class="text"><?php echo wp_kses_post($tweet['text']); ?></p>
						<div class="meta">
							<a href="https://twitter.com/intent/tweet?in_reply_to=<?php echo esc_attr($tweet['id']); ?>" target="_blank" title="<?php 
								esc_attr_e('Reply', 'cheerup'); ?>"><i class="tsi tsi-reply"></i></a>
								
							<a href="https://twitter.com/intent/retweet?tweet_id=<?php echo esc_attr($tweet['id']); ?>" target="_blank" title="<?php 
								esc_attr_e('Retweet', 'cheerup'); ?>"><i class="tsi tsi-retweet"></i></a>
								
							<a href="https://twitter.com/intent/like?tweet_id=<?php echo esc_attr($tweet['id']); ?>" target="_blank" title="<?php 
								esc_attr_e('Like', 'cheerup'); ?>"><i class="tsi tsi-heart"></i></a>
								
							<a href="<?php echo esc_url('https://twitter.com/'. urlencode($username) . '/status/' . $tweet['id']); ?>" class="date" target="_blank"><?php 
								echo esc_html($time); ?></a>
						</div>
					</li>
				
				<?php endforeach; ?>
				</ul>
				
				<div><a href="https://twitter.com/<?php echo esc_attr($username); ?>" class="follow" title="<?php 
					esc_attr_e('Follow on twitter', 'cheerup'); ?>">@<?php esc_html_e($username); ?></a></div>
				
			<?php endif; ?>
			
		</div>
		
		<?php echo $after_widget; ?>
	
		<?php
	
	} // end widget function
	
	
	/**
	 * Regex callback: Convert links
	 *  
	 * @param array $match
	 */
	public function _convert_link($match)
	{
		$anchor = $match[1];
		$url    = $match[1];
		
		if (!empty($match['search'])) {
			$url = 'https://twitter.com/search?q=' . urlencode($anchor);
			$anchor = '#' . $anchor;
		}
		
		if (!empty($match['user'])) {
			$url = 'https://twitter.com/' . urlencode($anchor);
			$anchor = '@' . $anchor;
		}
		
		return '<a href="'. esc_url($url) .'" target="_blank">' . esc_html($anchor) . '</a>';
	}

	/**
	 * Wrapper to get data off cache or via twitter API - cache updated every 10 mins
	 */
	public function get_data($instance)
	{
		if (empty($instance['access_token']) OR empty($instance['access_secret'])) {
			return false;
		}
		
		
		$type = 'tweets';
		
		$data_store  = 'bunyad-transient-' . $type;
		$back_store  = 'bunyad-transient-backup-' . $type;
		$cache       = get_transient($data_store);
		$cache_mins = 60;
		 
		// No cache found?
		if ($cache === false) {
			$data = $this->get_twitter_data($instance);
			
			if ($data) {
				// Save a transient to expire in $cache_mins and a permanent backup option
				set_transient($data_store, $data, 60 * $cache_mins);
				update_option($back_store, $data);
			}
			// Fall to permanent backup store - no fresh data available
			else { 
				$data = get_option($back_store);
			}
			
			return $data;
		}
		else {
			return $cache;
		}
	}
	
	/**
	 * Fetch timeline from twitter api
	 * 
	 * @param array $data
	 */
	public function get_twitter_data($data)
	{
		extract($data);

		/*
		 * Twitter API
		 */
		include_once SPHERE_LIBS_VENDOR . 'twitter-api.php';
		
		$twitter = new TwitterAPIExchange(array(
			'oauth_access_token' => $data['access_token'],
			'oauth_access_token_secret' => $data['access_secret'],
			'consumer_key'    => $data['consumer_key'],
			'consumer_secret' => $data['consumer_secret']
		));
		
		$url    = 'https://api.twitter.com/1.1/statuses/user_timeline.json';
		$params = http_build_query(array(
			'screen_name' => $username, 
			'count'       => $show_num
		));
		
		// Perform the request
		try {
			$data = $twitter
				->setGetfield('?' . $params)
				->buildOauth($url, 'GET')
				->performRequest();
		}
		catch (Exception $e) {}
		
		$data = json_decode($data, true);
		
		return $data;
	}
	
	
	/**
	 * Update widget data and delete cache 
	 * 
	 * @see WP_Widget::update()
	 */
	public function update($new, $old)
	{
		foreach ($new as $key => $val) {
			$new[$key] = wp_kses_post_deep(trim($val));
		}
		
		delete_transient('bunyad_transient_tweets');
		
		$new['show_num'] = intval($new['show_num']);
		
		return $new;
	}
	
	public function form($instance)
	{
		$instance = array_merge(array(
			'title' => 'Twitter Widget', 
			'username' => '', 
			'consumer_key' => '', 
			'consumer_secret' => '', 
			'access_token' => '', 
			'access_secret' => '', 
			'show_num' => 3
		), (array) $instance);
		
		extract($instance);
		
		?>
	
		<p><a href="https://dev.twitter.com/apps" target="_blank"><?php echo esc_html_x('Create your Twitter App', 'Admin', 'cheerup'); ?></a></p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php echo esc_html_x('Title:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('username')); ?>"><?php echo esc_html_x('Username:', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('username')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('username')); ?>" type="text" value="<?php echo esc_attr($username); ?>" />
		</p>
	
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('consumer_key')); ?>"><?php echo esc_html_x('Consumer Key', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('consumer_key')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('consumer_key')); ?>" type="text" value="<?php echo esc_attr($consumer_key); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('consumer_secret')); ?>"><?php echo esc_html_x('Consumer Secret', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('consumer_secret')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('consumer_secret')); ?>" type="text" value="<?php echo esc_attr($consumer_secret); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('access_token')); ?>"><?php echo esc_html_x('Access Token', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('access_token')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('access_token')); ?>" type="text" value="<?php echo esc_attr($access_token); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('access_secret')); ?>"><?php echo esc_html_x('Access Token Secret', 'Admin', 'cheerup'); ?></label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('access_secret')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('access_secret')); ?>" type="text" value="<?php echo esc_attr($access_secret); ?>" />
		</p>
		
		<p>
			<label for="<?php echo esc_attr($this->get_field_id('show_num')); ?>"><?php echo esc_html_x('Number of Tweets:', 'Admin', 'cheerup'); ?></label>
			<input class="width100" id="<?php echo esc_attr($this->get_field_id('show_num')); ?>" name="<?php 
				echo esc_attr($this->get_field_name('show_num')); ?>" type="text" value="<?php echo esc_attr($show_num); ?>" />
		</p>
	
		<?php
	
	} // end form()
}