<?php

namespace ZionBuilderPro\Fonts\Providers;

use ZionBuilder\Settings;
use ZionBuilder\FontsManager\FontProvider;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class CustomFonts
 *
 * @package ZionBuilder\FontsManager\Fonts
 */
class CustomFonts extends FontProvider {
	public static function get_id() {
		return 'custom-fonts';
	}

	/**
	 * Main class constructor
	 *
	 * Will load the scripts into 'zionbuilder/frontend/after_load_styles'
	 */
	public function __construct() {
		add_filter( 'zionbuilder/cache/dynamic_css', [ $this, 'add_custom_fonts' ] );
	}

	/**
	 * This function will return the custom fonts from options
	 *
	 * @return [] custom fonts
	 */
	public function get_fonts() {
		return Settings::get_value( 'custom_fonts', [] );
	}

	/**
	 * This function will return the custom fonts css
	 *
	 * @return string|bool
	 */
	public function get_fonts_css() {
		$fonts = $this->get_fonts();

		if ( empty( $fonts ) ) {
			return false;
		}

		$custom_fonts = '';

		foreach ( $fonts as $font ) {
			$custom_fonts .= '@font-face { font-family: ' . $font['font_family'] . ';';
				if ( ! empty( $font['eot'] ) ) {
					$custom_fonts .= "src: url('" . $font['eot'] . "'),
					url('" . set_url_scheme( $font['eot'], 'relative' ) . "?#iefix') format('embedded-opentype');";
				}
	
				$urls = [];
				if ( ! empty( $font['woff'] ) ) {
					$urls[] = "url('" . set_url_scheme( $font['woff'], 'relative' ) . "') format('woff');";
				}
				if ( ! empty( $font['woff2'] ) ) {
					$urls[] = "url('" . set_url_scheme( $font['woff2'], 'relative' ) . "') format('woff2');";
				}
				if ( ! empty( $font['ttf'] ) ) {
					$urls[] = "url('" . set_url_scheme( $font['ttf'], 'relative' ) . "') format('truetype');";
				}
				if ( ! empty( $font['svg'] ) ) {
					$urls[] = "url('" . set_url_scheme( $font['svg'], 'relative' ) . "') format('svg');";
				}
	
				if ( 0 !== count($urls) ) {
					$custom_fonts .= "src: " . implode(',',$urls) . ";";
				}
	
				if ( ! empty( $font['weight'] ) ) {
					$custom_fonts .= 'font-weight: ' . $font['weight'] . ';';
				}
				$custom_fonts .= '}';
		}

		return $custom_fonts;
	}

	public function get_data_set() {
		$returned_fonts = [];
		$all_fonts      = $this->get_fonts();

		// Get info for each kit and prepare the response
		if ( is_array( $all_fonts ) ) {
			foreach ( $all_fonts as $font ) {
				$returned_fonts[] = [
					'id'   => $font['font_family'],
					'name' => $font['font_family'],
				];
			}
		}

		return $returned_fonts;
	}

	/**
	 * This function will add inline styles for custom fonts
	 *
	 * @return void
	 */
	public function add_custom_fonts( $css ) {
		$custom_fonts = $this->get_fonts_css();

		if ( ! empty( $custom_fonts ) ) {
			// add inline styles
			$css .= $custom_fonts;
		}

		return $css;
	}
}
