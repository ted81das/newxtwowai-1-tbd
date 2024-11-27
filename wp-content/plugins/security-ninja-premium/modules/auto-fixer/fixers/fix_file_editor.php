<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_file_editor extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Disable plugins/themes file editor', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will disable the plugins and themes file editor.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Could not disable file editor.', 'security-ninja' ),
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
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'DISALLOW_FILE_EDIT', true );
		$no_wsod = wf_sn_af::test_wordpress_status();	
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'file_editor' );
			return self::get_label( 'msg_ok' );
		}
	}

	static function remove_fix() {
		$backup_timestamp = time();
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
		wf_sn_af::update_define( wf_sn_af::$wp_config_path, 'DISALLOW_FILE_EDIT', false );
		$no_wsod = wf_sn_af::test_wordpress_status();				
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'debug_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'file_editor' );
			return self::get_label( 'msg_ok' );
		}
	}


}
