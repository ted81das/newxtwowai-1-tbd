<?php
namespace WPSecurityNinja\Plugin;

class wf_sn_af_fix_config_location extends wf_sn_af {
	static function get_label( $label ) {
		$labels = array(
			'title'   => esc_html__( 'Move wp-config.php', 'security-ninja' ),
			'fixable' => true,
			'info'    => esc_html__( 'Move <i>wp-config.php</i> one level up in the folder structure.', 'security-ninja' ),
			'msg_ok'  => esc_html__( '<i>wp-config.php</i> moved successfully.', 'security-ninja' ),
			'msg_bad' => esc_html__( '<i>wp-config.php</i> move failed.', 'security-ninja' ),
		);
		if ( ! array_key_exists( $label, $labels ) ) {
			return '';
		} else {
			return $labels[ $label ];
		}
	}

	static function fix() {
		$backup_timestamp = time();
		// check if a wp-config file doesn't already exist one level up
		if ( file_exists( dirname( dirname( wf_sn_af::$wp_config_path ) ) . '/wp-config.php' ) ) {
			return esc_html__( 'A wp-config.php file already exists in the new location. Can\'t overwrite because it may belong to an existing installation.', 'security-ninja' );
		}

		// check if a wp-config file exists in the default location
		if ( ! file_exists( dirname( wf_sn_af::$wp_config_path ) . '/wp-settings.php' ) ) {
			return esc_html__( 'wp-config.php file is already in a non-default location.', 'security-ninja' );
		}

		// backup wp-config and generate hash
		wf_sn_af::backup_file( wf_sn_af::$wp_config_path, $backup_timestamp, 'core_updates_check' );
			$current_config_hash = wf_sn_af::generate_hash_file( wf_sn_af::$wp_config_path );

		copy( wf_sn_af::$wp_config_path, dirname( dirname( wf_sn_af::$wp_config_path ) ) . '/wp-config.php' );

		// check if file was copied successfully and delete it from the old location
		$new_config_hash = wf_sn_af::generate_hash_file( wf_sn_af::$wp_config_path );
		unlink( wf_sn_af::$wp_config_path );

		// if WordPress fails to load or file has doesn't match restore everything and abort
		$no_wsod = wf_sn_af::test_wordpress_status();
		if ( ! $no_wsod || $current_config_hash !== $new_config_hash ) {
			wf_sn_af::backup_file_restore( wf_sn_af::$wp_config_path, $backup_timestamp, 'core_updates_check' );
			unlink( dirname( dirname( wf_sn_af::$wp_config_path ) ) . '/wp-config.php' );
			return self::get_label( 'msg_bad' );
		} else {
			wf_sn_af::mark_as_fixed( 'config_location' );
			return self::get_label( 'msg_ok' );
		}
	}
}
