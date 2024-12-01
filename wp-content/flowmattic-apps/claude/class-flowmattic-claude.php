<?php
/**
 * Application Name: Anthropic (Claude)
 * Description: Add Anthropic (Claude) integration to FlowMattic.
 * Version: 1.1.0
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
 * Claude integration class.
 *
 * @since 1.0
 */
class FlowMattic_Claude {
	/**
	 * Anthropic (Claude) API URL
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $api_url = 'https://api.anthropic.com/v1/';

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
		// Enqueue custom view for claude.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'claude',
			array(
				'name'         => esc_attr__( 'Anthropic (Claude)', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/claude/icon.svg',
				'instructions' => esc_attr__( 'Enter your Claude account API Key. Your credentials are stored securely in your site.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'type'         => 'action',
				'version'      => '1.1.0',
			)
		);

		// Settings for authentication.
		$connect_settings = array(
			'name'           => 'Anthropic (Claude)',
			'fm_auth_type'   => 'api',
			'auth_api_addto' => 'header',
			'auth_api_key'   => 'x-api-key',
			'icon'           => FLOWMATTIC_APP_URL . '/claude/icon.svg',
		);

		// Add the connect to the list.
		flowmattic_add_connect( 'claude', $connect_settings );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-claude', FLOWMATTIC_APP_URL . '/claude/view-claude.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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

		switch ( $action ) {
			case 'create_completion':
				$response = $this->create_completion( $fields );
				break;
		}

		return $response;
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
			'create_completion' => array(
				'title'       => esc_attr__( 'Create a Completion', 'flowmattic' ),
				'description' => esc_attr__( 'Generates a completion on the basis of a given prompt', 'flowmattic' ),
			),
		);
	}

	/**
	 * Generate content.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields workflow fields.
	 * @return array
	 */
	public function create_completion( $fields ) {
		$connect_id     = ( isset( $fields['connect_id'] ) && '' !== $fields['connect_id'] ) ? $fields['connect_id'] : '';
		$api_key        = '';
		$ai_model       = ( isset( $fields['ai_model'] ) && '' !== $fields['ai_model'] ) ? $fields['ai_model'] : 'claude-2.1';
		$prompt         = ( isset( $fields['prompt'] ) && '' !== $fields['prompt'] ) ? $fields['prompt'] : '';
		$max_tokens     = ( isset( $fields['max_tokens_to_sample'] ) && '' !== $fields['max_tokens_to_sample'] ) ? $fields['max_tokens_to_sample'] : 256;
		$stop_sequences = ( isset( $fields['stop_sequences'] ) && '' !== $fields['stop_sequences'] ) ? $fields['stop_sequences'] : '';
		$temperature    = ( isset( $fields['temperature'] ) && '' !== $fields['temperature'] ) ? $fields['temperature'] : '';
		$top_p          = ( isset( $fields['top_p'] ) && '' !== $fields['top_p'] ) ? $fields['top_p'] : '';
		$top_k          = ( isset( $fields['top_k'] ) && '' !== $fields['top_k'] ) ? $fields['top_k'] : 1;
		$user_id        = ( isset( $fields['user_id'] ) && '' !== $fields['user_id'] ) ? $fields['user_id'] : '';

		// Backward compatibility for ai_model.
		if ( 'claude-2' === $ai_model ) {
			$ai_model = 'claude-2.1';
		}

		if ( '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$connect_data = $connect->connect_settings;

			// Get the api key.
			$api_key = isset( $connect_data['auth_api_value'] ) ? $connect_data['auth_api_value'] : '';
		}

		if ( '' === $api_key ) {
			return wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_attr__( 'API Key is invalid. Please check your connect data.', 'flowmattic' ),
				)
			);
		}

		$messages = array(
			array(
				'role'    => 'user',
				'content' => $prompt,
			),
		);

		// Form the request.
		$request_data = array(
			'model'      => $ai_model,
			'messages'   => $messages,
			'max_tokens' => (int) $max_tokens,
			'top_k'      => (int) $top_k,
		);

		if ( '' !== $top_p ) {
			$request_data['top_p'] = $top_p;
		}

		if ( '' !== $temperature ) {
			$request_data['temperature'] = (int) $temperature;
		}

		if ( '' !== $user_id ) {
			$request_data['metadata'] = array(
				'user_id' => $user_id,
			);
		}

		if ( '' !== $stop_sequences ) {
			$request_data['stop_sequences'] = array(
				$stop_sequences,
			);
		}

		// Set the request body.
		$this->request_body = $request_data;

		$args = array(
			'headers'     => array(
				'x-api-key'         => $api_key,
				'User-Agent'        => 'FlowMattic',
				'Content-Type'      => 'application/json',
				'anthropic-version' => '2023-06-01',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode( $request_data ),
		);

		// Send request.
		$request        = wp_remote_post( $this->api_url . 'messages', $args );
		$request_body   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_body, true );

		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $request_decode['error'] ) ) {
			$response_array['status'] = 'error';
		}

		return wp_json_encode( $response_array );
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
		$event       = $event_data['event'];
		$settings    = $event_data['settings'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id = $event_data['workflow_id'];

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

// Hide integration till the API issue fixed.
new FlowMattic_Claude();
