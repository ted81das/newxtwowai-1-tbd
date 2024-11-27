<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_deactivated_themes extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Delete unused themes', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will delete unused themes. There is NO undo.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Inactive themes removed successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Inactive Themes Removal Failed.', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {

		$msg            = '';

		// get all themes and determine active theme
		$all_themes    = wp_get_themes();
		// Note - keep in reverse order, latest first - this way the rest will be.
		$wp_themes_to_keep = array(
			'twentytwentyfive',
			'twentytwentyfour',
			'twentytwentythree',
			'twentytwentytwo',
			'twentytwentyone',
			'twentytwenty',
			'twentynineteen',
			'twentyseventeen',
			'twentysixteen',
			'twentyfifteen',
			'twentyfourteen',
			'twentythirteen',
			'twentytwelve',
			'twentyeleven',
			'twentyten',
		);

		// Parent
		$get_template = get_template();
		// Potentially a child sheet
		$get_stylesheet = get_stylesheet();

		// Unset active theme
		if ( isset( $all_themes[ $get_template ] ) ) {
			unset( $all_themes[ $get_template ] );
		}
		// Unset child theme
		if ( isset( $all_themes[ $get_stylesheet ] ) ) {
			unset( $all_themes[ $get_stylesheet ] );
		}

		$theme_directory = get_theme_root();
		$failed          = false;
		$newest_wp_found = false;

		// loop though all themes and delete inactive ones
		foreach ( $all_themes as $theme_path => $theme_info ) {
			if ( strlen( $theme_path ) > 0 ) {

				if ( wf_sn_af::directory_unlink( $theme_directory . '/' . $theme_path ) ) {
					$msg .= '<strong>' . esc_html( $all_themes[ $theme_path ] ) . '</strong> ' . esc_html__( 'Removed.', 'security-ninja' ) . '<br />';
				} else {
					$msg   .= '<strong>' . esc_html( $all_themes[ $theme_path ] ) . '</strong> ' . esc_html__( 'Could not be removed.', 'security-ninja' ) . '<br />';
					$failed = true;
				}
			}
		}

		if ( ! $failed ) {
			wf_sn_af::mark_as_fixed( 'deactivated_themes' );
			return $msg . self::get_label( 'msg_ok' );
		} else {
			return $msg . self::get_label( 'msg_bad' );
		}
	}
} // wf_sn_af_fix_ver_check
