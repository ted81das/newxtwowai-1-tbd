<?php

namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_db_table_prefix_check extends wf_sn_af
{

	/**
	 * get_label.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Sunday, October 29th, 2023.
	 * @access	static
	 * @param	mixed	$label	
	 * @return	void
	 */
	static function get_label($label)
	{
		$newprefix = 'wp_' . rand(555, 23232) . '_';
		$labels = array(
			'title'   => esc_html__( 'Change database table prefix', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Knowing the names of your database tables can help an attacker dump the table\'s data and get to sensitive information like password hashes. Since WP table names are predefined, the only way you can change table names is by using a unique prefix. One that\'s different from "wp_" or any similar variation such as "wordpress_". WARNING: Make sure the wp-config.php is writable. Enter your new desired table prefix:', 'security-ninja' ) . ' ' .
						'<input type="text" name="new_table_prefix" value="' . esc_attr( $newprefix ) . '" placeholder="' . esc_attr( $newprefix ) . '" />',
			'msg_ok'  => esc_html__( 'Prefix changed successfully', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Could not change table prefix', 'security-ninja' ),
		);

		if (!array_key_exists($label, $labels)) {
			return '';
		} else {
			return $labels[$label];
		}
	}

	/**
	 * fix.
	 *
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Sunday, October 29th, 2023.
	 * @access	static
	 * @return	boolean
	 */
	public static function fix()
	{
		global $wpdb;
		// Check for nonce for security (assuming a nonce has been created and sent with the form)
		if (!isset($_POST['_ajax_nonce']) || !wp_verify_nonce($_POST['_ajax_nonce'], 'wf_sn_do_fix')) {
			return esc_html__( 'Security check failed.', 'security-ninja' );
			exit();
		}

		// Check if fields are set
		if (!isset($_POST['fields'])) {
			return esc_html__( 'No fields submitted.', 'security-ninja' );
			exit();
		}

		$fields = json_decode(stripslashes($_POST['fields']), true);

		// Sanitize the new table prefix
		$new_table_prefix = isset($fields['new_table_prefix']) ? sanitize_text_field($fields['new_table_prefix']) : '';

		if ('' === $new_table_prefix) {
			$newprefix = 'wp_' . random_int(555, 23232) . '_'; 
		} else {
			// Ensure the prefix ends with an underscore
			$newprefix = rtrim($new_table_prefix, '_') . '_';
		}


		if ($wpdb->prefix != 'wp_') {
			return sprintf( esc_html__( 'Table prefix is already changed to %s. Please reanalyze your website to update the status of this test.', 'security-ninja' ), esc_attr( $wpdb->prefix ) );
		}

		// Check if file can be edited
		global $wp_filesystem;
		include_once ABSPATH . 'wp-admin/includes/file.php';
		// If for some reason the include doesn't work as expected just return false.
		if (!function_exists('WP_Filesystem')) {
			return false;
		}

		$writable = is_writable(wf_sn_af::$wp_config_path);

		if (!$writable) {
			return esc_html__( 'Cannot edit wp-config.php', 'security-ninja' );
			exit();
		}

		// get a list of all tables in the database
		$tables = $wpdb->get_results('SELECT * FROM information_schema.tables WHERE table_schema="' . DB_NAME . '"');

		// filter out all wp_ tables
		$table_names = array();
		foreach ($tables as $table_info) {
			if (strpos($table_info->TABLE_NAME, 'wp_') == 0) {
				$table_names[] = $table_info->TABLE_NAME;
			}
		}

		// for each wp_table make a copy with the new desired prefix
		$failed = false;
		foreach ($table_names as $table) {
			$new_table_name = $newprefix . '_' . substr($table, 3);
			if (false === $wpdb->query('CREATE TABLE `' . $new_table_name . '` LIKE `' . $table . '`')) {
				$failed = true;
				break;
			} elseif (false === $wpdb->query('INSERT INTO `' . $new_table_name . '` SELECT * FROM `' . $table . '`')) {
				$failed = true;
				break;
			}
		}

		// if copying any of the tables failed abort and remove any created tables
		if ($failed) {
			foreach ($table_names as $table) {
				$new_table_name = $newprefix . '_' . substr($table, 3);
				$wpdb->query('DROP TABLE ' . $new_table_name);
			}
			return self::get_label('msg_bad');
		}

		// update prefix in _usermeta and _options
		$wpdb->query('UPDATE `' . $newprefix . '_usermeta` SET meta_key = replace(meta_key, \'wp_\', \'' . $newprefix . '_\') WHERE meta_key LIKE \'wp_%\' ');
		$wpdb->query('UPDATE `' . $newprefix . '_options` SET option_name = replace(option_name, \'wp_\', \'' . $newprefix . '_\') WHERE option_name LIKE \'wp_%\' ');

		// updae wp_config
		$backup_timestamp = time();

		wf_sn_af::backup_file(wf_sn_af::$wp_config_path, $backup_timestamp, 'db_table_prefix_check');
		wf_sn_af::edit_variable(wf_sn_af::$wp_config_path, 'table_prefix', '\'' . $newprefix . '_\'');

		// test if WordPress works, if not restore everything and drop created tables
		$no_wsod = wf_sn_af::test_wordpress_status();

		if (!$no_wsod) {
			wf_sn_af::backup_file_restore(wf_sn_af::$wp_config_path, $backup_timestamp, 'db_table_prefix_check');
			foreach ($table_names as $table) {
				$new_table_name = $newprefix . '_' . substr($table, 3);
				$wpdb->query('DROP TABLE ' . $new_table_name);
			}
			return self::get_label('msg_bad');
		} else {
			foreach ($table_names as $table) {
				$wpdb->query('DROP TABLE ' . $table);
			}
			wf_sn_af::mark_as_fixed('db_table_prefix_check');
			return self::get_label('msg_ok');
		}
	}
}
