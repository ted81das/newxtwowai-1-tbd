<?php

namespace ZionBuilderPro;

use ZionBuilderPro\Plugin;
use ZionBuilderPro\License;

class Admin {
	public function __construct() {
		add_action( 'zionbuilder/admin/before_admin_scripts', [ $this, 'enqueue_scripts' ] );
	}

	public function enqueue_scripts() {
		Plugin::instance()->scripts->enqueue_style(
			'zion-pro-admin-styles',
			'admin',
			[],
			Plugin::instance()->get_version()
		);

		if ( is_rtl() ) {
			Plugin::instance()->scripts->enqueue_style(
				'zion-pro-editor-rtl-styles',
				'rtl-pro',
				[],
				Plugin::instance()->get_version()
			);
		};

		Plugin::instance()->scripts->enqueue_script(
			'zion-pro-admin-script',
			'admin',
			[
				'zb-admin',
			],
			Plugin::instance()->get_version(),
			true
		);

		wp_localize_script(
			'zion-pro-admin-script',
			'ZionProRestConfig',
			[
				'nonce'     => wp_create_nonce( 'wp_rest' ),
				'rest_root' => esc_url_raw( rest_url() ),
			]
		);

		wp_set_script_translations( 'zion-pro-admin-script', 'zionbuilder-pro', Plugin::instance()->get_root_path() . '/languages' );
		wp_localize_script( 'zion-pro-admin-script', 'ZionBuilderProInitialData', $this->get_editor_initial_data() );
	}

	private function get_editor_initial_data() {
		return apply_filters(
			'zionbuilderpro/admin/js_data',
			[
				'dynamic_fields_data' => Plugin::instance()->dynamic_content_manager->get_fields_data(),
				'dynamic_fields_info' => Plugin::instance()->dynamic_content_manager->get_fields_for_editor(),
				'license_details'     => License::get_license_details(),
				'license_key'         => License::get_license_key(),
				'schemas'             => [],
			]
		);
	}
}
