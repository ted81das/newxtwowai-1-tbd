<?php
/*
Plugin Name: Superadmin Admin Access
Plugin URI: https://www.edgehost.ing
Description: Control Admin Creation Access
Version: 1.0
Author: Edgehost
Author URI: https://www.edgehost.ing
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: edgehost-admin-access
Domain Path: /languages
*/

function hide_admin_from_user_list($user_query) {
    // Check if the current user is not an admin
    if (!current_user_can('administrator')) {
        // Get the current user ID and exclude it from the user list
        $user_query->set('exclude', array(get_current_user_id()));
    }
    
    return $user_query;
}
add_filter('pre_user_query', 'hide_admin_from_user_list');

add_action('rcp_membership_level_save', 'prevent_admin_membership_role');

function prevent_admin_membership_role($membership_level_id) {
    // Check if Restrict Content Pro is installed
    if (!class_exists('RCP_Membership')) {
        return; // Exit if RCP is not installed
    }

    $membership_data = array(
        'id' => $membership_level_id,
        'role' => get_post_meta($membership_level_id, '_rcp_membership_level_role', true) // Get the selected role for the membership
    );

    // Prevent saving the membership level if the role is set to 'administrator'
    if ($membership_data['role'] === 'administrator') {
        wp_die('Membership level cannot be created with an administrator role.', 'Membership Creation Error');
    }

    return $membership_level_id; // Allow saving if the role is valid
}

function wpse_edgehost_filter_editable_roles($all_roles) {
    // Check if the current user is not a super admin
    if (!is_super_admin(get_current_user_id())) {
        // Remove the 'administrator' role from the list
        unset($all_roles['administrator']);
    }
    
    // Return the modified list of roles
    return $all_roles;
}
add_filter('editable_roles', 'wpse_edgehost_filter_editable_roles');
