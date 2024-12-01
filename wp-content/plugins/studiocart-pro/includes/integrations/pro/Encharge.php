<?php

namespace Studiocart;

if (!defined('ABSPATH'))
    exit;

class Encharge {
    
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
        $this->service_name = 'encharge';
        $this->service_label = 'Encharge';
        add_action('plugins_loaded', array($this, 'init'));
    }
	
    public function init() {
        //add_action('_sc_register_sections', array($this, 'settings_section'), 10, 2);
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        $this->api_key = get_option('_sc_encharge_write_key');        
        if ( $this->api_key ) {
            add_filter('sc_integrations', array($this, 'add_encharge_service'));
            add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
            add_filter('sc_show_optin_checkbox_services', array($this, 'add_encharge_to_services_list'), 10);
            add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_service'), 10, 3);
            
            $tracking_enabled = get_option('_sc_encharge_track_events');
            if ($tracking_enabled) {
                add_action( 'sc_order_complete', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_order_pending', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_subscription_active', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_subscription_canceled', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_subscription_paused', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_order_lead', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_subscription_completed', [ $this, 'trigger_encharge_event' ], 10, 3 );
                add_action( 'sc_order_refunded', [ $this, 'trigger_encharge_event' ], 10, 3 );
            }
        } 
    }
    
    public function settings_section($intigrations) {    
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;
        // add_settings_section(
		// 	$sc_name . '-' .$this->service_name,
		// 	apply_filters( $sc_name . 'section-title-'.$this->service_name, esc_html__( $this->service_label, 'ncs-cart' ) ),
		// 	array( $sc, 'section_integrations_settings' ),
		// 	$sc_name
		// );
    }
    
    public function service_settings($options){
        
        $options[$this->service_name] = array(
            $this->service_name.'-track-events' => array(
                'type'          => 'checkbox',
                'label'         => esc_html__( 'Enable Order Events', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_encharge_track_events',
                    'value' 		=> '',
                    'description' 	=> '',
                ),
                'tab'=>'integrations'
            ),
            $this->service_name.'-write-key' => array(
                'type'          => 'text',
                'label'         => esc_html__( 'Encharge Write Key', 'ncs-cart' ),
                'settings'      => array(
                    'id'            => '_sc_encharge_write_key',
                    'value'         => '',
                    'description'   => '',
                ),
                'tab'=>'integrations'
            )
        );
        return $options;
    }
    
    public function add_encharge_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;  
    }    
    
    public function add_integration_fields($fields, $save) {
        
        $fields[1]['fields'][] = array(
            'text' =>array(
                'class'		    => 'wp-enchtag-search-custom widefat',
                'id'			=> $this->service_name.'_tags',
                'label'		    => esc_html__('Tags (existing or new)', 'ncs-cart'),
                'placeholder'	=> esc_html__('List of tags separated by commas', 'ncs-cart'),
                'type'		    => 'text',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                )
            ),
        );
        
        $fields[1]['fields'][] = array(
            'text' =>array(
                'class'		    => 'widefat',
                'id'			=> $this->service_name.'_event_name',
                'label'		    => esc_html__('Event name', 'ncs-cart'),
                'placeholder'	=> esc_html__('Defaults to this integration\'s trigger name', 'ncs-cart'),
                'type'		    => 'text',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                )
            ),
        );
        
        $fields[1]['fields'][] = array(
            'textarea' =>array(
                'class'		=> '',
                'description'	=> '',
                'id'			=> $this->service_name.'_properties',
                'label'		=> __('Properties','ncs-cart'),
                'placeholder'	=> 'Name 1 : Value 1 &#10;Name 2 : Value 2',
                'type'		=> 'textarea',
                'value'		=> '',
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
    
	public function add_encharge_to_services_list($services) {
        $services[] = $this->service_name;
        return $services;
    }
    
    public function get_event_name($status, $id) {
        
        if ($status == 'completed' && get_post_type($id) == 'sc_order') {
            return 'Order Complete';
        } 
        
        $options = array(
			'paid'       => __('Product Purchased','ncs-cart'),
			'purchased'  => __('Product Purchased','ncs-cart'),
			'refunded'   => __('Product Refunded','ncs-cart'),
			'pending'    => __('Pending Order Created','ncs-cart'),
			'active'     => __('Subscription Active','ncs-cart'),
			'completed'  => __('Installment Plan Completed','ncs-cart'),
			'canceled'   => __('Subscription Canceled','ncs-cart'),
			'paused'     => __('Subscription Paused','ncs-cart'),
			'renewal'    => __('Subscription Renewal Charged','ncs-cart'),
			'failed'     => __('Subscription Renewal Failed','ncs-cart'),
			'lead'       => __('Lead Captured','ncs-cart'),
		);
				
		if ($status && isset($options[$status])) {
		    return $options[$status];
		}
        
    }
    
    public function trigger_encharge_event($status, $order, $order_type) {
        
        if($order_type == 'bump') {
            return;
        }
        
        // set event name
        $name = $this->get_event_name($status, $order['ID']);
        
        if($status=='lead') {
            $order_info = $order;
        } else {
            $order_info = sc_webhook_order_body($order['ID']);
        }
        
        $order['first_name'] = $order['first_name'] ?? $order['firstname'] ?? '';
        $order['last_name'] = $order['last_name'] ?? $order['last_name'] ?? '';
        
        // set posted data
        $data = array(
            "name" => $name,
            "user" => array(
                    "email" => $order['email'],
                    "firstName" => $order['first_name'],
                    "lastName" => $order['last_name']
            ),
            "sourceIp" => $order_info['ip_address'],
        );
        
        $data['properties'] = $order_info;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ingest.encharge.io/v1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json',
                'X-Encharge-Token: '.$this->api_key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);

        sc_log_entry($order['ID'], sprintf(esc_html__('"%s" event sent to Encharge', 'ncs-cart'), $name));
    }
        
    public function add_remove_to_service($int, $sc_product_id, $order) {           
        
        $curl = curl_init();
        
        // set event name
        if (isset($int[$this->service_name.'_event_name'])) {
            $name = $int[$this->service_name.'_event_name'];
        } else {
            $name = $this->get_event_name($int['service_trigger'], $order['id']);
        }
        
        // set posted data
        $data = array(
                "name" => $name,
                "user" => array(
                        "email" => $order['email'],
                        "firstName" => $order['first_name'],
                        "lastName" => $order['last_name']
                ),
                "sourceIp" => get_post_meta($order['id'], '_sc_ip_address', true),
            );
        
        // set tags
        if (isset($int[$this->service_name.'_tags'])) {
            $data["user"]["tags"] = $int[$this->service_name.'_tags'];
        }
        
        // set properties
        $properties = sc_webhook_order_body($sc_product_id);
        
        // set custom properties
        if (isset($int[$this->service_name.'_properties'])) {
            $options = explode("\n", str_replace("\r", "", esc_attr($int[$this->service_name.'_properties'])));
            for ($i=0;$i<count($options);$i++) {
                $option = explode(':', $options[$i]);
                if (count($option) == 2) {
                    $properties[trim($option[0])] = trim($option[1]);
                }
            }
        }
        
        $data['properties'] = $properties;
        
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://ingest.encharge.io/v1',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => array(
                'content-type: application/json',
                'X-Encharge-Token: '.$this->api_key
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        sc_log_entry($order['id'], esc_html__("Contact added to Encharge", 'ncs-cart'));         
    } 
}