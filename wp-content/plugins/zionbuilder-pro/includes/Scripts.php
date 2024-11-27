<?php

namespace ZionBuilderPro;

use ZionBuilderPro\Environment;
use ZionBuilderPro\Utils;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Settings
 *
 * @package ZionBuilder
 */
class Scripts {

	/**
	 * Keeps a reference to the project assets root URL
	 *
	 * This folder is automatically created during build process
	 *
	 * @var string The asset root folder URL for this project
	 */
	private $assets_root_url = null;

	/**
	 * Keeps a reference to the project assets root
	 *
	 * This folder is automatically created during build process
	 *
	 * @var string The asset root folder for this project
	 */
	private $assets_root_path = null;

	/**
	 * Holds the value of the debug flag
	 *
	 * @var boolean
	 */
	private $is_debug = false;


	public function __construct() {
		$this->setup_environment();

		$this->is_debug = Environment::is_debug();

		if ( $this->is_debug ) {
			add_filter( 'script_loader_src', [ $this, 'remove_script_version' ], 15, 1 );
			add_filter( 'style_loader_src', [ $this, 'remove_script_version' ], 15, 1 );
			add_filter( 'script_loader_tag', [ $this, 'add_module_attribute' ], 10, 3 );
		}
	}

	public function remove_script_version( $src ) {
		$parts = explode( '?ver', $src );
		return $parts[0];
	}

	public function add_module_attribute( $tag, $handle, $src ) {
		// if not your script, do nothing and return original $tag
		if ( $this->is_debug ) {
			$scripts_map = Environment::get_value( 'devScripts', [] );

			if ( in_array( $src, $scripts_map, true ) ) {
				// phpcs:ignore WordPress.WP.EnqueuedResources.NonEnqueuedScript
				$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
			}
		}

		// change the script tag by adding type="module" and return it.
		return $tag;
	}

	/**
	 * Sets the root path and url for the assets
	 *
	 * @return void
	 */
	public function setup_environment() {
		// Get the project root
		$output_directory       = Environment::get_value( 'outputDir' );
		$this->assets_root_url  = trailingslashit( Utils::get_file_url( $output_directory, 'relative' ) );
		$this->assets_root_path = trailingslashit( Utils::get_file_path( $output_directory ) );
	}

	/**
	 * Register a script.
	 *
	 * Registers the script if $src provided (does NOT overwrite).
	 *
	 * @since 1.0.0
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       relative URL of the script, or path of the script relative to the Project root directory.
	 *                                    Default empty.
	 * @param string[]         $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                    as a query string for cache busting purposes. If version is set to false, a version
	 *                                    number is automatically added equal to current installed WordPress version.
	 *                                    If set to null, no version is added.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 *
	 * @return void
	 */
	public function register_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {
		wp_register_script( $handle, $this->get_script_url( $src, 'js' ), $deps, $ver, $in_footer );
	}

	/**
	 * Enqueue a script.
	 *
	 * Registers the script if $src provided (does NOT overwrite), and enqueues it.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $handle    Name of the script. Should be unique.
	 * @param string           $src       relative URL of the script, or path of the script relative to the Project root directory.
	 *                                    Default empty.
	 * @param string[]         $deps      Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver       Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                    as a query string for cache busting purposes. If version is set to false, a version
	 *                                    number is automatically added equal to current installed WordPress version.
	 *                                    If set to null, no version is added.
	 * @param bool             $in_footer Optional. Whether to enqueue the script before </body> instead of in the <head>.
	 *                                    Default 'false'.
	 *
	 * @return void
	 */
	public function enqueue_script( $handle, $src = '', $deps = [], $ver = false, $in_footer = false ) {
		wp_enqueue_script( $handle, $this->get_script_url( $src, 'js' ), $deps, $ver, $in_footer );
	}

	/**
	 * Register a style.
	 *
	 * Registers the style if $src provided (does NOT overwrite).
	 *
	 * @since 1.0.2
	 *
	 * @param string           $handle Name of the style. Should be unique.
	 * @param string           $src    relative URL of the style, or path of the style relative to the Project root directory.
	 *                                 Default empty.
	 * @param string[]         $deps   Optional. An array of registered style handles this style depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying style version number, if it has one, which is added to the URL
	 *                                 as a query string for cache busting purposes. If version is set to false, a version
	 *                                 number is automatically added equal to current installed WordPress version.
	 *                                 If set to null, no version is added.
	 * @param string           $media  (Optional) The media for which this stylesheet has been defined. Accepts media types like
	 *                                 'all', 'print' and 'screen', or media queries like '(orientation: portrait)' and
	 *                                 '(max-width: 640px)'.
	 *
	 * @return void
	 */
	public function register_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
		wp_register_style( $handle, $this->get_script_url( $src, 'css' ), $deps, $ver, $media );
	}


	/**
	 * Enqueue a style.
	 *
	 * Registers the style if $src provided (does NOT overwrite), and enqueues it.
	 *
	 * @since 1.0.0
	 *
	 * @param string           $handle Name of the script. Should be unique.
	 * @param string           $src    relative URL of the script, or path of the script relative to the Project root directory.
	 *                                 Default empty.
	 * @param string[]         $deps   Optional. An array of registered script handles this script depends on. Default empty array.
	 * @param string|bool|null $ver    Optional. String specifying script version number, if it has one, which is added to the URL
	 *                                 as a query string for cache busting purposes. If version is set to false, a version
	 *                                 number is automatically added equal to current installed WordPress version.
	 *                                 If set to null, no version is added.
	 * @param string           $media  Optional. The media for which this stylesheet has been defined.
	 *                                 Default 'all'. Accepts media types like 'all', 'print' and 'screen', or media queries like
	 *                                 '(orientation: portrait)' and '(max-width: 640px)'.
	 *
	 *
	 * @return void
	 */
	public function enqueue_style( $handle, $src = '', $deps = [], $ver = false, $media = 'all' ) {
		wp_enqueue_style( $handle, $this->get_script_url( $src, 'css' ), $deps, $ver, $media );
	}


	/**
	 * Returns the script url to be used in enqueue functions
	 *
	 * If the project is in development mode, it will use webpack dev server host
	 * instead of the dist folder public path
	 *
	 * @param string $path The script relative path
	 *
	 * @return string
	 */
	public function get_script_url( $path, $extension ) {
		$is_debug = Environment::is_debug();
		if ( $is_debug && $extension === 'js' ) {
			$scripts_map = Environment::get_value( 'devScripts', [] );

			if ( isset( $scripts_map[$path] ) ) {
				return $scripts_map[$path];
			}
		}

		return $this->assets_root_url . $path . '.' . $extension;
	}

	/**
	 * Returns the script path
	 *
	 * @param string $path The script relative path
	 *
	 * @return string
	 */
	public function get_script_path( $path ) {
		return $this->assets_root_path . $path;
	}
}
