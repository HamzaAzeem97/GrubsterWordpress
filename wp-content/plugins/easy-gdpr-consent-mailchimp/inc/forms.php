<?php
namespace Sphere\EGCF;

/**
 * The consent forms
 */
class Forms 
{
	/**
	 * @var ConsentLog
	 */
	public $consent_log;

	/**
	 * @var GeoLocation
	 */
	public $geolocation;

	/**
	 * @param ConsentLog $consent_log
	 * @param GeoLocation $geolocation
	 */
	public function __construct($consent_log = '', $geolocation = '') 
	{
		$this->consent_log = $consent_log;
		$this->geolocation = $geolocation;
	}

	/**
	 * Setup and register hooks
	 */
	public function init()
	{
		// Form AJAX
		add_action('wp_ajax_nopriv_egcf_should_show_form', array($this, 'should_show_form'));
		add_action('wp_ajax_egcf_should_show_form', array($this, 'should_show_form'));

		add_action('wp_ajax_nopriv_egcf_submit_consent', array($this, 'submit_consent'));
		add_action('wp_ajax_egcf_submit_consent', array($this, 'submit_consent'));

		// Footer template
		add_action('wp_footer', array($this, 'add_templates'));
		add_action('wp_head', array($this, 'disable_forms'));
	}

	/**
	 * Tests if the user is from the location that needs the consent popup
	 */
	public function should_show_form()
	{
		$form_id  = intval($_POST['egcf_form_id']);

		if ($form_id) {
			$form = get_post($form_id);
		}

		if (!$form) {
			wp_send_json_error();
		}

		$show      = true;
		$geolocate = get_post_meta($form->ID, 'egcf_geolocate', true);
		
		if ($geolocate == 'eu' && !$this->geolocation->is_eu()) {
			$show = false;
		}

		wp_send_json_success(array(
			'show' => $show
		));
	}

	/**
	 * AJAX call to log and submit consent
	 */
	public function submit_consent()
	{
		// Expected data
		$email    = sanitize_text_field($_POST['email']);
		$form_id  = intval($_POST['egcf_form_id']);

		if ($form_id) {
			$form = get_post($form_id);
		}

		if (!$form || !$email) {
			wp_send_json_error();
		}

		/**
		 * Flatten multi-dimensional POST array
		 */
		$fields = explode('&', http_build_query($_POST));
		$return = array();
		
		foreach ($fields as $field) {

			$field = explode('=', $field);
			$return[ urldecode($field[0]) ] = esc_attr(
				urldecode($field[1])
			);
		}

		unset(
			$return['action'], 
			$return['email']
		);

		/**
		 * Add IS_EU field to be posted if enabled
		 */
		$geolocate = get_post_meta($form->ID, 'egcf_geolocate', true);
		if ($geolocate == 'eu') {
			$eu_field = get_post_meta($form->ID, 'egcf_eu_field', true);
			$return[$eu_field] = 1;
		}

		/**
		 * Add checkbox labels to consent data
		 */
		$consent_data = $return;
		$checkboxes   = get_post_meta($form->ID, 'egcf_checkboxes', true);

		foreach ($checkboxes as $checkbox) {

			$name = $checkbox['name'];
			if (!empty($consent_data[ $name ])) {
				$consent_data[ 'label_' . $name ] = $checkbox['label'];
			}
		}

		unset($consent_data['email']); // redundant

		// Save consent with relevant data
		$consent = $this->consent_log->add_consent(
			$email,
			'egcf_' . $form->ID,
			$consent_data
		);

		// Don't need to return these fields
		unset(
			$return['submit'], 
			$return['egcf_form_id']
		);

		wp_send_json_success(array(
			'fields'  => $return
		));
	}

	/**
	 * Create checkboxes markup
	 */
	public function checkboxes($checkboxes)
	{
		$html = '';

		if (!$checkboxes) {
			return 'No checkboxes set. This form is INVALID.';
		}

		foreach ($checkboxes as $checkbox) {
			$html .= sprintf(
				'<label><input type="checkbox" name="%1$s" value="%2$s" class="checkbox"><span>%3$s</span></label>',
				esc_attr($checkbox['name']),
				wp_kses_post($checkbox['value']),
				wp_kses_post($checkbox['label'])
			);
		}

		return $html;
	}

