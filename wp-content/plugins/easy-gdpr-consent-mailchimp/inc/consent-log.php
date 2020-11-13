<?php

namespace Sphere\EGCF;

/**
 * Consent Logging in CPT
 *
 * Initially based on https://github.com/mrxkon/consent-log/
 */

defined('WPINC') || exit;

/**
 * A class for logging consents in a sphere_consent_log
 */
class ConsentLog
{
	/**
	 * Setup
	 */
	public function init()
	{
		add_action('init', array($this, 'register_cpt'), 0);
		add_action('admin_menu', array($this, 'register_submenu'));
		add_action('wp_ajax_sphere_consent_log_remove', array($this, 'ajax_remove_consents'));
	}

	/**
	 * Register the consent log post type
	 */
	public function register_cpt()
	{
		register_post_type(
			'sphere_consent_log',
			array(
				'labels' => array(
					'name' => __('Consent Log', 'sphere-egcf'),
					'singular_name' => __('Consent Log', 'sphere-egcf'),
				),
				'public' => true,
				'show_in_menu' => false,
				'hierarchical' => false,
				'supports' => array(''),
				'show_in_nav_menus' => false,
			)
		);
	}

	/**
	 * Create Admin Page for Consent Log.
	 */
	public function register_submenu()
	{
		add_submenu_page(
			'edit.php?post_type=egcf_forms',
			'Consent Log',
			'Consent Log',
			'manage_options',
			'egcf-consent-log',
			array($this, 'create_admin_page')
		);
	}

