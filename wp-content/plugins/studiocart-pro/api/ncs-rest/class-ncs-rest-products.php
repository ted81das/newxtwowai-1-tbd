<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Silence is golden!
}

class SC_Rest_Products extends WP_REST_Controller {

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
	protected $rest_base = 'products';

    /**
	 * Register the routes for products.
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
	 * Check whether a given request has permission to read products.
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
	 * Get all products.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_items( $request ) {
		$response = array();
		$prepared_args = array();
		$prepared_args['post_type']  = 'sc_product';
		$prepared_args['posts_per_page']  = $request['per_page'];
		if ( ! empty( $request['offset'] ) ) {
			$prepared_args['offset'] = $request['offset'];
		} else {
			$prepared_args['offset'] = ( $request['page'] - 1 ) * $prepared_args['posts_per_page'];
		}

		$orderby_possibles = array(
			'id'             => 'ID',
			'product_name'     => 'post_title',
			'date_created'   => 'post_date',
			'date_modified'  => 'post_modified'
		);
		$prepared_args['orderby'] = isset($request['orderby']) ? $orderby_possibles[ $request['orderby'] ] : 'ID';
		$prepared_args['order'] = isset($request['order']) ? $request['order'] : 'desc';

		$result = new WP_Query( $prepared_args );

		$productData = array();

		if(!empty($result->posts)){
			
			foreach ( $result->posts as $product ) {
				$data = $this->prepare_item_for_response( $product, $request );
				$productData[] = $this->prepare_response_for_collection( $data );
			}

			$response = rest_ensure_response( $productData );

			// Store pagination values for headers then unset for count query.
			$per_page = (int) $prepared_args['posts_per_page'];
			$page = ceil( ( ( (int) $prepared_args['offset'] ) / $per_page ) + 1 );

			$prepared_args['fields'] = 'ID';

			$total_products = $result->found_posts;
			if ( $total_products < 1 ) {
				unset( $prepared_args['posts_per_page'] );
				unset( $prepared_args['offset'] );
				$count_query = new WP_Query( $prepared_args );
				$total_products = $result->found_posts;
			}
			$response->header( 'X-WP-Total', (int) $total_products );
			$max_pages = ceil( $total_products / $per_page );
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
	 * Prepare a single products output for response.
	 *
	 * @param  WP_Post          $product User object.
	 * @param  WP_REST_Request  $request Request object.
	 * @return WP_REST_Response $response Response data.
	 */
	public function prepare_item_for_response( $product, $request ) {
		
		$image = get_the_post_thumbnail_url($product->ID,'full');
		$plans = $this->get_product_plans($product->ID);

		$data = array(
			'id'            => $product->ID,
            'name'         	=> $product->post_title,
			'status'        => $product->post_status, 
			'description'   => $product->post_content, 
			'date_created'  => $product->post_date, 
			'date_updated'  => $product->post_modified, 
            'url'           => get_permalink($product->ID),
			'image_url' 	=> $image ? $image : '',
			'is_on_sale'	=> intval(sc_is_prod_on_sale($product->ID)),
			'plans'     	=> $plans
		);

        return $data;
	}


	public function get_product_plans($product_id){

		$product_plans = get_post_meta($product_id,'_sc_pay_options',true);
		$plans = array();

		foreach($product_plans as $value){
			$plan = array(
				'id' 			=> $value['option_id'],
				'name' 			=> $value['option_name'] ?? "",
				'price_type' 	=> $value['product_type'] ?? 'one-time',
				'price' 		=> sc_price_in_cents($value['price']),
			);

			if($plan['price_type'] == 'recurring'){
				$plan['installments'] = $value['installments'];
				$plan['interval'] = $value['interval'];
				$plan['frequency'] = $value['frequency'];
				$plan['trial_days'] = $value['trial_days'];
				$plan['signup_fee'] = sc_price_in_cents($value['sign_up_fee']);
			}

			if(isset($value['sale_price'])) {
				$plan['sale_name'] = $value['option_name'] ?? "";
				$plan['sale_price'] = sc_price_in_cents($value['sale_price']);

				if($plan['price_type'] == 'recurring') {
					$plan['sale_installments'] = $value['sale_installments'];
					$plan['sale_interval'] = $value['sale_interval'];
					$plan['sale_frequency'] = $value['sale_frequency'];
					$plan['sale_trial_days'] = $value['sale_trial_days'];
					$plan['sale_signup_fee'] = sc_price_in_cents($value['sale_sign_up_fee']);
				}
			}

			$plans[] = $plan;
		}

		return $plans;
	}

    /**
	 * Get a single product.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_item( $request ) {
		$id        = (int) $request['id'];
		$product = get_post( $id );

		if ( empty( $id ) || empty( $product->ID ) ) {
			return new WP_Error( 'sc_rest_invalid_id', __( 'Invalid resource ID.', 'ncs-cart' ), array( 'status' => 404 ) );
		}

		$product = $this->prepare_item_for_response( $product, $request );
		$response = rest_ensure_response( $product );

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
			'enum'              => array('id','product_name','date_created','date_modified'),
			'validate_callback' => 'rest_validate_request_arg',
		);
		$params['post_status']         = array(
			'description'       => __( 'Post by Status.', 'ncs-cart' ),
			'type'              => 'string',
			'default'           => 'any',
			'enum'              => array('publish','draft','trash','any'),
			'validate_callback' => 'rest_validate_request_arg',
		);

		return $params;
	}

    public function dump($data){
        print_r($data);exit;
    }

}

?>