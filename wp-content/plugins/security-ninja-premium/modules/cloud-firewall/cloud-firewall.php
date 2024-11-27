<?php

namespace WPSecurityNinja\Plugin;

if (!defined('ABSPATH')) {
	exit;
}

define('WF_SN_CF_OPTIONS_KEY', 'wf_sn_cf'); // @important - if changed, this is used various places, 2FA, etc.
define('WF_SN_CF_LOG_TABLE', 'wf_sn_cf_vl');
define('WF_SN_CF_BLOCKED_IPS_TABLE', 'wf_sn_cf_bl_ips');
define('WF_SN_CF_VALIDATED_CRAWLERS', 'wf_sn_cf_validated_crawlers');

class Wf_sn_cf
{

	private static $cached_ip = null;

	public static $options = null;

	public static $banned_ips = null; // Bruges til at cache lokalt bannede IPs så ikke indlæst hver pageload.

	public static $central_api_url = 'https://api.securityninjawp.com/wp-json/secnin/v1/';

	public static function init()
	{
		self::$options = self::get_options();

		// update geolocation database via SN_Geolocation in class-sn-geolocation.php 

		add_action('secnin_update_geoip', array(__NAMESPACE__ . '\SN_Geolocation', 'update_database'));

		add_action('secnin_update_cloud_firewall', array(__NAMESPACE__ . '\\wf_sn_cf', 'update_cloud_ips'));

		add_action('secnin_prune_visitor_log', array(__NAMESPACE__ . '\\wf_sn_cf', 'prune_visitor_log'));
		add_action('secnin_prune_banned', array(__NAMESPACE__ . '\\wf_sn_cf', 'prune_banned'));

		add_action('secnin_update_blocked_ips', array(__NAMESPACE__ . '\\wf_sn_cf', 'action_update_blocked_ips'));

		add_action('init', array(__NAMESPACE__ . '\\wf_sn_cf', 'schedule_cron_jobs'));


		// setup_theme seems to be earliest hook - because of Freemius API - Future - add as mu-plugin - plugins_loaded earliest possible hook
		add_action('template_redirect', array(__NAMESPACE__ . '\\wf_sn_cf', 'check_visitor'), 1);
		add_action('login_head', array(__NAMESPACE__ . '\\wf_sn_cf', 'check_visitor'), 1);

		add_action('init', array(__NAMESPACE__ . '\\wf_sn_cf', 'do_init_action'), 1);

		add_action('wp_login', array(__NAMESPACE__ . '\\wf_sn_cf', 'set_login_timestamp'), 10, 2);
		add_action('login_init', array(__NAMESPACE__ . '\\wf_sn_cf', 'form_init_check'));
		add_filter('authenticate', array(__NAMESPACE__ . '\\wf_sn_cf', 'login_filter'), 10, 3);
		add_filter('login_message', array(__NAMESPACE__ . '\\wf_sn_cf', 'login_message'));
		add_action('wp_login_failed', array(__NAMESPACE__ . '\\wf_sn_cf', 'failed_login'));
		add_filter('login_errors', array(__NAMESPACE__ . '\\wf_sn_cf', 'process_login_errors'));
		add_filter('woocommerce_login_form_start', array(__NAMESPACE__ . '\\wf_sn_cf', 'process_woocommerce_login_form_start'));

		if (is_admin()) {
			// add tab to Security Ninja tabs
			add_filter('sn_tabs', array(__NAMESPACE__ . '\\wf_sn_cf', 'sn_tabs'));
			add_action('admin_enqueue_scripts', array(__NAMESPACE__ . '\\wf_sn_cf', 'enqueue_scripts'));
			add_action('admin_init', array(__NAMESPACE__ . '\\wf_sn_cf', 'register_settings'));
			add_action('sn_overlay_content', array(__NAMESPACE__ . '\\wf_sn_cf', 'overlay_content'));
			add_action('wp_ajax_sn_enable_firewall', array(__NAMESPACE__ . '\\wf_sn_cf', 'ajax_enable_firewall'));
			add_action('wp_ajax_sn_clear_blacklist', array(__NAMESPACE__ . '\\wf_sn_cf', 'ajax_clear_blacklist'));
			add_action('wp_ajax_sn_send_unblock_email', array(__NAMESPACE__ . '\\wf_sn_cf', 'ajax_send_unblock_email'));
			add_action('wp_ajax_sn_test_ip', array(__NAMESPACE__ . '\\wf_sn_cf', 'ajax_test_ip'));


			add_action('pre_user_login', array(__NAMESPACE__ . '\\wf_sn_cf', 'check_user_login'));
			add_action('register_post', array(__NAMESPACE__ . '\\wf_sn_cf', 'check_register_post'), 10, 3);
		}
	}


	/**
	 * check_user_login.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, June 7th, 2024.
	 * @access	public static
	 * @global
	 * @param	mixed	$user_login	
	 * @return	mixed
	 */
	public static function check_user_login($user_login)
	{
		self::get_options();

		$protect_login_form = self::$options['protect_login_form'];
		if (!$protect_login_form) {
			return $user_login;
		}
		self::check_visitor();
		return $user_login;
	}

	/**
	 * check_register_post.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, June 7th, 2024.
	 * @access	public static
	 * @global
	 * @param	mixed	$sanitized_user_login	
	 * @param	mixed	$user_email          	
	 * @param	mixed	$errors              	
	 * @return	mixed
	 */
	public static function check_register_post($sanitized_user_login, $user_email, $errors)
	{

		self::get_options();

		$protect_login_form = self::$options['protect_login_form'];
		if (!$protect_login_form) {
			return $errors;
		}

		self::check_visitor();

		return $errors;
	}


	/**
	 * Process IP only early.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, May 21st, 2024.
	 * @access	public static
	 * @return	void
	 */
	public static function do_init_action()
	{
		$current_user_ip = self::get_user_ip();
		$reason = self::is_banned_ip($current_user_ip);

		if ($reason) {
			self::update_blocked_count($current_user_ip);

			$ua_string = '';
			if (isset($_SERVER['HTTP_USER_AGENT'])) {
				$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
			}
			$data = [
				'user_agent' => $ua_string,
				'ip' => $current_user_ip,
				'reason' => $reason
			];
			wf_sn_el_modules::log_event('security_ninja', 'do_init_action', 'Blocked.', $data, null, $current_user_ip);
			wp_clear_auth_cookie();
			self::kill_request();
			exit();
		}
	}

	/**
	 * Sets a timestamp for the user when successfully logging in.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, February 6th, 2024.
	 * @param	mixed	$user_login	
	 * @param	mixed	$user      	
	 * @return	void
	 */
	public static function set_login_timestamp($user_login, $user)
	{
		update_user_meta($user->ID, 'sn_last_login', current_time('mysql'));
	}


	/**
	 * endsWith. - ref https://www.php.net/manual/en/function.str-ends-with.php
	 *
	 * @author  javalc6 at gmail dot com
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, August 30th, 2021.
	 * @param   mixed   $haystack
	 * @param   mixed   $needle
	 * @return  mixed
	 */
	private static function string_ends_with($haystack, $needle)
	{
		$length = strlen($needle);
		return $length > 0 ? substr($haystack, -$length) === $needle : true;
	}


	/**
	 * Validate a crawlers IP against the hostname
	 *
	 * @author	Lars Koudal
	 * @since	v5.123
	 * @version	v1.0.0	Monday, August 30th, 2021.	
	 * @version	v1.0.1	Monday, June 3rd, 2024.
	 * @access	private static
	 * @param	mixed	$testip	
	 * @return	boolean
	 */
	private static function validate_crawler_ip($testip)
	{
		// Lets check if the IP has already been validated
		$validated_crawlers = get_option(WF_SN_CF_VALIDATED_CRAWLERS);
		if ($validated_crawlers) {
			if (in_array($testip, $validated_crawlers, true)) {
				return true;
			}
		} else {
			$validated_crawlers = array();
		}

		$hostname = strtolower(gethostbyaddr($testip)); //"crawl-66-249-66-1.googlebot.com"
		$valid_host_names = array(
			'.crawl.baidu.com',
			'.crawl.baidu.jp',
			'.search.msn.com',
			'.google.com',
			'.googlebot.com',
			'.crawl.yahoo.net',
			'.yandex.ru',
			'.yandex.net',
			'.yandex.com',
			'.search.msn.com',
			'.petalsearch.com',
			'applebot.apple.com',
			'.ahrefs.com',  // Added Ahrefs
			'.semrush.com',
			'.duckduckgo.com',
			'facebookexternalhit.com',
			'.commoncrawl.org',
			'.googleother.com',
			'.google-inspectiontool.com',
			'.swiftype.com',
			'.sogou.com',
			'.yahoo.com',
			'.bing.com'
		);

		foreach ($valid_host_names as $valid_host) {
			if (self::string_ends_with($hostname, $valid_host)) {
				$returned_ip = gethostbyname($hostname);
				if ($returned_ip === $testip) {
					$validated_crawlers[] = $testip;
					update_option(WF_SN_CF_VALIDATED_CRAWLERS, $validated_crawlers, false);

					wf_sn_el_modules::log_event('security_ninja', 'validated_crawler_ip', 'Valid Crawler' . esc_attr($hostname), '', '', esc_attr($testip));
					return true;
				}
			}
		}
		return false;
	}




	/**
	 * Checks if an IP is from a service that has been enabled
	 *
	 * This method checks if the given IP address is whitelisted for services such as Broken Link Checker, WP Rocket, ManageWP, UptimeRobot, and WPCompress.
	 * It also checks for IP ranges (CIDR) in the whitelist.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Wednesday, May 8th, 2024.
	 * @access	public static
	 * @param	mixed	$current_user_ip	The IP address to check against the whitelist.
	 * @return	boolean					Returns true if the IP is whitelisted, false otherwise.
	 */
	public static function is_whitelisted_service($current_user_ip)
	{
		$whitelist_brokenlink = [
			'94.231.107.9', // Broken Link Checker
			'54.191.137.17', // Broken Link Checker
		];
		$whitelist_wprocket = [];
		if (isset(self::$options['whitelist_wprocket']) && self::$options['whitelist_wprocket']) {
			$whitelist_wprocket = [
				'109.234.160.58', // WP Rocket - Load CSS async
				'51.83.15.135', // WP Rocket - Load CSS async
				'51.210.39.196', // WP Rocket - Load CSS async
				'146.59.192.120', // WP Rocket - license validation
				'135.125.83.227', // WP Rocket - Remove unused CSS
				'146.59.251.59', // WP Rocket - RocketCDN
			];
		}

		$whitelist_wpmudev = [
			'18.204.159.253',
			'54.227.51.40',
			'18.219.56.14',
			'45.55.78.242',
			'35.171.56.101',
			'34.196.51.17',
			'35.157.144.199',
			'165.227.251.117',
			'165.227.251.120',
			'140.82.60.49',
			'45.63.10.140',
			'18.219.161.157',
			'165.227.127.103',
			'64.176.196.23',
			'144.202.86.106',
			'3.93.131.0',
			'167.71.93.101',
			'167.71.179.192'
		];

		$whitelist_divi_dash = [
			'67.227.164.200',
			'67.227.164.201',
			'67.227.164.202',
		];

		/*
		@todo - 
IP addresses used by wpcompress.com resolve to rDNS names in the format api.wpcompress.com. To simplify firewall configurations and ensure you're whitelisting the correct IP addresses, you can whitelist IPs based on the domain *.wpcompress.com by resolving the rDNS of our IPs.

		*/
		$whitelist_wpcompress = ['168.119.147.46',
		'71.19.240.35',
		'216.52.183.178',
		'167.160.91.242',
			'51.79.230.163',
			'51.161.208.134',
			'213.133.103.23',
			'162.55.161.208',
			'213.239.197.231',
			'88.99.209.68',
			'2a01:4f8:251:a11::/64',
			'2605:9f80:c000:240::2/64',
			'2605:9f80:1000:461::2/64',
			'2402:1f00:8001:11a3::/64',
			'2402:1f00:8201:486::/64',
			'2a01:4f8:a0:90d5::/64',
			'2a01:4f8:c012:bb07::/64',
			'2a01:4f8:222:1059::/64',
			'2a01:4f8:10a:3a47::/64'
		];

		$whitelist_managewp = [];
		if (isset(self::$options['whitelist_managewp']) && self::$options['whitelist_managewp']) {
			$whitelist_managewp_path = 'whitelist-managewp.php';
			if (file_exists($whitelist_managewp_path)) {
				// Load ranges from a local file.
				$whitelist_managewp = include($whitelist_managewp_path);
			}
		}

		$whitelist_uptimia = [];
		if (isset(self::$options['whitelist_uptimia']) && self::$options['whitelist_uptimia']) {
			$whitelist_uptimia_path = 'whitelist-uptimia.php';
			if (file_exists($whitelist_uptimia_path)) {
				// Load ranges from a local file.
				$whitelist_uptimia = include($whitelist_uptimia_path);
			}
		}

		$whitelist = array_merge($whitelist_brokenlink, $whitelist_wprocket, $whitelist_managewp, $whitelist_uptimia, $whitelist_wpmudev, $whitelist_wpcompress, $whitelist_divi_dash);

		foreach ($whitelist as $whitelist_item) {
			// Check if the current whitelist item is an IP range (CIDR)
			if (strpos($whitelist_item, '/') !== false) {
				[$ip, $subnet] = explode('/', $whitelist_item);
				$ip_long = ip2long($current_user_ip);
				if ($ip_long === false || ip2long($ip) === false) {
					continue; // Invalid IP or subnet, move to the next item
				}
				$subnet_mask = pow(2, (32 - $subnet)) - 1;
				$ip_min = (ip2long($ip) & ~$subnet_mask);
				$ip_max = $ip_min + $subnet_mask;
				if ($ip_long >= $ip_min && $ip_long <= $ip_max) {
					return true; // IP is whitelisted
				}
			} else {
				if ($current_user_ip === $whitelist_item) {
					return true; // IP is whitelisted
				}
			}
		}
		return false; // IP is not whitelisted
	}








	/**
	 * Checks the current visitor
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Thursday, January 14th, 2021.	
	 * @version	v1.0.2	Wednesday, June 9th, 2021.	
	 * @version	v1.0.3	Saturday, February 5th, 2022.	
	 * @version	v1.0.4	Saturday, November 19th, 2022.
	 * @access	public static
	 * @return	void
	 */
	public static function check_visitor()
	{
		global $wpdb;

		// Filter out AJAX, cron and admin related requests
		if (wp_doing_ajax() || wp_doing_cron() || is_admin() || wp_is_json_request()) {
			return;
		}

		$server_host = gethostname();
		$server_ip   = gethostbyname($server_host);

		$whitelisted_user = false;
		$administrator    = false; // @todo next linie - implementer egen løsning med bonus for at finde land hvis slået til
		$visit_logged = false;

		$current_user_ip = self::get_user_ip();

		if ($server_ip === $current_user_ip) {
			return;
		}

		if (current_user_can('manage_options')) {
			// A user with admin privileges
			$administrator = true;
			$whitelisted_user = true;
		}


		// Prevents user from being blocked even from a blocked country if IP is whitelisted
		if (in_array($current_user_ip, self::$options['whitelist'], true)) {
			$whitelisted_user = true;
		}

		if (self::is_whitelisted_service($current_user_ip)) {
			$whitelisted_user = true;
		}

		if (in_array($current_user_ip, ['::1', '127.0.0.1'], true)) {
			$whitelisted_user = false;
			$administrator = false;
		}

		$ua_string = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
		}

		// @todo - move to after bad queries?
		$current_user_country = '';
		$geolocate_ip = \WPSecurityNinja\Plugin\SN_Geolocation::geolocate_ip($current_user_ip, true);
		if ($geolocate_ip) {
			$current_user_country = $geolocate_ip['country'];
		}

