<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FlowMattic_Jotform {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for jotform.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'jotform',
			array(
				'name'         => esc_attr__( 'Jotform', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/jotform/icon.svg',
				'instructions' => __( 'Copy and enter the above webhook URL to your Jotform form webhook setting', 'flowmattic' ),
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
		wp_enqueue_script( 'flowmattic-app-view-jotform', FLOWMATTIC_APP_URL . '/jotform/view-jotform.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_triggers() {
		return array(
			'form_submission' => array(
				'title' => esc_attr__( 'New Form Submission', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Jotform();
