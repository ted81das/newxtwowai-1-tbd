<?php
/**
 * Application Name: Cloudflare
 * Description: Add Cloudflare integration to FlowMattic.
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
 * Cloudflare integration class.
 *
 * @since 1.0
 */
class FlowMattic_Cloudflare {
	/**
	 * Constructor.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function __construct() {
		// Enqueue custom view for cloudflare.
		add_action( 'flowmattic_enqueue_views', array( $this, 'enqueue_views' ) );

		flowmattic_add_application(
			'cloudflare',
			array(
				'name'         => esc_attr__( 'Cloudflare', 'flowmattic' ),
				'icon'         => FLOWMATTIC_APP_URL . '/cloudflare/icon.svg',
				'instructions' => '',
				'actions'      => $this->get_actions(),
				'type'         => 'action',
				'version'      => '1.0',
			)
		);

		// Ajax to capture the accounts list.
		add_action( 'wp_ajax_flowmattic_get_cloudflare_accounts', array( $this, 'get_cloudflare_accounts' ) );

		// Ajax to capture the zone list.
		add_action( 'wp_ajax_flowmattic_get_cloudflare_zones', array( $this, 'get_cloudflare_zones' ) );

		// Ajax to get the DNS records for zone.
		add_action( 'wp_ajax_flowmattic_get_cloudflare_zone_dns', array( $this, 'get_cloudflare_zone_dns' ) );
	}

	/**
	 * Enqueue view js.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function enqueue_views() {
		wp_enqueue_script( 'flowmattic-app-view-cloudflare', FLOWMATTIC_APP_URL . '/cloudflare/view-cloudflare.js', array( 'flowmattic-workflow-utils' ), FLOWMATTIC_VERSION, true );
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
			'create_domain'         => array(
				'title'       => esc_attr__( 'Create Domain', 'flowmattic' ),
				'description' => esc_attr__( 'Create a new domain zone.', 'flowmattic' ),
			),
			'delete_domain'         => array(
				'title'       => esc_attr__( 'Delete Domain', 'flowmattic' ),
				'description' => esc_attr__( 'Delete an existing domain zone.', 'flowmattic' ),
			),
			'create_dns_record'     => array(
				'title'       => esc_attr__( 'Create DNS Record', 'flowmattic' ),
				'description' => esc_attr__( 'Create a DNS record for domain.', 'flowmattic' ),
			),
			'update_dns_record'     => array(
				'title'       => esc_attr__( 'Update DNS Record', 'flowmattic' ),
				'description' => esc_attr__( 'Update an existing DNS record for domain.', 'flowmattic' ),
			),
			'delete_dns_record'     => array(
				'title'       => esc_attr__( 'Delete DNS Record', 'flowmattic' ),
				'description' => esc_attr__( 'Delete an existing DNS record from domain.', 'flowmattic' ),
			),
			'purge_cache'           => array(
				'title'       => esc_attr__( 'Purge All Files', 'flowmattic' ),
				'description' => esc_attr__( 'Remove ALL files from Cloudflare\'s cache for domain zone.', 'flowmattic' ),
			),
			'search_domain'         => array(
				'title'       => esc_attr__( 'Search Domain by Domain Name', 'flowmattic' ),
				'description' => esc_attr__( 'Search for the domain name and get details.', 'flowmattic' ),
			),
			'search_zone_by_domain' => array(
				'title'       => esc_attr__( 'Search Zone by Domain Name', 'flowmattic' ),
				'description' => esc_attr__( 'Search for the domain zone by domain name.', 'flowmattic' ),
			),
		);
	}

	/**
	 * Ajax to get accounts list.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_cloudflare_accounts() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get settings.
		$settings = $_POST['settings']; // @codingStandardsIgnoreLine

		$app_args = ( isset( $settings['actionAppArgs'] ) ) ? $settings['actionAppArgs'] : $settings['settings']['actionAppArgs'];
		$api_key  = $app_args['api_key'];
		$email    = $app_args['email'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => array(
				'page'      => '1',
				'per_page'  => 20,
				'direction' => 'desc',
			),
		);

		$request       = wp_remote_get( 'https://api.cloudflare.com/client/v4/accounts/', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_array = wp_json_encode(
				array(
					'status' => 'error',
					'code'   => $response_code,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		echo stripslashes( $response_array ); // @codingStandardsIgnoreLine

		die();
	}

	/**
	 * Ajax to get zone list.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_cloudflare_zones() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get settings.
		$settings = $_POST['settings']; // @codingStandardsIgnoreLine

		$app_args   = ( isset( $settings['actionAppArgs'] ) ) ? $settings['actionAppArgs'] : $settings['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => array(
				'account.id' => $account_id,
				'match'      => 'all',
				'page'       => '1',
				'per_page'   => 20,
				'direction'  => 'desc',
			),
		);

		$request       = wp_remote_get( 'https://api.cloudflare.com/client/v4/zones/', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_array = wp_json_encode(
				array(
					'status' => 'error',
					'code'   => $response_code,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		echo stripslashes( $response_array ); // @codingStandardsIgnoreLine

		die();
	}

	/**
	 * Ajax to get zone DNS records.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_cloudflare_zone_dns() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get settings.
		$settings = $_POST['settings']; // @codingStandardsIgnoreLine

		$app_args   = ( isset( $settings['actionAppArgs'] ) ) ? $settings['actionAppArgs'] : $settings['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => array(
				'match'     => 'all',
				'page'      => 1,
				'per_page'  => 100,
				'direction' => 'desc',
			),
		);

		$request       = wp_remote_get( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/dns_records', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_array = wp_json_encode(
				array(
					'status' => 'error',
					'code'   => $response_code,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		echo stripslashes( $response_array ); // @codingStandardsIgnoreLine

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

		switch ( $action ) {
			case 'create_domain':
				$response = $this->create_domain( $step );
				break;

			case 'delete_domain':
				$response = $this->delete_domain( $step );
				break;

			case 'create_dns_record':
				$response = $this->create_dns_record( $step );
				break;

			case 'update_dns_record':
				$response = $this->update_dns_record( $step );
				break;

			case 'delete_dns_record':
				$response = $this->delete_dns_record( $step );
				break;

			case 'purge_cache':
				$response = $this->purge_cache( $step );
				break;

			case 'search_domain':
				$response = $this->search_domain( $step );
				break;

			case 'search_zone_by_domain':
				$response = $this->search_zone_by_domain( $step );
				break;
		}

		return $response;
	}

	/**
	 * Add a new domain record in Cloudflare.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function create_domain( $fields ) {
		$app_args    = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key     = $app_args['api_key'];
		$email       = $app_args['email'];
		$account_id  = $app_args['account_id'];
		$type        = $app_args['type'];
		$domain_name = $app_args['domain_name'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode(
				array(
					'account'    => array(
						'id' => $account_id,
					),
					'name'       => $domain_name,
					'jump_start' => ( isset( $app_args['jump_start'] ) && 'yes' === $app_args['jump_start'] ) ? true : false,
					'type'       => $type,
				),
			),
		);

		$request       = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Delete domain record from Cloudflare.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function delete_domain( $fields ) {
		$app_args   = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => 'DELETE',
		);

		$request       = wp_remote_request( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id, $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Create a DNS record for domain.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function create_dns_record( $fields ) {
		$app_args   = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];
		$type       = $app_args['type'];
		$name       = $app_args['name'];
		$value      = $app_args['value'];
		$ttl        = $app_args['ttl'];
		$priority   = isset( $app_args['priority'] ) ? $app_args['priority'] : 0;
		$proxied    = ( isset( $app_args['proxied'] ) && 'Yes' === $app_args['proxied'] ) ? true : false;

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode(
				array(
					'type'     => $type,
					'name'     => $name,
					'content'  => $value,
					'ttl'      => (int) $ttl,
					'priority' => (int) $priority,
					'proxied'  => $proxied,
				)
			),
		);

		$request       = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/dns_records', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Create a DNS record for domain.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function update_dns_record( $fields ) {
		$app_args   = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];
		$dns_id     = $app_args['dns_id'];
		$type       = $app_args['type'];
		$name       = $app_args['name'];
		$value      = $app_args['value'];
		$ttl        = $app_args['ttl'];
		$priority   = isset( $app_args['priority'] ) ? $app_args['priority'] : 0;
		$proxied    = ( isset( $app_args['proxied'] ) && 'Yes' === $app_args['proxied'] ) ? true : false;

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode(
				array(
					'type'     => $type,
					'name'     => $name,
					'content'  => $value,
					'ttl'      => (int) $ttl,
					'priority' => (int) $priority,
					'proxied'  => $proxied,
				)
			),
			'method'      => 'PUT',
		);

		$request       = wp_remote_request( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/dns_records/' . $dns_id, $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Delete domain DNS record from Cloudflare.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function delete_dns_record( $fields ) {
		$app_args   = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];
		$dns_id     = $app_args['dns_id'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'method'      => 'DELETE',
		);

		$request       = wp_remote_request( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/dns_records/' . $dns_id, $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Remove ALL files from Cloudflare's cache.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function purge_cache( $fields ) {
		$app_args   = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key    = $app_args['api_key'];
		$email      = $app_args['email'];
		$account_id = $app_args['account_id'];
		$zone_id    = $app_args['zone_id'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => wp_json_encode(
				array(
					'purge_everything' => true,
				)
			),
		);

		$request       = wp_remote_post( 'https://api.cloudflare.com/client/v4/zones/' . $zone_id . '/purge_cache', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Search and get domain details.
	 *
	 * @access public
	 * @since 1.0
	 * @param array $fields Action params.
	 * @return array
	 */
	public function search_domain( $fields ) {
		$app_args    = ( isset( $fields['actionAppArgs'] ) ) ? $fields['actionAppArgs'] : $fields['settings']['actionAppArgs'];
		$api_key     = $app_args['api_key'];
		$email       = $app_args['email'];
		$account_id  = $app_args['account_id'];
		$domain_name = $app_args['domain_name'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
		);

		$request       = wp_remote_get( 'https://api.cloudflare.com/client/v4/accounts/' . $account_id . '/registrar/domains/' . $domain_name, $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_data  = json_decode( $response, true );
			$error_code     = isset( $response_data['errors'] ) ? $response_data['errors'][0]['code'] : $response_code;
			$error_message  = isset( $response_data['errors'] ) ? $response_data['errors'][0]['message'] : '';
			$response_array = wp_json_encode(
				array(
					'status'  => 'error',
					'code'    => $error_code,
					'message' => $error_message,
				)
			);
		} else {
			$response       = json_decode( $response, true );
			$response_array = wp_json_encode( $response );
		}

		return $response_array;
	}

