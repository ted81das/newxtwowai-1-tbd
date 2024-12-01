<?php

namespace Studiocart;

use WP_Query;

if (!defined('ABSPATH'))
	exit;
	
class RefundOrder {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;
	private $service_label;
    
	public function __construct() {
		$this->service_name = "sc_refund_order";
		$this->service_label = "Refund Order";
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        add_filter('sc_integrations', array($this, 'add_refund_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'maybe_refund_order_and_cancel'), 10, 3);
    }
	
    public function add_refund_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        
        $fields[1]['fields'][] = array(
            'select' => array(
                'class'		    => 'widefat required',
                'id'			=> 'sc_refund_prod_id',
                'label'		    => esc_html__('Select Product','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_products(),
                'note'   => esc_html__("Automatically refund the entire amount of the customer's most recent order containing this product (use wisely.)",'ncs-cart'),
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
	
	public function maybe_refund_order_and_cancel($int, $sc_product_id, $order) {

        $past_product_ids = get_post_meta($order['id'], '_sc_refund_integration_runs');
        if(is_array($past_product_ids) && in_array($int['sc_refund_prod_id'], $past_product_ids)) {
            return;
        }

        sc_log_entry($order['id'], sprintf('Order refund integration called by product ID: %s', $sc_product_id));
        
        $args = array();
        if($order['user_account']) { $args[] = 'user_id='.$order['user_account']; }
        if($order['email']) { $args[] = 'email='.$order['email']; }

        $shortcode = sprintf('[sc_customer_bought_product product_id=%s %s]', $int['sc_refund_prod_id'], implode(' ', $args));
        if($id = do_shortcode($shortcode)) {

            $data = array(
				'action' => 'sc_order_refund',
				'id' => $id,
				'restock' => 'YSE',
            );

            $ret = sc_order_refund($data);

            if ($ret == 'OK') {
                sc_log_entry($order['id'], sprintf(__("Order ID: %s has been refunded.", 'ncs-cart'), $id)); 
                add_post_meta($order['id'], '_sc_refund_integration_runs', $int['sc_refund_prod_id']);           
                  
                if($id = $order['subscription_id']) {
                    $sub = new \ScrtSubscription($id);
                    $now = ($int['sc_sub_cancel'] == 'yes') ? true : false;
                    
                    $out = sc_do_cancel_subscription($sub, $sub->subscription_id, $now, $echo=false);
                    
                    if($out == 'OK') {
                        sc_log_entry($order['id'], sprintf(__("Subscription ID: %s has been canceled", 'ncs-cart'), $id));
                    } else {
                        sc_log_entry($order['id'], sprintf(__("Error canceling subscription ID: %s! Message: %s", 'ncs-cart'), $id, $out));
                    }
                }

            } else {
                sc_log_entry($order['id'], $ret);
            }
        } else {
            sc_log_entry($order['id'], sprintf(__('No paid orders found for product ID: %s', 'ncs-cart'), $int['sc_refund_prod_id']));
        }
    }   
      
    private function get_products(){
        if (!isset($_GET['post'])) {
            return;
        }
        
        global $studiocart;
        remove_filter( 'the_title', array( $studiocart, 'public_product_name' ) );

        $options = array('' => __('-- Select Product --','ncs-cart'));
                
        // The Query
        $args = array(
            'post_type' => array( 'sc_product', 'sc_collection' ),
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'meta_query' => array(
                'key' => '_sc_pay_options',
                'compare' => 'EXISTS'
            )
        );
        $the_query = new WP_Query( $args );

        // The Loop
        if ( $the_query->have_posts() ) {
            while ( $the_query->have_posts() ) {
                $the_query->the_post(); 
                $options[get_the_ID()] = get_the_title() . ' (ID: '.get_the_ID().')';
            }
        } else{
            $options = array('' => __('-- none found --','ncs-cart'));
		}
        /* Restore original Post Data */
        wp_reset_postdata();
        
        add_filter( 'the_title', array( $studiocart, 'public_product_name' ) );
		return $options;
	}
}