	/**
	 * Ajax for removing consents of a User ID
	 */
	public function ajax_remove_consents()
	{
		check_ajax_referer('sphere_consent_log');

		$uid = sanitize_text_field($_POST['uid']);

		$args = array(
			'post_type' => 'sphere_consent_log',
			'posts_per_page' => '-1',
			'meta_query' => array(
				'_user_email' => array(
					'key' => '_cl_uid',
					'value' => $uid,
				),
			),
		);

		$query = new \WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()) {
				$query->the_post();
				wp_delete_post(get_the_ID());
			}
		}

		wp_send_json_success($uid);
	}

	/**
	 * Checks if the consent exists in the CPT
	 *
	 * @see \WP_Query
	 *
	 * @param string $uid The user's email address.
	 * @param string $cid Consent ID.
	 *
	 * @return mixed consent id if exists | false if there's no consent
	 */
	public function consent_exists($uid, $cid)
	{

		$uid = sanitize_text_field($uid);
		$cid = sanitize_text_field($cid);

		$args = array(
			'post_type' => 'sphere_consent_log',
			'posts_per_page' => '1',
			'meta_query' => array(
				'relation' => 'AND',
				'_user_email' => array(
					'key' => '_cl_uid',
					'value' => $uid,
				),
				'_consent_identifier' => array(
					'key' => '_cl_cid',
					'value' => $cid,
				),
			),
		);

		$query = new \WP_Query($args);

		if ($query->have_posts()) {
			return $query->post->ID;
		}

		return false;
	}

	/**
	 * Adds a new consent in the CPT
	 *
	 * @param string $uid  The user's email address.
	 * @param string $cid  Consent ID.
	 * @param array  $data Data to log with the consent.
	 */
	public function add_consent($uid, $cid, $data)
	{
		$uid = sanitize_text_field($uid);
		$cid = sanitize_text_field($cid);

		$consent = wp_insert_post(
			array(
				'post_author'   => 0,
				'post_status'   => 'publish',
				'post_type'     => 'sphere_consent_log',
				'post_date'     => current_time('mysql', false),
				'post_date_gmt' => current_time('mysql', true),
			),
			true
		);

		update_post_meta($consent, '_cl_uid', $uid);
		update_post_meta($consent, '_cl_cid', $cid);
		update_post_meta($consent, '_cl_data', $data);
	}

	/**
	 * Delete a consent from the CPT
	 *
	 * @param string $uid The user's email address.
	 * @param string $cid Consent ID.
	 *
	 * @return boolean true/false depending if the consent is deleted
	 */
	public function remove_consent($uid, $cid)
	{
		$uid = sanitize_text_field($uid);
		$cid = sanitize_text_field($cid);

		$exists = $this->consent_exists($uid, $cid);

		if ($exists) {

			wp_delete_post($exists);
			return true;
		}

		return false;
	}

	/**
	 * Update a consent from the CPT
	 *
	 * @param string $uid The user's email address.
	 * @param string $cid Consent ID.
	 * @param mixed  $sid Consent Status.
	 *
	 * @return boolean true/false depending if the consent is updated
	 */
	public function update_consent($uid, $cid, $data)
	{
		$uid = sanitize_text_field($uid);
		$cid = sanitize_text_field($cid);
		$sid = intval($sid);

		$exists = $this->consent_exists($uid, $cid);

		if ($exists) {

			update_post_meta($exists, '_cl_data', $data);
			return true;
		}
		return false;
	}

	// -- That's it for the API


	/**
	 * Creates the Admin Page for Consent Log
	 * 
	 * @uses \WP_Query()
	 */
	public function create_admin_page()
	{
		?>
		<div class="wrap">
		<h1>Consent Log</h1>
		<hr class="wp-header-end">
		
		<form method="post" class="consents-log-remove-form" id="consents-log-remove-form">
			<?php wp_nonce_field('sphere_consent_log'); ?>

			<h2><?php esc_html_e('Remove all the consents of a user.', 'sphere-egcf'); ?></h2>
			<label for="user_id_to_remove_consent"><?php esc_html_e('User ID', 'sphere-egcf'); ?></label>
			<input type="text" required class="regular-text" id="user_id_to_remove_consent" name="user_id_to_remove_consent" />
			<?php submit_button(__('Remove'), 'secondary', 'submit', false); ?>
		</form>
		
		<hr />

		<table class="widefat striped">
			<thead>
				<tr>
					<th><?php esc_attr_e('User ID', 'sphere-egcf'); ?></th>
					<th><?php esc_attr_e('Consent ID', 'sphere-egcf'); ?></th>
					<th><?php esc_attr_e('Date', 'sphere-egcf'); ?></th>
					<th><?php esc_attr_e('Data', 'sphere-egcf'); ?></th>
					
				</tr>
			</thead>
			<tfoot>
				<tr>
					<td><?php esc_attr_e('User ID', 'sphere-egcf'); ?></td>
					<td><?php esc_attr_e('Consent ID', 'sphere-egcf'); ?></td>
					<td><?php esc_attr_e('Date', 'sphere-egcf'); ?></td>
					<td><?php esc_attr_e('Data', 'sphere-egcf'); ?></td>
				</tr>
			</tfoot>
		
		<?php

		$args = array(
			'post_type' => 'sphere_consent_log',
			'posts_per_page' => '-1',
		);

		$query = new \WP_Query($args);

		if ($query->have_posts()) {
			while ($query->have_posts()): $query->the_post();
			?>
				<tr>
					<td class="row-title">
						<?php
							echo esc_html(get_post_meta(get_the_ID(), '_cl_uid', true));
						?>
					</td>
					<td>
						<?php
							echo esc_html(get_post_meta(get_the_ID(), '_cl_cid', true));
						?>
					</td>
					<td>
						<?php
							echo get_the_date() . ' - ' . get_the_time();
						?>
					</td>
					<td>
						<textarea class="widefat" rows="3"><?php 
							echo esc_textarea(
								print_r(get_post_meta(get_the_ID(), '_cl_data', true), true)
							); ?></textarea>
					</td>
				</tr>
			<?php
			endwhile;
		
		} else {
			echo "<tr><td colspan=4>Nothing here yet.</td></tr>";
		}
		?>
		</table>
		<script>
		( function( $ ) {
			$( '#consents-log-remove-form' ).submit( function( e ) {
				var data,
					uid = $( '#user_id_to_remove_consent' ).val();

				e.preventDefault();

				if (!confirm('Are you sure you want delete consents of this email?')) {
					return;
				}

				data = {
					'action': 'sphere_consent_log_remove',
					'uid': uid,
					'_ajax_nonce': '<?php echo esc_js(wp_create_nonce('sphere_consent_log')); ?>'
				};

				$.post(
					ajaxurl,
					data,
					function( response ) {
						if ( true === response.success ) {
							window.location.href = window.location.href;
						}
					});
			});
		} ) ( jQuery )
		</script>
		<?php

	}
}

