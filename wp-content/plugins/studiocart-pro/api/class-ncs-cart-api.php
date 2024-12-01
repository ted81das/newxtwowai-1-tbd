<?php

/**
 * Fired during plugin activation
 *
 * @link       https://ncstudio.co
 * @since      1.0.0
 *
 * @package    NCS_Cart
 * @subpackage NCS_Cart/api
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run api key authentication.
 *
 * @since      1.0.0
 * @package    NCS_Cart
 * @subpackage NCS_Cart/api
 * @author     N.Creative Studio <info@ncstudio.co>
 */
class NCS_Cart_API {
    /**
     * The route for the api calls to follow
     *
     * @since    1.0.0
     *
     * @var string Route for the api calls to follow
     */
    private $route = 'sc';

    /**
     * The version of this api.
     *
     * @since    1.0.0
     *
     * @var string The current version of this api.
     */
    private $version = '1';

    /**
     * The namespace to add to the api calls.
     *
     * @var string The namespace to add to the api call
     */
    private $namespace;

    /**
	 * The prefix of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prefix    The current version of this plugin.
	 */
	private $prefix;
    
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */

    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct( $prefix, $route = 'sc', $version = '1' ) {
        $this->route       = $route;
        $this->version     = $version;
        $this->prefix      = $prefix;
        $this->namespace   = $this->route . '/v' . intval( $this->version );
    }

