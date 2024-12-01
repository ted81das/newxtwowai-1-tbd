<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Drip {
    
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
	private $account_id;

	public function __construct() {
        $this->service_name = 'drip';
        $this->service_label = 'Drip';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        //add_action('_sc_register_sections', array($this, 'settings_section'), 10, 2);
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        add_filter('sc_show_optin_checkbox_services', array($this, 'add_optin_service'));

        add_action( 'admin_notices', array($this,'admin_notices') );
        
        // grab new tags when these options are changed
        add_action( 'add_option__sc_'.$this->service_name.'_api_key', array($this, 'get_'.$this->service_name.'_tags'), 10 );
        add_action( 'update_option__sc_'.$this->service_name.'_api_key', array($this, 'get_'.$this->service_name.'_tags'), 10 );

        add_action( "add_option_" . '_sc_'.$this->service_name.'_sync_products', array($this, 'sync_products'), 10, 2 );
        add_action( "update_option_" . '_sc_'.$this->service_name.'_sync_products', array($this, 'sync_products'), 10, 2 );
        
        $this->api_key = get_option('_sc_'.$this->service_name.'_api_key');
        $this->account_id = get_option('_sc_'.$this->service_name.'_id');
        
        if ( $this->api_key && $this->account_id ) {
            add_action( 'sc_renew_integrations_lists', array($this, 'get_'.$this->service_name.'_tags'));            
            add_filter('sc_integrations', array($this, 'add_service'));
            add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
            add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_service'), 10, 3);
            
            $tracking_enabled = get_option('_sc_drip_shopper_activity');
            if ($tracking_enabled) {
                add_action( 'sc_order_lead', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_order_complete', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_order_pending', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_subscription_active', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_subscription_canceled', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_subscription_paused', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_order_lead', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_subscription_completed', [ $this, 'trigger_shop_event' ], 10, 3 );
                add_action( 'sc_order_refunded', [ $this, 'trigger_shop_event' ], 10, 3 );
            }
        }
    }
    
    public function trigger_shop_event($status, $order, $order_type) {

        if ($order_type == 'bump') return;
        if ($status != 'lead' && get_post_type($order['id']) != 'sc_order') return;
        
        $apikey 	= $this->api_key;
        $acctId 	= $this->account_id;
        $auth = base64_encode( $apikey . ':' . '' );
                
        $action = ($status == 'lead') ? 'shopper_activity/cart' : 'shopper_activity/order';
        
        $body = sc_webhook_order_body($order);
        $date = isset($body['date_time']) && $status != 'lead' ? $body['date_time'] : 'now';
        $time = strtotime( $date );
        $activity_type = 'order';
                
        if($status == 'lead') {
            $activity_type = 'cart';
            $args = [
                "provider"          => get_bloginfo( 'name' ),
                "email"             => $body['customer_email'],
                "action"            => 'created',
                "cart_id"           => $order['page_id'].'|'.date( 'c', $time ),
                //"occurred_at"       => date( 'c', $time ),
                "grand_total"       => $body['order_amount'] ?? '',
                //"total_discounts"   => 5.34,
                "currency"          => $order['currency'],
                "cart_url"          => get_permalink($order['page_id']),
                "items"             => [
                    [
                        "product_id"        => (string) $body['product_id'],
                        "product_variant_id"=> $body['payment_plan_id'] ?? '',
                        //"sku"               => "XHB-1234",
                        "name"              => sc_get_public_product_name($body['product_id']),
                        //"brand"             => "Drip",
                        //"categories"        => ["Accessories"],
                        "price"             => (float) $order['main_offer_amt'],
                        "quantity"          => 1,
                        //"discounts"         => 5.34,
                        "total"             => (float) $order['main_offer_amt'],
                        "product_url"       => get_permalink($order['page_id']),
                        //"image_url"         => "https://www.getdrip.com/images/example_products/water_bottle.png",
                        //"product_tag"       => "Best Seller"
                    ]
                ]
            ];
        } else if(in_array($status,['pending','paid','purchased','renewal','refunded'])) {
            switch($status) {
                case 'pending':
                    $status = 'placed';
                    break;
                case 'renewal':
                case 'purchased':
                    $status = 'paid';
                    break;
                default:
                    $status = $status;
                    break;
            }
            
            $args = [
                "provider"          => get_bloginfo( 'name' ),
                "email"             => $body['customer_email'],
                "action"            => $status,
                //"occurred_at"       => date( 'c', $time ),
                "order_id"          => (string) $body['id'] ?? '',
                "grand_total"       => $body['order_amount'] ?? '',
                //"total_discounts"   => 5.34,
                "total_taxes"       => $body['tax_amount'],
                "currency"          => $order['currency'],
                "items"             => [
                    [
                        "product_id"        => (string) $body['product_id'],
                        "product_variant_id"=> $body['payment_plan_id'] ?? '',
                        //"sku"               => "XHB-1234",
                        "name"              => sc_get_public_product_name($body['product_id']),
                        //"brand"             => "Drip",
                        //"categories"        => ["Accessories"],
                        "price"             => (float) $order['main_offer_amt'],
                        "quantity"          => 1,
                        //"discounts"         => 5.34,
                        "total"             => (float) $order['main_offer_amt'],
                        "product_url"       => get_permalink($order['page_id']),
                        //"image_url"         => "https://www.getdrip.com/images/example_products/water_bottle.png",
                        //"product_tag"       => "Best Seller"
                    ]
                ]
            ];
            
            if($status=='refunded') {
                $refunds = $order['refund_log'];
                $total_amount = $order['amount'];
                if(is_array($refunds)) {
                   $args['refund_amount'] = array_sum(array_column($refunds, 'amount'));
                }
            }
        }
        
        if ( isset($order['custom_prices']) ) {
            foreach($order['custom_prices'] as $id=>$price) {
                $args["items"][] = [
                    "product_id"        => (string) $id,
                    "product_variant_id"=> '',
                    "name"              => $price['label'],
                    "price"             => (float) $price['price'],
                    "quantity"          => (integer) $price['qty'],
                    "total"             => (float) $price['price'],
                    "product_url"       => get_permalink($order['page_id']),
                ];
            }
        }

        if (!empty($order['order_bumps']) && is_array($order['order_bumps'])) {
            foreach($order['order_bumps'] as $k=>$order_bump) {
                $args["items"][] = [
                    "product_id"        => (string) $order_bump['id'],
                    "product_variant_id"=> '',
                    "name"              => sc_get_public_product_name($order_bump['id']),
                    "price"             => (float) $order_bump['amount'],
                    "quantity"          => 1,
                    "total"             => (float) $order_bump['amount'],
                    "product_url"       => get_permalink($order['page_id']),
                ];
            }
        }        
        
        try{        
            $url = "https://api.getdrip.com/v3/{$acctId}/{$action}";
            $response = wp_remote_post($url, array(
                'headers' => [
                    'Authorization' => "Basic $auth",
                    'content-type' => 'application/json'
                ],
                'User-Agent' => get_bloginfo( 'name' ).' ('.get_bloginfo( 'url' ).')',
                'body' => json_encode($args),
            ));
            $response = wp_remote_retrieve_body( $response );
            if (isset($order['id']) && $order['id']) {
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    sc_log_entry($order['id'], sprintf(__("Shopper activity not added to Drip: %s", 'ncs-cart'), $activity_type, $error_message));
                } else {
                    $response = json_decode($response);
                    if ( isset($response->validation_errors)) {
                        sc_log_entry($order['id'], sprintf(__("Shopper activity not added to Drip: %s", 'ncs-cart'), $activity_type, print_r($response->validation_errors, true)));
                    } else {
                        $log_entry = sprintf(__("Shopper activity added to Drip.", 'ncs-cart'), $activity_type);
                        sc_log_entry($order['id'], $log_entry);
                    }
                }
            }

        } catch(\Exception $e) {
            echo $e->getMessage(); //add custom message
            return;
        }
    }
    
    public function sync_products( $old_value, $value ) {

        if(!$value) {
            return;
        }

        delete_option('_sc_'.$this->service_name.'_sync_products');

        $apikey 	= $this->api_key;
        $acctId 	= $this->account_id;
        $auth = base64_encode( $apikey . ':' . '' );
                
        $action = 'shopper_activity/product/batch';
        
        $activity_type = 'updated';

        $products = array();
        $args = array(
            'post_type'  => 'sc_product',
            'posts_per_page' => -1,
        );
        
        $posts = get_posts($args);
        if (!empty($posts)) {

            $args = [
                "provider"          => get_bloginfo( 'name' ),
                "action"            => $activity_type,
                //"sku"               => "XHB-1234",
                //"brand"             => "Drip",
                //"categories"        => ["Accessories"],
                //"image_url"         => "https://www.getdrip.com/images/example_products/water_bottle.png",
            ];

            foreach($posts as $post) {
                $scp = sc_setup_product($post->ID);
                $items = $scp->pay_options;

                $args['product_id'] = (string) $scp->ID;
                $args['name'] = sc_get_public_product_name($scp->ID);
                $args["product_url"] = get_permalink($scp->ID);

                foreach ($items as $item) {
                    if(isset($item['price'])) {
                        $args['product_variant_id'] = $item['option_id'];
                        $args["price"] = (float) $item['price'];

                        $products[] = $args;
                    }
                }
            }
                
            try{        
                $url = "https://api.getdrip.com/v3/{$acctId}/{$action}";
                $response = wp_remote_post($url, array(
                    'headers' => [
                        'Authorization' => "Basic $auth",
                        'content-type' => 'application/json'
                    ],
                    'User-Agent' => get_bloginfo( 'name' ).' ('.get_bloginfo( 'url' ).')',
                    'body' => json_encode(['products'=>$products]),
                ));
                $response = wp_remote_retrieve_body( $response );
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    update_option('sc_drip_sync_response', array('error'=>sprintf(__('Drip product sync unsuccessful: %s', 'ncs-cart'), $error_message)));
                } else {
                    update_option('sc_drip_sync_response', array('success'=>__('Drip product sync successful!', 'ncs-cart')));
                }

            } catch(\Exception $e) {
                update_option('sc_drip_sync_response', array('error'=>sprintf(__('Drip product sync unsuccessful: %s', 'ncs-cart'), $e->getMessage())));
                return;
            }               
        }
    }

    public function admin_notices() {
        if($msg = get_option('sc_drip_sync_response')) {
            foreach($msg as $class=>$msg) {
                echo '<div class="notice notice-'.$class.'"><p>'.$msg.'</p></div>';
                delete_option('sc_drip_sync_response');
            }
        }
    }
    
    public function add_optin_service($options) {
        $options[] = $this->service_name;
        return $options;
    }
    
    public function get_drip_tags() {
        $tags = array();
        $apikey 	= get_option( '_sc_'.$this->service_name.'_api_key' );
        $acctId 	= get_option( '_sc_'.$this->service_name.'_id' );
        $auth = base64_encode( $apikey . ':' . '' );
        if( $apikey && $acctId ){
            try{
                $url = "https://api.getdrip.com/v2/{$acctId}/tags";
                $response = wp_remote_get($url, array(
                    'headers' => [
                        'Authorization' => "Basic $auth"
                    ],
                    'User-Agent' => get_bloginfo( 'name' ).' ('.get_bloginfo( 'url' ).')'
                ));
                $responseBody = wp_remote_retrieve_body( $response );
                $result = json_decode( $responseBody, true );
                if ( is_array( $result ) && ! is_wp_error( $result ) ) {
                    foreach( $result['tags'] as $tag ){
                        $id 	= $tag;
                        $name 	= $tag;
                        $tags[$id] = $name;
                    }
                }

            } catch(\Exception $e) {
                echo $e->getMessage(); //add custom message
                return;
            }
        }
        update_option('sc_drip_tags', $tags);
        return $tags;
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
    
    public function service_settings($options) {
        $options[$this->service_name] = array(
                                            $this->service_name.'-id' => array(
                                                'type'          => 'text',
                                                'label'         => sprintf(esc_html__( '%s Account ID', 'ncs-cart' ), $this->service_label),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_id',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        
                                            $this->service_name.'-api-key' => array(
                                                'type'          => 'text',
                                                'label'         => sprintf(esc_html__( '%s API Key', 'ncs-cart' ), $this->service_label),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_api_key',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        
                                            $this->service_name.'-shopper-activity' => array(
                                                'type'          => 'checkbox',
                                                'label'         => esc_html__( 'Track shopper activity', 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_shopper_activity',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        
                                            $this->service_name.'-sync-products' => array(
                                                'type'          => 'checkbox',
                                                'label'         => esc_html__( 'Sync products', 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_sync_products',
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
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => '',
                'id'			=> $this->service_name.'_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => [
                                        'add'=>__('Subscribe person','ncs-cart'),
                                        'unsubscribe' => __('Unsubscribe person','ncs-cart'), 
                                        'remove-tag' => __('Remove tag','ncs-cart')
                                    ],
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
            'select' =>array(
                'class'		    => '',
                'id'			=> $this->service_name.'_tag',
                'label'		    => __('Tag','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_tags(),
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
       
        $apikey 	= $this->api_key;
        $acctId 	= $this->account_id;
        $auth = base64_encode( $apikey . ':' . '' );
        $tag = $int['drip_tag'];
                        
        if( $apikey && $acctId ){
                    
            if ( $int['drip_action'] == 'add' ) {

                $args = array (
                            "status" => 'active',
                            "email" => $order['email'],
                            "first_name" => $order['first_name'],
                            "last_name" => $order['last_name'],
                        );
                
                $fields = array('address1','address2','city','state','zip','country','phone');
                foreach($fields as $info) {
                    if ( isset($order[$info]) ){
                        $args[$info] = $order[$info];
                    }
                }

                if ( $tag ){
                    $args['tags'] = array($tag);
                }
                                
                try{        
                    $url = "https://api.getdrip.com/v2/{$acctId}/subscribers";
                    $response = wp_remote_post($url, array(
                        'headers' => [
                            'Authorization' => "Basic $auth",
                            'content-type' => 'application/json'
                        ],
                        'User-Agent' => get_bloginfo( 'name' ).' ('.get_bloginfo( 'url' ).')',
                        'body' => json_encode(array('subscribers'=>array($args))),
                    ));
                    $response = wp_remote_retrieve_body( $response );
                    if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                        sc_log_entry($order['id'], sprintf(__("Something went wrong adding subscriber to Drip: %s", 'ncs-cart'), $error_message));
                    } else {
                        $log_entry = __('Subscriber added to Drip.', 'ncs-cart');
                        sc_log_entry($order['id'], $log_entry);
                    }

                } catch(\Exception $e) {
                    echo $e->getMessage(); //add custom message
                    return;
                }
            } else {
                
                // unsubscribe
                if ( $int['drip_action'] == 'remove-tag' && $tag ){
                    $method = 'DELETE';
                    $url = "https://api.getdrip.com/v2/{$acctId}/subscribers/{$order['email']}/tags/{$tag}";
                    $fail_msg = __("Something went wrong removing tag from Drip subscriber: %s", 'ncs-cart');
                    $success_msg = __('Tag removed from Drip subscriber.', 'ncs-cart');
                } else {
                    $method = 'POST';
                    $url = "https://api.getdrip.com/v2/{$acctId}/subscribers/{$order['email']}/unsubscribe_all";
                    $fail_msg = __("Something went wrong removing subscriber from Drip: %s", 'ncs-cart');
                    $success_msg = __('Person unsubscribed from Drip.', 'ncs-cart');
                }
                
                try {        
                    $response = wp_remote_post($url, array(
                        'method' => $method,
                        'headers' => [
                            'Authorization' => "Basic $auth"
                        ],
                        'User-Agent' => get_bloginfo( 'name' ).' ('.get_bloginfo( 'url' ).')',
                    ));
                    $response = wp_remote_retrieve_body( $response );
                    if ( is_wp_error( $response ) ) {
                        $error_message = $response->get_error_message();
                        sc_log_entry($order['id'], sprintf($fail_msg, $error_message));
                    } else {
                        sc_log_entry($order['id'], $success_msg);
                    }

                } catch(\Exception $e) {
                    echo $e->getMessage(); //add custom message
                    return;
                }
            }
        }        
    }
    
    public function get_tags() {
		$options = array('' => __('-- select tag --','ncs-cart'));
        $tags = get_option('sc_drip_tags');
        if( !empty( $tags ) ){
           // $options = array_merge($options, $tags);
        	foreach ($tags as $key => $value) {
        		$options[$key]=$value;
        	}
        } else {
            $options = array('' => __('-- none found --','ncs-cart'));
        }
		return $options;
	}
}