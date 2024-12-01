<?php
/**
 * Application Name: Notion
 * Description: Add Notion integration to FlowMattic.
 * Version: 2.0
 * Author: InfiWebs
 * Author URI: https://www.infiwebs.com
 * Textdomain: flowmattic
 *
 * @package FlowMattic
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Notion integration class.
 *
 * @since 1.0
 */
class FlowMattic_Notion {
	/**
	 * Notion API URL
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $api_url = 'https://api.notion.com/v1/';

	/**
	 * Request body.
	 *
	 * @access public
	 * @since 1.0
	 * @var array|string
	 */
	public $request_body;

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for notion.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'notion',
			array(
				'name'         => esc_attr__( 'Notion', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/notion/icon.svg',
				'instructions' => __( 'Connect Notion with your favourite apps.', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'type'         => 'trigger,action',
				'version'      => '2.0',
			)
		);

		// Settings for OAuth authentication.
		$connect_settings = array(
			'name'           => 'Notion',
			'fm_auth_type'   => 'oauth',
			'auth_api_addto' => 'header',
			'endpoint'       => 'https://api.flowmattic.com/notion',
			'icon'           => FLOWMATTIC_APP_URL . '/notion/icon.svg',
		);

		// Add the connect to the list.
		flowmattic_add_connect( 'notion', $connect_settings );

		// Ajax to get the databases.
		add_action( 'wp_ajax_flowmattic_get_notion_databases', array( $this, 'get_databases_ajax' ) );

		// Ajax to get the peoples.
		add_action( 'wp_ajax_flowmattic_get_notion_peoples', array( $this, 'get_peoples_ajax' ) );

		// Ajax to get the items.
		add_action( 'wp_ajax_flowmattic_get_notion_items', array( $this, 'get_items_ajax' ) );

		// Ajax to get the parent_pages.
		add_action( 'wp_ajax_flowmattic_get_notion_parent_pages', array( $this, 'get_parent_pages_ajax' ) );

		// Add filter for the Notion polling method.
		add_filter( 'flowmattic_poll_api_notion', array( $this, 'poll_notion' ), 10, 5 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-notion', FLOWMATTIC_APP_URL . '/notion/view-notion.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 2.0
	 * @return Array
	 */
	public function get_triggers() {
		return array(
			'new_database_item' => array(
				'title'       => esc_attr__( 'New Database Item', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new item is created in a database.', 'flowmattic' ),
				'api_polling' => true,
			),
			'update_database_item' => array(
				'title'       => esc_attr__( 'Updated Database Item', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an item in a selected database is updated.', 'flowmattic' ),
				'api_polling' => true,
			),
		);
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_actions() {
		return array(
			'create_database_item' => array(
				'title'       => esc_attr__( 'Create Database Item', 'flowmattic' ),
				'description' => esc_attr__( 'Creates an item in a database', 'flowmattic' ),
			),
			'update_database_item' => array(
				'title'       => esc_attr__( 'Update Database Item', 'flowmattic' ),
				'description' => esc_attr__( 'Updates a database item', 'flowmattic' ),
			),
			'get_database_details' => array(
				'title'       => esc_attr__( 'Get Database Details', 'flowmattic' ),
				'description' => esc_attr__( 'Get details of a specific database', 'flowmattic' ),
			),
			'find_database_item'   => array(
				'title'       => esc_attr__( 'Find Database Item', 'flowmattic' ),
				'description' => esc_attr__( 'Searches for an item in a database by property', 'flowmattic' ),
			),
			'create_page'          => array(
				'title'       => esc_attr__( 'Create Page', 'flowmattic' ),
				'description' => esc_attr__( 'Creates a Page inside a parent page', 'flowmattic' ),
			),
			'find_page_by_title'   => array(
				'title'       => esc_attr__( 'Find Page (By Title)', 'flowmattic' ),
				'description' => esc_attr__( 'Searches for a page by title', 'flowmattic' ),
			),
			'create_comment'       => array(
				'title'       => esc_attr__( 'Create Comment on page', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new text comment on any given page', 'flowmattic' ),
			),
		);
	}

	/**
	 * Get the Bearer token.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action step data.
	 * @return array
	 */
	public function get_bearer_token( $fields ) {
		$connect_id   = ( isset( $fields['connect_id'] ) && '' !== $fields['connect_id'] ) ? $fields['connect_id'] : '';
		$bearer_token = '';

		if ( '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$bearer_token = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';
		}

		return $bearer_token;
	}

	/**
	 * Poll Notion API.
	 *
	 * @access public
	 * @since 2.0
	 * @param Array  $default_response  Default response.
	 * @param String $workflow_id       Workflow ID.
	 * @param String $workflow_data     Workflow Data.
	 * @param Array  $workflow_settings Workflow Settings.
	 * @param Bool   $is_capturing      Whether the workflow is in capture mode.
	 * @return Array
	 */
	public function poll_notion( $default_response, $workflow_id, $workflow_data, $workflow_settings, $is_capturing = false ) {
		// Get the notion database ID.
		$database_id = isset( $workflow_data['database_id'] ) ? $workflow_data['database_id'] : '';

		// Get the connect id.
		$connect_id = isset( $workflow_data['trigger_connect_id'] ) ? $workflow_data['trigger_connect_id'] : '';

		// Get the bearer token.
		$bearer_token = $this->get_bearer_token( array( 'connect_id' => $connect_id ) );

		// Get the trigger action.
		$trigger_action = isset( $workflow_data['action'] ) ? $workflow_data['action'] : '';

		// Create endpoint URL.
		$endpoint = 'databases/' . $database_id . '/query';

		// Get the item index.
		$item_index = 'results';

		// Define the needle.
		$needle = 'results_id';

		// Get the Polling Frequency.
		$api_polling_frequency = isset( $workflow_data['apiPollingFrequency'] ) ? (int) $workflow_data['apiPollingFrequency'] : 10;

		// Get the simple response.
		$simple_response = isset( $workflow_data['simple_response'] ) ? $workflow_data['simple_response'] : 'Yes';

		// If Database ID is empty, return.
		if ( empty( $database_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Database ID is empty.', 'flowmattic' ),
			);
		}

		// If connect id is empty, return.
		if ( empty( $connect_id ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Connect ID is empty.', 'flowmattic' ),
			);
		}

		$timestamp = 'created_time';

		// If the action is update, change the timestamp to last edited time.
		if ( 'update_database_item' === $trigger_action ) {
			$timestamp = 'last_edited_time';
		}

		// Prepare the request body.
		$body = wp_json_encode(
			array(
				'sorts' => array(
					array(
						'timestamp' => $timestamp,
						'direction' => 'descending',
					),
				),
			)
		);

		// Prepare the request args.
		$request_args = array(
			'body'        => $body,
			'headers'     => array(
				'User-Agent'     => 'FlowMattic/' . FLOWMATTIC_VERSION,
				'Content-Type'   => 'application/json',
				'Notion-Version' => '2022-06-28',
				'Authorization'  => 'Bearer ' . $bearer_token,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => 'POST',
		);

		// Get the response.
		$response = wp_remote_request( $this->api_url . $endpoint, $request_args );

		// If the response is an error, return.
		if ( is_wp_error( $response ) ) {
			return array(
				'status'  => 'error',
				'message' => $response->get_error_message(),
			);
		}

		// Get the response code.
		$response_code = wp_remote_retrieve_response_code( $response );

		// If the response code is 404, return.
		if ( 404 === $response_code ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Database not found.', 'flowmattic' ),
			);
		}

		// Get the response body.
		$request_body  = wp_remote_retrieve_body( $response );
		$response_json = json_decode( $request_body, true );

		// If the response body is empty, return.
		if ( empty( $request_body ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'API response is empty.', 'flowmattic' ),
			);
		}

		$response = array(
			'status'          => 'success',
			'message'         => esc_html__( 'Update received.', 'flowmattic' ),
			'webhook_capture' => '',
		);

		$response_body      = array();
		$processed_response = array();
		$records_to_process = array();
		$index_record       = array();

		// Loop through the response body and get only the first level of nested arrays.
		foreach ( $response_json as $key => $value ) {
			// If index is set, get the item index.
			if ( '' !== $item_index && $key === $item_index ) {
				$index_record[ $key ] = ( ! empty( $value ) && is_array( $value ) ) ? $value[0] : $value;
				continue;
			}

			// If index is not set, try finding the key in itemizer indexes.
			if ( '' === $item_index && in_array( $key, $this->get_array_itemizer_indexes(), true ) ) {
				$index_record[ $key ] = ( ! empty( $value ) && is_array( $value ) ) ? $value[0] : $value;

				// Set the item index.
				$item_index = $key;

				continue;
			}

			$response_body[ $key ] = $value;
		}

		// If index record is found, merge it with the response body.
		if ( ! empty( $index_record ) ) {
			$response_body = array_merge( $response_body, $index_record );
		}

		$processed_response = wp_flowmattic()->api_polling->simple_response( $response_body, $simple_response );

		// Set the response body.
		$response['webhook_capture'] = $processed_response;

		if ( $is_capturing ) {
			// Since the data is just captured, update it as the stored data.
			wp_flowmattic()->api_polling->update_stored_data( $workflow_id, (array) $workflow_settings, $processed_response );

			// Return the response.
			return $response;
		}

		// Remove the webhook capture.
		unset( $response['webhook_capture'] );

		// Check if data item is new, if the trigger is update_database_item.
		if ( 'update_database_item' === $trigger_action ) {
			// Check if data is changed.
			$is_data_changed = ( $processed_response['results_created_time'] !== $processed_response['results_last_edited_time'] ) ? true : false;

			// If the data is not changed, return.
			if ( ! $is_data_changed ) {
				return array(
					'status'  => 'success',
					'message' => esc_html__( 'No changes detected in the database.', 'flowmattic' ),
				);
			}
		}

		// Check if data is changed.
		$is_data_changed = wp_flowmattic()->api_polling->compare_stored_data( $workflow_settings, $processed_response, $needle );

		// If the data is not changed, return.
		if ( ! $is_data_changed ) {
			return array(
				'status'  => 'success',
				'message' => esc_html__( 'No changes detected in the database.', 'flowmattic' ),
			);
		}

		// Merge the response.
		$response = array_merge( $processed_response, $response );

		// If is json or xml, loop through the request body and get the records to process until the stored data is found.
		$records_to_process = wp_flowmattic()->api_polling->get_records_to_process( $workflow_settings, $request_body, $processed_response, $item_index, $simple_response, $needle );

		// If records to process are found, process them.
		if ( ! empty( $records_to_process ) ) {
			// Loop through the records to process.
			foreach ( $records_to_process as $record ) {
				// Add the status and message to the record.
				$record['status']  = 'success';
				$record['message'] = esc_html__( 'New feed item detected', 'flowmattic' );

				// Check if data item is new, if the trigger is update_database_item.
				if ( 'update_database_item' === $trigger_action ) {
					// Check if data is changed.
					$is_data_changed = ( $record['results_created_time'] !== $record['results_last_edited_time'] ) ? true : false;

					// If the data is not changed, return.
					if ( ! $is_data_changed ) {
						continue;
					}
				} else {
					// Check if created time is already passed than the frequency.
					$created_time = strtotime( $record['results_created_time'] );
					$now          = time();
					$diff         = $now - $created_time;

					// If the created time is already passed than the frequency, continue.
					if ( $diff > ( $api_polling_frequency * 60 ) ) {
						continue;
					}
				}

				// Run the workflow.
				wp_flowmattic()->api_polling->run_workflow( $workflow_id, $record );
			}
		}
	}

	/**
	 * Run the action step.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id  Workflow ID.
	 * @param object $step         Workflow current step.
	 * @param array  $capture_data Data captured by the WordPress action.
	 * @return array
	 */
	public function run_action_step( $workflow_id, $step, $capture_data ) {
		$step   = (array) $step;
		$action = $step['action'];
		$fields = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );

		// Check if connect is selected, else throw an error.
		if ( ! isset( $fields['connect_id'] ) || '' === $fields['connect_id'] ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_attr__( 'Connect is required but not provided.', 'flowmattic' ),
				)
			);
		}

		switch ( $action ) {
			case 'create_database_item':
				$response = $this->create_database_item( $fields );
				break;

			case 'update_database_item':
				$response = $this->update_database_item( $fields );
				break;

			case 'get_database_details':
				$response = $this->get_database_details( $fields );
				break;

			case 'find_database_item':
				$response = $this->find_database_item( $fields );
				break;

			case 'create_page':
				$response = $this->create_page( $fields );
				break;

			case 'find_page_by_title':
				$response = $this->find_page_by_title( $fields );
				break;

			case 'create_comment':
				$response = $this->create_comment( $fields );
				break;
		}

		return $response;
	}

	/**
	 * Ajax to get databases.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_databases_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$request_args = array();

		// Send request.
		$response_data = $this->make_get_request( $_POST, $request_args, 'databases', '2021-08-16' );

		// Remove the status.
		unset( $response_data['status'] );

		$databases = array();

		if ( isset( $response_data['results'] ) ) {
			foreach ( $response_data['results'] as $key => $database ) {
				$databases['databases'][ $database['id'] ] = array(
					'id'         => $database['id'],
					'name'       => $database['title'][0]['plain_text'],
					'properties' => $database['properties'],
				);
			}
		} else {
			$databases = array(
				'status'  => 'error',
				'message' => esc_attr__( 'Failed to retrieve databases', 'flowmattic' ),
			);
		}

		echo wp_json_encode( $databases );

		die();
	}

	/**
	 * Ajax to get users.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_peoples_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$request_args = array();

		// Send request.
		$response_data = $this->make_get_request( $_POST, $request_args, 'users', '2021-08-16' );

		// Remove the status.
		unset( $response_data['status'] );

		$users = array();

		if ( isset( $response_data['results'] ) ) {
			foreach ( $response_data['results'] as $key => $user ) {
				if ( 'person' === $user['type'] ) {
					$users['users'][] = array(
						'id'   => $user['id'],
						'name' => $user['name'],
					);
				}
			}
		} else {
			$users = array(
				'status'  => 'error',
				'message' => esc_attr__( 'Failed to retrieve users', 'flowmattic' ),
			);
		}

		echo wp_json_encode( $users );

		die();
	}

	/**
	 * Ajax to get items.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_items_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$database_id  = isset( $_POST['database_id'] ) ? $_POST['database_id'] : '';
		$request_args = array();

		$endpoint = 'databases/' . $database_id . '/query';

		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $_POST );

		$args = array(
			'headers'     => array(
				'User-Agent'     => 'FlowMattic',
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => '2021-08-16',
			),
			'timeout'     => 20,
			'sslverify'   => false,
			'data_format' => 'body',
		);

		// Send request.
		$request = wp_remote_post( $this->api_url . $endpoint, $args );

		$response_code  = wp_remote_retrieve_response_code( $request );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$items = array();

		if ( isset( $request_decode['results'] ) && ! empty( $request_decode['results'] ) ) {
			foreach ( $request_decode['results'] as $key => $database ) {
				if ( isset( $database['properties']['Name']['title'][0] ) ) {
					$items['items'][ $database['id'] ] = array(
						'id'         => $database['id'],
						'name'       => $database['properties']['Name']['title'][0]['plain_text'],
						'properties' => $database['properties'],
					);
				}
			}
		} else {
			$items = array(
				'status'  => 'error',
				'message' => esc_attr__( 'Failed to retrieve items', 'flowmattic' ),
			);
		}

		echo wp_json_encode( $items );

		die();
	}

	/**
	 * Ajax to get parent pages.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_parent_pages_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$request_args = array();

		$endpoint = 'search';

		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $_POST );

		$request_data = array(
			'filter' => array(
				'value'    => 'page',
				'property' => 'object',
			),
		);

		$args = array(
			'headers'     => array(
				'User-Agent'     => 'FlowMattic',
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => '2021-08-16',
			),
			'timeout'     => 20,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request = wp_remote_post( $this->api_url . $endpoint, $args );

		$response_code  = wp_remote_retrieve_response_code( $request );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$items = array();

		if ( isset( $request_decode['results'] ) && ! empty( $request_decode['results'] ) ) {
			foreach ( $request_decode['results'] as $key => $database ) {
				if ( isset( $database['parent'] ) && ( 'page_id' === $database['parent']['type'] || 'workspace' === $database['parent']['type'] ) ) {
					$items['parentPages'][ $database['id'] ] = array(
						'id'   => $database['id'],
						'name' => $database['properties']['title']['title'][0]['plain_text'],
					);
				}
			}
		} else {
			$items = array(
				'status'  => 'error',
				'message' => esc_attr__( 'Failed to retrieve items', 'flowmattic' ),
			);
		}

		echo wp_json_encode( $items );

		die();
	}

	/**
	 * Create Database Item.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function create_database_item( $fields ) {
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';
		$content     = isset( $fields['content'] ) ? $fields['content'] : '';
		$embed_files = isset( $fields['embed_attachments'] ) && 'no' !== $fields['embed_attachments'] ? true : false;
		$field_ids   = ( isset( $fields[ 'notion_template_fields_' . $database_id ] ) && '' !== $fields[ 'notion_template_fields_' . $database_id ] ) ? explode( ',', $fields[ 'notion_template_fields_' . $database_id ] ) : '';

		// Check if fields are available.
		if ( '' === $database_id ) {
			return wp_json_encode(
				array(
					'status'   => 'error',
					'response' => 'Database ID is required',
				)
			);
		}

		$properties  = array();
		$attachments = array();

		foreach ( $field_ids as $key => $field_id ) {
			if ( ! isset( $fields[ 'variable_field_' . $field_id ] ) ) {
				continue;
			}

			$field_value = $fields[ 'variable_field_' . $field_id ];
			$field_type  = $fields[ 'variable_field_' . $field_id . '_type' ];
			$field_name  = $fields[ 'variable_field_' . $field_id . '_name' ];

			if ( $field_value && $field_name ) {
				$formatted_value = $this->format_value( $field_type, $field_value );

				if ( 'files' === $field_type ) {
					// If files should be embeded in blocks.
					if ( $embed_files ) {
						$attachments[ $field_name ] = $field_value;
					}
				}

				$properties[ $field_name ] = $formatted_value;
			}
		}

		// Form the request.
		$request_data = array(
			'parent'     => array(
				'type'        => 'database_id',
				'database_id' => $database_id,
			),
			'properties' => $properties,
		);

		// Initialize the blocks.
		$blocks = array();

		// Upload attachments.
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $field_name => $value ) {
				$image_formats = array(
					'bmp',
					'gif',
					'heic',
					'jpeg',
					'jpg',
					'png',
					'svg',
					'tif',
					'tiff',
				);

				$media_type = 'file';

				// Extract the file extension from the URL.
				$extension = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );
				if ( in_array( $extension, $image_formats, true ) ) {
					$media_type = 'image';
				} elseif ( 'pdf' === $extension ) {
					$media_type = 'pdf';
				}

				$blocks[] = array(
					'object'    => 'block',
					'type'      => $media_type,
					$media_type => array(
						'type'     => 'external',
						'external' => array(
							'url' => $value,
						),
					),
				);
			}
		}

		if ( '' !== $content ) {
			// Define the content blocks to be created inside the page.
			$blocks[] = array(
				'object'    => 'block',
				'type'      => 'paragraph',
				'paragraph' => array(
					'rich_text' => array(
						array(
							'type' => 'text',
							'text' => array(
								'content' => $content,
							),
						),
					),
				),
			);
		}

		if ( ! empty( $blocks ) ) {
			// Append the blocks to the main request.
			$request_data['children'] = $blocks;
		}

		$endpoint = 'pages';

		$response_data = $this->request( $fields, $request_data, $endpoint, 'POST', '2022-06-28' );

		return $response_data;
	}

	/**
	 * Update Database Item.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function update_database_item( $fields ) {
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';
		$item_id     = isset( $fields['item_id'] ) ? $fields['item_id'] : '';
		$content     = isset( $fields['content'] ) ? $fields['content'] : '';
		$embed_files = isset( $fields['embed_attachments'] ) && 'no' !== $fields['embed_attachments'] ? true : false;
		$field_ids   = ( isset( $fields[ 'notion_template_fields_' . $database_id ] ) && '' !== $fields[ 'notion_template_fields_' . $database_id ] ) ? explode( ',', $fields[ 'notion_template_fields_' . $database_id ] ) : '';

		// Check if fields are available.
		if ( '' === $database_id ) {
			return wp_json_encode(
				array(
					'status'   => 'error',
					'response' => 'Database ID is required',
				)
			);
		}

		$properties  = array();
		$attachments = array();

		foreach ( $field_ids as $key => $field_id ) {
			if ( ! isset( $fields[ 'variable_field_' . $field_id ] ) ) {
				continue;
			}

			$field_value = $fields[ 'variable_field_' . $field_id ];
			$field_type  = $fields[ 'variable_field_' . $field_id . '_type' ];
			$field_name  = $fields[ 'variable_field_' . $field_id . '_name' ];

			if ( $field_value && $field_name ) {
				$formatted_value = $this->format_value( $field_type, $field_value );

				if ( 'files' === $field_type ) {
					// If files should be embeded in blocks.
					if ( $embed_files ) {
						$attachments[ $field_name ] = $field_value;
					}
				}

				$properties[ $field_name ] = $formatted_value;
			}
		}

		// Form the request.
		$request_data = array(
			'parent'     => array(
				'type'        => 'database_id',
				'database_id' => $database_id,
			),
			'properties' => $properties,
		);

		// Initialize the blocks.
		$blocks = array();

		// Upload attachments.
		if ( ! empty( $attachments ) ) {
			foreach ( $attachments as $field_name => $value ) {
				$image_formats = array(
					'bmp',
					'gif',
					'heic',
					'jpeg',
					'jpg',
					'png',
					'svg',
					'tif',
					'tiff',
				);

				$media_type = 'file';

				// Extract the file extension from the URL.
				$extension = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );
				if ( in_array( $extension, $image_formats, true ) ) {
					$media_type = 'image';
				} elseif ( 'pdf' === $extension ) {
					$media_type = 'pdf';
				}

				$blocks[] = array(
					'object'    => 'block',
					'type'      => $media_type,
					$media_type => array(
						'type'     => 'external',
						'external' => array(
							'url' => $value,
						),
					),
				);
			}
		}

		if ( '' !== $content ) {
			// Define the content blocks to be created inside the page.
			$blocks[] = array(
				'object'    => 'block',
				'type'      => 'paragraph',
				'paragraph' => array(
					'rich_text' => array(
						array(
							'type' => 'text',
							'text' => array(
								'content' => $content,
							),
						),
					),
				),
			);
		}

		// Update the properties.
		$endpoint = 'pages/' . $item_id;

		$response_data         = $this->request( $fields, $request_data, $endpoint, 'PATCH', '2022-06-28' );
		$response_data_decode1 = json_decode( $response_data, true );
		$updated_response_data = $response_data_decode1;

		// Update the blocks.
		if ( ! empty( $blocks ) ) {
			$endpoint = 'blocks/' . $item_id . '/children';

			$response_data         = $this->request( $fields, array( 'children' => $blocks ), $endpoint, 'PATCH', '2022-06-28' );
			$response_data_decode2 = json_decode( $response_data, true );
			$updated_response_data = array_merge( $response_data_decode1, $response_data_decode2 );
		}

		return wp_json_encode( $updated_response_data );
	}

	/**
	 * Get Database details.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function get_database_details( $fields ) {
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';

		// Set the request body.
		$this->request_body = array(
			'database_id' => $database_id,
		);

		$endpoint = 'databases/' . $database_id;

		$request_args   = array();
		$response_array = array();

		// Send request.
		$response_data = $this->make_get_request( $fields, $request_args, $endpoint, '2021-08-16' );

		foreach ( $response_data as $key => $value ) {
			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Find Database Item.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function find_database_item( $fields ) {
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';
		$item_name   = isset( $fields['item_name'] ) ? $fields['item_name'] : '';

		// Set the request body.
		$this->request_body = array(
			'database_id' => $database_id,
			'item_name'   => $item_name,
		);

		$endpoint = 'databases/' . $database_id . '/query';

		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $fields );

		$request_data = array(
			'filter' => array(
				'property' => 'Name',
				'text'     => array(
					'equals' => $item_name,
				),
			),
		);

		$args = array(
			'headers'     => array(
				'User-Agent'     => 'FlowMattic',
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => '2021-08-16',
			),
			'timeout'     => 20,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request = wp_remote_post( $this->api_url . $endpoint, $args );

		$response_code  = wp_remote_retrieve_response_code( $request );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$response_array = array();

		if ( isset( $request_decode['results'] ) && ! empty( $request_decode['results'] ) ) {
			foreach ( $request_decode['results'] as $database_page ) {
				foreach ( $database_page as $key => $value ) {
					if ( is_array( $value ) ) {
						$response_array = flowmattic_recursive_array( $response_array, $key, $value );
					} else {
						$response_array[ $key ] = $value;
					}
				}
			}
		} else {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_attr__( 'No page found for the given search term', 'flowmattic' ),
			);
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Create Page.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function create_page( $fields ) {
		$database_id = isset( $fields['database_id'] ) ? $fields['database_id'] : '';
		$page_id     = isset( $fields['parent_page_id'] ) ? $fields['parent_page_id'] : '';
		$content     = isset( $fields['content'] ) ? $fields['content'] : '';
		$page_title  = isset( $fields['page_title'] ) ? $fields['page_title'] : '';

		// Set the request body.
		$this->request_body = array(
			'database_id'    => $database_id,
			'parent_page_id' => $page_id,
			'content'        => $content,
			'page_title'     => $page_title,
		);

		// Form the request.
		$request_data = array(
			'parent'     => array(
				'page_id' => $page_id,
			),
			'properties' => array(
				'title' => array(
					'title' => array(
						array(
							'text' => array(
								'content' => $page_title,
							),
						),
					),
				),
			),
		);

		// Initialize the blocks.
		$blocks = array();

		if ( '' !== $content ) {
			// Define the content blocks to be created inside the page.
			$blocks[] = array(
				'object'    => 'block',
				'type'      => 'paragraph',
				'paragraph' => array(
					'rich_text' => array(
						array(
							'type' => 'text',
							'text' => array(
								'content' => $content,
							),
						),
					),
				),
			);
		}

		// Create the new page.
		$endpoint = 'pages';

		$response_data         = $this->request( $fields, $request_data, $endpoint, 'POST', '2022-06-28' );
		$response_data_decode1 = json_decode( $response_data, true );
		$updated_response_data = $response_data_decode1;

		// Update the blocks.
		if ( ! empty( $blocks ) && isset( $updated_response_data['id'] ) ) {
			$endpoint = 'blocks/' . $updated_response_data['id'] . '/children';

			$response_data         = $this->request( $fields, array( 'children' => $blocks ), $endpoint, 'PATCH', '2022-06-28' );
			$response_data_decode2 = json_decode( $response_data, true );
			$updated_response_data = array_merge( $response_data_decode1, $response_data_decode2 );
		}

		return wp_json_encode( $updated_response_data );
	}

	/**
	 * Find Page (By Title).
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function find_page_by_title( $fields ) {
		$page_title = isset( $fields['page_title'] ) ? $fields['page_title'] : '';

		$endpoint = 'search';

		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $fields );

		// Set the request body.
		$this->request_body = array(
			'page_title' => $page_title,
		);

		$request_data = array(
			'filter'    => array(
				'value'    => 'page',
				'property' => 'object',
			),
			'sort'      => array(
				'direction' => 'descending',
				'timestamp' => 'last_edited_time',
			),
			'query'     => $page_title,
			'page_size' => 1,
		);

		$args = array(
			'headers'     => array(
				'User-Agent'     => 'FlowMattic',
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => '2021-08-16',
			),
			'timeout'     => 20,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request = wp_remote_post( $this->api_url . $endpoint, $args );

		$response_code  = wp_remote_retrieve_response_code( $request );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$response_array = array();

		if ( isset( $request_decode['results'] ) && ! empty( $request_decode['results'] ) ) {
			foreach ( $request_decode['results'] as $database_page ) {
				foreach ( $database_page as $key => $value ) {
					if ( is_array( $value ) ) {
						$response_array = flowmattic_recursive_array( $response_array, $key, $value );
					} else {
						$response_array[ $key ] = $value;
					}
				}
			}
		} else {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_attr__( 'No page found for the given search term', 'flowmattic' ),
			);
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Create comment.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields.
	 * @return array
	 */
	public function create_comment( $fields ) {
		$page_id      = isset( $fields['parent_page_id'] ) ? $fields['parent_page_id'] : '';
		$comment_text = isset( $fields['comment_text'] ) ? $fields['comment_text'] : '';

		// Set the request body.
		$this->request_body = array(
			'parent_page_id' => $page_id,
			'comment_text'   => $comment_text,
		);

		// Form the request.
		$request_data = array(
			'parent'     => array(
				'page_id' => $page_id,
			),
			'rich_text' => array(
				array(
					'text' => array(
						'content' => $comment_text,
					),
				),
			),
		);

		// Create the new comment.
		$endpoint = 'comments';

		$response_data = $this->request( $fields, $request_data, $endpoint, 'POST', '2022-06-28' );

		return $response_data;
	}

	/**
	 * Process the action request.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $fields       Action fields.
	 * @param array  $request_data Request data to be sent to flowlu api.
	 * @param string $endpoint     Endpoint slug to be used for request.
	 * @param string $method       Request method.
	 * @param string $version      Notion version.
	 * @return string
	 */
	public function request( $fields, $request_data, $endpoint, $method = 'POST', $version = '2021-08-16' ) {
		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $fields );

		$args = array(
			'headers'     => array(
				'User-Agent'     => 'FlowMattic',
				'Accept'         => 'application/json',
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => $version,
			),
			'timeout'     => 20,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
			'method'      => $method,
		);

		if ( empty( $request_data ) ) {
			unset( $args['body'] );
		}

		// Set the request body.
		$this->request_body = $request_data;

		// Send request.
		$request = wp_remote_request( $this->api_url . $endpoint, $args );

		$response_code  = wp_remote_retrieve_response_code( $request );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$response_array = array();

		if ( 200 !== $response_code ) {
			$response_array['status'] = 'error';
		} else {
			$response_array['status'] = 'success';
		}

		if ( is_array( $request_decode ) ) {
			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}
		} else {
			// If response is not array, it is an error.
			return wp_json_encode(
				array(
					'response'      => $request_body,
					'response_code' => $response_code,
				)
			);
		}

		if ( '' === $request_body ) {
			// If there's no response, return default message.
			return wp_json_encode(
				array(
					'status'   => esc_attr__( 'Error', 'flowmattic' ),
					'response' => esc_attr__( 'Request returned no response data', 'flowmattic' ),
				)
			);
		} else {
			return wp_json_encode( $response_array );
		}
	}

