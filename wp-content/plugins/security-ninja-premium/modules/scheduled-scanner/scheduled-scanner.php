<?php

namespace WPSecurityNinja\Plugin;

use Error;
use wf_sn_cf;
use Wf_Sn_Cs;
use Wf_Sn_Wl;
use Utils;

if (! function_exists('add_action')) {
	die('Please don\'t open this file directly!');
}


define('WF_SN_SS_OPTIONS_KEY', 'wf_sn_ss');
define('WF_SN_SS_CRON', 'wf_sn_ss_cron');
define('WF_SN_SS_TABLE', 'wf_sn_ss_log');
define('WF_SN_SS_LOG_LIMIT', 50);


class Wf_sn_ss
{




	public static $options = null;

	/**
	 * plugins_loaded.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function plugins_loaded()
	{
		add_filter('cron_schedules', array(__NAMESPACE__ . '\\wf_sn_ss', 'cron_intervals'));
	}


	/**
	 * init.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function init()
	{
		// does the user have enough privileges to use the plugin?
		self::$options = self::get_options();
		if (is_admin()) {


			// add tab to Security Ninja tabs
			add_filter('sn_tabs', array(__NAMESPACE__ . '\\wf_sn_ss', 'sn_tabs'));
			add_action('admin_enqueue_scripts', array(__NAMESPACE__ . '\\wf_sn_ss', 'enqueue_scripts'));
			add_action('sn_overlay_content', array(__NAMESPACE__ . '\\wf_sn_ss', 'overlay_content'));
			add_action('wp_ajax_sn_ss_truncate_log', array(__NAMESPACE__ . '\\wf_sn_ss', 'truncate_log'));
			add_action('wp_ajax_sn_ss_sn_details', array(__NAMESPACE__ . '\\wf_sn_ss', 'ajax_dialog_sn_details'));
			add_action('wp_ajax_sn_ss_cs_details', array(__NAMESPACE__ . '\\wf_sn_ss', 'ajax_dialog_cs_details'));
			add_action('wp_ajax_sn_ss_cs_test', array(__NAMESPACE__ . '\\wf_sn_ss', 'do_cron_task_ajax'));

			add_action('wp_ajax_sn_ss_get_logs', array(__NAMESPACE__ . '\\wf_sn_ss', 'get_logs'));

			// check and set default settings
			// self::default_settings( false ); // @todo?
			add_action('admin_init', array(__NAMESPACE__ . '\\wf_sn_ss', 'register_settings'));
		}
		add_action('wf_sn_ss_cron', array(__NAMESPACE__ . '\\wf_sn_ss', 'do_cron_task'));
	}


	/**
	 * Get log lines via AJAX call
	 *
	 * @author  AI Assistant
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  public
	 * @return  void
	 */
	public static function get_logs()
	{
		check_ajax_referer('secnin_scheduled_scanner', 'nonce');

		if (! current_user_can('manage_options')) {
			wp_send_json_error(esc_html__('You do not have sufficient permissions to access this page.', 'security-ninja'));
		}

		global $wpdb;

		$cache_key = 'wf_sn_ss_logs';

		// Try to get the logs from cache
		$logs = wp_cache_get($cache_key, 'security-ninja');

		if (false === $logs) {
			// If not cached, query the database
			$query = $wpdb->prepare(
				'SELECT * FROM ' . $wpdb->prefix . WF_SN_SS_TABLE . ' ORDER BY timestamp DESC LIMIT %d',
				WF_SN_SS_LOG_LIMIT
			);

			$logs = $wpdb->get_results($query);

			// Store the result in cache
			wp_cache_set($cache_key, $logs, 'security-ninja', 900); // Cache for 15 minutes
		}
		// $logs = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . WF_SN_SS_TABLE . ' ORDER by timestamp DESC LIMIT ' . WF_SN_SS_LOG_LIMIT);

		if ($logs) {
			$html = '';
			foreach ($logs as $log) {
				$tmp = strtotime($log->timestamp);
				$tmp = gmdate(get_option('date_format') . ' ' . get_option('time_format'), $tmp);
				$html .= '<tr>';
				$html .= '<td class="column-primary log-sn-ss-timestamp">' . wp_kses_post($tmp);
				$html .= '<button type="button" class="toggle-row">';
				$html .= '<span class="screen-reader-text">' . esc_html__('Show details', 'security-ninja') . '</span>';
				$html .= '</button>';
				$html .= '</td>';

				$html .= '<td class="log-sn-ss-runtime" data-colname="' . esc_attr__('Run time', 'security-ninja') . '">';
				$html .= esc_html(sprintf(__('%s sec', 'security-ninja'), number_format_i18n($log->runtime, 1)));
				$html .= '</td>';

				$html .= '<td class="log-sn-ss-sn" data-colname="' . esc_html__('Security Tests', 'security-ninja') . '">';
				if (! unserialize($log->sn_results)) {
					$html .= '<i>' . esc_html__('Tests were not run.', 'security-ninja') . '</i>';
				} else {
					if ($log->sn_change) {
						$html .= '<span class="sn-ss-change">' . esc_html__('The results have changed since last scan.', 'security-ninja') . '</span>';
					} else {
						$html .= '<span class="sn-ss-nochange">' . esc_html__('No changes in results since last scan.', 'security-ninja') . '</span>';
					}
					$html .= ' &nbsp;&nbsp;<a href="#" data-timestamp="' . esc_attr($tmp) . '" data-row-id="' . esc_attr($log->id) . '" class="ss-details-sn">' . esc_html__('View details', 'security-ninja') . '</a>';
				}
				$html .= '</td>';

				$html .= '<td class="log-sn-ss-ss" data-colname="' . esc_html__('Core Scanner', 'security-ninja') . '">';
				if (! ($log->cs_results)) {
					$html .= '<i>' . esc_html__('Tests were not run.', 'security-ninja') . '</i>';
				} else {
					if ($log->cs_change) {
						$html .= '<span class="sn-ss-change">' . esc_html__('The results have changed since last scan.', 'security-ninja') . '</span>';
					} else {
						$html .= '<span class="sn-ss-nochange">' . esc_html__('No changes in results since last scan.', 'security-ninja') . '</span>';
					}
					$html .= ' &nbsp;&nbsp;<a href="#" data-timestamp="' . esc_attr($tmp) . '" data-row-id="' . esc_attr($log->id) . '" class="ss-details-cs">' . esc_html__('View details', 'security-ninja') . '</a>';
				}
				$html .= '</td>';
				$html .= '</tr>';
			}
			wp_send_json_success($html);
		} else {
			wp_send_json_error(esc_html__('No logs found.', 'security-ninja'));
		}
	}


