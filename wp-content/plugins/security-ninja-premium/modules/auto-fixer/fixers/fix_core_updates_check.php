<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_core_updates_check extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Enable automatic WordPress core updates', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will enable automatic WordPress core updates.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Could not enable automatic updates.', 'security-ninja' ),
		);

		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		$backup_timestamp = time();
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'core_updates_check' );
		if ( ! wf_sn_af::remove_define( wf_sn_af::$wp_config_path, 'AUTOMATIC_UPDATER_DISABLED' ) || ! wf_sn_af::remove_define( wf_sn_af::$wp_config_path, 'WP_AUTO_UPDATE_CORE' ) ) {
			return self::get_label( 'msg_bad' );
		}

		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, ' core_updates_check' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'core_updates_check' );
			return self::get_label( 'msg_ok' );
		}
	}
} // wf_sn_af_fix_ver_check
