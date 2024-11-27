<?php
namespace WPSecurityNinja\Plugin;

/**
 * wf_sn_af_fix_wp_header_meta.
 *
 * @author	Lars Koudal
 * @since	v0.0.1
 * @version	v1.0.0	Wednesday, April 13th, 2022.
 * @see		wf_sn_af
 * @global
 */
class wf_sn_af_fix_wp_header_meta extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Hide WP version info', 'security-ninja' ),
			'fixable' => false,
			'info'    => esc_html__( 'Please go to the "Fixes" page and enable "Hide WP Version" if necessary.', 'security-ninja' ),
		);

		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

}
