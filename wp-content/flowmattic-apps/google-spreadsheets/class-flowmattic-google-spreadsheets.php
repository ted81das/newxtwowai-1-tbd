<?php
/**
 * Application Name: Google Sheets
 * Description: Add Google Sheets integration to FlowMattic.
 * Version: 2.0.2
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
 * Google Sheets integration class.
 *
 * @since 1.0
 */
class FlowMattic_Google_Spreadsheets {
	/**
	 * Request body.
	 *
	 * @access public
	 * @since 2.0
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
		// Enqueue custom view for google-spreadsheets.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'google_spreadsheets',
			array(
				'name'         => esc_attr__( 'Google Sheets', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/google-spreadsheets/icon.svg',
				'instructions' => esc_attr__( 'Connect your Google Sheets account. Your credentials are stored securely in your site.', 'flowmattic' ),
				'connect_note' => esc_attr__( 'Your Google Sheets account is already connected. To re-connect Google Sheets, click the button above. Your credentials are stored securely in your site.', 'flowmattic' ),
				'triggers'     => $this->get_triggers(),
				'actions'      => $this->get_actions(),
				'type'         => 'trigger,action',
				'version'      => '2.0.2',
			)
		);

		$connect_settings = array(
			'name'           => 'Google Sheets',
			'fm_auth_type'   => 'oauth',
			'auth_api_addto' => 'header',
			'auth_name'      => 'Bearer',
			'endpoint'       => 'https://api.flowmattic.com/google/sheets',
			'icon'           => FLOWMATTIC_APP_URL . '/google-spreadsheets/icon.svg',
			// 'custom'         => true, // For development purpose.
		);

		// // Add the connect to the list.
		flowmattic_add_connect( 'google_spreadsheets', $connect_settings );

		// Register cron for refreshing the access token.
		add_action( 'admin_init', array( $this, 'refresh_token' ), 1, 2 );
		add_action( 'refresh_google_access_token', array( $this, 'refresh_token' ), 1, 2 );

		// Ajax to refresh spreadsheets list.
		add_action( 'wp_ajax_refresh_spreadsheets', array( $this, 'refresh_spreadsheets' ) );

		// Ajax to refresh spreadsheets sheets.
		add_action( 'wp_ajax_refresh_spreadsheets_sheets', array( $this, 'refresh_spreadsheets_sheets' ) );

		// Ajax to fetch the given URL.
		add_action( 'wp_ajax_flowmattic_fetch_sheet_url', array( $this, 'fetch_sheet_url_ajax' ) );

		// Subscribe to webhook.
		add_action( 'wp_ajax_flowmattic_subscribe_google_sheet', array( $this, 'subscribe_webhook' ) );

		// Ajax to test event actions.
		add_action( 'wp_ajax_flowmattic_test_spreadsheet_action_event', array( $this, 'test_event_action' ) );

		// Perform cleanup on workflow deletion.
		add_action( 'flowmattic_workflow_deleted', array( $this, 'remove_crons_after_workflow_delete' ) );

		// Validate and expand the webhook object.
		add_filter( 'flowmattic_webhook_captured_data', array( $this, 'expand_webhook_object' ), 10, 2 );

		// Register cron to renew webhook expiration.
		add_action( 'flowmattic_renew_google_sheet_webhook', array( $this, 'renew_webhook_expiration_cron' ), 10, 5 );
	}

	/**
	 * Set triggers.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_triggers() {
		return array(
			'new_row'                     => array(
				'title'       => esc_attr__( 'New Spreadsheet Row', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a new row is added to the spreadsheet', 'flowmattic' ),
			),
			'update_row'                  => array(
				'title'       => esc_attr__( 'New Or Updated Spreadsheet Row', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a row is updated in the spreadsheet', 'flowmattic' ),
			),
			'delete_row'                  => array(
				'title'       => esc_attr__( 'Deleted Spreadsheet Row', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a row is deleted from the spreadsheet', 'flowmattic' ),
			),
			'update_cell_data'            => array(
				'title'       => esc_attr__( 'Update Cell Data', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when any cell is updated in the spreadsheet', 'flowmattic' ),
			),
			'update_specific_cell_data'   => array(
				'title'       => esc_attr__( 'Update Specific Cell Data', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a specific cell is updated in the spreadsheet', 'flowmattic' ),
			),
			'update_specific_column_data' => array(
				'title'       => esc_attr__( 'Update Specific Column Value', 'flowmattic' ),
				'description' => esc_attr__( 'Trigger when a specific column value is updated in the spreadsheet', 'flowmattic' ),
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
			'new_row'          => array(
				'title'       => esc_attr__( 'Add New Row', 'flowmattic' ),
				'description' => esc_attr__( 'Add a new row with mapped data', 'flowmattic' ),
			),
			'update_row'       => array(
				'title'       => esc_attr__( 'Update Row', 'flowmattic' ),
				'description' => esc_attr__( 'Update existing row in specific spreadsheet with mapped data', 'flowmattic' ),
			),
			'delete_row'       => array(
				'title'       => esc_attr__( 'Delete Row', 'flowmattic' ),
				'description' => esc_attr__( 'Delete row by ID in specific spreadsheet', 'flowmattic' ),
			),
			'clear_row'        => array(
				'title'       => esc_attr__( 'Clear Row Data', 'flowmattic' ),
				'description' => esc_attr__( 'Clear data or row by ID in specific spreadsheet', 'flowmattic' ),
			),
			'lookup_row'       => array(
				'title'       => esc_attr__( 'Lookup Row Data', 'flowmattic' ),
				'description' => esc_attr__( 'Search for data in specific spreadsheet', 'flowmattic' ),
			),
			'get_cell_data'    => array(
				'title'       => esc_attr__( 'Get Cell Data', 'flowmattic' ),
				'description' => esc_attr__( 'Get data for particular cell in specific spreadsheet', 'flowmattic' ),
			),
			'update_cell_data' => array(
				'title'       => esc_attr__( 'Update Cell Data', 'flowmattic' ),
				'description' => esc_attr__( 'Update data for particular cell in specific spreadsheet', 'flowmattic' ),
			),
			'get_row_count'    => array(
				'title'       => esc_attr__( 'Get Row Count', 'flowmattic' ),
				'description' => esc_attr__( 'Get total number of rows in specific spreadsheet', 'flowmattic' ),
			),
			'get_row_data'     => array(
				'title'       => esc_attr__( 'Get Row Data', 'flowmattic' ),
				'description' => esc_attr__( 'Get rows data from the sheet based on specified range', 'flowmattic' ),
			),
			'create_column'    => array(
				'title'       => esc_attr__( 'Create Spreadsheet Column', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new column in specific spreadsheet', 'flowmattic' ),
			),
			'new_sheet'        => array(
				'title'       => esc_attr__( 'Create New Sheet', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new sheet in specific spreadsheet', 'flowmattic' ),
			),
			'import_csv_json'  => array(
				'title'       => esc_attr__( 'Import CSV or JSON to Existing Sheet', 'flowmattic' ),
				'description' => esc_attr__( 'Import CSV or JSON data to existing sheet in specific spreadsheet', 'flowmattic' ),
			),
			'copy_sheet'       => array(
				'title'       => esc_attr__( 'Copy Sheet', 'flowmattic' ),
				'description' => esc_attr__( 'Copy a sheet to another or within the same spreadsheet', 'flowmattic' ),
			),
		);
	}

	/**
	 * Add cron for refresh token.
	 *
	 * @access public
	 * @since 1.0.4
	 * @return void
	 */
	public function remove_old_refresh_token_cron() {
		$next_scheduled     = wp_next_scheduled( 'refresh_google_access_token', array( 'refresh_token' ) );
		$next_scheduled_old = wp_next_scheduled( 'refresh_google_access_token' );

		if ( $next_scheduled_old ) {
			wp_unschedule_event( $next_scheduled_old, 'refresh_google_access_token' );
		}

		if ( $next_scheduled ) {
			wp_unschedule_event( $next_scheduled, 'refresh_google_access_token', array( 'refresh_token' ) );
		}
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-google-spreadsheets', FLOWMATTIC_APP_URL . '/google-spreadsheets/view-google-spreadsheets.js', array( 'flowmattic-workflow' ), wp_rand(), true );
	}

	/**
	 * Get the Access Token.
	 *
	 * @access public
	 * @since 2.0
	 * @param array  $fields      Action step data.
	 * @param string $workflow_id Workflow ID.
	 * @return array
	 */
	public function get_access_token( $fields, $workflow_id = '' ) {
		$connect_id   = ( isset( $fields['connect_id'] ) && '' !== $fields['connect_id'] ) ? $fields['connect_id'] : '';
		$auth_type    = isset( $fields['authType'] ) ? $fields['authType'] : 'traditional';
		$access_token = '';

		// If workflow ID is blank, auth type is connect.
		if ( '' === $workflow_id ) {
			$auth_type = 'connect';
		}

		if ( 'connect' === $auth_type && '' !== $connect_id ) {
			// Get the connect data.
			$connect_args = array(
				'connect_id' => $connect_id,
			);

			// Get the connect data from db.
			$connect = wp_flowmattic()->connects_db->get( $connect_args );

			// Get the access token.
			$access_token = isset( $connect->connect_data['access_token'] ) ? $connect->connect_data['access_token'] : '';

			return $access_token;
		} else {
			$authentication_data = flowmattic_get_auth_data( 'google_spreadsheets', $workflow_id );
			$authentication_data = isset( $authentication_data['auth_data'] ) ? $authentication_data['auth_data'] : array();

			if ( isset( $authentication_data['access_token'] ) ) {
				$access_token = $authentication_data['access_token'];
			}

			return $access_token;
		}
	}

