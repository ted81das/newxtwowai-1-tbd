<?php
namespace WPSecurityNinja\Plugin;

if ( ! function_exists( 'add_action' ) ) {
	die( 'Please don\'t open this file directly!' );
}

require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_application_passwords.php';

require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/core_upgrader.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_anyone_can_register.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_blog_site_url_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_bruteforce_login.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_check_failed_login_info.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_config_chmod.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_config_location.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_core_updates_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_db_table_prefix_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_deactivated_plugins.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_deactivated_themes.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_debug_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_file_editor.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_plugins_ver_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_rpc_meta.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_salt_keys_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_script_debug_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_themes_ver_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_uploads_browsable.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_user_exists.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_ver_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_wlw_meta.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_wp_header_meta.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/plugin_upgrader.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/theme_upgrader.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_id1_user_check.php';
require_once WF_SN_PLUGIN_DIR . 'modules/auto-fixer/fixers/fix_usernames_enumeration.php';


class Wf_sn_af {
	public static $wp_config_path = '';
	public static $hashed_files   = array();
	public static $af_options     = array();

	public static function init() {
		self::$af_options = get_option( 'wf_sn_af' );

		// @todo - use wp_filesystem
		if ( file_exists( ABSPATH . 'wp-config.php' ) ) {
			self::$wp_config_path = ABSPATH . 'wp-config.php';
		} elseif ( @file_exists( dirname( ABSPATH ) . '/wp-config.php' ) && ! @file_exists( dirname( ABSPATH ) . '/wp-settings.php' ) ) {
			self::$wp_config_path = dirname( ABSPATH ) . '/wp-config.php';
		}

		if ( is_admin() ) {
			add_action( 'wp_ajax_sn_af_get_fix_info', array( __NAMESPACE__ . '\\wf_sn_af', 'get_fix_info_ajax' ) );
			add_action( 'wp_ajax_sn_af_do_fix', array( __NAMESPACE__ . '\\wf_sn_af', 'do_fix_ajax' ) );
			add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\\wf_sn_af', 'enqueue_scripts' ) );

		}

		add_action( 'init', array( __NAMESPACE__ . '\\wf_sn_af', 'schedule_cron_jobs' ) );
		add_action( 'wf_sn_af_cleanup_backups', __NAMESPACE__ . '\\wf_sn_af::cleanup_backups' );

		add_action( 'wp_ajax_nopriv_wf_sn_af_test_wp', array( __NAMESPACE__ . '\\wf_sn_af', 'test_wordpress_status_request' ) );

		if ( ! empty( wf_sn_af::$af_options['sn-hide-wp-login-info'] ) ) {
				add_filter( 'login_errors', array( __NAMESPACE__ . '\\wf_sn_af', 'hide_login_info' ) );
		}

		if ( ! empty( self::$af_options['sn-hide-rpc-meta'] ) ) {
			remove_action( 'wp_head', 'rsd_link' );
			add_filter( 'xmlrpc_enabled', '__return_false' );
		}
	}


	/**
	 * cleanup_backups.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Sunday, October 29th, 2023.
	 * @access  public static
	 * @return  void
	 */
	public static function cleanup_backups() {
		$backup_folder = WP_CONTENT_DIR . '/sn-backups/';

		// Check if the folder exists
		if ( ! is_dir( $backup_folder ) ) {
				return;
		}

		// Get all backup files in the folder
		$backup_files = glob( $backup_folder . '*.wfbkp' );

		// Sort files by modification time, newest first
		usort(
			$backup_files,
			function ( $a, $b ) {
				return filemtime( $b ) - filemtime( $a );
			}
		);

		// If there are less than 15 files, return
		if ( count( $backup_files ) <= 15 ) {
				return;
		}
		$delcount = 0;
		// Remove files, keeping only the last 15
		foreach ( array_slice( $backup_files, 15 ) as $file_to_delete ) {
				unlink( $file_to_delete );
				++$delcount;
		}

		if ( 1 < $delcount ) {
			if ( secnin_fs()->is__premium_only() ) {
				if ( secnin_fs()->can_use_premium_code() ) {
					wf_sn_el_modules::log_event( 'security_ninja', 'auto_fixer', esc_html__( 'Cleaned /sn-backups/ folder ', 'security-ninja' ), '' );
				}
			}
		}
	}