	/**
	 * Enqueue scripts and styles for the Scheduled Scanner module.
	 *
	 * This function enqueues the necessary JavaScript file and localizes
	 * script data for the Scheduled Scanner functionality.
	 *
	 * @since 1.0.0
	 * @access public static
	 * @return void
	 */
	public static function enqueue_scripts()
	{
		if (! Wf_Sn::is_plugin_page()) {
			return;
		}

		wp_enqueue_script(
			'sn-ss',
			WF_SN_PLUGIN_URL . 'modules/scheduled-scanner/js/wf-sn-ss-min.js',
			array('jquery'),
			wf_sn::$version,
			true
		);

		wp_localize_script(
			'sn-ss',
			'secninScheduledScanner',
			array(
				'nonce' => wp_create_nonce('secnin_scheduled_scanner'),
			)
		);
	}

	/**
	 * add new tab
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, September 3rd, 2022.
	 * @access  static
	 * @param   mixed   $tabs
	 * @return  mixed
	 */
	public static function sn_tabs($tabs)
	{
		$schedule_tab = array(
			'id'       => 'sn_schedule',
			'class'    => '',
			'label'    => esc_html__('Scheduler', 'security-ninja'),
			'callback' => array(__NAMESPACE__ . '\\wf_sn_ss', 'schedule_page'),
		);
		$done         = 0;

		$tab_count = count($tabs);

		for ($i = 0; $i < $tab_count; $i++) {
			if ('sn_schedule' === $tabs[$i]['id']) {
				$tabs[$i] = $schedule_tab;
				$done       = 1;
				break;
			}
		}

		if (! $done) {
			$tabs[] = $schedule_tab;
		}

		return $tabs;
	}


	/**
	 * add custom message to overlay
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, September 3rd, 2022.
	 * @access  static
	 * @return  void
	 */
	public static function overlay_content()
	{
		echo '<div id="sn-scheduled-scanner" style="display: none;">';
		echo '<h3>' . esc_html__('Security Ninja is testing Scheduled Scanner settings.', 'security-ninja') . '</h3>';
		echo '<div id="sn-timer"></div>';
		echo '</div>';
	}

	/**
	 * set default options
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, September 3rd, 2022.
	 * @access  static
	 * @param   boolean $force  Default: false
	 * @return  array
	 */
	public static function default_settings()
	{
		return array(
			'main_setting'  => '0',
			'scan_schedule' => 'twicedaily',
			'email_report'  => 2,
			'email_to'      => get_bloginfo('admin_email'),
		);
	}




	/**
	 * get_options.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 6th, 2024.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_options()
	{

		if (isset(self::$options) && (0 < count(self::$options))) {

			return self::$options;
		}
		$options = get_option(WF_SN_SS_OPTIONS_KEY, array());

		if (isset($options[0])) {
			unset($options[0]);
		}

		if (! is_array($options)) {
			$options = array();
		}
		$options       = array_merge(self::default_settings(), $options);
		self::$options = $options; // her sætter vi globale options.
		return $options;
	}







	/**
	 * sanitize settings on save
	 * In scheduled-scanner.php
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, September 3rd, 2022.
	 * @version v1.0.1  Wednesday, April 10th, 2024.
	 * @access  static
	 * @global
	 * @param   mixed   $values
	 * @return  mixed
	 */
	public static function sanitize_settings($values)
	{
		// Assuming $values is an associative array of settings to be sanitized and saved
		$sanitized_values = array();
		foreach ($values as $key => $value) {
			// Sanitize each setting based on its expected type
			// This is a simplified example; adjust sanitization methods as needed
			switch ($key) {
				case 'main_setting':
				case 'scan_schedule':
					$sanitized_values[$key] = sanitize_text_field($value);
					break;
				case 'email_report':
					$sanitized_values[$key] = filter_var($value, FILTER_VALIDATE_INT);
					break;
				case 'email_to':
					$sanitized_values[$key] = sanitize_text_field($value);
					break;
			}
		}

		return $sanitized_values;
	}






