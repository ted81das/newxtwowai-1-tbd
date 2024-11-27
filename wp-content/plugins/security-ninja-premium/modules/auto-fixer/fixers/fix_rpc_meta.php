<?php
namespace WPSecurityNinja\Plugin;
class wf_sn_af_fix_rpc_meta extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Disable XML-RPC', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'If you\'re not using any Really Simple Discovery services such as pingbacks there\'s no need to advertise that endpoint (link) in the header.
			Please note that for most sites this is not a security issue because they "want to be discovered" but if you want to hide the fact that you\'re using WP this fix will disable it.
			We also block access to the xmlrpc.php file via the .htaccess file.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Disabled Successfully', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Disable Failed', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		global $wp_rewrite;
		$home_path     = get_home_path();
		$htaccess_file = $home_path . '.htaccess';

		$backup_timestamp = time();
		$msg              = ''; // response text
		$error            = false;

		if ( ( ! file_exists( $htaccess_file ) && is_writable( $home_path ) ) || is_writable( $htaccess_file ) ) {

			$insertion   = array();
			$insertion[] = '<Files xmlrpc.php>';
			$insertion[] = 'Order Deny,Allow';
			$insertion[] = 'Deny from all';
			$insertion[] = '</Files>';

			if ( insert_with_markers( $htaccess_file, esc_html__( 'Block access to xmlrpc.php', 'security-ninja' ), $insertion ) ) {
				$error = false;
			} else {
				// could not write
				$error = true;
			}
		} else {
			$msg  .= __( 'Cannot modify .htaccess file ', 'security-ninja' ) . ' ' . $htaccess_file;
			$error = true;
		}

		if ( ! $error ) {
			// Lars - added filter to auto-fixer.php - not wp-config(?)
			wf_sn_af::update_option( 'sn-hide-rpc-meta', true );
		}

		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod || $error ) {
			// if anyting went wrong restore everything
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'rpc_meta' );
			wf_sn_af::update_option( 'sn-hide-rpc-meta', false );

			$insertion = array(); // empty array to remove rules
			insert_with_markers( $htaccess_file, esc_html__( 'Block access to xmlrpc.php', 'security-ninja' ), $insertion );

			return $msg . self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'uploads_browsable' );
			return self::get_label( 'msg_ok' );
		}
	}
} // wf_sn_af_fix_rpc_meta
