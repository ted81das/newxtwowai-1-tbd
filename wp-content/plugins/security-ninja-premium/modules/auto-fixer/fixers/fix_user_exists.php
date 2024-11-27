<?php
namespace WPSecurityNinja\Plugin;
class wf_sn_af_fix_user_exists extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Change admin username', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will change the admin username. <br /><span style="color:#F00;">Once the fix is applied you will need to login again with the new username. Password will not be changed.</span><br /><br /> 
						 Please input your new desired username: ', 'security-ninja' ) . '<input type="text" name="new_user_login" value="" /><br /><small>' . esc_html__( 'Try not to use usernames like: "root", "god", "null" or similar ones.', 'security-ninja' ) . '</small>',
			'msg_ok'  => esc_html__( 'Fix applied successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Could not change username.', 'security-ninja' ),
		);

		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		global $wpdb;

		$fields = json_decode( stripslashes( $_GET['fields'] ), true );

		// check if admin username still exists
		$admin_user_id = $wpdb->get_var( 'SELECT ID FROM ' . $wpdb->users . ' WHERE user_login = "admin"' );
		if ( ! $admin_user_id ) {
			return esc_html__( 'Username admin does not exist. Please reanalyze your website to update the test status.', 'security-ninja' );
		}

		// check if new username entered is valid
		if ( strlen( $fields['new_user_login'] ) < 1 ) {
			return esc_html__( 'Username field cannot be empty.', 'security-ninja' );
		}

		if ( false === $wpdb->update( $wpdb->users, array( 'user_login' => $fields['new_user_login'] ), array( 'ID' => $admin_user_id ) ) ) {
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'user_exists' );
			return self::get_label( 'msg_ok' );
		}
	}
}
