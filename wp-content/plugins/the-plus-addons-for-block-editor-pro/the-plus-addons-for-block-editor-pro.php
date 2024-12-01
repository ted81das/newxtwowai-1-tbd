<?php
/*
* Plugin Name: Nexter Blocks Pro
* Plugin URI: https://nexterwp.com/nexter-blocks/
* Description: Highly customizable WordPress Gutenberg blocks with 90+ advanced blocks and 1000+ ready-to-use starter templates to create stunning websites effortlessly.
* Version: 4.0.5
* Author: POSIMYTH
* Author URI: https://posimyth.com
* Tested up to: 6.7
* Text Domain: tpgbp
*/
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

defined( 'TPGBP_VERSION' ) or define( 'TPGBP_VERSION', '4.0.5' );
define( 'TPGBP_FILE__', __FILE__ );

define( 'TPGBP_PATH', plugin_dir_path( __FILE__ ) );
define( 'TPGBP_BASENAME', plugin_basename(__FILE__) );
define( 'TPGBP_BDNAME', basename( dirname(__FILE__)) );
define( 'TPGBP_URL', plugins_url( '/', __FILE__ ) );
define( 'TPGBP_ASSETS_URL', TPGBP_URL );
define( 'TPGBP_INCLUDES_URL', TPGBP_PATH.'includes/' );
define( 'TPGBP_CATEGORY', 'tpgb' );
define( 'TPGBP_DEVELOPER', false );

function tpgb_pro_Dynamic_Gutenberg(){
	
	if ( ! version_compare( PHP_VERSION, '5.6.40', '>=' ) ) {
		add_action( 'admin_notices', 'tpgb_pro_check_php_version' ); //check php version 
	} elseif ( ! version_compare( get_bloginfo( 'version' ), '4.7.1', '>=' ) ) {
		add_action( 'admin_notices', 'tpgb_pro_check_wp_version' ); //check wordpress version
	} else {
		if(!defined("TPGB_VERSION")){
			add_action( 'admin_notices', 'tpgb_pro_gutenberg_load_notice' ); //Load Nexter Block Plugin
			return;
		} else if ( defined( 'TPGB_VERSION' ) && ! version_compare( TPGB_VERSION, '4.0.0', '>=' ) ) {
			add_action( 'admin_notices', 'tpgb_pro_check_tpag_version' );
		}
		
		require_once 'plus-block-loader.php';
	}
	
}
add_action('plugins_loaded', 'tpgb_pro_Dynamic_Gutenberg');

function tpgb_pro_gutenberg_load_notice(){
	
    $plugin = 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php';
	
	if ( tpgb_pro_plugin_activated() ) {
	
		if ( ! current_user_can( 'activate_plugins' ) ) { return; }
		
		$activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $plugin . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $plugin );
		
		$admin_notice = '<p>' . esc_html__( 'Please activate the FREE Version of Nexter Blocks to use the Premium version. Both the plugins are required to use all the 90+ WordPress Blocks.', 'tpgbp' ) . '</p>';
		
		$admin_notice .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, esc_html__( 'Activate Nexter Blocks', 'tpgbp' ) ) . '</p>';
		
	} else {
	
		if ( ! current_user_can( 'install_plugins' ) ) { return; }
		
		$install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=the-plus-addons-for-block-editor' ), 'install-plugin_the-plus-addons-for-block-editor' );
		
		$admin_notice = '<p>' . esc_html__( 'Please activate the FREE Version of Nexter Blocks to use the Premium version. Both the plugins are required to use all the 90+ WordPress Blocks.', 'tpgbp' ) . '</p>';
		
		$admin_notice .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, esc_html__( 'Install & Activate Nexter Blocks Free', 'tpgbp' ) ) . '</p>';
		
	}
	
	echo wp_kses_post('<div class="notice notice-error is-dismissible">'.$admin_notice.'</div>');
}

/**
 * Nexter Blocks check minimum PHP version.
 *
 * Warning when the site doesn't have the minimum required PHP version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function tpgb_pro_check_php_version() {
	/* translators: Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version. */
	$check_message      = sprintf( esc_html__( 'Nexter Blocks requires PHP version %s+. The plugin is currently not running. Please update to the latest PHP version.', 'tpgbp' ), '5.6.40' );
	
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks check minimum WordPress version.
 *
 * Warning when the site doesn't have the minimum required WordPress version.
 *
 * @since 1.0.0
 *
 * @return void
 */