	/**
	 * register cron event
	 *
	 * @author  Lars Koudal
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, September 3rd, 2022.
	 * @version v1.0.1  Tuesday, May 21st, 2024.
	 * @access  public static
	 * @param   boolean $options    Default: false
	 * @return  void
	 */
	public static function setup_cron()
	{
		$main_setting = self::$options['main_setting'] ?? '0'; // Ensure default if not set.
		$schedule     = self::$options['scan_schedule'] ?? 'twicedaily';

		// Clear any existing cron job.
		wp_clear_scheduled_hook(WF_SN_SS_CRON);

		// Setup or remove cron job based on 'main_setting'.
		if (in_array($main_setting, array('1', '2', '3'), true)) {
			if (! wp_next_scheduled(WF_SN_SS_CRON)) {
				wp_schedule_event(time(), $schedule, WF_SN_SS_CRON);
			}
		}
	}

	/**
	 * add additional cron intervals
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @version v1.0.1  Saturday, November 18th, 2023.
	 * @access  static
	 * @param   mixed   $schedules
	 * @return  mixed
	 */
	public static function cron_intervals($schedules)
	{
		$schedules['monthly'] = array(
			'interval' => DAY_IN_SECONDS * 30,
			'display'  => esc_html__('Once Monthly', 'security-ninja'),
		);
		return $schedules;
	}


