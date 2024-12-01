<?php
/**
 * Application Name: Google Drive
 * Description: Add Google Drive integration to FlowMattic.
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
 * Google Drive integration class.
 *
 * @since 1.0
 */
class FlowMattic_Google_Drive {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for google-drive.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'google_drive',
			array(
				'name'         => esc_attr__( 'Google Drive', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/google-drive/icon.svg',
				'instructions' => esc_attr__( 'Connect your Google Drive account. Your credentials are stored securely in your site.', 'flowmattic' ),
				'connect_note' => esc_attr__( 'Your Google Drive account is already connected. To re-connect Google Drive, click the button above. Your credentials are stored securely in your site.', 'flowmattic' ),
				'actions'      => $this->get_actions(),
				'type'         => 'action',
				'version'      => '1.1.0',
			)
		);

		// Register cron for refreshing the access token.
		add_action( 'admin_init', array( $this, 'add_refresh_token_cron' ) );
		add_action( 'refresh_google_access_token', array( $this, 'refresh_token' ) );

		// Ajax to refresh google drive folder list.
		add_action( 'wp_ajax_refresh_drive_folder_list', array( $this, 'refresh_drive_folder_list' ) );
	}

	/**
	 * Add cron for refresh token.
	 *
	 * @access public
	 * @since 1.0.4
	 * @return void
	 */
	public function add_refresh_token_cron() {
		// Get the authentication data.
		$authentication_data = get_option( 'flowmattic_auth_data', array() );
		$next_scheduled      = wp_next_scheduled( 'refresh_google_access_token', array( 'refresh_token' ) );

		if ( ! empty( array_column( $authentication_data, 'google_drive' ) ) ) {
			if ( ! $next_scheduled ) {
				wp_schedule_event( time(), 'flowmattic_hourly', 'refresh_google_access_token', array( 'refresh_token' ) );
			}
		} else {
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
		wp_enqueue_script( 'flowmattic-app-view-google-drive', FLOWMATTIC_APP_URL . '/google-drive/view-google-drive.js', array( 'flowmattic-workflow' ), FLOWMATTIC_VERSION, true );
	}

	/**
	 * Ajax to refresh the google drive folder list.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function refresh_drive_folder_list() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get the authentication data.
		$workflow_id         = isset( $_POST['workflow_id'] ) ? sanitize_text_field( wp_unslash( $_POST['workflow_id'] ) ) : '';
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$google_drive_folders = array();

		if ( isset( $authentication_data['auth_data']['access_token'] ) ) {

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 20,
			);

			// Get all available spreadsheets.
			$request              = wp_remote_get( 'https://www.googleapis.com/drive/v3/files?q=mimeType="application/vnd.google-apps.folder" and "root" in parents', $args );
			$request              = wp_remote_retrieve_body( $request );
			$google_drive_folders = json_decode( $request, true );

			set_transient( 'flowmattic-google-drive-folders', $google_drive_folders, HOUR_IN_SECONDS * 2 );

		}

		echo wp_json_encode( $google_drive_folders );

		die();
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
		$fields = $step['actionAppArgs'];

		switch ( $action ) {
			case 'upload_file':
				$response = $this->upload_new_file( $fields, $workflow_id, $step );
				break;

			case 'create_folder':
				$response = $this->create_new_folder( $fields, $workflow_id, $step );
				break;

			case 'create_sub_folder':
				$response = $this->create_sub_folder( $fields, $workflow_id, $step );
				break;

			case 'share_folder':
				$response = $this->share_folder( $fields, $workflow_id, $step );
				break;

			case 'share_folder_with_anyone':
				$response = $this->share_folder_with_anyone( $fields, $workflow_id, $step );
				break;

			case 'share_file':
				$response = $this->share_file( $fields, $workflow_id, $step );
				break;

			case 'share_file_with_anyone':
				$response = $this->share_file_with_anyone( $fields, $workflow_id, $step );
				break;
		}

		return $response;
	}

	/**
	 * Refresh the access token.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function refresh_token() {

		// Get the authentication data.
		$authentication_data = get_option( 'flowmattic_auth_data', array() );

		foreach ( $authentication_data as $workflow_id => $auth_data ) {

			if ( ! isset( $auth_data['google_drive'] ) ) {
				continue;
			}

			// Get the authentication data.
			$google_auth_data = $auth_data['google_drive'];

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

				flowmattic_update_auth_data( 'google_drive', $workflow_id, $google_auth_data );
			}
		}
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
			'upload_file'              => array(
				'title'       => esc_attr__( 'Upload a File', 'flowmattic' ),
				'description' => esc_attr__( 'Upload file to your Google Drive selected folder', 'flowmattic' ),
			),
			'create_folder'            => array(
				'title'       => esc_attr__( 'Create a Folder', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new folder in your Google Drive', 'flowmattic' ),
			),
			'create_sub_folder'        => array(
				'title'       => esc_attr__( 'Create a Sub Folder', 'flowmattic' ),
				'description' => esc_attr__( 'Create an empty sub folder', 'flowmattic' ),
			),
			'share_folder'             => array(
				'title'       => esc_attr__( 'Share Folder', 'flowmattic' ),
				'description' => esc_attr__( 'Share the folder by its ID to an email', 'flowmattic' ),
			),
			'share_folder_with_anyone' => array(
				'title'       => esc_attr__( 'Share Folder with Anyone', 'flowmattic' ),
				'description' => esc_attr__( 'Generates the link to share with anyone on the internet', 'flowmattic' ),
			),
			'share_file'               => array(
				'title'       => esc_attr__( 'Share File', 'flowmattic' ),
				'description' => esc_attr__( 'Share any file by its ID to an email', 'flowmattic' ),
			),
			'share_file_with_anyone'   => array(
				'title'       => esc_attr__( 'Share File with Anyone', 'flowmattic' ),
				'description' => esc_attr__( 'Generates the link to share with anyone on the internet', 'flowmattic' ),
			),
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
		$settings    = $event_data['settings'];
		$workflow_id = $event_data['workflow_id'];
		$fields      = isset( $event_data['fields'] ) ? $event_data['fields'] : ( isset( $settings['actionAppArgs'] ) ? $settings['actionAppArgs'] : array() );

		switch ( $event ) {
			case 'upload_file':
				$response = $this->upload_new_file( $fields, $workflow_id, $settings );
				break;

			case 'create_folder':
				$response = $this->create_new_folder( $fields, $workflow_id, $settings );
				break;

			case 'create_sub_folder':
				$response = $this->create_sub_folder( $fields, $workflow_id, $settings );
				break;

			case 'share_folder':
				$response = $this->share_folder( $fields, $workflow_id, $settings );
				break;

			case 'share_folder_with_anyone':
				$response = $this->share_folder_with_anyone( $fields, $workflow_id, $settings );
				break;

			case 'share_file':
				$response = $this->share_file( $fields, $workflow_id, $settings );
				break;

			case 'share_file_with_anyone':
				$response = $this->share_file_with_anyone( $fields, $workflow_id, $settings );
				break;
		}

		// Reset the cron to refresh token.
		$next_scheduled = wp_next_scheduled( 'refresh_google_access_token', array( 'refresh_token' ) );
		wp_unschedule_event( $next_scheduled, 'refresh_google_access_token', array( 'refresh_token' ) );

		return $response;
	}

	/**
	 * Upload file to Google drive.
	 *
	 * @access public
	 * @since 1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function upload_new_file( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$folder_id = $fields['folder_id'];
		$file_url  = $fields['file_url'];
		$file_info = pathinfo( $file_url );
		$file_name = ( isset( $fields['file_name'] ) && '' !== $fields['file_name'] ) ? $fields['file_name'] : $file_info['basename'];

		$file_request = wp_remote_get( $file_url, array( 'sslverify' => false ) );

		if ( is_wp_error( $file_request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $file_request->get_error_message(),
			);
		} else {
			$file_contents = wp_remote_retrieve_body( $file_request );

			// This is a multipart/related upload.
			$boundary = wp_rand();
			$boundary = str_replace( '"', '', $boundary );

			$content_type = 'multipart/related; boundary=' . $boundary;
			$mime_type    = $file_request['headers']['content-type'];

			$meta = array(
				'name'    => $file_name,
				'parents' => array(
					$folder_id,
				),
			);

			$related  = "--$boundary\r\n";
			$related .= "Content-Type: application/json; charset=UTF-8\r\n";
			$related .= "\r\n" . wp_json_encode( $meta ) . "\r\n";
			$related .= "--$boundary\r\n";
			$related .= "Content-Type: $mime_type\r\n";
			$related .= "Content-Transfer-Encoding: base64\r\n";
			$related .= "\r\n" . base64_encode( $file_contents ) . "\r\n"; // @codingStandardsIgnoreLine
			$related .= "--$boundary--";

			$post_body = $related;

			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
					'User-Agent'    => 'FlowMattic',
					'Content-Type'  => $content_type,
				),
				'timeout' => 20,
				'body'    => $post_body,
			);

			// Send request to Google Drive.
			$request = wp_remote_post( 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart', $args );

			if ( is_wp_error( $request ) ) {
				$response = array(
					'status' => 'error',
					'error'  => $request->get_error_message(),
				);
			} else {
				$request        = wp_remote_retrieve_body( $request );
				$request_decode = json_decode( $request, true );
				$response_array = array();

				if ( isset( $request_decode['error'] ) ) {
					$response = array(
						'status'  => 'error',
						'code'    => $request_decode['error']['code'],
						'message' => $request_decode['error']['message'],
					);

					return wp_json_encode( $response );
				}

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

		return wp_json_encode( $response );
	}

	/**
	 * Upload file to Google drive.
	 *
	 * @access public
	 * @since 1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function create_new_folder( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$folder_name = $fields['folder_name'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode(
				array(
					'name'     => $folder_name,
					'mimeType' => 'application/vnd.google-apps.folder',
				),
			),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files', $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Create a new sub-folder in Google drive.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function create_sub_folder( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$folder_name   = $fields['folder_name'];
		$parent_folder = $fields['folder_id'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode(
				array(
					'name'     => $folder_name,
					'mimeType' => 'application/vnd.google-apps.folder',
					'parents'  => array( $parent_folder ),
				),
			),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files', $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Share a folder in Google drive.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function share_folder( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$email       = $fields['email'];
		$role        = $fields['role'];
		$notify_user = 'yes' === $fields['notify_user'] ? 'true' : 'false';
		$folder_id   = $fields['folder_id'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$permissions = array(
			'role'         => $role,
			'type'         => 'user',
			'emailAddress' => $email,
		);

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode( $permissions ),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files/' . $folder_id . '/permissions?sendNotificationEmail=' . $notify_user, $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Share a folder in Google drive with anyone.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function share_folder_with_anyone( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$folder_id = $fields['folder_id'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$permissions = array(
			'role' => 'reader',
			'type' => 'anyone',
		);

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode( $permissions ),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files/' . $folder_id . '/permissions', $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			unset( $args['body'] );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			$request        = wp_remote_get( 'https://www.googleapis.com/drive/v3/files/' . $folder_id . '/?fields=id,name,webViewLink', $args );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );

			foreach ( $request_decode as $key => $value ) {
				$key = 'folder_' . $key;
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Share a file in Google drive.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function share_file( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$email       = $fields['email'];
		$role        = $fields['role'];
		$notify_user = 'yes' === $fields['notify_user'] ? 'true' : 'false';
		$file_id     = $fields['file_id'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$permissions = array(
			'role'         => $role,
			'type'         => 'user',
			'emailAddress' => $email,
		);

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode( $permissions ),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files/' . $file_id . '/permissions?sendNotificationEmail=' . $notify_user, $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}

	/**
	 * Share a file in Google drive with anyone.
	 *
	 * @access public
	 * @since 1.1.0
	 * @param array  $fields      Mapping fields.
	 * @param string $workflow_id Workflow ID.
	 * @param array  $settings    Workflow Settings.
	 * @return array
	 */
	public function share_file_with_anyone( $fields, $workflow_id, $settings ) {
		// Get the authentication data.
		$authentication_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );

		$file_id = $fields['file_id'];

		if ( ! isset( $authentication_data['auth_data']['access_token'] ) ) {
			$response = array(
				'status'  => 'error',
				'message' => esc_html__( 'Invalid Credentials', 'flowmattic' ),
			);

			return wp_json_encode( $response );
		}

		$permissions = array(
			'role' => 'reader',
			'type' => 'anyone',
		);

		$args = array(
			'headers' => array(
				'Authorization' => 'Bearer ' . $authentication_data['auth_data']['access_token'],
				'User-Agent'    => 'FlowMattic',
				'Content-Type'  => 'application/json',
			),
			'timeout' => 20,
			'body'    => wp_json_encode( $permissions ),
		);

		// Send request to Google Drive.
		$request = wp_remote_post( 'https://www.googleapis.com/drive/v3/files/' . $file_id . '/permissions', $args );

		if ( is_wp_error( $request ) ) {
			$response = array(
				'status' => 'error',
				'error'  => $request->get_error_message(),
			);
		} else {
			unset( $args['body'] );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );
			$response_array = array();

			if ( isset( $request_decode['error'] ) ) {
				$response = array(
					'status'  => 'error',
					'code'    => $request_decode['error']['code'],
					'message' => $request_decode['error']['message'],
				);

				return wp_json_encode( $response );
			}

			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			$request        = wp_remote_get( 'https://www.googleapis.com/drive/v3/files/' . $file_id . '/?fields=id,name,webViewLink', $args );
			$request        = wp_remote_retrieve_body( $request );
			$request_decode = json_decode( $request, true );

			foreach ( $request_decode as $key => $value ) {
				$key = 'file_' . $key;
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}

			return wp_json_encode( $response_array );
		}

		return wp_json_encode( $response );
	}
}

new FlowMattic_Google_Drive();