function tpgb_pro_check_wp_version() {
	/* translators: Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version. */
	$check_message      = sprintf( esc_html__( 'Nexter Blocks requires at least WordPress version %s+. Because you’re using an older version, the plugin is currently not running. Please update WordPress to the latest version.', 'tpgbp' ), '4.7.1' );
	
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks Free check minimum version 4.0.0.
 *
 * Warning when the site doesn't have the minimum required Nexter Blocks version.
 *
 * @since 4.0.0
 *
 * @return void
 */
function tpgb_pro_check_tpag_version() {
	/* translators: Nexter Blocks Pro requires Nexter Blocks Free version %s+. Since you’re using an older version, the plugin is currently not active. */
	$check_message      = sprintf( esc_html__( 'Nexter Blocks Pro requires Nexter Blocks Free version %s+. Since you’re using an older version, the plugin is currently not active.', 'tpgbp' ), '4.0.0' );
	
	$display_message = sprintf( '<div class="error">%s</div>', wpautop( $check_message ) );
	
	echo wp_kses_post( $display_message );
}

/**
 * Nexter Blocks Lite activated or not
 * @since 1.0.0
*/
function tpgb_pro_plugin_activated() {
	$file_path = 'the-plus-addons-for-block-editor/the-plus-addons-for-block-editor.php';
	$installed_plugins = get_plugins();
	
	return isset( $installed_plugins[ $file_path ] );
}

/**
 * Plugin activated action
 * @since  1.0.0
 */
function tpgb_activated_plugin( $plugin ){
	if( $plugin == plugin_basename( __FILE__ ) ) {
		$activate_label=get_option( 'tpgb_white_label' );			
		if ( !empty($activate_label["tpgb_hidden_label"]) && $activate_label["tpgb_hidden_label"] === 'on' ) {
			$activate_label["tpgb_hidden_label"] = '';
			update_option('tpgb_white_label', $activate_label);
		}
	}
}
add_action( 'activated_plugin', 'tpgb_activated_plugin', 10 );

/* 
 * Nexter Blocks Pro Plugin Update Message
 * @since 1.0.0
 */
add_action('in_plugin_update_message-the-plus-addons-for-block-editor-pro/the-plus-addons-for-block-editor-pro.php','tpgb_pro_plugin_update_message',10,2);
function tpgb_pro_plugin_update_message( $data, $response ){
	if( isset( $data['upgrade_notice'] ) && !empty($data['upgrade_notice']) ) {
		printf(
			'<div class="update-message">%s</div>',
			esc_html(wpautop( $data['upgrade_notice'] ))
		);
	}
}

if( !class_exists( 'Tpgb_SL_Plugin_Updater' ) ) {
	require_once TPGBP_INCLUDES_URL . 'plus-library/Tpgb_SL_Plugin_Updater.php';
	function tpgb_plugin_updater(){
		if(class_exists('Tpgb_Pro_Library')){
			$Tpgb_Pro_Library = Tpgb_Pro_Library::get_instance();
			$status = $Tpgb_Pro_Library->tpgb_activate_status();
			if(class_exists( 'Tpgb_SL_Plugin_Updater' ) && !empty($status) && $status=='valid'){
				$license = get_option( 'tpgb_activate' );
				
				if ( !empty( $license ) && isset( $license['tpgb_activate_key'] ) && !empty( $license['tpgb_activate_key'] ) ) {
					$edd_updater = new Tpgb_SL_Plugin_Updater( 'https://store.posimyth.com', __FILE__, array(
						'version' => TPGBP_VERSION,
						'license' => $license['tpgb_activate_key'],		
						//'item_name' => 'Nexter Blocks',
						'item_id' => 99119,
						'author' => 'POSIMYTH Themes',
						'url' => home_url(),
					));
				}
			}
		}
	}
	add_action( 'admin_init', 'tpgb_plugin_updater' , 0 );
}
?>