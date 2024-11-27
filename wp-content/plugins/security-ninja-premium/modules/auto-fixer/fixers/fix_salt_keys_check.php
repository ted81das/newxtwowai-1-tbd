<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_salt_keys_check extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Regenerate WordPress Security keys', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will regenerate all WordPress security/hash keys. After the fix is applied you will be asked to login again.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Could not update keys.', 'security-ninja' ),
		);

		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		$backup_timestamp = time();
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'salt_keys_check' );

		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'AUTH_KEY', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'SECURE_AUTH_KEY', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'LOGGED_IN_KEY', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'NONCE_KEY', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'AUTH_SALT', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'SECURE_AUTH_SALT', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'LOGGED_IN_SALT', wp_generate_password( 64, true, true ) );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'NONCE_SALT', wp_generate_password( 64, true, true ) );

		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'salt_keys_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'salt_keys_check' );
			return self::get_label( 'msg_ok' );
		}
	}
}