	/**
	 * Subscribe to webhook.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function subscribe_webhook() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		$workflow_id    = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
		$connect_id     = isset( $_POST['connect_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_id'] ) ) : '';
		$spreadsheet_id = isset( $_POST['spreadsheetID'] ) ? sanitize_text_field( wp_unslash( $_POST['spreadsheetID'] ) ) : '';
		$sheet_id       = isset( $_POST['sheetID'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetID'] ) ) : '';
		$access_token   = $this->get_access_token( $_POST );

		$webhook_url = FlowMattic_Webhook::get_url( $workflow_id );
		$webhook_url = str_replace( 'flowmattic.local', '6664-103-126-69-108.ngrok-free.app', $webhook_url );

		$webhook_data = array(
			'workflow_id'    => $workflow_id,
			'webhook_url'    => $webhook_url,
			'connect_id'     => $connect_id,
			'spreadsheet_id' => $spreadsheet_id,
			'access_token'   => $access_token,
			'sheet_id'       => $sheet_id,
		);

		$args = array(
			'body'    => wp_json_encode( $webhook_data ),
			'timeout' => 20,
			'headers' => array(
				'Content-Type' => 'application/json',
				'User-Agent'   => 'FlowMattic',
			),
		);

		$webhook_request = wp_remote_post( 'https://api.flowmattic.com/google/sheets/webhook.php?action=subscribe', $args );
		$request_body    = wp_remote_retrieve_body( $webhook_request );
		$request         = json_decode( $request_body, true );

		if ( isset( $request['error'] ) ) {
			$ajax_response = array(
				'status'  => 'error',
				'message' => $request['error']['message'],
			);
		} else {
			$ajax_response = array(
				'status'  => 'success',
				'message' => esc_attr__( 'Webhook subscribed successfully.', 'flowmattic' ),
			);

			// Get the resource ID.
			$resource_id = isset( $request['resourceId'] ) ? $request['resourceId'] : '';

			// Get the channel expiration.
			$channel_expiration = isset( $request['expiration'] ) ? $request['expiration'] : '';

			if ( '' !== $resource_id ) {
				$cron_args = array(
					'workflow_id'    => $workflow_id,
					'connect_id'     => $connect_id,
					'resource_id'    => $resource_id,
					'spreadsheet_id' => $spreadsheet_id,
					'expires'        => is_numeric( $channel_expiration ) ? gmdate( 'D, d M Y H:i:s', ( $channel_expiration / 1000 ) ) . ' GMT' : $channel_expiration,
				);

				$cron_id = 'flowmattic_renew_google_sheet_webhook';

				// Check if cron already scheduled.
				$next_scheduled = wp_next_scheduled( $cron_id, $cron_args );

				if ( $next_scheduled ) {
					// Unschedule the cron.
					wp_unschedule_event( $next_scheduled, $cron_id, $cron_args );
				}

				if ( '' !== $cron_args['expires'] ) {
					// Schedule the cron to renew the channel expiration.
					wp_schedule_event( time() + 28800, 'twicedaily', $cron_id, $cron_args );
				}
			}
		}

		// Fetch sheet to store initial data.
		$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values:batchGet?ranges=A1:ZZ1000000&majorDimension=ROWS";

		$headers = array(
			'Authorization: Bearer ' . $access_token,
			'Content-Type: application/json',
		);

		// @codingStandardsIgnoreStart
		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $api_url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

		$response = curl_exec( $ch );

		if ( curl_errno( $ch ) ) {
			error_log( 'Curl error: ' . curl_error( $ch ) );
		} else {
			$response_data = json_decode( $response, true );
			if ( isset( $response_data['error'] ) ) {
				// Do not store the initial data.
			} else {
				// Store the initial data in stored resource.
				// Get the workflow.
				$args = array(
					'workflow_id' => $workflow_id,
				);

				$workflow_data     = array();
				$workflow          = wp_flowmattic()->workflows_db->get( $args );
				$workflow_settings = json_decode( $workflow->workflow_settings,true );

				// Update the stored data.
				$workflow_settings['stored_response'] = base64_encode( wp_json_encode( $response_data ) ); // phpcs:ignore

				// Update the workflow settings.
				wp_flowmattic()->workflows_db->update_settings( $workflow_id, array( 'workflow_settings' => $workflow_settings ) );
			}
		}

		echo wp_json_encode( $ajax_response );

		die();
	}

	/**
	 * Renew the webhook expiration cron.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id     Workflow ID.
	 * @param string $connect_id      Connect ID.
	 * @param string $resource_id     Resource ID.
	 * @param string $spreadsheet_id  Spreadsheet ID.
	 * @param string $expires         Expiration time.
	 * @return void
	 */
	public function renew_webhook_expiration_cron( $workflow_id, $connect_id, $resource_id, $spreadsheet_id, $expires ) {
		$channel_headers = array(
			'X-Goog-Channel-Id'         => $spreadsheet_id,
			'X-Goog-Resource-Id'        => $resource_id,
			'X-Goog-Channel-Expiration' => $expires,
		);

		// Renew the webhook expiration.
		$this->renew_webhook_expiration( $workflow_id, $connect_id, $channel_headers );

		// Clear the messed up crons.
		$this->clear_messed_webhook_crons();
	}

	/**
	 * Renew the webhook expiration.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id     Workflow ID.
	 * @param string $connect_id      Connect ID.
	 * @param string $channel_headers Channel headers.
	 * @return void
	 */
	public function renew_webhook_expiration( $workflow_id, $connect_id, $channel_headers ) {
		$access_token = $this->get_access_token( array( 'connect_id' => $connect_id ) );

		// Get the spreadsheet ID.
		$spreadsheet_id = isset( $channel_headers['X-Goog-Channel-Id'] ) ? $channel_headers['X-Goog-Channel-Id'] : '';

		// Remove the workflow ID from the spreadsheet ID.
		$spreadsheet_id = str_replace( $workflow_id, '', $spreadsheet_id );

		// Get the resource ID.
		$resource_id = isset( $channel_headers['X-Goog-Resource-Id'] ) ? $channel_headers['X-Goog-Resource-Id'] : '';

		// Get channel expiration.
		$channel_expiration = isset( $channel_headers['X-Goog-Channel-Expiration'] ) ? $channel_headers['X-Goog-Channel-Expiration'] : '';

		// Proceed only if the channel is expiring in 4 hours.
		if ( ( time() + 14400 ) < strtotime( $channel_expiration ) ) {
			return;
		}

		// Get the webhook URL.
		$webhook_url = FlowMattic_Webhook::get_url( $workflow_id );

		$webhook_data = array(
			'workflow_id'    => $workflow_id,
			'webhook_url'    => $webhook_url,
			'connect_id'     => $connect_id,
			'spreadsheet_id' => $spreadsheet_id,
			'access_token'   => $access_token,
			'resource_id'    => $resource_id,
		);

		$args = array(
			'body'    => wp_json_encode( $webhook_data ),
			'timeout' => 20,
			'headers' => array(
				'Content-Type' => 'application/json',
				'User-Agent'   => 'FlowMattic',
			),
		);
		
		// Renew the webhook.
		$webhook_request = wp_remote_post( 'https://api.flowmattic.com/google/sheets/webhook.php?action=renew', $args );
		$request_body    = wp_remote_retrieve_body( $webhook_request );
		$request         = json_decode( $request_body, true );
		$new_expiration  = isset( $request['expiration'] ) ? $request['expiration'] : '';

		$cron_args = array(
			'workflow_id'    => $workflow_id,
			'connect_id'     => $connect_id,
			'resource_id'    => $resource_id,
			'spreadsheet_id' => $spreadsheet_id,
			'expires'        => $channel_expiration,
		);

		// If expires or new expiration is empty, return.
		if ( '' === $new_expiration || '' === $channel_expiration ) {
			return;
		}

		$cron_id = 'flowmattic_renew_google_sheet_webhook';

		// Check if cron already scheduled.
		$next_scheduled = wp_next_scheduled( $cron_id, $cron_args );

		if ( $next_scheduled ) {
			// Unschedule the cron.
			wp_unschedule_event( $next_scheduled, $cron_id, $cron_args );

			// Update the expiration time.
			$cron_args['expires'] = is_numeric( $new_expiration ) ? gmdate( 'D, d M Y H:i:s', ( (int) $new_expiration / 1000 ) ) . ' GMT' : $new_expiration;

			// Schedule the cron to renew the channel expiration.
			wp_schedule_event( time() + 28800, 'twicedaily', $cron_id, $cron_args );
		} else { 
			// Schedule the cron to renew the channel expiration.
			wp_schedule_event( time() + 28800, 'twicedaily', $cron_id, $cron_args );
		}
	}

