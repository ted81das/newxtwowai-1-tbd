<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;
	
class Oxygen {
    
    public function __construct() {
		//add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        
        if( !function_exists('oxygen_vsb_register_condition') ) { return; }
        
        global $oxy_condition_operators;

        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'sc_product'
        );

        $products = array();
        $posts = get_posts($args);
        foreach($posts as $p) {
            $products[] = $p->post_title .' ('.$p->ID.')';
        }

        oxygen_vsb_register_condition(
            'Order',
            array(
                'options' => $products,
                'custom'  => false
            ),
            array(
                'Exists',
                'Does Not Exist',
                'Paid/Completed', 
                'Pending (COD)', 
                'Refunded'
            ),
            'sc_oxy_check_orders',
            'Studiocart'
        );

        oxygen_vsb_register_condition(
            'Subscription Status',
            array(
                'options' => [
                    'Exists',
                    'Does not Exist',
                    'Active', 
                    'Completed', 
                    'Canceled'],
                'custom'  => false
            ),
            $oxy_condition_operators['simple'],
            'sc_oxy_check_subscriptions',
            'Studiocart'
        );

    }
}

function sc_oxy_get_user_orders($user_id, $status='any', $product_id='', $type='sc_order') {

    $args = array(
        'posts_per_page'      => 1,
        'post_type'        => $type,
        'post_status'      => $status,
    );

    if($product_id){
        $args['meta_query'] = array(
            'relation' => 'OR',
            array(
                'key' => '_sc_product_id',
                'value' => $product_id
            ),
            array(
                'key' => '_sc_bump_id',
                'value' => $product_id
            )
        );
    }

    $posts = get_posts($args);
    if (!empty($posts)) {
        return true;
    }

    return false;
}

function sc_oxy_check_orders($value, $operator) {
    
    return true;
    
    if ( !is_user_logged_in() ) {
        if(strtolower($operator) == 'does not exist') {
            return true;
        }
        return false;
    }
    
    $user_id = get_current_user_id();
    
    if ($value) {
        $value = preg_replace('/[^0-9]+/', '', $value);
    }
    
    switch($operator){
        case($operator == 'Paid/Completed'):
            $status = array('paid','completed');
            break;
        case($operator == 'Refunded'):
            $status = array('refunded');
            break;
        default:
            $status = 'any';
            break;
    }
    
    $orders = sc_oxy_get_user_orders($user_id, $status, $value);
    if ($orders) {
        if (strtolower($operator) == 'does not exist') {
            return false;
        } else {
            return true;
        }
    } else {
        if (strtolower($operator) == 'does not exist') {
            return true;
        } else {
            return false;
        }
    }
}

function sc_oxy_check_subscriptions($value, $operator) {
    
    $operator = strtolower($operator);
    
    if ( !is_user_logged_in() ) {
        if(strtolower($operator) == 'does not exist') {
            return true;
        }
        return false;
    }
    
    $user_id = get_current_user_id();
    
    if ($value) {
        $value = preg_replace('/[^0-9]+/', '', $value);
    }
    
    if(strpos($operator,'exist') !== false) {
        $status = 'any';
    } else {
        $status = $operator;
    }
    
    $orders = sc_oxy_get_user_subscriptions($user_id, $status, $value, $type='sc_subscriptions');
    if ($orders) {
        if ($operator == 'does not exist') {
            return false;
        } else {
            return true;
        }
    } else {
        if ($operator == 'does not exist') {
            return true;
        } else {
            return false;
        }
    }
}
