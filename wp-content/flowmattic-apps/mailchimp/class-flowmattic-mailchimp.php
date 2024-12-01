<?php
/**
 * Application Name: Mailchimp
 * Description: Add Mailchimp integration to FlowMattic.
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
 * Mailchimp integration class.
 *
 * @since 1.0
 */
class FlowMattic_Mailchimp {
	/**
	 * Workflow ID.
	 *
	 * @access public
	 * @since 1.0
	 * @var string
	 */
	public $workflow_id = '';

	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for mailchimp.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'mailchimp',
			array(
				'name'         => esc_attr__( 'Mailchimp', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/mailchimp/icon.svg',
				'instructions' => __(
					'<ul style="list-style: square;"><li>Log into Mailchimp, and then navigate to the <strong>Audience</strong> page.</li>
					<li>Select the drop-down menu next to the audience you want to work with, and then choose Settings.</li>
					<li>On the <strong>Settings</strong> page, click <strong>Webhooks</strong>.</li>
					<li>Click <strong>Create New Webhook</strong>.</li>
					<li>Copy the webhook link above and paste it under <strong>Callback URL</strong>&nbsp;field.</li>
					<li>Select the <strong class="mailchimp-event-selected"></strong> check box and all the 3 options under <strong>Only send updates when a change is made</strong>.</li>
					<li>Click <strong>Save</strong>.</li></ul>',
					'flowmattic'
				),
				'actions'      => $this->get_actions(),
				'triggers'     => $this->get_triggers(),
				'type'         => 'trigger,action',
				'version'      => '1.1.0',
			)
		);

		// Ajax to refresh the mailchimp lists.
		add_action( 'wp_ajax_flowmattic_refresh_mailchimp_lists', array( $this, 'refresh_mailchimp_lists' ) );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-mailchimp', FLOWMATTIC_APP_URL . '/mailchimp/view-mailchimp.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Get mailchimp authentication data.
	 *
	 * @access public
	 * @since 1.0
	 * @return array
	 */
	public function get_auth_data() {
		// Get current workflow ID.
		$workflow_id = $this->workflow_id;

		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'mailchimp', $workflow_id );
		$auth_data           = $authentication_data['auth_data'];

		return $auth_data;
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

		// Set current workflow ID.
		$this->workflow_id = $workflow_id;

		$step   = (array) $step;
		$action = $step['action'];

