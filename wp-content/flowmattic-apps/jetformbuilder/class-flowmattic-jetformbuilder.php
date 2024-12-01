<?php
/**
 * Application Name: JetFormBuilder
 * Description: Add JetFormBuilder integration to FlowMattic.
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
 * JetFormBuilder integration class.
 *
 * @since 1.0
 */
class FlowMattic_Jetformbuilder {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for jetformbuilder.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'jetformbuilder',
			array(
				'name'         => esc_attr__( 'JetFormBuilder', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/jetformbuilder/icon.svg',
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
		wp_enqueue_script( 'flowmattic-app-view-jetformbuilder', FLOWMATTIC_APP_URL . '/jetformbuilder/view-jetformbuilder.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'form_submission' => array(
				'title'       => esc_attr__( 'Form Submission', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a form is submitted', 'flowmattic' ),
			),
		);
	}
}

new FlowMattic_Jetformbuilder();
