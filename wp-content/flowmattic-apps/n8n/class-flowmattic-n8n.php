<?php
/**
 * Application Name: N8n
 * Description: Add n8n integration to FlowMattic.
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
 * N8n integration class.
 *
 * @since 1.0
 */
class FlowMattic_N8n {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for n8n.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'n8n',
			array(
				'name'         => esc_attr__( 'n8n.io', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/n8n/icon.svg',
				'instructions' => __( 'Copy and enter the above webhook URL to your n8n.io webhook setting', 'flowmattic' ),
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
		wp_enqueue_script( 'flowmattic-app-view-n8n', FLOWMATTIC_APP_URL . '/n8n/view-n8n.js', array( 'flowmattic-workflow-utils' ), wp_rand(), true );
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

		$post_fields = $step['settings']['n8n_parameters'];

		$args = array(
			'headers' => array(
				'Accept'       => 'application/json',
				'Content-Type' => 'application/json',
			),
			'body'    => wp_json_encode( $post_fields ),
		);

		// Create a new contact.
		$request       = wp_remote_post( $fields['n8n_webhook'], $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response      = wp_remote_retrieve_body( $request );

		if ( 200 !== $response_code ) {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					// translators: error code.
					'message' => ( 404 === $response_code ) ? sprintf( __( 'Error: %s. Webhook URL not found. Make sure your n8n.io workflow is turned ON.', 'flowmattic' ), $response_code ) : $response_code,
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
}

new FlowMattic_N8n();