	/**
	 * schedule_cron_jobs.
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Sunday, October 29th, 2023.
	 * @access  public static
	 * @return  void
	 */
	public static function schedule_cron_jobs() {
		if ( ! wp_next_scheduled( 'wf_sn_af_cleanup_backups' ) ) {
			wp_schedule_event( time() + 10, 'daily', 'wf_sn_af_cleanup_backups' );
		}
	}




	/**
	 * update options key
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, November 17th, 2021.
	 * @access  public static
	 * @param   mixed   $key
	 * @param   mixed   $value
	 * @return  mixed
	 */
	public static function update_option( $key, $value ) {
		self::$af_options[ $key ] = $value;
		return update_option( 'wf_sn_af', self::$af_options, false );
	}


	/**
	 * enqueue CSS and JS scripts on plugin's admin page
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, November 17th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function enqueue_scripts() {

		if ( wf_sn::is_plugin_page() ) {
			$plugin_url = plugin_dir_url( __FILE__ );

			wp_enqueue_script( 'sn-af-js', $plugin_url . 'js/wf-sn-af-min.js', array( 'jquery' ), wf_sn::$version, true );
			$js_vars = array(
				'nonce_get_fix_info' => wp_create_nonce( 'wf_sn_get_fix_info' ),
				'nonce_do_fix'       => wp_create_nonce( 'wf_sn_do_fix' ),
			);
			wp_localize_script( 'sn-af-js', 'wf_sn_af', $js_vars );
		}
	}


	/**
	 * see if we have a fix for that test and if we can apply
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function get_fix_info_ajax() {
		check_ajax_referer( 'wf_sn_get_fix_info' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed.', 'security-ninja' ),
				)
			);
		}

		if ( ! isset( $_GET['test_id'] ) ) {
			$data = array( 'message' => __( 'No test ID parsed', 'security-ninja' ) );
			wp_send_json_error( $data );
		}

		$test_id = sanitize_key( $_GET['test_id'] );

		$test_status = false;

		if ( isset( $_GET['test_status'] ) ) {
			$test_status = (int) $_GET['test_status'];
		}
		$out = '';

		if ( ! class_exists( __NAMESPACE__ . '\wf_sn_af_fix_' . $test_id ) ) {

			wp_send_json_success( __( 'Unfortunately, auto fix is not available for this test. Please read the instructions above to learn more about the test and how to resolve issues related to it.', 'security-ninja' ) );
		}

		if ( $test_status > 5 ) {
			$out .= __( 'There is nothing to fix for this test. It passed with flying colors.', 'security-ninja' );
		} elseif ( $test_status > 0 ) {
			$out .= __( "Unfortunately, automatic fix can't be applied.", 'security-ninja' );
		} else {
			$namespace      = __NAMESPACE__;
				$class_name = $namespace ? $namespace . '\\wf_sn_af_fix_' . $test_id : 'wf_sn_af_fix_' . $test_id;
			if ( class_exists( $class_name ) ) {

				$out .= '<p>' . call_user_func( array( $class_name, 'get_label' ), 'info' ) . '</p>';

				if ( call_user_func( array( $class_name, 'get_label' ), 'fixable' ) ) {
					$out .= '<a data-test-id="' . $test_id . '" href="#" class="button button-primary sn_af_run_fix">' . __( 'Apply Fix', 'security-ninja' ) . '</a>';
				}
			} else {
				$out .= __( 'No automatic fix available.', 'security-ninja' );
			}
		}

		wp_send_json_success( $out );
	}





	/**
	 * see if we have a fix for that test and if we can apply
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @version v1.0.1  Wednesday, April 13th, 2022.
	 * @access  public static
	 * @return  void
	 */
	public static function do_fix_ajax() {
		check_ajax_referer( 'wf_sn_do_fix' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Failed.', 'security-ninja' ),
				)
			);
		}

		if ( ! isset( $_POST['test_id'] ) ) {
			$data = array( 'message' => 'No test ID parsed' );
			wp_send_json_error( $data );
		}
		$test_id = sanitize_key( $_POST['test_id'] );
		$namespace  = 'WPSecurityNinja\\Plugin';
		$class_name = $namespace ? $namespace . '\\wf_sn_af_fix_' . $test_id : 'wf_sn_af_fix_' . $test_id;
	
		if (class_exists($class_name)) {
				if (method_exists($class_name, 'fix')) {		
						// Call the 'fix' method
						$result = call_user_func(array($class_name, 'fix'));
						// Send a success response
						wp_send_json_success($result);
				} else {
						wp_send_json_error(__('Fix method not available', 'security-ninja'));
				}
		} else {
				wp_send_json_error(__('Fix not available', 'security-ninja'));
		}
	}




	/**
	 * hide_login_info.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @return  mixed
	 */
	public static function hide_login_info() {
		return __( 'Wrong username or password.', 'security-ninja' );
	}


	/**
	 * remove_define.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $file_path
	 * @param   mixed   $constant
	 * @return  void
	 */
	public static function remove_define( $file_path, $constant ) {
		// @todo WP filesystem
		$file_contents = @file_get_contents( $file_path );
		if ( $file_contents && preg_match_all( '/define\(\s*[\'|"]' . $constant . '[\'|"]\s*,\s*(false|true|[\'|"].*[\'|"])\s*\);/i', $file_contents, $matches ) ) {
			$file_contents = str_replace( $matches[0], '', $file_contents );
			return file_put_contents( $file_path, $file_contents, LOCK_EX );
		} else {
			return false;
		}
	}





	/**
	 * update_ini_set. only works with wp-config.php
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Wednesday, April 6th, 2022.	
	 * @version	v1.0.1	Tuesday, April 2nd, 2024.
	 * @access	public static
	 * @param	mixed	$file_path	
	 * @param	mixed	$constant 	
	 * @param	mixed	$new_value	
	 * @return	void
	 */
	public static function update_ini_set( $file_path, $setting, $value ) {
    global $wp_filesystem;

    if ( empty( $wp_filesystem ) ) {
        require_once( ABSPATH . 'wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    // Check if the file exists and is writable.
    if ( ! $wp_filesystem->exists( $file_path ) || ! $wp_filesystem->is_writable( $file_path ) ) {
        return new \WP_Error( 'file_not_writable', __( 'The file does not exist or is not writable.', 'security-ninja' ) );
    }

    $content    = $wp_filesystem->get_contents( $file_path );
    $lines      = explode( "\n", $content );
    $new_lines  = [];
    $found      = false;

    foreach ( $lines as $line ) {
        // Enhanced regex pattern to match variations of ini_set(), including those prefixed with @ and different quote styles
        if ( preg_match( '/^\s*@?\s*ini_set\s*\(\s*[\'"]' . preg_quote( $setting, '/' ) . '[\'"]\s*,/i', $line ) ) {
            if ( $found ) {
                // Skip adding this line if the setting has already been found, removing duplicates
                continue;
            }
            // Format the value correctly for true, false, or other values
            $formatted_value = is_bool($value) ? ($value ? 'true' : 'false') : (is_numeric($value) ? $value : '\'' . addslashes($value) . '\'');
            $line = 'ini_set( \'' . $setting . '\', ' . $formatted_value . ' );';
            $found = true;
        }
        $new_lines[] = $line;
    }

    // If the setting was not found, add it at the end.
    if ( ! $found ) {
        $formatted_value = is_bool($value) ? ($value ? 'true' : 'false') : (is_numeric($value) ? $value : '\'' . addslashes($value) . '\'');
        $new_lines[] = 'ini_set( \'' . $setting . '\', ' . $formatted_value . ' );';
    }

    $new_content = implode( "\n", $new_lines );

    if ( ! $wp_filesystem->put_contents( $file_path, $new_content, FS_CHMOD_FILE ) ) {
        return new \WP_Error( 'write_error', __( 'Error writing to file.', 'security-ninja' ) );
    }

    return true;
}



	/**
	 * update_define.
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, January 25th, 2021.	
	 * @version	v1.0.1	Tuesday, April 2nd, 2024.
	 * @access	public static
	 * @param	mixed	$file_path	
	 * @param	mixed	$constant 	
	 * @param	mixed	$new_value	
	 * @return	void
	 */
	public static function update_define( $file_path, $constant, $value ) {
    global $wp_filesystem;

    if ( empty( $wp_filesystem ) ) {
        WP_Filesystem();
    }

    // Check if the file exists and is writable.
    if ( ! $wp_filesystem->exists( $file_path ) || ! $wp_filesystem->is_writable( $file_path ) ) {
        return new \WP_Error( 'file_not_writable', __( 'The file does not exist or is not writable.', 'security-ninja' ) );
    }

    $content    = $wp_filesystem->get_contents( $file_path );
    $lines      = explode( "\n", $content );
    $new_lines  = array();
    $found      = false;

    foreach ( $lines as $line ) {
        if ( preg_match( '/^\s*define\s*\(\s*[\'"]' . preg_quote( $constant, '/' ) . '[\'"]\s*,\s*(.*)\);/i', $line ) ) {
            if ( $found ) {
                // Skip adding this line if the constant has already been found.
                continue;
            }
            $line  = 'define( \'' . $constant . '\', ' . ( is_bool( $value ) ? ( $value ? 'true' : 'false' ) : '"' . addslashes( $value ) . '"' ) . ' );';
            $found = true;
        }
        $new_lines[] = $line;
    }

    // If the constant was not found, add it.
    if ( ! $found ) {
        $new_lines[] = 'define( \'' . $constant . '\', ' . ( is_bool( $value ) ? ( $value ? 'true' : 'false' ) : '"' . addslashes( $value ) . '"' ) . ' );';
    }

    $new_content = implode( "\n", $new_lines );

    if ( ! $wp_filesystem->put_contents( $file_path, $new_content, FS_CHMOD_FILE ) ) {
        return new \WP_Error( 'write_error', __( 'Error writing to file.', 'security-ninja' ) );
    }

    return true;
}
	 



	/**
	 * edit_variable.
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, January 25th, 2021.	
	 * @version	v1.0.1	Tuesday, April 2nd, 2024.
	 * @access	public static
	 * @param	mixed	$file_path	
	 * @param	mixed	$variable 	
	 * @param	mixed	$new_value	
	 * @return	boolean
	 */
	public static function edit_variable( $file_path, $variable, $new_value ) {
    global $wp_filesystem;

    // Initialize the WordPress filesystem, no more using 'file-put-contents' function directly
    if ( empty( $wp_filesystem ) ) {
        require_once ( ABSPATH . '/wp-admin/includes/file.php' );
        WP_Filesystem();
    }

    // Check if the file exists and is writable
    if ( $wp_filesystem->exists( $file_path ) && $wp_filesystem->is_writable( $file_path ) ) {
        // Read the contents of the file
        $file_contents = $wp_filesystem->get_contents( $file_path );

        // Check if the variable exists in the file and replace its value
        if ( preg_match_all( '/(\$' . preg_quote($variable, '/') . ')\s*=\s*(.*?);/i', $file_contents, $matches ) ) {
            $full_expression     = $matches[0][0];
            $replaced_expression = str_replace( $matches[2][0], $new_value, $full_expression );
            $file_contents       = str_replace( $full_expression, $replaced_expression, $file_contents );

            // Write the new contents back to the file
            $wp_filesystem->put_contents( $file_path, $file_contents, FS_CHMOD_FILE );
            return true;
        }
    }

    return false;
}


	/**
	 * find_ini_set_in_folder.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $folder
	 * @param   mixed   $directive
	 * @param   mixed   $values
	 * @return  mixed
	 */
	public static function find_ini_set_in_folder( $folder, $directive, $values ) {
		if ( is_array( $values ) ) {
			$values = implode( '|', $values );
		}
		$pattern = '/ini_set\([\'|"]\s*(' . $directive . ')\s*[\'|"]\s*,\s*[\'|"|\s*]*(' . $values . ')[\'|"|\s*]*\s*\);/i';

		$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $folder ) );

		$results = array();
		foreach ( $files as $filename => $object ) {
			// @todo
			$content = @file_get_contents( $filename );
			if ( $content && preg_match_all( $pattern, $content, $matches ) ) {
				foreach ( $matches[0] as $match ) {
					$results[ self::get_string_line( $filename, $match ) ] = $filename;
				}
			}
		}
		return $results;
	}


	/**
	 * get_string_line.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $file_path
	 * @param   mixed   $string
	 * @return  integer
	 */
	public static function get_string_line( $file_path, $search_for ) {
		$lines = file( $file_path );
		foreach ( $lines as $line_number => $line ) {
			if ( strpos( $line, $search_for ) !== false ) {
				return $line_number;
			}
		}
		return -1;
	}


	/**
	 * Backs up a file
	 *
	 * @author  Lars Koudal
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @version v1.0.1  Sunday, October 29th, 2023.
	 * @access  public static
	 * @param   mixed   $file_path
	 * @param   mixed   $backup_timestamp
	 * @param   mixed   $fix
	 * @return  void
	 */
	public static function backup_file( $file_path, $backup_timestamp, $fix ) {

		// @todo - use wp_filesystem
		if ( ! is_dir( WP_CONTENT_DIR . '/sn-backups/' ) ) {
			mkdir( WP_CONTENT_DIR . '/sn-backups/', 0755 );
			chmod( WP_CONTENT_DIR . '/sn-backups/', 0755 );
		}

		copy( $file_path, WP_CONTENT_DIR . '/sn-backups/' . basename( $file_path ) . '_' . $backup_timestamp . '_' . esc_attr( $fix ) . '.wfbkp' );
	}


	/**
	 * backup_file_restore.
	 *
	 * @author	Lars Koudal
	 * @author	Unknown
	 * @since	v0.0.1
	 * @version	v1.0.0	Monday, January 25th, 2021.	
	 * @version	v1.0.1	Sunday, April 7th, 2024.	
	 * @version	v1.0.2	Wednesday, May 15th, 2024.
	 * @access	public static
	 * @param	mixed	$file_path       	
	 * @param	mixed	$backup_timestamp	
	 * @param	mixed	$fix             	
	 * @return	void
	 */
	public static function backup_file_restore($file_path, $backup_timestamp, $fix) {
    // Validate input parameters
    $sanitized_file_path = sanitize_text_field($file_path);
    $sanitized_backup_timestamp = preg_replace('/[^0-9]/', '', $backup_timestamp);
    $sanitized_fix = preg_replace('/[^a-zA-Z0-9_-]/', '', $fix);

    // Construct the backup file path safely
    $backup_file_name = basename($sanitized_file_path) . '_' . $sanitized_backup_timestamp . '_' . $sanitized_fix . '.wfbkp';
    $backup_file_path = WP_CONTENT_DIR . '/sn-backups/' . $backup_file_name;

    // Check if the backup file exists
    if (file_exists($backup_file_path)) {
        // Attempt to copy the file and check for failure
        if (!copy($backup_file_path, $sanitized_file_path)) {
            // Log the error
            wf_sn_el_modules::log_event(
                'backup',
                'backup_restore_failed',
                sprintf(
                    esc_html__('Failed to restore backup file: %s', 'security-ninja'),
                    esc_html($backup_file_path)
                ),
                null
            );
        }
    } else {
        // Backup file does not exist, handle accordingly
        wf_sn_el_modules::log_event(
            'backup',
            'backup_file_not_found',
            sprintf(
                esc_html__('Backup file does not exist: %s', 'security-ninja'),
                esc_html($backup_file_path)
            ),
            null
        );
    }
}


	/**
	 * Tests if WordPress is accessible by posting AJAX req.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @version v1.0.1  Saturday, March 6th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function test_wordpress_status() {
		$args = array(
			'timeout' => 120,
			'body'    => array( 'action' => 'wf_sn_af_test_wp' ),
		);


		// Checks if it is a local environment
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
			$args['sslverify'] = false;
		} else {
			$args['sslverify'] = true; // Fallback to true if not in local environment
		}
		$response = wp_remote_post( admin_url( 'admin-ajax.php' ), $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		if ( site_url() === trim( $response['body'] ) ) {
			return true;
		} else {
			return false;
		}
	}


	/**
	 * generate_hashes_dir.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $origin_directory
	 * @return  void
	 */
	public static function generate_hashes_dir( $origin_directory ) {
		$current_working_directory = dir( $origin_directory );
		while ( $entry = $current_working_directory->read() ) {
			if ( '.' === $entry || '..' === $entry ) {
				continue;
			}

			if ( is_dir( $origin_directory . '\\' . $entry ) ) {
				self::generate_hashes_dir( $origin_directory . '\\' . $entry );
			} else {
				$ext                             = pathinfo( $entry, PATHINFO_EXTENSION );
				$filepath                        = self::fix_path( $origin_directory . '\\' . $entry );
				$md5                             = md5_file( $origin_directory . '\\' . $entry );
				self::$hashed_files[ $filepath ] = $md5;
			}
		} // while
		$current_working_directory->close();
	}


	/**
	 * generate_hash_file.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $filepath
	 * @return  mixed
	 */
	public static function generate_hash_file( $filepath ) {
		return md5_file( $filepath );
	}


	/**
	 * fix_path.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $path
	 * @return  mixed
	 */
	public static function fix_path( $path ) {
		$path = str_replace( getcwd(), '', $path );
		$path = str_replace( '\\', '/', $path );
		$path = str_replace( '//', '/', $path );
		$path = trim( $path, '/' );

		return $path;
	}


	/**
	 * test_wordpress_status_request.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function test_wordpress_status_request() {
		echo site_url();
		die();
	}


	/**
	 * mark_as_fixed.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $test
	 * @return  void
	 */
	public static function mark_as_fixed( $test ) {
		return;
	}

	/**
	 * directory_unlink.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $dir
	 * @return  mixed
	 */
	public static function directory_unlink( $dir ) {
		$files = array_diff( scandir( $dir ), array( '.', '..' ) );
		foreach ( $files as $file ) {
			( is_dir( "$dir/$file" ) ) ? self::directory_unlink( "$dir/$file" ) : unlink( "$dir/$file" );
		}
		return rmdir( $dir );
	}


	/**
	 * directory_copy.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @param   mixed   $src
	 * @param   mixed   $dst
	 * @return  void
	 */
	public static function directory_copy( $src, $dst ) {
		$dir = opendir( $src );
		@mkdir( $dst );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( ( '.' !== $file ) && ( '..' !== $file ) ) {
				if ( is_dir( $src . '/' . $file ) ) {
					self::directory_copy( $src . '/' . $file, $dst . '/' . $file );
				} else {
					copy( $src . '/' . $file, $dst . '/' . $file );
				}
			}
		}
		closedir( $dir );
	}


	/**
	 * clean-up when deactivated
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function deactivate() {
		$centraloptions = Wf_Sn::get_options();
		if ( ! isset( $centraloptions['remove_settings_deactivate'] ) ) {
			return;
		}
		delete_option('wf_sn_af');
		if ( $centraloptions['remove_settings_deactivate'] ) {
			delete_option( 'wf_sn_el' );
		}
	}
}


// hook everything up
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\wf_sn_af', 'init' ) );

// when deativated, clean up
register_deactivation_hook( WF_SN_BASE_FILE, array( __NAMESPACE__ . '\wf_sn_af', 'deactivate' ) );
