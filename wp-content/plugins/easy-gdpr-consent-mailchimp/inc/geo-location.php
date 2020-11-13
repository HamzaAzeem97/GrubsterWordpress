<?php

namespace Sphere\EGCF;

/**
 * Geolocation class based on WooCommerce 3.4 and MaxMind's GeoIP2
 */
class GeoLocation 
{
	/**
	 * GeoLite2 DB.
	 */
	const GEOLITE2_DB = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.tar.gz';

	public $database;

	public function init() 
	{
		// Cron schedule hook
		add_action('egcf_geoip_updater', array($this, 'update_database'));
	}

	/**
	 * Is a European Union State?
	 * 
	 * @return boolean
	 */
	public function is_eu($ip_address = '')
	{
		$country = $this->geolocate_ip();

		// From API: https://restcountries.eu/rest/v2/regionalbloc/eu
		$eu_countries = array(
			248 => 'AX',
			'040' => 'AT',
			'056' => 'BE',
			100 => 'BG',
			191 => 'HR',
			196 => 'CY',
			203 => 'CZ',
			208 => 'DK',
			233 => 'EE',
			234 => 'FO',
			246 => 'FI',
			250 => 'FR',
			254 => 'GF',
			276 => 'DE',
			292 => 'GI',
			300 => 'GR',
			348 => 'HU',
			372 => 'IE',
			833 => 'IM',
			380 => 'IT',
			428 => 'LV',
			440 => 'LT',
			442 => 'LU',
			470 => 'MT',
			528 => 'NL',
			616 => 'PL',
			620 => 'PT',
			642 => 'RO',
			703 => 'SK',
			705 => 'SI',
			724 => 'ES',
			752 => 'SE',
			826 => 'GB',
		);
		
		if (in_array($country, $eu_countries)) {
			return true;
		}

		return false;
	}

	/**
	 * Geolocate an IP address.
	 *
	 * @param  string $ip_address   IP Address.
	 * @return string ISO Country code
	 */
	public function geolocate_ip($ip_address = '') 
	{
		// The needed setup
		require_once Plugin::get_instance()->dir_path .'vendor/autoload.php';
		$this->set_database();

		// Filter to allow custom geolocation of the IP address.
		$country_code = apply_filters('egcf/geolocate_ip', false, $ip_address);

		if (false === $country_code) {

			// If GEOIP is enabled in CloudFlare, we can use that (Settings -> CloudFlare Settings -> Settings Overview).
			if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) {
				$country_code = strtoupper(sanitize_text_field(wp_unslash($_SERVER['HTTP_CF_IPCOUNTRY'])));
			} 
			else {

				$ip_address = $ip_address ? $ip_address : $this->get_ip_address();

				if (!file_exists($this->database)) {
					return '';
				}

				$country_code = $this->get_country_from_db($ip_address);
			}
		}

		return $country_code;
	}

	/**
	 * Get country 2-letters ISO by IP address.
	 * Retuns empty string when not able to find any ISO code.
	 *
	 * @param string $ip_address User IP address.
	 * @return string
	 */
	public function get_country_from_db($ip_address)
	{
		$iso_code = '';
		
		try {
			$reader = new \MaxMind\Db\Reader($this->database);
			$data = $reader->get($ip_address);
			$iso_code = $data['country']['iso_code'];
			$reader->close();
		} 
		catch (Exception $e) {}

		return sanitize_text_field(strtoupper($iso_code));
	}

	/**
	 * Get current user IP Address.
	 *
	 * @return string
	 */
	public function get_ip_address()
	{
		if (isset($_SERVER['HTTP_X_REAL_IP'])) {
			return sanitize_text_field(wp_unslash($_SERVER['HTTP_X_REAL_IP']));
		} 
		elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {

			// Proxy servers can send through this header like this: X-Forwarded-For: client1, proxy1, proxy2
			// Make sure we always only send through the first IP in the list which should always be the client IP.
			return (string) rest_is_ip_address(trim(current(preg_split('/[,:]/', sanitize_text_field(wp_unslash($_SERVER['HTTP_X_FORWARDED_FOR']))))));

		} 
		elseif (isset($_SERVER['REMOTE_ADDR'])) {
			return sanitize_text_field(wp_unslash($_SERVER['REMOTE_ADDR']));
		}

		return '';
	}

	/**
	 * Set database path
	 */
	public function set_database($db = '') 
	{
		if (!$db && !$this->database) {
			$upload_dir = wp_upload_dir();
			$db = $upload_dir['basedir'] . '/egcf-GeoLite2-Country.mmdb';
		}

		$this->database = $db;
		return $this;
	}

	/**
	 * Update geoip database.
	 * 
	 * From WooCommerce: includes/class-wc-geolocation.php
	 */
	public function update_database()
	{
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$upload_dir = wp_upload_dir();
		$tmp_database_path = download_url(self::GEOLITE2_DB);
		$success = false;

		if (!is_wp_error($tmp_database_path)) {

			try {
				// GeoLite2 database name.
				$database = 'GeoLite2-Country.mmdb';
				$dest_path = trailingslashit($upload_dir['basedir']) . 'egcf-' . $database;

				// Extract files with PharData. Tool built into PHP since 5.3.
				$file = new \PharData($tmp_database_path);
				$file_path = trailingslashit($file->current()->getFileName()) . $database;

				// Extract under uploads directory.
				$file->extractTo($upload_dir['basedir'], $file_path, true);

				// Remove old database.
				@unlink($dest_path);

				// Copy database and delete tmp directories.
				@rename(trailingslashit($upload_dir['basedir']) . $file_path, $dest_path);
				@rmdir(trailingslashit($upload_dir['basedir']) . $file->current()->getFileName());

				// Set correct file permission.
				@chmod($dest_path, 0644);

				$success = true;

			} 
			catch (Exception $e) {}

			@unlink($tmp_database_path);
		}

		// Reschedule download of DB in a month
		wp_clear_scheduled_hook('egcf_geoip_updater');
		wp_schedule_event(strtotime('first tuesday of next month'), 'monthly', 'egcf_geoip_updater');

		return $success;
	}
}