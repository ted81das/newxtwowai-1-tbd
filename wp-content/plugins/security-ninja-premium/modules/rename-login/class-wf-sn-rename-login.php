<?php

namespace WPSecurityNinja\Plugin;

if (!function_exists('add_action')) {
	die('Please don\'t open this file directly!');
}

if (defined('ABSPATH') && !class_exists(__NAMESPACE__ . '\SecNin_Rename_WP_Login')) {

	class SecNin_Rename_WP_Login
	{
		const DEFAULT_SLUG = 'my-login';
		private static $wp_login_php;

		public static $default_login_url = 'my-login';

		private static function basename()
		{
			// Set in security-ninja.php
			return WF_SN_PLUGIN_BASENAME;
		}

		private static function path()
		{
			return trailingslashit(dirname(__FILE__));
		}

		/**
		 * use_trailing_slashes.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Friday, October 22nd, 2021.
		 * @access	private static
		 * @return	string
		 */
		private static function use_trailing_slashes()
		{
			return '/' === substr(get_option('permalink_structure'), -1, 1);
		}

		/**
		 * user_trailingslashit.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Friday, October 22nd, 2021.
		 * @access	private static
		 * @param	mixed	$string	
		 * @return	mixed
		 */
		private static function user_trailingslashit($string)
		{
			return self::use_trailing_slashes() ? trailingslashit($string) : untrailingslashit($string);
		}

		/**
		 * forbidden_slugs.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Sunday, October 24th, 2021.
		 * @access	public static
		 * @return	mixed
		 */
		public static function forbidden_slugs()
		{
			$wp = new \WP;
			return array_merge($wp->public_query_vars, $wp->private_query_vars, array('wp-admin', 'wp-login'));
		}
		/**
		 * wp_template_loader.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Friday, October 22nd, 2021.
		 * @access	private static
		 * @return	void
		 */
		private static function wp_template_loader()
		{
			global $pagenow;

			$pagenow = 'index.php';

			if (!defined('WP_USE_THEMES')) {
				define('WP_USE_THEMES', true);
			}

			wp();

			if ($_SERVER['REQUEST_URI'] === self::user_trailingslashit(str_repeat('-/', 10))) {
				$_SERVER['REQUEST_URI'] = self::user_trailingslashit('/wp-login-php/');
			}

			require_once ABSPATH . WPINC . '/template-loader.php';

			die;
		}

		/**
		 * new_login_slug.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	private
		 * @return	void
		 */
		public static function new_login_slug()
		{
			$settings = wf_sn_cf::get_options();

			if ($settings['change_login_url'] && $settings['new_login_url']) {
				return $settings['new_login_url'];
			}
			return false;
		}

		/**
		 * new_login_url.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @param	mixed	$scheme	Default: null
		 * @return	void
		 */
		public static function new_login_url($scheme = null)
		{
			if (get_option('permalink_structure')) {
				return self::user_trailingslashit(home_url('/', $scheme) . self::new_login_slug());
			} else {
				return home_url('/', $scheme) . '?' . self::new_login_slug();
			}
		}

		/**
		 * __construct.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @return	void
		 */
		public function __construct()
		{

			add_action('admin_notices', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'admin_notices'));
			add_action('network_admin_notices', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'admin_notices'));
			if (is_multisite() && !function_exists('is_plugin_active_for_network')) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}
			if (SecNin_Rename_WP_Login::is_active()) {
				add_action('plugins_loaded', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'plugins_loaded'), 1);
				add_action('wp_loaded', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'wp_loaded'));
				add_filter('site_url', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'site_url'), 10, 4);
				add_filter('network_site_url', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'network_site_url'), 10, 3);
				add_filter('wp_redirect', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'wp_redirect'), 10, 2);
				add_filter('site_option_welcome_email', array(__NAMESPACE__ . '\\SecNin_Rename_WP_Login', 'welcome_email'));
				remove_action('template_redirect', 'wp_redirect_admin_locations', 1000);
			}
		}

		/**
		 * Checks if the rename login module is activated
		 * Via cloud-firewall module settings
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Tuesday, November 16th, 2021.	
		 * @version	v1.0.1	Saturday, February 19th, 2022.
		 * @access	private static
		 * @return	boolean
		 */
		private static function is_active()
		{
			if (!class_exists(__NAMESPACE__ . '\wf_sn_cf')) return false;

			$fw_settings = wf_sn_cf::get_options();

			// Return false if firewall in general are turned off
			if (!isset($fw_settings['active'])) return false;
			if (0 === intval($fw_settings['active'])) return false;

			// Checks the rename login URL feature
			if (!isset($fw_settings['change_login_url'])) return false;
			if (0 === intval($fw_settings['change_login_url'])) return false;
			if (1 === intval($fw_settings['change_login_url'])) return true;

			// Default state
			return false;
		}

		/**
		 * admin_notices.
		 *
		 * @author	Lars Koudal
		 * @since	v0.0.1
		 * @version	v1.0.0	Friday, March 11th, 2022.
		 * @access	public static
		 * @return	void
		 */
		public static function admin_notices()
		{
			global $pagenow;
			if (!self::is_active()) return;
			if (\PAnD::is_admin_notice_active('wf-sn-notice-newlogin-forever')) {
				if (!is_network_admin() && $pagenow === 'options-permalink.php' && isset($_GET['settings-updated'])) {
					echo '<div data-dismissible="wf-sn-notice-newlogin-forever" class="secnin-notice notice notice-success is-dismissible"><p>' . sprintf(__('Your login page is now here: %s. Bookmark this page!', 'security-ninja'), '<strong><a href="' . esc_url(self::new_login_url()) . '">' . esc_url(self::new_login_url()) . '</a></strong>') . '</p></div>';
				}
			}
		}

		/**
		 * plugins_loaded.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @return	void
		 */
		public static function plugins_loaded()
{
    global $pagenow;
    if (
        !is_multisite() && (
            strpos($_SERVER['REQUEST_URI'], 'wp-signup') !== false ||
            strpos($_SERVER['REQUEST_URI'], 'wp-activate') !== false
        )
    ) {
        wp_die(__('Not activated.', 'security-ninja'));
    }

    $request = parse_url($_SERVER['REQUEST_URI']);
    $path = isset($request['path']) ? $request['path'] : '';

    if ((
            strpos($_SERVER['REQUEST_URI'], 'wp-login.php') !== false ||
            (!empty($path) && untrailingslashit($path) === site_url('wp-login', 'relative'))
        ) && !is_admin()
    ) {
        self::$wp_login_php = true;
        $_SERVER['REQUEST_URI'] = self::user_trailingslashit('/' . str_repeat('-/', 10));
        $pagenow = 'index.php';
    } elseif (
        !empty($path) && 
        (untrailingslashit($path) === home_url(self::new_login_slug(), 'relative') || (
            !get_option('permalink_structure') &&
            isset($_GET[self::new_login_slug()]) &&
            empty($_GET[self::new_login_slug()])
        ))
    ) {
        $pagenow = 'wp-login.php';
    }
}

		/**
		 * wp_loaded.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @return	void
		 */
		public static function wp_loaded()
		{

			global $pagenow;

			if (is_admin() && !is_user_logged_in() && !defined('DOING_AJAX')) {

				$ua_string = '';
				if (isset($_SERVER['HTTP_USER_AGENT'])) {
					$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
				}

				$current_user_ip = \WPSecurityNinja\Plugin\Wf_sn_cf::get_user_ip();
				$extraarr = array(
					'ip' => $current_user_ip,
					'user_agent' => $ua_string,
				);

				wf_sn_el_modules::log_event('security_ninja', 'attempted_access_to_wp_admin_url', __('User tried to visit /wp-admin/ URL', 'security-ninja'), $extraarr);

				wp_die(__('ERROR 404 - Page not found!', 'security-ninja'), 404);
			}

			$request = parse_url($_SERVER['REQUEST_URI']);

			if (
				$pagenow === 'wp-login.php' &&
				$request['path'] !== self::user_trailingslashit($request['path']) &&
				get_option('permalink_structure')
			) {
				wp_safe_redirect(self::user_trailingslashit(self::new_login_url()) . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
				die;
			} elseif (self::$wp_login_php) {
				if (
					($referer = wp_get_referer()) &&
					strpos($referer, 'wp-activate.php') !== false &&
					($referer = parse_url($referer)) &&
					!empty($referer['query'])
				) {
					parse_str($referer['query'], $referer);

					if (
						!empty($referer['key']) &&
						($result = wpmu_activate_signup($referer['key'])) &&
						is_wp_error($result) && (
							$result->get_error_code() === 'already_active' ||
							$result->get_error_code() === 'blog_taken'
						)
					) {
						wp_safe_redirect(self::new_login_url() . (!empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : ''));
						die;
					}
				}

				$current_user_ip = \WPSecurityNinja\Plugin\Wf_sn_cf::get_user_ip();
				$ua_string = '';
				if (isset($_SERVER['HTTP_USER_AGENT'])) {
					$ua_string = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
				}
				$extraarr = array(
					'ip' => $current_user_ip,
					'user_agent' => $ua_string,
				);

				wf_sn_el_modules::log_event('security_ninja', 'attempted_access_to_wplogin_php', __('User tried to visit wp-login.php', 'security-ninja'), $extraarr);

				self::wp_template_loader();
			} elseif ($pagenow === 'wp-login.php') {
				global $error, $interim_login, $action, $user_login;

				@require_once ABSPATH . 'wp-login.php';

				die;
			}
		}

		/**
		 * site_url.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, May 2nd, 2022.
		 * @access	public static
		 * @param	mixed	$url    	
		 * @param	mixed	$path   	
		 * @param	mixed	$scheme 	
		 * @param	mixed	$blog_id	
		 * @return	mixed
		 */
		public static function site_url($url, $path, $scheme, $blog_id)
		{
			return self::filter_wp_login_php($url, $scheme);
		}

		/**
		 * network_site_url.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, May 2nd, 2022.
		 * @access	public static
		 * @param	mixed	$url   	
		 * @param	mixed	$path  	
		 * @param	mixed	$scheme	
		 * @return	mixed
		 */
		public static function network_site_url($url, $path, $scheme)
		{
			return self::filter_wp_login_php($url, $scheme);
		}

		/**
		 * wp_redirect.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, May 2nd, 2022.
		 * @access	public static
		 * @param	mixed	$location	
		 * @param	mixed	$status  	
		 * @return	mixed
		 */
		public static function wp_redirect($location, $status)
		{
			return self::filter_wp_login_php($location);
		}

		/**
		 * filter_wp_login_php.
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @param	mixed	$url   	
		 * @param	mixed	$scheme	Default: null
		 * @return	mixed
		 */
		public static function filter_wp_login_php($url, $scheme = null)
		{
			if (strpos($url, 'wp-login.php') !== false) {
				if (is_ssl()) {
					$scheme = 'https';
				}

				$args = explode('?', $url);

				if (isset($args[1])) {
					parse_str($args[1], $args);
					$url = add_query_arg($args, self::new_login_url($scheme));
				} else {
					$url = self::new_login_url($scheme);
				}
			}

			return $url;
		}

		/**
		 * Change the welcome email on Multisite
		 *
		 * @author	Unknown
		 * @since	v0.0.1
		 * @version	v1.0.0	Monday, October 18th, 2021.
		 * @access	public
		 * @param	mixed	$value	
		 * @return	mixed
		 */
		public static function welcome_email($value)
		{
			$settings = wf_sn_cf::get_options();
			if (isset($settings['change_login_url']) && $settings['change_login_url'] && ('' <> $settings['new_login_url'])) {
				return str_replace('wp-login.php', trailingslashit(self::new_login_slug()), $value);
			}
			return $value;
		}
	}

	new SecNin_Rename_WP_Login;
}
