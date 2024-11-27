<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_debug_check extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Enable automatic WordPress core updates', 'security-ninja' ),
			'fixable' => false,
			'info'    => esc_html__( 'Go to the Fixes page to enable or disable.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Failed to disable debug mode.', 'security-ninja' ),
		);

		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		$backup_timestamp = time();
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'WP_DEBUG', false );
		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'debug_check' );
			return self::get_label( 'msg_ok' );
		}
	}
	static function remove_fix() {
		$backup_timestamp = time();
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'WP_DEBUG', true );
		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'debug_check' );
			return self::get_label( 'msg_ok' );
		}
	}


}
