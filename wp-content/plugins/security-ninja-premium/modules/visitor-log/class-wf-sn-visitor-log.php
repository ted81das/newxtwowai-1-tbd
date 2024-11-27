<?php

namespace WPSecurityNinja\Plugin;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'WF_SN_CF_LOG_TABLE' ) ) {
    define( 'WF_SN_CF_LOG_TABLE', 'wf_sn_cf_log' );
}

/**
 * Wf_Sn_Visitor_log.
 *
 * @author  Lars Koudal <admin@wpsecurityninja.com>
 * @since   v0.0.1
 * @version v1.0.0    Tuesday, April 18th, 2023.
 * @global
 */
class Wf_Sn_Visitor_log {

    /**
     * init.
     *
     * @author  Lars Koudal
     * @since   v0.0.1
     * @version v1.0.0    Friday, May 13th, 2022.
     * @access  public static
     * @return  void
     */
    public static function init() {
        add_action( 'admin_enqueue_scripts', array( __NAMESPACE__ . '\\Wf_Sn_Visitor_log', 'enqueue_scripts' ) );
        add_action( 'wp_ajax_secnin_get_visitors', array( __NAMESPACE__ . '\\Wf_Sn_Visitor_log', 'do_ajax_return_latest_visitors' ) );
        add_action( 'wp_ajax_secnin_vl_banip', array( __NAMESPACE__ . '\\Wf_Sn_Visitor_log', 'do_ajax_banip' ) );
    }

    /**
     * URLs to be ignored when viewing the visitor log
     *
     * @author  Lars Koudal
     * @since   v0.0.1
     * @version v1.0.0    Wednesday, November 23rd, 2022.
     * @access  public static
     * @return  array
     */
    public static function return_filterable_urls() {
        static $clean_list = null;

        if ( $clean_list === null ) {
            global $wpdb;
            $default_ignore = array(
                '/favicon.ico',
                '/robots.txt',
                '/?doing_wp_cron',
                '/?kinsta-monitor',
                '&mainwpsignature=',
                '/wordfence_lh=',
                '/wp-json/',
            );
            $ignore_list = apply_filters( 'securityninja_visitorlog_filter_url', $default_ignore );

            $clean_list = array();
            foreach ( $ignore_list as $il ) {
                $clean_list[] = $wpdb->remove_placeholder_escape( esc_sql( $il ) );
            }
        }

        return $clean_list;
    }

