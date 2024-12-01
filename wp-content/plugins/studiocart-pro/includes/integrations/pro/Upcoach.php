<?php
    
namespace Studiocart;

use Exception;

if (!defined('ABSPATH'))
	exit;

class Upcoach{

	private $service_label;
	private $service_name;
	private $api_key;
	private $programs;
    
    const ADD = 'add_user';
    const API_URL = 'https://api.upcoach.com/';
    
	
	public function __construct() { 
        $this->service_name = 'upcoach';
        $this->service_label = 'Upcoach';
        $this->api_key = get_option('_sc_upcoach_api_token');
        add_action('plugins_loaded', array($this, 'init'));
    }
	
    public function init() {
       
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        if ( $this->api_key ) {
            if(is_admin() && $_GET['action']=='edit' && isset($_GET['post']) && get_post_type($_GET['post'])=='sc_product'){
                add_filter('sc_integrations', array($this, 'add_service'));
                add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
            }
            add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_service'), 10, 3);
        } 
    }
    
    public function settings_section($intigrations) {     
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;
    }
    
    public function service_settings($options){
        $options[$this->service_name] = array(	
            'heartbeat-api-key' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'API Token', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_upcoach_api_token',
                    'value'         => '',
                    'description'   => '',
                ),
                'tab'=>'integrations'
            ) 
        );
        return $options;
    }
    
    public function add_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;  
    }    
    
    public function add_integration_fields($fields, $save) {
        // $fields[1]['fields'][] = array(
        //     'select' =>array(
        //         'class'		    => '',
        //         'id'			=> 'upcoach_action',
        //         'label'		    => __('Action','ncs-cart'),
        //         'placeholder'	=> '',
        //         'type'		    => 'select',
        //         'value'		    => '',
        //         'class_size'    => '',
        //         'selections'    => array(
        //             self::ADD => __('Add User To Program','ncs-cart'), 
        //         ),
        //         'conditional_logic' => array( 
        //                 array(
        //                     'field' => 'services',
        //                     'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
        //                     'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
        //                 )
        //         )
        //     )
        // );
        $this->get_upcoach_program();
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'sc-selectize multiple',
                'id'			=> 'upcoach_program',
                'label'		    => esc_html__('Select Program','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->programs,
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
      
        return $fields;
    }
    
    public function add_remove_to_service($int, $sc_product_id, $order) {
        $upcoach_program_ids = $int['upcoach_program'];
        $username = $order['first_name']." ".$order['last_name'];
        $useremail = $order['email'];
        //sc_log_entry($order['id'], print_r( $upcoach_program_ids, true));
        $password = "";
        if(isset($order['custom_fields_post_data']['sc_custom_fields']) && isset($order['custom_fields_post_data']['sc_custom_fields']['passowrd'])){
            
            $password = $order['custom_fields_post_data']['sc_custom_fields']['passowrd'];
        }
        $client_id = $this->upcoach_find_client($useremail);
        if(!$client_id){
            // User already exist
            
            $client_id = $this->upcoach_add_client($useremail,$username,$password,$order['id']);

            if($client_id){
                // Client added in Upcoach Save response if needed
                sc_log_entry($order['id'], esc_html__( "Client added to Upcoach", "ncs-cart"));
            }else{
                sc_log_entry($order['id'], esc_html__( "Unable to add client to Upcoach", "ncs-cart"));
                return false;
            }
        }else{
            sc_log_entry($order['id'], esc_html__( "Client already exsits to Upcoach", "ncs-cart"));
        }
        foreach( $upcoach_program_ids as  $upcoach_program_id){
            $response = $this->upcoach_add_client_program($client_id,$upcoach_program_id);
            if($response->program_member){
                // User added in Heartbeat group Save response if needed
                sc_log_entry($order['id'], esc_html__( "Client added to Upcoach program", "ncs-cart"));
            }else{
                sc_log_entry($order['id'], esc_html__( "Unable to add Client to Upcoach program", "ncs-cart"));
            }
        }
    }



    /**
     * Get upcoach program array
     * @return Array $program
     */
    public function get_upcoach_program(){

        $this->set_upcoach_program(self::API_URL.'/programs');
        //die;
    }

    /**
     * Get upcoach program array
     * @return Array $program
     */
    public function set_upcoach_program($link){
        $args = array(
            'headers' => $this->upcoach_headers(),
        );

        $result = wp_remote_get($link, $args );
        $json_data = wp_remote_retrieve_body($result);
        $programsObj = json_decode($json_data);
        
        if(is_countable($programsObj->data )){
            foreach($programsObj->data as $key => $value){
                $this->programs[$value->id] = $value->name;
            }
        }
        
        if($programsObj->links->next){
            
            $this->set_upcoach_program($programsObj->links->next);
        }
    }
    
    /**
     * Find Existing user
     * @param String upcoach user email $useremail
     */
    private function upcoach_find_client($useremail){

        $args = array(
            'headers' => $this->upcoach_headers(),
        );

        $roles = wp_remote_get(self::API_URL.'/clients?filter[email]='.$useremail, $args );
        $json_data = wp_remote_retrieve_body($roles);
        $response = json_decode($json_data);

        if($response->meta->total ==0 ){
           return false;
        }
        return $response->data[0]->id;
    }

     /**
     * Add user to Heartbeat community
     * @param String $useremail Heartbeat community user email 
     * @param String $username Heartbeat community user name 
     * @param String $role Heartbeat community user role 
     * @param String $group_id Heartbeat community user group_id 
     */
    private function upcoach_add_client($useremail,$username,$password,$order_id){

        $body = array(
            "email"=>$useremail,
            "name"=>$username,
        );
        if(!empty($password)){
            $body['password'] = $password;
        }
        $args = array(
            'method' => 'POST',
            'body' => json_encode($body),
            'headers' => $this->upcoach_headers(),
        );
        sc_log_entry($order_id, print_r( $args, true));
        $result = wp_remote_request(self::API_URL.'/clients', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);
        sc_log_entry($order_id, print_r( $response, true));
        sc_log_entry($order_id, $json_data);
        return $response->client?$response->client->id:false;
    }

    /**
     * Add user to group 
     * @param String $useremail Heartbeat community user email 
     * @param String $group_id Heartbeat community user group_id 
     */
    private function upcoach_add_client_program($client_id,$upcoach_program_id){
        
        $body = array(
            "user"=>$client_id,
        );
        $args = array(
            'method' => 'POST',
            'body' => json_encode($body),
            'headers' => $this->upcoach_headers(),
        );
        sc_log_entry($order['id'], print_r( $args, true));
        $result = wp_remote_request(self::API_URL.'/programs/'.$upcoach_program_id.'/members', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);
        return $response;
    }


    /**
     * Prepare API request headers
     * @return Array
     */
    private function upcoach_headers(){

        return array(
            'Authorization' => 'Bearer '.$this->api_key,
            'Content-Type' => 'application/json',
        );
    }

}