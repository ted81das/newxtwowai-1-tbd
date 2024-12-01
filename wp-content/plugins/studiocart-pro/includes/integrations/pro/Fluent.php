<?php

namespace Studiocart;
use FluentCrm\App\Models\SubscriberNote;
use FluentCrm\App\Services\AutoSubscribe;
use FluentCrm\Includes\Helpers\Arr;

if (!defined('ABSPATH'))
	exit;

class Fluent {

	public function __construct() {
        add_action('plugins_loaded', array($this, 'init'), 99);
    }
    
    public function init() {  
        if (! function_exists('FluentCrmApi')) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_fluent_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_filter('sc_integration_service_action_field_logic_options', array($this, 'add_fluent_to_services_list'), 10, 2);
        add_action('studiocart_fluentcrm_integrations', array($this, 'add_remove_to_fluent'), 10, 3);
    }
    
    public function add_fluent_service($options) {
        $options['fluentcrm'] = "FluentCRM";
        return $options;
    }
    
    public function add_fluent_to_services_list($services) {
        $services[] = 'fluentcrm';
        return $services;
    }
    
    public function add_integration_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'fluentcrm_list sc-selectize multiple',
                'id'			=> 'fluentcrm_list',
                'label'		    => __('List','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_lists(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'fluentcrm', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'fluentcrm_tag sc-selectize multiple',
                'id'			=> 'fluentcrm_tag',
                'label'		    => __('Tag','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_tags(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'fluentcrm', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        $fields[1]['fields'][] = array(
            'textarea' =>array(
                'class'		    => 'fluentcrm_fields',
                'id'			=> 'fluentcrm_fields',
                'label'		    => __('Contact Field Map','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'textarea',
                'note'   => __('Put each field pair on a separate line and use a colon (":") to separate the field slug from the field value. For example: fluent_field_slug:studiocart_field_id','ncs-cart'),
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'fluentcrm', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        return $fields;
    }
        
    public function add_remove_to_fluent($int, $sc_product_id, $order) {        
        if( empty($int['fluentcrm_list']) && empty($int['fluentcrm_tag']) ){
			return;
	    }
        
        $contactApi = FluentCrmApi('contacts');
        $list_id = isset($int['fluentcrm_list']) ? (array) $int['fluentcrm_list'] : array();
        $tag_id = isset($int['fluentcrm_tag']) ? (array) $int['fluentcrm_tag'] : array(); 
            
        if($int['service_action'] == 'subscribed') {
            
            /*
            * Update/Insert a contact
            * You can create or update a contact in a single call
            */
            
            $settings = (new AutoSubscribe())->getRegistrationSettings();
            $isDoubleOptin = Arr::get($settings, 'double_optin') == 'yes';

            if ($isDoubleOptin) {
                $status = 'pending';
            } else {
                $status = 'subscribed';
            }

            $ip = get_post_meta($order['id'], '_sc_ip_address', true);
            $data = [
                'first_name' => $order['first_name'],
                'last_name' => $order['last_name'],
                'email' => $order['email'], // requied
                'status' => $status,
                'tags' => $tag_id, // tag ids as an array
                'lists' => $list_id, // list ids as an array
                'ip' => $ip
            ];
            
            $keys = array('address_line_1','address_line_2','city','state','postal_code','country','phone');
            $fields = array('address1','address2','city','state','zip','country','phone');
            for($i=0;$i<count($keys);$i++) {
                if ( $field = get_post_meta( $order['id'], '_sc_'.$fields[$i], true ) ){
                    $data[$keys[$i]] = $field;
                }
            }
            
            if(isset($int['fluentcrm_fields']) && $int['fluentcrm_fields']){
                $custom = [];
                $map = explode("\n", str_replace("\r", "", esc_attr($int['fluentcrm_fields'])));
                for ($i=0;$i<count($map);$i++) {
                    $option = explode(':', $map[$i]);
                    if (count($option) == 1) {
                        $custom[trim($option[0])] = trim($option[0]);
                    } else {
                        $custom[trim($option[0])] = trim($option[1]);
                    }
                }
                
                $map = array();
                $values = get_post_meta( $order['id'], '_sc_custom_fields', true );
                $info = sc_webhook_order_body($order['id']);
                if($values){
                    foreach($custom as $k=>$v) {
                        if($v && is_array($values) && isset($values[$v])){
                            $map[$k] = $values[$v]['value'];
                        } else if ($v && isset($info[$v])) {
                            $map[$k] = $info[$v]; // order data
                        } else if ($v && preg_match('/"([^"]+)"/', html_entity_decode($v), $val)) {
                            $map[$k] = trim($val[1]); // static value
                        } else if ($v && $val = get_post_meta($order['id'], '_sc_'.$v, true)) {
                            $map[$k] = $val; // meta key
                        }
                    }
                }
                if(!empty($map)) {
                    $data = array_merge($data, $map);
                }
            }
            
            $contact = $contactApi->createOrUpdate($data);
            
            if(strtolower(get_post_meta($order['id'], '_sc_consent', true)) == 'yes'){
                SubscriberNote::create([
                    'subscriber_id' => $contact->id,
                    'type' => 'system_log',
                    'title' => __('Subscriber opt-in confirmed', 'ncs-cart'),
                    'description' => sprintf(__('Subscriber confirmed opt-in via %s from IP Address:', 'ncs-cart'), apply_filters('studiocart_plugin_title', 'Studiocart')) .' '. $ip,
                ]);
            }

            if ($list_id) {
                sc_log_entry($order['id'], "User added to FluentCRM List ID: " .$list_id);
            }
            if ($tag_id) {
                sc_log_entry($order['id'], "User added to FluentCRM Tag ID: " .$tag_id);
            }
        } else {
            $contact = $contactApi->getContact($order['email']);
            $contact->detachTags($tag_id);
            $contact->detachLists($list_id);
            if ($list_id) {
                sc_log_entry($order['id'], "User removed from FluentCRM List ID: " .$list_id);
            }
            if ($tag_id) {
                sc_log_entry($order['id'], "User removed from FluentCRM Tag ID: " .$tag_id);
            }
        }
        
    }
    
    public function get_lists() {
        $listApi = FluentCrmApi('lists');
        $options = array();
        
        // Get all the lists
        $allLists = $listApi->all(); // array of all the lists and each list is an object
        
        if(!empty($allLists)) {
            $options[] = '- Select -';
            foreach($allLists as $list) {
                // accessing a list
                $options[$list->id] = $list->title;
            }
        } else {
            $options[] = 'No lists found';
        }        
		return $options;      
    }
    
    public function get_tags() {
        $tagApi = FluentCrmApi('tags');
        $options = array();
        
        // Get all the tags
        $allTags = $tagApi->all();
        
        if(!empty($allTags)) {
            $options[] = '- Select -';
            foreach($allTags as $tag) {
                // accessing a list
                $options[$tag->id] = $tag->title;
            }
        } else {
            $options[] = 'No tags found';
        }        
		return $options;      
    }
}