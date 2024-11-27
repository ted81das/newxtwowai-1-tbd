<?php
namespace WPSecurityNinja\Plugin;
class wf_sn_af_fix_config_chmod extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Update wp-config.php permissions', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'wp-config.php file permissions will be changed to an optimal value (0440).', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Unable to apply fix.', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		chmod( wf_sn_af::$wp_config_path, 0440 );
		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( $no_wsod ) {
			wf_sn_af::mark_as_fixed( 'config_chmod' );
			return self::get_label( 'msg_ok' ) . ' ' . esc_html__( 'Permission set to 0440', 'security-ninja' );
		}
		chmod( wf_sn_af::$wp_config_path, 0666 );
		return self::get_label( 'msg_bad' );
	}
}
