<?php
/**
 * Application Name: Facebook Lead Ads
 * Description: Add Facebook Lead Ads integration to FlowMattic.
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
 * Facebook Lead Ads integration class.
 *
 * @since 1.0
 */
class FlowMattic_Facebook_Lead_Ads {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for Facebook Lead Ads.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'facebook_lead_ads',
			array(
				'name'         => esc_attr__( 'Facebook Lead Ads', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/facebook-lead-ads/icon.svg',
				'instructions' => '',
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'type'         => 'trigger',
				'version'      => '1.0',
			)
		);

		// Settings for OAuth authentication.
		$connect_settings = array(
			'name'           => 'Facebook Lead Ads',
			'fm_auth_type'   => 'oauth',
			'auth_api_addto' => 'header',
			'endpoint'       => 'https://api.flowmattic.com/facebook-lead-ads',
			'icon'           => FLOWMATTIC_APP_URL . '/facebook-lead-ads/icon.svg',
		);

		// Add the connect to the list.
		flowmattic_add_connect( 'facebook_lead_ads', $connect_settings );

		// Ajax to get Facebook pages list.
		add_action( 'wp_ajax_flowmattic_facebook_lead_ads_get_pages', array( $this, 'ajax_get_pages' ) );

		// Ajax to get Facebook lead forms list.
		add_action( 'wp_ajax_flowmattic_facebook_lead_ads_get_lead_forms', array( $this, 'ajax_get_lead_forms' ) );

		// Ajax to register the facebook lead ads webhook.
		add_action( 'wp_ajax_flowmattic_create_facebook_webhook', array( $this, 'ajax_register_webhook' ) );

		// Check if the webhook request is from Facebook.
		add_action( 'flowmattic_webhook_response_captured', array( $this, 'check_webhook_request' ), 10, 2 );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-facebook-lead-ads', FLOWMATTIC_APP_URL . '/facebook-lead-ads/view-facebook-lead-ads.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'new_lead' => array(
				'title'       => esc_attr__( 'New Lead', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new lead is created', 'flowmattic' ),
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
			'retrieve_leads' => array(
				'title'       => esc_attr__( 'Retrieve Leads', 'flowmattic' ),
				'description' => esc_attr__( 'Retrieve the leads for specific lead form', 'flowmattic' ),
			),
		);
	}

