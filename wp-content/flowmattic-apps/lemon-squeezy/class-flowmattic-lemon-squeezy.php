<?php
/**
 * Application Name: Lemon squeezy
 * Description: Add Lemon-squeezy integration to FlowMattic.
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
 * Lemon-squeezy integration class.
 *
 * @since 1.0
 */
class FlowMattic_Lemon_Squeezy {

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Lemon-squeezy.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'lemon_squeezy',
			array(
				'name'         => esc_attr__( 'Lemon Squeezy', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/lemon-squeezy/icon.svg',
				'instructions' => '',
				'triggers'     => $this->get_triggers(),
				'type'         => 'trigger',
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
		wp_enqueue_script( 'flowmattic-app-view-lemon-squeezy', FLOWMATTIC_APP_URL . '/lemon-squeezy/view-lemon-squeezy.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'license_key_created'            => array(
				'title'       => esc_attr__( 'License Key Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new license key is created', 'flowmattic' ),
			),
			'order_created'                  => array(
				'title'       => esc_attr__( 'Order Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new order is created', 'flowmattic' ),
			),
			'subscription_cancelled'         => array(
				'title'       => esc_attr__( 'Subscription Cancelled', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is cancelled', 'flowmattic' ),
			),
			'subscription_expired'           => array(
				'title'       => esc_attr__( 'Subscription Expired', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is expired', 'flowmattic' ),
			),
			'subscription_payment_failed'    => array(
				'title'       => esc_attr__( 'Subscription Payment Failed', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription payment has failed', 'flowmattic' ),
			),
			'subscription_payment_success'   => array(
				'title'       => esc_attr__( 'Subscription Payment Success', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription payment has succeeded', 'flowmattic' ),
			),
			'subscription_unpaused'         => array(
				'title'       => esc_attr__( 'Subscription Un-Paused', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is unpaused', 'flowmattic' ),
			),
			'license_key_updated'            => array(
				'title'       => esc_attr__( 'License Key Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a license key is updated', 'flowmattic' ),
			),
			'order_refunded'                 => array(
				'title'       => esc_attr__( 'Order Refunded', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when an order is refunded', 'flowmattic' ),
			),
			'subscription_created'           => array(
				'title'       => esc_attr__( 'Subscription Created', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new subscription is created', 'flowmattic' ),
			),
			'subscription_paused'            => array(
				'title'       => esc_attr__( 'Subscription Paused', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is paused', 'flowmattic' ),
			),
			'subscription_payment_recovered' => array(
				'title'       => esc_attr__( 'Subscription Payment Recovered', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription payment has recovered', 'flowmattic' ),
			),
			'subscription_resumed'           => array(
				'title'       => esc_attr__( 'Subscription Resumed', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is resumed', 'flowmattic' ),
			),
			'subscription_updated'           => array(
				'title'       => esc_attr__( 'Subscription Updated', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a subscription is updated', 'flowmattic' ),
			),
		);
	}

	/**
	 * Validate if the workflow should execute or not.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id   Workflow ID for the workflow being executed.
	 * @param array  $workflow_step Current step in the workflow being executed.
	 * @param array  $capture_data  Data received in the webhook.
	 * @return bool  Whether the workflow can be executed or not.
	 */
	public function validate_workflow_step( $workflow_id, $workflow_step, $capture_data ) {
		if ( isset( $capture_data['meta_event_name'] ) ) {
			$step_action     = $workflow_step['action'];
			$captured_action = strtolower( $capture_data['meta_event_name'] );

			return ( $step_action === $captured_action );
		} else {
			return true;
		}
	}
}

// Initantiate the new class.
new FlowMattic_Lemon_Squeezy();
