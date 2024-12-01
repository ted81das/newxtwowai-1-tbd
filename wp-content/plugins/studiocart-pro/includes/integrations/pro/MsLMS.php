<?php

namespace MsLMS;

if (!defined('ABSPATH'))
	exit;

class MsLMS {
    
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
        $this->service_name = 'mslms';
        $this->service_label = 'MasterStudy LMS';
        $this->api_key = get_option('_sc_mslms_api_key');
        add_action('plugins_loaded', array( $this, 'init'));
    }
    
    public function init() {
        if ( ! defined( 'MS_LMS_VERSION' ) ) {
            return;
        }
      
        add_filter( 'sc_integrations', array($this, 'add_service' ) );
        add_filter( 'sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action( 'studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_mslms'), 10, 3);
    }

    public function add_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;
    }

    public function add_integration_fields($fields, $save){
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'mscorse',
                'id'			=> 'mscorse',
                'label'		    => __('Course','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_courses(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'mslms',
                            'compare' => '=',
                        )
                )
            ),
        );

        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'ms_order_status',
                'id'			=> 'ms_order_status',
                'label'		    => __('Order Status','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_order_statuses(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => 'mslms',
                            'compare' => '=',
                        )
                )
            )
        );

        return $fields;
    }

    private function get_order_statuses(){
        return array( 
            'pending' => __( 'Pending', 'ncs-cart' ),  
            'completed' => __( 'Completed', 'ncs-cart' ),  
            'cancelled' => __( 'Cancelled', 'ncs-cart' ), 
        );
    }

    public function get_courses() {
        $courses = array(''=>__('-- None Found --', 'ncs-cart') );
		$args = array( 'post_type' => 'stm-courses', 'posts_per_page' => -1 );
        $the_query = new \WP_Query( $args );
        if ( $the_query->have_posts() ) {
            $courses = array(''=>__('-- Select --', 'ncs-cart'));
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $courses[ get_the_ID() ] = get_the_title();
            }            
        }
        wp_reset_query();
		return $courses;
	}

    public function add_remove_to_mslms($int, $sc_product_id, $order){
        if( $int['services'] != 'mslms' ){
			return;
	    }

        $order_status = 'pending';
        if( $order['status'] == 'paid' ){
            $order_status = 'completed';
        } 

        $course_id = $int['mscorse'];
        $price = get_post_meta( $course_id, 'price', true );
       
        $cart_items = array(
            array(
                'item_id' => $course_id,
                'price' => is_numeric($price) ? intval($price) : 0,
            )
        );

        $user_id = sc_get_order_user_id($order, true);

         if ( ! is_wp_error( $user_id ) ) {
            $order_info = array(
                'user_id'         => $user_id,
                'cart_items'      => $cart_items,
                'date'            => time(),
                'status'          => $order_status,
                'order_key'       => uniqid( $user_id . time() ),
                'payment_code'    => $order['pay_method'],
                '_order_total'    => $order['amount'],
                '_order_currency' => $order['currency'],
            );
            
            $order_post = array(
                'post_type'   => 'stm-orders',
                'post_title'  => wp_strip_all_tags( $order_info['order_key'] ),
                'post_status' => 'publish',
            );

            $order_id = wp_insert_post( $order_post );

            foreach ( $order_info as $meta_key => $meta_value ) {
                update_post_meta( $order_id, $meta_key, $meta_value );
            }
            
            /* add student in course */
            foreach ( $cart_items as $cart_item ) {
                \STM_LMS_Course::add_user_course( $cart_item['item_id'], $user_id, 0, 0 );
                \STM_LMS_Course::add_student( $cart_item['item_id'] );
            }
        }
    }
}