<?php
    
namespace Studiocart;


if (!defined('ABSPATH'))
	exit;

class Mailer_Lite {

    public $service_name;
    public $service_label;
    private $mailerliteKey;
    private $mailerAPI;
    private $curlOpts;

    public function __construct() {

        $this->service_name = 'mailerlite';
        $this->service_label = 'Mailerlite';
        $this->mailerAPI = 'https://api.mailerlite.com/api/v2';
        $this->mailerliteKey = get_option( '_sc_mailerlite_api_key' );
        
        add_action('plugins_loaded', array($this, 'init'));
        
    }

    public function init(){
        
        if(is_plugin_active('studiocart-mailerlite/mailerlite.php')){
            add_action( 'admin_notices', array($this,'sc_mailer_admin_notice') );
            return true;
        }
        
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        add_filter('sc_show_optin_checkbox_services', array($this, 'add_optin_service'));
          
        $this->curlOpts = $this->setCurlOpts(); 

        if($this->mailerliteKey){
            add_filter('sc_integrations', array($this, 'add_mailerlite_service'));
            add_filter('sc_integration_fields', array($this, 'add_mailerlite_int_fields'), 10, 2);
            add_action('studiocart_mailerlite_integrations', array($this, 'add_remove_mailerlite_subscriber'), 10, 3);
            add_filter('sc_integration_service_action_field_logic_options', array($this, 'mailerlite_subscribe_list'));
        }
    }

    public function add_optin_service($options) {
        $options[] = $this->service_name;
        return $options;
    }

    function sc_mailer_admin_notice() {
        ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php _e( 'Mailerlite is now included in Studiocart! Please deactivate and remove the Mailerlite addon plugin to continue using the integration.', 'sample-text-domain' ); ?></p>
        </div>
        <?php
    }

    public function settings_section($intigrations) {  
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;

    }

    public function service_settings($options){

        $options[$this->service_name] = array(

            'mailerlite-api-key' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'Mailerlite API Key', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_mailerlite_api_key',
                    'value'         => '',
                    'description'   => '',
                ),
                'tab'=>'integrations',
            ),
        
        );

        return $options;
    }

    public function add_mailerlite_int_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'mailerlite_group_name required',
                'id'			=> 'mailerlite_group',
                'label'		    => __('Group','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_mailerlite_groups(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'mailerlite', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                    )
                )
            );
        
        $fields[1]['fields'][] = array(
            'textarea' =>array(
                'class'		    => 'mailerlite_fields',
                'id'			=> 'mailerlite_fields',
                'label'		    => __('Custom Field Map','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'textarea',
                'note'   => __('Put each field pair on a separate line and use a colon (":") to separate the field name from the field value. For example: mailerlite_field_tag:studiocart_field_id','ncs-cart'),
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'mailerlite', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        return $fields;
    }

    
    public function mailerlite_subscribe_list($services) {
        $services[] = 'mailerlite';
        return $services;
    }
    
    public function add_remove_mailerlite_subscriber($int, $sc_product_id, $order) {        
        if( empty($int['mailerlite_group'])){
            return;
        }
        
        $groupId = $int['mailerlite_group'];
        
        if($int['service_action'] == 'subscribed') {
            $subscriber = [
                'email' => $order['email'],
                'name' => $order['first_name'],
                'fields' => [
                'last_name' => $order['last_name'],
                ]
            ];
            
            $ip = get_post_meta($order['id'], '_sc_ip_address', true);
            $keys = array('city','state','zip','country','phone');
            $fields = array('city','state','zip','country','phone');
            for($i=0;$i<count($keys);$i++) {
                if ( $field = get_post_meta( $order['id'], '_sc_'.$fields[$i], true ) ){
                    $subscriber['fields'][$keys[$i]] = $field;
                }
            }
            
            if(isset($int['mailerlite_fields']) && $int['mailerlite_fields']){
                $custom = [];
                $map = explode("\n", str_replace("\r", "", esc_attr($int['mailerlite_fields'])));
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
                $body = sc_webhook_order_body($order);

                foreach($custom as $k=>$v) {
                    if($v && isset($values[$v])){ // custom field value
                        $map[$k] = $values[$v]['value'];
                    } else if ($v && isset($body[$v])) { // webhook payload value
                        $map[$k] = $body[$v];
                    } else if ($v && isset($order[$v])) { // order data value
                        $map[$k] = $order[$v];
                    } else if ($v && preg_match('/"([^"]+)"/', html_entity_decode($v), $val)) { // static value in double quotes
                        $map[$k] = trim($val[1]);
                    } else if ($v && $val = get_post_meta($order['id'], '_sc_'.$v, true)) { // order meta value
                        $map[$k] = $val;
                    }
                }
                
                if(!empty($map)) {
                    $subscriber['fields'] = array_merge($subscriber['fields'], $map);
                }
            }
            
            $addedSubscriber = $this->add_subscriber($groupId, $subscriber); // returns added subscriber
            sc_log_entry($order['id'], "New Mailerlite subscriber added to group ID: " .$groupId);
        } else {
            $this->remove_subscriber($order,$groupId);
        }
        
    }

    
    public function add_mailerlite_service($options) {
        
        $options['mailerlite'] = "Mailerlite";
        return $options;
    }
    
    public function get_mailerlite_groups() {
       
        try{
            $group_dropdown = array();

            $this->curlOpts[CURLOPT_URL] = $this->mailerAPI.'/groups';
            $this->curlOpts[CURLOPT_CUSTOMREQUEST] = 'GET';
            $groups = $this->doRequest(); // returns array of groups 

            if(!empty($groups)){
                foreach ($groups as $group) {
                    $group_dropdown[$group->id]=$group->name;
                }
            }

            return $group_dropdown;
        } catch (Exception $e){
            
        }   
    }

    public function add_subscriber($groupId, $subscriber){

        $this->curlOpts[CURLOPT_URL] = $this->mailerAPI.'/groups/'.$groupId.'/subscribers';
        $this->curlOpts[CURLOPT_CUSTOMREQUEST] = 'POST';
        $this->curlOpts[CURLOPT_POSTFIELDS]= json_encode($subscriber);
        return $this->doRequest();
        
    }

    public function remove_subscriber($order,$groupId){

        $this->curlOpts[CURLOPT_URL] = $this->mailerAPI.'/subscribers/'.$order['email'];
        $this->curlOpts[CURLOPT_CUSTOMREQUEST] = 'GET';
        $subscriber = $this->doRequest();

        if(isset($subscriber->error)){

            sc_log_entry($order['id'], $subscriber->error->message.". Unable to remove from group ID: " .$groupId);
        
        }else{

            $this->curlOpts[CURLOPT_URL] = $this->mailerAPI.'/groups/'.$groupId.'/subscribers/'.$order['email'];
            $this->curlOpts[CURLOPT_CUSTOMREQUEST] = 'DELETE';
            $this->doRequest();
            sc_log_entry($order['id'], "Mailerlite subscriber removed from group ID: " .$groupId);

        }
        
    }

    public function setCurlOpts(){

        return [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json',
                'X-MailerLite-ApiKey: '.$this->mailerliteKey),
        ];
    }

    public function doRequest(){
     
        $data = array();
        $curl = curl_init();
        curl_setopt_array($curl,$this->curlOpts);
        $response = curl_exec($curl);

        if(curl_exec($curl)){
            $data = json_decode($response);
            return $data;
        }

        curl_close($curl);
        return $data;

    }
}