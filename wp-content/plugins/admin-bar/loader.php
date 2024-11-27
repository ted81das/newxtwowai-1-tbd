<?php
namespace JewelTheme\AdminBarEditor;

// use WPAdminify\Modules\QuickCircleMenu\Libs\Update_Checker;
// use WPAdminify\Modules\QuickCircleMenu\Libs\Assets;

class Loader {

    public function __construct() {

        /**
         * Autoload Necessary Files
         */
        include_once JLT_ADMIN_BAR_EDITOR_DIR . '/vendor/autoload.php';

        /**
         * Check for Plugin Update
         */
        // $updater = new Update_Checker( QUICKCIRCLEMENU_FILE );
        // $updater->initialize();

        // new Assets();

        /**
         * Hook: Check WP Adminify Loaded
         */
        add_action( 'jlt_admin_bar_editor_fs_not_loaded', 'jlt_admin_bar_editor_fs_not_loaded_notice' );

        /**
         * Hook: Load This Plugin
         */
        add_action('jlt_admin_bar_editor_plugin_loaded', [ $this, 'jlt_admin_bar_editor_plugin_loaded' ] );



        /**
         * License Hooks
         */
        // if ( jlt_admin_bar_editor_fs_is_parent_active_and_loaded() ) {
        //     // If parent already included, init add-on.
        //     jlt_admin_bar_editor_fs_init();
        // } elseif ( jlt_admin_bar_editor_fs_is_parent_active() ) {
        //     // Init add-on only after the parent is loaded.
        //     add_action( 'jltwp_adminify_loaded', 'jlt_admin_bar_editor_fs_init' );
        // } else {
        //     // Even though the parent is not activated, execute add-on for activation / uninstall hooks.
        //     jlt_admin_bar_editor_fs_init();
        // }


    }

    public function jlt_admin_bar_editor_plugin_loaded() {

        if ( ! class_exists( '\\JewelTheme\\AdminBarEditor\\AdminBarEditor' ) ) {
            include_once JLT_ADMIN_BAR_EDITOR_DIR . '/class-admin-bar.php';
        }

    }

}

new Loader();