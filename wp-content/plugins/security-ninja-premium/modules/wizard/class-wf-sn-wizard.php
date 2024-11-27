<?php

namespace WPSecurityNinja\Plugin;
use WPSecurityNinja\Plugin\Wf_Sn;
use WPSecurityNinja\Plugin\Wf_Sn_Cf;
use WPSecurityNinja\Plugin\Wf_Sn_Wl;
use WPSecurityNinja\Plugin\Wf_Sn_Tests;
use WPSecurityNinja\Plugin\Wf_Sn_Utils;
use WPSecurityNinja\Plugin\Wf_Sn_El;


/**
 * Wizard class for Security Ninja plugin.
 *
 * This class handles the setup wizard functionality for the Security Ninja plugin.
 * It's based on the Whizzie package but has been heavily customized.
 *
 * @package WPSecurityNinja\Plugin
 * @since   0.0.1
 * @version 1.0.0
 */
class Wf_Sn_Wiz {

    /**
     * The version of the wizard.
     *
     * @var string
     */
    protected static $version = '1.2.0';

    /**
     * The slug for the wizard page.
     *
     * @var string
     */
    protected static $page_slug = 'security-ninja-wizard';

    /**
     * The title for the wizard page.
     *
     * @var string
     */
    protected static $page_title = 'Security Ninja Wizard';

    /**
     * An array of wizard steps set by the user.
     *
     * @var array
     */
    protected static $config_steps = array();

    /**
     * The relative plugin URL for this plugin folder.
     *
     * @var string
     */
    protected static $plugin_url = '';

    /**
     * Add the wizard page to the Security Ninja submenu.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function welcome_screen_page() {
        $plugin_name = 'Security Ninja';
        if ( secnin_fs()->is__premium_only() ) {
            if ( secnin_fs()->can_use_premium_code() ) {
                // If whitelabel enabled
                if ( class_exists( __NAMESPACE__ . '\wf_sn_wl' ) ) {
                    if ( wf_sn_wl::is_active() ) {
                        $plugin_name = wf_sn_wl::get_new_name();
                    }
                }
            }
        }

        add_submenu_page(
            'wf-sn',
            sprintf( __( '%s Install Wizard', 'security-ninja' ), $plugin_name ),
            __( 'Get started', 'security-ninja' ),
            'manage_options',
            'security-ninja-wizard',
            array( __NAMESPACE__ . '\\Wf_Sn_Wiz', 'welcome_page' )
        );
    }

    /**
     * Check if a string ends with a given substring.
     *
     * @since  0.0.1
     * @access public static
     * @param  string $haystack The string to search in.
     * @param  string $needle   The substring to search for.
     * @return bool             True if the string ends with the substring, false otherwise.
     */
    public static function str_ends_with( $haystack, $needle ) {
        if ( '' === $haystack && '' !== $needle ) {
            return false;
        }
        $len = strlen( $needle );
        return 0 === substr_compare( $haystack, $needle, -$len, $len );
    }

    /**
     * Initialize hooks and filters for the wizard.
     *
     * This method sets up the necessary WordPress hooks and filters
     * to handle AJAX actions and enqueue scripts for the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function init() {
        add_action( 'wp_ajax_secnin_activate_firewall', array( __NAMESPACE__ . '\\Wf_Sn_Wiz', 'activate_firewall' ) );
        add_action( 'wp_ajax_secnin_activate_default_fixes', array( __NAMESPACE__ . '\\Wf_Sn_Wiz', 'activate_default_fixes' ) );
        add_action( 'wp_ajax_secnin_finish_security_tests', array( __NAMESPACE__ . '\\Wf_Sn_Wiz', 'finish_security_tests' ) );
        add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\\Wf_Sn_Wiz', 'enqueue_scripts' ) );
    }

    /**
     * Render the wizard page.
     *
     * This method generates the HTML for the wizard page, including the steps
     * and the navigation menu. It also handles the file system credentials
     * request if necessary.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function wizard_page() {
        $url = wp_nonce_url( add_query_arg( array( 'plugins' => 'go' ) ), 'whizzie-setup' );

        // copied from TGM
        $method = ''; // Leave blank so WP_Filesystem can populate it as necessary.
        $fields = array_keys( $_POST ); // Extra fields to pass to WP_Filesystem.
        $creds  = request_filesystem_credentials( esc_url_raw( $url ), $method, false, false, $fields );
        if ( false === ( $creds ) ) {
            return true; // Stop the normal page form from displaying, credential request form will be shown.
        }
        // Now we have some credentials, setup WP_Filesystem.
        if ( ! WP_Filesystem( $creds ) ) {
            // Our credentials were no good, ask the user for them again.
            request_filesystem_credentials( esc_url_raw( $url ), $method, true, false, $fields );
            return true;
        }
        /* If we arrive here, we have the filesystem */ ?>

