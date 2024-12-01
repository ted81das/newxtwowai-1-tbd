<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class Teachable {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;
	private $service_label;
	private $email;
	private $password;
	private $url;

	public function __construct() {
        $this->service_name = 'teachable';
        $this->service_label = 'Teachable';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        //add_action('_sc_register_sections', array($this, 'settings_section'), 10, 2);
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));
        
        $this->email = get_option('_sc_'.$this->service_name.'_email');
        $this->password = get_option('_sc_'.$this->service_name.'_password');
        $this->url = get_option('_sc_'.$this->service_name.'_url');
        
        if ( $this->email && $this->password && $this->url ) {
            add_filter('sc_integrations', array($this, 'add_service'));
            add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
            add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_service'), 10, 3);
        }
    }
    
    public function settings_section($intigrations) {        
        $intigrations[$this->service_name] = $this->service_label;
        return $intigrations;      
        // add_settings_section(
		// 	$sc_name . '-' .$this->service_name,
		// 	apply_filters( $sc_name . 'section-title-'.$this->service_name, esc_html__( $this->service_label, 'ncs-cart' ) ),
		// 	array( $sc, 'section_integrations_settings' ),
		// 	$sc_name
		// );
    }
    
    public function service_settings($options) {
        $options[$this->service_name] = array(
                                            $this->service_name.'-username' => array(
                                                'type'          => 'text',
                                                'label'         => esc_html__( 'Email', 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_email',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        
                                            $this->service_name.'-password' => array(
                                                'type'          => 'password',
                                                'label'         => esc_html__( 'Password', 'ncs-cart'),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_password',
                                                    'value'         => '',
                                                    'description'   => '',
                                                ),
                                                'tab'=>'integrations'
                                            ),
            
                                            $this->service_name.'-url' => array(
                                                'type'          => 'text',
                                                'label'         => esc_html__( 'Base URL of your school', 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_'.$this->service_name.'_url',
                                                    'value'         => '',
                                                    'description'   => 'e.g. http://teachable-school-domain.com',
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        );
        return $options;
    }
    
    public function add_service($options) {
        $options[$this->service_name] = $this->service_label;
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        /*$fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => '',
                'id'			=> $this->service_name.'_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => [
                                        'add' => __('Enroll student','ncs-cart'),
                                        'remove' => __('Unenroll student','ncs-cart'), 
                                    ],
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );*/
        $fields[1]['fields'][] = array(
            'text' =>array(
                'class'		    => 'widefat',
                'id'			=> $this->service_name.'_product_id',
                'label'		    => __('Product ID','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'text',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );
        /*$fields[1]['fields'][] = array(
            'text' =>array(
                'class'		    => 'widefat',
                'id'			=> $this->service_name.'_course_id',
                'label'		    => __('Course ID','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'text',
                'value'		    => '',
                'class_size'    => '',
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );*/
        return $fields;
    }
        
    public function add_remove_to_service($int, $sc_product_id, $order) {        
       
        $auth = base64_encode( $this->email . ':' . $this->password );
        $user = $order['email'];
        
        $int[$this->service_name.'_action'] = 'add';
                                                                                
        if ( $int[$this->service_name.'_action'] == 'add' && isset($int[$this->service_name.'_product_id']) ) {

            // create user
            $product_id = intval($int[$this->service_name.'_product_id']);
            $args = array(
                'name' => $order['first_name'] . ' ' . $order['last_name'],
                'email' => $order['email'],
                'product_id' => $product_id,
                'src' => get_bloginfo( 'name' ),
            );
            try{        
                $url = "{$this->url}/api/v1/sales";
                $response = wp_remote_post($url, array(
                    'headers' => [
                        'Authorization' => "Basic $auth",
                        'content-type' => 'application/json'
                    ],
                    'body' => json_encode($args),
                ));
                $response = wp_remote_retrieve_body( $response );
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    sc_log_entry($order['id'], sprintf(__("Something went wrong adding student to Teachable: %s", 'ncs-cart'), $error_message));
                } else {
                    $result = json_decode( $response, true );                        
                    if(isset($result['error'])) {
                        $log_entry = sprintf(__('Teachable enroll error: %s', 'ncs-cart'), $result['error']);
                        sc_log_entry($order['id'], $log_entry);
                    } else {
                        $user_id = intval($result['user_id']);
                        update_post_meta($order['id'], '_sc_teachable_user_id', $user_id);

                        $log_entry = sprintf(__('Student ID: %s added to Teachable course.', 'ncs-cart'), $user_id);
                        sc_log_entry($order['id'], $log_entry);
                    }
                }

            } catch(\Exception $e) {
                echo $e->getMessage(); //add custom message
                return;
            }
        } else if ( $int[$this->service_name.'_action'] == 'remove' && isset($int[$this->service_name.'_course_id']) ) {

            $course_id = intval($int[$this->service_name.'_course_id']);

            // unenroll
            $args = array (
                "is_active" => false
            );
            $user_id = intval(get_post_meta($order['id'], '_sc_teachable_user_id', true));
            try{        
                $url = "{$this->url}/api/v1/users/{$user_id}/enrollments/{$course_id}";
                $response = wp_remote_post($url, array(
                    'headers' => [
                        'Authorization' => "Basic $auth",
                        'content-type' => 'application/json'
                    ],
                    'body' => json_encode($args),
                ));
                $response = wp_remote_retrieve_body( $response );
                if ( is_wp_error( $response ) ) {
                    $error_message = $response->get_error_message();
                    sc_log_entry($order['id'], sprintf(__("Something went wrong unenrolling student ID %n from Teachable: %s", 'ncs-cart'), $user_id, $error_message));
                } else {
                    $result = json_decode( $response, true );
                    if(isset($result['error'])) {
                        $log_entry = sprintf(__('Teachable unenroll error: %s', 'ncs-cart'), $result['error']);
                        sc_log_entry($order['id'], $log_entry);
                    } else {
                        $log_entry = sprintf(__('Student ID: %s removed from Teachable course.', 'ncs-cart'), $user_id);
                        sc_log_entry($order['id'], $log_entry);
                    }
                }

            } catch(\Exception $e) {
                echo $e->getMessage(); //add custom message
                return;
            }
        }        
    }
}