	/**
	 * runs cron task
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function do_cron_task_ajax()
	{
		if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'secnin_scheduled_scanner')) {
			wp_send_json_error(__('Nonce verification failed', 'security-ninja'), 403);
		}

		// Check if the user has the required capability
		if (! current_user_can('manage_options')) {
			wp_send_json_error(__('You do not have sufficient permissions to access this page. task_ajax', 'security-ninja'), 403);
		}

		self::do_cron_task();
		wp_send_json_success(array('message' => __('Tasks completed successfully', 'security-ninja')));
	}


	/**
	 * core cron function
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function do_cron_task()
	{
		global $wpdb;
		// $options           = get_option( WF_SN_SS_OPTIONS_KEY );
		$sn_change         = 0;
		$cs_change         = 0;
		$sn_change_details = array();
		$cs_change_details = array();
		$sn_results        = 0;
		$cs_results        = 0;
		$start_time        = microtime(true);

		$old = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . WF_SN_SS_TABLE . ' ORDER BY id DESC LIMIT 1');

		if ('1' === self::$options['main_setting'] || '3' === self::$options['main_setting']) {

			$sn_results = wf_sn::run_all_tests(true);

			if (! $old) {
				$sn_change = 1;
			} else {
				$old_sn_results = unserialize($old->sn_results);

				if ($sn_results && isset($sn_results['test']) && is_array($sn_results['test'])) {


					foreach ($sn_results['test'] as $snname => $testresult) {
						if (is_array($testresult) && isset($old_sn_results['test'][$snname])) {
							$old_testresult = $old_sn_results['test'][$snname];
							if ($testresult !== $old_testresult) {
								$changes          = array();
								$changes['title'] = $testresult['title'];

								if (isset($testresult['status'], $old_testresult['status'])) {
									if ($testresult['status'] !== $old_testresult['status']) {
										$changes['status_new'] = $testresult['status'];
										$changes['status_old'] = $old_testresult['status'];

										if ($changes['status_new'] > $changes['status_old']) {
											$changes['progress'] = 'good';
										} else {
											$changes['progress'] = 'bad';
										}
									}
								}

								// Set a default score if not set in old results
								$old_score = isset($old_testresult['score']) ? $old_testresult['score'] : '';
								if (isset($testresult['score']) && $testresult['score'] !== $old_score) {
									$changes['score_new'] = $testresult['score'];
									$changes['score_old'] = $old_score;
								}

								$changes['msg'] = $testresult['msg'];
								$sn_change = 1;
								$sn_change_details[$snname] = $changes;
							}
						}
					}

					// @todo - figure out WHAT exactly has changed and SHOW IT :-)
				}
			}
		}

		// Core Scanner
		if ('2' === self::$options['main_setting'] || '3' === self::$options['main_setting']) {
			$cs_results = \WPSecurityNinja\Plugin\Wf_Sn_Cs::scan_files(true);

			// Immediately handle the case where $cs_results is '0'
			if (is_array($cs_results)) {
				$keys_to_check = ['missing_ok', 'changed_bad', 'missing_bad', 'changed_bad', 'unknown', 'ok'];
				$change_detected = false;

				foreach ($keys_to_check as $key) {
					if (! empty($cs_results[$key])) {
						$change_detected = true;
						break;
					}
				}

				if ($change_detected) {
					$cs_change = 1;
				} elseif (! $old) {
					$cs_change = 1;
				} else {
					$old_cs_results = maybe_unserialize($old->cs_results);
					if (is_array($old_cs_results)) {
						unset($old_cs_results['last_run'], $old_cs_results['run_time']);
					}
					if (is_array($cs_results)) {
						unset($cs_results['last_run'], $cs_results['run_time']);
					}
					if ($cs_results !== $old_cs_results) {
						$cs_change = 1;
					}
				}
			} else {
				// Handle the case where $cs_results is not an array
				$cs_change = 0; // Assuming non-array $cs_results means no changes or an error
			}
		}

		// write results in database
		$date = current_time('mysql');

		$wpdb->query(
			$wpdb->prepare(
				'INSERT INTO ' . $wpdb->prefix . WF_SN_SS_TABLE .
					' (runtime, timestamp, sn_results, cs_results, sn_change, cs_change)
			VALUES (%s, %s, %s, %s, %s, %s)',
				microtime(true) - $start_time,
				$date,
				serialize($sn_results),
				serialize($cs_results),
				$sn_change,
				$cs_change
			)
		);

		// send report email
		if (self::$options['email_report'] && (0 <> self::$options['email_report'])) {

			$subject = esc_html__('Security status for', 'security-ninja') . ' ' . esc_url(get_home_url()) . ' ' . esc_html__('on', 'security-ninja') . ' ' . wp_date(get_option('date_format') . ' ' . get_option('time_format'));

			$body = esc_html__('Scan ran', 'security-ninja') . ' ' . wp_date(get_option('date_format') . ' ' . get_option('time_format')) . "\r\n";

			// Append additional information with proper escaping and internationalization
			$body .= esc_html__('Run time:', 'security-ninja') . ' ' . esc_html(round(microtime(true) - $start_time, 1)) . ' '. esc_html__('sec', 'security-ninja') . "\r\n";
			$body .= esc_html__('Plugin version:', 'security-ninja') . ' ' . esc_html(\WPSecurityNinja\Plugin\wf_sn::get_plugin_version()) . "\r\n";

			if (! $sn_results) {
				$body .= "\r\n";
				$body .= esc_html__('Security Testing Results: Tests were not run.', 'security-ninja') . "\r\n";
			} elseif ($sn_change) {
				$body .= "\r\n";
				$body .= '<strong>' . esc_html__('Security Testing Results: Results have changed since last scan.', 'security-ninja') . '</strong>' . "\r\n";

				if (is_array($sn_change_details)) {
					foreach ($sn_change_details as $scd) {
						$body .= "\r\n";
						// Ensure dynamic content is escaped properly
						$body .= '<strong>' . esc_html($scd['title']) . '</strong>' . "\r\n";
						$body .= esc_html__('Result:', 'security-ninja') . ' ' . esc_html($scd['msg']) . "\r\n";
					}
				}
			} else {
				$body .= "\r\n";
				$body .= esc_html__('Security Testing Results: No changes since last scan.', 'security-ninja') . "\r\n";
			}
			if (! $cs_results) {
				$body .= "\r\n";
				$body .= esc_html__('Core Scanner results: Test were not run.', 'security-ninja') . "\r\n";
			} elseif ($cs_change) {
				$body .= "\r\n";
				$body .= '<strong>' . esc_html__('Core Scanner results: Results have changed since last scan.', 'security-ninja') . '</strong>' . "\r\n";
			} else {
				$body .= "\r\n";
				$body .= esc_html__('Core Scanner results: No changes since last scan.', 'security-ninja') . "\r\n";
			}
			$body .= "\r\n";

			$dashboardlink       = admin_url('?page=wf-sn');
			$dashboardlinkanchor = esc_html__('Security Ninja Dashboard', 'security-ninja');

			$emailintrotext = esc_html__('Report from a scheduled scan of your website.', 'security-ninja');
			$emailtitle     = $subject;

			$body .= "\r\n";
			$body .= esc_html__('See details here:', 'security-ninja') . ' <a href="' . admin_url('admin.php?page=wf-sn#sn_tests') . '" target="_blank">' . admin_url('admin.php?page=wf-sn#sn_schedule') . '</a>';

			$my_replacements = array(
				'%%emailintrotext%%'      => $emailintrotext,
				'%%websitedomain%%'       => site_url(),
				'%%dashboardlink%%'       => $dashboardlink,
				'%%dashboardlinkanchor%%' => $dashboardlinkanchor,
				'%%secninlogourl%%'       => esc_url(WF_SN_PLUGIN_URL . 'images/security-ninja-logo.png'),
				'%%emailtitle%%'          => $emailtitle,
				'%%sentfromtext%%' => sprintf(
					__('This email was sent by %s from %s', 'security-ninja'),
					esc_html('WP Security Ninja'),
					esc_url(\WPSecurityNinja\Plugin\wf_sn_cf::url_to_domain(site_url()))
				),
				'%%emailcontent%%'        => nl2br($body),
			);

			// inserts the whitelabel name
			if (class_exists(__NAMESPACE__ . '\wf_sn_wl')) {
				if (\WPSecurityNinja\Plugin\wf_sn_wl::is_active()) {
					$pluginname = \WPSecurityNinja\Plugin\wf_sn_wl::get_new_name();
					$my_replacements['%%sentfromtext%%'] = sprintf(
						esc_html__('This email was sent by %1$s from %2$s', 'security-ninja'),
						esc_html($pluginname),
						esc_url(\WPSecurityNinja\Plugin\wf_sn_cf::url_to_domain(site_url()))
					);
				}
			}

			$template_path = WF_SN_PLUGIN_DIR . 'modules/scheduled-scanner/inc/email-default.php';

			$html = file_get_contents($template_path);

			foreach ($my_replacements as $needle => $replacement) {
				$html = str_replace($needle, $replacement, $html);
			}

			$headers = array('Content-Type: text/html; charset=UTF-8');

			$email_addresses = explode(',', self::$options['email_to']);
			$valid_emails    = array();

			foreach ($email_addresses as $email) {
				$email = trim($email);
				if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$valid_emails[] = $email;
				} else {
					// Log invalid email
					\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
						'security_ninja',
						'scheduled_scanner',
						__('Invalid email address skipped', 'security-ninja'),
						array(
							'email' => $email,
						)
					);
				}
			}
			// Send email to each valid recipient
			foreach ($valid_emails as $email) {
				$send_result = wp_mail($email, $subject, $html, $headers);

				// Log the email sending result
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
					'security_ninja',
					'scheduled_scanner',
					__('Sent email notification', 'security-ninja'),
					array(
						'recipient' => $email,
						'result'    => $send_result ? __('Success', 'security-ninja') : __('Failure', 'security-ninja'),
					)
				);
			}

			// Check if there were no valid emails
			if (empty($valid_emails)) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
					'security_ninja',
					'scheduled_scanner',
					__('Email notification failed', 'security-ninja'),
					array(
						'reason' => __('No valid email addresses provided', 'security-ninja'),
					)
				);
			}
		}

		do_action('security_ninja_scheduled_scanner_done_cron', microtime(true) - $start_time);
	}





	/**
	 * all settings are saved in one option key
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function register_settings()
	{
		register_setting(
			WF_SN_SS_OPTIONS_KEY,
			WF_SN_SS_OPTIONS_KEY,
			function ($value) {
				return self::handle_settings_change($value);
			}
		);
	}

	/**
	 * Handle the changes in the settings.
	 * Only update the cron job setup if settings have actually changed.
	 *
	 * @param array $new_settings New settings values.
	 * @return array Returns the sanitized settings.
	 */
	public static function handle_settings_change($new_settings)
	{
		$old_settings = get_option(WF_SN_SS_OPTIONS_KEY, array());
		if ($new_settings !== $old_settings) {
			self::$options = $new_settings;
			self::setup_cron();
		}
		return self::sanitize_settings($new_settings);
	}


