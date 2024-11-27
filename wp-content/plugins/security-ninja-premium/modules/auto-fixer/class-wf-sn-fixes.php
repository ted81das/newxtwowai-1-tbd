<?php

namespace WPSecurityNinja\Plugin;

if ( ! function_exists( 'add_action' ) ) {
	die( 'Please don\'t open this file directly!' );
}

define( 'WF_SN_FIXES_OPTIONS_KEY', 'wf_sn_fixes' );

class Wf_Sn_Fixes extends wf_sn_af {

	public static $options = null;

	public static function init() {
		self::$options = self::get_options();

		add_action( 'admin_init', array( __NAMESPACE__ . '\\wf_sn_fixes', 'admin_init' ) );

		add_filter( 'wp_headers', array( __NAMESPACE__ . '\\wf_sn_fixes', 'do_filter_wp_headers' ), PHP_INT_MAX, 1 );

		// Remove unwanted files

		if ( isset( self::$options['remove_unwanted_files'] ) ) {

			add_action( 'secnin_remove_files', array( __NAMESPACE__ . '\\wf_sn_fixes', 'remove_unwanted_files_func' ) );

			if ( self::$options['remove_unwanted_files'] ) {
				// add cron job - daily
				if ( ! wp_next_scheduled( 'secnin_remove_files' ) ) {
					wp_schedule_event( time(), 'daily', 'secnin_remove_files' );
				}
			} elseif ( wp_next_scheduled( 'secnin_remove_files' ) ) {
				// remove cron job
				wp_clear_scheduled_hook( 'secnin_remove_files' );
			}
		}

		// Remove Generator meta tag
		if ( isset( self::$options['hide_wp'] ) && self::$options['hide_wp'] ) {
			remove_action( 'wp_head', 'wp_generator' );
		}

		// Block Application Passwords
		if ( isset( self::$options['application_passwords'] ) && self::$options['application_passwords'] ) {
			add_filter( 'wp_is_application_passwords_available', '__return_false' );
		}

		// Block XML Sitemaps
		if ( isset( self::$options['disable_wp_sitemaps'] ) && self::$options['disable_wp_sitemaps'] ) {
			add_filter( 'wp_sitemaps_enabled', '__return_false' );
		}

		// Remove the Windows Live Writer meta tag
		if ( isset( self::$options['hide_wlw'] ) && self::$options['hide_wlw'] ) {
			remove_action( 'wp_head', 'wlwmanifest_link' );
		}

		// Disable the username enumeration
		if ( ! is_admin() && isset( self::$options['disable_username_enumeration'] ) && self::$options['disable_username_enumeration'] ) {

			add_filter( 'redirect_canonical', array( __NAMESPACE__ . '\\wf_sn_fixes', 'disable_usernames_enumeration' ), 10, 2 );

			if (
				isset( $_SERVER['QUERY_STRING'] ) &&
				preg_match( '/author=([0-9]*)/i', sanitize_text_field( $_SERVER['QUERY_STRING'] ) ) &&
				! ( preg_match( '/submit=Download\+Export\+File/i', sanitize_text_field( $_SERVER['QUERY_STRING'] ) ) &&
					strpos( $_SERVER['REQUEST_URI'], 'wp-admin/export.php' ) !== false ) &&
				! wp_is_json_request() &&
				!( $_SERVER['REQUEST_METHOD'] === 'GET' && strpos( $_SERVER['REQUEST_URI'], '/wp-json/' ) !== false )
			) {

				$details['query'] = '/' . sanitize_text_field( $_SERVER['QUERY_STRING'] );
				$current_user_ip  = wf_sn_cf::get_user_ip();
				if ( $current_user_ip ) {
					$details['ip'] = $current_user_ip;
				}

				$details['user_agent'] = '';
				if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
					$details['user_agent'] = sanitize_text_field( $_SERVER['HTTP_USER_AGENT'] );
				}

				wf_sn_el_modules::log_event( 'security_ninja', 'username_enumeration_disabled', __( 'Username enumeration is disabled.', 'security-ninja' ), $details );
				wf_sn_cf::kill_request();
			}
		}
	}





	/**
	 * Remove unwanted files automatically
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Thursday, January 27th, 2022.
	 * @version v1.0.1  Friday, May 24th, 2024.
	 * @access  public static
	 * @return  void
	 */
	public static function remove_unwanted_files_func() {
		$dangerous_files = array(
			'wp-config.php.old'      => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php_bak'      => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php~'         => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php-'         => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php--'        => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php---'       => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php.bkp'      => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'wp-config.php_revision' => esc_html__( 'Common name for config file backup - can contain critical information', 'security-ninja' ),
			'php_errorlog'           => esc_html__( 'Can contain server details or errors that can be exploited.', 'security-ninja' ),
			'php_mail.log'           => esc_html__( 'Can contain user details or errors that can be exploited.', 'security-ninja' ),
			'.htaccess.sg'           => esc_html__( '.htaccess backup files on SiteGround - Can show server details or configurations that should not be public.', 'security-ninja' ),
			'.htaccess_swift_backup' => esc_html__( '.htaccess backup file by Swift Performance - Can show server details or configurations that should not be public.', 'security-ninja' ),
			'phpinfo.php'            => esc_html__( 'Displays all details about PHP on your website, should only exist briefly during development.', 'security-ninja' ),
			'info.php'               => esc_html__( 'Should only exist briefly during development and not on a live site.', 'security-ninja' ),
			'test.php'               => esc_html__( 'Should only exist briefly during development and not on a live site.', 'security-ninja' ),
			'readme.html'            => esc_html__( 'Default readme.html file in English.', 'security-ninja' ),
			'liesmich.html'          => esc_html__( 'Default readme.html file in German.', 'security-ninja' ),
			'lisezmoi.html'          => esc_html__( 'Default readme.html file in French.', 'security-ninja' ),
			'leame.html'             => esc_html__( 'Default readme.html file in Spanish.', 'security-ninja' ),
			'leggimi.html'           => esc_html__( 'Default readme.html file in Italian.', 'security-ninja' ),
			'leesmij.html'           => esc_html__( 'Default readme.html file in Dutch.', 'security-ninja' ),
		);

		// Handle direct file names
		foreach ( $dangerous_files as $file => $reason ) {
			$full_path = ABSPATH . $file;
			if ( file_exists( $full_path ) ) {
				wp_delete_file( $full_path );
				wf_sn_el_modules::log_event( 'security_ninja', 'delete_unwanted_file', __( 'Unwanted file deleted.', 'security-ninja' ) . ' ' . $full_path . ' Reason: ' . $reason, array( 'path' => $full_path ) );
			}
		}

		// Handle wildcard patterns
		$dangerous_patterns = array(
			'*.sql'            => esc_html__( '.sql files should not be kept on your server - they may contain sensitive data.', 'security-ninja' ),
			'*.bak'            => esc_html__( 'Copies of old files could contain important info about your server.', 'security-ninja' ),
			'deleteme.wp*.php' => esc_html__( 'Files with this pattern are leftovers from installation scripts and should not be on your server.', 'security-ninja' ),
		);

		foreach ( $dangerous_patterns as $pattern => $reason ) {
			foreach ( self::glob_recursive( $pattern ) as $unwanted_file ) {
				if ( file_exists( $unwanted_file ) ) {
					wp_delete_file( $unwanted_file );
					$message = sprintf(
						'%s %s %s %s',
						esc_html__( 'Unwanted file deleted.', 'security-ninja' ),
						esc_html( $unwanted_file ),
						esc_html__( 'Reason:', 'security-ninja' ),
						esc_html( $reason )
					);
					wf_sn_el_modules::log_event( 'security_ninja', 'delete_unwanted_file', $message, array( 'path' => esc_html( $unwanted_file ) ) );
				}
			}
		}
	}


	/**
	 * Recursively search for files matching a pattern
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, May 25th, 2024.
	 * @access  public static
	 * @param   mixed   $pattern
	 * @param   integer $flags      Default: 0
	 * @return  mixed
	 */
	public static function glob_recursive( $pattern, $flags = 0 ) {
		$files = glob( ABSPATH . $pattern, $flags );
		foreach ( glob( ABSPATH . dirname( $pattern ) . '/*', GLOB_ONLYDIR | GLOB_NOSORT ) as $dir ) {
			$files = array_merge( $files, self::glob_recursive( $dir . '/' . basename( $pattern ), $flags ) );
		}
		return $files;
	}


	/**
	 * Disables usernames enumeration.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Monday, January 25th, 2021.
	 * @access  public static
	 * @global
	 * @param   mixed   $redirect
	 * @param   mixed   $request
	 * @return  void
	 */
	public static function disable_usernames_enumeration( $redirect, $request ) {
		if ( preg_match( '/\?author=([0-9]*)(\/*)/i', $request ) ) {
			http_response_code( 403 );
			die();
		} else {
			return $redirect;
		}
	}



	/**
	 * do_filter_wp_headers.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, June 5th, 2024.
	 * @access  public static
	 * @global
	 * @param   mixed   $headers
	 * @return  mixed
	 */
	public static function do_filter_wp_headers( $headers ) {
		if ( self::$options['enable_xcto'] && self::$options['sechead_xcto'] ) {
			$headers['X-Content-Type-Options'] = self::sanitize_header_value( self::$options['sechead_xcto'] );
		}
		if ( self::$options['enable_xfo'] && self::$options['sechead_xfo'] ) {
			$headers['X-Frame-Options'] = self::sanitize_header_value( self::$options['sechead_xfo'] );
		}
		if ( self::$options['enable_sts'] && self::$options['sechead_sts'] ) {
			$headers['Strict-Transport-Security'] = self::sanitize_header_value( self::$options['sechead_sts'] );
		}
		if ( self::$options['enable_rp'] && self::$options['sechead_rp'] ) {
			$headers['Referrer-Policy'] = self::sanitize_header_value( self::$options['sechead_rp'] );
		}
		if ( self::$options['enable_fp'] && self::$options['sechead_fp'] ) {
			$headers['Permissions-Policy'] = self::sanitize_header_value( self::$options['sechead_fp'] );
		}
		if ( self::$options['enable_csp'] && self::$options['sechead_csp'] ) {
			$headers['Content-Security-Policy'] = self::sanitize_header_value( self::$options['sechead_csp'] );
		}
		if ( self::$options['hide_php_ver'] ) {
			unset( $headers['X-Powered-By'] );
			unset( $headers['Server'] );
			unset( $headers['server'] );
		}

		return $headers;
	}






	/**
	 * Simple sanitation that removes newlines and returns to mitigate header injection.
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, April 16th, 2024.
	 * @access  private static
	 * @global
	 * @param   mixed   $value
	 * @return  mixed
	 */
	private static function sanitize_header_value( $value ) {

		// Delete old structure
		$colon_pos = strpos( $value, ':' );
		if ( false !== $colon_pos ) {
			$value = substr( $value, $colon_pos + 1 );
		}

		$value = str_replace( array( "\n", "\r" ), '', $value );

		$value = sanitize_text_field( $value );

		return $value;
	}

	/**
	 * Returns the options
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, December 16th, 2020.
	 * @access  public static
	 * @return  mixed
	 */
	public static function get_options() {
		$defaults = array(
			'hide_wp'                      => 0,
			'remove_unwanted_files'        => 0,
			'hide_wlw'                     => 0,
			'hide_php_ver'                 => 0,
			'application_passwords'        => 0,
			'disable_editors'              => 0,
			'disable_wp_debug'             => 0,
			'disable_username_enumeration' => 0,
			'hide_wp_debug'                => 0,
			'disable_wp_sitemaps'          => 0,

			'enable_xcto'                  => 0,
			'sechead_xcto'                 => 'X-Content-Type-Options: nosniff',

			'enable_xfo'                   => 0,
			'sechead_xfo'                  => 'X-Frame-Options: SAMEORIGIN',

			'enable_sts'                   => 0,
			'sechead_sts'                  => 'Strict-Transport-Security: max-age=31536000;',

			'enable_rp'                    => 0,
			'sechead_rp'                   => 'Referrer-Policy: same-origin',

			'enable_fp'                    => 0,
			'sechead_fp'                   => 'Permissions-Policy: accelerometer=(), ambient-light-sensor=(), autoplay=(), battery=(), camera=(), cross-origin-isolated=(), display-capture=(), document-domain=(), encrypted-media=(), execution-while-not-rendered=(), execution-while-out-of-viewport=(), fullscreen=(), geolocation=(), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), navigation-override=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), web-share=(), xr-spatial-tracking=()',

			'enable_csp'                   => 0,
			'sechead_csp'                  => '',

			'secure_cookies'               => 0,

		);

		$options = get_option( WF_SN_FIXES_OPTIONS_KEY, array() );
		$options = array_merge( $defaults, $options );
		return $options;
	}

	/**
	 * admin_init.
	 *
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Sunday, October 17th, 2021.
	 * @access  public static
	 * @return  void
	 */
	public static function admin_init() {
		register_setting( WF_SN_FIXES_OPTIONS_KEY, 'wf_sn_fixes', array( __NAMESPACE__ . '\\wf_sn_fixes', 'sanitize_settings' ) );
	}


	/**
	 * sanitize settings on save
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, June 18th, 2022.
	 * @access  public static
	 * @param   mixed   $new_options
	 * @return  mixed
	 */
	public static function sanitize_settings( $new_options ) {

		$old_options = get_option( WF_SN_FIXES_OPTIONS_KEY );
		if ( ! is_array( $old_options ) ) {
			$old_options = array();
		}
		$old_options['hide_wp']                      = 0;
		$old_options['hide_wlw']                     = 0;
		$old_options['remove_unwanted_files']        = 0;
		$old_options['hide_php_ver']                 = 0;
		$old_options['hide_server']                  = 0;
		$old_options['disable_editors']              = 0;
		$old_options['disable_wp_debug']             = 0;
		$old_options['disable_wp_sitemaps']          = 0;
		$old_options['disable_username_enumeration'] = 0;

		$old_options['hide_wp_debug']         = 0; // should be on by default but server config might mess this up.
		$old_options['application_passwords'] = 0;
		$old_options['enable_xcto']           = 0;
		$old_options['sechead_xcto']          = 'nosniff';
		$old_options['enable_xfo']            = 0;
		$old_options['sechead_xfo']           = 'SAMEORIGIN';

		$old_options['enable_sts']     = 0;
		$old_options['sechead_sts']    = 'max-age=31536000;';
		$old_options['enable_rp']      = 0;
		$old_options['sechead_rp']     = 'same-origin';
		$old_options['enable_fp']      = 0;
		$old_options['sechead_fp']     = 'accelerometer=(), ambient-light-sensor=(), autoplay=(), battery=(), camera=(), cross-origin-isolated=(), display-capture=(), document-domain=(), encrypted-media=(), execution-while-not-rendered=(), execution-while-out-of-viewport=(), fullscreen=(), geolocation=(), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), navigation-override=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), web-share=(), xr-spatial-tracking=()';
		$old_options['enable_csp']     = 0;
		$old_options['sechead_csp']    = "upgrade-insecure-requests; frame-ancestors 'self'";
		$old_options['secure_cookies'] = 0;

		foreach ( $new_options as $key => $value ) {
			switch ( $key ) {
				case 'hide_wp':
				case 'hide_wlw':
				case 'hide_php_ver':
				case 'enable_xcto':
				case 'enable_xfo':
				case 'enable_rp':
				case 'enable_sts':
				case 'enable_fp':
				case 'enable_csp':
				case 'disable_editors':
				case 'disable_wp_debug':
				case 'disable_wp_sitemaps':
				case 'disable_username_enumeration':
				case 'hide_wp_debug':
				case 'secure_cookies':
				case 'remove_unwanted_files':
					$new_options[ $key ] = intval( $value );
					break;
				case 'sechead_xcto':
				case 'sechead_xfo':
				case 'sechead_sts':
				case 'sechead_rp':
				case 'sechead_fp':
				case 'sechead_csp':
					$new_options[ $key ] = self::sanitize_header_value( $value );
					break;
			}
		}

		// Change disable editors
		if ( array_key_exists( 'disable_editors', $new_options ) ) {
			$res = wf_sn_af_fix_file_editor::fix();
		} else {
			$res = wf_sn_af_fix_file_editor::remove_fix();
		}

		// Change WP_DEBUG
		if ( array_key_exists( 'disable_wp_debug', $new_options ) ) {
			$res = wf_sn_af_fix_debug_check::fix();
		} else {
			$res = wf_sn_af_fix_debug_check::remove_fix();
		}

		// Change WP_DEBUG
		if ( array_key_exists( 'secure_cookies', $new_options ) ) {
			if ( class_exists( __NAMESPACE__ . '\Wf_Sn_Af' ) ) {
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.cookie_httponly', true );
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.cookie_secure', true );
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.use_only_cookies', true );
			}
		} elseif ( class_exists( __NAMESPACE__ . '\Wf_Sn_Af' ) ) {
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.cookie_httponly', false );
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.cookie_secure', false );
				Wf_Sn_Af::update_ini_set( Wf_Sn_Af::$wp_config_path, 'session.use_only_cookies', false );
		}

		$return = array_merge( $old_options, $new_options );

		return $return;
	}


	/**
	 * Renders vulnerability tab
	 *
	 * @author  Lars Koudal
	 * @author  Unknown
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, December 16th, 2020.
	 * @version v1.0.1  Wednesday, May 15th, 2024.
	 * @access  public static
	 * @return  void
	 */
	public static function fixes_page__premium_only() {
		?>
		<div class="wrap">
			<?php wf_sn::show_topbar(); ?>
			<div class="secnin_content_wrapper">
				<div class="secnin_content_cell" id="secnin_content_top">
					<div class="submit-test-container card">
						<h2><?php esc_html_e( 'Security Fixes', 'security-ninja' ); ?></h2>
						<p><?php esc_html_e( 'Use the settings here to control the security on your website. These features can be enabled and disabled based on what requirements your website/server has.', 'security-ninja' ); ?></p>

						<form action="options.php" method="post">
							<?php
							settings_fields( 'wf_sn_fixes' );

							?>

							<hr>

							<h2><?php esc_html_e( 'WordPress Features', 'security-ninja' ); ?></h2>
							<table class="form-table">
								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_editors' ); ?>">
											<?php esc_html_e( 'Disable plugin & theme editor', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_editors' ),
											array(
												'saved_value' => esc_attr( self::$options['disable_editors'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[disable_editors]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'Disables the plugin editor and the theme editor.', 'security-ninja' ); ?></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_wp_debug' ); ?>">
											<?php esc_html_e( 'Disable debug mode', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_wp_debug' ),
											array(
												'saved_value' => esc_attr( self::$options['disable_wp_debug'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[disable_wp_debug]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'Disables the error log. This could contain information hackers could abuse to attack your system.', 'security-ninja' ); ?></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_wp_sitemaps' ); ?>">
											<?php esc_html_e( 'Disable WP Sitemaps', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_wp_sitemaps' ),
											array(
												'saved_value' => esc_attr( self::$options['disable_wp_sitemaps'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[disable_wp_sitemaps]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'Disables the sitemaps functionality introduced in WordPress 5.5. This feature is not security related, but can create issues with SEO plugins.', 'security-ninja' ); ?></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_application_passwords' ); ?>">
											<?php esc_html_e( 'Disable Application Passwords', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_application_passwords' ),
											array(
												'saved_value' => esc_attr( self::$options['application_passwords'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[application_passwords]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'Disables the Application Password feature introduced in WordPress 5.6. Allows external services and programs to interact with your website. If not used specifically it does not need to be enabled.', 'security-ninja' ); ?></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_username_enumeration' ); ?>">
											<?php esc_html_e( 'Disable Username Enumeration', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_disable_username_enumeration' ),
											array(
												'saved_value' => esc_attr( self::$options['disable_username_enumeration'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[disable_username_enumeration]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'This will prevent redirections from yoursite.com/?author={id} to yoursite.com/author/username', 'security-ninja' ); ?></p>
									</td>
								</tr>

								<tr valign="top">
									<th scope="row">
										<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_remove_unwanted_files' ); ?>">
											<?php esc_html_e( 'Remove unwanted files', 'security-ninja' ); ?>
										</label>
									</th>
									<td class="sn-cf-options">
										<?php
										Wf_Sn::create_toggle_switch(
											esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_remove_unwanted_files' ),
											array(
												'saved_value' => esc_attr( self::$options['remove_unwanted_files'] ),
												'value' => '1',
												'option_key' => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[remove_unwanted_files]' ),
											)
										);
										?>
										<p class="description"><?php esc_html_e( 'If enabled, a daily cleanup process cleans up unneeded files from your WordPress installation. Access to .bak, .sql and other files are blocked via the firewall module.', 'security-ninja' ); ?></p>
									</td>
								</tr>
							</table>

							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_wp' ); ?>">
												<?php esc_html_e( 'Hide WP Version', 'security-ninja' ); ?>
											</label>
										</th>
										<td>
											<?php
											Wf_Sn::create_toggle_switch(
												esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_wp' ),
												array(
													'saved_value' => esc_attr( self::$options['hide_wp'] ),
													'value'       => '1',
													'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[hide_wp]' ),
												)
											);
											?>
											<p class="description"><?php esc_html_e( "Hide WordPress version info is revealed in page's meta data.", 'security-ninja' ); ?></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_wlw' ); ?>">
												<?php esc_html_e( 'Hide WLW', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<?php
											Wf_Sn::create_toggle_switch(
												esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_wlw' ),
												array(
													'saved_value' => esc_attr( self::$options['hide_wlw'] ),
													'value'       => '1',
													'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[hide_wlw]' ),
												)
											);
											?>
											<p class="description"><?php esc_html_e( "Hide Windows Live Writer link in page's meta data.", 'security-ninja' ); ?></p>
										</td>
									</tr>
								</tbody>
							</table>

							<hr>
							<h2><?php esc_html_e( 'Secure Cookies', 'security-ninja' ); ?></h2>
							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_secure_cookies' ); ?>">
												<?php esc_html_e( 'Enable Secure Cookies', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_secure_cookies' ),
													array(
														'saved_value' => esc_attr( self::$options['secure_cookies'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[secure_cookies]' ),
													)
												);
												?>
											</p>
											<p class="description"><?php esc_html_e( 'Enforcing all cookies are set as secure adds a layer of protection against cross-site scripting attacks and is an easy measure to protect your website.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'These settings are added to your wp-config.php file.', 'security-ninja' ); ?>
											<pre>
@ini_set('session.cookie_httponly', true);
@ini_set('session.cookie_secure', true);
@ini_set('session.use_only_cookies', true);
</pre>
											</p>
										</td>
									</tr>
								</tbody>
							</table>

							<hr>
							<h2><?php esc_html_e( 'Security Headers', 'security-ninja' ); ?></h2>
							<p><?php esc_html_e( 'Configuring your website with the right security headers can help your website from a lot of problems. It can be difficult to get the settings right, so be sure to test your website properly.', 'security-ninja' ); ?></p>

							<table class="form-table">
								<tbody>
									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_php_ver' ); ?>">
												<?php esc_html_e( 'Hide PHP Version', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<?php
											Wf_Sn::create_toggle_switch(
												esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_hide_php_ver' ),
												array(
													'saved_value' => esc_attr( self::$options['hide_php_ver'] ),
													'value'       => '1',
													'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[hide_php_ver]' ),
												)
											);
											?>
											<p class="description"><?php esc_html_e( 'Removes the "X-Powered-By" header. No need to tell what software version you are running.', 'security-ninja' ); ?></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_xcto' ); ?>">
												<?php esc_html_e( 'X-Content-Type-Options', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_xcto' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_xcto'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_xcto]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_xcto' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_xcto]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_xcto'] ); ?>" placeholder="" class="regular-text">
											<p class="description"><?php esc_html_e( 'Setting this will force a browser to only load external resources (.css, .js and so on) if the content-type matches what is expected. This prevents malicious hidden code in unexpected files.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'Default: ', 'security-ninja' ); ?><code>nosniff</code></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_xfo' ); ?>">
												<?php esc_html_e( 'X-Frame-Options', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_xfo' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_xfo'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_xfo]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_xfo' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_xfo]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_xfo'] ); ?>" placeholder="" class="regular-text">
											<p><?php esc_html_e( 'The X-Frame-Options response header indicates if a page is allowed to render a page in an iframe, frame or object. Avoid clickjacking attacks simply by not allowing your content to be embedded on other websites.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'Default: ', 'security-ninja' ); ?><code>SAMEORIGIN</code></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_sts' ); ?>">
												<?php esc_html_e( 'Strict-Transport-Security', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_sts' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_sts'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_sts]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_sts' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_sts]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_sts'] ); ?>" placeholder="" class="regular-text">
											<p class="description"><?php esc_html_e( 'When enabled, HSTS instructs browsers to only connect to your website using HTTPS, preventing SSL stripping attacks and ensuring all data transmission is encrypted.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'Default: ', 'security-ninja' ); ?><code>max-age=31536000</code></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_rp' ); ?>">
												<?php esc_html_e( 'Referrer-Policy', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_rp' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_rp'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_rp]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_rp' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_rp]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_rp'] ); ?>" placeholder="" class="regular-text">
											<p class="description"><?php esc_html_e( 'Referrer-Policy is a way to control when the "referrer" header information is allowed. This means which websites can see where visitors are referred from. The recommended setting "same-origin" allows you to still track data internally on your website.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'Default: ', 'security-ninja' ); ?><code>same-origin</code></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_fp' ); ?>">
												<?php esc_html_e( 'Permissions-Policy', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_fp' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_fp'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_fp]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_fp' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_fp]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_fp'] ); ?>" placeholder="" class="regular-text">
											<p class="description"><?php esc_html_e( 'This is a way to instruct a browser which features it can use on a website. With this you can explitly prevent access to the camera, microphone, geolocation and many other features.', 'security-ninja' ); ?></p>
											<p><?php esc_html_e( 'For a full and updated list check out the link: ', 'security-ninja' ); ?><a href="https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Feature-Policy" target="_blank" rel="noopener"><?php esc_html_e( 'Mozilla.org - Permissions Policy', 'security-ninja' ); ?></a></p>
											<p><?php esc_html_e( 'Default: ', 'security-ninja' ); ?><code>accelerometer=(), ambient-light-sensor=(), autoplay=(), battery=(), camera=(), cross-origin-isolated=(), display-capture=(), document-domain=(), encrypted-media=(), execution-while-not-rendered=(), execution-while-out-of-viewport=(), fullscreen=(), geolocation=(), gyroscope=(), keyboard-map=(), magnetometer=(), microphone=(), midi=(), navigation-override=(), payment=(), picture-in-picture=(), publickey-credentials-get=(), screen-wake-lock=(), sync-xhr=(), usb=(), web-share=(), xr-spatial-tracking=()</code></p>
											<p><em><?php esc_html_e( 'Note: This was previously named Feature Policy. Both the name and the syntax have been changed since then.', 'security-ninja' ); ?></em></p>
										</td>
									</tr>

									<tr valign="top">
										<th scope="row">
											<label for="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_csp' ); ?>">
												<?php esc_html_e( 'Content-Security-Policy', 'security-ninja' ); ?>
											</label>
										</th>
										<td class="sn-cf-options">
											<p>
												<?php
												Wf_Sn::create_toggle_switch(
													esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_enable_csp' ),
													array(
														'saved_value' => esc_attr( self::$options['enable_csp'] ),
														'value'       => '1',
														'option_key'  => esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[enable_csp]' ),
													)
												);
												?>
											</p>
											<input type="text" id="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '_sechead_csp' ); ?>" name="<?php echo esc_attr( WF_SN_FIXES_OPTIONS_KEY . '[sechead_csp]' ); ?>" value="<?php echo esc_attr( self::$options['sechead_csp'] ); ?>" placeholder="" class="regular-text">
											<p class="description"><strong><?php esc_html_e( 'Warning: If you embed scripts from external websites, Google Analytics or other sources this could break your website functionality. Read and test before implementing.', 'security-ninja' ); ?></strong></p>
											<p><?php esc_html_e( 'Since each website is different, we can only give a general suggestion and strongly advise to remove the fix again if something on your website stops working.', 'security-ninja' ); ?></p>

											<h3><?php esc_html_e( 'Examples', 'security-ninja' ); ?></h3>
											<p><?php esc_html_e( 'All is allowed, but only in code coming from this website:', 'security-ninja' ); ?></p>
											<p><code><?php esc_html_e( "upgrade-insecure-requests; frame-ancestors 'self'", 'security-ninja' ); ?></code></p>
											<br>
											<p><?php esc_html_e( 'Allow JavaScript only from this website and Google Analytics:', 'security-ninja' ); ?></p>
											<p><code><?php esc_html_e( "upgrade-insecure-requests; frame-ancestors 'self'; script-src 'self' www.google-analytics.com;", 'security-ninja' ); ?></code></p>
											<br>
											<p><?php esc_html_e( 'Scott Helme is a security researcher and has written a really in-depth walkthrough of Content Security Policy. ', 'security-ninja' ); ?><a href="https://scotthelme.co.uk/content-security-policy-an-introduction/" target="_blank" rel="noopener"><?php esc_html_e( 'Content Security Policy - An Introduction', 'security-ninja' ); ?></a></p>
										</td>
									</tr>

									<tr>
										<td></td>
										<td>
											<p class="submit"><input type="submit" value="<?php esc_attr_e( 'Save Changes', 'security-ninja' ); ?>" class="input-button button-primary" name="Submit" /></p>
										</td>
									</tr>
								</tbody>
							</table>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}



	/**
	 * Routines that run on deactivation
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Saturday, June 18th, 2022.
	 * @access  public static
	 * @return  void
	 */
	public static function deactivate() {

		$centraloptions = Wf_Sn::get_options();
		if ( ! isset( $centraloptions['remove_settings_deactivate'] ) ) {
			return;
		}
		if ( $centraloptions['remove_settings_deactivate'] ) {
			delete_option( WF_SN_FIXES_OPTIONS_KEY );
			// Remove unwanted files hook - if enabled
			wp_clear_scheduled_hook( 'secnin_remove_files' );
		}
	}
}

// hook everything up
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Wf_Sn_Fixes', 'init' ) );

// when deativated clean up
register_deactivation_hook( WF_SN_BASE_FILE, array( __NAMESPACE__ . '\Wf_Sn_Fixes', 'deactivate' ) );
