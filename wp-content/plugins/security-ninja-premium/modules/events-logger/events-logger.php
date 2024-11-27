<?php

namespace WPSecurityNinja\Plugin;

if (!function_exists('add_action')) {
	die('Please don\'t open this file directly!');
}

require 'sn-el-modules.php'; // @todo

class Wf_Sn_El
{


	private static $is_active = null;

	private static $options = null;

	/**
	 * init plugin
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Wednesday, May 15th, 2024.
	 * @access	public static
	 * @return	void
	 */
	public static function init()
	{

		if (is_null(self::$is_active)) {
			self::$is_active = self::is_active();
		}

		if (is_admin()) {
			// add tab to Security Ninja tabs
			add_filter('sn_tabs', array(__NAMESPACE__ . '\Wf_Sn_El', 'sn_tabs'));

			add_action('admin_enqueue_scripts', array(__NAMESPACE__ . '\Wf_Sn_El', 'enqueue_scripts'));

			add_action('wp_ajax_sn_el_truncate_log', array(__NAMESPACE__ . '\Wf_Sn_El', 'ajax_truncate_log'));

			self::default_settings(false);

			add_action('admin_init', array(__NAMESPACE__ . '\Wf_Sn_El', 'register_settings'));

			add_action('wp_ajax_get_events_data', array(__NAMESPACE__ . '\Wf_Sn_El', 'ajax_get_events_data'));
		}

		// Schedule the cron job to run twice daily
		if (!wp_next_scheduled('secnin_prune_logs_cron')) {
			wp_schedule_event(time(), 'daily', 'secnin_prune_logs_cron');
		}

		add_action('secnin_prune_logs_cron', array(__NAMESPACE__ . '\Wf_Sn_El', 'do_cron_prune_logs'));

		if (self::$is_active) {
			add_action('all', array(__NAMESPACE__ . '\Wf_Sn_El', 'watch_actions'), 9, 10);
		}
	}


	/**
	 * return_table_name.
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, December 8th, 2023.
	 * @access  public static
	 * @return  mixed
	 */
	public static function return_table_name()
	{
		global $wpdb;
		return $wpdb->prefix . 'wf_sn_el';
	}



