<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}



/*
 * @version       1.0.0
 * @package       AdminBarEditor
 * @license       Copyright AdminBarEditor
 */

if ( ! function_exists( 'jlt_admin_bar_editor_option' ) ) {
	/**
	 * Get setting database option
	 *
	 * @param string $section default section name jlt_admin_bar_editor_general .
	 * @param string $key .
	 * @param string $default .
	 *
	 * @return string
	 */
	function jlt_admin_bar_editor_option( $section = 'jlt_admin_bar_editor_general', $key = '', $default = '' ) {
		$settings = get_option( $section );

		return isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
	}
}

if ( ! function_exists( 'jlt_admin_bar_editor_exclude_pages' ) ) {
	/**
	 * Get exclude pages setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function jlt_admin_bar_editor_exclude_pages() {
		return jlt_admin_bar_editor_option( 'jlt_admin_bar_editor_triggers', 'exclude_pages', array() );
	}
}

if ( ! function_exists( 'jlt_admin_bar_editor_exclude_pages_except' ) ) {
	/**
	 * Get exclude pages except setting option data
	 *
	 * @return string|array
	 *
	 * @version 1.0.0
	 */
	function jlt_admin_bar_editor_exclude_pages_except() {
		return jlt_admin_bar_editor_option( 'jlt_admin_bar_editor_triggers', 'exclude_pages_except', array() );
	}
}