        <div class="wrap">
            <?php
            wf_sn::show_topbar();

            echo '<div class="card whizzie-wrap">';
            // The wizard is a list with only one item visible at a time
            $steps = self::get_steps();
            echo '<ul class="whizzie-menu">';
            $allowed_html = wp_kses_allowed_html( 'post' );

            foreach ( $steps as $step ) {
                $class = 'step step-' . esc_attr( $step['id'] );
                echo '<li data-step="' . esc_attr( $step['id'] ) . '" class="' . esc_attr( $class ) . '">';
                printf( '<h2>%s</h2>', esc_html( $step['title'] ) );
                // $content is split into summary and detail
                $content = call_user_func( array( __NAMESPACE__ . '\Wf_Sn_Wiz', $step['view'] ) );

                if ( isset( $content['summary'] ) ) {
                    printf(
                        '<div class="summary">%s</div>',
                        wp_kses( $content['summary'], $allowed_html )
                    );
                }

                // Show newsletter
                if ( isset( $step['newsletter'] ) && ( $step['newsletter'] ) ) {
                    $current_user    = wp_get_current_user();
                    $show_newsletter = true;

                    if ( secnin_fs()->is__premium_only() ) {
                        if ( secnin_fs()->can_use_premium_code() ) {
                            // If whitelabel enabled
                            if ( class_exists( __NAMESPACE__ . '\wf_sn_wl' ) ) {
                                if ( wf_sn_wl::is_active() ) {
                                    $show_newsletter = false;
                                }
                            }
                        }
                    }

                    if ( $show_newsletter ) {
                        echo '<div class="wizardnewsletter">';
                        echo '<h3>' . esc_html__( 'Join the newsletter', 'security-ninja' ) . '</h3>';
                        echo '<h4>' . esc_html__( 'Updates about the plugin and important WordPress security in general', 'security-ninja' ) . '</h4>';
                        echo '<p>' . esc_html__( 'Please note: The newsletter is currently available only in English.', 'security-ninja' ) . '</p>'; // Added note about language

                        echo '<form class="ml-block-form" action="' . esc_url( 'https://assets.mailerlite.com/jsonp/16490/forms/106309154087372203/subscribe' ) . '" method="post" target="_blank">';
                        echo '<table>
                                <tbody>
                                    <tr>
                                        <td>
                                            <input type="text" class="regular-text" name="fields[name]" placeholder="' . esc_attr__( 'Name', 'security-ninja' ) . '" autocomplete="name" style="width:15em;" value="' . esc_html( $current_user->display_name ) . '">
                                        </td>
                                        <td>
                                            <input aria-label="email" aria-required="true" type="email" class="regular-text required email" name="fields[email]" placeholder="' . esc_attr__( 'Email', 'security-ninja' ) . '" autocomplete="email" style="width:15em;" value="' . esc_html( $current_user->user_email ) . '" required="required">
                                        </td>
                                        <td>
                                            <button type="submit" class="button button-primary button-small">' . esc_html__( 'Subscribe', 'security-ninja' ) . '</button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>';

                        echo '<input type="hidden" name="fields[signupsource]" value="' . esc_attr( sprintf( 'Plugin Wizard v.%s', Wf_Sn::get_plugin_version() ) ) . '">
                              <input type="hidden" name="ml-submit" value="1">
                              <input type="hidden" name="anticsrf" value="true">
                        </form>';

                        echo '<p>' . esc_html__( 'You can unsubscribe anytime. For more details, review our', 'security-ninja' ) . ' <a href="' . esc_url( 'https://wpsecurityninja.com/privacy-policy/' ) . '" target="_blank" rel="noopener">' . esc_html__( 'Privacy Policy', 'security-ninja' ) . '</a>.</p>';
                        echo '</div>';
                    }
                }

                // The next button
                if ( isset( $step['button_text'] ) && $step['button_text'] ) {
                    $button_class = '';
                    if ( isset( $step['button_class'] ) ) {
                        $button_class = $step['button_class'];
                    }
                    printf(
                        '<div class="button-wrap"><a href="#" class="button button-hero button-primary do-it %s" data-callback="%s" data-step="%s">%s</a></div>',
                        esc_attr( $button_class ),
                        esc_attr( $step['callback'] ),
                        esc_attr( $step['id'] ),
                        esc_html( $step['button_text'] )
                    );
                }
                // The skip button
                if ( isset( $step['can_skip'] ) && $step['can_skip'] ) {
                    printf(
                        '<div class="button-wrap" style="margin-left: 0.5em;"><a href="#" class="button button-secondary button-hero do-it" data-callback="%s" data-step="%s">%s</a></div>',
                        'do_next_step',
                        esc_attr( $step['id'] ),
                        __( 'Skip', 'security-ninja' )
                    );
                }
                echo '</li>';
            }
            echo '</ul>';
            echo '<hr>';
            echo '<ul class="whizzie-nav">';