		// Processing
		if (!$administrator && (1 === (int) self::$options['active'])) {

			// Checks if we are trying to unblock a new IP.
			if (isset($_REQUEST['snf']) && sanitize_key($_REQUEST['snf']) === self::$options['unblock_url']) {


				// Disable 2FA option when unblocking IP
				self::$options['2fa_enabled'] = false;
				self::$options['2fa_enabled_timestamp'] = '';
				update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
				wf_sn_el_modules::log_event('security_ninja', 'disabled_2fa', __('2FA disabled when unblocking IP.', 'security-ninja'), '');


				if (!in_array($current_user_ip, self::$options['whitelist'], true)) {
					$whitelisted_user = true;
					self::$options['whitelist'][] = $current_user_ip;
					wf_sn_el_modules::log_event('security_ninja', 'unblocked_ip', __('New IP added to the whitelist using the secret access URL.', 'security-ninja'), '');
					update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
				}
			}

			// Check IP against blacklist
			$blacklist = self::$options['blacklist'];
			if ((!$whitelisted_user) && (is_array($blacklist))) {
				foreach ($blacklist as $bl) {
					if (trim($bl) === $current_user_ip) {
						$visit_logged = true;
						wf_sn_el_modules::log_event('security_ninja', 'blacklisted_IP', $current_user_ip . ' IP is blacklisted locally.', '');

						self::update_blocked_count($current_user_ip);
						self::kill_request();
					}
				}
			}

			// Checks if IP is from a known crawler
			if ((!$whitelisted_user) && (self::validate_crawler_ip($current_user_ip))) {
				// Validated crawler
				$whitelisted_user = true;
			}

			// Check if an IP is banned and blocks
			if ((!$whitelisted_user) && (1 === (int) self::$options['active'])) {
				$ban_reason = self::is_banned_ip($current_user_ip);
				if ($ban_reason) {

					$extraarr = array(
						'ip'          => $current_user_ip,
						'user_agent'  => $ua_string,
						'request_uri' => sanitize_text_field($_SERVER['REQUEST_URI']),
						'country'     => $current_user_country,
						'ban_reason'  => 'banned_ip',
						'description' => $ban_reason,
					);

					self::log_visitor($extraarr);
					$visit_logged = true;

					wf_sn_el_modules::log_event('security_ninja', 'blocked_ip_banned', __('IP is blocked.', 'security-ninja'), $extraarr);

					self::update_blocked_count($current_user_ip);

					self::kill_request();
				}
			}

			$bad_query = self::check_bad_queries();

			// Filter bad queries
			if (!$whitelisted_user && $bad_query !== false) {
				// Detects if we are importing
				if (defined('WP_IMPORTING') && $bad_query) {
					self::log_visitor(
						array(
							'ip'          => $current_user_ip,
							'user_agent'  => $ua_string,
							'country'     => $current_user_country,
							'banned'      => 1,
							'ban_reason'  => 'login_denied_banned_ip',
							'description' => __('Suspicious data detected during import', 'security-ninja'),
							'raw_data'    => esc_sql($bad_query),
						)
					);
					$visit_logged = true;
					// set the query to false, not going to block but we left a notice
					$bad_query = false;
				}


				if ($bad_query) {
					$extramessage  = '';
					$extraarr      = [
						'ban_type' => '',
					];

					if (isset($bad_query['request_uri'])) {
						$extraarr['ban_reason'] =  $bad_query['request_uri'];
						$extraarr['ban_type']   = 'request_uri';
						$extramessage = 'request_uri';
					}

					if (isset($bad_query['query_string'])) {
						$extraarr['ban_type']   = 'query_string';
						$extraarr['ban_reason'] =  $bad_query['query_string'];
					}

					if (isset($bad_query['http_user_agent'])) {
						$extraarr['ban_type']   = 'http_user_agent';
						$extraarr['ban_reason'] =  $bad_query['http_user_agent'];
					}

					if (isset($bad_query['referrer'])) {
						$extraarr['ban_type']   = 'referrer';
						$extraarr['ban_reason'] =  $bad_query['referrer'];
					}

					if (isset($bad_query['blocked_host'])) {
						$extraarr['ban_type']   = 'blocked_host';
						$extraarr['ban_reason'] = $bad_query['visitor_host'];
					}

					$extraarr = array_merge($extraarr, $bad_query);

					$extraarr['ip']         = $current_user_ip;
					$extraarr['country']    = $current_user_country;
					$extraarr['user_agent'] = $ua_string;

					$request_uri = isset($_SERVER['REQUEST_URI']) ? esc_url($_SERVER['REQUEST_URI']) : '';
					if (!empty($request_uri)) {
						$extraarr['request_uri'] = $request_uri;
					}

					$query_string = isset($_SERVER['QUERY_STRING']) ? esc_url($_SERVER['QUERY_STRING']) : '';
					if (!empty($query_string)) {
						$extraarr['query_string'] = $query_string;
					}

					$http_referer = isset($_SERVER['HTTP_REFERER']) ? esc_url($_SERVER['HTTP_REFERER']) : '';
					if (!empty($http_referer)) {
						$extraarr['http_referer'] = $http_referer;
					}

					$blockedmessage = __('Suspicious Request', 'security-ninja');
					if (isset($extramessage)) {
						$blockedmessage .= ' ' . $extramessage;
					}

					wf_sn_el_modules::log_event('security_ninja', 'blocked_ip_suspicious_request', $blockedmessage, $extraarr);

					$extraarr = array_merge($extraarr, $bad_query);

					self::log_visitor(
						array(
							'ip'          => $current_user_ip,
							'user_agent'  => $ua_string,
							'country'     => $current_user_country,
							'banned'      => 1,
							'ban_reason'  => 'blocked_ip_suspicious_request_' . esc_attr($extraarr['ban_type']),
							'description' => __('Blocked', 'security-ninja') . ' ' . esc_attr($extraarr['ban_type']),
							'raw_data'    => $extraarr,
						)
					);
					$visit_logged = true;

					// Report to network - checks if enabled inside the function.
					$submitargs = array(
						'ban_type'   => $extraarr['ban_type'],
						'ban_reason' => $extraarr['ban_reason'],
					);
					self::network_reportip($current_user_ip, $submitargs);
					self::update_blocked_count($current_user_ip);

					self::kill_request();
				}
			}


			// checking if user is from blocked country - only if country has been deteccted
			if ((!$whitelisted_user) && ('' !== $current_user_country) && (self::is_banned_country($current_user_ip) === $current_user_country)) {

				self::log_visitor(
					array(
						'ip'          => $current_user_ip,
						'user_agent'  => $ua_string,
						'request_uri' => sanitize_text_field($_SERVER['REQUEST_URI']),
						'country'     => $current_user_country,
						'banned'      => 1,
						'ban_reason'  => 'country_banned_' . esc_attr($current_user_country),
						'description' => 'Blocked visit, country banned - ' . $current_user_country,
					)
				);

				$visit_logged = true;

				wf_sn_el_modules::log_event('security_ninja', 'blocked_ip_country_ban', $current_user_country . ' is blocked.', '');

				self::update_blocked_count($current_user_ip);
				self::kill_request();
			}
		} // not admin and active

