<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class SC_Rest_Orders extends WP_REST_Controller {

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
	protected $rest_base = 'orders';

    /**
	 * Register the routes for orders.
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

		register_rest_route( $this->namespace, '/' . $this->rest_base . '/subscription/(?P<id>[\d]+)', array(
			'args' => array(
				'id' => array(
					'description' => __( 'Unique identifier for the resource.', 'ncs-cart' ),
					'type'        => 'integer',
				),
			),
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'get_subscription_orders' ),
				'permission_callback' => array( $this, 'get_permissions_check' ),
				'args'                => array(
					'context' => $this->get_context_param( array( 'default' => 'view' ) ),
				),
			),
			
			
			'schema' => array( $this, 'get_public_item_schema' ),
		) );
    }

	public function get_subscription_orders($request){
		
		$response = array();
		$prepared_args = array(
			'meta_key' => '_sc_subscription_id',
			'meta_value' => $request['id'],
			'post_type' => 'sc_order'
		);

		$prepared_args['posts_per_page']  = $request['per_page'];
		if ( ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['posts_per_page'];
		}

		$orderby_possibles = array(
			'id'             => 'ID',
			'date_created'   => 'post_date',
			'date_modified'  => 'post_modified'
		);
		
		$prepared_args['orderby'] = isset($request['orderby']) ? $orderby_possibles[ $request['orderby'] ] : 'ID';
		$prepared_args['order'] = isset($request['order']) ? $request['order'] : 'desc';

		if(isset($request['date_after']) && !empty($request['date_after'])){
			$prepared_args['date_query'] = array( 'after' => $request['date_after'] );
		}
		
		$result = new WP_Query( $prepared_args );

		$orderData = array();
		if(!empty($result->posts)){

			foreach ( $result->posts as $order ) {
				$data = $this->prepare_item_for_response( $order, $request );
				$orderData[] = $this->prepare_response_for_collection( $data );
			}

			$response = rest_ensure_response( $orderData );

			// Store pagination values for headers then unset for count query.
			$per_page = (int) $prepared_args['posts_per_page'];
			$page = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

			$prepared_args['fields'] = 'ID';

			$total_orders = $result->found_posts;
			if ( $total_orders < 1 ) {
				unset( $prepared_args['posts_per_page'] );
				unset( $prepared_args['offset'] );
				$count_query = new WP_Query( $prepared_args );
				$total_orders = $result->found_posts;
			}
			$response->header( 'X-WP-Total', (int) $total_orders );
			$max_pages = ceil( $total_orders / $per_page );
			$response->header( 'X-WP-TotalPages', (int) $max_pages );

			$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );
			if ( $page > 1 ) {
				$prev_page = $page - 1;
				if ( $prev_page > $max_pages ) {
					$prev_page = $max_pages;
				}
				$prev_link = add_query_arg( 'page', $prev_page, $base );
				$response->link_header( 'prev', $prev_link );
			}
			if ( $max_pages > $page ) {
				$next_page = $page + 1;
				$next_link = add_query_arg( 'page', $next_page, $base );
				$response->link_header( 'next', $next_link );
			}
		}

		return $response;
	}

    /**
	 * Check whether a given request has permission to read orders.
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
	 * Get all orders.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$response = array();
		$prepared_args = array();
		$prepared_args['post_type']  = 'sc_order';
		$prepared_args['posts_per_page']  = $request['per_page'];
		if ( ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['posts_per_page'];
		}

		$orderby_possibles = array(
			'id'             => 'ID',
			'date_created'   => 'post_date',
			'date_modified'  => 'post_modified',
		);

		$prepared_args['orderby'] = isset($request['orderby']) ? $orderby_possibles[ $request['orderby'] ] : 'ID';
		$prepared_args['order'] = isset($request['order']) ? $request['order'] : 'desc';
		
		if(isset($request['date_after']) && !empty($request['date_after'])){
			$prepared_args['date_query'] = array( 'after' => $request['date_after'] );
		}

		$result = new WP_Query( $prepared_args );

		if(!empty($result->posts)){

			$orderData = array();
			foreach ( $result->posts as $order ) {
				$data = $this->prepare_item_for_response( $order, $request );
				$orderData[] = $this->prepare_response_for_collection( $data );
			}

			$response = rest_ensure_response( $orderData );

			// Store pagination values for headers then unset for count query.
			$per_page = (int) $prepared_args['posts_per_page'];
			$page = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

			$prepared_args['fields'] = 'ID';

			$total_orders = $result->found_posts;
			if ( $total_orders < 1 ) {
				unset( $prepared_args['posts_per_page'] );
				unset( $prepared_args['offset'] );
				$count_query = new WP_Query( $prepared_args );
				$total_orders = $result->found_posts;
			}
			$response->header( 'X-WP-Total', (int) $total_orders );
			$max_pages = ceil( $total_orders / $per_page );
			$response->header( 'X-WP-TotalPages', (int) $max_pages );

			$base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );
			if ( $page > 1 ) {
				$prev_page = $page - 1;
				if ( $prev_page > $max_pages ) {
					$prev_page = $max_pages;
				}
				$prev_link = add_query_arg( 'page', $prev_page, $base );
				$response->link_header( 'prev', $prev_link );
			}
			if ( $max_pages > $page ) {
				$next_page = $page + 1;
				$next_link = add_query_arg( 'page', $next_page, $base );
				$response->link_header( 'next', $next_link );
			}
		}

		return $response;
	}


    /**
	 * Prepare a single orders output for response.
	 *
	 * @param  WP_Post          $order User object.
	 * @param  WP_REST_Request  $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $order, $request, $price_in_cents = true ) {

        //$user_info = array_map( function( $a ){ return $a[0]; }, get_user_meta( $order->ID ) );
		$data = sc_webhook_order_body($order->ID, '', $price_in_cents);

		$response = rest_ensure_response( $data );
        $response->data['link'] = rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $order->ID ) );
        return $response;
	}

    /**
	 * Get a single order.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id        = (int) $request['id'];
		$order = get_post( $id );

		if ( empty( $id ) || empty( $order->ID ) ) {
			return new WP_Error( 'sc_rest_invalid_id', __( 'Invalid resource ID.', 'ncs-cart' ), array( 'status' => 404 ) );
		}

		$order = $this->prepare_item_for_response( $order, $request );
		$response = rest_ensure_response( $order );

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
			'default'           => 'date_created',
			'enum'              => array('id','date_created','date_modified'),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['post_status']         = array(
			'description'       => __( 'Post by Status.', 'ncs-cart' ),
			'type'              => 'string',
			'default'           => 'any',
			'enum'              => array('publish','draft','trash','any'),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['date_after']         = array(
			'description'       => __( 'A filter based on the object created_date field.', 'ncs-cart' ),
			'type'              => 'string',
			'format'            => 'date',
			'default'           => '',
			'validate_callback' => [$this, 'sc_rest_validate_date'],
		);

		return $params;
	}

	public function sc_rest_validate_date( $value, $request, $param ) {

		if($value){

			$attributes = $request->get_attributes();

			if ( ! isset( $attributes['args'][ $param ] ) || ! is_array( $attributes['args'][ $param ] ) ) {
				return true;
			}
			$args = $attributes['args'][ $param ];
		
			if ( 'string' === $args['type'] && ! is_string( $value ) ) {
				return new WP_Error( 'sc_invalid_param', sprintf( __( '%1$s is not of type %2$s', 'ncs-cart' ), $param, 'string' ) );
			}
		
			if ( 'date' === $args['format'] ) {
				$regex = '#^\d{4}-\d{2}-\d{2}$#';
		
				if ( ! preg_match( $regex, $value, $matches ) ) {
					return new WP_Error( 'sc_invalid_param', __( 'The date you provided is invalid.', 'ncs-cart' ) );
				}
			}
		}
	
		return true;
	}

    public function dump($data){
        print_r($data);exit;
    }

}

?>