    /**
     * Returns a row in HTML format ready to be displayed.
     *
     * @param object $visitor The visitor object.
     * @param array  $fmtargs Formatting arguments.
     * @return string|boolean HTML string or false if visitor is invalid.
     */
    public static function format_line( $visitor, $fmtargs = array() ) {
        $defaults                 = array(
            'marknew'         => true,
            'current_user_ip' => false,
        );
        $secnin_visitor_log_nonce = wp_create_nonce( 'secnin_visitor_log' );
        $args                     = wp_parse_args( $fmtargs, $defaults );

        $current_user_country = '';
        $ipcol_output         = $visitor->ip;
        $geolocate_ip         = \WPSecurityNinja\Plugin\SN_Geolocation::geolocate_ip( $visitor->ip, true );

        if ( $geolocate_ip ) {
            $current_user_country = $geolocate_ip['country'];
            if ( $current_user_country && '-' !== $current_user_country ) {
                $country_img_url = wf_sn::get_country_img__premium_only( $current_user_country );

                if ( ! isset( $geoip_countrylist ) ) {
                    include WF_SN_PLUGIN_DIR . 'modules/cloud-firewall/class-sn-geoip-countrylist.php';
                }
                $country_name = '';
                if ( isset( $geoip_countrylist[ $current_user_country ] ) ) {
                    $country_name = $geoip_countrylist[ $current_user_country ];
                }
                if ( $country_img_url ) {
                    $ipcol_output = '<img src="' . esc_url( $country_img_url ) . '" width="20" height="20" class="countryimg" title="' . esc_html( $country_name ) . '"> '.$visitor->ip;
                }

            }
        }

        if ( ! $visitor ) {
            return false;
        }
        $rowatts = '';

        $rowclasses = '';

        if ( $args['marknew'] ) {
            $rowclasses .= ' newrow';
        }
        $rowclasses .= ' visit-' . intval( $visitor->id );

        $notes = '';
        if ( $visitor->banned ) {
            $rowclasses .= ' blocked';
            $notes      .= __( 'Blocked', 'security-ninja' );
            if ( $visitor->ban_reason ) {
                $notes .= ': ' . esc_html( $visitor->ban_reason );
            }
        }

        if ( Wf_sn_cf::is_banned_ip( $visitor->ip ) ) {
            $rowclasses .= ' blocked';
            $notes      .= __( 'Blacklisted by IP', 'security-ninja' );
            if ( $visitor->ban_reason ) {
                $notes .= ': ' . esc_html( $visitor->ban_reason );
            }
        }

        $rowatts = ' class="' . $rowclasses . '"';

        $details  = '<details><summary>' . __( 'Details', 'security-ninja' ) . '</summary><dl>';
        $details .= '<dt>' . __( 'User Agent', 'security-ninja' ) . '</dt><dd>' . esc_html( $visitor->user_agent ) . '</dd>';

        if ( $visitor->action ) {
            $details .= '<dt>' . __( 'Action', 'security-ninja' ) . '</dt><dd>' . esc_html( $visitor->action ) . '</dd>';
        }

        if ( maybe_unserialize( $visitor->description ) ) {
            $details .= '<dt>' . __( 'Description', 'security-ninja' ) . '</dt><dd>' . esc_html( wp_json_encode( $visitor->description ) ) . '</dd>';
        }

        if ( ! empty( $visitor->raw_data ) ) {
            $parsed_raw_data = json_decode( $visitor->raw_data );
        } else {
            $parsed_raw_data = false;
        }

        if ( ( is_array( $parsed_raw_data ) || ( is_object( $parsed_raw_data ) ) ) && ! empty( $parsed_raw_data ) ) {
            $details .= '<dt>' . __( 'Details', 'security-ninja' ) . '</dt><dd>';
            $details .= '<dl class="inner">';
            foreach ( $parsed_raw_data as $key => $rd ) {
                $details .= '<dt>' . esc_html( $key ) . '</dt><dd>' . esc_html( $rd ) . '</dd>';
            }
            $details .= '</dl>';
            $details .= '</dd>';
        } elseif ( is_string( $parsed_raw_data ) ) {
            $details .= '<dt>' . __( 'Details', 'security-ninja' ) . '</dt><dd>' . esc_html( $parsed_raw_data ) . '</dd>';
        }

        $details .= '</dl></details>';

        $output       = '<tr ' . $rowatts . ' >';
        $output      .= '<td>' . esc_html( $visitor->timestamp ) . '</td>';
        $output      .= '<td>' . wp_kses_post( $ipcol_output ) . '</td>';
        $output      .= '<td>' . esc_url( $visitor->URL ) . '</td>';
        $allowed_html = wp_kses_allowed_html( 'post' );
        $output      .= '<td class="secnin-details"><dd>' . wp_kses( $details, $allowed_html ) . '</dd></td>';

        if ( $visitor->banned ) {
            $output .= '<td></td>';
        } elseif ( $args['current_user_ip'] === $visitor->ip ) {
            $output .= '<td class="isme">' . __( 'You', 'security-ninja' ) . '</td>';
        } else {
            $output .= '<td class="secnin-visit-actions">';
            $output .= '<a href="#" data-banip="' . esc_attr( $visitor->ip ) . '" data-nonce="' . esc_attr( $secnin_visitor_log_nonce ) . '" class="button button-small button-secondary secnin-banip">' . __( 'Ban IP', 'security-ninja' ) . '</a>';

            $output .= '</td>';
        }

        $output .= '</tr>';

        return $output;
    }

    /**
     * Enqueue CSS and JS scripts on plugin's admin page
     *
     * @author  Lars Koudal
     * @since   v0.0.1
     * @version v1.0.0    Tuesday, May 18th, 2021.
     * @access  public static
     * @return  void
     */
    public static function enqueue_scripts() {
        $current_screen = get_current_screen();
        if ( ! $current_screen ) {
            return false;
        }

        if ( in_array(
            $current_screen->id,
            array(
                'security-ninja-1_page_wf-sn-visitor-log',
                'security-ninja-2_page_wf-sn-visitor-log',
                'security-ninja_page_wf-sn-visitor-log',
            ),
            true
        ) ) {

            $js_vars = array(
                'vl_nonce' => wp_create_nonce( 'secnin_visitor_log' ),
                'text'     => array(
                    'notactive'         => __( 'This tab is not active, visitor data not updated', 'security-ninja' ),
                    'areyousureblockip' => __( 'Are you sure you want to block this IP?', 'security-ninja' ),
                ),
            );

            wp_enqueue_script(
                'secnin-vl',
                WF_SN_PLUGIN_URL . 'modules/visitor-log/js/secnin-visitor-log-min.js',
                array( 'jquery' ),
                Wf_Sn::$version,
                true // Load the script in the footer
            );
            wp_localize_script( 'secnin-vl', 'secnin_vl', $js_vars );
        }
    }