	/**
	 * Search for zone by domain name.
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function search_zone_by_domain() {
		check_ajax_referer( 'flowmattic_workflow_nonce', 'workflow_nonce' );

		// Get settings.
		$settings = $_POST['settings']; // @codingStandardsIgnoreLine

		$app_args    = ( isset( $settings['actionAppArgs'] ) ) ? $settings['actionAppArgs'] : $settings['settings']['actionAppArgs'];
		$api_key     = $app_args['api_key'];
		$email       = $app_args['email'];
		$account_id  = $app_args['account_id'];
		$domain_name = $app_args['domain_name'];

		// Make a single response array.
		$response_array = array();

		$args = array(
			'headers'     => array(
				'Content-Type' => 'application/json',
				'X-Auth-Email' => $email,
				'X-Auth-Key'   => $api_key,
			),
			'timeout'     => 60,
			'sslverify'   => false,
			'data_format' => 'body',
			'body'        => array(
				'name'       => $domain_name,
				'account.id' => $account_id,
				'match'      => 'all',
				'page'       => '1',
				'per_page'   => 20,
				'direction'  => 'desc',
			),
		);

		$request       = wp_remote_get( 'https://api.cloudflare.com/client/v4/zones/', $args );
		$response_code = wp_remote_retrieve_response_code( $request );
		$response_body = wp_remote_retrieve_body( $request );
		$response      = $response_body;

		if ( 200 !== $response_code ) {
			$response_array = wp_json_encode(
				array(
					'status' => 'error',
					'code'   => $response_code,
				)
			);
		} else {
			$response = json_decode( $response, true );

			if ( isset( $response['result'][0] ) ) {
				$result         = $response['result'][0];
				$response_array = array();
				if ( is_array( $result ) ) {
					foreach ( $result as $key => $value ) {
						if ( is_array( $value ) ) {
							$response_array = flowmattic_recursive_array( $response_array, $key, $value );
						} else {
							$response_array[ $key ] = $value;
						}
					}
				}

				$response = $response_array;
			} else {
				$response = array(
					'status'  => 'error',
					'message' => esc_html__( 'Record not found. Please try again.', 'flowmattic' ),
				);
			}

			$response_array = wp_json_encode( $response );
		}

		echo stripslashes( $response_array ); // @codingStandardsIgnoreLine

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
		$fields         = $event_data;
		$response_array = array();

		// Replace action for testing.
		$event_data['action'] = $event;

		$request = $this->run_action_step( $workflow_id, $event_data, $fields );

		$request_decode = json_decode( $request, true );

		if ( is_array( $request_decode ) ) {
			foreach ( $request_decode as $key => $value ) {
				if ( is_array( $value ) ) {
					$response_array = flowmattic_recursive_array( $response_array, $key, $value );
				} else {
					$response_array[ $key ] = $value;
				}
			}
		}

		return wp_json_encode( $response_array );
	}
}

new FlowMattic_Cloudflare();
