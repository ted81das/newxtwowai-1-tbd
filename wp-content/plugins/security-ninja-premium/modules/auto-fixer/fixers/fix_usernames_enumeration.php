<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_usernames_enumeration extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Prevent usernames discovery via user IDs', 'security-ninja' ),
			'fixable' => false,
			'info'    => esc_html__( 'Please go to the "Fixes" page and enable "Disable Username Enumeration"', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

} 
