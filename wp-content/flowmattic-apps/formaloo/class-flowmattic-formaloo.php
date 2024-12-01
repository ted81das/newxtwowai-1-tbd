<?php
/**
 * Application Name: Formaloo
 * Description: Add Formaloo integration to FlowMattic.
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
 * Formaloo integration class.
 *
 * @since 1.0
 */
class FlowMattic_Formaloo {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for formaloo.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'formaloo',
			array(
				'name'         => esc_attr__( 'Formaloo', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/formaloo/icon.jpg',
				'instructions' => __( 'Copy and enter the above webhook URL to your Formaloo webhook setting under Integrations & Webhooks for the Formaloo form.', 'flowmattic' ),
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
		wp_enqueue_script( 'flowmattic-app-view-formaloo', FLOWMATTIC_APP_URL . '/formaloo/view-formaloo.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'new_form_submission'    => array(
				'title'       => esc_attr__( 'New Form Submission', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new submission is received', 'flowmattic' ),
			),
			'new_successful_payment' => array(
				'title'       => esc_attr__( 'New Successful Payment', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new payment is successful', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Formaloo();