            foreach ( $steps as $step ) {
                if ( isset( $step['icon'] ) && $step['icon'] ) {
                    if ( isset( $step['skip_icon'] ) && $step['skip_icon'] ) {
                        $iconout = '';
                    } else {
                        $iconout = '<span class="dashicons dashicons-' . esc_attr( $step['icon'] ) . '"></span>';
                    }
                    echo '<li class="nav-step-' . esc_attr( $step['id'] ) . '">' . $iconout . '<span class="titleshort">' . esc_html( $step['title_short'] ) . '</span></li>';
                }
            }
            echo '</ul>';
            ?>
            <div class="step-loading"><span class="spinner"></span></div>
        </div>
        <div class="secnin-wizard-warning">
            <p><small><?php esc_html_e( 'Warning: Rerunning this wizard will overwrite your old settings.', 'security-ninja' ); ?></small></p>
        </div>
					</div>
        <?php
    }

    /**
     * Get the steps for the wizard.
     *
     * This method returns an array of steps for the wizard, including their
     * configuration and options. It also allows for customization of the
     * steps by the theme developer.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array of wizard steps.
     */
    public static function get_steps() {
        $dev_steps = self::$config_steps;

        $plugin_name = 'Security Ninja';
        if ( secnin_fs()->is__premium_only() ) {
            if ( secnin_fs()->can_use_premium_code() ) {
                // If whitelabel enabled
                if ( class_exists( __NAMESPACE__ . '\wf_sn_wl' ) ) {
                    if ( wf_sn_wl::is_active() ) {
                        $plugin_name = wf_sn_wl::get_new_name();
                    }
                }
            }
        }

        $steps = array(
            'intro'          => array(
                'id'           => 'intro',
                'detail' => '###TEST###',
                'title'        => __( 'Thank you for choosing', 'security-ninja' ) . ' ' . esc_attr( $plugin_name ) . ' &hearts;',
                'title_short'  => __( 'Welcome', 'security-ninja' ),
                'icon'         => 'dashboard',
                'view'         => 'get_step_intro', // Callback for content
                'callback'     => 'do_next_step', // Callback for JS
                'button_text'  => __( 'Start Wizard', 'security-ninja' ),
                'button_class' => ' button-hero', // extra CSS - optional
                'can_skip'     => false, // Show a skip button?
            ),
            'firewall'       => array(
                'id'          => 'firewall',
                'title'       => __( 'Activate Firewall Protection', 'security-ninja' ),
                'title_short' => __( 'Firewall', 'security-ninja' ),
                'icon'        => 'shield',
                'view'        => 'get_step_firewall',
                'callback'    => 'activate_firewall',
                'button_text' => __( 'Activate', 'security-ninja' ),
                'can_skip'    => true,
            ),
            'default_fixes'  => array(
                'id'          => 'default_fixes',
                'title'       => __( 'Activate Default Security Measures', 'security-ninja' ),
                'title_short' => __( 'Fixes', 'security-ninja' ),
                'icon'        => 'admin-plugins',
                'view'        => 'get_step_default_fixes',
                'callback'    => 'activate_default_fixes',
                'button_text' => __( 'Activate', 'security-ninja' ),
                'can_skip'    => true,
            ),
            'security_tests' => array(
                'id'          => 'security_tests',
                'title'       => __( 'Thank you - All done ', 'security-ninja' ) . ' &hearts;',
                'title_short' => __( 'Done', 'security-ninja' ),
                'icon'        => 'yes-alt',
                'view'        => 'get_step_security_tests',
                'newsletter'  => true,
                'can_skip'    => false,
            ),
        );

        // Iterate through each step and replace with dev config values
        if ( $dev_steps ) {
            // Configurable elements - these are the only ones the dev can update from config.php
            $can_config = array( 'title', 'icon', 'button_text', 'can_skip' );
            foreach ( $dev_steps as $dev_step ) {
                // We can only proceed if an ID exists and matches one of our IDs
                if ( isset( $dev_step['id'] ) ) {
                    $id = $dev_step['id'];
                    if ( isset( $steps[ $id ] ) ) {
                        foreach ( $can_config as $element ) {
                            if ( isset( $dev_step[ $element ] ) ) {
                                $steps[ $id ][ $element ] = $dev_step[ $element ];
                            }
                        }
                    }
                }
            }
        }
        return $steps;
    }

    /**
     * Get the content for the intro step.
     *
     * This method returns an array containing the summary and detail content
     * for the intro step of the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array containing the summary and detail content.
     */
    public static function get_step_intro() {
        $content = array();

        $direct_link = admin_url( 'admin.php?page=wf-sn' );

        $content['summary'] = '<h3>' . __( 'Use this wizard to get you up and running in a few minutes.', 'security-ninja' ) . '</h3>';

        $content['summary'] .= '<p>' . __( 'This wizard sets up standard best security practices for your website.', 'security-ninja' ) . '</p>';

        $content['summary'] .= '<p>' . sprintf(
            __( 'Click the button to get started or %sclick here to go to the dashboard%s.', 'security-ninja' ),
            '<a href="' . esc_url( $direct_link ) . '">',
            '</a>'
        ) . '</p>';
        return $content;
    }

    /**
     * Get the content for the default fixes step.
     *
     * This method returns an array containing the summary and detail content
     * for the default fixes step of the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array containing the summary and detail content.
     */
    public static function get_step_default_fixes() {
        $content            = array();
        $content['summary'] = '<p>' . __( 'Additional protection measures for your website.', 'security-ninja' ) . '</p>';

        $content['summary'] .= '<p>' . __( "Strengthen your website's security with our comprehensive suite of protective measures, designed to guard against common vulnerabilities and ensure your site's integrity.", 'security-ninja' ) . '</p><p>' . __( "Our security features include concealing version information, removing unnecessary files, and implementing Secure Cookies along with essential security headers. These enhancements help defend your site against various cyber threats, keeping it safe and secure.", 'security-ninja' ) . '</p>';

        $url = Utils::generate_sn_web_link( 'install_wizard', 'docs/installation-and-usage/activate-default-security-measures/' );

        if ( $url ) {
            $content['summary'] .= '<p><a href="' . esc_url( $url ) . '" target="_blank" rel="noopener">' . __( 'Learn more about the default security measures', 'security-ninja' ) . '</a></p>';
        }

        return $content;
    }

    /**
     * Get the content for the firewall step.
     *
     * This method returns an array containing the summary and detail content
     * for the firewall step of the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array containing the summary and detail content.
     */
    public static function get_step_firewall() {
        $content = array();

        $content['summary'] = '<p>' . __( 'The firewall protects you against hack attempts, blocks known spammers and malicious IPs.', 'security-ninja' ) . '</p>';

        $content['summary'] .= '<ul class="summarylist"><li>' . __( 'Enable the Cloud Firewall list of 600 million+ bad IPs. The list is updated daily from many sources.', 'security-ninja' ) . '</li>
        <li>' . __( 'Block suspicious queries (probing for security holes)', 'security-ninja' ) . '</li>
        <li>' . __( 'Protect your login form from mass attacks', 'security-ninja' ) . '</li>		
        <li>' . __( 'Enable the Block IP network - automatically block IPs trying to hack other websites', 'security-ninja' ) . '</li></ul>';

        $content['summary'] .= '<div class="attbox">' . __( 'In case you ever get locked out - Save this URL:', 'security-ninja' ) . '</br>';
        $unblock_url         = wf_sn_cf::get_unblock_url();
        $content['summary'] .= '<pre>' . esc_url( $unblock_url ) . '</pre>';
        $content['summary'] .= __( 'Visiting that URL will unblock your IP and allow you to log in.', 'security-ninja' ) . '</br></div>';

        return $content;
    }

    /**
     * Get the content for the security tests step.
     *
     * This method returns an array containing the summary and detail content
     * for the security tests step of the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array containing the summary and detail content.
     */
    public static function get_step_security_tests() {
        $content = array();

        $content['summary'] = '<p>' . __( 'Great, you have now finished the getting started wizard!', 'security-ninja' ) . '</p>';

        $content['summary'] .= '<p>' . __( 'Your website is now protected, but there are many more features to secure your website even further.', 'security-ninja' ) . '</p>';

        // Translators: %1$s is the documentation link, %2$s is the support link.
        $content['summary'] .= '<p>' . sprintf(
            esc_html__( 'Visit our %1$s or %2$s if you have any problems.', 'security-ninja' ),
            '<a href="https://wpsecurityninja.com/docs/" target="_blank" rel="noopener">' . esc_html__( 'Documentation', 'security-ninja' ) . '</a>',
            '<a href="https://wpsecurityninja.com/help/" target="_blank" rel="noopener">' . esc_html__( 'Reach out to our support', 'security-ninja' ) . '</a>'
        ) . '</p>';

        $secninlink = admin_url( 'admin.php?page=wf-sn' );

        $content['summary'] .= '<p><a href="' . esc_url( $secninlink ) . '" class="button button-primary">' . __( 'Finish Wizard', 'security-ninja' ) . '</a></p>';

        return $content;
    }

    /**
     * Enqueue CSS and JS scripts for the wizard.
     *
     * This method enqueues the necessary CSS and JS scripts for the wizard
     * page, including the localized parameters.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function enqueue_scripts() {
        global $current_screen;
        $needle = 'page_security-ninja-wizard';

        // Checks if we are on the wizard page, otherwise we leave
        if ( ! self::str_ends_with( $current_screen->id, $needle ) ) {
            return;
        }

        wp_register_script(
            'secnin-wizard',
            WF_SN_PLUGIN_URL . 'modules/wizard/assets/js/secnin-wizard-min.js',
            array( 'jquery' ),
            wf_sn::$version,
            true
        );

        $js_vars = array(
            'nonce'         => wp_create_nonce( 'secnin_wizard_nonce' ),
            'sn_plugin_url' => WF_SN_PLUGIN_URL,
        );

        // If the test class is loaded
        if ( class_exists( __NAMESPACE__ . '\wf_sn_tests' ) ) {
            $js_vars['security_tests'] = wf_sn_tests::return_security_tests();
        }

        wp_localize_script( 'secnin-wizard', 'whizzie_params', $js_vars );

        wp_enqueue_script( 'secnin-wizard' );
    }

    /**
     * Get the content for the done step.
     *
     * This method returns an array containing the summary and detail content
     * for the done step of the wizard.
     *
     * @since  0.0.1
     * @access public static
     * @return array An array containing the summary and detail content.
     */
    public static function get_step_done() {
        $content = array();
        // The summary element will be the content visible to the user
        $content['summary'] = '<h3>' . esc_html__( 'All done!', 'security-ninja' ) . '</h3>';

        $direct_link = admin_url( 'admin.php?page=wf-sn' );

        $content['summary'] .= '<p>' . sprintf(
            /* translators: %s: Link to Security Ninja dashboard */
            esc_html__( 'Click the button to get started or %s', 'security-ninja' ),
            '<a href="' . esc_url( $direct_link ) . '" class="button button-hero button-primary">' . esc_html__( 'Security Ninja', 'security-ninja' ) . '</a>'
        ) . '</p>';

        return $content;
    }

    /**
     * Activate the firewall and set default settings.
     *
     * This method activates the firewall and sets the default settings
     * for the Security Ninja plugin. It also sends the unblock URL to the
     * current user's email address.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function activate_firewall() {
        check_ajax_referer( 'secnin_wizard_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed.', 'security-ninja' ),
                )
            );
        }

        $default_firewall_options = wf_sn_cf::get_options();
        // Tweak the default settings
        $default_firewall_options['active']            = 1;
        $default_firewall_options['global']            = 1;
        $default_firewall_options['filterqueries']     = 1;
        $default_firewall_options['usecloud']          = 1;
        $default_firewall_options['globalbannetwork']  = 1;
        $default_firewall_options['blockadminlogin']   = 0;
        $default_firewall_options['trackvisits']       = 0;
        $default_firewall_options['blocked_countries'] = '';

        update_option( WF_SN_CF_OPTIONS_KEY, $default_firewall_options, false );

        if ( class_exists( __NAMESPACE__ . '\Wf_sn_cf' ) ) {
            // Sends password to current user admin email address.
            $current_user = wp_get_current_user();
            Wf_sn_cf::send_secret_access_unblock_url( $current_user->user_email );
        }

        $results = array(
            'done'    => 1,
            'message' => __( 'Firewall enabled.', 'security-ninja' ),
        );

        wp_send_json_success( $results );
        exit;
    }

    /**
     * Finish the security tests.
     *
     * This method is called when the security tests step is completed.
     * It sends a JSON response indicating that the tests are done.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function finish_security_tests() {
        check_ajax_referer( 'secnin_wizard_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed.', 'security-ninja' ),
                )
            );
        }

        $results = array(
            'done'    => 1,
            'message' => __( 'Security Tests done', 'security-ninja' ),
        );

        wp_send_json_success( $results );
        exit;
    }

    /**
     * Mark the wizard as all done.
     *
     * This method is called when the wizard is completed.
     * It sends a JSON response indicating that the wizard is done.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function all_done() {
        check_ajax_referer( 'secnin_wizard_nonce' );

        $results = array(
            'done'    => 1,
            'message' => __( 'All finished.', 'security-ninja' ),
        );
        wp_send_json_success( $results );
    }

    /**
     * Activate the default fixes.
     *
     * This method activates the default fixes for the Security Ninja plugin.
     * It sets the default options for the fixes and enables automatic
     * background updates for the plugin.
     *
     * @since  0.0.1
     * @access public static
     * @return void
     */
    public static function activate_default_fixes() {
        check_ajax_referer( 'secnin_wizard_nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed.', 'security-ninja' ),
                )
            );
        }

        $default_fixes_options = Wf_Sn_Fixes::get_options();
        // Tweak the default settings
        $default_fixes_options['disable_username_enumeration'] = 1;
        $default_fixes_options['hide_wp']                      = 1;
        $default_fixes_options['hide_wlw']                     = 1;
        $default_fixes_options['hide_php_ver']                 = 1;
        $default_fixes_options['hide_wp_debug']                = 1;
        $default_fixes_options['application_passwords']        = 1;
        $default_fixes_options['remove_unwanted_files']        = 1;

        $default_fixes_options['enable_xcto']  = 1;
        $default_fixes_options['sechead_xcto'] = 'nosniff';

        $default_fixes_options['enable_xfo']  = 1;
        $default_fixes_options['sechead_xfo'] = 'SAMEORIGIN';

        $default_fixes_options['enable_sts']  = 1;
        $default_fixes_options['sechead_sts'] = 'max-age=31536000;';

        $default_fixes_options['enable_rp']  = 1;
        $default_fixes_options['sechead_rp'] = 'same-origin';

        $default_fixes_options['secure_cookies'] = 1;

        update_option( WF_SN_FIXES_OPTIONS_KEY, $default_fixes_options, false );


        $results = array(
            'done'    => 1,
            'message' => __( 'Default fixes enabled.', 'security-ninja' ),
        );

        wp_send_json_success( $results );
        exit;
    }
}
// hook everything up
add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Wf_Sn_Wiz', 'init' ) );