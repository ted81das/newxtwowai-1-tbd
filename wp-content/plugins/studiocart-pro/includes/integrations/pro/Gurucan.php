<?php
    
namespace Studiocart;

use Exception;

if (!defined('ABSPATH'))
	exit;

class Gurucan {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;
	private $service_label;
	private $api_key;
    
	
	public function __construct() { 
        $this->service_name = 'gurucan';
        $this->service_label = 'Gurucan';
        $this->api_key = get_option('_sc_gurucan_api_key');
        add_action('plugins_loaded', array($this, 'init'));
    }
	
    public function init() {
        //add_action('_sc_register_sections', array($this, 'settings_section'), 10, 2);
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        if ( $this->api_key ) {
            add_filter('sc_integrations', array($this, 'add_gurucan_service'));
            add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
            add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_service'), 10, 3);
        } 
    }
    
    public function settings_section($intigrations) {     
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;
    }
    
    public function service_settings($options){
        $options[$this->service_name] = array(	
            'gurucan-app-domain' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'School URL', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_gurucan_app_domain',
                    'value'         => '',
                    'description'   => 'e.g. https://{your-domain}.gurucan.com',
                ),
                'tab'=>'integrations'
            ),
            'gurucan-api-key' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'API Key', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_gurucan_api_key',
                    'value'         => '',
                    'description'   => '',
                ),
                'tab'=>'integrations'
            ) 
        );
        return $options;
    }
    
    public function add_gurucan_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;  
    }    
    
    public function add_integration_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'sc-selectize',
                'id'			=> 'ncs_gurucan_action',
                'label'		    => esc_html__('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => array('grant' => esc_html__('Grant Access','ncs-cart'), 'revoke' => esc_html__('Remove Access','ncs-cart')),
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'sc-selectize multiple',
                'id'			=> 'ncs_gurucan_courses',
                'label'		    => esc_html__('Course(s)','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->get_courses(),
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'sc-selectize multiple',
                'id'			=> 'ncs_gurucan_offers',
                'label'		    => esc_html__('Offer(s)', 'ncs-cart'),
                'placeholder'	=> '',
                'description'   => '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->get_offers(),
                'conditional_logic' => array(
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                )
            ),
        );        
        return $fields;
    }
    
    public function add_remove_to_service($int, $sc_product_id, $order) {
        $this->error = array();
        $courses = $int['ncs_gurucan_courses'];
        $offers = $int['ncs_gurucan_offers'];
        $username = $order['first_name']." ".$order['last_name'];
        $useremail = $order['email'];
        $user_exsist = $this->gc_search_email($useremail);
        if($user_exsist){
            $purchasedItems = [];
            $gcan_user_id = $user_exsist->_id;
            sc_log_entry($order['id'], esc_html__( "Existing Gurucan user found", "ncs-cart"));
        } else {
            $gcan_user_id = $this->gc_create_user_by_email($useremail,$username);
            if(!$gcan_user_id){
                sc_log_entry($order['id'], esc_html__( "Fail to create Gurucan user", "ncs-cart"));
            }
        }
        
        if(isset($int['ncs_gurucan_action']) && $int['ncs_gurucan_action'] == 'revoke'){
            if($user_exsist && count($user_exsist->purchasedItems)>0){
                foreach($user_exsist->purchasedItems as $purchasedItem):
                    $add_item = true;
                    if($purchasedItem->refPath == 'Course'):
                        foreach($courses as $course){
                            if($purchasedItem->_id->_id == $course):
                                $add_item = false;
                            endif;
                        }
                    endif;
                    if($purchasedItem->refPath == 'Plan'):
                        foreach($offers as $offer){
                            if($purchasedItem->_id->_id != $offer):
                                $add_item = false;
                            endif;
                        }
                    endif;
                    if($add_item){
                        $purchasedItems[] = $purchasedItem;
                    }
                endforeach;
            }
            $this->update_user($gcan_user_id,$purchasedItems);
            $success_message = "Access is revoked to gurucan user.";
        } else {
            foreach($courses as $course){
                $purchasedItems = array(
                    "module"=>"course",
                    "refPath"=>"Course",
                    "_id"=>$course
                );
                $this->grant_access($gcan_user_id, $purchasedItems);
            }
            foreach($offers as $offer){
                $purchasedItems = array(
                    "module"=>"plan",
                    "refPath"=>"Plan",
                    "_id"=>$offer
                );
                $this->grant_access($gcan_user_id, $purchasedItems);
            }
            $success_message = "Access is granted to gurucan user.";
        }
        if(empty($this->error)){
            sc_log_entry($order['id'], esc_html__($success_message , "ncs-cart"));  
        } else {
            sc_log_entry($order['id'], esc_html__( implode(',',$this->error), "ncs-cart"));  
            
        }               
    }

    private function gc_search_email($useremail){
        $appdomain = get_option('_sc_gurucan_app_domain');
        $args = array("search"=>urlencode($useremail));
        $search_url = add_query_arg($args,$appdomain.'/api/admin/users');
        $args = array(
            'headers' => array(
                'gc-api-key' => $this->api_key
            )
        );
        $gc_search_email_request = wp_remote_get( $search_url, $args );
        $json_data = wp_remote_retrieve_body($gc_search_email_request);
        $response = json_decode($json_data);
        if(!empty($response->data)){
            return $response->data[0]??false;
        }
        return false;
    }

    private function update_user($gcan_user_id,$purchasedItems){
        $appdomain = get_option('_sc_gurucan_app_domain');
        $update_user_endpoint = $appdomain.'/api/admin/users/'.$gcan_user_id;

        $user_agrs = array("purchasedItems"=>$purchasedItems);
        $header = array(
            'gc-api-key' => $this->api_key,
            'Content-Type' => 'application/json',
        );
        $args = array(
            'body'        => wp_json_encode($user_agrs),
            'headers'     => $header,
        );
        
        $json_response = wp_remote_post( $update_user_endpoint, $args );
        $json_data = wp_remote_retrieve_body($json_response);
        $response = json_decode($json_data);
        if($response->status=='ok'){
            return true;
        } else {
            $this->error[] = 'Revoke Access failed';
        }
        
        return false;
    }

    private function gc_create_user_by_email($useremail,$username){
        $appdomain = get_option('_sc_gurucan_app_domain');
        $add_user_endpoint = $appdomain.'/api/admin/users';

        $user_agrs = array("email"=>$useremail,"name"=>$username);
        $header = array(
            'gc-api-key' => $this->api_key
        );
        $args = array(
            'body'        => $user_agrs,
            'headers'     => $header,
        );
        
        $json_response = wp_remote_post( $add_user_endpoint, $args );
        $json_data = wp_remote_retrieve_body($json_response);
        $response = json_decode($json_data);
        if(!empty($response->user)){
            return $response->user->_id??false;
        }
        
        return false;
    }

    public function grant_access($gcan_user_id,$purchasedItems){
        $appdomain = get_option('_sc_gurucan_app_domain');
        $grant_access_endpoint = $appdomain.'/api/admin/users/grant-access';
        $grant_args = array('_id'  =>$gcan_user_id,
                            'item' =>$purchasedItems
                        );

        $header = array(
            'gc-api-key' => $this->api_key,
            'Content-Type' => 'application/json',
        );
        $args = array(
            'body'        => wp_json_encode($grant_args),
            'headers'     => $header,
        );
        
        $json_response = wp_remote_post( $grant_access_endpoint, $args );
        $json_data = wp_remote_retrieve_body($json_response);
        $response = json_decode($json_data);
        if($response->status=="ok"){
            return true;
        } else {
            $this->error[] = 'Grant Access failed';
        }
        return;
    }

    public function get_courses() { 
		$selected_courses = array();
        $appdomain = get_option('_sc_gurucan_app_domain');
        $course_endpoint = $appdomain.'/api/admin/courses';
        $args = array(
            'headers' => array(
                'gc-api-key' => $this->api_key
            )
        );
        $course_request = wp_remote_get( $course_endpoint, $args );
        $json_data = wp_remote_retrieve_body($course_request);
        $response = json_decode($json_data);
        if($response->status == "ok"){
            foreach($response->courses as $course){
                $course_id = $course->_id;
                $course_title = $course->title;
                $selected_courses[$course_id] = $course_title;
            }
        }
        return $selected_courses;
	}

    public function get_offers(){
        $selected_offers = array();
        $appdomain = get_option('_sc_gurucan_app_domain');
        $plans_endpoint = $appdomain.'/api/admin/plans';
        $args = array(
            'headers' => array(
                'gc-api-key' => $this->api_key
            )
        );
        $plans_request = wp_remote_get( $plans_endpoint, $args );
        $json_data = wp_remote_retrieve_body($plans_request);
        $response = json_decode($json_data);
        if($response->status == "ok"){
            foreach($response->plans as $offer){
                $offer_id = $offer->_id;
                $offer_name = $offer->name;
                $selected_offers[$offer_id] = $offer_name;
            }
        }
        return $selected_offers;      
    }    

}