	/**
	 * Ajax to get list of pages.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function ajax_get_pages() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$pages = array();

		// Get connect ID.
		$connect_id = isset( $_POST['connect_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_id'] ) ) : '';

		if ( '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$connect_data = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Please select the connect account.', 'flowmattic' ),
				)
			);
		}

		if ( '' !== $connect_data ) {
			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $connect_data,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
			);

			// Send request to retrieve the page ID.
			$request       = wp_remote_get( 'https://graph.facebook.com/v18.0/me/accounts', $args );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );
			$pages         = json_decode( $request_body, true );
			$response_data = $request_body;

			if ( 200 !== $response_code && 201 !== $response_code ) {
				$response_data = wp_json_encode(
					array(
						'status'   => 'error',
						'response' => $request_body,
					)
				);
			}
		}

		echo $response_data;
		die();
	}

	/**
	 * Ajax to get list of lead forms.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function ajax_get_lead_forms() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$forms = array();

		// Get connect ID.
		$connect_id = isset( $_POST['connect_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_id'] ) ) : '';
		$page_id    = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';

		if ( '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$connect_data = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Please select the connect account.', 'flowmattic' ),
				)
			);
		}

		if ( '' !== $connect_data ) {
			// Get the page access token.
			$request       = wp_remote_get( 'https://graph.facebook.com/v18.0/me/accounts?access_token=' . $connect_data );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );
			$page_data     = json_decode( $request_body, true );

			// Get the access token.
			$page_access_token = '';

			foreach ( $page_data['data'] as $page ) {
				if ( $page['id'] === $page_id ) {
					$page_access_token = $page['access_token'];
				}
			}

			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $page_access_token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
			);

			if ( '' !== $page_id ) {
				$args['headers']['Authorization'] = 'Bearer ' . $page_access_token;

				// Send request to retrieve the forms.
				$request       = wp_remote_get( 'https://graph.facebook.com/v18.0/' . $page_id . '/leadgen_forms', $args );
				$response_code = wp_remote_retrieve_response_code( $request );
				$request_body  = wp_remote_retrieve_body( $request );
				$forms_array   = json_decode( $request_body, true );
				$forms_data    = $forms_array['data'];

				// Get the active forms.
				$active_forms = array();

				foreach ( $forms_data as $form ) {
					if ( 'ACTIVE' === $form['status'] ) {
						$active_forms[] = $form;
					}
				}

				$response_data = wp_json_encode(
					array(
						'status' => 'success',
						'forms'  => $active_forms,
					)
				);
			} else {
				$response_data = wp_json_encode(
					array(
						'status'  => 'error',
						'message' => 'Invalid page ID',
					)
				);
			}
		}

		echo $response_data;

		die();
	}

	/**
	 * Ajax to register the facebook lead ads webhook.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function ajax_register_webhook() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get connect ID.
		$connect_id  = isset( $_POST['connect_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_id'] ) ) : '';
		$page_id     = isset( $_POST['page_id'] ) ? sanitize_text_field( wp_unslash( $_POST['page_id'] ) ) : '';
		$form_id     = isset( $_POST['form_id'] ) ? sanitize_text_field( wp_unslash( $_POST['form_id'] ) ) : '';
		$webhook_url = isset( $_POST['webhook_url'] ) ? sanitize_text_field( wp_unslash( $_POST['webhook_url'] ) ) : '';

		if ( '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$connect_data = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => esc_html__( 'Please select the connect account.', 'flowmattic' ),
				)
			);
		}

		if ( '' !== $connect_data ) {
			// Get the page access token.
			$request       = wp_remote_get( 'https://graph.facebook.com/v18.0/me/accounts?access_token=' . $connect_data );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );
			$page_data     = json_decode( $request_body, true );

			// Get the access token.
			$page_access_token = '';

			foreach ( $page_data['data'] as $page ) {
				if ( $page['id'] === $page_id ) {
					$page_access_token = $page['access_token'];
				}
			}

			// Install the app on page.
			$install_request  = wp_remote_post( 'https://graph.facebook.com/v18.0/' . $page_id . '/subscribed_apps?subscribed_fields=leadgen&access_token=' . $page_access_token );
			$install_response = wp_remote_retrieve_body( $install_request );

			$args = array(
				'headers'     => array(
					'Authorization' => 'Bearer ' . $page_access_token,
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout'     => 60,
				'sslverify'   => false,
				'data_format' => 'body',
				'body'        => wp_json_encode(
					array(
						'webhook_url'  => $webhook_url,
						'page_id'      => $page_id,
						'form_id'      => $form_id,
						'access_token' => $connect_data,
					),
				),
			);

			// Send request to retrieve the forms.
			$request       = wp_remote_post( 'https://api.flowmattic.com/facebook-lead-ads/subscribe.php', $args );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );
			$response_data = $request_body;
		}

		echo $response_data;

		die();
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
		// If not trigger, skip validation and return true.
		if ( 'trigger' !== $workflow_step['type'] ) {
			return true;
		}

		if ( isset( $capture_data['form_id'] ) ) {
			$step_action     = $workflow_step['form_id'];
			$captured_action = $capture_data['form_id'];

			if ( $step_action === $captured_action ) {
				return true;
			} else {
				return false;
			}
		} elseif ( isset( $capture_data['hub_challenge'] ) ) {
			$response = $capture_data['hub_challenge'];
			die( $response );
		}
	}

	/**
	 * Check if the webhook request is from Facebook.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $webhook_id   Webhook ID.
	 * @param array  $capture_data Data received in the webhook.
	 * @return void
	 */
	public function check_webhook_request( $webhook_id, $capture_data ) {
		if ( isset( $capture_data['entry_0_changes'] ) && ( isset( $capture_data['object'] ) && 'page' === $capture_data['object'] ) ) {
			$captured_entry = is_array( $capture_data['entry_0_changes'] ) ? $capture_data['entry_0_changes'] : json_decode( $capture_data['entry_0_changes'], true );
			$captured_lead  = $captured_entry[0]['value'];

			$args = array(
				'workflow_id' => $webhook_id,
			);

			$workflow = wp_flowmattic()->workflows_db->get( $args );
			$steps    = $workflow->workflow_steps;
			$steps    = json_decode( $steps, true );

			// Get the trigger step.
			$workflow_step = $steps[0];

			$lead_id     = $captured_lead['leadgen_id'];
			$connect_id  = $workflow_step['connect_id'];
			$step_action = $workflow_step['form_id'];

			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the connect data.
			$connect_data = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

			$args = array(
				'headers'   => array(
					'Authorization' => 'Bearer ' . $connect_data,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout'   => 60,
				'sslverify' => false,
			);

			// Get lead data.
			$request       = wp_remote_get( 'https://graph.facebook.com/' . $lead_id, $args );
			$response_code = wp_remote_retrieve_response_code( $request );
			$request_body  = wp_remote_retrieve_body( $request );
			$lead_data     = json_decode( $request_body, true );

			$response_array = array(
				'leadgen_id'   => $captured_lead['leadgen_id'],
				'page_id'      => $captured_lead['page_id'],
				'form_id'      => $captured_lead['form_id'],
				'adgroup_id'   => $captured_lead['adgroup_id'],
				'ad_id'        => $captured_lead['ad_id'],
				'created_time' => $captured_lead['created_time'],
				'created_at'   => date_i18n( 'Y-m-d H:i:s', $captured_lead['created_time'] ),
			);

			$lead_field_data = $lead_data['field_data'];

			foreach ( $lead_field_data as $data_key => $field ) {
				// Normalize the field name.
				$field_name = strtolower( str_replace( ' ', '_', trim( $field['name'] ) ) );
				$field_name = preg_replace( '/[^A-Za-z0-9\-\_]/', '', trim( $field_name ) );
				$field_name = preg_replace( '/-+/', '_', $field_name );

				$response_array[ 'field_' . $field_name ] = $field['values'][0];
			}

			// Check if is capturing.
			$is_capturing = ( get_option( 'webhook-capture-live', false ) === $webhook_id );

			// If capturing, save the response.
			if ( $is_capturing ) {
				update_option( 'webhook-capture-' . $webhook_id, $response_array, false );
				delete_option( 'webhook-capture-live' );
				delete_option( 'webhook-capture-application' );
			}

			// Run the workflow.
			$flowmattic_workflow = new FlowMattic_Workflow();
			$flowmattic_workflow->run( $webhook_id, $response_array );

			$response = array(
				'status'  => 'success',
				'message' => 'Response Captured',
			);

			// Send response.
			header( 'Content-Type: application/json' );
			header( 'User-Agent: FlowMattic/' . FLOWMATTIC_VERSION );
			die( wp_json_encode( $response ) );
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

		switch ( $action ) {
			case 'retrieve_leads':
				$fields   = isset( $step['fields'] ) ? $step['fields'] : ( isset( $step['actionAppArgs'] ) ? $step['actionAppArgs'] : array() );
				$response = $this->retrieve_leads( $fields );

				break;
		}

		return $response;
	}

	/**
	 * Retrieve leads.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Fields data.
	 * @return array
	 */
	public function retrieve_leads( $fields ) {
		$connect_id = isset( $fields['connect_id'] ) ? $fields['connect_id'] : '';
		$page_id    = isset( $fields['page_id'] ) ? $fields['page_id'] : '';
		$form_id    = isset( $fields['form_id'] ) ? $fields['form_id'] : '';
		$limit      = isset( $fields['lead_limit'] ) ? $fields['lead_limit'] : 100;

		// Get the connect data.
		$connect_args = array(
			'connect_id' => $connect_id,
		);

		// Get the connect data from db.
		$connect = wp_flowmattic()->connects_db->get( $connect_args );

		// Get the connect data.
		$connect_data = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

		$args = array(
			'headers'     => array(
				'Authorization' => 'Bearer ' . $connect_data,
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
		);

		// Send request to retrieve the forms.
		$request       = wp_remote_get( 'https://graph.facebook.com/v18.0/' . $form_id . '/leads?limit=' . $limit, $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$request_body  = wp_remote_retrieve_body( $request );
		$leads_array   = json_decode( $request_body, true );
		$leads_data    = $leads_array['data'];

		if ( ! empty( $leads_data ) ) {
			$response_data = wp_json_encode(
				array(
					'status' => 'success',
					'leads'  => wp_json_encode( $leads_data ),
				)
			);
		} else {
			$response_data = wp_json_encode(
				array(
					'status'  => 'error',
					'message' => 'No leads found',
				)
			);
		}

		return $response_data;
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

new FlowMattic_Facebook_Lead_Ads();
