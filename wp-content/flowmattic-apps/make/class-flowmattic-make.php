<?php
/**
 * Application Name: Make
 * Description: Add make integration to FlowMattic.
 * Version: 1.0
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
 * Make integration class.
 *
 * @since 1.0
 */
class FlowMattic_Make {
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
		// Enqueue custom view for make.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'make',
			array(
				'name'         => esc_attr__( 'Make', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/make/icon.svg',
				'instructions' => __( 'Copy and enter the above webhook URL to your Make webhook setting', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'type'         => 'trigger,action',
				'version'      => '1.0',
			)
		);
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-make', FLOWMATTIC_APP_URL . '/make/view-make.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'receive_data' => array(
				'title' => esc_attr__( 'Receive Data', 'flowmattic' ),
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
			'send_data' => array(
				'title' => esc_attr__( 'Submit Data', 'flowmattic' ),
			),
		);
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
		$action         = $step['action'];
		$fields         = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
		$response_array = array();

		$post_fields = ( isset( $step['settings']['make_parameters'] ) ) ? $step['settings']['make_parameters'] : $step['make_parameters'];

		// CS.
		$capture_data;

		// Set the request body.
		$this->request_body = $post_fields;

		$args = array(
			'headers' => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $post_fields ),
		);

		// Create a new contact.
		$request       = wp_remote_post( $fields['make_webhook'], $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response      = wp_remote_retrieve_body( $request );

		if ( 200 !== $response_code ) {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					// translators: error code.
					'message' => ( 404 === $response_code ) ? sprintf( __( 'Error: %s. Webhook URL not found. Make sure your make workflow is turned ON.', 'flowmattic' ), $response_code ) : $response_code,
				)
			);
		}

		if ( '' === $response ) {
			return wp_json_encode(
				array(
					'status' => esc_attr__( 'Success', 'flowmattic' ),
				)
			);
		}

		return $response;
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
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$workflow_id    = $event_data['workflow_id'];
		$response_array = array();

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		return $request;
	}

	/**
	 * Return the request data.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Make();