    /**
     * Add the endpoints to the API
     */
    public function add_api_routes() {
        
        // eg: /wp-json/sc/v1/webhook/zap-validate/
        register_rest_route( $this->namespace, 'webhook/validate', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'validate' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/subscribe', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'subscribe' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/delete', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'delete' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/zap-validate', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'validate' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/zap-subscribe', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'subscribe' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/zap-delete', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'sc_zapier_delete' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/latest-orders', array(
            'methods'  => 'POST',
            'callback' => array( $this, 'sc_api_get_latest_orders' ),
            'permission_callback' => '__return_true'
        ));
    
        register_rest_route( $this->namespace, 'webhook/products', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'sc_api_get_products' ),
            'permission_callback' => '__return_true'
        ));
        
        register_rest_route( $this->namespace, 'webhook/plans', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'sc_api_get_plans' ),
            'permission_callback' => '__return_true'
        ));
   
        register_rest_route( $this->namespace, 'webhook/get-order', array(
            'methods'  => 'GET',
            'callback' => array( $this, 'sc_api_get_order' ),
            'permission_callback' => '__return_true'
        ));
    
    }
    
    public function validate($request) {
            
        if ( $request['api_key'] != get_option( '_sc_api_key' ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource.', 'ncs-cart' ), array( 'status' => rest_authorization_required_code() ) );
        }

        $response = new WP_REST_Response('');
        $response->set_status(200);

        return $response;
    }

    public function subscribe($request) {
            
        if ( $request['api_key'] != get_option( '_sc_api_key' ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource.', 'ncs-cart' ), array( 'status' => rest_authorization_required_code() ) );
        }

        if (isset($request['hook_url']) || isset($request['hookUrl'])) {
            
            $request['hook_url'] = $request['hook_url'] ?? $request['hookUrl'];

            $opt_name = 'sc_zaphooks';

            $zaphooks = get_option($opt_name);

            if (!$zaphooks) {
                $zaphooks = array();
            }

            $hook_id = time();
            $hook = array('url' => sanitize_text_field($request['hook_url'])); 

            if(isset($request['product_id']) && $request['product_id']) {
                $hook['product_id'] = sanitize_text_field($request['product_id']);
            }
            
            if(isset($request['plan_id']) && $request['plan_id']) {
                if(is_array($request['plan_id'])) {
                    $hook['plan_id'] = sanitize_text_field(implode(',',$request['plan_id']));
                    $hook['plan_id'] = explode(',', $hook['plan_id']);
                } else {
                    $hook['plan_id'] = sanitize_text_field($request['plan_id']);
                }                
            }
            
            if(isset($request['source']) && $request['source']){
                $hook['source'] = sanitize_text_field($request['source']);
            }
            
            if(isset($request['prices_in_cents']) && $request['prices_in_cents']){
                $hook['prices_in_cents'] = true;
            }
            
            // purchased - Product Purchased
            // refunded - Product Refunded
            // pending - COD Order Created
            // active - Subscription Active
            // completed - Installment Plan Completed
            // canceled - Subscription Canceled
            // paused - Subscription Paused
            // lead - Lead Captured (2-step form only)
            // trialing - subscription trialing 
            
            $request['eventName'] = $request['status'] ?? $request['eventName'] ?? false;
            if($request['eventName']){
                $request['eventName'] = (array) $request['eventName'];
                $triggers = array();
                foreach($request['eventName'] as $event) {
                    $triggers[] = sanitize_text_field($event);
                }
                $hook['trigger'] = $triggers;
            }

            $zaphooks[$hook_id] = $hook;

            $res = update_option( $opt_name, $zaphooks );
            if (!$res) {
               return new WP_Error( 'rest_subscribe_error', __( 'There was a problem adding this URL to the database.', 'ncs-cart' ), array('status' => 404) );
            }

            $response = new WP_REST_Response(array('id'=>$hook_id));
            $response->set_status(200);

            return $response;
        }

        $response = array( __('there was a problem adding this data to the database:', 'ncs-cart') );
        $response[] = $request;

        return new WP_Error( 'rest_subscribe_error', $response, array('status' => 404) );

    }
    
    public function sc_zapier_delete($request) {
            
        if (isset($request['id'])) {
            
            $id = sanitize_text_field($request['id']);

            $opt_name = 'sc_zaphooks';

            $zaphooks = get_option($opt_name);
            // $zaphooks = array();
            // eg: $zaphooks[1584499113] = array('url' => 'https://example.com/hook-request-origin');

            if (is_array($zaphooks)) {
                if ( isset($zaphooks[$id]) ) {

                    $return = $zaphooks[$id];
                    $return['id'] = $id;

                    unset($zaphooks[$id]);
                    update_option( $opt_name, $zaphooks );
                    $response = new WP_REST_Response(array($return));
                    $response->set_status(200);
                    return $response;
                } else {
                    return new WP_Error( 'rest_subscribe_error', __( 'no webhook with this ID found: ', 'ncs-cart' ) . $id, array('status' => 404) );
                }
            }

            return new WP_Error( 'rest_subscribe_error', __( 'no webhooks found', 'ncs-cart'), array('status' => 404) );

        }

        $response = new WP_REST_Response(array('Invalid hook URL sent!'));
        $response->set_status(200);

    }
    
    public function sc_api_get_latest_orders($request) {
            
        if ( $request['api_key'] != get_option( '_sc_api_key' ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource.', 'ncs-cart' ), array( 'status' => rest_authorization_required_code() ) );
        }

        $args = array('posts_per_page' => 10, 'post_status' => 'any');

        if(isset($request['product_id']) && $request['product_id']) {
            $args['meta_query'] = array(
                'relation'		=> 'OR',
                array(
                    'key' => '_sc_product_id',
                    'value' => intval($request['product_id']),
                ),
                array(
                    'key'		=> '_sc_order_bumps',
                    'value'		=> intval($request['product_id']),
                    'compare'	=> 'LIKE'
                )
            );
        }

        if(isset($request['plan_id']) && $request['plan_id']) {
            $p_args = array('key' => '_sc_option_id');
            if(is_array($request['plan_id'])) {
                $p_args['compare'] = 'IN';
                for($i=0;$i<count($request['plan_id']);$i++) {
                    $request['plan_id'][$i] = sanitize_text_field($request['plan_id'][$i]);
                }
                $p_args['value'] = $request['plan_id'];
            } else {
                $p_args['value'] = sanitize_text_field($request['plan_id']);
            }
            
			if(!isset($args['meta_query'])) {
				$args['meta_query'] = array();
			}
            
            $args['meta_query'][] = $p_args;
        }

        $request['eventName'] = $request['status'] ?? $request['eventName'] ?? false;
        if ($request['eventName']) {
            $args['post_status'] = sanitize_text_field($request['eventName']);
        }
        
        if($args['post_status'] == 'paid') {
            $args['post_status'] = array('paid', 'completed');
        } else if($args['post_status'] == 'pending') {
            $args['post_status'] = 'pending-payment';
        } else if (in_array($request['eventName'], array('active','completed','canceled', 'paused'))) {
            $args['post_type'] = 'sc_subscription';
        }

        $posts = sc_get_orders($args);
        if (empty($posts)) {
            return new WP_Error( 'no_orders', __('there are no orders for this product yet', 'ncs-cart'), array('status' => 404) );
        }

        $response = new WP_REST_Response($posts);
        $response->set_status(200);

        return $response;
    }
    
    public function sc_api_get_products() {
            
        $args = array(
            'posts_per_page' => -1,
            'post_type' => 'sc_product'
        );

        $products = array();
        $posts = get_posts($args);
        foreach($posts as $p) {
            $products[] = array('id' => $p->ID, 'name' => $p->post_title);
        }

        $response = new WP_REST_Response($products);
        $response->set_status(200);

        return $response;
    }
    
    /**
     * Grab payment plans by a product!
     *
     * @param array $request Options for the function.
     * @return string|null Post title for the latest,
     * or null if none.
     */
    public function sc_api_get_plans( $request ) {
        
        if(!isset($request['product_id'])) return;

        $plans = array();
        $id = intval($request['product_id']);
        
        $items = get_post_meta($id, '_sc_pay_options', true);
        if(is_array($items)) {
            foreach ( $items as $item ) {
                if ( $item['option_id'] != null && isset($item['option_name'])) {
                    $name = ($item['option_name']) ? $item['option_name'] : $item['option_id'];
                    $plans[] = array('id' => $item['option_id'], 'name' => $name);
                }
            }
        }
        
        $plans[] = array('id' => 'bump', 'name' => __('Purchased as a bump','ncs-cart'));
        $plans[] = array('id' => 'upsell', 'name' => __('Purchased as an upsell','ncs-cart'));
        $plans[] = array('id' => 'downsell', 'name' => __('Purchased as a downsell','ncs-cart'));
        
		$response = new WP_REST_Response($plans);
        $response->set_status(200);

        return $response;
        
    }
    
    public function sc_api_get_order($request) {
            
        if ( $request['api_key'] != get_option( '_sc_api_key' ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource.', 'ncs-cart' ), array( 'status' => rest_authorization_required_code() ) );
        }

        $order = sc_get_order(intval($request['order_id']));

        if (empty($order)) {
            return new WP_Error( 'no_order_found',$request, array('status' => 404) );
        }

        $response = new WP_REST_Response($order);
        $response->set_status(200);

        return $response;
    }

}
