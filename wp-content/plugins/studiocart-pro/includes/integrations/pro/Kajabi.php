<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Kajabi {
    
    private $service_name;
	private $service_label;
    private $enabled;

	public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() { 
        
        $this->enabled = get_option( '_sc_kajabi_enable' );
        
        $this->service_name = 'kajabi';
        $this->service_label = 'Kajabi';
        
        add_filter('_sc_integrations_tab_section', array($this, 'add_integration'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        if(!$this->enabled) return;
        
        add_filter('sc_integrations', array($this, 'add_integration'));        
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_customer'), 10, 3);
    
    }
    
    public function add_integration($integrations) {     
        $integrations[$this->service_name] = $this->service_label;
        return $integrations;
    }
    
    public function service_settings($options) {
        $options[$this->service_name] = array(
                                            $this->service_name.'-enable' => array(
                                                'type'          => 'checkbox',
                                                'label'         => esc_html__( 'Enable Kajabi', 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_kajabi_enable',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        );
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        
        $fields[1]['fields'][] = array(
            'text' =>array(
                'class'		    => 'widefat',
                'description'	=> '',
                'id'			=> 'kajabi_url',
                'label'		    => __('Webhook URL','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'text',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array (
                        array(
                            'field' => 'services',
                            'value' => 'kajabi', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                    )
            )
        );
        
        $fields[1]['fields'][] = array(
            'checkbox' =>array(
                'class'		=> 'widefat',
                'description'	=> '',
                'id'			=> 'kajabi_email_confirmation',
                'label'		=> __('Kajabi Confirmation Email','ncs-cart'),
                'placeholder'	=> '',
                'type'		=> 'checkbox',
                'value'		=> '',
                'class_size'		=> '',
                'conditional_logic' => array (
                        array(
                            'field' => 'services',
                            'value' => 'kajabi', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                    )
            )
        );
        
        return $fields;
    }
    
    function add_remove_customer($intg, $sc_product_id, $order) {
        $kajabi_email_confirmation = $intg['kajabi_email_confirmation'];
        $kajabi_url = $intg['kajabi_url'];
        $email = $order['email'];
        $phone = $order['phone'];
        $fname = $order['first_name'];
        $lname = $order['last_name'];
        
        $kajabi_add = array('email' => $email, 'name' => $fname.' '.$lname, 'external_user_id' => $email);
        
        $url = esc_url_raw($kajabi_url);
        if(!empty($kajabi_email_confirmation)){
            $url.='?send_offer_grant_email=true';
        }
        
        $request = wp_remote_post($url, array(
            'method'        => 'POST',
            'body'          => json_encode($kajabi_add),
            'timeout'       => 60,
            'sslverify'     => false,
            'redirection'   => 10,
            'httpversion'   => '1.0',
            'headers' => array(
                'Content-Type' => 'application/json',
            ),
        ) );
        
        $response = wp_remote_retrieve_body( $request );

        if ( is_wp_error( $request ) ) {
            $error_message = $request->get_error_message();
            sc_log_entry($order['id'], sprintf(__("Kajabi webhook response: %s", 'ncs-cart'), $error_message));
        } else if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            sc_log_entry($order['id'], sprintf(__("Kajabi webhook response: %s", 'ncs-cart'), $error_message));
        } else if (isset($request['body'])) {
            sc_log_entry($order['id'], __('Kajabi webhook response: ', 'ncs-cart') . $request['body'] );                   
        }
        
		return;	
	}
    
}