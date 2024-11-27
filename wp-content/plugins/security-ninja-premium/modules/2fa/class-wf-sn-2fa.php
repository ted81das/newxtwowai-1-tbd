<?php

namespace WPSecurityNinja\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once WF_SN_PLUGIN_DIR . 'vendor/autoload.php';

use Da\TwoFA\Manager;
use Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use WP_User;

class Wf_Sn_2fa {


	/**
	 * Stores password-based authentication sessions to be invalidated before 2FA.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	private static $password_auth_tokens = array();

	public static $options = null;

	public function __construct() {
		if ( ! is_null( self::$options ) ) {
			self::$options = self::get_options();
		}
		// Check if 2FA is enabled before registering hooks
		if ( isset( self::$options['2fa_enabled'] ) && true === self::$options['2fa_enabled'] ) {
			self::register_hooks();
		}
	}

	/**
	 * register_hooks.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, June 4th, 2024.
	 * @access  private
	 * @return  void
	 */
	private function register_hooks() {
		add_action( 'wp_ajax_nopriv_secnin_generate_qr_code', array( $this, 'ajax_generate_qr_code' ) );
		add_action( 'wp_ajax_nopriv_secnin_verify_2fa_code', array( $this, 'ajax_verify_2fa_code' ) );

		
		add_action( 'clear_auth_cookie', array( $this, 'clear_2fa_session' ) );

		add_action( 'edit_user_profile', array( $this, 'add_bypass_2fa_checkbox' ) );
		add_action( 'show_user_profile', array( $this, 'add_bypass_2fa_checkbox' ) );
		add_action( 'edit_user_profile_update', array( $this, 'save_bypass_2fa_checkbox' ) );
		add_action( 'personal_options_update', array( $this, 'save_bypass_2fa_checkbox' ) );

		// Add hooks for collecting auth cookies
		add_action( 'set_auth_cookie', array( __CLASS__, 'collect_auth_cookie_tokens' ) );
		add_action( 'set_logged_in_cookie', array( __CLASS__, 'collect_auth_cookie_tokens' ) );
		add_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10, 2 );
	}

	/**
	 * Handles the login process for users with 2FA enabled.
	 *
	 * @param string $user_login The user's login name.
	 * @param WP_User $user The user object.
	 * @return void
	 */
	public static function wp_login( $user_login, $user ) {
		$is_user_using_two_factor   = self::is_user_using_two_factor( $user->ID );
		$should_user_use_two_factor = self::should_user_use_two_factor( $user->ID );

		$my_options = self::get_options();

		// Check if 2FA is enabled
		if ( isset( $my_options['2fa_enabled'] ) && $my_options['2fa_enabled'] ) {
			if ( $should_user_use_two_factor ) {
				// Check if the user has clicked the skip link and is within the grace period
				if ( isset( $_GET['skip_2fa'] ) && '1' === $_GET['skip_2fa'] && isset( $_GET['nonce'] ) && wp_verify_nonce( sanitize_key( $_GET['nonce'] ), 'skip_2fa_nonce' ) ) {
					$enabled_timestamp = isset( $my_options['2fa_enabled_timestamp'] ) ? intval( $my_options['2fa_enabled_timestamp'] ) : 0;
					$grace_period      = isset( $my_options['2fa_grace_period'] ) ? intval( $my_options['2fa_grace_period'] ) : 0;
					$current_time      = time();
					$time_left         = ( $enabled_timestamp + ( $grace_period * DAY_IN_SECONDS ) ) - $current_time;

					if ( $time_left > 0 ) {
						// Allow the user to continue the login process
						wp_set_current_user( $user->ID, $user->user_login );
						wp_set_auth_cookie( $user->ID, true );

						remove_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10 );
						do_action( 'wp_login', $user->user_login, $user );
						add_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10, 2 );

						wp_safe_redirect( admin_url() );
						exit;
					}
				}

				// Invalidate the current login session for the user
				self::destroy_current_session_for_user( $user );

				// Clear the cookies which are no longer valid.
				wp_clear_auth_cookie();

				if ( ! $is_user_using_two_factor ) {
					// User needs to set up 2FA
					$temp_token     = wp_generate_password( 32, false );
					$transient_name = 'secnin_2fa_temp_' . $temp_token;
					$expiration     = 30 * MINUTE_IN_SECONDS;
					set_transient( $transient_name, $user->ID, $expiration );

					self::render_2fa_verify_page( $user, $temp_token );
				} else {
					// User is using 2FA, show verification form
					self::clear_session_and_show_2fa_form( $user );
				}
				exit;
			}
		}
		// If 2FA is not enabled, the login process continues normally
	}

	/**
	 * Generate QR code for 2FA setup
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Thursday, July 20th, 2023.
	 * @access  public static
	 * @return  void
	 */
	public static function ajax_generate_qr_code() {
		if (
			! isset( $_POST['nonce'] )
			|| ! wp_verify_nonce( $_POST['nonce'], 'secnin_two_factor_auth_nonce' )
		) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid nonce.', 'security-ninja' ),
				)
			);
		}

		$user_id = 0;
		if ( isset( $_POST['temp_token'] ) ) {
			$temp_token = sanitize_text_field( $_POST['temp_token'] );
			$user_id    = get_transient( 'secnin_2fa_temp_' . $temp_token );
		} elseif ( is_user_logged_in() ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'ajax_generate_qr_code_failed', __( 'Invalid user identification.', 'security-ninja' ) );
			wp_send_json_error(
				array(
					'message' => __( 'Invalid user identification.', 'security-ninja' ),
				)
			);
		}

		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'ajax_generate_qr_code_failed', sprintf( __( 'User not found for ID: %d', 'security-ninja' ), $user_id ) );
			wp_send_json_error(
				array(
					'message' => __( 'User not found.', 'security-ninja' ),
				)
			);
		}

		$encrypted_secret = get_user_meta( $user_id, 'secnin_2fa_secret', true );

		if ( empty( $encrypted_secret ) ) {
			$secret     = ( new Da\TwoFA\Manager() )->generateSecretKey();
			$passphrase = self::get_passphrase();

			if ( empty( $secret ) || empty( $passphrase ) ) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'ajax_generate_qr_code_failed', __( 'Failed to generate or retrieve necessary security data.', 'security-ninja' ) );
				wp_send_json_error(
					array(
						'message' => __( 'Failed to generate or retrieve necessary security data.', 'security-ninja' ),
					)
				);
			}

			$encrypted_secret = self::encrypt_secret( $secret, $passphrase );

			if ( false === $encrypted_secret ) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'ajax_generate_qr_code_failed', __( 'Failed to secure the 2FA secret. Please try again.', 'security-ninja' ) );
				wp_send_json_error(
					array(
						'message' => __( 'Failed to secure the 2FA secret. Please try again.', 'security-ninja' ),
					)
				);
			}

			$update_result = update_user_meta( $user_id, 'secnin_2fa_secret', $encrypted_secret );

			if ( ! $update_result ) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'ajax_generate_qr_code_failed', __( 'Failed to save the 2FA secret. Please try again.', 'security-ninja' ) );
				wp_send_json_error(
					array(
						'message' => __( 'Failed to save the 2FA secret. Please try again.', 'security-ninja' ),
					)
				);
			}
		} else {

			$secret = self::decrypt_secret( $encrypted_secret, self::get_passphrase() );

			if ( false === $secret ) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', '2fa_secret_retrieval_failure', 'Failed to retrieve the 2FA secret for user ID ' . $user_id, array( 'user_id' => $user_id ) );
				wp_send_json_error(
					array(
						'message' => __( 'Failed to retrieve the 2FA secret. Please contact support.', 'security-ninja' ),
					)
				);
			}
		}

		$site_url    = get_bloginfo( 'url' );
		$issuer_name = wp_parse_url( $site_url, PHP_URL_HOST );

		$uri_service = new Da\TwoFA\Service\TOTPSecretKeyUriGeneratorService(
			$issuer_name,
			$user->user_email,
			$secret
		);
		$uri         = $uri_service->run();

		$qr_code        = new chillerlan\QRCode\QRCode();
		$qr_code_output = $qr_code->render( $uri );

		$data = array(
			'qr_code'    => $qr_code_output,
			'secret_key' => $secret,
		);

		wp_send_json_success( $data );
	}

	/**
	 * Adds a checkbox to bypass 2FA for administrators.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.1  Thursday, July 18th, 2024.
	 * @access  public static
	 * @param   WP_User $user The user object.
	 * @return  void
	 */
	public static function add_bypass_2fa_checkbox( $user ) {
		if ( ! $user instanceof WP_User || ! in_array( 'administrator', (array) $user->roles, true ) ) {
			return;
		}

		$bypass2fa = get_user_meta( $user->ID, 'bypass_2fa', true );
		?>
		<h3><?php esc_html_e( 'Two Factor Authentication', 'security-ninja' ); ?></h3>
		<table class="form-table">
			<tr>
				<th><label for="bypass_2fa"><?php esc_html_e( 'Bypass 2FA', 'security-ninja' ); ?></label></th>
				<td>
					<input type="checkbox" name="bypass_2fa" id="bypass_2fa" value="1" <?php checked( $bypass2fa, '1' ); ?> />
					<span class="description">
						<?php esc_html_e( 'Check this box to allow the user to bypass Two Factor Authentication.', 'security-ninja' ); ?>
					</span>
				</td>
			</tr>
		</table>
		<?php
	}

	/**
	 * save_bypass_2fa_checkbox.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Thursday, June 6th, 2024.
	 * @access  public static
	 * @global
	 * @param   mixed   $user_id
	 * @return  void
	 */
	public static function save_bypass_2fa_checkbox( $user_id ) {
		if ( ! current_user_can( 'edit_user', $user_id ) ) {
			return false;
		}

		$bypass_2fa = isset( $_POST['bypass_2fa'] ) ? sanitize_text_field( $_POST['bypass_2fa'] ) : '';
		update_user_meta( $user_id, 'bypass_2fa', $bypass_2fa );
	}

	/**
	 * Redirect if 2FA is not validated.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, June 4th, 2024.
	 * @return  void
	 */
	public static function redirect_if_2fa_not_validated() {
		if ( is_user_logged_in() ) {
			$user       = wp_get_current_user();
			$bypass_2fa = get_user_meta( $user->ID, 'bypass_2fa', true );
			if ( '1' === $bypass_2fa ) {
				// If 'bypass_2fa' is set to '1', stop execution and return
				return;
			}

			$is_session_validated = get_user_meta( $user->ID, 'secnin_2fa_session_validated', true );
			if ( $is_session_validated ) {
				return;
			}

			$my_options = self::get_options();

			if (
				isset( $my_options['2fa_enabled'] ) && true === $my_options['2fa_enabled']
				&& ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX )
			) {
				$required_roles = isset( $my_options['2fa_required_roles'] ) ? (array) $my_options['2fa_required_roles'] : array();
				if ( array_intersect( $required_roles, $user->roles ) ) {
					$secret_key           = get_user_meta( $user->ID, 'secnin_2fa_secret', true );
					$is_validated         = get_user_meta( $user->ID, 'secnin_2fa_code_validated', true );
					$is_session_validated = get_user_meta( $user->ID, 'secnin_2fa_session_validated', true );

					// Check if user is not on the 2FA setup or verification page
					if ( ! $secret_key || ( ! $is_validated && ! $is_session_validated ) ) {
						wp_safe_redirect( home_url( '/?secnin-2fa-setup=1' ) );
						exit;
					} elseif ( $secret_key && $is_validated && ! $is_session_validated ) {
						wp_safe_redirect( home_url( '/?secnin-2fa-verify=1' ) );
						exit;
					}
				}
			}
		}
	}

	/**
	 * init.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, June 4th, 2024.
	 * @access  public static
	 * @return  void
	 */
	public static function init() {
		if ( is_null( self::$options ) ) {
			self::$options = self::get_options();
		}

		add_action( 'rest_api_init', array( __CLASS__, 'register_skip_2fa_endpoint' ) );
	}

	public static function register_skip_2fa_endpoint() {
		register_rest_route(
			'security-ninja/v1',
			'/skip-2fa',
			array(
				'methods'             => 'GET',
				'callback'            => array( __CLASS__, 'handle_skip_2fa' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'temp_token' => array(
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) {
							return is_string( $param );
						},
					),
					'nonce'      => array(
						'required'          => true,
						'validate_callback' => function ( $param, $request, $key ) {
							return wp_verify_nonce( $param, 'skip_2fa_nonce' );
						},
					),
				),
			)
		);
	}

	public static function handle_skip_2fa( $request ) {
		$temp_token = $request->get_param( 'temp_token' );
		$nonce      = $request->get_param( 'nonce' );

		// Validate the temporary token and nonce
		if ( ! wp_verify_nonce( $nonce, 'skip_2fa_nonce' ) ) {
			return new \WP_Error( 'invalid_nonce', __( 'Invalid nonce', 'security-ninja' ), array( 'status' => 403 ) );
		}

		$user_id = get_transient( 'secnin_2fa_temp_' . $temp_token );

		if ( ! $user_id ) {
			return new \WP_Error( 'invalid_user', __( 'Invalid user identification', 'security-ninja' ), array( 'status' => 400 ) );
		}

		$user = get_user_by( 'ID', $user_id );

		if ( ! $user ) {
			return new \WP_Error( 'user_not_found', __( 'User not found', 'security-ninja' ), array( 'status' => 404 ) );
		}

		$my_options        = self::get_options();
		$enabled_timestamp = isset( $my_options['2fa_enabled_timestamp'] ) ? intval( $my_options['2fa_enabled_timestamp'] ) : 0;
		$grace_period      = isset( $my_options['2fa_grace_period'] ) ? intval( $my_options['2fa_grace_period'] ) : 0;
		$current_time      = time();
		$time_left         = ( $enabled_timestamp + ( $grace_period * DAY_IN_SECONDS ) ) - $current_time;

		if ( $time_left > 0 ) {
			// Allow the user to continue the login process
			wp_set_current_user( $user->ID, $user->user_login );
			wp_set_auth_cookie( $user->ID, true );
			remove_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10 );
			do_action( 'wp_login', $user->user_login, $user );
			add_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10, 2 );
			wp_safe_redirect( admin_url() );
			exit;
		} else {
			return new \WP_Error( 'grace_period_expired', __( 'The grace period has expired', 'security-ninja' ), array( 'status' => 400 ) );
		}
	}

	/**
	 * Checks if the 2FA code is validated for the current session.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.0  Tuesday, June 4th, 2024.
	 * @access  public static
	 * @param   WP_User $user The user object to check.
	 * @return  bool    True if the code is validated, false otherwise.
	 */
	public static function is_code_validated_for_session( $user ) {
		if ( ! $user instanceof WP_User ) {
			return false;
		}

		$code_validated = get_user_meta( $user->ID, 'secnin_2fa_code_validated', true );
		return ! empty( $code_validated );
	}

	/**
	 * Clears user session for 2FA when logging out.
	 *
	 * This method is called when the auth cookie is cleared, typically during logout.
	 * It removes the 2FA session validation metadata for the current user.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.1  Tuesday, June 4th, 2024.
	 * @access  public static
	 * @return  void
	 */
	public static function clear_2fa_session() {
		$user_id = get_current_user_id();
		if ( $user_id > 0 ) {
			delete_user_meta( $user_id, 'secnin_2fa_session_validated' );
		}
	}

	/**
	 * Retrieves the 2FA options.
	 *
	 * This method fetches all options and filters out only those related to 2FA.
	 * If options have already been retrieved, it returns the cached version.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.1  Tuesday, June 4th, 2024.
	 * @access  public static
	 * @return  array An array of 2FA-related options.
	 */
	public static function get_options() {
		if ( ! is_null( self::$options ) ) {
			return self::$options;
		}

		$options = Wf_sn_cf::get_options();

		$options = array_filter(
			$options,
			function ( $key ) {
				return strpos( $key, '2fa_' ) === 0;
			},
			ARRAY_FILTER_USE_KEY
		);

		self::$options = $options;
		return self::$options;
	}

	/**
	 * Retrieve or generate a passphrase for encryption.
	 *
	 * This method retrieves an existing passphrase from the WordPress options table,
	 * or generates a new one if it doesn't exist.
	 *
	 * @since   v0.0.1
	 * @version v1.0.0  Wednesday, July 17th, 2024.
	 * @access  private
	 * @return  string The retrieved or newly generated passphrase.
	 * @throws  \Exception If a secure random bytes generation fails.
	 */
	private static function get_passphrase() {
		$option_name = 'secnin_2fa_passphrase';
		$passphrase  = get_option( $option_name );

		if ( empty( $passphrase ) ) {
			try {
				// Generate a secure 256-bit passphrase.
				$random_bytes = random_bytes( 32 );
				$passphrase   = bin2hex( $random_bytes );

				$updated = update_option( $option_name, $passphrase );
				if ( ! $updated ) {
					throw new \Exception( __( 'Failed to save the generated passphrase.', 'security-ninja' ) );
				}
			} catch ( \Exception $e ) {
				// Log the error or handle it appropriately
				// Log the error using the event logger
				wf_sn_el_modules::log_event(
					'security_ninja',
					'2fa_passphrase_generation_failed',
					sprintf(
						/* translators: %s: Error message */
						__( 'Failed to generate or save 2FA passphrase: %s', 'security-ninja' ),
						$e->getMessage()
					)
				);
				throw $e; // Re-throw the exception for the caller to handle
			}
		}

		return $passphrase;
	}

	/**
	 * Encrypt a secret with IV concatenation.
	 *
	 * @param string $secret The secret to encrypt.
	 * @param string $passphrase The passphrase to use for encryption.
	 * @return string|false The concatenated IV and encrypted secret, or false on failure.
	 */
	private static function encrypt_secret( $secret, $passphrase ) {
		if ( empty( $secret ) || empty( $passphrase ) ) {
			return false;
		}

		$iv               = openssl_random_pseudo_bytes( 16 );
		$encrypted_secret = openssl_encrypt(
			$secret,
			'aes-256-cbc',
			$passphrase,
			OPENSSL_RAW_DATA,
			$iv
		);

		if ( false === $encrypted_secret ) {
			return false;
		}

		return base64_encode( $iv . $encrypted_secret );
	}

	/**
	 * Decrypt a secret with IV extraction.
	 *
	 * @param string $data The concatenated IV and encrypted secret.
	 * @param string $passphrase The passphrase to use for decryption.
	 * @return string|false The decrypted secret, or false on failure.
	 */
	private static function decrypt_secret( $data, $passphrase ) {
		if ( empty( $data ) || empty( $passphrase ) ) {
			return false;
		}

		$decoded_data = base64_decode( $data );
		if ( false === $decoded_data ) {
			return false;
		}

		if ( strlen( $decoded_data ) < 16 ) {
			return false;
		}

		$iv               = substr( $decoded_data, 0, 16 );
		$encrypted_secret = substr( $decoded_data, 16 );

		$decrypted = openssl_decrypt(
			$encrypted_secret,
			'aes-256-cbc',
			$passphrase,
			OPENSSL_RAW_DATA,
			$iv
		);

		if ( false !== $decrypted ) {
			return $decrypted;
		} else {
			return false;
		}
	}

	/**
	 * Verify the 2FA code submitted by the user.
	 *
	 * This method handles the AJAX request for verifying the 2FA code.
	 * It checks the nonce, validates the code, and logs the user in if successful.
	 *
	 * @since   v0.0.1
	 * @version v1.0.2  Friday, July 19th, 2024.
	 * @access  public static
	 * @return  void
	 */
	public static function ajax_verify_2fa_code() {
		if ( ! check_ajax_referer( 'secnin_two_factor_auth_nonce', 'nonce', false ) ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'login_2fa_verification_failed', __( 'Invalid nonce.', 'security-ninja' ) );
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'security-ninja' ) ) );
		}

		$user_id = isset( $_POST['user_id'] ) ? absint( $_POST['user_id'] ) : 0;
		$code    = isset( $_POST['code'] ) ? sanitize_text_field( $_POST['code'] ) : '';

		if ( ! $user_id || empty( $code ) ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event( 'security_ninja', 'login_2fa_verification_failed', __( 'Missing user ID or code.', 'security-ninja' ) );
			wp_send_json_error( array( 'message' => __( 'Missing required information.', 'security-ninja' ) ) );
		}

		$encrypted_secret = get_user_meta( $user_id, 'secnin_2fa_secret', true );

		if ( empty( $encrypted_secret ) ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
				'security_ninja',
				'login_2fa_verification_failed',
				sprintf(
					/* translators: %d: user ID */
					__( 'User secret is missing for user ID: %d', 'security-ninja' ),
					$user_id
				)
			);
			wp_send_json_error( array( 'message' => __( 'User secret is missing.', 'security-ninja' ) ) );
		}

		$secret = self::decrypt_secret( $encrypted_secret, self::get_passphrase() );
		if ( false === $secret ) {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
				'security_ninja',
				'login_2fa_verification_failed',
				sprintf(
					/* translators: %d: user ID */
					__( 'Failed to decrypt 2FA secret for user ID: %d', 'security-ninja' ),
					$user_id
				)
			);
			wp_send_json_error( array( 'message' => __( 'Failed to retrieve the 2FA secret. Please contact support.', 'security-ninja' ) ) );
		}

		$manager = new Da\TwoFA\Manager();
		if ( $manager->verify( $code, $secret ) ) {
			update_user_meta( $user_id, 'secnin_2fa_code_validated', 1 );
			update_user_meta( $user_id, 'secnin_2fa_session_validated', time() );
			update_user_meta( $user_id, 'secnin_2fa_setup_complete', 1 );

			$user_data = get_userdata( $user_id );
			if ( false === $user_data ) {
				\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
					'security_ninja',
					'login_2fa_verification_failed',
					sprintf(
						/* translators: %d: user ID */
						__( 'Failed to retrieve user data for user ID: %d', 'security-ninja' ),
						$user_id
					)
				);
				wp_send_json_error( array( 'message' => __( 'Failed to retrieve user data.', 'security-ninja' ) ) );
				return;
			}

			wp_set_current_user( $user_id, $user_data->user_login );
			wp_set_auth_cookie( $user_id, true );
			remove_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10 );
			do_action( 'wp_login', $user_data->user_login, $user_data );
			add_action( 'wp_login', array( __CLASS__, 'wp_login' ), 10, 2 );

			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
				'security_ninja',
				'login_2fa_verification_successful',
				sprintf(
					/* translators: %s: user display name */
					__( '2FA verification successful for %s', 'security-ninja' ),
					$user_data->display_name
				)
			);

			$redirect_to = apply_filters( 'login_redirect', admin_url(), '', $user_data );

			wp_send_json_success(
				array(
					'message'  => __( 'Verified. Logging you in.', 'security-ninja' ),
					'redir_to' => esc_url( $redirect_to ),
				)
			);
		} else {
			\WPSecurityNinja\Plugin\wf_sn_el_modules::log_event(
				'security_ninja',
				'login_2fa_verification_failed',
				sprintf(
					/* translators: %d: user ID */
					__( 'Incorrect 2FA code entered for user: %d', 'security-ninja' ),
					$user_id
				)
			);
			wp_send_json_error(
				array(
					'message' => __( 'Incorrect 2FA code. Please try again.', 'security-ninja' ),
				)
			);
		}
	}

	/**
	 * Checks if 2FA setup is complete for a user.
	 *
	 * @param int $user_id The ID of the user to check.
	 * @return bool True if 2FA setup is complete, false otherwise.
	 */
	public static function is_2fa_setup_complete( $user_id ) {
		if ( ! is_numeric( $user_id ) || $user_id <= 0 ) {
			return false;
		}

		$setup_complete = get_user_meta( $user_id, 'secnin_2fa_setup_complete', true );
		return '1' === $setup_complete;
	}

	/**
	 * Renders the 2FA verification page.
	 *
	 * @author  Lars Koudal
	 * @since   v0.0.1
	 * @version v1.0.1  Tuesday, June 4th, 2024.
	 * @access  public static
	 * @param   int $user_id The ID of the user to verify.
	 * @return  void
	 */
	public static function render_2fa_verify_page( $user_id ) {

		if ( ! $user_id ) {
			return;
		}

		// Check if $user_id is an int value or a user object
		if ( $user_id instanceof WP_User ) {
			$user_id = $user_id->ID;
		} elseif ( is_numeric( $user_id ) ) {
			$user_id = absint( $user_id );
		} else {
			wp_die( esc_html__( 'Invalid user ID or object.', 'security-ninja' ) );
		}
		if ( ! get_userdata( $user_id ) ) {
			wp_die( esc_html__( 'Invalid user ID.', 'security-ninja' ) );
		}

		$script_url = plugins_url( '/js/two-factor-auth.js', __FILE__ );
		$ajax_url   = admin_url( 'admin-ajax.php' );
		$nonce      = wp_create_nonce( 'secnin_two_factor_auth_nonce' );
		$temp_token = wp_generate_password( 32, false );
		$user_ip    = $_SERVER['REMOTE_ADDR'];
		$user_agent = $_SERVER['HTTP_USER_AGENT'];

		// Store the temp token in a transient, associated with the user
		set_transient( 'secnin_2fa_temp_' . $temp_token, $user_id, 30 * MINUTE_IN_SECONDS );

		// Check if the user has completed 2FA setup
		$user_2fa_setup_complete = self::is_2fa_setup_complete( $user_id );

		?>
		<!DOCTYPE html>
		<html <?php language_attributes(); ?>>

		<head>
			<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ); ?>; charset=<?php bloginfo( 'charset' ); ?>" />
			<title><?php esc_html_e( '2FA Verification', 'security-ninja' ); ?></title>
			<?php
			wp_enqueue_style( 'login' );
			wp_enqueue_script( 'jquery' );
			do_action( 'login_enqueue_scripts' );
			do_action( 'login_head' );
			?>
			<style>
				.spinner {
					background: url(<?php echo esc_url( admin_url( 'images/spinner.gif' ) ); ?>) no-repeat;
					background-size: 20px 20px;
					display: none;
					float: right;
					opacity: 0.7;
					width: 20px;
					height: 20px;
					margin: 5px 5px 0;
				}

				.spinner.is-active {
					display: inline-block;
				}

				.qr-code {
					text-align: center;
					margin-bottom: 20px;
				}

				#qr-code-img {
					display: inline-block;
					max-width: 100%;
					height: auto;
				}

				.errmsg {
					color: #dc3232;
					font-weight: bold;
				}

				.okmsg {
					color: #46b450;
					font-weight: bold;
				}

				#verify-2fa:disabled {
					opacity: 0.5;
					cursor: not-allowed;
				}
			</style>
			<script>
				var two_factor_auth = {
					ajaxurl: '<?php echo esc_js( $ajax_url ); ?>',
					nonce: '<?php echo esc_js( $nonce ); ?>',
					temp_token: '<?php echo esc_js( $temp_token ); ?>',
					user_id: '<?php echo esc_js( $user_id ); ?>',
					user_ip: '<?php echo esc_js( $user_ip ); ?>',
					user_agent: '<?php echo esc_js( $user_agent ); ?>'
				};
			</script>
			<script src="<?php echo esc_url( $script_url ); ?>"></script>
		</head>

		<body class="login js login-action-2fa_verification wp-core-ui">
			<div id="login">

				<?php if ( ! $user_2fa_setup_complete ) : ?>
					<div class="qr-code">
						<img id="qr-code-img" src="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='40' height='40'%3E%3Crect width='100%' height='100%' fill='%23ffffff' stroke='%23eee' stroke-width='1'/%3E%3C/svg%3E" alt="<?php esc_attr_e( 'QR Code Loading', 'security-ninja' ); ?>" style="display: inline-block; width: 200px; height: 200px;" />
						<div id="qr-code-img-err"></div>
						<div class="spinner"></div>
						<button id="generate-qr" class="button button-secondary"><?php esc_html_e( 'Generate New QR Code', 'security-ninja' ); ?></button>
					</div>
				<?php endif; ?>

				<form name="twofa-form-verify" id="twofa-form-verify" action="" method="post">
					<p>
						<label for="twofa-code"><?php esc_html_e( 'Enter the 6-digit code from your authentication app.', 'security-ninja' ); ?></label>
						<input type="text" name="twofa-code" id="twofa-code" class="input" value="" size="6" maxlength="6" pattern="\d{6}" inputmode="numeric" autocapitalize="off" autocomplete="off" data-1p-ignore required />
					</p>
					<div id="twofa-verify-msg"></div>
					<p class="submit">
						<input type="submit" name="wp-submit" id="verify-2fa" class="button button-primary button-large" value="<?php esc_attr_e( 'Verify Code', 'security-ninja' ); ?>" disabled />
					</p>

					<?php

					$my_options        = self::get_options();
					$grace_period      = isset( $my_options['2fa_grace_period'] ) ? intval( $my_options['2fa_grace_period'] ) : 0;
					$enabled_timestamp = isset( $my_options['2fa_enabled_timestamp'] ) ? intval( $my_options['2fa_enabled_timestamp'] ) : 0;

					if ( 0 === $enabled_timestamp ) {
						$enabled_timestamp                   = time();
						$my_options['2fa_enabled_timestamp'] = $enabled_timestamp;
						update_option( 'wf_sn_cf', $my_options ); // Save the updated options
					}
					$current_time = time();
					$time_left = ( $enabled_timestamp + ( $grace_period * DAY_IN_SECONDS ) ) - $current_time;

					if ( $grace_period > 0 && $time_left > 0 ) :
						$days_left  = floor( $time_left / 86400 );
						$hours_left = floor( ( $time_left % 86400 ) / 3600 );
						?>
						<p class="grace-period-notice">
							<?php
							printf(
								/* translators: %1$d: number of days left, %2$d: number of hours left */
								esc_html__( 'You have %1$d days and %2$d hours left to set up 2FA. ', 'security-ninja' ),
								esc_html( $days_left ),
								esc_html( $hours_left )
							);
							?>
							<a href="<?php echo esc_url( rest_url( 'security-ninja/v1/skip-2fa?temp_token=' . $temp_token . '&nonce=' . wp_create_nonce( 'skip_2fa_nonce' ) ) ); ?>">
								<?php esc_html_e( 'Skip for now', 'security-ninja' ); ?>
							</a>
						</p>
					<?php endif; ?>
				</form>

				<p id="backtoblog">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php
						/* translators: %s: Site title. */
						printf( esc_html_x( '&larr; Back to %s', 'security-ninja' ), esc_html( get_bloginfo( 'title', 'display' ) ) );
						?>
					</a>
				</p>
			</div>

			<?php
			do_action( 'login_footer' );
			?>
			<div class="clear"></div>
			<script>
						document.addEventListener('DOMContentLoaded', function() {
							document.getElementById('twofa-code').focus();
				});
			</script>
		</body>

		</html>
		<?php
	}


	/**
	 * Collect authentication cookies for later removal.
	 *
	 * This method parses the provided cookie string and stores the authentication
	 * token if present. These tokens will be used later to invalidate sessions
	 * during the two-factor authentication process.
	 *
	 * @since 1.0.0
	 *
	 * @param string $cookie The authentication cookie string.
	 * @return void
	 */
	public static function collect_auth_cookie_tokens( $cookie ) {
		if ( ! is_string( $cookie ) || empty( $cookie ) ) {
			return;
		}

		$parsed = wp_parse_auth_cookie( $cookie );
		if ( is_array( $parsed ) && ! empty( $parsed['token'] ) ) {
			self::$password_auth_tokens[] = $parsed['token'];
		}
	}


	/**
	 * Destroy the known password-based authentication sessions for the current user.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_User $user User object.
	 * @return void
	 */
	public static function destroy_current_session_for_user( $user ) {
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		$session_manager = \WP_Session_Tokens::get_instance( $user->ID );

		if ( ! empty( self::$password_auth_tokens ) ) {
			foreach ( self::$password_auth_tokens as $auth_token ) {
				if ( ! empty( $auth_token ) && is_string( $auth_token ) ) {
					$session_manager->destroy( $auth_token );
				}
			}
		}
	}








	/**
	 * Clears current user session, displays a 2FA verification form, and terminates the request.
	 *
	 * This method invalidates the current login session, clears authentication cookies,
	 * renders the 2FA verification page, and then exits the script execution.
	 *
	 * @since 1.0.0
	 *
	 * @param \WP_User $user WordPress user object.
	 * @return void
	 */
	private static function clear_session_and_show_2fa_form( $user ) {
		if ( ! $user instanceof \WP_User ) {
			return;
		}

		// Invalidate the current login session to prevent it from being re-used.
		self::destroy_current_session_for_user( $user );

		// Clear the authentication cookies which are no longer valid.
		wp_clear_auth_cookie();

		self::render_2fa_verify_page( $user->ID );
		exit;
	}



	/**
	 * Check if the user is using two-factor authentication.
	 *
	 * This method checks if a user has a 2FA secret key set in their user meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user to check.
	 * @return bool True if the user has a 2FA secret key set, false otherwise.
	 */
	private static function is_user_using_two_factor( $user_id ) {
		if ( ! is_numeric( $user_id ) || $user_id <= 0 ) {
			return false;
		}

		$secret_key = get_user_meta( $user_id, 'secnin_2fa_secret', true );
		return ! empty( $secret_key );
	}
	/**
	 * Check if the user should be using two-factor authentication.
	 *
	 * This method determines whether a user should be required to use 2FA based on their
	 * bypass status and role.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id The ID of the user to check.
	 * @return bool True if the user should use 2FA, false otherwise.
	 */
	private static function should_user_use_two_factor( $user_id ) {
		if ( ! is_numeric( $user_id ) || $user_id <= 0 ) {
			return false;
		}

		$bypass_2fa = get_user_meta( $user_id, 'bypass_2fa', true );
		if ( '1' === $bypass_2fa ) {
			return false;
		}

		$user = get_userdata( $user_id );
		if ( ! $user || ! $user instanceof \WP_User ) {
			return false;
		}

		$my_options     = self::get_options();
		$required_roles = isset( $my_options['2fa_required_roles'] ) ? (array) $my_options['2fa_required_roles'] : array();

		return ! empty( array_intersect( $required_roles, $user->roles ) );
	}
}

\WPSecurityNinja\Plugin\wf_sn_2fa::init();
new \WPSecurityNinja\Plugin\wf_sn_2fa();
