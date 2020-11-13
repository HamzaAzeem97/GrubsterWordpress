<?php

namespace Sphere\EGCF;

/**
 * Admin initialization for EGCF plugin
 */
class Admin 
{
	/**
	 * Setup hooks
	 */
	public function init()
	{
		add_action('cmb2_admin_init', array($this, 'setup_options'));

		// Enqueue at a lower priority to be after CMB2
		add_action('admin_enqueue_scripts', array($this, 'register_assets'), 99);

		add_action('save_post', array($this, 'save_post_process'));

		/**
		 * Add notice for database is needed
		 */
		add_action('init', function() {

			if (!empty(get_option('egcf_should_geolocate')) && !get_option('egcf_geoip_database')) {
				add_action('admin_notices', array($this, 'geo_notice'));
			}
		});

		// Page to download database
		add_action('admin_menu', function() {
			add_submenu_page(null, 'Download', 'Download', 'manage_options', 'egcf-download-database', array($this, 'download_database'));
		});
	}

	/**
	 * Register admin assets
	 */
	public function register_assets()
	{
		wp_enqueue_script(
			'egcf-admin-scripts', 
			Plugin::get_instance()->dir_url . 'js/admin.js', 
			array('jquery'),
			Plugin::VERSION
		);

		wp_enqueue_style(
			'egcf-admin-style',
			Plugin::get_instance()->dir_url . 'css/admin.css',
			array(),
			Plugin::VERSION
		);
	}

	/**
	 * Process post on save
	 */
	public function save_post_process($post_id)
	{
		if (get_post_type($post_id) !== 'egcf_forms') {
			return;
		}

		if (isset($_POST['egcf_geolocate'])) {
			$current = (array) get_option('egcf_should_geolocate');

			if (array_key_exists($post_id, $current)) {
				unset($current[$post_id]);
			}

			if ($_POST['egcf_geolocate'] == 'eu') {
				$current[$post_id] = 1;
			}

			update_option('egcf_should_geolocate', array_filter($current));
		}
	}

	/**
	 * Download database page
	 */
	public function download_database()
	{
		// nonce check
		check_admin_referer('egcf-download-db');

		echo "<p>Downloading database..</p>";

		// Update database
		$success = Plugin::get_instance()->geolocation->update_database();

		if ($success) {
			echo "<p>Done. GeoLite2 database has been installed.</p>";
			update_option('egcf_geoip_database', true);
		}
		else {
			echo "<p>Failed to download database. Please try again.</p>";
		}
	}

	/**
	 * Admin notice about geolocation database
	 */
	public function geo_notice()
	{
		if (!empty($_GET['page']) && $_GET['page'] == 'egcf-download-database') {
			return;
		}

		?>
		<div class="error notice">
		
		<h3><?php esc_html_e('Required: GeoIP Database', 'sphere-egcf'); ?></h3>
		<p>
		<?php 
		esc_html_e(
			'Since you have enabled EU Countries Only option on a form, a GeoIP database needs to be installed. Click below to download the free Maxmind GeoLite2 now and auto-update it periodically.', 
			'sphere-egcf'
		); 
		?>
		</p>
		<p><?php esc_html_e('This database is licenced CC BY-SA and will be downloaded from MaxMind.com server.', 'sphere-egcf'); ?></p>
		
		<form action="<?php echo admin_url('admin.php?page=egcf-download-database'); ?>" method="post">
			<?php wp_nonce_field('egcf-download-db'); ?>
			<p>
				<input type="submit" value="<?php esc_attr_e('Download and Install Now', 'sphere-egcf');?>" class="button button-primary" />
			</p>
		</form>
		</div>
		<?php
	}