	/**
	 * GET request wrapper.
	 *
	 * @access public
	 * @since 1.0
	 * @param array  $fields       Action step fields.
	 * @param array  $request_args Request parameters.
	 * @param string $endpoint     API endpoint.
	 * @param string $version      Notion version.
	 * @return array
	 */
	public function make_get_request( $fields, $request_args, $endpoint, $version = '2021-08-16' ) {
		// Get Bearer Token.
		$bearer_token = $this->get_bearer_token( $fields );

		// Set the request body.
		$this->request_body = $request_args;

		$args = array(
			'headers'     => array(
				'Content-Type'   => 'application/json',
				'Authorization'  => 'Bearer ' . $bearer_token,
				'Notion-Version' => $version,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => $request_args,
		);

		// Send request.
		$request        = wp_remote_get( $this->api_url . $endpoint, $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_code   = wp_remote_retrieve_response_code( $request );
		$request_decode = json_decode( $request_body, true );

		$response_array = $request_decode;

		if ( 200 === $request_code ) {
			$response_array['status'] = 'success';
		} else {
			$response_array['status'] = 'error';
		}

		if ( '' === $request_body ) {
			// If there's no response, return default message.
			return array(
				'status'   => esc_attr__( 'Error', 'flowmattic' ),
				'response' => esc_attr__( 'Request returned no response data', 'flowmattic' ),
			);
		} else {
			return $response_array;
		}
	}

	/**
	 * Format value according to the given Notion property type.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $type  The type of the Notion property.
	 * @param mixed  $value The value to be formatted.
	 *
	 * @return array Formatted value for Notion property.
	 */
	public function format_value( $type, $value ) {
		switch ( $type ) {
			case 'title':
				return array(
					'title' => array(
						array( 'text' => array( 'content' => $value ) ),
					),
				);

			case 'rich_text':
				return array(
					'rich_text' => array(
						array( 'text' => array( 'content' => $value ) ),
					),
				);

			case 'number':
				return array(
					'number' => floatval( $value ),
				);

			case 'select':
				return array(
					'select' => array( 'name' => $value ),
				);

			case 'multi_select':
				return array(
					'multi_select' => array_map(
						function( $v ) {
							return array( 'name' => $v );
						},
						(array) $value
					),
				);

			case 'date':
				return array(
					'date' => array( 'start' => $value ),
				);

			case 'people':
				return array(
					'people' => array(
						array(
							'id' => $value,
						),
					),
				);

			case 'files':
				return array(
					'files' => array(
						array(
							'type'     => 'external',
							'external' => array(
								'url' => $value,
							),
							'name'     => basename( $value ),
						),
					),
				);

			case 'checkbox':
			case 'url':
			case 'email':
			case 'phone_number':
			case 'formula':
			case 'relation':
			case 'rollup':
			case 'created_time':
			case 'created_by':
			case 'last_edited_time':
			case 'last_edited_by':
				// Handle other types accordingly.
				return array( $type => $value );
		}
	}

	/**
	 * Test action event ajax.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $event_data Test event data.
	 * @return array
	 */
	public function test_event_action( $event_data ) {
		$event          = $event_data['event'];
		$workflow_id    = $event_data['workflow_id'];
		$fields         = $event_data;
		$response_array = array();

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data sent to API endpoint.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Notion();
