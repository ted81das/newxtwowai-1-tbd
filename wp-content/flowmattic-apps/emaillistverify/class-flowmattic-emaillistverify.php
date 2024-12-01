<?php
/**
 * Application Name: Emaillistverify
 * Description: Add Emaillistverify integration to FlowMattic.
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
 * Emaillistverify integration class.
 *
 * @since 1.0
 */
class FlowMattic_Emaillistverify {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for emaillistverify.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'emaillistverify',
			array(
				'name'         => esc_attr__( 'EmailListVerify', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/emaillistverify/icon.svg',
				'instructions' => esc_attr__( 'Enter your Emaillistverify account API Key. Your credentials are stored securely in your site.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'type'         => 'action',
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
		wp_enqueue_script( 'flowmattic-app-view-emaillistverify', FLOWMATTIC_APP_URL . '/emaillistverify/view-emaillistverify.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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

		switch ( $action ) {
			case 'verify_email':
				$fields   = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
				$response = $this->verify_email( $fields );

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
			'verify_email' => array(
				'title' => esc_attr__( 'Verify Single Email', 'flowmattic' ),
			),
		);
	}

	/**
	 * Verify email.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Email field.
	 * @return array
	 */
	public function verify_email( $fields ) {
		$api_key = $fields['api_key'];
		$email   = $fields['email'];

		// Initiliaze curl.
		// @codingStandardsIgnoreStart
		$url = 'https://apps.emaillistverify.com/api/verifyEmail?secret=' . $api_key . '&email=' . urlencode( $email ) . '&timeout=15';
		$ch  = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );

		$request = curl_exec( $ch );

		curl_close( $ch );
		// @codingStandardsIgnoreEnd

		$response_array = array(
			'response' => $request,
		);

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
		$event          = $event_data['event'];
		$workflow_id    = $event_data['workflow_id'];
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$response_array = array();

		switch ( $event ) {
			case 'verify_email':
				$request = $this->verify_email( $fields );

				break;
		}

		return $request;
	}
}

// Hide integration till the API issue fixed.
new FlowMattic_Emaillistverify();