	/**
	 * display results
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, November 14th, 2023.
	 * @access  static
	 * @return  void
	 */
	public static function schedule_page()
	{
		$main_settings   = array();
		$main_settings[] = array(
			'val'   => '0',
			'label' => esc_html__('Disable scheduled scans', 'security-ninja'),
		);
		$main_settings[] = array(
			'val'   => '1',
			'label' => esc_html__('Enable scheduled scans only for Security Testing', 'security-ninja'),
		);
		$main_settings[] = array(
			'val'   => '2',
			'label' => esc_html__('Enable scheduled scans only for Core Scanner', 'security-ninja'),
		);
		$main_settings[] = array(
			'val'   => '3',
			'label' => esc_html__('Enable scheduled scans for both', 'security-ninja'),
		);

		$scan_schedule = array();
		$tmp           = wp_get_schedules();
		foreach ($tmp as $name => $details) {
			if ('twicedaily' === $name) {
				$scan_schedule[] = array(
					'val'   => $name,
					'label' => $details['display'] . ' ' . esc_html__('(recommended)', 'security-ninja'),
				);
			} else {
				$scan_schedule[] = array(
					'val'   => $name,
					'label' => $details['display'],
				);
			}
		}

		$email_reports   = array();
		$email_reports[] = array(
			'val'   => 0,
			'label' => esc_html__('Never send any emails', 'security-ninja'),
		);
		$email_reports[] = array(
			'val'   => 1,
			'label' => esc_html__('Send an email each time the tests run', 'security-ninja'),
		);
		$email_reports[] = array(
			'val'   => 2,
			'label' => esc_html__('Send an email only when test results change', 'security-ninja'),
		);

		echo '<div class="submit-test-container"><div class="card">';
		if (isset(self::$options['main_setting']) && in_array(self::$options['main_setting'], array('1', '2', '3'), true)) {
			$tmp = wp_get_schedules();
			$tmp = '<p class="sn-ss-nochange">' . esc_html__('Scheduled scans are enabled and will run', 'security-ninja') . ' ' . strtolower($tmp[self::$options['scan_schedule']]['display']) . '</p>';
			// Retrieve the next scheduled time for the cron job

			$timestamp    = wp_next_scheduled(WF_SN_SS_CRON);
			$current_time = current_time('timestamp');

			if ($timestamp) {
				$time_until_next = human_time_diff($current_time, $timestamp);
				$missed_cron     = ($timestamp < $current_time);

				$next_scan_at = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);

				
			} else {
				echo '<p>' . esc_html__('Scheduled scans are not currently set.', 'security-ninja') . '</p>';
			}
		} else {
			$tmp = '<span class="sn-ss-change">' . esc_html__('Scheduled scans are disabled', 'security-ninja') . '</span>';
		}