		switch ( $action ) {
			case 'new_member':
				$args                     = $step['mailchimpArgs'];
				$args['audience_list_id'] = $step['audienceList'];

				$response = $this->add_new_member( $args );

				break;

			case 'new_member_tag':
				$args                     = $step['mailchimpArgs'];
				$args['audience_list_id'] = $step['audienceList'];

				$response = $this->add_new_member_tag( $args );

				break;

			case 'remove_member_tag':
				$args                     = $step['mailchimpArgs'];
				$args['audience_list_id'] = $step['audienceList'];

				$response = $this->remove_member_tag( $args );

				break;

			case 'new_member_note':
				$args                     = $step['mailchimpArgs'];
				$args['audience_list_id'] = $step['audienceList'];

				$response = $this->add_new_member_note( $args );

				break;

			case 'delete_list_member':
				$args                     = $step['mailchimpArgs'];
				$args['audience_list_id'] = $step['audienceList'];

				$response = $this->delete_list_member( $args );

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
			'new_member'         => array(
				'title'       => esc_attr__( 'Add New Member', 'flowmattic' ),
				'description' => esc_attr__( 'Add a new member to the list', 'flowmattic' ),
			),
			'new_member_tag'     => array(
				'title'       => esc_attr__( 'Add Member Tag', 'flowmattic' ),
				'description' => esc_attr__( 'Add a new tag to the existing member. New tags will be added if they are not exist.', 'flowmattic' ),
			),
			'remove_member_tag'  => array(
				'title'       => esc_attr__( 'Remove Member Tag', 'flowmattic' ),
				'description' => esc_attr__( 'Remove a tag from the list member.', 'flowmattic' ),
			),
			'new_member_note'    => array(
				'title'       => esc_attr__( 'Add Member Note', 'flowmattic' ),
				'description' => esc_attr__( 'Add a new note to the existing member.', 'flowmattic' ),
			),
			'delete_list_member' => array(
				'title'       => esc_attr__( 'Delete List Member', 'flowmattic' ),
				'description' => esc_attr__( 'Remove a member from the audience list.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 1.1.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'new_subscriber'        => array(
				'title'       => esc_attr__( 'New Subscriber', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new subscriber is added.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Subscribes', 'flowmattic' ),
			),
			'new_unsubscriber'      => array(
				'title'       => esc_attr__( 'New Unsubscriber', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when any current subscriber unsubscribes.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Unsubscribes', 'flowmattic' ),
			),
			'profile_updates'       => array(
				'title'       => esc_attr__( 'Profile Updates', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a profile updated.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Profile updates', 'flowmattic' ),
			),
			'cleaned_emails'        => array(
				'title'       => esc_attr__( 'Cleaned Emails', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when emails cleaned.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Email changed', 'flowmattic' ),
			),
			'email_address_changes' => array(
				'title'       => esc_attr__( 'Email Address Changes', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when email address changes.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Cleaned address', 'flowmattic' ),
			),
			'new_campaign'          => array(
				'title'       => esc_attr__( 'New Campaign', 'flowmattic' ),
				'description' => esc_attr__( 'Triggers when a new campaign is created or sent.', 'flowmattic' ),
				'trigger'     => esc_attr__( 'Campaign sending', 'flowmattic' ),
			),
		);
	}

	/**
	 * Create a new member.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Customer fields.
	 * @return array
	 */
	public function add_new_member( $fields ) {
		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			// Get the list id from fields array and unset it.
			$list_id = $fields['audience_list_id'];
			unset( $fields['audience_list_id'] );

			$fields['merge_fields'] = array(
				'FNAME' => $fields['first_name'],
				'LNAME' => $fields['last_name'],
			);

			$args = array(
				'headers' => array(
					'Authorization' => 'user: ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $fields ),
				'method'  => 'PUT',
			);

			$member_id = md5( strtolower( $fields['email_address'] ) );
			$api_url   = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id;

			// Create a new member.
			$request = wp_remote_request( $api_url, $args );
			$request = wp_remote_retrieve_body( $request );

			return $request;
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Delete a member from the list.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Customer fields.
	 * @return array
	 */
	public function delete_list_member( $fields ) {
		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			// Get the list id from fields array and unset it.
			$list_id = $fields['audience_list_id'];
			unset( $fields['audience_list_id'] );

			$args = array(
				'headers' => array(
					'Authorization' => 'user: ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			);

			$member_id = md5( strtolower( $fields['member'] ) );
			$api_url   = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id . '/actions/delete-permanent';

			// Create a new member.
			$request = wp_remote_post( $api_url, $args );
			$request = wp_remote_retrieve_body( $request );

			if ( '' === $request ) {
				// If there's no response, return default message.
				return wp_json_encode(
					array(
						'status'   => esc_attr__( 'Success', 'flowmattic' ),
						'response' => esc_attr__( 'Request has no response data', 'flowmattic' ),
					)
				);
			} else {
				return $request;
			}
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Create a new member tag.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Customer fields.
	 * @return array
	 */
	public function add_new_member_tag( $fields ) {
		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			// Get the list id from fields array and unset it.
			$list_id = $fields['audience_list_id'];
			unset( $fields['audience_list_id'] );

			$fields['is_syncing'] = ( 'Yes' === $fields['is_syncing'] ) ? true : false;
			$tags                 = explode( ',', $fields['tags'] );
			$processed_tags       = array();

			foreach ( $tags as $key => $tag ) {
				$processed_tags[ $key ]['name']   = trim( $tag );
				$processed_tags[ $key ]['status'] = 'active';
			}

			$fields['tags'] = $processed_tags;

			$member_id = md5( strtolower( $fields['member'] ) );
			unset( $fields['member'] );

			$args = array(
				'headers' => array(
					'Authorization' => 'user: ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $fields ),
			);

			$api_url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id . '/tags';

			// Create a new member tag.
			$request = wp_remote_post( $api_url, $args );
			$request = wp_remote_retrieve_body( $request );

			if ( '' === $request ) {
				// If there's no response, return default message.
				return wp_json_encode(
					array(
						'status'   => esc_attr__( 'Success', 'flowmattic' ),
						'response' => esc_attr__( 'Request has no response data', 'flowmattic' ),
					)
				);
			} else {
				return $request;
			}
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Remove member tag.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Customer fields.
	 * @return array
	 */
	public function remove_member_tag( $fields ) {
		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			// Get the list id from fields array and unset it.
			$list_id = $fields['audience_list_id'];
			unset( $fields['audience_list_id'] );

			$fields['is_syncing'] = ( 'Yes' === $fields['is_syncing'] ) ? true : false;
			$tags                 = explode( ',', $fields['tags'] );
			$processed_tags       = array();

			foreach ( $tags as $key => $tag ) {
				$processed_tags[ $key ]['name']   = trim( $tag );
				$processed_tags[ $key ]['status'] = 'inactive';
			}

			$fields['tags'] = $processed_tags;

			$member_id = md5( strtolower( $fields['member'] ) );
			unset( $fields['member'] );

			$args = array(
				'headers' => array(
					'Authorization' => 'user: ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $fields ),
			);

			$api_url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id . '/tags';

			// Create a new member tag.
			$request = wp_remote_post( $api_url, $args );
			$request = wp_remote_retrieve_body( $request );

			if ( '' === $request ) {
				// If there's no response, return default message.
				return wp_json_encode(
					array(
						'status'   => esc_attr__( 'Success', 'flowmattic' ),
						'response' => esc_attr__( 'Request has no response data', 'flowmattic' ),
					)
				);
			} else {
				return $request;
			}
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Create a new member note.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Customer fields.
	 * @return array
	 */
	public function add_new_member_note( $fields ) {
		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			// Get the list id from fields array and unset it.
			$list_id = $fields['audience_list_id'];
			unset( $fields['audience_list_id'] );

			$member_id = md5( strtolower( $fields['member'] ) );
			unset( $fields['member'] );

			$args = array(
				'headers' => array(
					'Authorization' => 'user: ' . $access_token,
					'Content-Type'  => 'application/json',
				),
				'body'    => wp_json_encode( $fields ),
			);

			$api_url = 'https://' . $data_center . '.api.mailchimp.com/3.0/lists/' . $list_id . '/members/' . $member_id . '/notes';

			// Create a new member tag.
			$request = wp_remote_post( $api_url, $args );
			$request = wp_remote_retrieve_body( $request );

			return $request;
		} else {
			return wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}
	}

	/**
	 * Ajax to test Mailchimp connection.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function refresh_mailchimp_lists() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Set current workflow ID.
		$this->workflow_id = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';

		$auth_data = $this->get_auth_data();

		if ( isset( $auth_data['access_token'] ) ) {
			$access_token = $auth_data['access_token'];
			$data_center  = $auth_data['data_center'];

			$args = array(
				'headers' => array(
					'Authorization' => 'OAuth ' . $access_token,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 20,
			);

			// Get all available spreadsheets.
			$request         = wp_remote_get( 'https://' . $data_center . '.api.mailchimp.com/3.0/lists', $args );
			$request         = wp_remote_retrieve_body( $request );
			$mailchimp_lists = json_decode( $request, true );

			set_transient( 'flowmattic-mailchimp-lists', $mailchimp_lists, HOUR_IN_SECONDS * 8 );

			echo wp_json_encode( $mailchimp_lists );
		} else {
			echo wp_json_encode(
				array(
					'status'  => esc_attr__( 'Error', 'flowmattic' ),
					'message' => esc_html__( 'Invalid access token. Please reconnect your mailchimp account.', 'flowmattic' ),
				)
			);
		}

		die();
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
		$step_ids       = $event_data['stepIDs'];
		$list_id        = isset( $event_data['settings']['audienceList'] ) ? $event_data['settings']['audienceList'] : '';
		$fields         = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );
		$response_array = array();

		// Set current workflow ID.
		$this->workflow_id = $workflow_id;

		// Add list ID to fields array.
		$fields['audience_list_id'] = $list_id;

		switch ( $event ) {
			case 'new_member':
				$request = $this->add_new_member( $fields );

				break;

			case 'new_member_tag':
				$request = $this->add_new_member_tag( $fields );

				break;

			case 'remove_member_tag':
				$request = $this->remove_member_tag( $fields );

				break;

			case 'new_member_note':
				$request = $this->add_new_member_note( $fields );

				break;

			case 'delete_list_member':
				$request = $this->delete_list_member( $fields );

				break;
		}

		$request_decode = json_decode( $request, true );

		foreach ( $request_decode as $key => $value ) {
			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		return wp_json_encode( $response_array );
	}
}

// Hide integration till the API issue fixed.
new FlowMattic_Mailchimp();