		if (!$visit_logged) {
			// LARS - vi tracker besøgende uanset hvad
			self::log_visitor(
				array(
					'ip'         => $current_user_ip,
					'user_agent' => $ua_string,
					'country'    => $current_user_country,
					'banned'     => 0,
				)
			);
		}
	}




	/**
	 * Checks for bad queries - CBQ - Taken with a little shame from BBQ - thank you for the superfast firewall
	 * Based on 8G Firewall by Jeff Starr - https://perishablepress.com/8g-blacklist/
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  array
	 */
	public static function check_bad_queries()
	{
		$request_uri_array = apply_filters(
			'request_uri_items',
			array(
				',{3,}', // Matches three or more commas
				'-{7,}', // Matches seven or more hyphens
				'[\^`<>\\\\|]', // Matches caret, backtick, less than, greater than, backslash, or pipe characters
				'[a-z0-9]{2000,}', // Matches any string with 2000 or more alphanumeric characters
				'=[\\\\\']?(\.)', // Matches = followed optionally by a backslash or single quote and a dot
				'/(\*|"|\'|\.|,|&|&amp;?)/?$', // Matches / followed by *, ", ', ., ,, &, optionally &amp;, and optionally ending with /
				'\.(php)\(([0-9]+)\)/*$', // Matches .php followed by a parenthesis with numbers and optionally ending with /
				'/(.*)(header:|set-cookie:)(.*)=', // Matches any string containing header: or set-cookie: followed by =
				'(\.(s?ftp-?)config|(s?ftp-?)config\.)', // Matches .config or config. with optional sftp- prefix
				'/(f?ckfinder|fck/|f?ckeditor|fullclick)', // Matches paths related to file editors or ckfinder
				'/((force-)?download|framework/main)(\.php)', // Matches specific PHP files or paths indicating file download or framework access
				'(\{0\}|"0"="0|/\(/|\.\.\.|\+\+\+|\\")', // Matches {0}, "0"="0, /(/, ..., +++, or \"
				'/(vbull(etin)?|boards|vbforum|vbweb|webvb)(/)?', // Matches paths related to vBulletin or similar forum software
				'(\.|20)(get|the)(_)(permalink|posts_page_url)(\()', // Matches specific patterns that could indicate an attempt to exploit or probe
				'(/|;|=|,|&)nt\.', // Matches /, ;, =, ,, or & followed immediately by nt.
				'(@eval|eval\()', // Matches eval function calls with more specificity
				'union(.*)select', // Matches SQL UNION SELECT injections
				'\(null\)', // Matches (null)
				'base64_', // Matches base64_ indicating potential base64 encoded payloads
				'(\/|%2f)localhost', // Matches /localhost with direct or URL encoded slash
				'(\/|%2f)pingserver', // Matches /pingserver, potentially indicating probing
				'wp-config\.php', // Matches WordPress configuration file access attempts
				'(\/|\.)(s?ftp-?)?conf(ig)?(uration)?\.', // Matches config or configuration file access attempts with various prefixes
				'\/wwwroot', // Matches /wwwroot, indicating attempts to access root directories
				'\/makefile', // Matches /makefile, potentially indicating an attempt to access Unix make files
				'crossdomain\.', // Matches crossdomain., indicating attempts to access cross-domain policy files
				'self\/environ', // Matches self/environ, indicating attempts to access environment variables
				'usr\/bin\/perl', // Matches usr/bin/perl, potentially indicating attempts to execute Perl scripts
				'var\/lib\/php', // Matches var/lib/php, potentially indicating attempts to access PHP session files
				'etc\/passwd', // Matches etc/passwd, indicating attempts to access system password files
				'\/ftp:',
				'\/file:',
				'\/php:',
				'\/cgi\/', // Matches attempts to access various protocols or CGI - removed '\/https?:', '\/http:',
				'\.asp',
				'\.bak',
				'\.bash',
				'\.bat',
				'\.cfg',
				'\.cgi',
				'\.cmd', // Matches various script or backup file extensions
				'\.conf',
				'\.db',
				'\.dll',
				'\.ds_store',
				'\.exe', // More file extensions potentially indicating malicious intent
				'\/\.git',
				'\.hta',
				'\.htp',
				'\.inc',
				'\.init?', // More patterns indicating access to hidden, config, or script files
				'\.jsp',
				'\.mysql',
				'\.pass',
				'\.pwd', // Matches attempts to access JSP, MySQL, password, or pwd files
				'\.env',
				'\.c99\.php',
				'\.sql', // Matches attempts to access environment settings, C99 PHP shells, or SQL dumps
				'\/\.svn', // Matches attempts to access SVN directories
				'\.exec\(',
				'\)\.html\(',
				'\{x\.html\(', // Matches attempts to execute code or inject HTML
				'\.php\([0-9]+\)', // Matches PHP files with a numeric parameter, indicating potential exploits
				'(benchmark|sleep)(\s|%20)*\(', // Matches attempts to use SQL benchmark or sleep functions for DoS
				'\/(db|mysql)-?admin', // Matches attempts to access database admin interfaces
				'\/document_root', // Matches attempts to access the document root path
				'\/error_log', // Matches attempts to access error log files
				'indoxploi',
				'\/sqlpatch',
				'xrumer', // Matches known exploit or spam bot identifiers
				'www\.(.*)\.cn', // Matches domains potentially related to malicious Chinese sites
				'%3Cscript', // Matches URL encoded <script tags
				'\/vbforum(\/)?',
				'\/vbulletin(\/)?', // Matches attempts to access vBulletin forums
				'\{\$itemURL\}', // Matches attempts to exploit template injection vulnerabilities
				'(\/bin\/)(cc|chmod|chsh|cpp|echo|id|kill|mail|nasm|perl|ping|ps|python|tclsh)(\/)?$', // Matches commands that should not be accessible via URL
				'((curl_|shell_)?exec|(f|p)open|function|fwrite|leak|p?fsockopen|passthru|phpinfo|posix_(kill|mkfifo|setpgid|setsid|setuid)|proc_(close|get_status|nice|open|terminate)|system)(.*)(\()(.*)(\))', // Matches various functions that could be used maliciously to execute code or commands
				'(\/)(^$|0day|configbak|curltest|db|index\.php\/index|(my)?sql|(php|web)?shell|php-?info|temp00|vuln|webconfig)(\.php)' // Matches known paths and filenames associated with exploits, vulnerabilities, or administrative tools
			)
		);






		$request_uri_array = apply_filters(
			'request_uri_items',
			array(
				',{3,}', // Matches three or more commas
				'-{7,}', // Matches seven or more hyphens
				'[\^`<>\\|]', // Matches caret, backtick, less than, greater than, backslash, or pipe characters
				'[a-z0-9]{2000,}', // Matches any string with 2000 or more alphanumeric characters
				'=?\\\\(\'|%27)/?\\.', // Matches = followed optionally by a backslash or single quote and a dot
				'/(\*|"|\'|\.|,|&|&amp;?)/?$', // Matches / followed by *, ", ', ., ,, &, optionally &amp;, and optionally ending with /
				'\.(php)(\()?([0-9]+)(\))?(/)?$', // Matches .php optionally followed by (), a number, and optionally ending with /
				'/(.*)(header:|set-cookie:)(.*)=', // Matches any string containing header: or set-cookie: followed by =
				'\.(s?ftp-?)config|(s?ftp-?)config\.', // Matches .config or config. with optional sftp- prefix
				'/(f?ckfinder|fck/|f?ckeditor|fullclick)', // Matches paths related to file editors or ckfinder
				'/((force-)?download|framework/main)(\.php)', // Matches specific PHP files or paths indicating file download or framework access
				'\{0\}|"0"="0|/\(/\|\.\.\.|\+\+\+|\\\\"', // Matches {0}, "0"="0, /(/, ..., +++, or \"
				'/(vbull(etin)?|boards|vbforum|vbweb|webvb)(/)?', // Matches paths related to vBulletin or similar forum software
				'(\.|20)(get|the)(_)(permalink|posts_page_url)\(', // Matches specific patterns that could indicate an attempt to exploit or probe
				'///|\?\?|/&&|/\*(.*)\*/|/:/|\\\\|0x00|%00|%0d%0a', // Matches sequences that could be part of complex injections
				'(/)(cgi_?)?alfa(_?cgiapi|_?data|_?v[0-9]+)?(\\.php)', // Matches specific CGI or PHP files with "alfa" in the name
				'(thumbs?(_editor|open)?|tim(thumbs?)?)((\\.|%2e)php)', // Matches thumbnail editor or opener PHP files
				'(/)((boot)?_?admin(er|istrator|s)(_events)?)(\\.php)', // Matches admin-related PHP files with optional prefixes
				'(/%7e)(root|ftp|bin|nobody|named|guest|logs|sshd)(/)', // Matches user directories for sensitive usernames
				'(archive|backup|db|master|sql|wp|www|wwwroot)\\.(gz|zip)', // Matches backup or archive files
				'(/)(\\.?mad|alpha|c99|php|web)?sh(3|e)ll([0-9]+|\\w)(\\.php)', // Matches shell scripts, including common names and versions
				'(/)(admin-?|file-?)(upload)(bg|_?file|ify|svu|ye)?(\\.php)', // Matches various upload handler scripts
				'(/)(etc|var)(/)(hidden|secret|shadow|ninja|passwd|tmp)(/)?', // Matches paths attempting to access sensitive directories
				'(s)?(ftp|inurl|php)(s)?(:(/|%2f|%u2215)(/|%2f|%u2215))', // Matches attempts to access or manipulate URLs // *** Lars removed 'http'
				'(/)(=|\\$&?|&?(pws|rk)=0|_mm|_vti_|cgi(\\.|-)?|(=|/|;|,)nt\\.)', // Matches various query string manipulations and CGI invocations
				'(\\.)(ds_store|htaccess|htpasswd|init?|mysql-select-db)(/)?', // Matches attempts to access sensitive server files
				'(/)(bin)(/)(cc|chmod|chsh|cpp|echo|id|kill|mail|nasm|perl|ping|ps|python|tclsh)(/)?', // Matches commands that should not be accessible via URL
				// '(/)(::[0-9999]|%3a%3a[0-9999]|127\\.0\\.0\\.1|ccx|localhost|makefile|pingserver|wwwroot)(/)?', // Matches localhost, specific ports, or sensitive file names
				// '^(/)(123|backup|bak|beta|bkp|default|demo|dev(new|old)?|home|new-?site|null|old|old_files|old1)(/)?', // Matches common directory names used for backups or development
				'(/)?j(\\s+)?a(\\s+)?v(\\s+)?a(\\s+)?s(\\s+)?c(\\s+)?r(\\s+)?i(\\s+)?p(\\s+)?t(\\s+)?(%3a|:)', // Matches obfuscated JavaScript
				// '^(/)(old-?site(back)?|old(web)?site(here)?|sites?|staging|undefined|wordpress([0-9]+)|wordpress-old)(/)?', // Matches common temporary or old site structures
				'(/)(filemanager|htdocs|httpdocs|https?|mailman|mailto|msoffice|undefined|usage|var|vhosts|webmaster|www)(/)', // Matches common server directories or services - removed
				'(\\(null\\)|\\{\\$itemURL\\}|cast\\(0x|echo(.*)kae|etc/passwd|eval\\(|null(.*)null|open_basedir|self/environ|\\+union\\+all\\+select)', // Matches various injection or exploit attempts
				'(/)(db-?|j-?|my(sql)?-?|setup-?|web-?|wp-?)?(admin-?)?(setup-?)?(conf\\b|conf(ig)?)(uration)?(\\.?bak|\\.inc)?(\\.inc|\\.old|\\.php|\\.txt)', // Matches configuration or setup files
				'(/)((.*)crlf-?injection|(.*)xss-?protection|__(inc|jsc)|administrator|author-panel|cgi-bin|database|downloader|(db|mysql)-?admin)(/)', // Matches common admin or exploit paths
				'(/)(haders|head|helpear|incahe|includes?|indo(sec)?|infos?|install|ioptimizes?|jmail|js|king|kiss|kodox|kro|legion|libsoft)(\\.php)', // Matches potentially malicious or sensitive PHP scripts
				'(/)(awstats|document_root|dologin\\.action|error.log|extension/ext|htaccess\\.|lib/php|listinfo|phpunit/php|remoteview|server/php|www\\.root\\.)', // Matches server management or info leak paths
				'(base64_(en|de)code|benchmark|curl_exec|e?chr|eval\(|function|fwrite|(f|p)open|html|leak|passthru|p?fsockopen|phpinfo)(.*)(\\(|%28)(.*)(\\)|%29)', // Matches code execution or info leak functions
				'(posix_(kill|mkfifo|setpgid|setsid|setuid)|(child|proc)_(close|get_status|nice|open|terminate)|(shell_)?exec|system)(.*)(\\(|%28)(.*)(\\)|%29)', // Matches commands or functions that manage processes or execute commands
				'(/)((c99|php|web)?shell|crossdomain|fileditor|locus7|nstview|php(get|remoteview|writer)|r57|remview|sshphp|storm7|webadmin)(.*)(\\.|%2e|\\(|%28)', // Matches shells or remote admin tools
				'/((wp-)((201\\d|202\\d|[0-9]{2})|ad|admin(fx|rss|setup)|booking|confirm|crons|data|file|mail|one|plugins?|readindex|reset|setups?|story))(\\.php)', // Matches WordPress-specific paths
				'(/)(^$|-|\\!|\\w|\\.(.*)|100|123|([^iI])?ndex|index\\.php/index|7yn|90sec|aill|ajs\\.delivery|al277|alexuse?|ali|allwrite)(\\.php)', // Matches various PHP scripts or obfuscated paths
				'(/)(analyser|apache|apikey|apismtp|authenticat(e|ing)|autoload_classmap|backup(_index)?|bakup|bkht|black|bogel|bookmark|bypass|cachee?)(\\.php)', // Matches scripts related to site management or exploits
				'(/)(clean|cm(d|s)|con|connector\\.minimal|contexmini|contral|curl(test)?|data(base)?|db|db-cache|db-safe-mode|defau11|defau1t|dompdf|dst)(\\.php)', // Matches database, cleanup, or testing scripts
				'(/)(elements|emails?|error.log|ecscache|edit-form|eval-stdin|export|evil|fbrrchive|filemga|filenetworks?|f0x|gank(\\.php)?|gass|gel|guide)(\\.php)', // Matches various scripts that could be used for exploits or management
				'(/)(logo_img|lufix|mage|marg|mass|mide|moon|mssqli|mybak|myshe|mysql|mytag_js?|nasgor|newfile|news|nf_?tracking|nginx|ngoi|ohayo|old-?index)(\\.php)', // Matches scripts that could indicate custom or exploit functionality
				'(/)(olux|owl|pekok|petx|php-?info|phpping|popup-pomo|priv|r3x|radio|rahma|randominit|readindex|readmy|reads|repair-?bak|robot(s\\.txt)?|root)(\\.php)', // Matches diagnostic, exploit, or specific functionality scripts
				'(/)(router|savepng|semayan|shell|shootme|sky|socket(c|i|iasrgasf)ontrol|sql(bak|_?dump)?|sym403|sys|system_log|tmp-?(uploads)?)(\\.php)', // Matches testing, system control, or potential exploit scripts @todo? coupon=testcoupon

				'(/)(traffic-advice|u2p|udd|ukauka|up__uzegp|up14|upa?|upxx?|vega|vip|vu(ln)?(\\w)?|webroot|weki|wikindex|wp_logns?|wp_wrong_datlib)(\\.php)', // Matches various specific or general scripts and admin tools - lars removed "wordpress"
				'(/)((wp-?)?install(ation)?|wp(3|4|5|6)|wpfootes|wpzip|ws0|wsdl|wso(\\w)?|www|(uploads|wp-admin)?xleet(-shell)?|xmlsrpc|xup|xxu|zibi|zipy)(\\.php)', // Matches WordPress and other CMS related scripts or tools - lars removed 'xxx' June 3rd.
				// Malware and exploit signatures
				'(bkv74|cachedsimilar|core-stab|crgrvnkb|ctivrc|deadcode|deathshop|dkiz|e7xue|eqxafaj90zir|exploits|ffmkpcal|filellli7|(fox|sid)wso|gel4y|goog1es|gvqqpinc)',
				'(@md5|00\\.temp00|0byte|0d4y|0xor|wso1337|1h6j5|40dd1d|4price|70bex?|a57bze893|abbrevsprl|abruzi|adminer|aqbmkwwx|archivarix|beez5|bgvzc29)',  // removed 'backdoor' as it is too common '0day', '3XP' also
				'(handler_to_code|hax(0|o)r|hmei7|hnap1|home_url=|ibqyiove|icxbsx|indoxploi|jahat|jijle3|kcrew|laobiao|lock360|marijuan|mod_(aratic|ariimag))', // removed longdog and keywordspy as they are too common
				'(mobiquo|muiebl|nessus|osbxamip|priv8|qcmpecgy|r3vn330|racrew|raiz0|reportserver|r00t|respectmus|rom2823|roseleif|sh3ll|site((.){0,2})copier|sqlpatch|sux0r)',  // removed phpunit 
				'(sym403|telerik|uddatasql|utchiha|visualfrontend|w0rm|wangdafa|wpyii2|wsoyanzo|x5cv|xattack|xbaner|xertive|xiaolei|xltavrat|xorz|xsamxad|xsvip|xxxs?s?|zabbix|zebda)'
			)
		);

		$query_string_array = apply_filters(
			'query_string_items',
			array(
				'^(-|%2d)[^=]+$', // Matches strings that start with a hyphen or its encoding, without an equals sign following.
				'[a-z0-9]{4000,}', // Matches any alphanumeric string longer than 4000 characters.
				'(/|%2f)(:|%3a)(/|%2f)', // Matches sequences attempting to simulate or obfuscate forward slashes and colons, commonly used in URLs.
				'etc/(hosts|motd|shadow)', // Matches attempts to access sensitive system files.
				'order(\s|%20)by(\s|%20)1--', // Matches SQL injection attempts that use ORDER BY clauses.
				'(/|%2f)(\*|%2a)(\*|%2a)(/|%2f)', // Matches sequences of asterisks surrounded by slashes, potentially indicating comment injection or bypass attempts.
				'(`|<|>|\\^|\\||0x00|%00|%0d%0a)', // Matches attempts to use special characters or encoded null bytes and line terminators.
				'(f?ckfinder|f?ckeditor|fullclick)', // Matches common file upload and management tools that might be targeted for exploitation.
				'((.*)header:|(.*)set-cookie:(.*)=)', // Matches manipulation attempts of HTTP headers.
				'(localhost|127(\\.|%2e)0(\\.|%2e)0(\\.|%2e)1)', // Matches local IP addresses, potentially indicating SSRF attacks.
				'(cmd|command)(=|%3d)(chdir|mkdir)(.*)(x20)', // Matches command injection attempts.
				'(globals|mosconfig[a-z_]{1,22}|request)(=|\\[)', // Matches attempts to manipulate PHP globals or Joomla configuration globals.
				'(/|%2f)((wp-)?config)((\\.|%2e)inc)?((\\.|%2e)php)', // Matches attempts to access WordPress or other configuration files.
				'(thumbs?(_editor|open)?|tim(thumbs?)?)((\\.|%2e)php)', // Matches attempts to exploit thumb generator scripts.
				'(absolute_|base|root_)(dir|path)(=|%3d)(ftp|https?)', // Matches attempts to manipulate PHP directory or path settings.
				'(s)?(ftp|inurl|php)(s)?(:(/|%2f|%u2215)(/|%2f|%u2215))', // Matches attempts to inject FTP or PHP URLs.
				'(\\.|20)(get|the)(_|%5f)(permalink|posts_page_url)(\\(|%28)', // Matches complex injection attempts, potentially targeting WordPress permalink structure.
				'((boot|win)((\\.|%2e)ini)|etc(/|%2f)passwd|self(/|%2f)environ)', // Matches attempts to access critical system files or environment paths.
				'(((/|%2f){3,})|((\\.|%2e){3,})|((\\.|%2e){2,})(/|%2f|%u2215))', // Matches path traversal attempts with excessive slashes or dots.
				'(benchmark|char|exec|fopen|function|html)(.*)(\\(|%28)(.*)(\\)|%29)', // Matches code execution or injection attempts.
				'(php)([0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})', // Matches PHP object injection attempts.
				'(e|%65|%45)(v|%76|%56)(a|%61|%31)(l|%6c|%4c)(.*)(\\(|%28)(.*)(\\)|%29)', // Matches eval function attempts with various encodings.
				'(/|%2f)(=|%3d|$&|_mm|cgi(\\.|\\-)|inurl(:|%3a)(/|%2f)|(mod|path)(=|%3d)(\\.|%2e))', // Matches various injection or manipulation attempts.
				'(<|%3c)(.*)(e|%65|%45)(m|%6d|%4d)(b|%62|%42)(e|%65|%45)(d|%64|%44)(.*)(>|%3e)', // Matches embedded HTML or scripts
				'(<|%3c)(.*)(i|%69|%49)(f|%66|%46)(r|%72|%52)(a|%61|%41)(m|%6d|%4d)(e|%65|%45)(.*)(>|%3e)', // Matches iframe injections
				'(<|%3c)(.*)(o|%4f|%6f)(b|%62|%42)(j|%4a|%6a)(e|%65|%45)(c|%63|%43)(t|%74|%54)(.*)(>|%3e)', // Matches object tag injections
				'(<|%3c)(.*)(s|%73|%53)(c|%63|%43)(r|%72|%52)(i|%69|%49)(p|%70|%50)(t|%74|%54)(.*)(>|%3e)', // Matches script tag injections
				'(\\+|%2b|%20)(d|%64|%44)(e|%65|%45)(l|%6c|%4c)(e|%65|%45)(t|%74|%54)(e|%65|%45)(\\+|%2b|%20)', // Matches space or encoded space followed by 'delete'
				'(\\+|%2b|%20)(i|%69|%49)(n|%6e|%4e)(s|%73|%53)(e|%65|%45)(r|%72|%52)(t|%74|%54)(\\+|%2b|%20)', // Matches space or encoded space followed by 'insert'
				'(\\+|%2b|%20)(s|%73|%53)(e|%65|%45)(l|%6c|%4c)(e|%65|%45)(c|%63|%43)(t|%74|%54)(\\+|%2b|%20)', // Matches space or encoded space followed by 'select'
				'(\\+|%2b|%20)(u|%75|%55)(p|%70|%50)(d|%64|%44)(a|%61|%41)(t|%74|%54)(e|%65|%45)(\\+|%2b|%20)', // Matches space or encoded space followed by 'update'
				'(\\\\x00|(\"|%22|\'|%27)?0(\"|%22|\'|%27)?(=|%3d)(\"|%22|\'|%27)?0|cast(\\(|%28)0x|or%201(=|%3d)1)', // Matches null byte, zero equals zero, or SQL 'or 1=1' pattern
				'(g|%67|%47)(l|%6c|%4c)(o|%6f|%4f)(b|%62|%42)(a|%61|%41)(l|%6c|%4c)(s|%73|%53)(=|\\[|%[0-9A-Z]{0,2})', // Matches attempts to access or manipulate PHP globals
				'(_|%5f)(r|%72|%52)(e|%65|%45)(q|%71|%51)(u|%75|%55)(e|%65|%45)(s|%73|%53)(t|%74|%54)(=|\\[|%[0-9A-Z]{2,})', // Matches attempts to access or manipulate the request array
				'(j|%6a|%4a)(a|%61|%41)(v|%76|%56)(a|%61|%31)(s|%73|%53)(c|%63|%43)(r|%72|%52)(i|%69|%49)(p|%70|%50)(t|%74|%54)(:|%3a)(.*)(;|%3b|\\)|%29)', // Matches javascript protocol injection
				'(b|%62|%42)(a|%61|%41)(s|%73|%53)(e|%65|%45)(6|%36)(4|%34)(_|%5f)(d|%64|%44)(e|%65|%45|n|%6e|%4e)(c|%63|%43)(o|%6f|%4f)(d|%64|%44)(e|%65|%45)(.*)(\\()(.*)(\\))', // Matches base64 encode function attempts




			)
		);



		$user_agent_array = apply_filters(
			'user_agent_items',
			array(
				'[a-z0-9]{2000,}', // Matches user agents longer than 2000 alphanumeric characters.
				'(&lt;|%0a|%0d|%27|%3c|%3e|%00|0x00|\\\x22)', // Matches specific encoded entities and characters often used in injection attacks.
				'(curl|libwww-perl|pycurl|scan)', // Matches a list of common scraping and automation user agents. - note removed ahrefs and archiver
				'(oppo\\sa33|(c99|php|web)shell|site((.){0,2})copier)', // Matches known malicious or automated tools user agents.
				'(base64_decode|bin/bash|disconnect|eval|unserializ)', // Matches patterns indicative of command execution or deserialization attempts.
				// Continues with patterns to match various bot and potentially malicious user agents
				'(acapbot|acoonbot|alexibot|asterias|attackbot|awario|backdor|becomebot|binlar|blackwidow|blekkobot|blex|blowfish|bullseye|bunnys|butterfly|careerbot|casper)',
				'(checkpriv|cheesebot|cherrypick|chinaclaw|choppy|clshttp|cmsworld|copernic|copyrightcheck|cosmos|crescent|datacha|\\bdemon\\b|diavol|discobot|dittospyder)',
				'(dotbot|dotnetdotcom|dumbot|econtext|emailcollector|emailsiphon|emailwolf|eolasbot|eventures|extract|eyenetie|feedfinder|flaming|flashget|flicky|foobot|fuck)',
				'(g00g1e|getright|gigabot|go-ahead-got|gozilla|grabnet|grafula|harvest|heritrix|httracks?|icarus6j|jetbot|jetcar|jikespider|kmccrew|leechftp|libweb|liebaofast)',
				'(linkscan|linkwalker|loader|lwp-download|majestic|masscan|miner|mechanize|mj12bot|morfeus|moveoverbot|netmechanic|netspider|nicerspro|nikto|nominet|nutch)',
				'(octopus|pagegrabber|petalbot|planetwork|postrank|proximic|purebot|queryn|queryseeker|radian6|radiation|realdownload|remoteview|rogerbot|scan|scooter|seekerspid)',
				'(semalt|siclab|sindice|sistrix|sitebot|siteexplorer|sitesnagger|skygrid|smartdownload|snoopy|sosospider|spankbot|spbot|sqlmap|stackrambler|stripper|sucker|surftbot)',
				'(sux0r|suzukacz|suzuran|takeout|telesoft|true_robots|turingos|turnit|vampire|vikspider|voideye|webleacher|webreaper|webstripper|webvac|webviewer|webwhacker)',
				'(winhttp|wwwoffle|woxbot|xaldon|xxxyy|yamanalab|yioopbot|youda|zeus|zmeu|zune|zyborg)' // Matches various known bots, crawlers, and potentially harmful automated agents.
			)
		);



		$referrer_array = apply_filters(
			'referrer_items',
			array(
				'100dollars',
				'@unlink',
				'assert\(',
				'best-seo',
				'blue\s?pill',
				'cocaine',
				'ejaculat',
				'erectile',
				'erections',
				'hoodia',
				'huronriveracres',
				'impotence',
				'levitra',
				'libido',
				'lipitor',
				'mopub\.com',
				'order(\s|%20)by(\s|%20)1--',
				'phentermin',
				'pornhelm',
				'print_r\(',
				'pro[sz]ac',
				'sandyauer',
				'semalt\.com',
				'social-buttions',
				'todaperfeita',
				'tramadol',
				'troyhamby',
				'ultram',
				'unicauca',
				'valium',
				'viagra',
				'vicodin',
				'x00',
				'xanax',
				'xbshell',
				'ypxaieo'
			)
		);

		$blocked_hosts_array = apply_filters(
			'blocked_hosts_items',
			array(
				'163data',
				'colocrossing',
				'crimea',
				'g00g1e',
				'justhost',
				'kanagawa',
				'loopia',
				'masterhost',
				'onlinehome',
				'poneytel',
				'sprintdatacenter',
				'reverse.softlayer',
				'safenet',
				'ttnet',
				'woodpecker',
				'wowrack'
			)
		);


		$request_uri_string  = false;
		$query_string_string = false;
		$user_agent_string   = false;
		$referrer_string     = false;
		$visitor_host = false;

		if (isset($_SERVER['REQUEST_URI']) && !empty($_SERVER['REQUEST_URI'])) {
			$request_uri_string = $_SERVER['REQUEST_URI'];
		}
		if (isset($_SERVER['QUERY_STRING']) && !empty($_SERVER['QUERY_STRING'])) {
			$query_string_string = $_SERVER['QUERY_STRING'];
		}
		if (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT'])) {
			$user_agent_string = $_SERVER['HTTP_USER_AGENT'];
		}
		if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
			$referrer_string = $_SERVER['HTTP_REFERER'];
		}
		if (isset($_SERVER['REMOTE_ADDR']) && !empty($_SERVER['REMOTE_ADDR'])) {

			$visitor_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		}

		if ($request_uri_string || $query_string_string || $user_agent_string || $referrer_string || $visitor_host) {

			$response = array();

			foreach ($blocked_hosts_array as $item) {
				if (preg_match('#' . preg_quote($item, '/') . '#i', $visitor_host, $matches)) {
					$response = [
						'blocked_host' => esc_html($matches[0]),
						'visitor_host' => esc_html($visitor_host),
						'matched_rule' => esc_html($item),
						'message' => 'A match was found in the blocked hosts.'
					];
					break;
				}
			}

			foreach ($request_uri_array as $pattern) {
				// Direct use of pattern in preg_match, without preg_quote
				if (preg_match('#' . $pattern . '#i', $request_uri_string, $req_matches)) {
					$response = [
						'request_uri' => esc_html($req_matches[0]),
						'matched_rule' => esc_html($pattern),
						'message' => 'A match was found in the request URI.'
					];
					break;
				}
			}

			// Check in $query_string_array
			if (empty($response)) { // Proceed only if no match was found previously
				foreach ($query_string_array as $item) {
					// Directly use the pattern without preg_quote() for regex matching
					if (preg_match('#' . $item . '#i', $query_string_string, $query_matches)) {
						$response = [
							'query_string' => esc_html($query_matches[0]),
							'query_string_string' => esc_html($query_string_string),
							'matched_rule' => esc_html($item),
							'message' => 'A match was found in the query string.'
						];
						break;
					}
				}
			}


			if (empty($response)) { // Proceed only if no match was found previously
				foreach ($user_agent_array as $item) {
					// Using '#' as delimiter to avoid conflicts with common characters in user agents
					if (preg_match('#' . $item . '#i', $user_agent_string, $ua_matches)) {
						$response = [
							'http_user_agent' => esc_html($ua_matches[0]),
							'user_agent_string' => esc_html($user_agent_string),
							'matched_rule' => esc_html($item),
							'message' => 'A match was found in the user agent.'
						];
						break;
					}
				}
			}


			if (empty($response)) {
				foreach ($referrer_array as $item) {
					if (preg_match('/' . preg_quote($item, '/') . '/i', $referrer_string, $rf_matches)) {
						$response = [
							'referrer' => esc_html($rf_matches[0]),
							'referrer_string' => esc_html($referrer_string),
							'matched_rule' => esc_html($item),
							'message' => 'A match was found in the referrer.'
						];
						break;
					}
				}
			}

			if (!empty($response)) {
				return $response;
			}
		}
		return false;
	}





	/**
	 * Terminate current request - Checks if option is set to redirect to an URL first
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Saturday, December 10th, 2022.	
	 * @version	v1.0.2	Wednesday, December 20th, 2023.
	 * @access	public static
	 * @return	void
	 */
	public static function kill_request()
	{

		// Set the constant to prevent caching
		if (! defined('DONOTCACHEPAGE')) {
			define('DONOTCACHEPAGE', true);
		}

		// Add headers to prevent caching on Cloudflare and other proxies
		header('Cache-Control: no-cache, no-store, must-revalidate');
		header('Pragma: no-cache');
		header('Expires: 0');

		// Add Cloudflare specific header to prevent caching
		header('Cache-Tag: dontcache'); // Cloudflare specific header to control caching
		header('CF-Cache-Status: DYNAMIC'); // Forces dynamic content, not cacheable
			
		// Checks if we need to redirect the killed request.
		$redirect_url = esc_url_raw(self::$options['redirect_url']);
		if ((isset($redirect_url)) && (wp_http_validate_url($redirect_url))) {
			wp_safe_redirect($redirect_url, 301);
			exit;
		}
		$message = '<p>' . esc_html(self::$options['message']) . '</p>';
		// Add IP info to message
		$current_user_ip = self::get_user_ip();
		$message .= '<p><small>' . esc_html__('Your IP:', 'security-ninja') . ' ' . esc_html($current_user_ip) . '</small></p>';

		// Removes a couple of filters that uses a check "is_embed()" which is too soon to be available
		// and that creates a PHP warning.
		remove_filter('wp_robots', 'wp_robots_noindex_search');
		remove_filter('wp_robots', 'wp_robots_noindex_embeds');

		wp_die(
			$message,
			esc_html__('Blocked', 'security-ninja'),
			array(
				'response' => 403,
			)
		);
	}





	/**
	 * Updates global blocked visits count
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $ip IP that was blocked - NOT IN USE YET
	 * @return  void
	 */
	public static function update_blocked_count($ip)
	{
		// @todo - store block count per IP
		$blocked_count = get_option('wf_sn_cf_blocked_count');
		if ($blocked_count) {
			$blocked_count++;
		} else {
			$blocked_count = 1;
		}
		update_option('wf_sn_cf_blocked_count', $blocked_count, false);
	}

	/**
	 * Logs visitor event.
	 * Accepted parameters:
	 * ip - detected automatically if not parsed
	 * country - detected automatically if not parsed
	 * user_agent - can be empty
	 * banned - can be empty, default 0
	 * ban_reason - can be empty
	 * description - can be empty
	 * raw_data - can be empty - accepts array
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Thursday, July 22nd, 2021.	
	 * @version	v1.0.1	Saturday, November 19th, 2022.	
	 * @version	v1.0.2	Monday, January 16th, 2023.
	 * @access	public static
	 * @param	mixed	$params	Default: array()
	 * @return	mixed
	 */
	public static function log_visitor($params = array())
	{
		$trackvisits = intval(self::$options['trackvisits']);
		if ($trackvisits !== 1) {
			// We are not tracking visitors, so returning.
			return;
		}
		$ua_string = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
		}
		$default_values = array(
			'ip'           => '',
			'raw_data'     => array(),
			'ban_reason'   => '',
			'description'  => '',
			'banned'       => 0,
			'country'      => '',
			'user_agent'   => $ua_string,
		);
		$params = array_merge($default_values, $params);
		global $wpdb;
		$args = array(
			'timestamp'   => current_time('mysql'),
			'ip'          => $params['ip'],
			'country'     => $params['country'],
			'user_agent'  => $params['user_agent'],
			'banned'      => $params['banned'],
			'ban_reason'  => $params['ban_reason'],
			'description' => maybe_serialize($params['description']),
			'URL'         => esc_url_raw($_SERVER['REQUEST_URI']),
			'raw_data'    => wp_json_encode($params['raw_data']),
		);
		$insert_result = $wpdb->insert(
			$wpdb->prefix . WF_SN_CF_LOG_TABLE,
			$args,
			array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
		);
		if (false === $insert_result) {
			return false;
		}
		return $wpdb->insert_id;
	}


	/**
	 * Update local list of blocked IPs.
	 * First delete expired > 24 hours.
	 * Then download and bulk add entries
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Sunday, May 30th, 2021.
	 * @version v1.0.1  Wednesday, June 9th, 2021.
	 * @access  public static
	 * @global
	 * @param   boolean $force  Default: false
	 * @return  void
	 */
	public static function action_update_blocked_ips($force = false)
	{

		$listips = self::get_network_listips();

		if (!$listips) {
			wf_sn_el_modules::log_event('security_ninja', 'update_blocked_ips', 'Error getting blocked IPs from server');
			return false;
		}
		global $wpdb;
		// Cleaning up
		$table_name = $wpdb->prefix . WF_SN_CF_BLOCKED_IPS_TABLE;

		$delquery = "DELETE FROM `{$table_name}` WHERE HOUR(TIMEDIFF(NOW(), tid))>24;";
		$delres   = $wpdb->query($delquery);
		if ($delres) {
			wf_sn_el_modules::log_event('security_ninja', 'update_blocked_ips', sprintf(esc_html__('Removed %1$s IPs from the Blocklist - older than 24 hours.', 'security-ninja'), intval($delres)), '');
		} else {
			wf_sn_el_modules::log_event('security_ninja', 'update_blocked_ips', 'No old IPs needs to be removed.');
		}

		$blockedips = json_decode($listips, true);

		if ($blockedips && is_array($blockedips) && isset($blockedips['ips']) && is_array($blockedips['ips'])) {

			global $wpdb;
			$current_count = 0;
			$limit         = 15;
			$longquery     = '';
			$totalcount    = 0;
			$timenow       = current_time('mysql');
			foreach ($blockedips['ips'] as $ip) {
				if (0 === $current_count) {
					$longquery .= ' INSERT IGNORE INTO `' . $table_name . "` (`ip`) VALUES ('" . esc_sql($ip) . "')";
				} else {
					$longquery .= ",('" . esc_sql($ip) . "')";
				}
				$current_count++;

				if ($current_count > $limit) {
					$longquery .= ';'; // add ending semicolon before executing
					$wpdb->query($longquery);
					$longquery     = '';
					$current_count = 0;
				}
				$totalcount++;
			}

			// Leftovers?
			if ($current_count > 0) {
				$longquery .= ';'; // add ending semicolon before executing
				$wpdb->query($longquery);
				$longquery     = '';
				$current_count = 0;
			}
		}
	}

	/**
	 * Prune events log table
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   boolean $force  Default: false
	 * @return  boolean
	 */
	public static function prune_visitor_log($force = false)
	{
		global $wpdb;

		$trackvisits_howlong = intval(self::$options['trackvisits_howlong']);

		if (!$trackvisits_howlong) {
			$trackvisits_howlong = 7; // in days
		}

		$table_name = $wpdb->prefix . WF_SN_CF_LOG_TABLE;

		// Only prune if the table exists
		if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name)) === $table_name) {

			$wpdb->query('DELETE FROM ' . $wpdb->prefix . WF_SN_CF_LOG_TABLE . " WHERE timestamp < DATE_SUB(NOW(), INTERVAL $trackvisits_howlong DAY);");

			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event('security_ninja', 'pruned_visitor_log', sprintf(esc_html__('Pruned firewall visitors log - %1$s days.', 'security-ninja'), $trackvisits_howlong), '');
		}

		return true;
	}





	/**
	 * Plugin activation routines
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function activate()
	{
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . WF_SN_CF_LOG_TABLE;
		$charset = $wpdb->get_charset_collate();
		$main_sql_create = "CREATE TABLE $table_name (
id bigint(32) unsigned NOT NULL AUTO_INCREMENT,
timestamp datetime NOT NULL,
ip varchar(46) NOT NULL,
user_agent varchar(255) NOT NULL,
action varchar(64) NOT NULL,
description text NOT NULL,
raw_data blob NOT NULL,
country varchar(2) NOT NULL DEFAULT '',
banned tinyint(1) NOT NULL DEFAULT '0',
ban_reason varchar(64) NOT NULL,
URL text,
PRIMARY KEY  (id)
) $charset";
		dbDelta($main_sql_create);

		// local list of blocked IPs from the SN network
		$table_name = $wpdb->prefix . WF_SN_CF_BLOCKED_IPS_TABLE;
		$main_sql_create = "CREATE TABLE $table_name (
tid datetime NOT NULL DEFAULT NOW(),
ip varchar(46) NOT NULL,
PRIMARY KEY  (ip),
KEY tid (tid)
) $charset";
		dbDelta($main_sql_create);

		// Download first time the IP list or update
		self::update_cloud_ips();

		// Updates the country database when turning on firewall + via cron afterwards.
		\WPSecurityNinja\Plugin\SN_Geolocation::update_database();
	}



	/**
	 * clean-up when deactivated
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Thursday, January 14th, 2021.	
	 * @version	v1.0.1	Monday, February 21st, 2022.	
	 * @version	v1.0.2	Saturday, November 19th, 2022.
	 * @access	public static
	 * @return	void
	 */
	public static function deactivate()
	{

		//$centraloptions = Wf_Sn::get_options();
		// $centraloptions = $options = Wf_sn_cf::$options;
		if (!isset(self::$options['remove_settings_deactivate'])) {
			return;
		}
		if (self::$options['remove_settings_deactivate']) {
			global $wpdb;

			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_SN_CF_LOG_TABLE);
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_SN_CF_BLOCKED_IPS_TABLE);

			delete_option(WF_SN_CF_BLOCKED_IPS_TABLE);
			delete_option(WF_SN_CF_LOG_TABLE);
			delete_option(WF_SN_CF_VALIDATED_CRAWLERS);
			delete_option('wf_sn_cf_blocked_count');
			delete_option(WF_SN_CF_OPTIONS_KEY);
			delete_option('wf_sn_cf_ips');

			delete_option('wf_sn_banned_ips'); // list of locally banned IPs
		}
	}


	/**
	 * Schedule cron jobs
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @version v1.0.1  Thursday, January 14th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function schedule_cron_jobs()
	{
		// Update GEOIP database - once a month
		if (!wp_next_scheduled('secnin_update_geoip')) {
			wp_schedule_event(time() + 30, 'weekly', 'secnin_update_geoip');
		}

		// Update cloud IPs
		if (!wp_next_scheduled('secnin_update_cloud_firewall')) {
			wp_schedule_event(time() + 15, 'twicedaily', 'secnin_update_cloud_firewall');
		}

		// Prune local banned IPs
		if (!wp_next_scheduled('secnin_prune_banned')) {
			wp_schedule_event(time() + 3600, 'twicedaily', 'secnin_prune_banned');
		}

		// Prune visitor log
		if (!wp_next_scheduled('secnin_prune_visitor_log')) {
			wp_schedule_event(time() + 3600, 'twicedaily', 'secnin_prune_visitor_log');
		}

		// Update blocked IPs from central server
		if (!wp_next_scheduled('secnin_update_blocked_ips')) {
			wp_schedule_event(time() + 45, 'twicedaily', 'secnin_update_blocked_ips');
		}
	}


	/**
	 * Check if IP is from banned country
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   boolean $ip User IP - optionals
	 * @return  boolean
	 */
	public static function is_banned_country($ip = false)
	{

		if ($ip) {
			$current_user_ip = $ip;
		} else {
			$current_user_ip = self::get_user_ip();
		}

		$blocked_countries = self::get_blocked_countries();

		$geolocate_ip = SN_Geolocation::geolocate_ip($current_user_ip, true);

		if (isset($geolocate_ip['country'])) {
			if (in_array($geolocate_ip['country'], $blocked_countries)) {
				return $geolocate_ip['country'];
			}
		}
		return false;
	}



	/**
	 * Enqueues JS and CSS needed for Firewall tab
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function enqueue_scripts()
	{
		if (!Wf_Sn::is_plugin_page()) {
			return;
		}

		wp_enqueue_style('select2', WF_SN_PLUGIN_URL . 'modules/cloud-firewall/select2/css/select2.min.css', array(), Wf_Sn::$version);
		wp_enqueue_script('select2', WF_SN_PLUGIN_URL . 'modules/cloud-firewall/select2/js/select2.min.js', array('jquery'), Wf_Sn::$version);

		wp_enqueue_style('sn-cf-css', WF_SN_PLUGIN_URL . 'modules/cloud-firewall/css/wf-sn-cf-min.css', array(), Wf_Sn::$version);

		wp_register_script('sn-cf-js', WF_SN_PLUGIN_URL . 'modules/cloud-firewall/js/wf-sn-cf-min.js', array('select2'), wf_sn::$version, true);

		$js_vars = array(
			'nonce' => wp_create_nonce('wf_sn_cf'),
		);

		wp_localize_script('sn-cf-js', 'wf_sn_cf', $js_vars);

		wp_enqueue_script('sn-cf-js');
	}


	/**
	 * Return firewall options
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_options()
	{

		if (!is_null(self::$options)) {
			return self::$options;
		}

		$options = get_option('wf_sn_cf', array());

		$defaults = array(
			'active'                  => 0,
			'globalbannetwork'        => true,
			'global'                  => true,
			'filterqueries'           => true,
			'trackvisits'             => true,
			'trackvisits_howlong'     => 7,
			'usecloud'                => true,
			'protect_login_form'      => true,
			'hide_login_errors'       => true,
			'blocked_countries'       => array(),
			'blacklist'               => array(),
			'whitelist'               => array(self::get_user_ip()),
			'whitelist_managewp'      => true,
			'whitelist_wprocket'      => false,
			'whitelist_uptimia'				=> false,
			'max_login_attempts'      => 5,
			'max_login_attempts_time' => 5,
			'bruteforce_ban_time'     => 120,
			'login_msg'               => __('Warning: Multiple failed login attempts will get you locked out temporarily.', 'security-ninja'),
			'login_error_msg'         => __('Something went wrong', 'security-ninja'),
			'message'                 => __('You are not allowed to visit this website.', 'security-ninja'),
			'redirect_url'            => '',
			'blockadminlogin'         => 0,
			'change_login_url'        => 0,
			'new_login_url'           => 'my-login',
			'unblock_url'             => '',

			'2fa_enabled'             => false,
			'2fa_enabled_timestamp'   => '',
			'2fa_required_roles'      => ['administrator', 'editor'],
			'2fa_methods'             => ['app'],
			'2fa_grace_period'        => 14,
			'2fa_backup_codes_enabled' => true,

			'2fa_intro'               => __('Secure your account with two-factor authentication.', 'security-ninja'),
			'2fa_enter_code'          => __('Enter the code from your 2FA app to continue logging in.', 'security-ninja'),
		);

		$return = array_merge($defaults, $options);
		return $return;
	}


	/**
	 * Enables the firewall - via AJAX
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Monday, February 6th, 2023.
	 * @access	public static
	 * @return	void
	 */
	public static function ajax_enable_firewall()
	{
		check_ajax_referer('wf_sn_cf');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Failed.', 'security-ninja'),
				)
			);
		}

		self::$options['active'] = 1;
		update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
		\WPSecurityNinja\Plugin\SN_Geolocation::update_database(); // updates the geoip when turning on firewall + via cron afterwards.
		wp_send_json_success();
	}




	/**
	 * Tests if an IP is banned, via AJAX
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  string
	 */
	public static function ajax_test_ip()
	{
		check_ajax_referer('wf_sn_cf');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Failed.', 'security-ninja'),
				)
			);
		}

		if (!isset($_POST['ip'])) {
			wp_send_json_error(
				array(
					'message' => __('Missing IP.', 'security-ninja'),
				)
			);
		}

		$ip = sanitize_text_field($_POST['ip']);

		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			wp_send_json_success(__('Please enter a valid IP address to test.', 'security-ninja'));
		}

		if ($reason = self::is_banned_ip($ip)) {
			wp_send_json_success(
				sprintf(
					/* translators: 1: IP - 2: The reason, leave as is. */
					__('%1$s is banned. %2$s', 'security-ninja'),
					$ip,
					$reason
				)
			);
		} else {
			wp_send_json_success(
				sprintf(
					/* translators: 1: IP */
					__('%1$s is NOT banned.', 'security-ninja'),
					$ip
				)
			);
		}
	}




	/**
	 * Return domain from full parsed URL
	 *
	 * https://stackoverflow.com/a/18560043/452515
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $url
	 * @return  mixed
	 */
	public static function url_to_domain($url)
	{
		return implode(array_slice(explode('/', preg_replace('/https?:\/\/(www\.)?/', '', $url)), 0, 1));
	}

	/**
	 * Clear the blacklist - via AJAX
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function ajax_clear_blacklist()
	{
		check_ajax_referer('wf_sn_cf');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Failed.', 'security-ninja'),
				)
			);
		}

		self::update_banned_ips(array()); // storing an empty array overwrites
		wp_send_json_success();
	}




	/**
	 * get_banned_ips.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Wednesday, June 5th, 2024.
	 * @access	public static
	 * @return	mixed
	 */
	public static function get_banned_ips()
	{
		if (!is_null(self::$banned_ips)) {
			return self::$banned_ips;
		}

		// get the option wf_sn_banned_ips
		$wf_sn_banned_ips = get_option('wf_sn_banned_ips');

		if (is_array($wf_sn_banned_ips)) {
			return $wf_sn_banned_ips;
		} else {
			return array();
		}
	}



	/**
	 * Function to send email with unblock link via AJAX
	 * 
	 * Moved email sending to seperate function March 2022 - send_secret_access_unblock_url()
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Wednesday, March 16th, 2022.
	 * @access	public static
	 * @return	void
	 */
	public static function ajax_send_unblock_email()
	{
		check_ajax_referer('wf_sn_cf');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Failed.', 'security-ninja'),
				)
			);
		}

		if (!isset($_GET['email'])) {
			$error = new \WP_Error('001', 'No email?');
			wp_send_json_error($error);
		}

		$sanitized_email = sanitize_email($_GET['email']);

		if (false === is_email($sanitized_email)) {
			$error = new \WP_Error('002', 'Not a valid email!');
			wp_send_json_error($error);
		}

		if (!(array_key_exists('unblock_url', self::$options))) {
			self::$options['unblock_url'] = md5(time());
			update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
		}

		$sendresult = self::send_secret_access_unblock_url($sanitized_email);

		if ($sendresult) {
			wp_send_json_success(
				array(
					'message' => __('Email sent.', 'security-ninja'),
				)
			);
		} else {
			wp_send_json_error(
				array(
					'message' => __('Email could not be sent.', 'security-ninja'),
				)
			);
		}

		die();
	}



	/**
	 * send_secret_access_unblock_url.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Wednesday, March 16th, 2022.
	 * @access	public static
	 * @param	mixed	$email	
	 * @return	boolean
	 */
	public static function send_secret_access_unblock_url($email)
	{
		if (!$email) return false;

		$sanitized_email = sanitize_email($email);

		if (false === is_email($sanitized_email)) {
			$error = new \WP_Error('002', 'Not a valid email!');
			wp_send_json_error($error);
		}

		$subject = __('Security Ninja Firewall secret access link', 'security-ninja');

		$body    = '<p>Thank you for installing Security Ninja</p>';
		$body   .= '<p>Please keep this email for your records.</p>';
		$body   .= '<p>In the unlikely situation that your IP gets banned, you will need the secret access link.</p>';

		$body   .= '<p><strong>Your secret access link is ' . self::get_unblock_url() . '</strong></p>';

		$body   .= '<p>Copy-paste this URL to your browser to whitelist your IP, allowing you to log back in.</p>';

		$body   .= '<p>Please keep it safe and do not share it with others. Use it only if you get blocked by the firewall.</p>';

		$sal_email_link = Utils::generate_sn_web_link('secret_access_link', '/docs/firewall-protection/secret-access-link/', array('utm_medium' => 'email'));

		$body .= '<p><a href="' . $sal_email_link . '" target="_blank" rel="noopener">Documentation for Secret Access Link</a></p>';

		$headers = array('Content-Type: text/html; charset=UTF-8');

		$emailintrotext = 'Save your secret access link for ' . self::url_to_domain(site_url());

		$dashboardlink       = admin_url('?page=wf-sn');
		$dashboardlinkanchor = 'Security Ninja settings';

		$body .= '<p><a href="' . $dashboardlink . '" target="_blank" rel="noopener">' . $dashboardlinkanchor . '</a></p>';

		$my_replacements = array(
			'%%emailintrotext%%'      => $emailintrotext,
			'%%websitedomain%%'       => site_url(),
			'%%dashboardlink%%'       => $dashboardlink,
			'%%dashboardlinkanchor%%' => $dashboardlinkanchor,
			'%%secninlogourl%%'       => WF_SN_PLUGIN_URL . 'images/security-ninja-logo.png',
			'%%emailtitle%%'          => $subject,
			'%%sentfromtext%%'        => 'This email was sent by WP Security Ninja from ' . self::url_to_domain(site_url()),
			'%%emailcontent%%'        => nl2br($body),
		);

		if (class_exists(__NAMESPACE__ . '\wf_sn_wl')) {
			if (wf_sn_wl::is_active()) {
				$pluginname = wf_sn_wl::get_new_name();
				$my_replacements['%%sentfromtext%%'] = 'This email was sent by ' . esc_attr($pluginname) . ' from ' . esc_url(self::url_to_domain(site_url()));
			}
		}

		$template_path = WF_SN_PLUGIN_DIR . 'modules/scheduled-scanner/inc/email-default.php';

		$html = file_get_contents($template_path);

		foreach ($my_replacements as $needle => $replacement) {
			$html = str_replace($needle, $replacement, $html);
		}

		$sendresult = wp_mail($sanitized_email, $subject, $html, $headers);

		wf_sn_el_modules::log_event('security_ninja', 'install_wizard', 'Sent unblock URL to email ' . $sanitized_email);

		return $sendresult;
	}


	/**
	 * Checking if visitor is even allowed to see the login form.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function form_init_check()
	{

		self::check_visitor();
		$user_ip = self::get_user_ip();

		if ($reason = self::is_banned_ip($user_ip)) {
			self::update_blocked_count($user_ip);
			wf_sn_el_modules::log_event('security_ninja', 'login_form_blocked_ip', esc_attr($user_ip) . ' blocked from accessing the login page. ' . esc_attr($reason));
			wp_clear_auth_cookie();
			self::kill_request();
			return false;
		}
	}





	/**
	 * login_filter.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $user
	 * @param   mixed   $username
	 * @param   mixed   $password
	 * @return  mixed
	 */
	public static function login_filter($user, $username, $password)
	{

		$protect_login_form = self::$options['protect_login_form'];
		if (!$protect_login_form) {
			return $user;
		}

		$blockadminlogin = self::$options['blockadminlogin'];
		$current_user_ip = self::get_user_ip();
		if ($blockadminlogin && 'admin' === strtolower($username)) {

			wf_sn_el_modules::log_event('security_ninja', 'blockadminlogin', $current_user_ip . ' Attempt to log in as "admin" blocked.', '');
			self::update_blocked_count($current_user_ip);

			self::kill_request();
		}

		if ($reason = self::is_banned_ip($current_user_ip)) {

			// Gets IP and country array with 'ip' and 'country'
			self::update_blocked_count($current_user_ip);
			wf_sn_el_modules::log_event('security_ninja', 'login_form_blocked_ip', $current_user_ip . ' blocked from logging in. ' . $reason);
			// Kills the request or redirects based on settings
			wp_clear_auth_cookie();

			self::kill_request();
		}

		return $user;
	}




	public static function update_banned_ips($new_list)
	{

		// Check if $new_list is an array
		if (!is_array($new_list)) {
			return false;
		}

		update_option('wf_sn_banned_ips', $new_list, false);
	}

	/**
	 * Prune banned ips
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function prune_banned()
	{
		$update = false;

		$banned_ips = self::get_banned_ips();
		if ($banned_ips) {
			foreach ($banned_ips as $ip => $time) {
				if ($time < current_time('timestamp')) {
					unset($banned_ips[$ip]);
					$update = true;
				}
			}
		}

		if ($update) {
			self::update_banned_ips($banned_ips);
			if (class_exists(__NAMESPACE__ . '\wf_sn_el_modules')) {
				wf_sn_el_modules::log_event('security_ninja', 'pruned_banned_ips', 'Pruned Firewall local banned IPs.', '');
			}
		} else {
			if (class_exists(__NAMESPACE__ . '\wf_sn_el_modules')) {
				wf_sn_el_modules::log_event('security_ninja', 'pruned_banned_ips', 'No update.', '');
			}
		}
	}





	/**
	 * log failed login
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $username
	 * @return  void
	 */
	public static function failed_login($username)
	{
		global $wpdb;

		// To prevent double logging
		$logged = false;

		$my_banned_ips = self::get_banned_ips();

		$current_user_ip = self::get_user_ip();
		$ua_string       = '';
		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
		}

		// @todo replace ip refs with $current_user - used throughout plugin
		$date = date('Y-m-d H:i:m', current_time('timestamp'));
		$query = $wpdb->prepare(
			'SELECT COUNT(id) FROM ' . $wpdb->prefix . 'wf_sn_el WHERE ip = %s AND action = %s AND timestamp >= DATE_SUB(%s, INTERVAL %s MINUTE)',
			$current_user_ip,
			'wp_login_failed',
			$date,
			self::$options['max_login_attempts_time']
		);
		$login_attempts = intval($wpdb->get_var($query));

		if ($login_attempts >= intval(self::$options['max_login_attempts']) && !isset($my_banned_ips[$current_user_ip])) {

			$my_banned_ips[$current_user_ip] = current_time('timestamp') + self::$options['bruteforce_ban_time'] * 60;

			self::update_banned_ips($my_banned_ips);

			// Translators: 1: User IP address, 2: Number of login attempts, 3: Time in minutes
			$block_details = sprintf(
				__('%s blocked due to multiple failed login attempts. %d in %d min.', 'security-ninja'),
				esc_html($current_user_ip),
				intval($login_attempts),
				intval(self::$options['max_login_attempts_time'])
			);

			self::log_visitor(
				array(
					'ip'          => $current_user_ip,
					'user_agent'  => $ua_string,
					'banned'      => 1,
					'username' => sanitize_key($username),
					'description' => $block_details,
				)
			);

			// Logging to event module (if enabled)
			wf_sn_el_modules::log_event('security_ninja', 'firewall_ip_banned', $current_user_ip . ' blocked due to multiple failed login attempts. ' . $login_attempts, '');

			wp_clear_auth_cookie();
			update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
			$logged = true; // We have logged this event

			$submitargs = array(
				'ban_type'   => 'failed_login',
				'ban_reason' => 'multiple_failed_logins',
			);
			self::network_reportip($current_user_ip, $submitargs);

			self::kill_request($current_user_ip);
		} else {
			// Increase count of failed logins for IP
			$login_attempts++;
		}

		update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);

		$ban_reason = self::is_banned_ip();

		if ($ban_reason && !$logged) {
			wf_sn_el_modules::log_event('security_ninja', 'login_denied_banned_IP', $current_user_ip . ' blocked from logging in.', '');

			self::log_visitor(
				array(
					'ip'          => esc_attr($current_user_ip),
					'user_agent'  => esc_attr($ua_string),
					'banned'      => 1,
					'ban_reason'  => 'login_denied_banned_ip',
					'description' => esc_attr($current_user_ip) . ' blocked from logging in. ' . esc_attr($ban_reason),
					'username' => esc_attr($username),
				)
			);

			$logged = true; // We have logged this event
			wp_clear_auth_cookie();
			self::kill_request();
		}

		if (!$logged) {
			self::log_visitor(
				array(
					'ip'          => $current_user_ip,
					'user_agent'  => $ua_string,
					'banned'      => 0,
					'description' => 'Failed login attempt!- ' . $login_attempts,
					'action'      => 'wp_login_failed',
					'username' => sanitize_key($username),
				)
			);
		}
	}




	/**
	 * ipCIDRMatch.
	 *
	 * @author  Unknown
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0   Saturday, August 20th, 2022.    
	 * @version v1.0.1   Tuesday, August 27th, 2024.
	 * @access  public static
	 * @param   string $ip   The IP address to check.
	 * @param   string $cidr The CIDR range to check against.
	 * @return  bool         True if the IP matches the CIDR range, false otherwise.
	 */
	public static function ipCIDRMatch($ip, $cidr)
	{
		$c = explode('/', $cidr);
		$subnet = isset($c[0]) ? $c[0] : NULL;
		$mask = isset($c[1]) ? (int)$c[1] : NULL;

		if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			$ipVersion = 'v4';
		} elseif (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			$ipVersion = 'v6';
		} else {
			return false;
		}

		switch ($ipVersion) {
			case 'v4':
				if ($mask === NULL || $mask < 0 || $mask > 32) {
					return false;
				}
				return self::IPv4Match($ip, $subnet, $mask);
			case 'v6':
				if ($mask === NULL || $mask < 0 || $mask > 128) {
					return false;
				}
				return self::IPv6Match($ip, $subnet, $mask);
			default:
				return false;
		}
	}



	/**
	 * inspired by: http://stackoverflow.com/questions/7951061/matching-ipv6-address-to-a-cidr-subnet
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, May 14th, 2024.
	 * @access	private static
	 * @param	mixed	$subnetMask	
	 * @return	mixed
	 */
	private static function IPv6MaskToByteArray($subnetMask)
	{
		$addr = str_repeat("f", $subnetMask / 4);
		switch ($subnetMask % 4) {
			case 0:
				break;
			case 1:
				$addr .= "8";
				break;
			case 2:
				$addr .= "c";
				break;
			case 3:
				$addr .= "e";
				break;
		}
		$addr = str_pad($addr, 32, '0');
		$addr = pack("H*", $addr);

		return $addr;
	}

	/**
	 * inspired by: http://stackoverflow.com/questions/7951061/matching-ipv6-address-to-a-cidr-subnet
	 *
	 * @author	Unknown
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, May 14th, 2024.	
	 * @version	v1.0.1	Tuesday, August 27th, 2024.
	 * @access	private static
	 * @param	mixed	$address      	
	 * @param	mixed	$subnetAddress	
	 * @param	mixed	$subnetMask   	
	 * @return	mixed
	 */
	private static function IPv6Match($address, $subnetAddress, $subnetMask)
	{
		// Validate the subnet address
		if (
			!filter_var($subnetAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
			$subnetMask === NULL ||
			$subnetMask === "" ||
			$subnetMask < 0 ||
			$subnetMask > 128
		) {
			return false;
		}

		// Convert addresses to binary form
		$subnet = inet_pton($subnetAddress);
		$addr = inet_pton($address);

		// Ensure that both addresses were converted correctly
		if ($subnet === false || $addr === false) {
			return false;
		}

		// Convert the subnet mask to a binary string
		$binMask = self::IPv6MaskToByteArray($subnetMask);

		// Perform the bitwise AND operation and compare
		return ($addr & $binMask) === $subnet;
	}


	/**
	 * inspired by: http://stackoverflow.com/questions/594112/matching-an-ip-to-a-cidr-mask-in-php5
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, May 14th, 2024.
	 * @access	private static
	 * @param	mixed	$address      	
	 * @param	mixed	$subnetAddress	
	 * @param	mixed	$subnetMask   	
	 * @return	mixed
	 */
	private static function IPv4Match($address, $subnetAddress, $subnetMask)
	{
		if (!filter_var($subnetAddress, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || $subnetMask === NULL || $subnetMask === "" || $subnetMask < 0 || $subnetMask > 32) {
			return false;
		}

		$address = ip2long($address);
		$subnetAddress = ip2long($subnetAddress);
		$mask = -1 << (32 - $subnetMask);
		$subnetAddress &= $mask; # nb: in case the supplied subnet wasn't correctly aligned
		return ($address & $mask) == $subnetAddress;
	}



	/**
	 * Checks if an IP is in array
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $needle
	 * @param   mixed   $haystack
	 * @return  void
	 */
	public static function IP_in_array($needle, $haystack)
	{

		// Check if haystack is array and makes sure it is trimmed from apostrophes
		if (is_array($haystack)) {
			$ip_arr = array();
			foreach ($haystack as $key => $item) {
				$ip_arr[] = trim($item, "'");
			}
		}

		if (in_array($needle, $ip_arr)) {
			return true;
		}

		foreach ($haystack as $key => $item) {
			if ($item === $needle) {
				return true;
			}
		}
	}




	/**
	 * Checks a specific IP is banned or not
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Wednesday, June 9th, 2021.	
	 * @version	v1.0.2	Sunday, June 13th, 2021.	
	 * @version	v1.0.3	Monday, November 8th, 2021.	
	 * @version	v1.0.4	Wednesday, February 9th, 2022.
	 * @access	public static
	 * @param	boolean	$ip	(defaults to false)
	 * @return	boolean
	 */
	public static function is_banned_ip($ip = false)
	{

		if (!$ip) return false;

		// Checks if IP is set or try to get it - always use $current_user_ip from here
		if ($ip) {
			$current_user_ip = $ip;
		} else {
			$current_user_ip = self::get_user_ip();
		}

		$server_host = gethostname();
		$server_ip   = gethostbyname($server_host);

		// If server IP same as referring IP, continue
		if ($server_ip === $current_user_ip) {
			return false;
		}

		// Checks if IP is from a whitelisted external service - note, the function checks if settings are enabled before allowing
		if (self::is_whitelisted_service($current_user_ip)) {
			return false;
		}


		// Checks if the IP is in local whitelist
		$local_whitelist = self::$options['whitelist'];

		// Ensure $local_whitelist is an array
		if (!is_array($local_whitelist)) {
			$local_whitelist = array();
		}

		foreach ($local_whitelist as $lw) {
			if (trim($lw) === $ip) {
				return false;
			}
		}

		// Checks if the IP is whitelisted from whitelist arr
		if (in_array($ip, $local_whitelist, true)) {
			return false;
		}
		// Check if IP is in blacklist. P.s. could use in_array() but had trouble with spaces ... perhaps trim first.. hmm...
		$blacklist = self::$options['blacklist'];
		if (is_array($blacklist)) {
			foreach ($blacklist as $bl) {
				if (trim($bl) === $ip) {
					return 'IP is in local blacklist.';
				}
				if (self::ipCIDRMatch($ip, $bl)) {
					return 'IP is in local blacklist mask - ' . $bl;
				}
			}
		}

		$my_banned_list = self::get_banned_ips();


		// IPs are currently stored in the options table
		$ips = get_option('wf_sn_cf_ips');
		if (!is_array($ips)) {
			$ips = array(
				'ips' => array(),
				'subnets' => array()
			);
		}
		$banned_ips = self::get_banned_ips();

		if (is_array(self::$options['whitelist']) && self::is_whitelisted($current_user_ip, self::$options['whitelist'])) {
			return false;
		} elseif (array_key_exists($current_user_ip, $banned_ips)) {
			return 'Local blacklist.';
		} elseif (('1' === self::$options['usecloud']) && (self::IP_in_array($current_user_ip, $ips['ips']))) {
			return 'IP in cloud blacklist.';
		} else {
			$nework_array = explode('.', $current_user_ip, 2);
			// is cloud firewall enabled?
			if ('1' === self::$options['usecloud']) {
				if (array_key_exists($nework_array[0], $ips['subnets'])) {
					foreach ($ips['subnets'][$nework_array[0]] as $subnet) {
						// trim apostrophes
						$subnet = trim($subnet, "'");
						if (self::ipCIDRMatch($current_user_ip, $subnet)) {
							return 'IP in cloud blacklist range.';
						}
					}
				}
			}
		}
		// checks for visitor ban
		if ($blocked_country = self::is_banned_country($current_user_ip)) {
			return 'Country is blocked ' . $blocked_country;
		}

		// Checks if included in SecNin Global Block network
		global $wpdb;
		$table_name = $wpdb->prefix . WF_SN_CF_BLOCKED_IPS_TABLE;
		$answer     = $wpdb->get_var(
			$wpdb->prepare("SELECT tid FROM `{$table_name}` WHERE ip = %s", $ip)
		);
		if ($answer) {
			return 'SecNin Global Block network.';
		}
		return false;
	}


	/**
	 * Checks if an IP is whitelisted
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $ip
	 * @param   mixed   $whitelist
	 * @return  boolean
	 */
	public static function is_whitelisted($ip, $whitelist)
	{
		foreach ($whitelist as $key => $wip) {
			if (strpos($wip, '/') !== false) {
				if (self::ipCIDRMatch($ip, $wip)) {
					return true;
				}
			} else {
				if ($ip === $wip) {
					return true;
				}
			}
		}
		return false;
	}






	/**
	 * Update cloud firewall blocked IPs and update server IP to whitelist
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function update_cloud_ips()
	{

		if (secnin_fs()->is__premium_only()) {
			if (secnin_fs()->can_use_premium_code()) {


				$server_host = gethostname();
				$server_ip   = gethostbyname($server_host);
				// $options 		 = self::$options;

				$local_whitelist = (isset(self::$options) && is_array(self::$options) && isset(self::$options['whitelist']) && is_array(self::$options['whitelist'])) ? self::$options['whitelist'] : array();

				if (!in_array($server_ip, $local_whitelist)) {
					self::$options['whitelist'][] = trim($server_ip);
					update_option(WF_SN_CF_OPTIONS_KEY, self::$options, false);
					wf_sn_el_modules::log_event('security_ninja', 'unblocked_ip', 'Added server IP to whitelist ' . $server_ip);
				}

				$firehol = 'https://raw.githubusercontent.com/firehol/blocklist-ipsets/master/firehol_level1.netset';

				$response = wp_remote_get($firehol);

				if (!is_wp_error($response)) {
					$body             = wp_remote_retrieve_body($response);
					$sn_firewall_data = array(
						'ips'     => array(),
						'subnets' => array(),
					);

					$lines = explode(PHP_EOL, $body);

					foreach ($lines as $line_num => $line) {

						if (strpos($line, '#') !== false) {
							// Skip comments
							continue;
						} elseif (strpos($line, '/') !== false) {

							$nework_array = explode('.', trim($line), 2);

							if (!array_key_exists($nework_array[0], $sn_firewall_data['subnets'])) {

								$sn_firewall_data['subnets'][$nework_array[0]] = array();
							}
							$sn_firewall_data['subnets'][$nework_array[0]][] = trim($line);
						} else {
							$sn_firewall_data['ips'][] = trim($line);
						}
					}
					$sn_firewall_data['timestamp'] = time();
					update_option('wf_sn_cf_ips', $sn_firewall_data, false);
				} else {
					wf_sn_el_modules::log_event('security_ninja', 'geolocation_download', 'Unable to download GeoIP Database: ' . $response->get_error_message(), '');
				}
			} // if ( secnin_fs()->can_use_premium_code() )
		} // if ( secnin_fs()->is__premium_only() )



	}







	/**
	 * Register module settings.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function register_settings()
	{
		register_setting(WF_SN_CF_OPTIONS_KEY, 'wf_sn_cf', array(__NAMESPACE__ . '\\wf_sn_cf', 'sanitize_settings'));
	}


	/**
	 * Centralized way to get users IP - @todo - replace med opdateret version
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Tuesday, May 14th, 2024.
	 * @access	public static
	 * @return	boolean
	 * @todo	- replace med opdateret version
	 */
	public static function get_user_ip()
	{

		// Check if we have already cached the IP
		if (self::$cached_ip !== null) {
			return self::$cached_ip;
		}


		$headers = array(
			'HTTP_CF_CONNECTING_IP', // CloudFlare
			'HTTP_X_FORWARDED_FOR', // May contain a comma+space separated list of IP addresses
			'HTTP_X_REAL_IP',
			'HTTP_CLIENT_IP',
			'HTTP_X_FORWARDED',
			'HTTP_X_CLUSTER_CLIENT_IP',
			'HTTP_X_COMING_FROM',
			'HTTP_PROXY_CONNECTION',
			'HTTP_FORWARDED_FOR',
			'HTTP_FORWARDED',
			'HTTP_COMING_FROM',
			'HTTP_VIA',
			'REMOTE_ADDR'
		);

		foreach ($headers as $header) {
			if (!empty($_SERVER[$header])) {
				foreach (explode(',', $_SERVER[$header]) as $ip) {
					$ip = trim($ip);
					// Check if IP is valid, including private/reserved ranges for local/dev environments
					if (filter_var($ip, FILTER_VALIDATE_IP)) {
						// Cache the result
						self::$cached_ip = $ip;
						return $ip;
					}
				}
			}
		}

		// If no valid IP is found, cache and return false
		self::$cached_ip = false;
		return false;
	}






	/**
	 * Function runs if WooCommerce installed and you add the account shortcode [woocommerce_my_account] somewhere on the website.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function process_woocommerce_login_form_start()
	{
		$protect_login_form = self::$options['protect_login_form'];
		if (!$protect_login_form) {
			return;
		}

		$show_message = apply_filters('secnin_show_woocommerce_login_message', true);

		if (! $show_message) {
			return;
		}

		$msg = '<p class="message">' . self::$options['login_msg'] . '</p>';
		echo $msg;
	}



	/**
	 * Process login errors and optionally hide detailed error messages.
	 *
	 * @since   v0.0.1
	 * @version v1.1.0
	 *
	 * @param string|\WP_Error $error The error message or WP_Error object.
	 * @return string The processed error message.
	 */
	public static function process_login_errors($error): string
	{
		if (!self::$options['hide_login_errors']) {
			return $error;
		}

		$current_user_ip = self::get_user_ip();

		$error_message = $error instanceof \WP_Error ? $error->get_error_message() : $error;

		$message = sprintf(
			/* translators: 1: IP address, 2: Error message */
			__('%1$s login error. Message: %2$s.', 'security-ninja'),
			esc_html($current_user_ip),
			wp_strip_all_tags($error_message)
		);

		wf_sn_el_modules::log_event('security_ninja', 'login_error', $message);

		$login_error_msg = self::$options['login_error_msg'] ?: __('Something went wrong', 'security-ninja');

		return sprintf(
			'<strong>%s</strong>: %s',
			esc_html__('Error', 'security-ninja'),
			wp_kses($login_error_msg, ['p' => [], 'br' => []])
		);
	}



	/**
	 * Adds warning message above login form
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.1  Tuesday, March 5th, 2024.
	 * @access  public static
	 * @param   string $msg The existing message content.
	 * @return  string Modified message content.
	 */
	public static function login_message($msg)
	{
		if (!self::is_active() || empty(self::$options['protect_login_form']) || empty(self::$options['login_msg'])) {
			return $msg;
		}

		$action = isset($_GET['action']) ? sanitize_key($_GET['action']) : '';
		if (!in_array($action, array('register', 'lostpassword'), true)) {
			$custom_msg = '<p class="message">' . esc_html(self::$options['login_msg']) . '</p>';
			$msg = $custom_msg . $msg;
		}

		return $msg;
	}



	/**
	 * isValidCIDR.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Tuesday, August 27th, 2024.
	 * @access	private static
	 * @param	mixed	$cidr	
	 * @return	boolean
	 */
	private static function isValidCIDR($cidr)
	{
		$parts = explode('/', $cidr);
		if (count($parts) !== 2) {
			return false;
		}

		$subnet = $parts[0];
		$mask = (int)$parts[1];

		if (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
			return $mask >= 0 && $mask <= 32;
		} elseif (filter_var($subnet, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
			return $mask >= 0 && $mask <= 128;
		}

		return false;
	}


	/**
	 * sanitize settings on save
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, December 21st, 2020.	
	 * @version	v1.0.1	Friday, May 31st, 2024.
	 * @access	public static
	 * @param	mixed	$values	
	 * @return	mixed
	 */
	public static function sanitize_settings($values)
	{
		$defaults = array(
			'active'                      => 0,
			'globalbannetwork'            => true,
			'global'                      => true,
			'filterqueries'               => true,
			'trackvisits'                 => true,
			'trackvisits_howlong'         => '7',
			'usecloud'                    => true,
			'protect_login_form'          => true,
			'hide_login_errors'           => true,
			'blocked_countries'           => array(),
			'blacklist'                   => array(),
			'whitelist'                   => array(self::get_user_ip()),
			'whitelist_managewp'          => true,
			'whitelist_wprocket'          => false,
			'whitelist_uptimia'           => false,
			'max_login_attempts'          => 5,
			'max_login_attempts_time'     => 5,
			'bruteforce_ban_time'         => 120,
			'login_msg'                   => __('Warning: Multiple failed login attempts will get you locked out temporarily.', 'security-ninja'),
			'login_error_msg'             => __('Something went wrong', 'security-ninja'),
			'message'                     => __('You are not allowed to visit this website.', 'security-ninja'),
			'redirect_url'                => '',
			'blockadminlogin'             => 0,
			'change_login_url'            => '0',
			'new_login_url'               => 'my-login',
			'unblock_url'                 => md5(time()),
			'2fa_required_roles'          => array(),
			'2fa_methods'                 => array(),
			'2fa_enabled'                 => false,
			'2fa_enabled_timestamp'       => '',
			'2fa_backup_codes_enabled'    => false,
			'2fa_grace_period'            => 14,
			'2fa_intro'                   => '',
			'2fa_enter_code'              => '',
		);

		$current_options = self::get_options();
		$old_2fa_status = $current_options['2fa_enabled'];

		// Ensure all boolean settings are explicitly checked
		$boolean_keys = array(
			'active',
			'globalbannetwork',
			'global',
			'filterqueries',
			'trackvisits',
			'usecloud',
			'protect_login_form',
			'hide_login_errors',
			'whitelist_managewp',
			'whitelist_wprocket',
			'whitelist_uptimia',
			'2fa_enabled',
			'2fa_backup_codes_enabled'
		);

		foreach ($boolean_keys as $key) {
			if (!isset($values[$key])) {
				$values[$key] = false;
			}
		}

		foreach ($values as $key => $value) {
			if (array_key_exists($key, $defaults)) {
				switch ($key) {
					case '2fa_required_roles':
					case '2fa_methods':
					case 'blocked_countries':
						if (is_array($value)) {
							$values[$key] = array_map('sanitize_text_field', $value);
						} else {
							$values[$key] = sanitize_text_field($value);
						}
						break;
					case 'blacklist':
					case 'whitelist':
						if (!is_array($value) && is_string($value)) {
							// Split the string into an array by line breaks
							$ips = explode("\n", $value);
							// Trim whitespace, sanitize each IP address or CIDR, and ensure uniqueness
							$values[$key] = array_unique(array_filter(array_map(function ($ip) {
								$sanitized_ip = sanitize_text_field(trim($ip));
								if (
									filter_var($sanitized_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
									filter_var($sanitized_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)
								) {
									return $sanitized_ip;
								}
								if (strpos($sanitized_ip, '/') !== false && self::isValidCIDR($sanitized_ip)) {
									return $sanitized_ip;
								}

								// Return null if invalid to filter it out
								return null;
							}, $ips), function ($ip) {
								return !is_null($ip);
							}));
						} elseif (is_array($value)) {
							// If it's already an array, sanitize and validate each entry
							$values[$key] = array_unique(array_filter(array_map(function ($ip) {
								$sanitized_ip = sanitize_text_field(trim($ip));

								// Validate IP or CIDR using your existing function
								if (
									filter_var($sanitized_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ||
									filter_var($sanitized_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) ||
									(strpos($sanitized_ip, '/') !== false && self::isValidCIDR($sanitized_ip))
								) {
									return $sanitized_ip;
								}
								return null;
							}, $value), function ($ip) {
								return !is_null($ip);
							}));
						} else {
							// If the value is neither a string nor an array, set an empty array
							$values[$key] = [];
						}
						break;
					case '2fa_enabled':
					case '2fa_backup_codes_enabled':
					case 'globalbannetwork':
					case 'global':
					case 'filterqueries':
					case 'trackvisits':
					case 'usecloud':
					case 'protect_login_form':
					case 'hide_login_errors':
					case 'whitelist_managewp':
					case 'whitelist_wprocket':
					case 'whitelist_uptimia':
						$values[$key] = (bool)$value;
						break;
					case '2fa_grace_period':
					case 'max_login_attempts':
					case 'max_login_attempts_time':
					case 'bruteforce_ban_time':
					case 'blockadminlogin':
					case 'change_login_url':
					case 'trackvisits_howlong': // number of days to track visits
						$values[$key] = intval($value);
						break;
					case '2fa_intro':
					case '2fa_enter_code':
					case '2fa_enabled_timestamp':
					case 'login_msg':
					case 'login_error_msg':
					case 'message':
					case 'new_login_url':
						$values[$key] = sanitize_text_field($value);
						break;
					case 'redirect_url':
						$values[$key] = esc_url_raw($value);
						break;
					default:
						$values[$key] = sanitize_text_field($value);
						break;
				}
			} else {
				unset($values[$key]);
			}
		}

		// Check for user IP whitelisting if the firewall is active
		$user_ip = self::get_user_ip();
		if (isset($values['active']) && $values['active'] && !in_array($user_ip, $values['whitelist'], true)) {
			$values['whitelist'][] = $user_ip;
		}

		// Check if 'active' is set to false and deactivate 2FA if so
if (isset($values['active']) && !$values['active'] && $values['2fa_enabled']) {
	$values['2fa_enabled'] = false; // Deactivate 2FA
	$values['2fa_enabled_timestamp'] = ''; // Optionally reset the timestamp
}


		$current_twofa_status = $current_options['2fa_enabled'];
		// If the 2fa_enabled is set to true and it used to be false, set the timestamp '2fa_enabled_timestamp' to the current time
		$old_2fa_status = $current_twofa_status;


		// Check if 2FA was just enabled
		if (isset($values['2fa_enabled']) && $values['2fa_enabled'] && !$old_2fa_status) {
			// Get all users
			$values['2fa_enabled_timestamp'] = current_time('timestamp');
			$users = get_users();
			// Loop through all users
			foreach ($users as $user) {
				// Cleaning up 2FA metadata for all users
				delete_user_meta($user->ID, 'secnin_2fa_secret');
				delete_user_meta($user->ID, 'secnin_2fa_setup_complete');
				delete_user_meta($user->ID, 'secnin_2fa_code_validated');
			}
		}











		// Ensure a non-empty login URL if the change login URL feature is active
		if (class_exists('SecNin_Rename_WP_Login') && $values['change_login_url'] && ('' === $values['new_login_url'])) {
			$values['new_login_url'] = \WPSecurityNinja\Plugin\SecNin_Rename_WP_Login::$default_login_url;
		}

		// Merge sanitized values with defaults to ensure all settings are complete and valid
		$merged = array_merge($defaults, $values);
		return $merged;
	}








	/**
	 * return_new_login_slug.
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Thursday, October 21st, 2021.
	 * @access  private static
	 * @param   mixed   $newslug
	 * @return  void
	 */
	private static function return_new_login_slug($newslug)
	{
		$forbidden_slugs = SecNin_Rename_WP_Login::forbidden_slugs();

		if (!in_array($newslug, $forbidden_slugs, true)) {
			return sanitize_title($newslug);
		}
	}




	/**
	 * add new tab
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @param   mixed   $tabs
	 * @return  mixed
	 */
	public static function sn_tabs($tabs)
	{
		$core_tab = array(
			'id'       => 'sn_cf',
			'class'    => '',
			'label'    => 'Firewall',
			'callback' => array(__NAMESPACE__ . '\\wf_sn_cf', 'do_page'),
		);
		$done     = 0;

		for ($i = 0; $i < sizeof($tabs); $i++) {
			if ($tabs[$i]['id'] == 'sn_cf') {
				$tabs[$i] = $core_tab;
				$done       = 1;
				break;
			}
		}

		if (!$done) {
			$tabs[] = $core_tab;
		}

		return $tabs;
	}




	/**
	 * get_table_prefix.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_table_prefix()
	{
		global $wpdb;

		if (is_multisite() && !defined('MULTISITE')) {
			$table_prefix = $wpdb->base_prefix;
		} else {
			$table_prefix = $wpdb->get_blog_prefix(0);
		}

		return $table_prefix;
	} // get_table_prefix






	/**
	 * add custom message to overlay
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function overlay_content()
	{
		echo '<div id="sn-cloud-firewall" style="display: none; text-align:center;">';
		echo '<h2 style="font-weight: bold;">' . __('Important! Please READ!', 'security-ninja') . '</h2>';
		echo '<p>' . __('In the unlikely situation that your IP gets banned, you will not be able to login or access the site. In that case you need the secret access link.', 'security-ninja') . '</p>';

		echo '<p>' . __('It whitelists your IP and enables access. Please store the link in a safe place or use the form below to get it sent to your email address.', 'security-ninja') . '</p>';

		echo '<p><code>' . self::get_unblock_url() . '</code></p>';
		echo '<div id="sn-firewall-status">' . __('Enabling firewall, please wait', 'security-ninja') . '</p><p class="spinner is-active"></div>';
		echo '<p>' . __('Enter your email below to receive the secret access link in case you get locked out', 'security-ninja') . '</p>';
		echo '<input style="width: 250px;" type="text" id="sn-ublock-email" name="sn-ublock-email" value="' . get_option('admin_email') . '" placeholder="john@example.com"><br />
																									<p id="sn-unblock-message"></p>';

?>
		<input type="button" value="<?php esc_html_e('Send secret access link', 'security-ninja'); ?>" id="sn-send-unlock-code" class="input-button button button-secondary" />
	<?php
		echo '<p><br><input type="button" value="Close (3)" id="sn-close-firewall" class="input-button button-primary" /></p>';

		echo '</div>';
	} // overlay_content


	/**
	 * Checks if the firewall module is active
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function is_active()
	{
		return (bool) self::$options['active'];
	}





	/**
	 * Returns list of blocked country codes for use with GEOIP.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_blocked_countries()
	{
		$blocked_countries = self::$options['blocked_countries'];
		if (!$blocked_countries) {
			return array();
		}
		if (is_array($blocked_countries)) {
			$bclist = array();
			foreach ($blocked_countries as $key => $ba) {
				$bclist[] = $ba;
			}
			return $bclist;
		}
		return array();
	}




	/**
	 * get_unblock_url.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_unblock_url()
	{
		$my_options = self::get_options();
		$unblock_url = '';
		if (isset($my_options['unblock_url']) && !empty($my_options['unblock_url'])) {
			$unblock_url = $my_options['unblock_url'];
		}

		// check if already set
		if (!$unblock_url) {
			$my_options['unblock_url'] = md5(time());
			update_option(WF_SN_CF_OPTIONS_KEY, $my_options, false);
		}

		$outurl = add_query_arg(
			array(
				'snf' => $my_options['unblock_url'],
			),
			get_site_url()
		);

		return $outurl;
	}





	/**
	 * Return bad IPs from the central API
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Thursday, February 11th, 2021.
	 * @access  private static
	 * @return  boolean
	 */
	private static function get_network_listips()
	{
		// Check if the feature is enabled
		if (!self::$options['globalbannetwork']) {
			wf_sn_el_modules::log_event('security_ninja', 'get_network_listips', 'Global network feature is disabled');
			return false;
		}

		$license_id            = secnin_fs()->_get_license()->id;
		$install_id            = secnin_fs()->get_site()->id;
		$site_private_key      = secnin_fs()->get_site()->secret_key;
		$nonce                 = date('Y-m-d');
		$pk_hash               = hash('sha512', $site_private_key . '|' . $nonce);
		$authentication_string = base64_encode($pk_hash . '|' . $nonce);

		$url = self::$central_api_url . 'listips/';

		$response = wp_remote_get( // Cannot use wp_safe_remote_get because we need to set the header
			$url,
			array(
				'headers'   => array(
					'Authorization' => $authentication_string,
				),
				'body'      => array(
					'install_id' => $install_id,
					'license_id' => $license_id,
				),
				'blocking'  => true,
				'timeout'   => 15,
				'sslverify' => false, // @todo
			)
		);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			wf_sn_el_modules::log_event('security_ninja', 'update_blocked_ips', 'Error getting IPs from network: "' . esc_html($error_message) . '"');
			return false;
		} else {
			$body    = wp_remote_retrieve_body($response);
			$decoded = json_decode($body);
			$newips = 0;
			if (is_object($decoded) && isset($decoded->ips)) {
				$newips  = count($decoded->ips);
				wf_sn_el_modules::log_event('security_ninja', 'update_blocked_ips', sprintf(esc_html__('Added/updated %1$s IPs from the blocklist.', 'security-ninja'), $newips), '');
			}

			return $body;
		}

		return false;
	}





	/**
	 * network_reportip.
	 *
	 * @author	Lars Koudal
	 * @since	v5.114
	 * @version	v1.0.0	Thursday, January 28th, 2021.	
	 * @version	v1.0.1	Friday, March 3rd, 2023.
	 * @access	private static
	 * @param	mixed	$ip  	
	 * @param	mixed	$args	Default: array()
	 * @return	boolean
	 */
	private static function network_reportip($ip, $args = array())
	{

		if (!self::$options['globalbannetwork']) {
			return false;
		}
		if (!$ip) {
			return false;
		}
		if ('::1' === $ip) {
			return false;
		}
		if ('127.0.0.1' === $ip) {
			return false;
		}
		if (!filter_var($ip, FILTER_VALIDATE_IP)) {
			return false;
		}

		$ban_type   = '';
		$ban_reason = '';

		if (isset($args['ban_reason'])) {
			$ban_reason = $args['ban_reason'];
		}
		if (isset($args['ban_type'])) {
			$ban_type = $args['ban_type'];
		}

		$license_id = secnin_fs()->_get_license()->id;
		$install_id = secnin_fs()->get_site()->id;
		$site_private_key = secnin_fs()->get_site()->secret_key;
		$nonce = current_time('Y-m-d', true);
		$pk_hash = hash('sha512', $site_private_key . '|' . $nonce);
		$authentication_string = base64_encode($pk_hash . '|' . $nonce);

		$url = self::$central_api_url . 'reportip/';

		$response = wp_remote_post(
			$url,
			array(
				'headers'  => array(
					'Authorization' => $authentication_string,
				),
				'body'     => array(
					'ban_reason' => esc_attr($ban_reason),
					'ban_ip'     => $ip,
					'install_id' => $install_id,
					'license_id' => $license_id,
					'ban_type'   => esc_attr($ban_type),
					'ver'        => esc_attr(Wf_Sn::$version),
				),
				'blocking' => false,
				'timeout'  => 15,
			)
		);

		if (is_wp_error($response)) {
			$error_message = $response->get_error_message();
			wf_sn_el_modules::log_event('security_ninja', 'ip_network_submit_error', 'Error submitting IP to network ' . $error_message);
			return false;
		} else {
			$body = wp_remote_retrieve_body($response);
			wf_sn_el_modules::log_event('security_ninja', 'ip_network_submit_success', 'Blocked IP submitted to network.');
		}
		return true;
	}





	/**
	 * display results
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, December 21st, 2020.
	 * @access  public static
	 * @return  void
	 */
	public static function do_page()
	{
		global $wpdb;

		$ips = get_option('wf_sn_cf_ips');


		if ($ips && !array_key_exists('total', $ips)) {
			$total_ips = 0;
			if (isset($ips['subnets'])) {
				foreach ($ips['subnets'] as $prefix => $subnet) {
					foreach ($subnet as $sub) {
						$mask       = explode('/', str_replace('\'', '', $sub));
						$total_ips += pow(2, 32 - $mask[1]) - 2;
					}
				}
			}

			$total_ips = isset($total_ips) ? intval($total_ips) : 0;

			$banned_ips = self::get_banned_ips();
			$banned_ips_count = is_array($banned_ips) ? count($banned_ips) : 0;

			$ips['total'] = $total_ips + count($ips['ips']) + $banned_ips_count;
			// update_option('wf_sn_cf_ips', $ips, false);
		}


	?>
		<div class="submit-test-container card">
			<h3><?php esc_html_e('Firewall - Protect your website', 'security-ninja'); ?></h3>
			<?php
			global $secnin_fs;

			// Checks if the inline links help is enabled
			if (($secnin_fs->is_registered()) && (!$secnin_fs->is_pending_activation()) && (!wf_sn_wl::is_active())) {
			}

			if (self::is_active()) {
			} else {
				echo '<input type="button" value="' . __('Enable Firewall', 'security-ninja') . '" id="sn-enable-firewall-overlay" class="button button-primary button-hero"/>';
			}

			if (!self::is_active()) {
			?>

				<h3><?php esc_html_e('Block attacks to your website', 'security-ninja'); ?></h3>
				<ul class="security-test-list">
					<li>
						<?php esc_html_e('Protect against 600+ million known bad IPs - The list is automatically updated several times a day.', 'security-ninja'); ?>
					</li>
					<li>
						<?php esc_html_e('Protect against dangerous requests - SQL injections and other malicious page requests.', 'security-ninja'); ?>
					</li>
					<li>
						<?php esc_html_e('Country Blocking - Prevent visitors from specific countries to visit your website.', 'security-ninja'); ?>
					</li>
					<li>
						<?php esc_html_e('Redirect blocked visitors. Prevent visitors from even viewing your website.', 'security-ninja'); ?>
					</li>
					<li><?php esc_html_e('Login Form Protection. Prevent multiple repeated failed logins.', 'security-ninja'); ?>
					</li>
					<li><?php esc_html_e('Rename login URLs and confuse robots.', 'security-ninja'); ?>
					</li>
				</ul>

				<?php if (isset($ips) && isset($ips['total']) && isset($ips['timestamp'])) : ?>
					<p>
						<?php
						printf(
							esc_html__(
								'%1$s bad IPs in list. Last updated %2$s (%3$s) ',
								'security-ninja'
							),
							'<strong>' . number_format_i18n($ips['total']) . '</strong>',
							date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ips['timestamp']),
							human_time_diff($ips['timestamp'], current_time('timestamp')) . ' ' . __('ago', 'security-ninja')
						);
						?>
					</p>
				<?php endif; ?>


		</div>
	<?php
			}
			$blocked_count = get_option('wf_sn_cf_blocked_count');
			if ($blocked_count) {
	?>
		<p>
			<?php
				printf(
					esc_html__('%s blocked visits so far.', 'security-ninja'),
					'<strong>' . number_format_i18n($blocked_count) . '</strong>'
				);
			?>
		</p>
	<?php
			}


			if ((int) self::$options['active'] === 1) {
				echo '<input type="button" value="' . __('Disable Firewall', 'security-ninja') . '" id="sn-disable-firewall" class="input-button button-secondary button-secondary" />';

				// echo '</div><div class="card">';

				echo '<h3>' . esc_html__('Secret Access URL', 'security-ninja') . '</h3>';
				echo '<code>' . self::get_unblock_url() . '</code>';
				echo '<p class="description">' . esc_html__('Do not share this URL! Use it only to access your website if your IP gets banned.', 'security-ninja') . '</p>';
			}

			if ((int) self::$options['active'] === 1) {
				echo '</div>';
				echo '<div class="card">';
				echo '<form action="options.php" id="sn-firewall-settings-form" method="post">';
				settings_fields('wf_sn_cf');
	?>

		<h3><?php esc_html_e('Firewall Settings', 'security-ninja'); ?></h3>
		<table class="form-table" id="sn-cf-options">
			<tbody>
				<?php


				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_global">' . __('Prevent Banned IPs from Accessing the Site', 'security-ninja') . '</label></th><td class="sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_global',
					array(
						'saved_value' => self::$options['global'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[global]',
					)
				);


				echo '<p class="description">' . __('If set to ON cloud and local firewall will prevent banned IPs from accessing the site all together.', 'security-ninja');

				echo '<p class="description">' . __('If set to OFF they will not be able to log in, but will be able to view the site.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_filterqueries">Block Suspicious Page Requests</label></th><td class="sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_filterqueries',
					array(
						'saved_value' => self::$options['filterqueries'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[filterqueries]',
					)

				);

				echo '<p class="description">' . __('Filter out page requests with suspicious query strings.', 'security-ninja');

				echo '</td></tr>';

				?>

				<tr valign="top">
					<th scope="row">
						<label for="wf_sn_cf_usecloud">Cloud Firewall</label>
					</th>
					<td class="sn-cf-options">
						<?php

						Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_usecloud',
							array(
								'saved_value' => self::$options['usecloud'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[usecloud]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('The list of 600+ million IPs can sometimes block traffic that should not be blocked. Use this to turn off this feature.', 'security-ninja'); ?></p>

						<?php
						if (self::is_active()) {
							if (isset($ips['timestamp'])) {
						?>
								<p>
									<?php
									printf(
										esc_html__('%s bad IPs blocked from logging in.', 'security-ninja'),
										'<strong>' . number_format_i18n($ips['total']) . '</strong>'
									);
									?>
								</p>
								<p><small>
										<?php
										printf(
											esc_html__('List last updated %1$s (%2$s)', 'security-ninja'),
											date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $ips['timestamp']),
											human_time_diff($ips['timestamp'], current_time('timestamp')) . ' ' . __('ago', 'security-ninja')
										);
										?>
									</small></p>
						<?php
							}
						}
						?>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						<label for="wf_sn_cf_globalbannetwork">Block IP Network</label>
					</th>
					<td class="sn-cf-options globalbannetwork">
						<?php

						Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_globalbannetwork',
							array(
								'saved_value' => self::$options['globalbannetwork'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[globalbannetwork]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('Participate in the global bad IP network. Submit hack attempts to central database.', 'security-ninja'); ?></p>
					</td>
				</tr>

				<?php

				echo '<tr valign="top"><th scope="row">' . __('Block visits from these countries', 'security-ninja') . '</th><td class="sn-cf-options">';

				$countrylist_uri = WF_SN_PLUGIN_DIR . 'modules/cloud-firewall/class-sn-geoip-countrylist.php';

				require_once $countrylist_uri;
				?>

				<select name="wf_sn_cf[blocked_countries][]" id="wf_sn_cf_blocked_countries" multiple="multiple" style="width:100%;" class="">
					<?php

					$blocked_countries = self::get_blocked_countries();
					if ($geoip_countrylist) {
						foreach ($geoip_countrylist as $key => $gc) {
							$selected = in_array($key, $blocked_countries, true) ? ' selected="selected" ' : '';
					?>
							<option value="<?php echo $key; ?>" <?php echo $selected; ?>><?php echo $gc . ' (' . $key . ')'; ?></option>
					<?php
						}
					}
					?>
				</select>
				<button id="select_all_countries" class="button button-secondary button-small alignright">Select All</button>
				<button id="select_no_countries" class="button button-secondary button-small alignright">Select None</button>
				<p class="description"><label for="wf_sn_cf_blocked_countries"><?php esc_html_e('Choose the countries you want to block.', 'security-ninja'); ?></label>
				</p>

				<p class="description">
					<?php esc_html_e('Be careful not to block USA if you depend on traffic from Google as Google crawls your website from USA.', 'security-ninja'); ?>
				</p>
				</tr>

				<?php

				echo '<tr valign="top">
					<th scope="row"><label for="wf_sn_cf_message">' . __('Message for blocked IPs', 'security-ninja') . '</label></th>
				<td class="sn-cf-options"><textarea id="wf_sn_cf_message" name="' . WF_SN_CF_OPTIONS_KEY . '[message]" rows="3" cols="50">' . self::$options['message'] . '</textarea><p class="description">' . __('Message shown to blocked visitors when they try to access the site or log in.', 'security-ninja') . '</p></td></tr>';
				?>
				<tr>
					<th></th>
					<td><?php esc_html_e('Or you can redirect blocked visitors', 'security-ninja'); ?>:</td>
				</tr>
				<?php

				echo '<tr valign="top"><th scope="row"><label for="wf-cf-redirect-url">' . __('Redirect blocked visitors', 'security-ninja') . '</label></th><td>';
				echo '<input type="text" placeholder="https://" class="regular-text" value="' . self::$options['redirect_url'] . '" id="wf-cf-redirect-url" name=' . WF_SN_CF_OPTIONS_KEY . '[redirect_url]>';

				echo '<p class="description">' . __('301 redirect blocked visitors to any URL. Leave empty to show message instead.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				?>
				<tr valign="top">
					<th colspan="2">
						<h3><?php esc_html_e('Visitor Logging', 'security-ninja'); ?></h3>
						<hr>
					</th>
				</tr>
				<?php

				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_trackvisits">' . __('Track visitors', 'security-ninja') . '</label></th><td class="sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_trackvisits',
					array(
						'saved_value' => self::$options['trackvisits'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[trackvisits]',
					)
				);

				echo '<p class="description">' . esc_html__('Track all visitors and page requests to the website.', 'security-ninja');

				echo '</td></tr>';

				$trackvisits_howlong = array();
				$trackvisits_howlong[] = array('val'   => 1, 'label' => esc_html__('1 day', 'security-ninja'));
				$trackvisits_howlong[] = array('val'   => 3, 'label' => esc_html__('3 days', 'security-ninja'));
				$trackvisits_howlong[] = array('val'   => 7, 'label' => esc_html__('7 days', 'security-ninja'));
				$trackvisits_howlong[] = array('val'   => 14, 'label' => esc_html__('14 days', 'security-ninja'));
				$trackvisits_howlong[] = array('val'   => 30, 'label' => esc_html__('30 days', 'security-ninja'));

				echo '<tr valign="top">';
				echo '<th scope="row"><label for="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '_trackvisits_howlong">' . esc_html__('Keep visitor logs for', 'security-ninja') . '</label></th>';
				echo '<td class="' . (!self::$options['trackvisits'] ? 'sn-disabled' : '') . ' sn-protect-track-visitors-subinput sn-cf-options"><select name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[trackvisits_howlong]" id="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '_trackvisits_howlong" class="regular-text">';
				Utils::create_select_options($trackvisits_howlong, self::$options['trackvisits_howlong']);
				echo '</select>';

				echo '<p class="description">' . esc_html__('How long to keep a log of visitors to your website.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				?>
				<tr valign="top">
					<th colspan="2">
						<h3><?php esc_html_e('Login Form Protection', 'security-ninja'); ?></h3>
						<hr>
					</th>
				</tr>

				<?php
				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_protect_login_form">' . esc_html__('Protect the login form', 'security-ninja') . '</label></th><td class="sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_protect_login_form',
					array(
						'saved_value' => self::$options['protect_login_form'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[protect_login_form]',
					)
				);

				echo '<p class="description">' . __('Protect the login form for repeated login attempts.', 'security-ninja');

				echo '</td></tr>';

				echo '<tr valign="top">';
				echo '<th scope="row"><label for="wf_sn_options_login_msg">' . esc_html__('Login notice', 'security-ninja') . '</label></th>';
				echo '<td class="' . (!self::$options['protect_login_form'] ? 'sn-disabled' : '') . ' sn-protect-login-form-subinput sn-cf-options">
				<textarea cols="50" rows="3" name="' . WF_SN_CF_OPTIONS_KEY . '[login_msg]" id="wf_sn_options_login_msg">' . self::$options['login_msg'] . '</textarea>';
				echo '<p class="description">' . esc_html__('Useful on a multi-user site to warn people what happens if they fail to login too many times.', 'security-ninja') . '</p>';
				echo '</td>';
				echo '</tr>';

				for ($i = 2; $i <= 10; $i++) {
					$max_login_attempts[] = array(
						'val'   => $i,
						'label' => $i,
					);
				}
				for ($i = 2; $i <= 15; $i++) {
					$max_login_attempts_time_s[] = array(
						'val'   => $i,
						'label' => $i,
					);
				}


				// Bruteforce Timeout Options
				$bruteforce_timeouts = array(
					array(
						'val'   => 2,
						'label' => __('2 minutes', 'security-ninja'),
					),
					array(
						'val'   => 10,
						'label' => __('10 minutes', 'security-ninja'),
					),
					array(
						'val'   => 20,
						'label' => __('20 minutes', 'security-ninja'),
					),
					array(
						'val'   => 30,
						'label' => __('30 minutes', 'security-ninja'),
					),
					array(
						'val'   => 60,
						'label' => __('1 hour', 'security-ninja'),
					),
					array(
						'val'   => 120,
						'label' => __('2 hours', 'security-ninja'),
					),
					array(
						'val'   => 1440,
						'label' => __('1 day', 'security-ninja'),
					),
					array(
						'val'   => 2880,
						'label' => __('2 days', 'security-ninja'),
					),
					array(
						'val'   => 10080,
						'label' => __('7 days', 'security-ninja'),
					),
					array(
						'val'   => 43200,
						'label' => __('1 month', 'security-ninja'),
					),
					array(
						'val'   => 525600,
						'label' => __('1 year', 'security-ninja'),
					),
					array(
						'val'   => 5256000,
						'label' => __('forever', 'security-ninja'),
					)
				);

				// Notification Settings
				$notification_settings = array(
					array(
						'val'   => 0,
						'label' => __('No', 'security-ninja'),
					),
					array(
						'val'   => 1,
						'label' => __('Yes', 'security-ninja'),
					)
				);

				echo '<tr valign="top">';
				echo '<th scope="row"><label for="wf_sn_options_mla">' . __('Auto-ban rules for failed login attempts', 'security-ninja') . '</label></th>';
				echo '<td class="' . (!self::$options['protect_login_form'] ? 'sn-disabled' : '') . ' sn-protect-login-form-subinput sn-cf-options">
			<select name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[max_login_attempts]" id="wf_sn_options_mla">';

				Utils::create_select_options($max_login_attempts, self::$options['max_login_attempts']);
				echo '</select> failed login attempts in ';
				echo '<select name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[max_login_attempts_time]" id="wf_sn_options_mlat">';
				Utils::create_select_options($max_login_attempts_time_s, self::$options['max_login_attempts_time']);
				echo '</select> minutes get the IP banned for ';
				echo '<select name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[bruteforce_ban_time]" id="wf_sn_options_bbt">';
				Utils::create_select_options($bruteforce_timeouts, self::$options['bruteforce_ban_time']);
				echo '</select>';
				echo '<p class="description">' . esc_html__('Users who continuously make failed login attempts will get banned. Five failed attempts in five minutes is a good threshold.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top">
			<th scope="row"><label for="wf_sn_cf_blockadminlogin">' . __('Block "admin" login', 'security-ninja') . '</label></th>
			<td class="' . (!self::$options['protect_login_form'] ? 'sn-disabled' : '') . ' sn-protect-login-form-subinput sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_blockadminlogin',
					array(
						'saved_value' => self::$options['blockadminlogin'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[blockadminlogin]',
					)
				);

				echo '<p class="description">' . esc_html__('Immediately block anyone trying to log in with the classic username "admin". These are most likely automated attempts to test for weak passwords.', 'security-ninja') . '</p>';

				echo '<p class="description">' . esc_html__('Warning: You should not turn this on if you have a user with username "admin".', 'security-ninja') . '</p>';

				echo '</td></tr>';

				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_hide_login_errors">' . __('Hide login errors', 'security-ninja') . '</label></th><td class="' . (!self::$options['protect_login_form'] ? 'sn-disabled' : '') . ' sn-protect-login-form-subinput sn-cf-options">';

				Wf_Sn::create_toggle_switch(
					WF_SN_CF_OPTIONS_KEY . '_hide_login_errors',
					array(
						'saved_value' => self::$options['hide_login_errors'],
						'option_key'  => WF_SN_CF_OPTIONS_KEY . '[hide_login_errors]',
					)
				);

				echo '<p class="description">' . esc_html__('This makes it harder for automated scripts to see if the account they attempt to log into even exists.', 'security-ninja') . '</p>';

				echo '</td></tr>';

				echo '<tr valign="top">';
				echo '<th scope="row"><label for="wf_sn_options_login_error_msg">Login notice</label></th>';
				echo '<td class="' . (!self::$options['protect_login_form'] ? 'sn-disabled' : '') . ' sn-protect-login-form-subinput sn-cf-options">
				<textarea cols="50" rows="3" name="' . WF_SN_CF_OPTIONS_KEY . '[login_error_msg]" id="wf_sn_options_login_error_msg" placeholder="">' . self::$options['login_error_msg'] . '</textarea>';
				echo '<p class="description">' . esc_html__('Error message to show on failed logins. Default: "Something went wrong".', 'security-ninja') . '</p>';
				echo '</td>';
				echo '</tr>';


				?>




			</tbody>
		</table>
		<h2><?php esc_html_e('Change Login URL', 'security-ninja'); ?></h2>
		<p><?php esc_html_e('Many automated hacking scripts look for the default wp-login.php file and the default /wp-admin URL to try to bruteforce their way in to your website. Change the default login URL to prevent this from happening.', 'security-ninja'); ?></p>

		<table class="form-table">
			<tbody>

				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_FIXES_OPTIONS_KEY) . '_change_login_url'; ?>"><?php esc_html_e('Change login URL', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options">

						<?php
						Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_change_login_url',
							array(
								'saved_value' => self::$options['change_login_url'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[change_login_url]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('Warning: You will not be able to log in without the new URL, please remember to write down this information.', 'security-ninja'); ?></p>
						<br />
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_FIXES_OPTIONS_KEY) . '_new_login_url'; ?>"><?php esc_html_e('New login slug', 'security-ninja'); ?></label></th>
					<td class="<?php if (!self::$options['change_login_url'] ? 'sn-disabled' : ''); ?> sn-cf-options sn-change-login-subinput">
						<input type="text" id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_new_login_url'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[new_login_url]'; ?>" value="<?php echo self::$options['new_login_url']; ?>" placeholder="<?php echo esc_attr(SecNin_Rename_WP_Login::$default_login_url); ?>" class="regular-text">
						<p class="description"><?php esc_html_e('Only alphanumeric characters, underscore (_) and dash (-) are allowed.', 'security-ninja'); ?></p>

						<p><?php esc_html_e('Preview', 'security-ninja'); ?>: <code><?php echo esc_url(trailingslashit(site_url(self::$options['new_login_url']))); ?></code></p>

					</td>
				</tr>
			</tbody>
		</table>
		<h3><?php esc_html_e('2FA - Two Factor Authentication', 'security-ninja'); ?></h3>
		<table class="form-table">
			<tbody>
				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_FIXES_OPTIONS_KEY) . '2fa_enabled'; ?>"><?php esc_html_e('Enable 2FA', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options">

						<?php
						Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '2fa_enabled',
							array(
								'saved_value' => self::$options['2fa_enabled'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[2fa_enabled]',
							)
						);
						?>
						<p class="description"><strong><?php esc_html_e('Warning', 'security-ninja'); ?>:</strong> <?php esc_html_e('Enabling this feature will mandate the setup and use of 2FA for login by the selected user roles.', 'security-ninja'); ?></p>
						<br />
					</td>
				</tr>


				<tr>
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_grace_period'; ?>"><?php esc_html_e('Grace Period', 'security-ninja'); ?></label></th>
					<td class="<?php if (!self::$options['2fa_enabled'] ? 'sn-disabled' : ''); ?> sn-cf-options sn-2fa-subinput">
						<input type="number" id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '2fa_grace_period'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_grace_period]'; ?>" value="<?php echo self::$options['2fa_grace_period']; ?>" class="regular-text" data-1p-ignore>
						<p class="description"><?php esc_html_e('How many days to allow users to skip setting up 2FA.', 'security-ninja'); ?></p>
						<p class="description"><?php esc_html_e('Note: If you change the number of days after enabling 2FA, the last day will be recalculated.', 'security-ninja'); ?></p>
						<p class="description"><?php esc_html_e('Set the value to 0 to enforce 2FA straight away.', 'security-ninja'); ?></p>
						<input type="hidden" id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '2fa_enabled_timestamp'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_enabled_timestamp]'; ?>">
						<?php

						if (self::$options['2fa_grace_period'] < 1) {
							echo '<p class="description">' . esc_html__('Setting this to 0 will require all users to set up 2FA immediately.', 'security-ninja') . '</p>';
						}
						if (self::$options['2fa_enabled']) {

							if (isset(self::$options['2fa_enabled_timestamp']) && '' !== self::$options['2fa_enabled_timestamp']) {
								$enabled_timestamp = self::$options['2fa_enabled_timestamp'];
								// use the value in self::$options['2fa_grace_period'] as a day value to calulate the cutoff time. You can use the $enabled_timestamp as the starting point when the 2FA was last enabled
								$cutoff_time = strtotime('+' . self::$options['2fa_grace_period'] . ' days', $enabled_timestamp);

								$current_time = current_time('timestamp');
								if ($current_time < $cutoff_time) {
									$formatted_cutoff_time = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $cutoff_time);
									$time_diff = human_time_diff($current_time, $cutoff_time);
									echo '<p class="description">' . sprintf(esc_html__('The grace period will end on %s, which is in about %s.', 'security-ninja'), $formatted_cutoff_time, $time_diff) . '</p>';
								} else {
									echo '<p class="description">' . esc_html__('The grace period has ended. Two-factor authentication is now enforced for all selected users.', 'security-ninja') . '</p>';
								}
							}
						}
						?>
					</td>
				</tr>

				<tr>
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_required_roles'; ?>"><?php esc_html_e('Required Roles', 'security-ninja'); ?></label></th>
					<td class="<?php if (!self::$options['2fa_enabled'] ? 'sn-disabled' : ''); ?> sn-cf-options sn-2fa-subinput">
						<?php
						$editable_roles = get_editable_roles();
						$selected_roles = isset(self::$options['2fa_required_roles']) ? (array) self::$options['2fa_required_roles'] : array();

						foreach ($editable_roles as $role => $details) {
							$name = translate_user_role($details['name']);
							$checked = in_array($role, $selected_roles) ? 'checked' : '';
						?>
							<label>
								<input type="checkbox" id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '2fa_required_roles'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_required_roles][]'; ?>" value="<?php echo esc_attr($role); ?>" <?php echo $checked; ?>>
								<?php echo esc_html($name); ?>
							</label><br>
						<?php
						}
						?>
						<p class="description"><?php esc_html_e('Only the selected roles will be required to use 2FA when logging in.', 'security-ninja'); ?></p>
					</td>
				</tr>
				<tr>
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_methods'; ?>"><?php esc_html_e('2FA Methods', 'security-ninja'); ?></label></th>
					<td class="<?php if (!self::$options['2fa_enabled'] ? 'sn-disabled' : ''); ?> sn-cf-options sn-2fa-subinput">

						<label>
							<input type="radio" id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '2fa_methods'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_methods]'; ?>" value="app" checked>
							<?php esc_html_e('Application', 'security-ninja'); ?>
						</label><br>
						<p class="description"><?php esc_html_e('Allowed login methods. Currently only App option is available.', 'security-ninja'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_intro'; ?>"><?php esc_html_e('2FA Introduction', 'security-ninja'); ?></label></th>
					<td class="<?php if (!self::$options['2fa_enabled'] ? 'sn-disabled' : ''); ?> sn-2fa-subinput sn-cf-options">

						<textarea id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_intro'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_intro]'; ?>" rows="3" cols="50"><?php echo self::$options['2fa_intro']; ?></textarea>
						<p class="description"><?php esc_html_e('This text will be displayed to users when they are prompted to set up two-factor authentication.', 'security-ninja'); ?></p>
					</td>
				</tr>


				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_enter_code'; ?>"><?php esc_html_e('2FA Enter Code', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options <?php if (!self::$options['2fa_enabled'] ? 'sn-disabled' : ''); ?> sn-2fa-subinput">
						<textarea id="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_2fa_enter_code'; ?>" name="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '[2fa_enter_code]'; ?>" rows="3" cols="50"><?php echo self::$options['2fa_enter_code']; ?></textarea>
						<p class="description"><?php esc_html_e('Shown next to the input field where the user enters their code.', 'security-ninja'); ?></p>
					</td>
				</tr>

			</tbody>
		</table>


		<table class="form-table">
			<tbody>



				<tr valign="top">
					<th colspan="2">
						<hr>
						<h3><?php esc_html_e('IP handling', 'security-ninja'); ?></h3>
					</th>
				</tr>
				<?php

				$current_user_ip = self::get_user_ip();

				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_blacklist">' . __('BLACKLIST IPs', 'security-ninja') . '</label></th><td class="sn-cf-options"><textarea id="wf_sn_cf_blacklist" name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[blacklist]" rows="5" cols="50">' . (is_array(self::$options['blacklist']) ? implode(PHP_EOL, self::$options['blacklist']) : '') . '</textarea>';
				echo '<p class="description">' . esc_html__('Manually block these IPs. Write one IP or subnet mask per line.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top"><th scope="row"><label for="wf_sn_cf_whitelist">' . __('Whitelist IPs', 'security-ninja') . '</label></th><td class="sn-cf-options"><textarea id="wf_sn_cf_whitelist" name="' . esc_attr(WF_SN_CF_OPTIONS_KEY) . '[whitelist]" rows="5" cols="50">' . (is_array(self::$options['whitelist']) ? implode(PHP_EOL, self::$options['whitelist']) : '') . '</textarea>';
				echo '<p class="description">' . esc_html__('These IPs are never blocked. Write one IP or subnet mask per line.', 'security-ninja') . '</p>';
				echo '<p>' . sprintf(esc_html__('Your IP address is: %s', 'security-ninja'), esc_html($current_user_ip)) . '</p>';
				$server_host = gethostname();
				$server_ip = '';
				if ($server_host) $server_ip   = gethostbyname($server_host);
				echo '<p>' . esc_html__('Your webserver is:', 'security-ninja') . ' ' . esc_html($server_host) . ' (' . esc_html($server_ip) . ')</p>';

				?>
				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_whitelist_wprocket'; ?>"><?php esc_html_e('Whitelist WP Rocket IPs', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options">

						<?php
						\WPSecurityNinja\Plugin\Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_whitelist_wprocket',
							array(
								'saved_value' => self::$options['whitelist_wprocket'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[whitelist_wprocket]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('Enable this option to automatically whitelist IP addresses associated with WP Rocket, ensuring uninterrupted service.', 'security-ninja'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_whitelist_uptimia'; ?>"><?php esc_html_e('Whitelist Uptimia IPs', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options">
						<?php
						\WPSecurityNinja\Plugin\Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_whitelist_uptimia',
							array(
								'saved_value' => self::$options['whitelist_uptimia'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[whitelist_uptimia]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('Enable this feature to whitelist IP addresses used by Uptimia.', 'security-ninja'); ?></p>
					</td>
				</tr>

				<tr valign="top">
					<th scope="row"><label for="<?php echo esc_attr(WF_SN_CF_OPTIONS_KEY) . '_whitelist_managewp'; ?>"><?php esc_html_e('Whitelist ManageWP IPs', 'security-ninja'); ?></label></th>
					<td class="sn-cf-options">
						<?php
						\WPSecurityNinja\Plugin\Wf_Sn::create_toggle_switch(
							WF_SN_CF_OPTIONS_KEY . '_whitelist_managewp',
							array(
								'saved_value' => self::$options['whitelist_managewp'],
								'option_key'  => WF_SN_CF_OPTIONS_KEY . '[whitelist_managewp]',
							)
						);
						?>
						<p class="description"><?php esc_html_e('Enable this feature to whitelist IP addresses used by ManageWP, facilitating seamless integration and maintenance tasks.', 'security-ninja'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<td colspan="2">
						<p class="description"><?php esc_html_e('Note: Some services automatically validate crawlers. This plugin allows traffic from Baidu, Bing (MSN), Google, Yahoo, Yandex, Petal Search, and Applebot if they can be successfully validated.', 'security-ninja'); ?></p>
					</td>
				</tr>
				<tr valign="top">
					<th colspan="2">
						<hr>
						<h3><?php esc_html_e('Locally Banned IPs', 'security-ninja'); ?></h3>
					</th>
				</tr>
				<?php
				echo '<tr valign="top"><th scope="row"><label for="wf-cf-ip-test">' . esc_html__('Test IP', 'security-ninja') . '</label></th><td id="sn-firewall-test-ip">';
				echo '<input type="text" placeholder="' . esc_html__('IP address, ie: 213.45.66.12', 'security-ninja') . '" class="regular-text" value="" id="wf-cf-ip-test">&nbsp; &nbsp;';
				echo '<a href="#" id="wf-cf-do-test-ip" class="button js-action">' . esc_html__('Test if IP is Banned', 'security-ninja') . '</a>';
				echo '<p class="description">Enter an IP address in order to test if it\'s banned. Your IP address is: ' . esc_html($current_user_ip) . '</p>';
				echo '</td></tr>';


				$banned_ips = self::get_banned_ips();
				$banned_ips = array_values($banned_ips);
				$banned_ips = array_filter($banned_ips);

				if (0 < count($banned_ips)) {
					echo '<tr valign="top"><th scope="row"><label for="">' . esc_html__('Currently Banned', 'security-ninja') . '</label></th><td id="sn-firewall-blacklist">';
					echo '<ul style="margin-top: 5px;">';
					foreach ($banned_ips as $banned_ip => $ban_time) {
						$message = sprintf(
							/* translators: 1: IP address, 2: Time until ban expires */
							__('<li>%1$s; time till ban expires: %2$s</li>', 'security-ninja'),
							$banned_ip,
							human_time_diff(current_time('timestamp'), $ban_time)
						);
						if (strlen($message) > 85) {
							$message = substr($message, 0, 82) . '...';
						}
						echo $message;
					}
					echo '</ul>';
					echo '<br><input type="button" value="' . esc_html__('Clear list of banned IPs', 'security-ninja') . '" id="sn-firewall-blacklist-clear" style="background: #cc0000;" class="button button-primary" />';
					echo '</td></tr>';
				} else {
					echo '<p>' . esc_html__('No locally banned IPs', 'security-ninja') . '</p>';
				}
				?>


				<input type="hidden" id="wf_sn_cf_active" name="wf_sn_cf[active]" value="<?php echo esc_attr(self::$options['active']); ?>" />

				<input type="hidden" id="wf_sn_cf_unblock_url" name="wf_sn_cf[unblock_url]" value="<?php echo esc_attr(self::$options['unblock_url']); ?>" />

				<tr valign="top">
					<td colspan="2" style="padding:0px;">
						<p class="submit"><br><input type="submit" value="<?php echo esc_html__('Save Changes', 'security-ninja'); ?>" class="input-button button-primary" name="Submit" /></p>
					</td>
				</tr>
		</table>
		</form>
		</div>
	<?php

			}
	?>
	<div id="sn-firewall-modal" class="sn-modal">
		<div class="sn-modal-content">
			<h2><?php esc_html_e('Enable Firewall', 'security-ninja'); ?></h2>
			<p><?php esc_html_e('To ensure you can regain access to your website if you get blocked, please enter your email address. You will be sent a secret access URL to help you regain access.', 'security-ninja'); ?></p>
			<input type="email" id="sn-firewall-email" autocomplete="off" placeholder="<?php esc_attr_e('Enter your email', 'security-ninja'); ?>" value="<?php echo esc_attr(wp_get_current_user()->user_email); ?>" required data-1p-ignore data-lpignore="true">
			<a href="#" id="sn-modal-continue" class="button button-primary"><?php esc_html_e('Continue', 'security-ninja'); ?></a><div id="sn-unblock-message"></div><div id="sn-firewall-status"></div>
		</div>
	</div>


<?php
	}
}

add_action('plugins_loaded', array(__NAMESPACE__ . '\wf_sn_cf', 'init'));
register_activation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_cf', 'activate'));
register_deactivation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_cf', 'deactivate'));