	/**
	 * Expand webhook object.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $capture_data  Data captured by the webhook.
	 * @param array $workflow_data Workflow data.
	 * @return array $capture_data Expanded data.
	 */
	public function expand_webhook_object( $capture_data, $workflow_data ) {
		if ( isset( $workflow_data['application'] ) && 'google_spreadsheets' === $workflow_data['application'] ) {
			// Get the request headers.
			$headers = getallheaders();

			// Get the secret token.
			$secret_token = 'FM' . $workflow_data['workflow_id'];

			if ( isset( $headers['X-Goog-Channel-Token'] ) && $headers['X-Goog-Channel-Token'] !== $secret_token ) {
				http_response_code( 403 );
				exit( 'Invalid token' );
			}

			// If capture response in progress, get the webhook ID.
			$capture_workflow_id = get_option( 'webhook-capture-live', false );

			// Get workflow ID.
			$workflow_id = $workflow_data['workflow_id'];

			// Get trigger spreadsheet ID.
			$spreadsheet_id = isset( $workflow_data['trigger_sheet_id'] ) ? $workflow_data['trigger_sheet_id'] : '';

			// Get action.
			$action = $workflow_data['action'];

			// Get access token.
			$access_token = $this->get_access_token( $workflow_data );

			if ( isset( $headers['X-Goog-Resource-Id'] ) ) {
				$cron_args = array(
					'workflow_id'    => $workflow_id,
					'connect_id'     => $workflow_data['connect_id'],
					'resource_id'    => $headers['X-Goog-Resource-Id'],
					'spreadsheet_id' => $spreadsheet_id,
					'expires'        => $headers['X-Goog-Channel-Expiration'],
				);

				$cron_id = 'flowmattic_renew_google_sheet_webhook';

				// Check if cron already scheduled.
				$next_scheduled = wp_next_scheduled( $cron_id, $cron_args );

				if ( ! $next_scheduled && '' !== $cron_args['expires'] ) {
					// Schedule the cron to renew the channel expiration.
					$schedule = wp_schedule_event( time() + 28800, 'twicedaily', $cron_id, $cron_args );
				}
			}

			// Renew the webhook expiration, if required.
			$this->renew_webhook_expiration( $workflow_id, $workflow_data['connect_id'], $headers );

			// Get the sheets in spreadsheet.
			$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id";
			$args    = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			);

			$sheets_request = wp_remote_get( $api_url, $args );
			$sheets_request = wp_remote_retrieve_body( $sheets_request );
			$sheets_request = json_decode( $sheets_request, true );
			$sheets         = isset( $sheets_request['sheets'] ) ? $sheets_request['sheets'] : array();
			$sheet_ids      = array();
			$sheet_titles   = array();

			foreach ( $sheets as $sheet ) {
				// Get sheet title.
				$sheet_title = str_replace( "'", '', $sheet['properties']['title'] );
				$sheet_title = base64_encode( $sheet_title ); // @codingStandardsIgnoreLine

				// Create an array of sheet title and sheet ID.
				$sheet_ids[ $sheet_title ] = $sheet['properties']['sheetId'];

				// Create an array of sheet ID and sheet title.
				$sheet_titles[ $sheet['properties']['sheetId'] ] = $sheet['properties']['title'];
			}

			// Get the sheet ID.
			$sheet_id       = isset( $workflow_data['trigger_spreadsheet_sheet_id'] ) ? $workflow_data['trigger_spreadsheet_sheet_id'] : '';
			$sheet_range_id = isset( $sheet_titles[ $sheet_id ] ) ? rawurlencode( $sheet_titles[ $sheet_id ] ) : 'Sheet1';

			// Get trigger cell id.
			$trigger_cell_id = isset( $workflow_data['trigger_cell_id'] ) ? $workflow_data['trigger_cell_id'] : '';

			// Get the trigger column ID.
			$trigger_column_id = isset( $workflow_data['trigger_column_id'] ) ? $workflow_data['trigger_column_id'] : '';

			// Get the trigger after column ID.
			$trigger_after_column_id = isset( $workflow_data['trigger_after_column_id'] ) ? $workflow_data['trigger_after_column_id'] : '';

			// If trigger after column is set, wait a sec. and fetch fresh data.
			if ( '' !== $trigger_after_column_id && 'new_row' === $action ) {
				sleep( 1 ); // Wait a sec.
			}

			// Form the API URL.
			$api_url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id/values:batchGet?ranges=$sheet_range_id!A1:ZZ1000000&majorDimension=ROWS";

			$headers = array(
				'Authorization: Bearer ' . $access_token,
				'Content-Type: application/json',
			);

			// @codingStandardsIgnoreStart
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $api_url );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );

			$response = curl_exec( $ch );

			$error = curl_errno( $ch ) ? curl_error( $ch ) : '';

			curl_close( $ch );
			// @codingStandardsIgnoreEnd

			if ( '' !== $error ) {
				error_log( 'Curl error: ' . $error ); // @codingStandardsIgnoreLine For debugging purpose.
				exit;
			} else {
				$response_data = json_decode( $response, true );
				if ( isset( $response_data['error'] ) ) {
					error_log( 'Error: ' . $response_data['error']['message'] ); // @codingStandardsIgnoreLine For debugging purpose.
					exit;
				} else {
					// Process the changes.
					$changes = $this->process_changes( $response_data, $sheet_ids, $sheet_id, $workflow_id );

					$rows_updated = isset( $changes['rows_updated'] ) ? $changes['rows_updated'] : array();
					$main_action  = $action;

					// If empty, check if new rows added.
					if ( 'update_row' === $action && empty( $rows_updated ) && isset( $changes['rows_added'] ) ) {
						// Set action as new row.
						$action = 'new_row';
					}

					// Switch action.
					switch ( $action ) {
						case 'new_row':
							$rows_added = isset( $changes['rows_added'] ) ? $changes['rows_added'] : array();

							// If empty, do not proceed.
							if ( empty( $rows_added ) ) {
								exit;
							}

							// Loop through the rows added.
							foreach ( $rows_added as $row ) {
								// Get row id.
								$row_id = $row['row_id'];

								// Get the row data.
								$row_data = $row['values'];

								// Generate the response.
								$capture_data = array(
									'row_id'   => $row_id,
									'row_data' => wp_json_encode( $row_data ),
								);

								foreach ( $row_data as $key => $value ) {
									// Get the column ID.
									$column_id = $this->column_index_to_letter( $key );

									// Add the column data in simple format.
									$capture_data[ 'cell_' . $column_id . '_id' ]    = $column_id . $row_id;
									$capture_data[ 'cell_' . $column_id . '_value' ] = $value;

									// Compatibility for order update trigger.
									if ( 'update_row' === $main_action ) {
										$capture_data[ 'cell_' . $column_id . '_updated_value' ] = $value;
										$capture_data[ 'cell_' . $column_id . '_old_value' ]     = $value;
									}
								}

								// If trigger after column is set, and the column is not present, skip.
								if ( '' !== $trigger_after_column_id ) {
									if ( ! isset( $capture_data[ 'cell_' . $trigger_after_column_id . '_value' ] ) ) {
										continue;
									}
								}

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );

								// Wait for 1 second.
								sleep( 1 );
							}
							break;

						case 'update_row':
							$rows_updated = isset( $changes['rows_updated'] ) ? $changes['rows_updated'] : array();

							// If empty, do not proceed.
							if ( empty( $rows_updated ) ) {
								exit;
							}

							// Loop through the rows updated.
							foreach ( $rows_updated as $row_id => $row ) {
								// Get the row data.
								$row_data = $row;

								// Generate the response.
								$capture_data = array(
									'row_id'   => $row_id,
									'row_data' => wp_json_encode( $changes['row_data'][ $row_id ] ),
								);

								foreach ( $row_data as $key => $value ) {
									// Get the column ID.
									$column_id = $value['column_id'];

									// Add the column data in simple format.
									$capture_data[ 'cell_' . $column_id . '_id' ]            = $value['cell_id'];
									$capture_data[ 'cell_' . $column_id . '_updated_value' ] = $value['updated_value'];
									$capture_data[ 'cell_' . $column_id . '_old_value' ]     = $value['previous_value'];
								}

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );
							}
							break;

						case 'delete_row':
							$rows_deleted = isset( $changes['rows_removed'] ) ? $changes['rows_removed'] : array();

							// If empty, do not proceed.
							if ( empty( $rows_deleted ) ) {
								exit;
							}

							// Loop through the rows deleted.
							foreach ( $rows_deleted as $row ) {
								// Get row id.
								$row_id = $row['row_id'];

								// Get the row data.
								$row_data = $row['values'];

								// Generate the response.
								$capture_data = array(
									'row_id'           => $row_id,
									'deleted_row_data' => wp_json_encode( $row_data ),
								);

								foreach ( $row_data as $key => $value ) {
									// Get the column ID.
									$column_id = $this->column_index_to_letter( $key );

									// Add the column data in simple format.
									$capture_data[ 'cell_' . $column_id . '_id' ]    = $column_id . $row_id;
									$capture_data[ 'cell_' . $column_id . '_value' ] = $value;
								}

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );
							}
							break;

						case 'update_cell_data':
							$cells_updated = isset( $changes['cells_updated'] ) ? $changes['cells_updated'] : array();

							// If empty, do not proceed.
							if ( empty( $cells_updated ) ) {
								exit;
							}

							// Loop through the cells updated.
							foreach ( $cells_updated as $cell_id => $cell ) {
								// Generate the response.
								$capture_data = $cell;

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );
							}
							break;

						case 'update_specific_cell_data':
							$cells_updated = isset( $changes['cells_updated'] ) ? $changes['cells_updated'] : array();

							// If empty, do not proceed.
							if ( empty( $cells_updated ) ) {
								exit;
							}

							// Loop through the cells updated.
							foreach ( $cells_updated as $cell_id => $cell ) {
								// If cell ID does not match, skip the cell.
								if ( $cell_id !== $trigger_cell_id ) {
									continue;
								}

								// Generate the response.
								$capture_data = $cell;

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );
							}
							break;

						case 'update_specific_column_data':
							$rows_updated = isset( $changes['rows_updated'] ) ? $changes['rows_updated'] : array();

							// If empty, do not proceed.
							if ( empty( $rows_updated ) ) {
								exit;
							}

							// Loop through the rows updated.
							foreach ( $rows_updated as $row_id => $row ) {
								// Get the row data.
								$row_data = $row;

								// Generate the response.
								$capture_data = array(
									'row_id'   => $row_id,
									'row_data' => '',
								);

								foreach ( $row_data as $key => $value ) {
									// Get the column ID.
									$column_id = $value['column_id'];

									// If column ID does not match, skip the column.
									if ( $column_id !== $trigger_column_id ) {
										continue;
									}

									// Add the column data in simple format.
									$capture_data[ 'cell_' . $column_id . '_id' ]            = $value['cell_id'];
									$capture_data[ 'cell_' . $column_id . '_updated_value' ] = $value['updated_value'];
									$capture_data[ 'cell_' . $column_id . '_old_value' ]     = $value['previous_value'];

									// Add the row data.
									$capture_data['row_data'] = wp_json_encode( $value );

									// Break the loop.
									break;
								}

								if ( $capture_workflow_id ) {
									update_option( 'webhook-capture-' . $capture_workflow_id, $capture_data );
									delete_option( 'webhook-capture-live' );

									// Do not execute workflow if capture data in process.
									break;
								}

								// Run the workflow.
								$flowmattic_workflow = new FlowMattic_Workflow();
								$flowmattic_workflow->run( $workflow_id, $capture_data );
							}
							break;
					}

					// Get the workflow.
					$args = array(
						'workflow_id' => $workflow_id,
					);

					$workflow_data     = array();
					$workflow          = wp_flowmattic()->workflows_db->get( $args );
					$workflow_settings = json_decode( $workflow->workflow_settings, true );

					// Save the current state for future comparisons.
					// Update the stored data.
					$workflow_settings['stored_response'] = base64_encode( wp_json_encode( $response_data ) ); // phpcs:ignore

					// Update the workflow settings.
					wp_flowmattic()->workflows_db->update_settings( $workflow_id, array( 'workflow_settings' => $workflow_settings ) );
				}

				// Prevent the webhook from being executed multiple times.
				exit;
			}
		}

		return $capture_data;
	}

	/**
	 * Clear the messed webhook crons.
	 *
	 * @access public
	 * @since 4.3.1
	 * @return void
	 */
	public function clear_messed_webhook_crons() {
		// Get cron array.
		$crons = _get_cron_array();

		foreach ( $crons as $timestamp => $cron ) {
			foreach ( $cron as $hook => $args ) {
				if ( 'flowmattic_renew_google_sheet_webhook' === $hook ) {
					$args = array_values( $args )[0]['args'];

					// Get the expiration time.
					$expires = isset( $args['expires'] ) ? $args['expires'] : '';

					// If expiration is empty, unschedule the cron.
					if ( '' === $expires ) {
						$unschedued = wp_unschedule_event( $timestamp, 'flowmattic_renew_google_sheet_webhook', $args, true );

						if ( ! empty( $cron[ $hook ] ) ) {
							unset( $crons[ $timestamp ][ $hook ] );
						}

						if ( empty( $crons[ $timestamp ] ) ) {
							unset( $crons[ $timestamp ] );
						}
					}
				}
			}
		}

		// Update the cron array.
		_set_cron_array( $crons );
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

		// CS.
		$capture_data;

		// Check if sheet is mapped custom.
		$custom_sheet_toggle = isset( $step['map-field-google-spreadsheet-sheet'] ) && '' !== $step['map-field-google-spreadsheet-sheet'] ? true : false;

		// Set the authType on first level of step.
		$step['authType'] = isset( $step['actionAppArgs']['authType'] ) ? $step['actionAppArgs']['authType'] : 'traditional';

		// If sheet ID is numeric, get the sheet title.
		if ( $custom_sheet_toggle ) {
			// Get the access token.
			$access_token = $this->get_access_token( $step, $workflow_id );

			$sheet_id_or_title = $step['actionAppArgs']['google-spreadsheet-sheet'];
			$spreadsheet_id    = $step['actionAppArgs']['spreadsheet_id'];
			$sheet_info        = $this->get_sheet_name_id( $spreadsheet_id, $sheet_id_or_title, $access_token );

			if ( ! isset( $sheet_info['sheet_title'] ) ) {
				$response = $sheet_info;

				return wp_json_encode( $response );
			}

			$sheet_title = $sheet_info['sheet_title'];
			$sheet_id    = $sheet_info['sheet_id'];

			// Update the step.
			$step['sheetTitle'] = $sheet_title;
			$step['sheetID']    = $sheet_id;

			// If title is not found, return error.
			if ( ! $sheet_title ) {
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Sheet title missing. Please select a valid sheet.', 'flowmattic' ),
				);

				return wp_json_encode( $response );
			}
		}

		switch ( $action ) {
			case 'new_row':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$column_mapping = array_map( 'stripslashes', $column_mapping );
				$response       = $this->add_new_row( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->add_new_row( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'update_row':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$column_mapping = array_map( 'stripslashes', $column_mapping );
				$response       = $this->update_row( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->update_row( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'delete_row':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$column_mapping = array_map( 'stripslashes', $column_mapping );
				$response       = $this->delete_row( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->delete_row( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'clear_row':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$response = $this->clear_row( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->clear_row( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'lookup_row':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$response = $this->lookup_row( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->lookup_row( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'get_cell_data':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$response = $this->get_cell_data( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->get_cell_data( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'update_cell_data':
				$column_mapping = isset( $step['columnMapping'] ) ? $step['columnMapping'] : array();

				if ( isset( $step['customColumnMapping'] ) && ! empty( $step['customColumnMapping'] ) ) {
					$column_mapping = $step['customColumnMapping'];
				}

				$response = $this->update_cell_data( $column_mapping, $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->update_cell_data( $column_mapping, $workflow_id, $step );
				}

				break;

			case 'get_row_count':
				$response = $this->get_row_count( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->get_row_count( $workflow_id, $step );
				}

				break;

			case 'get_row_data':
				$response = $this->get_row_data( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->get_row_data( $workflow_id, $step );
				}

				break;

			case 'create_column':
				$response = $this->create_column( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->create_column( $workflow_id, $step );
				}

				break;

			case 'new_sheet':
				$response = $this->new_sheet( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->new_sheet( $workflow_id, $step );
				}

				break;

			case 'import_csv_json':
				$response = $this->import_csv_json( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->import_csv_json( $workflow_id, $step );
				}

				break;

			case 'copy_sheet':
				$response = $this->copy_sheet( $workflow_id, $step );

				// If authentication failed, try refreshing access token and do another test.
				if ( false !== strpos( $response, 'authentication credential' ) ) {
					// Try refreshing access token.
					$this->refresh_token( '', $workflow_id );

					// Wait for 1 sec.
					sleep( 1 );

					// Do another test.
					$response = $this->copy_sheet( $workflow_id, $step );
				}

				break;

		}

		return $response;
	}

	/**
	 * Refresh the access token.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $refresh_token Refresh token app name.
	 * @param string $workflow_id   Workflow ID.
	 * @return void
	 */
	public function refresh_token( $refresh_token = '', $workflow_id = '' ) {

		// Get the authentication data.
		$authentication_data = get_option( 'flowmattic_auth_data', array() );

		foreach ( $authentication_data as $auth_workflow_id => $auth_data ) {

			// If workflow does not exist, remove the scheduled event and skip refresh token.
			$args = array(
				'workflow_id' => $auth_workflow_id,
			);

			$workflow = wp_flowmattic()->workflows_db->get( $args );

			// If workflow is deleted, remove the scheduled event and skip refresh token.
			if ( empty( $workflow ) ) {
				$next_scheduled = wp_next_scheduled(
					'refresh_google_access_token',
					array(
						'refresh_token' => 'google',
						'workflow_id'   => $auth_workflow_id,
					)
				);

				if ( $next_scheduled ) {
					wp_unschedule_event(
						$next_scheduled,
						'refresh_google_access_token',
						array(
							'refresh_token' => 'google',
							'workflow_id'   => $auth_workflow_id,
						)
					);
				}

				continue;
			}

			if ( '' === $workflow_id ) {
				$next_scheduled = wp_next_scheduled(
					'refresh_google_access_token',
					array(
						'refresh_token' => 'google',
						'workflow_id'   => $auth_workflow_id,
					)
				);

				if ( $next_scheduled ) {
					continue;
				}
			} elseif ( $workflow_id !== $auth_workflow_id ) {
				// If workflow ID does not match, skip the refresh token.
				continue;
			}

			if ( isset( $auth_data['google_spreadsheets']['auth_data']['error'] ) ) {
				continue;
			}

			if ( ! isset( $auth_data['google_spreadsheets'] ) ) {
				continue;
			}

			// Get the authentication data.
			$google_auth_data = $auth_data['google_spreadsheets'];

			if ( isset( $google_auth_data['auth_data']['refresh_token'] ) ) {
				$refresh_token = $google_auth_data['auth_data']['refresh_token'];

				$args = array(
					'body'    => array(
						'grant_type'    => 'refresh_token',
						'refresh_token' => $refresh_token,
					),
					'timeout' => 20,
				);

				$request = wp_remote_post( 'https://api.flowmattic.com/google', $args );
				$request = wp_remote_retrieve_body( $request );
				$request = json_decode( $request, true );

				$google_auth_data['auth_data']                  = $request;
				$google_auth_data['auth_data']['refresh_token'] = $refresh_token;

				flowmattic_update_auth_data( 'google_spreadsheets', $auth_workflow_id, $google_auth_data );

				$next_scheduled = wp_next_scheduled(
					'refresh_google_access_token',
					array(
						'refresh_token' => 'google',
						'workflow_id'   => $auth_workflow_id,
					)
				);

				if ( ! $next_scheduled ) {
					wp_schedule_event(
						time(),
						'flowmattic_hourly',
						'refresh_google_access_token',
						array(
							'refresh_token' => 'google',
							'workflow_id'   => $auth_workflow_id,
						)
					);
				}
			}
		}

		// Remove old crons.
		$this->remove_old_refresh_token_cron();
	}

	/**
	 * Ajax to refresh the spreadsheets list.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function refresh_spreadsheets() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the authentication data.
		$post_data           = $_POST;
		$workflow_id         = isset( $post_data['workflow_id'] ) ? sanitize_text_field( wp_unslash( $post_data['workflow_id'] ) ) : '';
		$authentication_data = isset( $post_data['authData'] ) ? $post_data['authData'] : ''; // @codingStandardsIgnoreLine
		$connect_id          = isset( $post_data['connect_id'] ) ? sanitize_text_field( wp_unslash( $post_data['connect_id'] ) ) : '';
		$auth_type           = isset( $post_data['authType'] ) ? sanitize_text_field( wp_unslash( $post_data['authType'] ) ) : 'tranditional';
		$from                = isset( $post_data['from'] ) ? sanitize_text_field( wp_unslash( $post_data['from'] ) ) : '';
		$access_token        = '';

		if ( 'connect' === $auth_type || 'trigger' === $from ) {
			// Get the access token.
			$access_token = $this->get_access_token( $post_data );
		} else {
			if ( '' === $authentication_data ) {
				$authentication_data = flowmattic_get_auth_data( 'google_spreadsheets', $workflow_id );
				$authentication_data = isset( $authentication_data['auth_data'] ) ? $authentication_data['auth_data'] : array();
			}

			if ( isset( $authentication_data['access_token'] ) ) {
				$access_token = $authentication_data['access_token'];
			}
		}

		$google_spreadsheets = array();

		if ( '' !== $access_token ) {

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 60,
			);

			// Get all available spreadsheets.
			$request             = wp_remote_get( 'https://www.googleapis.com/drive/v3/files?q=mimeType="application/vnd.google-apps.spreadsheet"', $args );
			$request             = wp_remote_retrieve_body( $request );
			$google_spreadsheets = json_decode( $request, true );

			set_transient( 'flowmattic-google-spreadsheets-' . $workflow_id, $google_spreadsheets, HOUR_IN_SECONDS * 2 );

		}

		echo wp_json_encode( $google_spreadsheets );

		die();
	}

	/**
	 * Ajax to refresh the sheets from spreadsheet.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function refresh_spreadsheets_sheets() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the authentication data.
		$workflow_id    = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
		$connect_id     = isset( $_POST['connect_id'] ) ? sanitize_text_field( wp_unslash( $_POST['connect_id'] ) ) : '';
		$spreadsheet_id = isset( $_POST['spreadsheetID'] ) ? sanitize_text_field( wp_unslash( $_POST['spreadsheetID'] ) ) : '';
		$access_token   = '';

		if ( '' !== $connect_id ) {
			// Get the access token.
			$access_token = $this->get_access_token( $_POST );
		}

		$spreadsheet_sheets = array();

		if ( '' !== $access_token ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 60,
			);

			// Get all available spreadsheets.
			$request             = wp_remote_get( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet_id . '?includeGridData=false', $args );
			$request             = wp_remote_retrieve_body( $request );
			$google_spreadsheets = json_decode( $request, true );
		}

		echo wp_json_encode( $google_spreadsheets );

		die();
	}

	/**
	 * Ajax to fetch given sheet URL.
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function fetch_sheet_url_ajax() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the authentication data.
		$workflow_id  = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
		$access_url   = isset( $_POST['access_url'] ) ? sanitize_text_field( wp_unslash( $_POST['access_url'] ) ) : '';
		$access_token = isset( $_POST['accessToken'] ) ? sanitize_text_field( wp_unslash( $_POST['accessToken'] ) ) : '';
		$auth_type    = isset( $_POST['authType'] ) ? sanitize_text_field( wp_unslash( $_POST['authType'] ) ) : 'tranditional';
		$sheet_name   = isset( $_POST['sheetName'] ) ? sanitize_text_field( wp_unslash( $_POST['sheetName'] ) ) : '';

		// If sheet name is set, encode it and replace it in the URL.
		if ( '' !== $sheet_name ) {
			$access_url = str_replace( 'sheetTitle', rawurlencode( $sheet_name ), $access_url );
		}

		if ( 'connect' === $auth_type ) {
			// Get the access token.
			$access_token = $this->get_access_token( $_POST );
		} elseif ( '' === $access_token ) {
			$authentication_data = flowmattic_get_auth_data( 'google_spreadsheets', $workflow_id );
			$authentication_data = isset( $authentication_data['auth_data'] ) ? $authentication_data['auth_data'] : array();

			if ( isset( $authentication_data['access_token'] ) ) {
				$access_token = $authentication_data['access_token'];
			}
		}

		$response = array();

		if ( '' !== $access_token ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 60,
			);

			// Fetch the given URL.
			$request  = wp_remote_get( $access_url . '&access_token=' . $access_token );
			$response = wp_remote_retrieve_body( $request );
		}

		echo $response; // @codingStandardsIgnoreLine

		die();
	}

	/**
	 * Set actions.
	 *
	 * @access public
	 * @since 1.0
	 * @param string $workflow_id Workflow ID for the deleted workflow.
	 * @return void
	 */
	public function remove_crons_after_workflow_delete( $workflow_id ) {
		// Reset the cron to refresh token.
		$next_scheduled = wp_next_scheduled(
			'refresh_google_access_token',
			array(
				'refresh_token' => 'google',
				'workflow_id'   => $workflow_id,
			)
		);
		wp_unschedule_event(
			$next_scheduled,
			'refresh_google_access_token',
			array(
				'refresh_token' => 'google',
				'workflow_id'   => $workflow_id,
			)
		);
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
		$response    = array();
		$event       = $event_data['event'];
		$workflow_id = $event_data['workflow_id'];
		$step_ids    = $event_data['stepIDs'];
		$settings    = $event_data['settings'];
		$fields      = isset( $settings['columnMapping'] ) ? $settings['columnMapping'] : array();

		if ( ! $event ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Action event not selected.', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$settings = array_merge( $settings, $event_data['fields'] );

		// Check if sheet is mapped custom.
		$custom_sheet_toggle = isset( $settings['map-field-google-spreadsheet-sheet'] ) && '' !== $settings['map-field-google-spreadsheet-sheet'] ? true : false;

		// If sheet ID is numeric, get the sheet title.
		if ( $custom_sheet_toggle ) {
			// Get the access token.
			$access_token = $this->get_access_token( $settings, $workflow_id );

			$sheet_id_or_title = $settings['google-spreadsheet-sheet'];
			$spreadsheet_id    = $settings['spreadsheet_id'];
			$sheet_info        = $this->get_sheet_name_id( $spreadsheet_id, $sheet_id_or_title, $access_token );
			$sheet_title       = $sheet_info['sheet_title'];
			$sheet_id          = $sheet_info['sheet_id'];

			// Update the settings.
			$settings['sheetTitle'] = $sheet_title;
			$settings['sheetID']    = $sheet_id;

			// If title is not found, return error.
			if ( ! $sheet_title ) {
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Sheet title missing. Please select a valid sheet.', 'flowmattic' ),
				);

				return wp_json_encode( $response );
			}
		}

		switch ( $event ) {
			case 'new_row':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$fields   = array_map( 'stripslashes', $fields );
				$response = $this->add_new_row( $fields, $workflow_id, $settings );
				break;

			case 'update_row':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$fields   = array_map( 'stripslashes', $fields );
				$response = $this->update_row( $fields, $workflow_id, $settings );
				break;

			case 'delete_row':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$fields   = array_map( 'stripslashes', $fields );
				$response = $this->delete_row( $fields, $workflow_id, $settings );
				break;

			case 'clear_row':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$fields   = array_map( 'stripslashes', $fields );
				$response = $this->clear_row( $fields, $workflow_id, $settings );
				break;

			case 'lookup_row':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$response = $this->lookup_row( $fields, $workflow_id, $settings );
				break;

			case 'get_cell_data':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$response = $this->get_cell_data( $fields, $workflow_id, $settings );
				break;

			case 'update_cell_data':
				if ( isset( $event_data['settings']['customColumnMapping'] ) && ! empty( $event_data['settings']['customColumnMapping'] ) ) {
					$fields = $event_data['settings']['customColumnMapping'];
				}

				$response = $this->update_cell_data( $fields, $workflow_id, $settings );
				break;

			case 'get_row_count':
				$response = $this->get_row_count( $workflow_id, $settings );
				break;

			case 'get_row_data':
				$response = $this->get_row_data( $workflow_id, $settings );
				break;

			case 'create_column':
				$response = $this->create_column( $workflow_id, $settings );
				break;

			case 'new_sheet':
				$response = $this->new_sheet( $workflow_id, $settings );
				break;

			case 'import_csv_json':
				$response = $this->import_csv_json( $workflow_id, $settings );
				break;

			case 'copy_sheet':
				$response = $this->copy_sheet( $workflow_id, $settings );
				break;
		}

		// Reset the cron to refresh token.
		$next_scheduled = wp_next_scheduled(
			'refresh_google_access_token',
			array(
				'refresh_token' => 'google',
				'workflow_id'   => $workflow_id,
			)
		);
		wp_unschedule_event(
			$next_scheduled,
			'refresh_google_access_token',
			array(
				'refresh_token' => 'google',
				'workflow_id'   => $workflow_id,
			)
		);

		return $response;
	}

	/**
	 * Add new row.
	 *
	 * @access public
	 * @since 1.0
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function add_new_row( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = stripslashes( $settings['sheetTitle'] );
		$sheet_title = rawurlencode( $sheet_title );
		$sheet_id    = $settings['sheetID'];

		// Fix empty new lines.
		foreach ( $fields as $key => $value ) {
			$new_value      = preg_replace( "/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $value );
			$fields[ $key ] = $new_value;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode(
				array(
					'majorDimension' => 'ROWS',
					'values'         => array(
						array_values( $fields ),
					),
				)
			),
		);

		// Get all available spreadsheets.
		$request        = wp_remote_post( "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet/values/$sheet_title!A:A:append?insertDataOption=INSERT_ROWS&valueInputOption=USER_ENTERED", $args );
		$request        = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['updates_updatedRange'] ) && '' !== $response_array['updates_updatedRange'] ) {
			$range              = explode( '!', $response_array['updates_updatedRange'] );
			$range              = $range[1];
			$range_data         = explode( ':', $range );
			$range_column_first = $range_data[0];
			preg_match_all( '/\d.*/', $range_column_first, $matches );
			$range_row = $matches[0];

			$response_array['row_number'] = ( is_array( $range_row ) ) ? $range_row[0] : $range_row;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Update row.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function update_row( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = stripslashes( $settings['sheetTitle'] );
		// $sheet_title = rawurlencode( $sheet_title );
		$sheet_id    = $settings['sheetID'];
		$row_number  = $settings['actionAppArgs']['row_number'];

		// Get the column data.
		$column_mappings = array_values( $fields );

		// Prepare the column data.
		$column_data = array();

		// Loop through the column mappings, and prepare the column data for batch update.
		foreach ( $column_mappings as $key => $value ) {
			if ( '' === $value ) {
				continue;
			}

			$column_id     = $this->column_index_to_letter( $key );
			$column_data[] = array(
				'range'  => $sheet_title . '!' . $column_id . $row_number,
				'values' => array(
					array( $value ),
				),
			);
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'valueInputOption' => 'USER_ENTERED',
					'data'             => $column_data,
				)
			),
		);

		// Get all available spreadsheets.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values:batchUpdate', $args );
		$request        = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['updatedRange'] ) && '' !== $response_array['updatedRange'] ) {
			$range              = explode( '!', $response_array['updatedRange'] );
			$range              = $range[1];
			$range_data         = explode( ':', $range );
			$range_column_first = $range_data[0];
			preg_match_all( '/\d.*/', $range_column_first, $matches );
			$range_row = $matches[0];

			$response_array['row_number'] = ( is_array( $range_row ) ) ? $range_row[0] : $range_row;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Delete row.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function delete_row( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$row_number  = (int) $settings['actionAppArgs']['row_number'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'requests' => array(
						array(
							'deleteDimension' => array(
								'range' => array(
									'sheetId'    => $sheet_id,
									'dimension'  => 'ROWS',
									'startIndex' => $row_number - 1,
									'endIndex'   => $row_number,
								),
							),
						),
					),
				)
			),
		);

		// Get all available spreadsheets.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . ':batchUpdate', $args );
		$request        = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['spreadsheetId'] ) && '' !== $response_array['spreadsheetId'] ) {
			$response_array['removed_row_number'] = $row_number;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Clear row.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function clear_row( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$row_number  = (int) $settings['actionAppArgs']['row_number'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
		);

		// Get all available spreadsheets.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!A' . $row_number . ':ZZ' . $row_number . ':clear', $args );
		$request        = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['spreadsheetId'] ) && '' !== $response_array['spreadsheetId'] ) {
			$response_array['cleared_row_number'] = $row_number;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Lookup row.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function lookup_row( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$search      = str_replace( '/', '\/', $settings['actionAppArgs']['search_text'] ); // Fix for URL or strings with slashes.

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
		);

		$request      = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!A1:ZZ1000000', $args );
		$request_json = wp_remote_retrieve_body( $request );
		$request_data = json_decode( $request_json );

		// If error, return error.
		if ( isset( $request_data->error ) ) {
			$response_array = array(
				'status'  => 'error',
				'message' => $request_data->error->message,
			);

			return wp_json_encode( $response_array );
		}

		$response_array = array();
		$searched_rows  = array();
		$i              = 1;

		// Get the headers row.
		$headers = isset( $request_data->values[0] ) ? $request_data->values[0] : array();

		// Loop through the available data.
		foreach ( $request_data->values as $key => $row_value ) {
			if ( is_array( $row_value ) ) {
				$matches = preg_grep( '/' . $search . '/i', $row_value );
				if ( ! empty( $matches ) ) {
					$row_data = array(
						'row_number' => $key + 1,
					);

					$col = 'A';
					foreach ( $row_value as $index => $value ) {
						// Check if header column available for this, else assign the column letter.
						$header_title = ( isset( $headers[ $index ] ) ) ? $headers[ $index ] : $col;

						$row_data[ 'column_' . $header_title ] = $value;
						++$col;
					}

					$searched_rows['data'][ $i ] = $row_data;
					++$i;
				}
			}
		}

		if ( isset( $searched_rows['data'] ) ) {
			foreach ( $searched_rows as $key => $value ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			}

			// To be used in iterator.
			$response_array['data_json'] = wp_json_encode( array_values( $searched_rows['data'] ) );
		} else {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_html__( 'No rows found', 'flowmattic' ),
			);
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Get cell data.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function get_cell_data( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$cell        = $settings['actionAppArgs']['cell_id'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
		);

		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!' . $cell . ':' . $cell, $args );
		$request_json   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_json, true );
		$response_array = array(
			'status' => 'success',
			'cell'   => $cell,
		);

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['values_0_0'] ) && '' !== $response_array['values_0_0'] ) {
			$response_array[ 'cell_' . $cell . '_value' ] = $response_array['values_0_0'];
			$response_array['cell_value']                 = $response_array['values_0_0'];

			preg_match_all( '/\d.*/', $cell, $matches );
			$range_row = $matches[0];

			$response_array['row_number'] = ( is_array( $range_row ) ) ? $range_row[0] : $range_row;

			unset( $response_array['values_0_0'] );
		}

		// Clean up response.
		unset( $response_array['majorDimension'] );

		return wp_json_encode( $response_array );
	}

	/**
	 * Update cell data.
	 *
	 * @access public
	 * @since 1.1
	 * @param array  $fields      Column mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function update_cell_data( $fields, $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$cell        = $settings['actionAppArgs']['cell_id'];
		$cell_data   = stripslashes( $settings['actionAppArgs']['cell_data'] );

		// $cell_data = explode( ',', $cell_data );

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'PUT',
			'body'    => wp_json_encode(
				array(
					'values' => array(
						array( $cell_data ),
					),
				)
			),
		);

		// Update cell data.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!' . $cell . '?valueInputOption=USER_ENTERED', $args );
		$request_json   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_json, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['updatedRange'] ) && '' !== $response_array['updatedRange'] ) {
			$response_array['cell_updated']               = $cell;
			$response_array[ 'cell_' . $cell . '_value' ] = $cell_data;

			preg_match_all( '/\d.*/', $cell, $matches );
			$range_row = $matches[0];

			$response_array['row_number'] = ( is_array( $range_row ) ) ? $range_row[0] : $range_row;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Get row count.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function get_row_count( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];

		// If sheet title is not set, return error.
		if ( '' === $sheet_title ) {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_html__( 'Sheet selection is required.', 'flowmattic' ),
			);

			return wp_json_encode( $response_array );
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
		);

		$request      = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!A1:ZZ1000000', $args );
		$request_json = wp_remote_retrieve_body( $request );
		$request_data = json_decode( $request_json );

		// If error, return error.
		if ( isset( $request_data->error ) ) {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_html__( 'Error fetching data.', 'flowmattic' ),
				'error'   => $request_data->error->message,
			);

			return wp_json_encode( $response_array );
		}

		$response_array = array(
			'status' => 'success',
			'sheet'  => urldecode( $sheet_title ),
		);

		// Get the headers row.
		$headers = isset( $request_data->values[0] ) ? $request_data->values[0] : array();

		// Loop through the available data.
		$row_count = count( $request_data->values );

		$response_array['row_count'] = $row_count;

		return wp_json_encode( $response_array );
	}

	/**
	 * Get row data.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function get_row_data( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = rawurlencode( $settings['sheetTitle'] );
		$sheet_id    = $settings['sheetID'];
		$range       = $settings['actionAppArgs']['range'];

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
		);

		$request      = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!' . $range, $args );
		$request_json = wp_remote_retrieve_body( $request );
		$request_data = json_decode( $request_json );

		// If error, return error.
		if ( isset( $request_data->error ) ) {
			$response_array = array(
				'status'  => 'error',
				'message' => esc_html__( 'Error fetching data.', 'flowmattic' ),
				'error'   => $request_data->error->message,
			);

			return wp_json_encode( $response_array );
		}

		$response_array = array(
			'status' => 'success',
			'range'  => $range,
		);

		// Seperate the range into row and column.
		$range_data = explode( ':', $range );
		$range_row  = $range_data[0];
		$range_col  = $range_data[1];

		// Get the start row number and column ID.
		$range_start_row    = preg_replace( '/\D/', '', $range_row );
		$range_start_letter = preg_replace( '/\d/', '', $range_row );

		// Get the end row number and column ID.
		$range_end_row    = preg_replace( '/\D/', '', $range_col );
		$range_end_letter = preg_replace( '/\d/', '', $range_col );

		$row_data = array();

		// Loop through the values and prepare the response in a simplified format.
		foreach ( $request_data->values as $key => $row_value ) {
				$col = $range_start_letter;

			foreach ( $row_value as $index => $value ) {
				$i = is_int( $range_start_row ) ? $range_start_row + $key : $key + 1;
				$response_array[ 'cell_' . $col . $i . '_value' ] = $value;
				$row_data[ $i ][ 'column_' . $col ]               = array(
					'cell_id'    => $col . $i,
					'cell_value' => $value,
				);
				++$col;
			}
		}

		$response_array['row_data'] = wp_json_encode( $row_data );

		return wp_json_encode( $response_array );
	}


	/**
	 * Create column.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function create_column( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet  = $settings['spreadsheetID'];
		$sheet_title  = rawurlencode( $settings['sheetTitle'] );
		$sheet_id     = $settings['sheetID'];
		$column_name  = $settings['actionAppArgs']['column_name'];
		$column_index = isset( $settings['actionAppArgs']['column_index'] ) && '' !== $settings['actionAppArgs']['column_index'] ? $settings['actionAppArgs']['column_index'] : '';

		// Prepare the column dimension.
		$column_dimension = array();

		if ( '' !== $column_index ) {
			// Column index is set, insert column at the specified index.
			$column_dimension = array(
				'insertDimension' => array(
					'range' => array(
						'sheetId'    => $sheet_id,
						'startIndex' => $column_index,
						'endIndex'   => $column_index + 1,
						'dimension'  => 'COLUMNS',

					),
				),
			);
		} else {
			// Instead, append column at the end.
			$column_dimension = array(
				'appendDimension' => array(
					'sheetId'   => $sheet_id,
					'dimension' => 'COLUMNS',
					'length'    => 1,
				),
			);

			// URL to get the first row of the specified sheet.
			$metadata_url = 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '?fields=sheets(properties(sheetId,title,gridProperties(columnCount)))';

			// Making the GET request to fetch the updated sheet metadata.
			$response = wp_remote_get(
				$metadata_url,
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
					),
				)
			);

			// Check for errors in the response.
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// Retrieve the response body.
			$body             = wp_remote_retrieve_body( $response );
			$spreadsheet_data = json_decode( $body, true );

			// Find the updated column count for the specified sheet.
			$column_count = 0;
			foreach ( $spreadsheet_data['sheets'] as $key => $sheet ) {
				if ( (int) $sheet_id === (int) $sheet['properties']['sheetId'] ) {
					$column_count = $sheet['properties']['gridProperties']['columnCount'];
					break;
				}
			}

			$column_index = $column_count;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'requests' => array(
						$column_dimension,
						array(
							'updateCells' => array(
								'rows'   => array(
									array(
										'values' => array(
											array(
												'userEnteredValue' => array(
													'stringValue' => $column_name,
												),
											),
										),
									),
								),
								'fields' => 'userEnteredValue',
								'start'  => array(
									'sheetId'     => $sheet_id,
									'rowIndex'    => 0,
									'columnIndex' => $column_index,
								),
							),
						),
					),
				)
			),
		);

		// Create column.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . ':batchUpdate', $args );
		$request_json   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_json, true );
		$response_array = array();

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['spreadsheetId'] ) && '' !== $response_array['spreadsheetId'] ) {
			$response_array['column_created'] = $column_name;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Create new sheet.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function new_sheet( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = $settings['actionAppArgs']['sheet_title'];
		$headers     = explode( ',', $settings['actionAppArgs']['sheet_headers'] );
		$overwrite   = ( isset( $settings['actionAppArgs']['overwrite_sheet'] ) && 'yes' === $settings['actionAppArgs']['overwrite_sheet'] ) ? true : false;

		$sheet_exists   = false;
		$response_array = array(
			'status'        => 'success',
			'sheet_created' => $sheet_title,
		);

		// If overwrite, clear the existing sheet.
		if ( $overwrite ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
					'Content-type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout' => 20,
				'method'  => 'POST',
			);

			// Clear the existing sheet.
			$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . ':clear', $args );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );

			// If response has clearedrange, then sheet exists.
			if ( isset( $request_decode['clearedRange'] ) && '' !== $request_decode['clearedRange'] ) {
				$sheet_exists = true;
			}
		}

		// If sheet not exists, create new sheet.
		if ( ! $sheet_exists ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
					'Content-type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout' => 20,
				'method'  => 'POST',
				'body'    => wp_json_encode(
					array(
						'requests' => array(
							array(
								'addSheet' => array(
									'properties' => array(
										'title' => $sheet_title,
									),
								),
							),
						),
					)
				),
			);

			// Create new sheet.
			$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . ':batchUpdate', $args );
			$request_json   = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request_json, true );

			foreach ( $request_decode as $key => $value ) {
				if ( 'error' === $key ) {
					$response_array['status'] = 'error';
				}

				// If key is 'replies', then loop through the replies.
				if ( 'replies' === $key ) {
					$value = $value[0]['addSheet'];
					$key   = 'sheet';
				}

				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			if ( 'error' !== $response_array['status'] ) {
				// Set sheet exists to true.
				$sheet_exists = true;
			}
		}

		// If headers are set, add headers to the new sheet.
		if ( ! empty( $headers ) && $sheet_exists ) {
			// Prepare the column data.
			$column_data = array();

			// Loop through the headers, and prepare the column data for batch update.
			foreach ( $headers as $key => $value ) {
				$column_id     = $this->column_index_to_letter( $key );
				$column_data[] = array(
					'range'  => $sheet_title . '!' . $column_id . '1',
					'values' => array(
						array( $value ),
					),
				);
			}

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
					'Content-type'  => 'application/json',
					'Accept'        => 'application/json',
				),
				'timeout' => 20,
				'method'  => 'POST',
				'body'    => wp_json_encode(
					array(
						'valueInputOption' => 'USER_ENTERED',
						'data'             => $column_data,
					)
				),
			);

			// Add headers to the new sheet.
			$request          = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values:batchUpdate', $args );
			$request          = wp_remote_retrieve_body( $request );
			$request_decode   = json_decode( $request, true );
			$response_array_2 = array();

			foreach ( $request_decode as $key => $value ) {
				if ( 'error' === $key ) {
					$response_array['status'] = 'error';

					// Remove the sheet_created key.
					unset( $response_array['sheet_created'] );
				}

				if ( is_array( $value ) ) {
					$response_array_2 = flowmattic_recursive_array( $response_array_2, $key, $value );
				} else {
					$response_array_2[ $key ] = $value;
				}
			}

			$response_array = array_merge( $response_array, $response_array_2 );
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Import CSV to existing sheet.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function import_csv_json( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = $settings['sheetTitle'];
		$sheet_id    = $settings['sheetID'];
		$csv_data    = json_decode( $settings['actionAppArgs']['csv_data'], true );
		$header_row  = ( isset( $settings['actionAppArgs']['header_row'] ) && 'yes' === $settings['actionAppArgs']['header_row'] ) ? true : false;
		$import_type = $settings['actionAppArgs']['import_type'];

		// Check if the CSV data is in array format, else try with stripslashes and json_decode.
		if ( ! is_array( $csv_data ) ) {
			$csv_data = json_decode( stripslashes( $settings['actionAppArgs']['csv_data'] ), true );
		}

		$headers = array();
		$rows    = array();

		// If header row is set, get the headers.
		if ( $header_row ) {
			$headers = isset( $csv_data[0] ) && is_array( $csv_data[0] ) ? $csv_data[0] : array();

			// If single associative array, set the headers as keys.
			if ( empty( $headers ) ) {
				$headers = array_keys( $csv_data );

				// If keys are numeric, then set the headers as empty.
				if ( array_keys( $headers ) !== range( 0, count( $headers ) - 1 ) ) {
					$headers = array();
				}

				if ( ! empty( $headers ) ) {
					// If keys are string, then set the csv_data as values.
					$csv_data = array_values( $csv_data );

					// Convert the csv_data to multi-dimensional array.
					$csv_data = array( $csv_data );
				}
			} else {
				// Check if headers array is associative, then set the headers as keys.
				if ( array_keys( $headers ) !== range( 0, count( $headers ) - 1 ) ) {
					$headers = array_keys( $headers );

					// Since headers are picked from the first item as keys, set the csv_data as values.
					$csv_data = array_map(
						function ( $val ) {
							return array_values( $val );
						},
						$csv_data
					);
				} else {
					// Remove the headers row.
					$csv_data = is_array( $headers ) ? array_slice( $csv_data, 1 ) : $csv_data;
				}
			}
		}

		// Prepare the column data.
		$column_data = array();

		// Loop through the CSV data, and prepare the column data for batch update.
		$csv_data = array_values( $csv_data );

		if ( ! empty( $csv_data ) ) {
			foreach ( $csv_data as $key => $value ) {
				$column_id     = $this->column_index_to_letter( $key );
				$last_column   = is_array( $value ) ? $this->column_index_to_letter( count( $value ) - 1 ) : $column_id;
				$column_values = is_array( $value ) ? array_map(
					function ( $val ) {
						return array( $val );
					},
					$value
				) : array( array( $value ) );

				$insert_row = ! empty( $headers ) ? $key + 2 : $key + 1;

				$column_data[] = array(
					'values' => $value,
				);
			}
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
			'body'    => '',
		);

		// If import type is 'overwrite', clear the existing sheet.
		if ( 'overwrite' === $import_type ) {
			// Clear the existing sheet.
			wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . ':clear', $args );
		}

		// If headers are set, add headers to the new sheet.
		if ( ! empty( $headers ) ) {
			$headers_column_data   = array();
			$headers_column_data[] = array(
				'range'  => $sheet_title . '!A1:' . $this->column_index_to_letter( count( $headers ) - 1 ) . '1',
				'values' => array(
					array_values( $headers ),
				),
			);

			$args['body'] = wp_json_encode(
				array(
					'valueInputOption' => 'USER_ENTERED',
					'data'             => $headers_column_data,
				)
			);

			// Add headers to the new sheet.
			wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values:batchUpdate', $args );
		}

		$rows_count   = count( $csv_data );
		$column_count = is_array( $csv_data[0] ) ? count( $csv_data[0] ) : count( $csv_data );

		// If number of rows are more than 2500, split the data into chunks.
		if ( $rows_count > 2500 ) {
			$column_data_chunks = array_chunk( $column_data, 2500 );

			foreach ( $column_data_chunks as $key => $value ) {
				$args['body'] = wp_json_encode(
					array(
						'values' => $value,
					)
				);

				// Import CSV to existing sheet.
				$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!A:A:append?valueInputOption=USER_ENTERED', $args );
				$request        = wp_remote_retrieve_body( $request );
				$request_decode = json_decode( $request, true );
			}
		} else {
			$args['body'] = wp_json_encode(
				array(
					'values' => $column_data,
				)
			);

			// Import CSV to existing sheet.
			$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/values/' . $sheet_title . '!A:A:append?valueInputOption=USER_ENTERED', $args );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
		}

		// Get the response.
		$response_array = array(
			'status'            => 'success',
			'spreadsheet_id'    => $spreadsheet,
			'sheet_title'       => $sheet_title,
			'data_rows_count'   => $rows_count,
			'data_column_count' => $column_count,
		);

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			} else {
				$key = 'last_' . $key;
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		// Remove last_tablerange and last_spreadsheetid keys.
		unset( $response_array['last_tableRange'] );
		unset( $response_array['last_spreadsheetId'] );

		return wp_json_encode( $response_array );
	}

	/**
	 * Copy sheet to another sheet.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return string
	 */
	public function copy_sheet( $workflow_id, $settings ) {
		// Get the access token.
		$access_token = $this->get_access_token( $settings, $workflow_id );

		$spreadsheet = $settings['spreadsheetID'];
		$sheet_title = $settings['sheetTitle'];
		$sheet_id    = $settings['sheetID'];
		$destination = $settings['actionAppArgs']['destination_spreadsheet_id'];

		// If destination spreadsheet ID is not set, set the destination as the same spreadsheet.
		if ( '' === $destination ) {
			$destination = $spreadsheet;
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $access_token,
				'User-Agent'    => 'FlowMattic',
				'Content-type'  => 'application/json',
				'Accept'        => 'application/json',
			),
			'timeout' => 20,
			'method'  => 'POST',
			'body'    => wp_json_encode(
				array(
					'destinationSpreadsheetId' => $destination,
				)
			),
		);

		// Copy sheet to another sheet.
		$request        = wp_remote_request( 'https://sheets.googleapis.com/v4/spreadsheets/' . $spreadsheet . '/sheets/' . $sheet_id . ':copyTo', $args );
		$request_json   = wp_remote_retrieve_body( $request );
		$request_decode = json_decode( $request_json, true );
		$response_array = array(
			'status'                => 'success',
			'copied_to_spreadsheet' => $destination,
		);

		foreach ( $request_decode as $key => $value ) {
			if ( 'error' === $key ) {
				$response_array['status'] = 'error';
			}

			if ( is_array( $value ) ) {
				$response_array = flowmattic_recursive_array( $response_array, $key, $value );
			} else {
				$response_array[ $key ] = $value;
			}
		}

		if ( isset( $response_array['sheetId'] ) && '' !== $response_array['sheetId'] ) {
			$response_array['sheet_copied'] = $sheet_title;
		}

		return wp_json_encode( $response_array );
	}

	/**
	 * Process changes in the Google Sheet.
	 *
	 * @access public
	 * @since 2.0
	 * @param array  $response_data    The data from Google Sheets API.
	 * @param array  $sheet_ids        The sheet IDs.
	 * @param string $trigger_sheet_id The sheet ID that triggered the event.
	 * @param string $workflow_id      The workflow ID.
	 * @return array      The detected changes.
	 */
	public function process_changes( $response_data, $sheet_ids, $trigger_sheet_id, $workflow_id ) {
		$ranges      = $response_data['valueRanges'];
		$all_changes = array();

		// Get the workflow.
		$args = array(
			'workflow_id' => $workflow_id,
		);

		$workflow_data     = array();
		$workflow          = wp_flowmattic()->workflows_db->get( $args );
		$workflow_settings = json_decode( $workflow->workflow_settings, true );

		$stored_response = isset( $workflow_settings['stored_response'] ) ? $workflow_settings['stored_response'] : array();

		if ( '' === $stored_response ) {
			$previous_state = array();
		} else {
			// Decode the stored response.
			$stored_response_decoded = base64_decode( $stored_response ); // phpcs:ignore
			$previous_state          = json_decode( $stored_response_decoded, true );
		}

		foreach ( $ranges as $key => $range ) {
			$sheet_title        = explode( '!', $range['range'] )[0];
			$sheet_title        = str_replace( "'", '', $sheet_title );
			$sheet_id           = $sheet_ids[ base64_encode( $sheet_title ) ]; // @codingStandardsIgnoreLine
			$current_sheet_data = $range['values'];

			// If sheet ID does not match, skip.
			if ( (string) $sheet_id !== (string) $trigger_sheet_id ) {
				continue;
			}

			if ( ! empty( $previous_state ) ) {
				$prev_sheet_data = $previous_state;
				$prev_sheet_data = $prev_sheet_data['valueRanges'][ $key ]['values'];
				$all_changes     = $this->detect_changes( $sheet_id, $prev_sheet_data, $current_sheet_data );

				// Add sheet ID to the changes.
				$all_changes['sheet_id'] = $sheet_id;
			}
		}

		return $all_changes;
	}

	/**
	 * Detect changes between the previous and current state of the sheet.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $sheet_id           The ID of the sheet.
	 * @param array  $prev_sheet_data    The previous state of the sheet.
	 * @param array  $current_sheet_data The current state of the sheet.
	 * @return array                     The detected changes.
	 */
	public function detect_changes( $sheet_id, $prev_sheet_data, $current_sheet_data ) {
		$changes = array();

		// Detect added rows.
		$added_rows = $this->detect_added_rows( $prev_sheet_data, $current_sheet_data );
		if ( ! empty( $added_rows ) ) {
			$changes['rows_added'] = $added_rows;
		}

		// Detect removed rows.
		$removed_rows = $this->detect_removed_rows( $prev_sheet_data, $current_sheet_data );
		if ( ! empty( $removed_rows ) ) {
			$changes['rows_removed'] = $removed_rows;
		}

		// Detect updated cells.
		$updated_cells = $this->detect_updated_cells( $prev_sheet_data, $current_sheet_data );
		if ( ! empty( $updated_cells ) && ! empty( $updated_cells['cells'] ) ) {
			$changes['cells_updated'] = $updated_cells['cells'];
		}

		// Detect updated rows.
		if ( ! empty( $updated_cells ) && ! empty( $updated_cells['rows'] ) ) {
			$changes['rows_updated'] = $updated_cells['rows'];
			$changes['row_data']     = $updated_cells['row_data'];
		}

		return $changes;
	}

	/**
	 * Detect added rows.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $prev_sheet_data    The previous state of the sheet.
	 * @param array $current_sheet_data The current state of the sheet.
	 * @return array                    The detected added rows.
	 */
	public function detect_added_rows( $prev_sheet_data, $current_sheet_data ) {
		$added_rows = array();

		// Use associative array to keep track of previous and current rows.
		$prev_rows_map    = array_map( 'serialize', $prev_sheet_data );
		$current_rows_map = array_map( 'serialize', $current_sheet_data );

		// Filter out identical rows.
		$new_array_map = array_diff( $current_rows_map, $prev_rows_map );

		// Find truly new rows in the current state.
		foreach ( $new_array_map as $i => $current_row ) {
			// Check if the row is not in the previous state. ( Row added at bottom ).
			if ( ! isset( $prev_rows_map[ $i ] ) ) {
				$added_rows[] = array(
					'row_id' => $i + 1,
					'values' => maybe_unserialize( $current_row ),
				);
			} elseif ( isset( $prev_rows_map[ $i ] ) ) {
				// Check if the row is not in the previous state. ( Row added in between ).
				$prev_data_item = maybe_unserialize( $prev_rows_map[ $i ] );
				$current_data   = maybe_unserialize( $current_row );

				// Check if the current row is not in the previous state.
				$is_new_row = true;
				foreach ( $prev_data_item as $key => $value ) {
					if ( isset( $current_data[ $key ] ) && $current_data[ $key ] === $value ) {
						$is_new_row = false;
						break;
					}
				}

				if ( $is_new_row ) {
					$added_rows[] = array(
						'row_id' => $i + 1,
						'values' => maybe_unserialize( $current_row ),
					);
				}
			}
		}

		return $added_rows;
	}

	/**
	 * Detect removed rows.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $prev_sheet_data    The previous state of the sheet.
	 * @param array $current_sheet_data The current state of the sheet.
	 * @return array                    The detected removed rows.
	 */
	public function detect_removed_rows( $prev_sheet_data, $current_sheet_data ) {
		$removed_rows = array();

		// Use associative array to keep track of previous and current rows.
		$prev_rows    = array_map( 'serialize', $prev_sheet_data );
		$current_rows = array_map( 'serialize', $current_sheet_data );

		// If count of previous rows is less than current rows, then no rows are removed.
		if ( count( $prev_rows ) <= count( $current_rows ) ) {
			return $removed_rows;
		}

		// Filter out identical rows.
		$new_array_map = array_diff( $prev_rows, $current_rows );

		foreach ( $new_array_map as $i => $row ) {
			// Check if the row is not in the current state.
			if ( isset( $current_rows[ $i ] ) && $row === $current_rows[ $i ] ) {
				continue;
			} elseif ( isset( $current_rows[ $i ] ) && $row !== $current_rows[ $i ] ) {
				$removed_rows[] = array(
					'row_id' => $i + 1,
					'values' => maybe_unserialize( $row ),
				);

				// Break the loop.
				break;
			} elseif ( ! isset( $current_rows[ $i ] ) ) {
				$removed_rows[] = array(
					'row_id' => $i + 1,
					'values' => maybe_unserialize( $row ),
				);
				break;
			}
		}

		return $removed_rows;
	}

	/**
	 * Detect updated cells.
	 *
	 * @access public
	 * @since 2.0
	 * @param array $prev_sheet_data    The previous state of the sheet.
	 * @param array $current_sheet_data The current state of the sheet.
	 * @return array                    The detected updated cells.
	 */
	public function detect_updated_cells( $prev_sheet_data, $current_sheet_data ) {
		$updated_cells = array();
		$updated_rows  = array();
		$row_data      = array();

		// Use associative array to keep track of previous and current rows.
		$prev_rows    = array_map( 'serialize', $prev_sheet_data );
		$current_rows = array_map( 'serialize', $current_sheet_data );

		// Filter out identical rows.
		$new_array_map = array_diff( $prev_rows, $current_rows );

		foreach ( $new_array_map as $i => $row ) {
			if ( isset( $current_rows[ $i ] ) ) {
				// Unserialize the row data.
				$prev_data_item = maybe_unserialize( $row );
				$current_data   = maybe_unserialize( $current_rows[ $i ] );

				foreach ( $prev_data_item as $key => $value ) {
					$cell_id   = $this->column_index_to_letter( $key ) . ( $i + 1 );
					$cell_data = array(
						'cell_id'        => $cell_id,
						'row_id'         => $i + 1,
						'column_id'      => $this->column_index_to_letter( $key ),
						'previous_value' => $value,
						'updated_value'  => $current_data[ $key ],
					);

					if ( isset( $current_data[ $key ] ) && $current_data[ $key ] !== $value ) {
						$updated_cells[ $cell_id ] = $cell_data;
					}

					// Add all cells to row data.
					$row_data[ $i + 1 ][] = array(
						'row_id'     => $i + 1,
						'cell_id'    => $cell_id,
						'cell_value' => $current_data[ $key ],
					);
				}
			}
		}

		// Add row to updated rows if more than one cell is updated.
		if ( count( $updated_cells ) > 1 ) {
			foreach ( $updated_cells as $cell_id => $data ) {
				// Get the row ID.
				$row_id = $data['row_id'];

				// Add the data to the updated rows.
				$updated_rows[ $row_id ][] = $data;
			}
		}

		return array(
			'cells'    => $updated_cells,
			'rows'     => $updated_rows,
			'row_data' => $row_data,
		);
	}

	/**
	 * Get the sheet name by sheet ID.
	 *
	 * @access public
	 * @since 2.0
	 * @param string $spreadsheet_id The ID of the spreadsheet.
	 * @param int    $sheet_id       The ID of the sheet.
	 * @param string $access_token   The access token for authentication.
	 * @return string|bool The sheet name if found, false otherwise.
	 */
	public function get_sheet_name_id( $spreadsheet_id, $sheet_id, $access_token ) {
		$url = "https://sheets.googleapis.com/v4/spreadsheets/$spreadsheet_id?fields=sheets(properties(sheetId,title))";

		$response = wp_remote_get(
			$url,
			array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'Content-Type'  => 'application/json',
				),
			)
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		// If error, return error.
		if ( isset( $data['error'] ) ) {
			return array(
				'status'  => 'error',
				'message' => esc_html__( 'Error fetching data.', 'flowmattic' ),
				'error'   => $data['error']['message'],
			);
		}

		foreach ( $data['sheets'] as $sheet ) {

			// Check if the sheet ID or title matches.
			if ( $sheet['properties']['sheetId'] === $sheet_id || $sheet['properties']['title'] === $sheet_id ) {
				return array(
					'sheet_title' => $sheet['properties']['title'],
					'sheet_id'    => $sheet['properties']['sheetId'],
				);
			}
		}

		return false;
	}

	/**
	 * Convert column index to letter.
	 *
	 * @access public
	 * @since 2.0
	 * @param int $index The column index.
	 * @return string    The column letter.
	 */
	public function column_index_to_letter( $index ) {
		$letters = '';
		while ( $index >= 0 ) {
			$letters = chr( $index % 26 + 65 ) . $letters;
			$index   = floor( $index / 26 ) - 1;
		}
		return $letters;
	}

	/**
	 * Return the request data sent to API endpoint.
	 *
	 * @access public
	 * @since 2.0
	 * @return array
	 */
	public function get_request_data() {
		return $this->request_body;
	}
}

new FlowMattic_Google_Spreadsheets();