	/**
	 * Setup admin options with CMB2
	 */
	public function setup_options()
	{

		$url = Plugin::get_instance()->dir_url;
$instructions = <<<EOF
<div class="help-tab-content egcf-instructions">

<p>Instructions vary depending on the form plugin you have installed. Besides these, the consent popups can be configured for many type of forms but may 
require assistance from a developer to identify form IDs and keys to use.</p>

<h3><a href="#egcf-i-mc4wp">Plugin: MailChimp for WordPress</a></h3>
<section id="egcf-i-mc4wp">
	<p>Assuming you have already configured MailChimp for WordPress plugin.</p>
	<ol>
		<li>Go to <strong>MailChimp &gt; Lists</strong>. Click on your desired list.</li>
		<li>Click <strong>Manage Contacts &gt; Groups &gt; Create Groups</strong>.</li>
		<li>The default setting should be selected as a checkbox. Enter Group Category: <strong>Consent</strong>. Under Group Names, enter the consent 
		you would need, such as <strong>Special Offers</strong> and <strong>Advertising</strong>.</li>
		<li>Click Save.</li>
		<li>Go to your dashboard and <strong>MailChimp for WordPress &gt; MailChimp</strong> and click <strong>Renew MailChimp Lists</strong>.</li>
		<li>Browse to <strong>MailChimp for WordPress &gt; Form</strong> - or edit your desired form in pro.</strong>
		<li>Under <strong>Interest Categories</strong>, you will find <strong>Consent</strong>. Click on it and add it to your form. DO NOT SAVE this, you will just copy the code.</li>
		<li>Copy your <strong>Form Code</strong>, paste below under Form Code, and click <strong>Detect</strong>.</li>
	</ol>
</section>

<h3><a href="#egcf-i-offical">Official: MailChimp Embedded Form</a></h3>
<section id="egcf-i-offical">
<ol>
	<li>Go to <strong>MailChimp &gt; Lists</strong>. Click on your desired list.</li>
	<li>Click <strong>Manage Contacts &gt; Groups &gt; Create Groups</strong>.</li>
	<li>The default setting should be selected as a checkbox. Enter Group Category: <strong>Consent</strong>. Under Group Names, enter the consent 
	you would need, such as <strong>Special Offers</strong> and <strong>Advertising</strong>.</li>
	<li>Click Save.</li>
	<li>Next, click <strong>Signup Forms</strong> and choose <strong>Embedded Form</strong>.</li>
	<li>(Optional) These checkboxes may be shown in your live form. Hide them by editing your form (<a href="https://kb.mailchimp.com/lists/signup-forms/hide-groups-on-signup-forms#Change-Visibility-of-Groups-on-Your-Signup-Form" target="_blank">instructions here</a>).</li>
	<li>(Optional) If you use <strong>EU Countries Only</strong> feature, you need to add a custom field to log if a subscriber was from EU. Use the label as "Is Europe?" and MERGE field name: <code>IS_EU</code> - <a href="https://kb.mailchimp.com/lists/manage-contacts/manage-list-and-signup-form-fields" target="_blank">instructions here</a> (hide it by unchecking visibility).</li>
	<li>Copy the code and paste below under Form Code and click Detect.</li>
</ol>
</section>

<h3><a href="#egcf-i-offical">Extra Info: EU Countries Only Feature</a></h3>
<section id="egcf-i-eu">
	<h4>About EU Countries Only Feature</h4>

	<p>When using EU Countries only, MaxMind.com GeoLite2 database is downloaded. You will have to make sure you have added an IS_EU or a similar field for EU visitors.</p>
	<p>After using this, you can create two <a href="https://kb.mailchimp.com/lists/segments/getting-started-with-segments" target="_blank">segments</a> in MailChimp. One to send newsletter to EU users with Consent condition and another to non-EU users. For example:</p>

	<div class="screenshots">
	<img src="{$url}img/screenshot1.png" width="400" />
	<img src="{$url}img/screenshot2.png" width="400" />
	</div>
</section>

</div>
EOF;


		$info = new_cmb2_box(array(
			'id'           => 'egcf_instructions',
			'title'        => esc_html__('Instructions', 'sphere-egcf'),
			'object_types' => array('egcf_forms'),
			'save_fields'  => false
		));

		$info->add_field(array(
			'name'    => '',
			'description' => $instructions,
			'type'    => 'title',
			'id'      => 'egcf_instructions',
		));

		$info->add_field(array(
			'name'    => esc_html__('Form Code (Auto Checkboxes)', 'sphere-egcf'),
			'desc'    => esc_html__('Paste your MailChimp form code and press process to auto-detect and use checkboxes.', 'sphere-egcf'),
			'id'      => 'egcf_form_code',
			'type'    => 'textarea',
			'save_field'  => false,
			'after_field' => '<br /><input type="button" class="button egcf-code-detect" value="Detect">',
			'options' => array(
				'textarea_rows' => 6,
			),
		));

		// Configure meta box

		$options = new_cmb2_box(array(
			'id'           => 'egcf_options',
			'title'        => esc_html__('Configure Form', 'sphere-egcf'),
			'object_types' => array('egcf_forms'),
		));

		$options->add_field(array(
			'name'    => esc_html__('HTML Selector', 'sphere-egcf'),
			'desc'    => esc_html__('Enter the form classes, ids or any valid jQuery selectors to target a specific form.', 'sphere-egcf'),
			'id'      => 'egcf_selector',
			'type'    => 'text',
			'default' => apply_filters('egcf/default_selector', '#mc_embed_signup, .mc4wp-form, .subscribe-box, .widget-subscribe')
		));

		$options->add_field(array(
			'name'    => esc_html__('Modal Heading', 'sphere-egcf'),
			'desc'    => esc_html__('Heading for the consent modal.', 'sphere-egcf'),
			'id'      => 'egcf_heading',
			'type'    => 'text',
			'default' => 'Newsletter Details',
		));

		$options->add_field(array(
			'name'    => esc_html__('Message', 'sphere-egcf'),
			'desc'    => esc_html__('A clear message suited to inform the user on what their data will be used for. Change the sample to your own message.', 'sphere-egcf'),
			'id'      => 'egcf_message',
			'type'    => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 6,
				'teeny' => true,
			),
			'default' => 'As you subscribe, MyBlog will send the latest blog posts at your email address. In addition, we’d like to send you special offers and suggest our relevant products. Please tick below for consent:',
		));

