<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Groups {

	public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {  
        if ( !class_exists('Groups_Group') ){
            return;
        }
        add_filter('sc_integrations', array($this, 'add_groups_service'));
        add_filter('sc_integration_fields', array($this, 'add_groups_int_fields'), 10, 2);
        add_action('studiocart_groups_integrations', array($this, 'add_remove_to_group'), 10, 3);
    }
    
    public function add_remove_to_group($int, $sc_product_id, $order) {        
        if( empty($int['groups_group'])){
			return;
	    }
        
        $group_id = $int['groups_group'];
            
        if($int['groups_action'] == 'add') {
            $user_id = sc_get_order_user_id($order, $create=true );
            \Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $group_id ) );
            sc_log_entry($order['id'], "User added to group ID: " .$group_id);
        } else {
            $user_id = sc_get_order_user_id($order, $create=false );
            if($user_id) {
                \Groups_User_Group::delete( $user_id, $group_id );
                sc_log_entry($order['id'], sprintf(__("User removed from group ID: %s", 'ncs-cart'), $group_id));
            } else {
                sc_log_entry($order['id'], sprintf(__("Can't remove from group ID: %s, no user found.", 'ncs-cart'), $group_id));
            }
        }
        
    }
    
    public function add_groups_int_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'groups_action',
                'id'			=> 'groups_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ['add'=>'Add user to group', 'remove'=>'Remove user from group'],
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'groups', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'groups_group',
                'id'			=> 'groups_group',
                'label'		    => __('Group','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_groups(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'groups', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        return $fields;
    }
    
    public function add_groups_service($options) {  
        $options['groups'] = "Groups";
        return $options;
    }
    
    public function get_groups() {
        global $wpdb;
            
        $order_by = 'name';
        $order = 'ASC';
        $group_table = _groups_get_tablename( 'group' );
        
        $options = array(''=>'No groups found');
            
		if ( $groups = $wpdb->get_results(
			"SELECT group_id FROM $group_table ORDER BY $order_by $order"
		) ) {
            
            $options = array();
			
            foreach( $groups as $group ) {
				$group = new \Groups_Group( $group->group_id );
                $options[$group->group_id] = $group->name;
			}
		}
		return $options;      
    }
}