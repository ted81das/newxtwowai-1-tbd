<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_deactivated_plugins extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Delete inactive plugins', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Fix will delete inactive plugins. There is NO undo.', 'security-ninja' ),
			'msg_ok'  => esc_html__( 'Plugins removed successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( 'Plugins removal failed.', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		$all_plugins    = get_plugins();
		$active_plugins = get_option( 'active_plugins', array() );
		$remove_plugins = array();
		$msg            = '';
		$failed         = false;

		// loop though all plugins and delete inactive ones
		foreach ( $all_plugins as $plugin_path => $plugin_data ) {
			$success = false;
			if ( ! in_array( $plugin_path, $active_plugins ) ) {
				if ( strpos( $plugin_path, '/' ) !== false ) { // if plugin is a folder
					$plugin_path_array = explode( '/', $plugin_path );
					if ( 2 == count( $plugin_path_array ) ) {  // make sure it's a valid plugin path and not some header from a subfolder inside a plugin
						if ( wf_sn_af::directory_unlink( WP_PLUGIN_DIR . '/' . $plugin_path_array[0] ) ) {
							$success = true;
						}
					}
				} else { // if plugin is a single file
					if ( unlink( WP_PLUGIN_DIR . '/' . $plugin_path ) ) {
						$success = true;
					}
				}

				if ( $success ) {
					$msg .= '<strong>' . esc_html( $all_plugins[ $plugin_path ]['Name'] ) . '</strong> ' . esc_html__( 'Removed.', 'security-ninja' ) . '<br />';
				} else {
					$msg   .= '<strong>' . esc_html( $all_plugins[ $plugin_path ]['Name'] ) . '</strong> ' . esc_html__( 'Could not be removed.', 'security-ninja' ) . '<br />';
					$failed = true;
				}
			}
		}

		if ( ! $failed ) {
			wf_sn_af::mark_as_fixed( 'deactivated_plugins' );
			return $msg;
		} else {
			return $msg;
		}
	}
}
