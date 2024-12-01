<?php
// <Internal Doc Start>
/*
*
* @description: 
* @tags: 
* @group: UserRelated
* @name: AddTemplateKitforOtherRoles
* @type: PHP
* @status: published
* @created_by: 
* @created_at: 
* @updated_at: 2024-12-01 08:08:47
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
add_filter( 'ast_block_template_capability_additional_roles', function( $roles ) { 
 $roles[] = 'director'; // Add the 'miniadmin' role 
 return $roles; });