    /**
     * Return log lines.
     *
     * @param int|false $latestid The latest ID to fetch logs from.
     * @return array Array of log entries.
     */
    public static function return_log_lines( $latestid = false ) {
        global $wpdb;

        $internal_ignore_list = self::return_filterable_urls();

        $placeholders = array_fill(0, count($internal_ignore_list), '%s');
        $like_patterns = array_map(function($url) use ($wpdb) { 
            return '%' . $wpdb->esc_like($url) . '%'; 
        }, $internal_ignore_list);

        $wherestring = $wpdb->prepare(
            "WHERE URL NOT LIKE " . implode(" AND URL NOT LIKE ", $placeholders),
            $like_patterns
        );

        if ( $latestid ) {
            $wherestring .= $wpdb->prepare(' AND id > %d', $latestid);
        }

        $log_table    = $wpdb->prefix . WF_SN_CF_LOG_TABLE;
        $query_string = "SELECT * FROM {$log_table} {$wherestring} ORDER by id DESC LIMIT 100";
        $results      = $wpdb->get_results( $query_string, OBJECT );

        if ( $wpdb->last_error ) {
            return array(); // Return an empty array instead of potentially invalid results
        }

        return $results;
    }

    /**
     * Return the latest log ID.
     *
     * @param int $offset Offset for the query.
     * @return int|string The highest ID or 0 if no results.
     */
    public static function return_latest_log_id( $offset = 0 ) {
        global $wpdb;

        $internal_ignore_list = self::return_filterable_urls();

        $placeholders = array_fill(0, count($internal_ignore_list), '%s');
        $like_patterns = array_map(function($url) use ($wpdb) { 
            return '%' . $wpdb->esc_like($url) . '%'; 
        }, $internal_ignore_list);

        $wherestring = $wpdb->prepare(
            "WHERE URL NOT LIKE " . implode(" AND URL NOT LIKE ", $placeholders),
            $like_patterns
        );

        $log_table = $wpdb->prefix . WF_SN_CF_LOG_TABLE;
        $query_string = $wpdb->prepare(
            "SELECT id FROM {$log_table} {$wherestring} ORDER by id DESC LIMIT 1 OFFSET %d",
            intval($offset)
        );
        $highestid = $wpdb->get_var( $query_string );

        if ( ! $highestid ) {
            $highestid = 0;
        }
        return $highestid;
    }

    /**
     * AJAX handler for returning latest visitors.
     *
     * @return void
     */
    public static function do_ajax_return_latest_visitors() {

        if ( ! check_ajax_referer( 'secnin_visitor_log', false, false ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'security-ninja' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed.', 'security-ninja' ),
                )
            );
        }

        $latestid = isset( $_GET['current'] ) ? sanitize_text_field( $_GET['current'] ) : '';

        $results = self::return_log_lines( $latestid );

        if ( $results ) {
            $highestid       = false;
            $latestvisits    = array();
            $current_user_ip = \WPSecurityNinja\Plugin\Wf_sn_cf::get_user_ip();
            $fmtlineargs     = array(
                'current_user_ip' => $current_user_ip,
            );
            foreach ( $results as $re ) {
                $highestid      = $re->id;
                $latestvisits[] = self::format_line( $re, $fmtlineargs );
            }
            $latestvisits = array_reverse( $latestvisits );
        }