		echo '<form action="options.php" method="post">';
		settings_fields('wf_sn_ss');

		echo '<p class="ss_header">' . wp_kses_post($tmp) . '</p>';

?>
		<table class="form-table">
			<tbody>
				<?php
				echo '<tr valign="top">
		<th scope="row"><label for="main_setting">' . esc_html__('Scan Settings', 'security-ninja') . '</label></th>
		<td><select id="main_setting" name="wf_sn_ss[main_setting]" class="regular-text">';
		\WPSecurityNinja\Plugin\Utils::create_select_options($main_settings, self::$options['main_setting']);
				echo '</select>';
				echo '<p class="description">' . esc_html__('Depending on the add-ons that are active you can choose to include them in scheduled scans or not.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top">
		<th scope="row"><label for="scan_schedule">' . esc_html__('Scan Schedule', 'security-ninja') . '</label></th>
		<td><select id="scan_schedule" name="wf_sn_ss[scan_schedule]" class="regular-text">';
		\WPSecurityNinja\Plugin\Utils::create_select_options($scan_schedule, self::$options['scan_schedule']);
				echo '</select>';
				echo '<p class="description">' . esc_html__("Running the scan once a day will ensure you get a prompt notice of any problems and at the same time don't overload the server.", 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top">
		<th scope="row"><label for="email_report">' . esc_html__('Email Report', 'security-ninja') . '</label></th>
		<td><select id="email_report" name="wf_sn_ss[email_report]" class="regular-text">';
				\WPSecurityNinja\Plugin\Utils::create_select_options($email_reports, self::$options['email_report']);
				echo '</select>';
				echo '<p class="description">' . esc_html__('Depending on the amount of email you like to receive you can get reports for all scans or just ones when results change.', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top">
		<th scope="row"><label for="email_to">' . esc_html__('Email Recipient', 'security-ninja') . '</label></th>
		<td><input type="text" class="regular-text" id="email_to" name="wf_sn_ss[email_to]" value="' . self::$options['email_to'] . '" />';
				echo '<p class="description">' . esc_html__("Email address of the person (usually the site admin) who'll receive the email reports.", 'security-ninja') . '</p>';
				echo '<p class="description">' . esc_html__('Separate multiple recipients with a comma ","', 'security-ninja') . '</p>';
				echo '</td></tr>';

				echo '<tr valign="top"><td colspan="2" style="padding:0px;">';
				echo '<p class="submit"><input type="submit" value="Save Changes" class="input-button button-primary" name="Submit" />';
				echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="button" value="Test settings (run scan)" class="input-button gray button-secondary" id="sn-ss-test" /></p>';
				?>
				</td>
				</tr>
		</table>
		</form>
		<?php

		if (!isset($timestamp)) {
			$timestamp    = wp_next_scheduled(WF_SN_SS_CRON);
		}
		if ($timestamp) {
			$time_until_next = human_time_diff($current_time, $timestamp);

			$next_scan_at = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $timestamp);

			$current_server_time = date_i18n(get_option('date_format') . ' ' . get_option('time_format'), $current_time);
			echo "<table>";
			echo '<tr><td>' . esc_html__('Server Time:', 'security-ninja') . '</td><td>' . esc_html($current_server_time) . '</td></tr>';

			echo '<tr><td>' . esc_html__('Next Scheduled Scan:', 'security-ninja') . ' (wf_sn_ss_cron)</td><td>' . esc_html($next_scan_at) . ' (' . esc_html($time_until_next) . ' ' . esc_html__('From now', 'security-ninja') . ')</td></tr></table>';
		}

		echo '<div id="wf-ss-output" style="display: none;" class="card">';
		echo '<p>' . esc_html__('Security Ninja is testing Scheduled Scanner settings.', 'security-ninja') . ' <span id="sn-timer"></span></p>';
		echo '</div>';

		self::log_list();
		?>
		<p><?php esc_html_e('Please read!', 'security-ninja'); ?></p>
		<p><?php esc_html_e('WordPress cron function depends on site visitors to regularly run its tasks. If your site has very few visitors the tasks wont be run on a regular, predefined interval.', 'security-ninja'); ?></p>

		<?php
		$url = 'https://wp.tutsplus.com/articles/insights-into-wp-cron-an-introduction-to-scheduling-tasks-in-wordpress/';

		echo wp_kses(
			sprintf(
				__('Wptuts+ has a great <a href="%s" target="_blank">article</a> explaining how to make sure the cron does run even if you have very few visitors.', 'security-ninja'),
				esc_url($url)
			),
			array(
				'a' => array(
					'href'   => array(),
					'target' => array(),
				),
				'p' => array(),
			)
		);
		?>
		<p><?php esc_html_e("Please test the settings after changing them to ensure you're getting the emails and that the testing finish in a timely manner.", 'security-ninja'); ?></p>
		</div>
		</div>
	<?php
	}











	/**
	 * log_list.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @version v1.0.1  Saturday, September 3rd, 2022.
	 * @access  static
	 * @return  void
	 */
	public static function log_list()
	{
		global $wpdb;

		$logs = $wpdb->get_results('SELECT * FROM ' . $wpdb->prefix . WF_SN_SS_TABLE . ' ORDER by timestamp DESC LIMIT ' . WF_SN_SS_LOG_LIMIT);

	?>
		<h3><?php esc_html_e('Scan log', 'security-ninja'); ?></h3>

		<table class="wp-list-table widefat striped" cellspacing="0" id="wf-sn-ss-log">
			<thead>
				<tr>
					<th id="header_time" class="column-primary"><?php esc_html_e('Timestamp', 'security-ninja'); ?></th>
					<th id="header_runtime"><?php esc_html_e('Run time', 'security-ninja'); ?></th>
					<th id="header_sn"><?php esc_html_e('Security Tests', 'security-ninja'); ?></th>
					<th id="header_ss"><?php esc_html_e('Core Scanner', 'security-ninja'); ?></th>
				</tr>
			</thead>
			<tbody></tbody>
		</table>
<?php

		echo '<p><input type="button" value="' . esc_html__('Delete all log entries', 'security-ninja') . '" class="button button-secondary" id="wf-sn-ss-truncate-log"></p>';
	}









	/**
	 * truncate scan log table
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @version v1.0.1  Wednesday, April 10th, 2024.
	 * @access  static
	 * @return  void
	 */
	public static function truncate_log()
	{
		if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'secnin_scheduled_scanner')) {
			wp_send_json_error('Nonce verification failed', 403);
		}
		if (! current_user_can('manage_options')) {
			wp_send_json_error('You do not have sufficient permissions to access this page. truncate', 403);
		}
		global $wpdb;
		$wpdb->query('TRUNCATE TABLE ' . $wpdb->prefix . WF_SN_SS_TABLE);
		wp_send_json_success();
	}


	/**
	 * display dialog with SN test details
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @access  static
	 * @return  void
	 */
	public static function ajax_dialog_sn_details()
	{

		if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'secnin_scheduled_scanner')) {
			wp_send_json_error('Nonce verification failed', 403);
		}

		if (! current_user_can('manage_options')) {
			wp_send_json_error('You do not have sufficient permissions to access this page. sn_details', 403);
		}
		global $wpdb;

		$id = isset($_POST['row_id']) ? intval($_POST['row_id']) : 0;

		$table_name = $wpdb->prefix . WF_SN_SS_TABLE; // Assuming WF_SN_SS_TABLE is a defined constant holding your table's suffix.
		$result     = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d LIMIT 1", $id));

		if ($result->sn_results && is_array(unserialize($result->sn_results))) {
			echo '<table class="wp-list-table widefat" cellspacing="0" id="security-ninja">';
			echo '<thead><tr>';
			echo '<th class="sn-status">' . __('Status', 'security-ninja') . '</th>';
			echo '<th>' . __('Test description', 'security-ninja') . '</th>';
			echo '<th>' . __('Test results', 'security-ninja') . '</th>';
			echo '</tr></thead>';
			echo '<tbody>';

			$tmp = unserialize($result->sn_results);

			foreach ($tmp['test'] as $test_name => $details) {
				echo '<tr>
				<td class="sn-status">' . wp_kses_post(wf_sn::status($details['status'])) . '</td>
				<td>' . esc_attr($details['title']) . '</td>
				<td>' . esc_attr($details['msg']) . '</td>
				</tr>';
			}

			echo '</tbody>';
			echo '<tfoot><tr>';
			echo '<th class="sn-status">' . esc_html__('Status', 'security-ninja') . '</th>';
			echo '<th>' . esc_html__('Test Description', 'security-ninja') . '</th>';
			echo '<th>' . esc_html__('Test Results', 'security-ninja') . '</th>';
			echo '<th>&nbsp;</th>';
			echo '</tr></tfoot>';
			echo '</table>';
		} else {
			echo esc_html__('Unknown Error.', 'security-ninja');
		}

		die();
	}


	/**
	 * displays dialog with core scanner details
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @access  static
	 * @param   boolean $return         Default: false
	 * @param   boolean $hidebuttons    Default: false
	 * @return  mixed
	 */
	public static function ajax_dialog_cs_details($return_output = false)
	{
		global $wpdb;

		if (! isset($_POST['nonce']) || ! wp_verify_nonce($_POST['nonce'], 'secnin_scheduled_scanner')) {
			wp_send_json_error(__('Nonce verification failed', 'security-ninja'), 403);
		}

		// Check if the user has the required capability
		if (! current_user_can('manage_options')) {
			wp_send_json_error(__('You do not have sufficient permissions to access this page. cs_details', 'security-ninja'), 403);
		}

		$output = '';

		if (isset($_POST['row_id'])) {
			$id     = (int) $_POST['row_id'];
			$result = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}" . WF_SN_SS_TABLE . ' WHERE id = %d LIMIT 1', $id));
		} else {
			$result = $wpdb->get_row('SELECT * FROM ' . $wpdb->prefix . WF_SN_SS_TABLE . ' ORDER BY `id` DESC LIMIT 1;');
		}

		if ((isset($result->cs_results)) && (is_array(unserialize($result->cs_results)))) {

			$results = unserialize($result->cs_results);

			$output .= '<div style="margin: 20px">';
			if ($results['changed_bad']) {
				$output .= '<div class="sn-cs-changed-bad"><h4>' . esc_html__('The following WordPress core files have been modified', 'security-ninja') . '</h4><p>' . esc_html__('If you did not modify the following files, you should review them to make sure no malicious code is there.', 'security-ninja') . '</p>';
				$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['changed_bad'], false, false);
				$output .= '</div>';
			}

			if ($results['unknown_bad']) {
				$output .= '<div class="sn-cs-changed-bad"><h4>' . esc_html__('Following files are unknown and should not be in your core folders', 'security-ninja') . '</h4><p>' . esc_html__('These are files not included with WordPress default installation and should not be in your core WordPress folders.', 'security-ninja') . '</p>';
				if ($return_output) {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['unknown_bad'], false, false, false);
				} else {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['unknown_bad'], true, false, true);
				}
				$output .= '</div>';
			}

			if ($results['missing_bad']) {
				$output .= '<div class="sn-cs-missing-bad">';
				$output .= '<h4>' . esc_html__('Following core files are missing and they should not be.', 'security-ninja') . '</h4>';
				$output .= '<p>' . esc_html__('Missing core files my indicate a bad auto-update or they simply were not copied on the server when the site was setup.', 'security-ninja') . '<br>' . esc_html__('If there is no legitimate reason for the files to be missing use the restore action to create them.', 'security-ninja') . '</p>';
				if ($return_output) {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['missing_bad'], false, false, false);
				} else {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['missing_bad'], false, false, false);
				}
				$output .= '</div>';
			}


			if ($results['missing_ok']) {
				$output .= '<div class="sn-cs-missing-ok">';
				$output .= '<h4>' . esc_html__('The following core files are missing but are not essential for WordPress functionality:', 'security-ninja') . '</h4>';
				$output .= '<p>' . esc_html__('These files are part of the WordPress core but are not necessary for the basic operation of your site. Their absence does not indicate a security issue.', 'security-ninja') . '</p>';
				if ($return_output) {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['missing_ok'], false, false, false);
				} else {
					$output .= \WPSecurityNinja\Plugin\Wf_Sn_Cs::list_files($results['missing_ok'], false, false, false);
				}
				$output .= '</div>';
			}


			if (0 === intval($result->cs_change)) {
				$output .= '<div class="sn-cs-ok">';

				$output .= '<h4>' . sprintf(
					__('A total of <span class="sn_count">%1$s</span> files were scanned and <span class="sn_count">%2$s</span> are unmodified and safe.', 'security-ninja'),
					number_format_i18n($results['total']),
					number_format_i18n($results['total'])
				) . '</h4>';
				$output .= '</div>';
			}
		} else {
			$output .= '<p>' . sprintf(
				__('Problem loading Core Scanner Results - %s', 'security-ninja'),
				esc_html__('Undocumented error.', 'security-ninja')
			) . '</p>';
		}

		$output .= '</div>';

		if ($return_output) {
			return $output;
		}
		echo wp_kses_post($output);
		wp_die();
	}


	/**
	 * activate plugin
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @access  static
	 * @return  void
	 */
	public static function activate()
	{
		// create table
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset         = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . WF_SN_SS_TABLE;

		$sql = "CREATE TABLE $table_name (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			timestamp datetime NOT NULL,
			runtime float NOT NULL,
			sn_results text,
			cs_results text,
			sn_change tinyint(4) NOT NULL,
			cs_change tinyint(4) NOT NULL,
			PRIMARY KEY  (id)
		) $charset;";
		dbDelta($sql);
	}

	/**
	 * clean-up when deactivated
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, May 16th, 2022.
	 * @access  static
	 * @return  void
	 */
	public static function deactivate()
	{
		global $wpdb;
		wp_clear_scheduled_hook(WF_SN_SS_CRON);
		delete_option(WF_SN_SS_OPTIONS_KEY);
		$wpdb->query('DROP TABLE IF EXISTS ' . $wpdb->prefix . WF_SN_SS_TABLE);
	}
}

add_action('plugins_loaded', array(__NAMESPACE__ . '\wf_sn_ss', 'init'));
add_action('plugins_loaded', array(__NAMESPACE__ . '\wf_sn_ss', 'plugins_loaded'));
register_activation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_ss', 'activate'));
register_deactivation_hook(WF_SN_BASE_FILE, array(__NAMESPACE__ . '\wf_sn_ss', 'deactivate'));