	/**
	 * ajax_get_events_data.
	 *
	 * @author	Lars Koudal
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, October 27th, 2023.	
	 * @version	v1.0.1	Thursday, October 26th, 2023.	
	 * @version	v1.0.2	Monday, May 20th, 2024.
	 * @access	public static
	 * @return	mixed
	 */
	public static function ajax_get_events_data()
	{
		global $wpdb;

		// Verify nonce
		if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'sn_datatables_nonce')) {
			wp_die('Permission denied');
		}

		if (! current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Error saving dismiss pointer.'));
		}

		// Get DataTables parameters
		$draw   = intval($_POST['draw']);
		$start  = intval($_POST['start']);
		$length = intval($_POST['length']);
		$search = sanitize_text_field($_POST['search']['value'] ?? '');
		$order  = $_POST['order'] ?? array();

		// Build the initial query
		$query = 'SELECT id, timestamp, ip, user_agent, user_id, action, raw_data, description FROM ' . $wpdb->prefix . 'wf_sn_el';

		// Handle search filtering
		if (! empty($search)) {
			$query .= ' WHERE (description LIKE "%' . esc_sql($search) . '%" OR ip LIKE "%' . esc_sql($search) . '%" OR action LIKE "%' . esc_sql($search) . '%" OR user_agent LIKE "%' . esc_sql($search) . '%")';
		}

		// Get the total number of records before filtering
		$total_records = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->prefix . 'wf_sn_el');

		// Get the total number of records after filtering
		$total_filtered = $wpdb->get_var("SELECT COUNT(*) FROM ($query) AS filtered_table");

		// Handle sorting
		$order_by = ' ORDER BY timestamp DESC';
		if (! empty($order)) {
			$columns  = array('timestamp', 'description', 'ip', 'user_agent', 'user_id', 'action');
			$order_by = ' ORDER BY ';
			foreach ($order as $o) {
				$col_index = intval($o['column']);
				$col_dir   = ($o['dir'] === 'asc') ? 'ASC' : 'DESC';
				$order_by .= $columns[$col_index] . ' ' . $col_dir . ', ';
			}
			$order_by = rtrim($order_by, ', ');
		}
		$query .= $order_by;

		// Add pagination
		if ($length != -1) {
			$query .= ' LIMIT ' . $start . ', ' . $length;
		}

		// Execute the query to get events
		$events = $wpdb->get_results($query);
		$data   = array();

		$current_time = current_time('timestamp');
		// Process each event for output
		foreach ($events as $event) {
			$user        = ($event->user_id && $event->user_id !== '0') ? get_userdata($event->user_id) : null;
			$user_details = '';

			if ($user instanceof \WP_User) {
				$user_details = esc_html($user->user_nicename) . '<br><small>';
			}

			// Geolocate IP
			$geolocate_ip = \WPSecurityNinja\Plugin\SN_Geolocation::geolocate_ip($event->ip, true);
			if ($geolocate_ip && $geolocate_ip['country'] !== '-') {
				$country_code   = $geolocate_ip['country'];
				$country_img_url = wf_sn::get_country_img__premium_only($country_code);
				if ($country_img_url) {
					if (! isset($geoip_countrylist)) {
						include WF_SN_PLUGIN_DIR . 'modules/cloud-firewall/class-sn-geoip-countrylist.php';
					}
					$country_name   = $geoip_countrylist[$country_code] ?? 'Unknown';
					$country_img_html = '<img src="' . esc_url($country_img_url) . '" width="20" height="20" class="countryimg" title="' . esc_html($country_name) . '"> ';
					$user_details .= $country_img_html;
				}
			}
			$user_details .= esc_html($event->ip) . '</small>';

			// Prepare details output
			$raw_data = maybe_unserialize($event->raw_data);

			// Initialize the details output
			$details_output = '';

			if (! empty($raw_data)) {
				$details_output = '<button class="button button-small button-secondary">' . __('Details', 'security-ninja') . '</button>';
				$details_output .= '<div class="details-content" style="display:none;"><dl class="rowdetails">';

				if (is_array($raw_data)) {
					foreach ($raw_data as $key => $value) {
						// Check if the value is a WP_Error object
						if (is_wp_error($value)) {
							// Handle the error, for example, display the error message
							$details_output .= '<dt>' . esc_html($key) . '</dt><dd>' . esc_html($value->get_error_message()) . '</dd>';
						} elseif (is_object($value)) {
							// Handle the object, for example, display the class name
							$details_output .= '<dt>' . esc_html($key) . '</dt><dd>' . esc_html(get_class($value)) . '</dd>';
						} elseif (is_array($value)) {
							// Handle array values
							$details_output .= '<dt>' . esc_html($key) . '</dt><dd>';
							foreach ($value as $sub_key => $sub_value) {
								if (is_scalar($sub_value)) {
									$details_output .= esc_html($sub_key) . ': ' . esc_html($sub_value) . '<br>';
								} else {
									$details_output .= esc_html($sub_key) . ': ' . esc_html(gettype($sub_value)) . '<br>';
								}
							}
							$details_output .= '</dd>';
						} else {
							// Process normally if it's not an error, object, or array
							$details_output .= '<dt>' . esc_html($key) . '</dt><dd>' . esc_html($value) . '</dd>';
						}
					}
				} else {
					// If $raw_data is not an array, check if it is a WP_Error
					if (is_wp_error($raw_data)) {
						$details_output .= '<dd>' . esc_html($raw_data->get_error_message()) . '</dd>';
					} elseif (is_object($raw_data)) {
						// Handle the object, for example, display the class name
						$details_output .= '<dd>' . esc_html(get_class($raw_data)) . '</dd>';
					} else {
						$details_output .= '<dd>' . esc_html($raw_data) . '</dd>';
					}
				}

				$details_output .= '</dl></div>';
			}

			$timestamp_unix = strtotime($event->timestamp);

			// Calculate the time since
			$time_since = human_time_diff($timestamp_unix, $current_time) . ' ' . __('ago', 'security-ninja');

			// Format the original timestamp
			$formatted_timestamp = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp_unix);

			// Concatenate the time since and the formatted timestamp
			$timestamp_details = esc_html($time_since) . '<br><small>' . esc_html($formatted_timestamp) . '</small>';

			$data[] = array(
				'timestamp'   => $timestamp_details,
				'user_id'     => $user_details,
				'action'      => esc_html($event->action),
				'description' => esc_html($event->description),
				'details'     => $details_output,
			);
		}

		// Return JSON response
		$response = array(
			'draw'            => $draw,
			'recordsTotal'    => $total_records,
			'recordsFiltered' => $total_filtered,
			'data'            => $data,
		);
		wp_send_json($response);
		wp_die();
	}





	/**
	 * Send a webhook event.
	 *
	 * @since   v0.0.1
	 * @version v1.0.1  Thursday, October 5th, 2023.
	 * @access  public static
	 * @param   string $event The event name.
	 * @param   array  $data  The event data.
	 * @return  bool          True on success, false on failure.
	 */
	public static function send_webhook_event($event, $data)
	{
		if (empty($event) || !is_string($event) || empty($data) || !is_array($data)) {
			return false;
		}

		$options = get_option('wf_sn_el');

		if (
			!isset($options['webhook_active']) || intval($options['webhook_active']) !== 1 ||
			empty($options['webhook_url']) || !filter_var($options['webhook_url'], FILTER_VALIDATE_URL)
		) {
			return false;
		}

		if (empty($options[$event]) || intval($options[$event]) !== 1) {
			return false;
		}

		$data = array_merge(
			$data,
			array(
				'event'          => sanitize_text_field($event),
				'source'         => site_url(),
				'plugin_version' => wf_sn::get_plugin_version(),
				'webhook_url'    => esc_url($options['webhook_url']),
			)
		);

		$response = wp_remote_post(
			$options['webhook_url'],
			array(
				'body'    => wp_json_encode($data),
				'headers' => array('Content-Type' => 'application/json'),
				'timeout' => 15,
			)
		);

		if (is_wp_error($response)) {
			wf_sn_el_modules::log_event(
				'security_ninja',
				'webhook_event',
				esc_html__('Webhook request failed', 'security-ninja'),
				array('error' => $response->get_error_message())
			);
			return false;
		}

		wf_sn_el_modules::log_event(
			'security_ninja',
			'webhook_event',
			sprintf(
				// translators: %s: event name
				esc_html__('Webhook event sent - %s', 'security-ninja'),
				esc_attr($event)
			)
		);
		return true;
	}




	/**
	 * Is the event logger enabled
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  mixed
	 */
	public static function is_active()
	{
		if (self::$is_active !== null) {
			return self::$is_active;
		}

		if (!self::$options) {
			self::$options = get_option('wf_sn_el');
		}

		if (isset(self::$options['active'])) {
			self::$is_active = (bool) self::$options['active'];
		} else {
			self::$is_active = false;
		}

		return self::$is_active;
	}

	/**
	 * enqueue CSS and JS scripts on plugin's admin page
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function enqueue_scripts()
	{
		if (!Wf_Sn::is_plugin_page()) {
			return;
		}

		$plugin_url = plugin_dir_url(__FILE__);

		$datatables_nonce = wp_create_nonce('sn_datatables_nonce');
		wp_enqueue_script('sn-el-datatables', $plugin_url . 'js/jquery.dataTables.min.js', array('jquery'), wf_sn::$version, true);
		wp_localize_script(
			'sn-el-datatables',
			'datatables_object',
			array(
				'nonce' => $datatables_nonce,
			)
		);

		wp_enqueue_style('sn-el-datatables', $plugin_url . 'css/jquery.dataTables.min.css', array(), wf_sn::$version);

		$js_vars = array(
			'nonce' => wp_create_nonce('wf_sn_el'),
		);

		wp_register_script('sn-el', $plugin_url . 'js/wf-sn-el-min.js', array('jquery', 'sn-el-datatables'), wf_sn::$version, true);
		wp_localize_script('sn-el', 'wf_sn_el', $js_vars);
		wp_enqueue_script('sn-el');

		wp_enqueue_style('sn-el', $plugin_url . 'css/wf-sn-el.css', array(), wf_sn::$version);
	}

	/**
	 * add new tab
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @param   mixed   $tabs
	 * @return  mixed
	 */
	public static function sn_tabs($tabs)
	{
		$logger_tab = array(
			'id'       => 'sn_logger',
			'class'    => '',
			'label'    => esc_html__('Events', 'security-ninja'),
			'callback' => array(__NAMESPACE__ . '\\wf_sn_el', 'logger_page'),
		);
		$done = false;
		$tab_count = count($tabs);

		for ($i = 0; $i < $tab_count; $i++) {
			if ($tabs[$i]['id'] === 'sn_logger') {
				$tabs[$i] = $logger_tab;
				$done       = true;
				break;
			}
		}

		if (!$done) {
			$tabs[] = $logger_tab;
		}

		return $tabs;
	}


	/**
	 * set default options
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @param   boolean $force  Default: false
	 * @return  void
	 */
	public static function default_settings($force = false)
	{
		$defaults = array(
			'active'                  => 0,
			'retention'               => 'day-7',
			'email_reports'           => '',
			'email_modules'           => array('users', 'menus', 'file_editor', 'taxonomies', 'media', 'posts', 'widgets', 'installer', 'comments', 'settings', 'security_ninja', 'woocommerce'),
			'email_to'                => get_bloginfo('admin_email'),
			'last_reported_event'     => 0,
			// Webhook section
			'webhook_active'          => 0,
			'webhook_url'             => '',
			'webhook_firewall_events' => 0,
			'webhook_user_logins'     => 0,
			'webhook_updates'         => 0,
		);

		if (!self::$options) {
			self::$options = get_option('wf_sn_el');
		}

		if ($force || !self::$options || !isset(self::$options['retention'])) {
			update_option('wf_sn_el', $defaults, false);
		}
	}



	/**
	 * sanitize settings on save
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @version v1.0.1  Monday, November 13th, 2023.
	 * @version v1.0.2  Thursday, February 22nd, 2024.
	 * @access  public static
	 * @param   mixed   $values
	 * @return  mixed
	 */
	public static function sanitize_settings($values)
	{
		$old_options = get_option('wf_sn_el');
		$new_options = $old_options; // Start with old_options

		// List of keys with boolean values (checkboxes typically), default to 0 if unchecked
		$boolean_keys = array(
			'active', // Correctly handle 'active'
			'webhook_firewall_events',
			'webhook_user_logins',
			'webhook_updates',
			'webhook_active',
		);

		// Ensure all boolean keys are set properly, defaulting to 0 if not present
		foreach ($boolean_keys as $key) {
			$new_options[$key] = isset($values[$key]) ? intval($values[$key]) : 0;
		}

		// Handle all other keys with specific data types or requirements
		foreach ($values as $key => $value) {
			switch ($key) {
				case 'retention':
				case 'email_reports':
				case 'webhook_url':
				case 'email_to':
				case 'remove_settings_deactivate':
					// Sanitize text fields
					$new_options[$key] = sanitize_text_field($value);
					break;

				case 'webhook_events':
				case 'email_modules':
					// Ensure array values are sanitized
					if (is_array($value)) {
						$new_options[$key] = array_map('sanitize_text_field', $value);
					}
					break;
			}
		}

		// Optional: Check and initialize missing fields if necessary
		$new_options['email_modules'] = $new_options['email_modules'] ?? array();

		return $new_options;
	}





	/**
	 * all settings are saved in one option key
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function register_settings()
	{
		register_setting('wf_sn_el', 'wf_sn_el', array(__NAMESPACE__ . '\\wf_sn_el', 'sanitize_settings'));
	}



	/**
	 * process selected actions / filters
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function watch_actions()
	{
		$users          = array(
			'user_register',
			'wp_login_failed',
			'profile_update',
			'password_reset',
			'retrieve_password',
			'set_logged_in_cookie',
			'clear_auth_cookie',
			'delete_user',
			'deleted_user',
			'set_user_role',
		);
		$menus          = array(
			'wp_create_nav_menu',
			'wp_update_nav_menu',
			'delete_nav_menu',
		);
		$file_editor    = array('wp_redirect');
		$taxonomies     = array(
			'created_term',
			'delete_term',
			'edited_term',
		);
		$media          = array(
			'add_attachment',
			'edit_attachment',
			'delete_attachment',
			'wp_save_image_editor_file',
		);
		$posts          = array(
			// 'transition_post_status', // When a post changes status - lars, too much details in log - maybe make optional to log this level?
			'deleted_post',           // When a post is deleted

			'publish_post',           // When a post is published
			'edit_post',              // When a post is updated
			'trash_post',             // When a post is moved to trash
			'untrash_post',           // When a post is restored from trash
		);
		$widgets        = array(
			'update_option_sidebars_widgets',
			'wp_ajax_widgets-order',
			'widget_update_callback',
		);
		$installer      = array(
			'upgrader_process_complete',
			'activate_plugin',
			'deactivate_plugin',
			'switch_theme',
			'_core_updated_successfully',
		);
		$comments       = array(
			'comment_flood_trigger',
			'wp_insert_comment',
			'edit_comment',
			'delete_comment',
			'trash_comment',
			'untrash_comment',
			'spam_comment',
			'unspam_comment',
			'transition_comment_status',
			'comment_duplicate_trigger',
		);
		$settings       = array(
			'whitelist_options',
			'update_site_option',
			'update_option_permalink_structure',
			'update_option_category_base',
			'update_option_tag_base',
		);


		$security_ninja = array(
			'security_ninja_done_testing',
			'security_ninja_scheduled_scanner_done_cron',
			'security_ninja_core_scanner_done_scanning',
			'security_ninja_remote_access',
			'security_ninja_malware_scanner_done_scanning',
		);

		$woocommerce = array(
			// Product actions
			'woocommerce_update_product',
			'woocommerce_new_product_data',
			'woocommerce_product_duplicate',
			'woocommerce_update_product_variation',
			'woocommerce_delete_product',

			// Customer actions
			'woocommerce_new_customer',             // New customer added
			'woocommerce_delete_customer',          // Customer deleted
			'woocommerce_customer_reset_password',  // Customer reset password

			// Order actions
			'woocommerce_new_order',                // New order created
			'woocommerce_delete_order',             // Order deleted
			'woocommerce_order_status_changed',     // Order status changed
			'woocommerce_order_refunded',           // Order refunded

			// Coupon actions
			'woocommerce_delete_coupon',            // Coupon deleted
			'woocommerce_coupon_updated',           // Coupon updated
			'woocommerce_coupon_created',           // Coupon created

		);

		$args = func_get_args();
		if (in_array(current_action(), $users, true)) {
			wf_sn_el_modules::parse_action_users(current_action(), $args);
		} elseif (in_array(current_action(), $menus, true)) {
			wf_sn_el_modules::parse_action_menus(current_action(), $args);
		} elseif (in_array(current_action(), $file_editor, true)) {
			wf_sn_el_modules::parse_action_file_editor(current_action(), $args);
		} elseif (in_array(current_action(), $taxonomies, true)) {
			wf_sn_el_modules::parse_action_taxonomies(current_action(), $args);
		} elseif (in_array(current_action(), $media, true)) {
			wf_sn_el_modules::parse_action_media(current_action(), $args);
		} elseif (in_array(current_action(), $posts, true)) {
			wf_sn_el_modules::parse_action_posts(current_action(), $args);
		} elseif (in_array(current_action(), $widgets, true)) {
			wf_sn_el_modules::parse_action_widgets(current_action(), $args);
		} elseif (in_array(current_action(), $installer, true)) {
			wf_sn_el_modules::parse_action_installer(current_action(), $args);
		} elseif (in_array(current_action(), $comments, true)) {
			wf_sn_el_modules::parse_action_comments(current_action(), $args);
		} elseif (in_array(current_action(), $settings, true)) {
			wf_sn_el_modules::parse_action_settings(current_action(), $args);
		} elseif (in_array(current_action(), $security_ninja, true)) {
			wf_sn_el_modules::parse_action_security_ninja(current_action(), $args);
		} elseif (in_array(current_action(), $woocommerce, true)) {
			wf_sn_el_modules::parse_action_woocommerce(current_action(), $args);
		}
	}





	/**
	 * truncate event log table
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function ajax_truncate_log()
	{
		global $wpdb;

		check_ajax_referer('wf_sn_el');

		if (!current_user_can('manage_options')) {
			wp_send_json_error(
				array(
					'message' => __('Failed.', 'security-ninja'),
				)
			);
		}
		$options = get_option('wf_sn_el');

		$options['last_reported_event'] = 0;
		update_option('wf_sn_el', $options, false);

		$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . 'wf_sn_el');

		wp_send_json_success(
			array(
				'message' => __('Emptied the log.', 'security-ninja'),
			)
		);

		exit();
	}








	/**
	 * prune events log table
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @version v1.0.1  Tuesday, October 24th, 2023.
	 * @version v1.0.2  Monday, November 13th, 2023.
	 * @access  public static
	 * @param   boolean $force  Default: false
	 * @return  void
	 */
	public static function do_cron_prune_logs()
	{
		global $wpdb;
		if (empty(self::$options['retention'])) {
			return false;
		}

		// Define the list of protected actions
		$protected_actions = array(
			'login_form_blocked_ip',
			'blockadminlogin',
			'blacklisted_IP',
			'blocked_ip_banned',
			'blocked_ip_suspicious_request',
			'blocked_ip_country_ban',
			'login_denied_banned_IP',
			'firewall_ip_banned',
		);

		// Prepare placeholders for protected actions
		$placeholders = implode(', ', array_fill(0, count($protected_actions), '%s'));

		// Prepare the base query with placeholders for dynamic values
		$base_query = "DELETE FROM {$wpdb->prefix}wf_sn_el WHERE (action NOT IN ($placeholders) OR (action IN ($placeholders) AND timestamp < DATE_SUB(NOW(), INTERVAL 2 YEAR)))";

		// Determine retention strategy
		$tmp             = explode('-', self::$options['retention']);
		$retention_value = (int) $tmp[1];

		if ('cnt' === $tmp[0]) {
			$id = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$wpdb->prefix}wf_sn_el ORDER BY id DESC LIMIT %d, 1", $retention_value));
			if ($id) {
				$query = $wpdb->prepare($base_query . ' AND id < %d', array_merge($protected_actions, $protected_actions, array($id)));
				$wpdb->query($query);
			}
		} else {
			$query = $wpdb->prepare($base_query . ' AND timestamp < DATE_SUB(NOW(), INTERVAL %d DAY)', array_merge($protected_actions, $protected_actions, array($retention_value)));
			$wpdb->query($query);
		}

		$deleted_rows = $wpdb->rows_affected;
		if ($deleted_rows > 0) {
			wf_sn_el_modules::log_event(
				'security_ninja',
				'prune_events_log',
				sprintf(
					// translators: %d: number of deleted rows
					esc_html__('Cron job: Emptied event logs. Deleted rows: %d', 'security-ninja'),
					$deleted_rows
				),
				array('Deleted rows' => $deleted_rows)
			);
		}

		return true;
	}


	/**
	 * send email reports based on user's preferences
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @version v1.0.1  Friday, March 3rd, 2023.
	 * @access  public static
	 * @param   mixed   $last_id
	 * @return  void
	 */
	public static function send_email_reports($last_id)
	{
		global $wpdb;

		$body    = '';

		if (!isset(self::$options) || !is_array(self::$options) || !isset(self::$options['email_reports']) || !self::$options['email_reports'] || !$last_id) {
			return false;
		}

		if ($last_id - self::$options['last_reported_event'] >= (int) self::$options['email_reports']) {
			$modules = '';

			if (self::$options['email_modules']) {
				$modules = " and module IN('" . implode("', '", self::$options['email_modules']) . "') ";
			}

			$events = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . 'wf_sn_el WHERE id > ' . self::$options['last_reported_event'] . $modules . ' ORDER BY id DESC LIMIT ' . self::$options['email_reports']);

			if ( ! $events || count( $events ) < (int) self::$options['email_reports'] ) {
				return;
			}

			self::$options['last_reported_event'] = $events[0]->id;
			update_option('wf_sn_el', self::$options, false);

			$admin_url = admin_url('admin.php?page=wf-sn#sn_logger');
			// if ($admin_url = SecNin_Rename_WP_Login::new_login_slug()) {
			// 	$admin_url = trailingslashit(site_url( $admin_url)) . 'admin.php?page=wf-sn#sn_logger';
			// }

			$headers = array('Content-Type: text/html; charset=UTF-8');

			$body .= sprintf(
				// translators: %1$s: site name, %2$s: opening link tag, %3$s: closing link tag, %4$s: line break
				__('Recent events on %1$s: %2$s(more details are available in WordPress admin)%3$s%4$s', 'security-ninja'),
				esc_html(get_bloginfo('name')),
				'<a href="' . esc_url($admin_url) . '">',
				'</a>',
				'<br>'
			);
			
			// Add email-friendly responsive table styles
			$body .= '
			<table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
				<thead>
					<tr style="background-color: #f8f9fa;">
						<th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">' . 
							esc_html__('Date & Time', 'security-ninja') . 
						'</th>
						<th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">' . 
							esc_html__('Event Details', 'security-ninja') . 
						'</th>
					</tr>
				</thead>
				<tbody>';

			foreach ($events as $event) {
				$user = '';
				if ($event->user_id) {
					$user_info = get_userdata($event->user_id);
					if ($user_info) {
						$user  = '<strong>' . esc_html($user_info->user_nicename) . '</strong>';
						$user .= ' (' . esc_html(implode(', ', $user_info->roles)) . ')';
					} else {
						$user = '<strong>' . __('user deleted', 'security-ninja') . '</strong>';
					}
				} elseif (substr($event->user_agent, 0, 10) === 'WordPress/') {
					$user = '<strong>' . __('WP cron', 'security-ninja') . '</strong>';
				} else {
					$user = '<strong>' . __('Anonymous user', 'security-ninja') . '</strong>';
				}

				if ('' !== $event->ip) {
					$user .= ' (' . esc_html($event->ip) . ')';
				}

				$module = str_replace(array('_', '-', 'ninja'), array(' ', ' ', 'Ninja'), ucfirst($event->module));

				// Format the timestamp according to WP settings
				$timestamp = sprintf(
					'%s<br><span style="color: #666; font-size: 0.9em;">%s</span>',
					esc_html(gmdate(get_option('date_format'), strtotime($event->timestamp))),
					esc_html(gmdate(get_option('time_format'), strtotime($event->timestamp)))
				);

				// Format the event details
				$event_details = sprintf(
					// translators: 1: Event description, 2: User name, 3: Module name
					__('%1$s by %2$s in %3$s module.', 'security-ninja'),
					esc_html($event->description),
					$user, // already escaped
					esc_html($module)
				);

				$body .= sprintf(
					'<tr style="border-bottom: 1px solid #dee2e6;">
						<td style="padding: 12px; vertical-align: top; min-width: 140px;">%s</td>
						<td style="padding: 12px; vertical-align: top;">%s</td>
					</tr>',
					$timestamp,
					$event_details
				);
			}

			$body .= '</tbody></table>';

			$body .= sprintf(
				'<p style="margin-top: 20px; color: #666;">' .
					// translators: %1$s: opening link tag, %2$s: closing link tag
					__('Events Logger email report settings can be adjusted in %1$sWordPress admin%2$s', 'security-ninja') . '</p>',
				'<a href="' . esc_url($admin_url) . '" style="color: #0073aa; text-decoration: underline;">',
				'</a>'
			);

			$emreps = (array) explode(',', self::$options['email_to']);
			foreach ($emreps as $emrep) {
				$emrep = trim($emrep);
				if (!empty($emrep) && is_email($emrep)) {
					
					try {
						add_filter('wp_mail_content_type', array(__NAMESPACE__ . '\Wf_Sn_El', 'sn_set_html_mail_content_type'));

						$subject = sprintf(
							esc_html__('[%s] Security Ninja - Events Logger report', 'security-ninja'),
							wp_specialchars_decode(get_option('blogname'), ENT_QUOTES)
						);

						// Ensure body is properly formatted as HTML
						if (strpos($body, '<html') === false) {
							$body = sprintf(
								'<!DOCTYPE html><html><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /></head><body>%s</body></html>',
								$body
							);
						}

						// Send email
						$sendrep = wp_mail($emrep, $subject, $body, $headers);

						if (!$sendrep) {
							wf_sn_el_modules::log_event(
								'security_ninja',
								'send_email_update',
								esc_html__('Email could not be sent.', 'security-ninja'),
								array('recipient' => $emrep)
							);
						} else {
							wf_sn_el_modules::log_event(
								'security_ninja',
								'send_email_update',
								esc_html__('Email update sent', 'security-ninja'),
								array('recipient' => $emrep)
							);
						}

					} catch (\Exception $e) {
						// Log the error
						wf_sn_el_modules::log_event(
							'security_ninja',
							'send_email_error',
							esc_html__('Email error occurred', 'security-ninja'),
							array(
								'error' => $e->getMessage(),
								'recipient' => $emrep
							)
						);
					} finally {
						// Always remove the filter, even if an error occurred
						remove_filter('wp_mail_content_type', array(__NAMESPACE__ . '\Wf_Sn_El', 'sn_set_html_mail_content_type'));
					}
				} else {
					wf_sn_el_modules::log_event('security_ninja', 'send_email_update', __('Invalid email address.', 'security-ninja'), array('recipient' => $emrep));
				}
			}
		}
	}



	/**
	 * sn_set_html_mail_content_type.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Friday, November 8th, 2024.
	 * @access	public static
	 * @return	mixed
	 */
	public static function sn_set_html_mail_content_type() {
    return 'text/html';
}

	/**
	 * display results
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function logger_page()
	{

		global $wpdb;
		$options = get_option('wf_sn_el');
?>
		<div class="card">
			<h3><?php esc_html_e('Events Logger', 'security-ninja'); ?></h3>


			<table class="wp-list-table widefat fixed striped table-view-list" id="sn-el-datatable" style="border-spacing: 0;">
				<thead>
					<tr>
						<th id="sn-el-date" class="column-primary"><?php _e('Time', 'security-ninja'); ?></th>
						<th id="sn-el-event"><?php _e('Event', 'security-ninja'); ?></th>
						<th id="sn-el-user_id"><?php _e('User', 'security-ninja'); ?></th>
						<th id="sn-el-action"><?php _e('Action', 'security-ninja'); ?></th>
						<th id="sn-el-details"><?php _e('Details', 'security-ninja'); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
				<tfoot>
					<tr>
						<th class="column-primary"><?php _e('Time', 'security-ninja'); ?></th>
						<th><?php _e('Event', 'security-ninja'); ?></th>
						<th><?php _e('User', 'security-ninja'); ?></th>
						<th><?php _e('Action', 'security-ninja'); ?></th>
						<th><?php _e('Details', 'security-ninja'); ?></th>
					</tr>
				</tfoot>
			</table>

			<div id="datatable-error" class="card" style="display:none;"></div>

			<?php

			$retention_settings = array(
				array(
					'val'   => 'day-7',
					'label' => __('Keep event logs for up to 7 days', 'security-ninja'),
				),
				array(
					'val'   => 'day-15',
					'label' => __('Keep event logs for up to 15 days', 'security-ninja'),
				),
				array(
					'val'   => 'day-30',
					'label' => __('Keep event logs for up to 30 days', 'security-ninja'),
				),
				array(
					'val'   => 'day-45',
					'label' => __('Keep event logs for up to 45 days', 'security-ninja'),
				),
			);

			$email_reports_settings = array(
				array(
					'val'   => '0',
					'label' => __('Do not email any reports', 'security-ninja'),
				),
				array(
					'val'   => '100',
					'label' => __('Send one email for every 100 events', 'security-ninja'),
				),
				array(
					'val'   => '500',
					'label' => __('Send one email for every 500 events', 'security-ninja'),
				),
				array(
					'val'   => '1000',
					'label' => __('Send one email for every 1000 events', 'security-ninja'),
				),
				array(
					'val'   => '2000',
					'label' => __('Send one email for every 2000 events', 'security-ninja'),
				),

			);

			$modules = array(
				array(
					'val'   => 'comments',
					'label' => __('Comments', 'security-ninja'),
				),
				array(
					'val'   => 'file_editor',
					'label' => __('File editor', 'security-ninja'),
				),
				array(
					'val'   => 'installer',
					'label' => __('Installer', 'security-ninja'),
				),
				array(
					'val'   => 'media',
					'label' => __('Media', 'security-ninja'),
				),
				array(
					'val'   => 'menus',
					'label' => __('Menus', 'security-ninja'),
				),
				array(
					'val'   => 'posts',
					'label' => __('Posts', 'security-ninja'),
				),
				array(
					'val'   => 'security_ninja',
					'label' => 'Security Ninja',
				),
				array(
					'val'   => 'settings',
					'label' => __('Settings', 'security-ninja'),
				),
				array(
					'val'   => 'taxonomies',
					'label' => __('Taxonomies', 'security-ninja'),
				),
				array(
					'val'   => 'users',
					'label' => __('Users', 'security-ninja'),
				),
				array(
					'val'   => 'widgets',
					'label' => __('Widgets', 'security-ninja'),
				),
				array(
					'val'   => 'woocommerce',
					'label' => __('WooCommerce', 'security-ninja'),
				),
			);
			?>
			<div id="wf-sn-el-options-container">
				<h3><?php esc_html_e('Settings', 'security-ninja'); ?></h3>
				<form action="options.php" method="post">
					<?php settings_fields('wf_sn_el'); ?>
					<table class="form-table">
						<tbody>
							<tr valign="top">
								<th scope="row"><label for="wf_sn_el[active]"><?php esc_html_e('Enable events logging', 'security-ninja'); ?></label></th>
								<td class="sn-cf-options">
									<?php
									Wf_Sn::create_toggle_switch(
										'active',
										array(
											'saved_value' => self::$options['active'],
											'option_key'  => 'wf_sn_el[active]',
										)
									);
									?>
									<p class="description"><?php esc_html_e('If enabled events happening on your website will be logged here.', 'security-ninja'); ?></p>
									<p class="description"><?php esc_html_e('Note - Some important events will still be logged here.', 'security-ninja'); ?></p>

								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><label for="email_reports"><?php esc_html_e('Email Reports', 'security-ninja'); ?></label></th>
								<td><select id="email_reports" name="wf_sn_el[email_reports]" class="regular-text">
										<?php
										Utils::create_select_options($email_reports_settings, self::$options['email_reports']);
										?>
									</select>
									<p class="description"><?php esc_html_e('Email reports with a specified number of latest events can be automatically emailed to alert the admin of any suspicious events. Default: Do not email any reports', 'security-ninja'); ?></p>
								</td>
							</tr>
							<?php
							$selected_modules = (array) self::$options['email_modules'];

							?>
							<tr valign="top">
								<th scope="row"><label for="email_modules"><?php esc_html_e('Modules Included in Email Reports', 'security-ninja'); ?></label></th>
								<td><select size="12" id="email_modules" multiple="multiple" name="wf_sn_el[email_modules][]">
										<?php
										Utils::create_select_options($modules, $selected_modules);
										?>
									</select>
									<p class="description"><?php esc_html_e('If you don\'t want to receive event reports from specific modules, deselect them. Default: all modules.', 'security-ninja'); ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="email_to"><?php esc_html_e('Email Recipient', 'security-ninja'); ?></label></th>
								<td><input type="text" class="regular-text" id="email_to" name="wf_sn_el[email_to]" value="<?php echo esc_html(self::$options['email_to']); ?>" />
									<p class="description"><?php esc_html_e('One or more email addresses who will receive the reports. Separate more recipients with comma. Default: WP admin email.', 'security-ninja'); ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="retention"><?php esc_html_e('Log Retention Policy', 'security-ninja'); ?></label></th>
								<td><select id="retention" name="wf_sn_el[retention]" class="regular-text">
										<?php
										Utils::create_select_options($retention_settings, self::$options['retention']);
										?>
									</select>
									<p class="description"><?php esc_html_e('In order to preserve disk space logs are automatically deleted based on this option. Default: keep logs for 7 days.', 'security-ninja'); ?></p>
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for=""><?php esc_html_e('Miscellaneous', 'security-ninja'); ?></label></th>
								<td><input type="button" value="<?php esc_html_e('Delete all log entries', 'security-ninja'); ?>" class="button-secondary button" id="sn-el-truncate" />
									<p class="description"><?php esc_html_e('Delete all logged events in the database. Please note that there is NO undo for this action.', 'security-ninja'); ?></p>
								</td>
							</tr>
					</table>

					<h3><?php esc_html_e('Webhook Events', 'security-ninja'); ?></h3>
					<table class="form-table">
						<tbody>

							<tr valign="top">
								<th scope="row"><label for="webhook_active"><?php esc_html_e('Webhook Active', 'security-ninja'); ?></label></th>

								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e('Webhook Active', 'security-ninja'); ?></span></legend>

										<?php
										Wf_Sn::create_toggle_switch(
											'webhook_active',
											array(
												'saved_value' => self::$options['webhook_active'],
												'option_key'  => 'wf_sn_el[webhook_active]',
											)
										);
										?>
										<p><?php esc_html_e('If enabled the webhook URL will be notified about the selected events.', 'security-ninja'); ?></p>
									</fieldset>

								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="webhook_url"><?php esc_html_e('Webhook URL', 'security-ninja'); ?></label></th>
								<td><input type="url" class="regular-text code" id="webhook_url" name="wf_sn_el[webhook_url]" value="<?php echo esc_url(self::$options['webhook_url']); ?>" placeholder="https://" />
									<p class="description"><?php esc_html_e('Webhooks are sent as POST requests to the URL you specify. The request body contains a JSON object with information about the event that triggered the webhook. You can use this information to take action in your own systems.', 'security-ninja'); ?></p>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row"><?php esc_html_e('Events', 'security-ninja'); ?></th>
								<td>
									<fieldset>
										<legend class="screen-reader-text"><span><?php esc_html_e('Webhook Events Settings', 'security-ninja'); ?></span></legend>
										<?php
										Wf_Sn::create_toggle_switch(
											'webhook_firewall_events',
											array(
												'saved_value' => self::$options['webhook_firewall_events'],
												'option_key'  => 'wf_sn_el[webhook_firewall_events]',
											)
										);
										?>
										<p><?php esc_html_e('Firewall events - Notify about blocked visitors', 'security-ninja'); ?></p>
											<br>
											<?php
											Wf_Sn::create_toggle_switch(
												'webhook_user_logins',
												array(
													'saved_value' => self::$options['webhook_user_logins'],
													'option_key'  => 'wf_sn_el[webhook_user_logins]',
												)
											);
											?>
										<p><?php esc_html_e('User logins - Notify on failed and successful logins', 'security-ninja'); ?></p>

											<?php
											Wf_Sn::create_toggle_switch(
												'webhook_updates',
												array(
													'saved_value' => self::$options['webhook_updates'],
													'option_key'  => 'wf_sn_el[webhook_updates]',
												)
											);

											?>
										<p><?php esc_html_e('Updates - Notify about WordPress, plugins, and themes updates', 'security-ninja'); ?></p>
										<br>

										<p class="description"><?php esc_html_e('Select the events you want to send as webhooks. Webhooks are sent as POST requests to the specified URL. Each request contains a JSON object with details about the event, enabling you to react or log these events in your system. Note: Changes apply to future events only.', 'security-ninja'); ?></p>
									</fieldset>
								</td>
							</tr>
							<tr valign="top">
								<td colspan="2">
									<p class="submit"><input type="submit" value="<?php esc_html_e('Save Changes', 'security-ninja'); ?>" class="button-primary input-button" name="Submit" /></p>
								</td>
							</tr>
					</table>

				</form>
			</div>

		</div>

<?php
	}



	/**
	 * helper function for $_POST checkbox handling
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @param   mixed   &$values
	 * @param   mixed   $variables
	 * @return  void
	 */
	public static function check_var_isset(&$values, $variables)
	{
		foreach ($variables as $key => $value) {
			if (!isset($values[$key])) {
				$values[$key] = $value;
			}
		}
	}




	/**
	 * activate plugin
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function activate()
	{
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$table_name = $wpdb->prefix . 'wf_sn_el';
		$charset    = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
timestamp datetime NOT NULL,
ip varchar(39) NOT NULL,
user_agent varchar(255) NOT NULL,
user_id int(10) unsigned NOT NULL,
module varchar(32) NOT NULL,
action varchar(64) NOT NULL,
description text NOT NULL,
raw_data blob NOT NULL,
PRIMARY KEY  (id)
) $charset;";

		dbDelta($sql);
		self::default_settings(false);
	}


	/**
	 * clean-up when deactivated
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Friday, January 1st, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function deactivate()
	{
		if (!isset(self::$options['remove_settings_deactivate'])) {
			return;
		}
		if (self::$options['remove_settings_deactivate']) {
			global $wpdb;
			delete_option('wf_sn_el');
			$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . 'wf_sn_el');
		}
	}
}

add_action('plugins_loaded', array(__NAMESPACE__ . '\wf_sn_el', 'init'));
register_activation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_el', 'activate'));
register_deactivation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_el', 'deactivate'));