        if ( $results ) {
            $output = array(
                'visits'  => $latestvisits,
                'current' => $highestid,
            );
            wp_send_json_success( $output );
        } else {
            wp_send_json_error( __( 'No data', 'security-ninja' ) );
        }
    }

    /**
     * Ban IP via AJAX.
     *
     * @return void
     */
    public static function do_ajax_banip() {

        if ( ! check_ajax_referer( 'secnin_visitor_log', false, false ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'security-ninja' ) );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error(
                array(
                    'message' => __( 'Failed.', 'security-ninja' ),
                )
            );
        }

        $banip = isset( $_POST['banip'] ) ? sanitize_text_field( $_POST['banip'] ) : false;

        if ( ! $banip || ! filter_var( $banip, FILTER_VALIDATE_IP ) ) {
            wp_send_json_error( __( 'Please enter a valid IP.', 'security-ninja' ) );
        }

        // Add this check to prevent banning localhost or private IPs
        if ( filter_var( $banip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) === false ) {
            wp_send_json_error( __( 'Cannot ban private or reserved IP addresses.', 'security-ninja' ) );
        }

        if ( ! defined( 'WF_SN_CF_OPTIONS_KEY' ) ) {
            return false;
        }

        $cf_options              = get_option( WF_SN_CF_OPTIONS_KEY );
        $blacklist               = $cf_options['blacklist'];
        $blacklist[]             = $banip;
        $blacklist               = array_unique( $blacklist );
        $cf_options['blacklist'] = $blacklist;
        update_option( WF_SN_CF_OPTIONS_KEY, $cf_options, false );
        wp_send_json_success( __( 'Added IP to blacklist', 'security-ninja' ) );
        wf_sn_el_modules::log_event( 'security_ninja', 'blacklisted_ip', __( 'New IP added to the blacklist manually from the visitor log.', 'security-ninja' ), '' );
        return false;
    }

    /**
     * Display the visitor log page
     *
     * @author  Lars Koudal
     * @since   v0.0.1
     * @version v1.0.2    Wednesday, January 11th, 2023.
     * @access  public static
     * @return  void
     */
    public static function live_log_page() {

        $options     = Wf_sn_cf::get_options();
        $trackvisits = intval( $options['trackvisits'] );

        if ( ! $trackvisits ) {
            ?>
            <div class="secnin-live-vis">
                <p><?php esc_html_e( 'Visitor log is not enabled. Go to the Firewall tab and turn on "Visitor Logging".', 'security-ninja' ); ?></p>
            </div>
            <?php
        }

        ?>
        <div class="wrap">
            <?php
            wf_sn::show_topbar();
            ?>
            <div id="secnin-live-visitor-log-notice" class="">
                <h3><?php esc_html_e( 'Live Visitor Log', 'security-ninja' ); ?></h3>
                <p><?php esc_html_e( 'Updates every 10 seconds when enabled.', 'security-ninja' ); ?></p>
                <p><label for="secnin_enable_live">
                        <input name="secnin_enable_live" type="checkbox" id="secnin_enable_live" value="1" checked><?php esc_html_e( 'Enable live updates', 'security-ninja' ); ?></label></p>
            </div>
            <div id="enablecontainer">

                <p id="loadingspinner" class="spinner is-active"><span><?php esc_html_e( 'Loading', 'security-ninja' ); ?></span></p>
                <div id="secnin-visitor-log-container">

                    <div id="secnin-visitor-log-unfocused" style="display:none;">
                        <h3><?php esc_html_e( 'Paused', 'security-ninja' ); ?></h3>
                        <p><?php esc_html_e( 'Switch back to this window to resume live updates.', 'security-ninja' ); ?></p>
                    </div><!-- #secnin-visitor-log-unfocused -->
                    <table class="wp-list-table widefat flexible striped table-view-list" id="secnin-visitor-log">
                        <thead>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Time', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'IP', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'URL', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Details', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Action', 'security-ninja' ); ?></th>

                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $results = self::return_log_lines( 0 );

                            if ( $results ) {
                                $current_user_ip = \WPSecurityNinja\Plugin\Wf_sn_cf::get_user_ip();
                                $fmtlineargs     = array(
                                    'current_user_ip' => $current_user_ip,
                                );
                                foreach ( $results as $re ) {
                                    echo wp_kses_post( self::format_line( $re, $fmtlineargs ) );
                                }
                            }
                            // Write the latest ID we collected, so we later down can write it in to HTML and let the JS pick it up.
                            $firstbatch = self::return_latest_log_id();

                            ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th scope="col"><?php esc_html_e( 'Time', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'IP', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'URL', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Details', 'security-ninja' ); ?></th>
                                <th scope="col"><?php esc_html_e( 'Action', 'security-ninja' ); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div id="secnin_vl_latestid"><?php echo esc_html( $firstbatch ); ?></div>
            </div>
        <?php
    }
}

add_action( 'plugins_loaded', array( __NAMESPACE__ . '\Wf_Sn_Visitor_log', 'init' ) );
