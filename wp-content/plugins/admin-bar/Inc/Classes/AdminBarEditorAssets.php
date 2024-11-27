<?php

namespace JewelTheme\AdminBarEditor\Inc\Classes;

use JewelTheme\AdminBarEditor\Inc\Utils;

// no direct access allowed
if (!defined('ABSPATH'))  exit;

/**
 * Jewel Theme
 * @package Jewel Theme: Admin Bar Editor
 *
 * @author Jewel Theme <support@jeweltheme.com>
 */

class AdminBarEditorAssets extends AdminBarEditorModel
{
    public $options;
    public $adminify_ui;

    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'adminbar_editor_enqueue_scripts'], 100);
        add_action('admin_footer', [$this, 'adminbar_editor_enqueue_scripts_backend'], 999);
        add_action('wp_footer', [$this, 'adminbar_editor_enqueue_scripts_frontend'], 999);
        // add_action('wp_enqueue_scripts', [$this, 'adminbar_editor_enqueue_styles_frontend'], 999);

        // add_action('admin_enqueue_scripts', [$this, 'jltwp_adminify_admin_scripts'], 100);
        add_action( 'admin_enqueue_scripts', [$this, 'jlt_admin_bar_css'], 999);
        add_action( 'wp_enqueue_scripts', array( $this, 'jlt_admin_bar_editor_enqueue_scripts' ), 100 );
    }

    /**
     * Enqueue Scripts
     *
     * @method wp_enqueue_scripts()
     */
    public function jlt_admin_bar_editor_enqueue_scripts() {

        // CSS Files .
        // wp_enqueue_style( 'admin-bar-frontend', JLT_ADMIN_BAR_EDITOR_ASSETS . 'css/admin-bar-frontend.css', JLT_ADMIN_BAR_EDITOR_VER, 'all' );

        // JS Files .
        // wp_enqueue_script( 'admin-bar-frontend', JLT_ADMIN_BAR_EDITOR_ASSETS . 'js/admin-bar-frontend.js', array( 'jquery' ), JLT_ADMIN_BAR_EDITOR_VER, true );

        if(!is_admin()){
            $frontend_admin_bar_css = '#wpadminbar .ab-top-menu .ab-sub-wrapper .ab-submenu .ab-item {
                height: auto !important;
            }';
            wp_add_inline_style( 'admin-bar', $frontend_admin_bar_css );
        }
    }



    // Admin Bar Css
    public function jlt_admin_bar_css()
    {
        
        $admin_bar_items            = (new AdminBarEditorOptions())->get();

        if(empty($admin_bar_items['admin_bar_settings'])) return;

        $admin_bar_settings = $admin_bar_items['admin_bar_settings'];

        if( class_exists('\WPAdminify\Inc\Admin\AdminSettings')){
            $adminify_options = (array) \WPAdminify\Inc\Admin\AdminSettings::get_instance()->get();
            $this->adminify_ui = $adminify_options['admin_ui'];
        }

        $admin_bar_css = '';
        $admin_bar_css = $this->jlt_admin_bar_dymanic_styles($admin_bar_settings);

        // $admin_bar_css .= ' </style>';
		// echo Utils::wp_kses_custom($admin_bar_css);
		// echo $admin_bar_css;

        if(is_admin()){
            wp_add_inline_style( 'admin-bar', $admin_bar_css );
        }

    }

    public function jlt_admin_bar_dymanic_styles($admin_bar_settings) {
        $admin_bar_css  = '';
		// $admin_bar_css .= '<style type="text/css">';

        $bg_type = !empty( $admin_bar_settings['admin_bar_bg_type'] ) ? $admin_bar_settings['admin_bar_bg_type'] : 'color';

        // Background Color
        if ( $bg_type === 'color' && !empty( $admin_bar_settings['admin_bar_bg_color']) ) {
            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper {
                        background: ' . esc_attr($admin_bar_settings['admin_bar_bg_color']) . ';
                    }';
            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar {
                    background: ' . esc_attr($admin_bar_settings['admin_bar_bg_color']) . ';
                }';
            }
		}

        // Background Color Gradient
        if ( $bg_type === 'gradient' && !empty( $admin_bar_settings['admin_bar_bg_gradient']) ) {
            $direction = array_key_exists("direction", $admin_bar_settings['admin_bar_bg_gradient']) ? esc_attr($admin_bar_settings['admin_bar_bg_gradient']['direction']) : "to right";

            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper {
                    background-image : linear-gradient(' . $direction . ', ' . esc_attr($admin_bar_settings['admin_bar_bg_gradient']['first_color']) . ' , ' . esc_attr($admin_bar_settings['admin_bar_bg_gradient']['second_color']) . ');
                }';
            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar {
                    background-image : linear-gradient(' .  $direction . ', ' . esc_attr($admin_bar_settings['admin_bar_bg_gradient']['first_color']) . ' , ' . esc_attr($admin_bar_settings['admin_bar_bg_gradient']['second_color']) . ');
                }';
            }
		}

        // Text Color
		if (!empty( $admin_bar_settings['admin_bar_text_color']['color'] )) {
            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu > .adminify-top-menu-item:not(.adminify-top-menu-new-content) > a, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--preview .wp-adminify-preview-trigger > .dashicons {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['color']) . ';
                }';

                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-hambuger-menu > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .frame-adminify-search-icon > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-comment-trigger .topbar-icon > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .adminify-mode-icon > svg path {
                    fill:'. esc_attr($admin_bar_settings['admin_bar_text_color']['color']) . ';
                }';

            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-label:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon), .jlt-admin-bar #wpadminbar .ab-item:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon), .jlt-admin-bar #wpadminbar .ab-icon:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon) {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['color']) . '!important;
                }';

                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-label:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon)::before, .jlt-admin-bar #wpadminbar .ab-item:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon)::before, .jlt-admin-bar #wpadminbar .ab-icon:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label, .ab-sub-wrapper .ab-icon)::before {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['color']) . '!important;
                }';
            }

		}

        // Text Color Hover
        if (!empty( $admin_bar_settings['admin_bar_text_color']['hover'] )) {
            $hover_bg = !empty( $admin_bar_settings['admin_bar_text_color']['bg_hover'] ) ? 'background: ' . $admin_bar_settings['admin_bar_text_color']['bg_hover'] . ';' : '';

            if( !empty($this->adminify_ui) ) {                
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu > .adminify-top-menu-item:not(.adminify-top-menu-new-content) > a:hover, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--preview .wp-adminify-preview-trigger > .dashicons:hover {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['hover']) . '!important;
                }';

                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-hambuger-menu:hover > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .frame-adminify-search-icon:hover > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-comment-trigger .topbar-icon:hover > svg path, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .adminify-mode-icon:hover > svg path {
                    fill:' . esc_attr($admin_bar_settings['admin_bar_text_color']['hover']) . '!important;
                }';
            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-top-menu > li:hover > .ab-item {
                    ' . $hover_bg . '
                }';

                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-label:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label), .jlt-admin-bar #wpadminbar .ab-item:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label), .jlt-admin-bar #wpadminbar .ab-icon:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label) {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['hover']) . '!important;
                }';

                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-label:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label)::before, .jlt-admin-bar #wpadminbar .ab-label:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label) ::before, .jlt-admin-bar #wpadminbar .ab-item:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label)::before, .jlt-admin-bar #wpadminbar .ab-item:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label) ::before, .jlt-admin-bar #wpadminbar .ab-icon:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label)::before, .jlt-admin-bar #wpadminbar .ab-icon:hover:not(.ab-sub-wrapper .ab-item, .ab-sub-wrapper .ab-label) ::before {
                    color:' . esc_attr($admin_bar_settings['admin_bar_text_color']['hover']) . '!important;
                }';
            }
        }

        // Dropdown
        if ( !empty( $admin_bar_settings['admin_bar_dropdown_color']) ) {
            $wraper_bg = !empty( $admin_bar_settings['admin_bar_dropdown_color']['wrapper_bg'] ) ? 'background: ' . $admin_bar_settings['admin_bar_dropdown_color']['wrapper_bg'] . ' !important;' : '';

            $item_bg = !empty( $admin_bar_settings['admin_bar_dropdown_color']['bg_color'] ) ? 'background: ' . $admin_bar_settings['admin_bar_dropdown_color']['bg_color'] . ' !important;' : '';

            $item_color = !empty( $admin_bar_settings['admin_bar_dropdown_color']['link_color'] ) ? 'color: ' . $admin_bar_settings['admin_bar_dropdown_color']['link_color'] . ' !important;' : '';
            
            $item_fill_color = !empty( $admin_bar_settings['admin_bar_dropdown_color']['link_color'] ) ? 'fill: ' . $admin_bar_settings['admin_bar_dropdown_color']['link_color'] . ' !important;' : '';
            
            $item_stroke_color = !empty( $admin_bar_settings['admin_bar_dropdown_color']['link_color'] ) ? 'stroke: ' . $admin_bar_settings['admin_bar_dropdown_color']['link_color'] . ' !important;' : '';

            $item_color_hover = !empty( $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] ) ? 'color: ' . $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] . ' !important;' : '';
            
            $item_fill_color_hover = !empty( $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] ) ? 'fill: ' . $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] . ' !important;' : '';
            
            $item_stroke_color_hover = !empty( $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] ) ? 'stroke: ' . $admin_bar_settings['admin_bar_dropdown_color']['hover_color'] . ' !important;' : '';

            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item > .adminify-dropdown {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item > .adminify-dropdown::before {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item > .adminify-dropdown > .adminify-top-menu-item:hover {
                    '. $item_bg .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item > .adminify-dropdown > .adminify-top-menu-item:hover a {
                    '. $item_color_hover .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item > .adminify-dropdown > .adminify-top-menu-item a {
                    '. $item_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown::before {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown > div span {
                    '. $item_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown > div svg path {
                    '. $item_fill_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown > div:hover {
                    '. $item_bg .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown > div:hover span {
                    '. $item_color_hover .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar #adminify-color-mode-wrapper .light-dark-dropdown > div:hover svg path {
                    '. $item_fill_color_hover .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper::before {
                    ' . $wraper_bg . '
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper .wp-adminify-user-info .wp-adminify-user-name, .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper .wp-adminify-user-info > span {
                    '. $item_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper > ul li a {
                    '. $item_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper > ul li a svg path {
                    '. $item_stroke_color .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper > ul li:hover {
                    '. $item_bg .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper > ul li:hover a {
                    '. $item_color_hover .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .wp-adminify--user--account .wp-adminify--user--wrapper > ul li:hover a svg path {
                    '. $item_stroke_color_hover .'
                }';

            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar .ab-sub-wrapper, .jlt-admin-bar #wpadminbar .ab-sub-secondary {
                    ' . $wraper_bg . '
                }
                .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li .ab-label, .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li .ab-item, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li .ab-label, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li .ab-item {
                    ' . $item_color . '
                }
                .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li .ab-label .ab-icon::before, .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li .ab-item .ab-icon::before, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li .ab-label .ab-icon::before, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li .ab-item .ab-icon::before {
                    ' . $item_color . '
                }
                .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li:hover, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li:hover {
                    ' . $item_bg . '
                }
                .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li:hover > .ab-item .ab-label, .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li:hover > .ab-item .ab-item, .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li:hover > .ab-item, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li:hover > .ab-item .ab-label, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li:hover > .ab-item .ab-item, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li:hover > .ab-item {
                    ' . $item_color_hover . '
                }
                .jlt-admin-bar #wpadminbar .ab-sub-wrapper > ul > li:hover .ab-icon::before, .jlt-admin-bar #wpadminbar .ab-sub-secondary > ul > li:hover .ab-icon::before {
                    ' . $item_color_hover . '
                }';
            }
		}

        // New Button
		if (!empty( $admin_bar_settings['admin_bar_new_button_color'] )) {
            $bg_color = !empty( $admin_bar_settings['admin_bar_new_button_color']['bg_color'] ) ? 'background: ' . $admin_bar_settings['admin_bar_new_button_color']['bg_color']  . ' !important;' : '';

            $color = !empty( $admin_bar_settings['admin_bar_new_button_color']['link_color'] ) ? 'color: ' . $admin_bar_settings['admin_bar_new_button_color']['link_color']  . ' !important;' : '';

            $hover_color = !empty( $admin_bar_settings['admin_bar_new_button_color']['hover_color'] ) ? 'color: ' . $admin_bar_settings['admin_bar_new_button_color']['hover_color']  . ' !important;' : '';

            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item.adminify-top-menu-new-content > a {
                    ' . $bg_color . '
                    '. $color  .'
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar-wrapper .adminify-toolbar .adminify-top-menu .adminify-top-menu-item.adminify-top-menu-new-content > a:hover {
                    '. $hover_color  .'
                }';
            } else {
                $admin_bar_css .= '.jlt-admin-bar #wpadminbar #wp-admin-bar-new-content > a.ab-item {
                    ' . $bg_color . '
                }
                .jlt-admin-bar #wpadminbar #wp-admin-bar-new-content > a.ab-item > .ab-label, .jlt-admin-bar #wpadminbar #wp-admin-bar-new-content > a.ab-item > .ab-icon::before {
                    '. $color  .'
                }
                .jlt-admin-bar #wpadminbar #wp-admin-bar-new-content:hover > a.ab-item > .ab-label, .jlt-admin-bar #wpadminbar #wp-admin-bar-new-content:hover > a.ab-item > .ab-icon::before {
                    '. $hover_color  .'
                }';

            }
		}

        // Admin Bar Position
        if (!empty( $admin_bar_settings['admin_bar_position'] ) && $admin_bar_settings['admin_bar_position'] === 'bottom' ) {

            if( !empty($this->adminify_ui) ) {
                $admin_bar_css .= '.adminify-ui #frame-adminify-app {
                    display: flex;
                    flex-direction: column;
                    flex-direction: column-reverse;
                }
                .adminify-ui #frame-adminify-app .adminify-frame-wrapper {
                    height: calc(100vh - 60px);
                }
                .adminify-ui #frame-adminify-app .adminify-frame-wrapper .adminify-frame-content {
                    padding-bottom: 0;
                    padding-top: 0.5rem;
                    height: 100%;
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar {
                    padding-left: 1rem;
                    padding-right: 1rem;
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-top-menu .adminify-dropdown {
                    top: unset;
                    bottom: 39px !important;
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-top-menu .adminify-dropdown::before {
                    bottom: -7px;
                    top: unset;
                    transform: rotate(225deg);
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-admin-bar-top-secondary #adminify-color-mode-wrapper .light-dark-dropdown {
                    top: unset;
                    bottom: 40px;
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-admin-bar-top-secondary #adminify-color-mode-wrapper .light-dark-dropdown::before {
                    top: unset;
                    bottom: -7px;
                    transform: rotate(225deg);
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-admin-bar-top-secondary .wp-adminify--user--account .wp-adminify--user--wrapper {
                    top: unset;
                    bottom: calc(100% + 8px);
                }
                .adminify-ui #frame-adminify-app .adminify-toolbar .adminify-admin-bar-top-secondary .wp-adminify--user--account .wp-adminify--user--wrapper::before {
                    top: unset;
                    bottom: -7px;
                    transform: rotate(225deg);
                }';
            } else {
                $admin_bar_css .= 'body.jlt-admin-bar {
                    margin-top: -28px;
                    padding-bottom: 28px;
                }
                body.jlt-admin-bar.adminify-ui {
                    margin-top: -66px;
                    padding-bottom: 66px;
                }
                body.jlt-admin-bar.adminify-ui .adminify-top_bar {
                    top: auto !important;
                    bottom: 0;
                    border-bottom: 0;
                    border-top: 1px solid #dedee7;
                }
                body.jlt-admin-bar.adminify-ui #adminmenuwrap {
                    padding-bottom: 66px;
                }
                body.jlt-admin-bar #wpadminbar {
                    top: auto !important;
                    bottom: 0;
                }
                body.jlt-admin-bar #wpadminbar .ab-sub-wrapper {
                    bottom: 100% !important;
                }
                body.jlt-admin-bar #wpfooter {
                    padding-bottom: 48px;
                }';
            }
        }

        return $admin_bar_css;
    }

    public function adminbar_editor_enqueue_styles_frontend()
    {
        wp_enqueue_style('jlt-admin-bar-simple-line-icons');
        wp_enqueue_style('jlt-admin-bar-icomoon');
        wp_enqueue_style('jlt-admin-bar-themify-icons');
        ?>
            <style>
            .jlt-admin-bar-menu .ab-icon{
                top: 2px !important;
            }
            .jlt-admin-bar-menu .ab-icon[aria-hidden="true"] {
                display: none !important;
            }
            #wp-admin-bar-wp-logo-custom .jlt-admin-bar-menu .ab-icon{
                margin-right: 0 !important;
            }
            </style>
        <?php
    }

    public function adminbar_editor_enqueue_scripts_backend()
    {
        ?>
            <script>
                if( null != document.querySelector("#wp-admin-bar-my-sites > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-my-sites").id ="wp-admin-bar-my-sites-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-wp-logo > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-wp-logo").id ="wp-admin-bar-wp-logo-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-site-name > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-site-name").id ="wp-admin-bar-site-name-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-updates > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-updates").id ="wp-admin-bar-updates-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-comments > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-comments").id ="wp-admin-bar-comments-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-new-content > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-new-content").id ="wp-admin-bar-new-content-custom";
                }
            </script>
        <?php
    }

    public function adminbar_editor_enqueue_scripts_frontend()
    {
        ?>
            <script>
                if( null != document.querySelector("#wp-admin-bar-my-sites > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-my-sites").id ="wp-admin-bar-my-sites-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-new-content > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-new-content").id ="wp-admin-bar-new-content-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-comments > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-comments").id ="wp-admin-bar-comments-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-updates > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-updates").id ="wp-admin-bar-updates-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-customize > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-customize").id ="wp-admin-bar-customize-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-site-editor > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-site-editor").id ="wp-admin-bar-site-editor-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-site-name > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-site-name").id ="wp-admin-bar-site-name-custom";
                }
                if( null != document.querySelector("#wp-admin-bar-wp-logo > .ab-item > .jlt-admin-bar-menu")){
                    document.querySelector("#wp-admin-bar-wp-logo").id ="wp-admin-bar-wp-logo-custom";
                }
            </script>
        <?php
    }

    public function adminbar_editor_enqueue_scripts()
    {
        global $pagenow, $wp_roles;

        if (('admin.php' === $pagenow) && ('jlt_admin_bar_editor-settings' === $_GET['page'])) {
            // Enqueue Styles
            wp_enqueue_style('jlt-admin-bar-sdk');
            wp_enqueue_style('jlt-admin-bar-admin');

            // icons
            wp_enqueue_style('jlt-admin-bar-simple-line-icons');
            wp_enqueue_style('jlt-admin-bar-icomoon');
            wp_enqueue_style('jlt-admin-bar-themify-icons');

            // Enqueue Scripts
            wp_enqueue_script('jlt-admin-bar-admin');
        }

        // De-register and Dequeue Scripts/Styles
        if (!empty($this->options['adminify_assets'])) {
            foreach ($this->options['adminify_assets'] as $value) {
                wp_dequeue_style($value);
                wp_deregister_style($value);
            }
        }

        // Localize Scripts
        $localize_adminbar_data = array(
            'rest_urls'                => [
                'baseUrl'                   => AdminBarEditorApiEndPoints::get_rest_url(''),
                'getAdminBarItems'          => AdminBarEditorApiEndPoints::get_rest_url('/get-adminbar-menu-items/')
            ],
            'ajax_url'                 => admin_url('admin-ajax.php'),
            'is_elementor_active'      => Utils::is_plugin_active('elementor/elementor.php'),
            'uploaded_url'             => wp_upload_dir()['baseurl'],
            'image_path'               => JLT_ADMIN_BAR_EDITOR_IMAGES,
            'nonce'                    => wp_create_nonce( 'wp_rest' ),
            'wp_roles' => $wp_roles->roles,
            'wp_users' => get_users(),
            'adminify_ui' => class_exists('\WPAdminify\Inc\Admin\AdminSettings') ?  \WPAdminify\Inc\Admin\AdminSettings::get_instance()->get()['admin_ui'] : false,
        );
        wp_localize_script('jlt-admin-bar-admin', 'JltAdminBarEditor', $localize_adminbar_data);
    }

}