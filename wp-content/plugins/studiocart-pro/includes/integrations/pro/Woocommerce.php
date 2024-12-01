<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Woocommerce {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name,$plugin_name,$plugin_title;

	public function __construct() {
        $this->service_name = 'WooCommerce';
		$this->plugin_name = 'ncs-cart';
		$this->plugin_title = 'Studiocart';
		if ( defined( 'NCS_CART_VERSION' ) ) {
			$this->version = NCS_CART_VERSION;
		} else {
			$this->version = '1.0';
		}
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        if ( ! class_exists( 'WooCommerce' ) ) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_woocommerce_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_woocommerce'), 10, 3);
		add_action( 'admin_enqueue_scripts', array($this, 'enqueue_scripts') );
        add_action( 'woocommerce_email_order_meta', array($this,'sc_add_email_order_meta'), 10, 3 );
    }
	
    public function enqueue_scripts(){
		global $post_type;
		if ( 'sc_product' === $post_type ) {
			wp_enqueue_script( $this->plugin_name.'-woo', plugin_dir_url( __FILE__ ) . 'js/' . $this->plugin_name . '-woo.js', array( $this->plugin_name ), $this->version, true );
			wp_localize_script( $this->plugin_name.'-woo', 'sc_woo_inti', array('search_products_nonce'  => wp_create_nonce( 'search-products' )) );
		}
	}
    public function add_woocommerce_service($options) {
        $options[$this->service_name] = "WooCommerce";
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'wc-product-search-custom sc-selectize multiple',
                'id'			=> 'wc_item',
                'label'		    => __('Product','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_item(),
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
                'id'			=> 'wc-include-bump',
                'label'		    => __('Include Bump Amount(s)','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => array(''=>'No','1'=>'Yes'),
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
                'class'		    => 'wc-product-search-custom sc-selectize multiple',
                'id'			=> 'wc_bump_item',
                'label'		    => __('Bump Product(s)','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_item(true),
                'conditional_logic' => array(
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    ),
                    array(
                        'field' => 'wc-include-bump',
                        'value' => "1", // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                )
            )
        );
        /*$fields[1]['fields'][] = array(
            'checkbox' =>array(
                'class'		    => '',
                'id'			=> 'sc-include-custom_fields',
                'label'		    => __('Include Custom Fields','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'checkbox',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );*/
        $fields[1]['fields'][] = array(
            'select' =>array(
            'class'		    => 'wc-order-status-custom',
            'id'			=> 'wc-status',
            'label'		    => __('WooCommerce Order Status','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'select',
            'value'		    => '1',
            'class_size'    => '',
            'selections'    => ($save) ? '' : $this->get_order_statuses(),
            'conditional_logic' => array(
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                )
        ));
        return $fields;
    }
    
    private function get_order_statuses(){
        return array( 
            'wc-processing' => __( 'Processing', 'ncs-cart' ),  
            'wc-pending' => __( 'Pending payment', 'ncs-cart' ),  
            'wc-on-hold' => __( 'On hold', 'ncs-cart' ),  
            'wc-completed' => __( 'Completed', 'ncs-cart' ),  
        );
    }
    public function add_remove_to_woocommerce($int, $sc_product_id, $order) {  
        global $woocommerce;

        if( empty($int['wc_item']) ){
			return;
	    }

        $order_info = new \ScrtOrder($order['id']);
        $order_data = $order;
        $company = "";
        if(isset($order['custom_fields'])){
            $order['woo_extra_data'] = $order['custom_fields'];
            if(isset($order['custom_fields']['company'])){
                $company = $order['custom_fields']['company']['value'];
            }
        }
        
        $order['wc_item'] = $int['wc_item'];
        $order['wc_status'] = $int['wc-status']??'wc-processing';
        $order['wc_bump_item'] = $int['wc_bump_item']??"";
        $order['bump_amount'] = 0;
        $order['wc-include-bump'] = $int['wc-include-bump']??"";
        $order['address'] = array(
            'first_name' => $order_info->firstname,
            'last_name'  => $order_info->lastname,
            'company'    => $company,
            'email'      => $order_info->email,
            'phone'      => $order_info->phone,
            'address_1'  => $order_info->address1,
            'address_2'  => $order_info->address2,
            'city'       => $order_info->city,
            'state'      => $order_info->state,
            'postcode'   => $order_info->zip,
            'country'    => $order_info->country,
        );

        $bump_amount_list = array();

        if($order['order_type']=='bump'){
            $order_amount = $order_data['amount'];
        } else {
            if(!empty($order_info->order_bumps)){
                $bump_amount_list = wp_list_pluck( $order_info->order_bumps, 'amount' );
            } else {
                $order['wc-include-bump'] = '';
            }
            $order_amount = $order_info->amount;
            $order['order_total_amount'] = \Automattic\WooCommerce\Utilities\NumberUtil::round($order_amount,2);
            $order['bump_amount'] = array_sum($bump_amount_list);
            if(empty($order['wc-include-bump']) ){
                $order_amount -= $order['bump_amount'];
                $order['order_total_amount'] = \Automattic\WooCommerce\Utilities\NumberUtil::round($order_amount,2);
            } else {
                if(!empty($order['wc_bump_item']) && is_array($order['wc_bump_item'])){
                    $order_amount -= $order['bump_amount'];
                }
            }
        }
        
        // if($order_amount==0 && $order['bump_amount']==0){
        //     return;
        // }
        
        $order['order_amount'] = \Automattic\WooCommerce\Utilities\NumberUtil::round($order_amount,2);
        $order['bump_amount'] = \Automattic\WooCommerce\Utilities\NumberUtil::round($order['bump_amount'],2);
        // first we create the order.  We already have assigned $user_id the user ID of the customer placing the order.
        $this->create_woo_order($order);
    }
    
    private function create_woo_order($order){
        $args = null;
        if($user_id = get_post_meta( $order['id'], '_sc_user_account', true )){
            $args = array(
                'customer_id'   => $user_id,
            );
        }
        $this->wooOrder = wc_create_order($args);
        
        // The add_product() function below is located in /plugins/woocommerce/includes/abstracts/abstract_wc_order.php
		$this->add_item_woo_order($order,'wc_item','order_amount');
        if($order['wc-include-bump'] == 1 ){
            $this->add_item_woo_order($order,'wc_bump_item','bump_amount');
        }
        // You can add more products if needed by repeating the above line
        $this->wooOrder->set_address( $order['address'], 'billing' );
        $this->wooOrder->set_address( $order['address'], 'shipping' );
        if(isset($order['woo_extra_data'])){
            $this->wooOrder->update_meta_data( 'sc_custom_meta', $order['woo_extra_data']);
            foreach($order['woo_extra_data'] as $woo_extra_data){
                $this->wooOrder->update_meta_data( $woo_extra_data['label'], $woo_extra_data['value'] );
            }
        }

        $this->wooOrder->calculate_totals();

        //Set Order total to SC order total
        if(!empty($order['order_total_amount'])){
            $this->wooOrder->set_total($order['order_total_amount']);
        }
        // here we are adding some system notes to the order
        sc_log_entry( $order['id'], sprintf(esc_html__("Created WooCommerce order ID: %d", 'ncs-cart' ), $this->wooOrder->get_id(), $order['wc_status']) );
        $this->wooOrder->update_status($order['wc_status'], sprintf(__('Imported order from Studiocart order ID: %d', 'ncs-cart'), $order['id']), TRUE);
          
    }

    private function add_item_woo_order($order,$item_key='wc_item',$amount_key='order_amount'){
        if(!empty($order[$item_key]) && is_array($order[$item_key])){
            $item_args = array();
            //Check if Single item to set in order. To set order total as price
            if(count($order[$item_key])==1){
                //Change item price for order to SC order total in case of single item.
                $product = get_product( $order[$item_key][0] );
                $return_price = $order[$amount_key];
                if ( $product->is_taxable()){
                    //echo 'here';
                    $tax_rates      = \WC_Tax::get_rates( $product->get_tax_class() );
                    $base_tax_rates = \WC_Tax::get_base_tax_rates( $product->get_tax_class( 'unfiltered' ) );
                    $remove_taxes   = apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ? \WC_Tax::calc_tax( $order[$amount_key], $base_tax_rates, true ) : \WC_Tax::calc_tax( $order[$amount_key], $tax_rates, true );
                    $return_price   = $order[$amount_key] - array_sum( $remove_taxes ); // Unrounded since we're dealing with tax inclusive prices. Matches logic in cart-totals class. @see adjust_non_base_location_price.
                }
                $item_args = array(  'subtotal'=>$return_price ,
                                'total'=>$return_price);
            }
			foreach($order[$item_key] as $wc_item):
				$this->wooOrder->add_product( get_product( $wc_item ), 1, $item_args); // This is the ID of an existing SIMPLE product
			endforeach;
		}
    }

    public function sc_add_email_order_meta($order_obj, $sent_to_admin, $plain_text){
        $sc_custom_meta = get_post_meta( $order_obj->get_order_number(), 'sc_custom_meta', true );
	
        // we won't display anything if it is not a gift
        if( empty( $sc_custom_meta ) )
            return;

        if ( $plain_text === false ) {

            // you shouldn't have to worry about inline styles, WooCommerce adds them itself depending on the theme you use
            echo '<h2>'.apply_filters('sc_wc_email_custom_field_heading','Additional information').'</h2>';
            echo '<table style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:\'Helvetica Neue\',Helvetica,Roboto,Arial,sans-serif" border="1" cellpadding="6" cellspacing="0">';
            foreach($sc_custom_meta as $meta){
                echo "<tr>";
                echo "<th style='color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left'>".apply_filters('sc_wc_email_custom_label'.$meta['label'],$meta['label'])."</th>";
                echo "<td style='color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left'>".$meta['value']."</td>";
                echo "</tr>";
            }
            
            echo '</table>';
        
        } else {
        
            echo apply_filters('sc_woo_email_custom_info_heading','Additional information')."\n";

            foreach($sc_custom_meta as $meta){
                echo apply_filters('sc_woo_email_custom_label'.$meta['label'],$meta['label']).": ".$meta['value'];
            }	
        
        }
    }
    public function get_item($bump=false) {
        if(!isset($_GET['post'])) return;
		$post_id = $_GET['post'];
        $integrations = get_post_meta($post_id,'_sc_integrations',true);
        $items = array('' => __('Begin typing to add products...', 'ncs-cart'));
        if(!empty($integrations)):
            
            foreach($integrations as $integration):
                if($bump){
                    $item_key = 'wc_bump_item';
                } else {
                    $item_key = 'wc_item';
                }
                if(!empty($integration[$item_key])):
		
					if(!empty($integration[$item_key]) && is_array($integration[$item_key])){
						foreach($integration[$item_key] as $wc_item):
							$product = wc_get_product( $wc_item );
                            if($product !== false){
                                $items[$wc_item] = wp_strip_all_tags($product->get_formatted_name());
                            }
						endforeach;
					}
                    
                endif;
            endforeach;
        endif;
        return $items;
	}
}