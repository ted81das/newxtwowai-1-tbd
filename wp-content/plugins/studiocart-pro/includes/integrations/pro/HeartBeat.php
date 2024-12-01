<?php
    
namespace Studiocart;

use Exception;

if (!defined('ABSPATH'))
	exit;

class HeatBeat{

	private $service_label;
	private $api_key;
    const ADD = 'add_user';
    const REMOVE = 'remove_user';
    const INVITE = 'invite_user';
    const REMOVE_GROUP ='remove_group';
    const API_URL = 'https://api.heartbeat.chat/v0';
    
	
	public function __construct() { 
        $this->service_name = 'heartbeat';
        $this->service_label = 'Heartbeat';
        $this->api_key = get_option('_sc_heartbeat_api_key');
        add_action('plugins_loaded', array($this, 'init'));
    }
	
    public function init() {
       
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        if ( $this->api_key ) {
            add_filter('sc_integrations', array($this, 'add_heartbeat_service'));
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
            'heartbeat-api-key' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'API Key', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_heartbeat_api_key',
                    'value'         => '',
                    'description'   => '',
                ),
                'tab'=>'integrations'
            ) 
        );
        return $options;
    }
    
    public function add_heartbeat_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;  
    }    
    
    public function add_integration_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => '',
                'id'			=> 'heartbeat_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => array(
                    self::ADD => __('Add User To Community','ncs-cart'), 
                    self::INVITE => __('Invite User To Community','ncs-cart'), 
                    self::REMOVE => __('Remove User From Community','ncs-cart'),
                    self::REMOVE_GROUP => __('Remove User From Group','ncs-cart'),
                ),
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
                'class'		    => '',
                'id'			=> 'heartbeat_user_role',
                'label'		    => esc_html__('Select User Role','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->heartbeat_roles(),
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        ),
                        array(
                            'field' => 'heartbeat_action',
                            'value' => self::ADD, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );

        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => '',
                'id'			=> 'heartbeat_invite_id',
                'label'		    => esc_html__('Select Invitation Code','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->heartbeat_invitation(),
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        ),
                        array(
                            'field' => 'heartbeat_action',
                            'value' => self::INVITE, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );

        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => '',
                'id'			=> 'heartbeat_user_group',
                'label'		    => esc_html__('Group','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => $this->heartbeat_groups(),
                'conditional_logic' => array( 
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        ),
                        array(
                            'field' => 'heartbeat_action',
                            'value' => [self::ADD,self::REMOVE_GROUP], // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => 'IN', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        ),
                )
            )
        );
      
        return $fields;
    }
    
    public function add_remove_to_service($int, $sc_product_id, $order) {

        $this->error = array();
        $action = $int['heartbeat_action'];
        $username = $order['first_name']." ".$order['last_name'];
        $useremail = $order['email'];
        //sc_log_entry($order['id'], print_r( $order, true));
        
        $user_exist = $this->heartbeat_find_user($useremail);
        if($action == self::INVITE){
            $invitaion_id = $int['heartbeat_invite_id']??"";
            if(empty($invitaion_id)){
                sc_log_entry($order['id'], esc_html__( "No invite code selected", "ncs-cart"));
            } else {
                $response = $this->hearbeat_invite_user($useremail,$invitaion_id);
                if($response->success){
                    // User invited in Heartbeat Save response if needed
                    sc_log_entry($order['id'], esc_html__( "User invited to Heartbeat", "ncs-cart"));
                }else{
                    sc_log_entry($order['id'], esc_html__( $response->errorType .' - '. $response->message, "ncs-cart"));
                }
            }
            
        } elseif($action == self::ADD){

            if($user_exist){
                // User already exist
                sc_log_entry($order['id'], esc_html__( "User already added to Heartbeat Community", "ncs-cart"));
                $group_id = $int['heartbeat_user_group']??"";
                $response = $this->hearbeat_add_user_group($useremail,$group_id);
                if($response->success){
                    // User added in Heartbeat group Save response if needed
                    sc_log_entry($order['id'], esc_html__( "User added to Heartbeat group", "ncs-cart"));
                }else{
                    sc_log_entry($order['id'], esc_html__( $response->errorType .' - '. $response->message, "ncs-cart"));
                }
                
            }else{
                // Create User
                if(isset($int['heartbeat_user_role']) && !empty($int['heartbeat_user_role'])){
                    $role = $int['heartbeat_user_role'];
                    $group_id = $int['heartbeat_user_group']??"";
                    $response = $this->hearbeat_add_user($useremail,$username,$role,$group_id);
                    

                    if(property_exists($response, 'userID') === true){
                        // User added in Heartbeat community Save response if needed
                        sc_log_entry($order['id'], esc_html__( "User added to Heartbeat Community", "ncs-cart"));
                    }else{
                        sc_log_entry($order['id'], esc_html__( $response->errorType .' - '. $response->message, "ncs-cart"));
                    }

                }else{
                    sc_log_entry($order['id'], esc_html__( "Unable to add user to Heartbeat Community, role not found", "ncs-cart"));
                }

            }

        }elseif($action == self::REMOVE){
            // Remove user from Heartbeat community
            if($user_exist){
                $response = $this->heartbeat_remove_user($useremail);
                if($response->success){
                    sc_log_entry($order['id'], esc_html__( "User deleted from Heartbeat Community", "ncs-cart"));
                }else{
                    sc_log_entry($order['id'], esc_html__( $response->errorType .' - '. $response->message , "ncs-cart"));
                }
            }
        }elseif($action == self::REMOVE_GROUP){
            if($user_exist && !empty($int['heartbeat_user_group'])){
                $group_id = $int['heartbeat_user_group'];
                $response = $this->heartbeat_remove_user_group($useremail,$group_id);
                if($response->success){
                    sc_log_entry($order['id'], esc_html__( "User has been removed from Heartbeat group", "ncs-cart"));
                }else{
                    sc_log_entry($order['id'], esc_html__( $response->errorType .' - '. $response->message , "ncs-cart"));
                }
            }
        }
    }


    /**
     * Get Heartbeat User Groups
     * @return Array $groups
     */
    public function heartbeat_groups(){
        $groups = array();
        $args = array(
            'headers' => $this->heartbeat_headers(),
        );
        $result = wp_remote_get(self::API_URL.'/groups', $args );
        $json_data = wp_remote_retrieve_body($result);
        $groupsObj = json_decode($json_data);

        if(is_countable($groupsObj )){
            foreach($groupsObj as $key => $value){
                $groups[$value->id] = $value->name;
            }
        }

        return $groups;
    }



    /**
     * Get Heartbeat user roles array
     * @return Array $roles
     */
    public function heartbeat_roles(){

        $roles = array();
        $args = array(
            'headers' => $this->heartbeat_headers(),
        );

        $result = wp_remote_get(self::API_URL.'/roles', $args );
        $json_data = wp_remote_retrieve_body($result);
        $rolesObj = json_decode($json_data);

        if(is_countable($rolesObj )){
            foreach($rolesObj as $key => $value){
                $roles[$value->id] = $value->name;
            }
        }

        return array_reverse($roles);
    }

    /**
     * Get Heartbeat user roles array
     * @return Array $invitation
     */
    function heartbeat_invitation(){
        $invitations = array();
        $args = array(
            'headers' => $this->heartbeat_headers(),
        );

        $result = wp_remote_get(self::API_URL.'/invitations', $args );
        $json_data = wp_remote_retrieve_body($result);
        $invitationsObj = json_decode($json_data);

        if(is_countable($invitations )){
            foreach($invitationsObj as $value){
                $invitations[$value->id] = $value->code;
            }
        }

        return array_reverse($invitations);
    }

    /**
     * Find Existing user
     * @param String Heartbeat community user email $useremail
     */
    private function heartbeat_find_user($useremail){

        $args = array(
            'headers' => $this->heartbeat_headers(),
        );

        $roles = wp_remote_get(self::API_URL.'/find/users?email='.$useremail, $args );
        $json_data = wp_remote_retrieve_body($roles);
        $response = json_decode($json_data);

        if(property_exists($response,'error') === true ){
           return false;
        }

      return true;

    }

    /**
     * Remove user from Heartbeat community
     * @param String Heartbeat community user email $useremail
     */
    private function heartbeat_remove_user($email){

        $body = array(
            "email"=>$email,
        );

        $args = array(
            'method' => 'DELETE',
            'body' => json_encode($body),
            'headers' => $this->heartbeat_headers(),
        );

       
        $result = wp_remote_request(self::API_URL.'/users', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);

        return $response ;
    }

    /**
     * Remove user from Heartbeat community
     * @param String Heartbeat community user email $useremail
     * @param String $group_id Heartbeat community user group_id 
     */
    private function heartbeat_remove_user_group($email,$group_id){

        $body = array(
            "emails"=>[$email],
        );
        $args = array(
            'method' => 'DELETE',
            'body' => json_encode($body),
            'headers' => $this->heartbeat_headers(),
        );

        $result = wp_remote_request(self::API_URL.'/groups/'.$group_id.'/memberships', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);

        return $response ;
    }

    /**
     * Add user to Heartbeat community
     * @param String $useremail Heartbeat community user email 
     * @param String $invite_id Heartbeat community invitation_id 
     */
    private function hearbeat_invite_user($useremail,$invite_id){

        $body = array(
            "emails"=>[$useremail],
            "shouldSendEmail"=>true,
        );
        $args = array(
            'method' => 'POST',
            'body' => json_encode($body),
            'headers' => $this->heartbeat_headers(),
        );
       
        $result = wp_remote_request(self::API_URL.'/invitations/'.$invite_id, $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);

        return $response ;
    }

     /**
     * Add user to Heartbeat community
     * @param String $useremail Heartbeat community user email 
     * @param String $username Heartbeat community user name 
     * @param String $role Heartbeat community user role 
     * @param String $group_id Heartbeat community user group_id 
     */
    private function hearbeat_add_user($useremail,$username, $role,$group_id){

        $body = array(
            "email"=>$useremail,
            "name"=>$username,
            "roleID"=>$role,
            "groupIDs"=>[$group_id]
        );
        $args = array(
            'method' => 'PUT',
            'body' => json_encode($body),
            'headers' => $this->heartbeat_headers(),
        );
       
        $result = wp_remote_request(self::API_URL.'/users', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);

        return $response ;
    }

    /**
     * Add user to group 
     * @param String $useremail Heartbeat community user email 
     * @param String $group_id Heartbeat community user group_id 
     */
    private function hearbeat_add_user_group($useremail,$group_id){
        
        $body = array(
            "emails"=>[$useremail],
        );
        $args = array(
            'method' => 'PUT',
            'body' => json_encode($body),
            'headers' => $this->heartbeat_headers(),
        );
       
        $result = wp_remote_request(self::API_URL.'/groups/'.$group_id.'/memberships', $args );
        $json_data = wp_remote_retrieve_body($result);
        $response = json_decode($json_data);
        return $response;
    }


    /**
     * Prepare API request headers
     * @return Array
     */
    private function heartbeat_headers(){

        return array(
            'Authorization' => 'Bearer '.$this->api_key,
            'Content-Type' => 'application/json',
        );
    }

}