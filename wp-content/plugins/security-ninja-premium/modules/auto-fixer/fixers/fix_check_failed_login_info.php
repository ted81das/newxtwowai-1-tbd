<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_check_failed_login_info extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Hide unnecessary information on failed login attempts', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'A universal "wrong username or password" message without any details will be displayed on all failed login attempts.', 'security-ninja' ),
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
		if ( wf_sn_af::update_option( 'sn-hide-wp-login-info', true ) ) {
			wf_sn_af::mark_as_fixed( 'check_failed_login_info' );
			return self::get_label( 'msg_ok' );
		} else {
			return self::get_label( 'msg_bad' );
		}
	}
}