	/**
	 * Callback: Disable forms until JS is loaded by adding CSS
	 */
	public function disable_forms() 
	{
		// Using a script or minfication plugins are going to gobble it up with no way
		// to remove via JS.
		echo '<script>
		document.head.innerHTML += \'<style id="egcf-disable-submit">input[type=submit], button[type=submit] { pointer-events: none; } form { cursor: progress !important; }</style>\';
		</script>';
	}

	/**
	 * Output form modal templates
	 */
	public function add_templates()
	{

		$query = new \WP_Query;
		$forms = (array) $query->query(array(
			'post_type'      => 'egcf_forms', 
			'posts_per_page' => -1
		));

		foreach ($forms as $form) {

			/**
			 * Whether to output this form or not.
			 */
			if (!apply_filters('egcf/forms/output_form', true, $form->ID)) {
				continue;
			}

			$opts = array(
				'checkboxes'    => get_post_meta($form->ID, 'egcf_checkboxes', true),
				'heading'       => get_post_meta($form->ID, 'egcf_heading', true),
				'message'       => get_post_meta($form->ID, 'egcf_message', true),
				'message_lower' => get_post_meta($form->ID, 'egcf_message_lower', true),
				'submit_label'  => get_post_meta($form->ID, 'egcf_submit_label', true),
				'cancel_label'  => get_post_meta($form->ID, 'egcf_cancel_label', true),
				'process_label' => get_post_meta($form->ID, 'egcf_process_label', true),
				'selector'      => get_post_meta($form->ID, 'egcf_selector', true),
				'email_field'   => get_post_meta($form->ID, 'egcf_email_field', true),
				'eu_field'      => get_post_meta($form->ID, 'egcf_eu_field', true),
				'geolocate'     => get_post_meta($form->ID, 'egcf_geolocate', true),
			);

			/**
			 * Override the template output below by using own markup. Hook to filter and return
			 * the output. 
			 * 
			 * @param string $content
			 * @param object $form
			 * @param array  $opts
			 */
			$override = apply_filters('egcf/forms/form_template', '', $form, $opts);

			if ($override) {

				// Use the override and move on
				echo $override;
				
				continue;
			}

			?>

			<script type="text/template" class="egcf-consent-tpl" data-id="<?php echo esc_attr($form->ID); ?>" 
				data-selector="<?php echo esc_attr($opts['selector']); ?>"
				data-show-test="<?php echo $opts['geolocate'] ? 1 : ''; ?>"
				data-process-label="<?php echo esc_attr($opts['process_label']); ?>">

				<div class="egcf-modal-head">
					<div><?php echo esc_html($opts['heading']); ?></div>
					<a href="#" class="egcf-modal-close">&times;</a>
				</div>

				<form method="post" class="egcf-consent-form" data-email="<?php echo esc_attr($opts['email_field']); ?>">
					<input type="hidden" name="egcf_form_id" value="<?php echo esc_attr($form->ID); ?>">

					<?php if ($opts['message']): ?>
					
						<div class="egcf-modal-message">
							<?php echo wp_kses_post($opts['message']); ?>
						</div>

					<?php endif; ?>

					
					<div class="egcf-modal-consents">
						<?php echo $this->checkboxes($opts['checkboxes']); ?>
					</div>


					<?php if ($opts['message_lower']): ?>
					
						<div class="egcf-modal-lower">
							<?php echo wp_kses_post($opts['message_lower']); ?>
						</div>
					
					<?php endif; ?>

					<div class="egcf-modal-submit">
						<input type="submit" class="submit-btn" name="submit" value="<?php echo esc_attr($opts['submit_label']); ?>">
						<span><a href="#" class="cancel"><?php echo esc_html($opts['cancel_label']); ?></a></span>
					</div>

				</form>
			</script>

			<?php

		}
	}
}