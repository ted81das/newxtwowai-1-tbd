<?php
namespace WPSecurityNinja\Plugin;
class wf_sn_af_fix_themes_ver_check extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Update Outdated Themes', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will update all themes to the latest version.', 'security-ninja' ),
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
		// load up core upgrade classes
		if ( ! class_exists( 'Core_Upgrader' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		if ( ! function_exists( 'wp_update_plugins' ) ) {
			include_once ABSPATH . 'wp-includes/update.php';
		}

		if ( ! function_exists( 'wp_get_theme' ) ) {
			require_once ABSPATH . WPINC . '/theme.php';
		}

		// get a list of themes that need to be upgraded
		$current = get_site_transient( 'update_themes' );

		if ( isset( $current->response ) && is_array( $current->response ) ) {
			$themes_update_cnt = count( $current->response );
		} else {
			return self::get_label( 'msg_bad' );
		}

		$themes_to_update = array();
		if ( count( $current->response ) > 0 ) {
			foreach ( $current->response as $theme ) {
				$current_theme_status                = wp_get_theme( $theme['theme'] );
				$themes_to_update[ $theme['theme'] ] = $current_theme_status->get( 'Version' );
			}
		}

		$upgrader = new wf_sn_af_theme_upgrader();
		$result   = $upgrader->bulk_upgrade( array_keys( $themes_to_update ) );

		$msg = esc_html__( 'Update Result:', 'security-ninja' ) . '<br />';
		$themes_updated = 0;

		// upgrade themes and log result for each one
		foreach ( $themes_to_update as $theme => $ver ) {
			$new_theme_status = wp_get_theme( $theme );
			if ( version_compare( $themes_to_update[ $theme ], $new_theme_status->get( 'Version' ), '<' ) ) {
				/* translators: %1$s: Theme name, %2$s: Old version, %3$s: New version */
				$msg .= sprintf(
					'<strong>%1$s</strong> %2$s<br />',
					esc_html( $new_theme_status->get( 'Name' ) ),
					sprintf(
						esc_html__( 'Updated from %1$s to %2$s', 'security-ninja' ),
						esc_html( $themes_to_update[ $theme ] ),
						esc_html( $new_theme_status->get( 'Version' ) )
					)
				);
				$themes_updated++;
				} else {
					/* translators: %s: Theme name */
					$msg .= sprintf(
						'<strong>%s</strong> %s<br />',
						esc_html( $new_theme_status->get( 'Name' ) ),
						esc_html__( 'Update failed', 'security-ninja' )
					);
			}
		}

		if ( count( $themes_to_update ) == $themes_updated ) {
			wf_sn_af::mark_as_fixed( 'themes_ver_check' );
			return $msg;
		} else {
			return $msg;
		}
	}
}
