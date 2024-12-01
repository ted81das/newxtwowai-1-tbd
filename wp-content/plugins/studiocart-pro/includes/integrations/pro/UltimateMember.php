<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class UltimateMember {
    
    private $service_name;
	private $service_label;

	public function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {  
        if ( !class_exists('UM') ){
            return;
        }
        
        $this->service_name = 'um';
        $this->service_label = 'Ultimate Member';
        
        //add_action('_sc_register_sections', array($this, 'settings_section'), 10, 2);
        add_filter('_sc_integrations_tab_section', array($this, 'settings_section'), 10, 1);
        add_filter('_sc_integrations_option_list', array($this, 'service_settings'));

        if (get_option('_sc_send_um_welcome_email')) {
            add_action('sc_after_user_is_created', array($this, 'send_um_welcome_email'), 10, 2);
            add_filter('sc_send_new_user_email', array($this, 'turn_off_default_email'), 10, 2);
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
                                            $this->service_name.'-id' => array(
                                                'type'          => 'checkbox',
                                                'label'         => esc_html__( "Enable Ultimate Member's welcome email", 'ncs-cart' ),
                                                'settings'      => array(
                                                    'id'            => '_sc_send_um_welcome_email',
                                                    'value'         => '',
                                                    'description'   => esc_html__("Disable the default email notification and send Ultimate Member's welcome email when a new user is created.", 'ncs-cart' ),
                                                ),
                                                'tab'=>'integrations'
                                            ),
                                        );
        return $options;
    }
    
    public function send_um_welcome_email($user_id, $order_id) {
        um_fetch_user( $user_id );
		\UM()->user()->approve();
        sc_log_entry($order_id, esc_html__("UltimateMember welcome email sent", 'ncs-cart' ));
    }
    
    public function turn_off_default_email($send, $order_id) {
        return false;
    }
}