		$group = $options->add_field(array(
			'id'          => 'egcf_checkboxes',
			'name'        => esc_html__('Checkboxes', 'sphere-egcf'),
			'type'        => 'group',
			'description' => '<br />Add consent checkboxes. <strong>Examples: </strong><br /><br />
				I agree to receive occasional special offers and discounts via email.<br />
				I understand the Privacy Policy and agree to see our product suggestions via Facebook Ads.<br />',
			'options'     => array(
				'group_title'   => esc_html__('Checkbox {#}', 'sphere-egcf'),
				'add_button'    => esc_html__('Add Checkbox', 'sphere-egcf'),
				'remove_button' => esc_html__('Remove', 'sphere-egcf'),
				'sortable'      => true,
				'closed' => true,
			),
		));

		$options->add_group_field($group, array(
			'name'    => esc_html__('Checkbox Message/Label', 'sphere-egcf'),
			'desc'    => '',
			'id'      => 'label',
			'type'    => 'text',
		));

		$options->add_group_field($group, array(
			'name'    => esc_html__('Field Name', 'sphere-egcf'),
			'desc'    => esc_html__('Field name is the form value this checkbox is associated with. It is <input ... name="THIS"> part in a form code.', 'sphere-egcf'),
			'id'      => 'name',
			'type'    => 'text',
		));

		$options->add_group_field($group, array(
			'name'    => esc_html__('Field Value', 'sphere-egcf'),
			'desc'    => esc_html__('Field value is the form value that will be submitted with. It is <input ... value="THIS"> part in a form code.', 'sphere-egcf'),
			'id'      => 'value',
			'type'    => 'text',
		));

		$options->add_field(array(
			'name'    => esc_html__('Message Below', 'sphere-egcf'),
			'desc'    => esc_html__('An additional message to show below the checkboxes. You may use this to provide additional info that MailChimp recommends, such as how you use MailChimp as your marketing tool.', 'sphere-egcf'),
			'id'      => 'egcf_message_lower',
			'type'    => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 6,
				'teeny' => true,
			),
			'default' => 'You can change your mind at any time by clicking the unsubscribe link in the footer of any email you receive from us. We use MailChimp as our marketing automation platform and data processor. Please review our privacy policy for more details.',
		));

		$options->add_field(array(
			'name'    => esc_html__('Email Field', 'sphere-egcf'),
			'desc'    => esc_html__('The input name of the field that will contain the email address. This is EMAIL for MailChimp.'),
			'id'      => 'egcf_email_field',
			'type'    => 'text_small',
			'default' => 'EMAIL'
		));

		$options->add_field(array(
			'name'    => esc_html__('Show Consent For', 'sphere-egcf'),
			'desc'    => esc_html__('"EU Countries Only" will show the consent popup only to EU users by using geolocation. When using this setting, GeoLite2 database by MaxMind it will be automatically downloaded from maxmind.com.', 'sphere-egcf'),
			'id'      => 'egcf_geolocate',
			'type'    => 'select',
			'options' => array(
				''    => esc_html__('All Countries', 'sphere-egcf'),
				'eu'  => esc_html__('EU Countries Only', 'sphere-egcf')
			),
			'default' => ''
		));

		$options->add_field(array(
			'name'    => esc_html__('"Is Europe?" Field', 'sphere-egcf'),
			'desc'    => esc_html__('When using EU Countries Only feature, an IS_EU field is needed so you can segment EU and non-EU subscribers based on consent. Check under Extra Info in Instructions.'),
			'id'      => 'egcf_eu_field',
			'type'    => 'text_small',
			'default' => 'IS_EU'
		));

		$options->add_field(array(
			'name'    => esc_html__('Processing Label', 'sphere-egcf'),
			'desc'    => '',
			'id'      => 'egcf_process_label',
			'type'    => 'text',
			'default' => esc_html__('Submitting...', 'sphere-egcf'),
		));


		$options->add_field(array(
			'name'    => esc_html__('Submit Label', 'sphere-egcf'),
			'desc'    => '',
			'id'      => 'egcf_submit_label',
			'type'    => 'text',
			'default' => esc_html__('Agree & Subscribe', 'sphere-egcf'),
		));

		$options->add_field(array(
			'name'    => esc_html__('Cancel Label', 'sphere-egcf'),
			'desc'    => '',
			'id'      => 'egcf_cancel_label',
			'type'    => 'text',
			'default' => esc_html__('Cancel', 'sphere-egcf'),
		));

	}

}