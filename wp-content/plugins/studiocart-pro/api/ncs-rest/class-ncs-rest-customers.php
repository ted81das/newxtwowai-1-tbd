<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class SC_Rest_Customers extends WP_REST_Controller {

    /**
	 * Endpoint namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'sc/v1';
    
    /**
	 * Route base.
	 *
	 * @var string
	 */
	protected $rest_base = 'customers';

    /**
	 * Register the routes for customers.
	 */
	public function register_routes() {

		register_rest_route( $this->namespace, '/' . $this->rest_base, array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_items' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => $this->get_collection_params(),
			),
            'schema' => array( $this, 'get_public_item_schema' ),
		) );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'ncs-cart' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_item' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			
			
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
    }

    /**
	 * Check whether a given request has permission to read customers.
	 *
	 * @param  WP_REST_Request $request Full details about the request.
	 * @return WP_Error|boolean
	 */
	public function get_permissions_check( $request ) {
        if ( $request->get_header('x-api-key') != get_option( '_sc_api_key' ) ) {
            return new WP_Error( 'rest_forbidden_context', __( 'Sorry, you cannot view this resource.', 'ncs-cart' ), array( 'status' => rest_authorization_required_code() ) );
        }

        return true;
	}

    /**
	 * Get all customers.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$customers = sc_get_customers();
		$data = $this->prepare_item_for_response( $customers, $request );
		$users = $this->prepare_response_for_collection( $data );
		$response = rest_ensure_response( $users );
		return $response;
	}


    /**
	 * Prepare a single customer output for response.
	 *
	 * @param  WP_User          $user_data User object.
	 * @param  WP_REST_Request  $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $customers, $request ) {
       
		foreach ( $customers as $customer_email => $value ) {
			
			$user = get_user_by('email',$customer_email);
			$user_info = array_map( function( $a ){ return $a[0]; }, get_user_meta( $user->ID ) );
			$paid_amount = sc_price_in_cents(array_sum(array_column($value , 'total_amount')));
			$total_orders = count($value);
			$last_order = end($value);
			$user->lifetime_value = $paid_amount;
			$user->total_orders = $total_orders;
			$user->last_order_id = $last_order['id'];

			$data[] = array(
				'id'            => $user->ID,
				'first_name'    => $user_info['first_name'],
				'last_name'     => $user_info['last_name'],
				'email'         => $user->user_email,
				'date_created'  => $user->user_registered,
				'lifetime_value' => $paid_amount,
				'total_orders' => $total_orders,
				'last_order_date' => get_the_date( 'Y-m-d H:i:s', $last_order['id'] )
			);
		}

        return $data;
	}

    /**
	 * Get a single customer.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id        = (int) $request['id'];
		$customers = sc_get_customers($id);

		if ( empty( $id )) {
			return new WP_Error( 'sc_rest_invalid_id', __( 'Invalid resource ID.', 'ncs-cart' ), array( 'status' => 404 ) );
		}

		$response = $this->prepare_item_for_response( $customers, $request );
		$response = rest_ensure_response( $response );

		return $response;
	}

    /**
	 * Get the query params for collections of attachments.
	 *
	 * @return array
	 */
	public function get_collection_params() {
		$params                       = array();
		$params['context']            = $this->get_context_param();
		$params['context']['default'] = 'view';

		$params['page']            = array(
			'description'       => __( 'Current page of the collection.', 'ncs-cart' ),
			'type'              => 'integer',
			'default'           => 1,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
			'minimum'           => 1,
		);
		$params['per_page']        = array(
			'description'       => __( 'Maximum number of items to be returned in result set.', 'ncs-cart' ),
			'type'              => 'integer',
			'default'           => 10,
			'minimum'           => 1,
			'maximum'           => 100,
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);

        $params['offset']          = array(
			'description'       => __( 'Offset the result set by a specific number of items.', 'ncs-cart' ),
			'type'              => 'integer',
			'sanitize_callback' => 'absint',
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['order']           = array(
			'description'       => __( 'Order sort attribute ascending or descending.', 'ncs-cart' ),
			'type'              => 'string',
			'default'           => 'desc',
			'enum'              => array( 'asc', 'desc' ),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['orderby']         = array(
			'description'       => __( 'Sort collection by object attribute.', 'ncs-cart' ),
			'type'              => 'string',
			'default'           => 'id',
			'enum'              => array('id','name','registered_date'),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

    public function dump($data){
        print_r($data);exit;
    }

}

?>