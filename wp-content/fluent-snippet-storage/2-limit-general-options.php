<?php
// <Internal Doc Start>
/*
*
* @description: It is not working at present. To be seen. 
* @tags: 
* @group: 
* @name: Limit General Options
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-12-01 09:32:40
* @is_valid: 
* @updated_by: 
* @priority: 10
* @run_at: all
* @load_as_file: 
* @condition: {"status":"no","run_if":"assertive","items":[[]]}
*/
?>
<?php if (!defined("ABSPATH")) { return;} // <Internal Doc End> ?>
<?php
add_filter( 'whitelist_options', 'wpse_maybe_remove_settings_from_whitelist' );
function wpse_maybe_remove_settings_from_whitelist( array $whitelist_options ) {
    if ( ! current_user_can( 'install_plugins' ) ) {
        $whitelist_options['general'] = array( 'blogname', 'new_admin_email' );
    }
    return $whitelist_options;
}