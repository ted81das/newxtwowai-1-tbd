<?php

namespace Studiocart;
use ARM_Plan;
use ARM_subscription_plans;

if (!defined('ABSPATH'))
	exit;

class ARMember {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;

	public function __construct() {
        $this->service_name = 'armember';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        if ( !class_exists('ARM_subscription_plans') || (! class_exists('ARMemberlite') && ! class_exists('ARMember')) ) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_integration'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_customer'), 10, 3);
        add_filter('sc_integration_service_action_field_logic_options', array($this, 'add_to_filter'));
        add_filter('sc_create_user_integrations', array($this, 'add_to_filter'));
    }
    
    public function add_integration($options) {
        $options[$this->service_name] = "ARMember";
        return $options;
    }
    
    public function add_to_filter($options) {
        $options[] = $this->service_name;
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'arm_plan',
                'id'			=> 'arm_plan',
                'label'		    => __('Plan','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ($save) ? '' : $this->get_plans(),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'arm_status',
                'id'			=> 'arm_status',
                'label'		    => __('Status','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => ['1'=>__('Active','ncs-cart'), '2' => __('Inactive','ncs-cart'), '4' => __('Terminated','ncs-cart')],
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            )
        );
        
        
        $fields[1]['fields'][] = array(
        'checkbox' =>array(
            'class'		=> '',
            'description'	=> '',
            'id'			=> 'arm_email',
            'label'		=> __('Send new user notification?','ncs-cart'),
            'placeholder'	=> '',
            'type'		=> 'checkbox',
            'value'		=> '',
            'conditional_logic' => array (
                array(
                    'field' => 'services',
                    'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                    'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                ),
                array(
                    'field' => 'arm_status',
                    'value' => '1', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                    'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                )
            ),
        ));
        
        return $fields;
    }
        
    public function add_remove_customer($int, $sc_product_id, $order) {        
        if( empty($int['arm_plan']) ){
			return;
	    }
        
        $plan_id = intval($int['arm_plan']);
        $new_status = intval($int['arm_status']);
        $user_notification = intval($int['arm_email']);
        $create = array('send_email' => false, 'user_role' => $int['user_role']);
        $user_id = sc_get_order_user_id($order, $create);
        
        do_action('arm_apply_plan_to_member', $plan_id, $user_id);
        $this->arm_change_user_status($user_id, $new_status, $user_notification);
        sc_log_entry($order['id'], "User added to ARMember Plan ID: " .$plan_id);
               
    }
    
    private function arm_change_user_status($user_id, $new_status, $send_user_notification) {
        global $wpdb, $arm_email_settings, $arm_global_settings, $arm_subscription_plans;

        $date_format = $arm_global_settings->arm_get_wp_date_format();

        $nowDate = current_time('mysql');

        if (!empty($user_id) && $user_id != 0) {
            if ($new_status == '1') {
                arm_set_member_status($user_id, 1);
                if (!empty($send_user_notification) && $send_user_notification == 1) {
                    $user_data = get_user_by('ID', $user_id);
                    $arm_global_settings->arm_mailer($arm_email_settings->templates->on_menual_activation, $user_id);
                }
            } else if ($new_status == '2') {
                arm_set_member_status($user_id, 2, 0);
            } else if ($new_status == '4') {
                arm_set_member_status($user_id, 4);
                $defaultPlanData = $arm_subscription_plans->arm_default_plan_array();
                $stop_plan_ids = get_user_meta($user_id, 'arm_user_plan_ids', true);
                $stop_future_plan_ids = get_user_meta($user_id, 'arm_user_future_plan_ids', true);

                if (!empty($stop_future_plan_ids) && is_array($stop_future_plan_ids)) {
                    foreach ($stop_future_plan_ids as $stop_future_plan_id) {
                        $arm_subscription_plans->arm_add_membership_history($user_id, $stop_future_plan_id, 'cancel_subscription', array(), 'terminate');
                        delete_user_meta($user_id, 'arm_user_plan_' . $stop_future_plan_id);
                    }
                    delete_user_meta($user_id, 'arm_user_future_plan_ids');
                }

                if (!empty($stop_plan_ids) && is_array($stop_plan_ids)) {
                    foreach ($stop_plan_ids as $stop_plan_id) {
                        $old_plan = new ARM_Plan($stop_plan_id);
                        $userPlanDatameta = get_user_meta($user_id, 'arm_user_plan_' . $stop_plan_id, true);
                        $userPlanDatameta = !empty($userPlanDatameta) ? $userPlanDatameta : array();
                        $planData = shortcode_atts($defaultPlanData, $userPlanDatameta);
                        $plan_detail = $planData['arm_current_plan_detail'];
                        $planData['arm_cencelled_plan'] = 'yes';
                        update_user_meta($user_id, 'arm_user_plan_' . $stop_plan_id, $planData);

                        if (!empty($plan_detail)) {
                            $planObj = new ARM_Plan(0);
                            $planObj->init((object) $plan_detail);
                        } else {
                            $planObj = new ARM_Plan($stop_plan_id);
                        }
                        if ($planObj->exists() && $planObj->is_recurring()) {
                            do_action('arm_cancel_subscription_gateway_action', $user_id, $stop_plan_id);
                        }
                        $arm_subscription_plans->arm_add_membership_history($user_id, $stop_plan_id, 'cancel_subscription', array(), 'terminate');
                        do_action('arm_cancel_subscription', $user_id, $stop_plan_id);
                        $arm_subscription_plans->arm_clear_user_plan_detail($user_id, $stop_plan_id);
                    }
                }
            }
        }
    }
    
    public function get_plans() {
        $arm_subscription_plans = new ARM_subscription_plans();
        global $arm_subscription_plans;
        $courses = array(''=>__('-- None Found --', 'ncs-cart') );

        $form_result = $arm_subscription_plans->arm_get_all_subscription_plans();
        if (!empty($form_result)) {
            $courses = array();
            foreach($form_result as $planData) {
                $planObj = new ARM_Plan();
                $planObj->init((object) $planData);
                $planID = $planData['arm_subscription_plan_id'];
                $planName = esc_html(stripslashes($planObj->name));
                $courses[$planID] = $planName;
            }
        }
		return $courses;
	}
}