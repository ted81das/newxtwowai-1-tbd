<?php

namespace Studiocart;

use ScrtOrder;
use ScrtSubscription;

if (!defined('ABSPATH'))
	exit;

class MemberPress {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name;

	public function __construct() {
        $this->service_name = 'memberpress';
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
         if (! class_exists('MeprBaseCtrl')) {
            return;
        }
        
        add_filter('sc_integrations', array($this, 'add_memberpress_service'));
        add_filter('sc_integration_fields', array($this, 'add_integration_fields'), 10, 2);
        add_filter('sc_paypal_custom_payment_vars', array($this, 'add_mepr_pp_id'), 10, 2);
        add_action('studiocart_'.$this->service_name.'_integrations', array($this, 'add_remove_to_memberpress'), 10, 3);
        add_action('sc_paypal_recurring_payment_data', array($this, 'maybe_add_paypal_txn'), 10, 2);
        add_action('mepr-txn-store', array($this, 'maybe_update_subscription_amount'), 10);
    }
    
    public function add_mepr_pp_id($custom, $vars) {
        $id = $vars['product_id'];
        $integrations = get_post_meta($id, '_sc_integrations', true);
                
        if( $integrations ){
            $plan_id = $vars['plan_id'];
            $option_id = $vars['option_id'];
            foreach ( $integrations as $ind=>$intg ) {
                $_sc_services = isset($intg['services']) ? $intg['services'] : "";
                $_sc_plan_ids = (isset($intg['int_plan'])) ? (array) $intg['int_plan'] : array();
                
                if ($_sc_services == $this->service_name &&
                    isset($intg['mepr_gateway_paypal']) &&
                    (empty($_sc_plan_ids) || in_array('',$_sc_plan_ids) || in_array($plan_id, $_sc_plan_ids) || in_array($option_id, $_sc_plan_ids) )) {
                    
                    // add Mepr gateway so that we know to add rebills for this subscription in MP
                    $custom['gateway_id'] = $intg['mepr_gateway_paypal'];
                }
            }
        }
        return $custom;
    }

    public function add_memberpress_service($options) {
        $options[$this->service_name] = "MemberPress";
        return $options;
    }
    
    public function add_integration_fields($fields, $save) {        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'mepr',
                'id'			=> 'mepr_action',
                'label'		    => __('Action','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'class_size'    => '',
                'selections'    => array(
                                        'start'   =>__('Start Membership','ncs-cart'), 
                                        'end'=>__('End Membership','ncs-cart'), 
                                        //'end_main'=>__('End Parent Order Membership','ncs-cart'), 
                    ),
                'conditional_logic' => array(
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );
        
        $fields[1]['fields'][] = array(
            'select' =>array(
                'class'		    => 'mepr',
                'id'			=> 'mepr_membership',
                'label'		    => __('Membership','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'selections'    => ($save) ? '' : $this->get_memberships(),
                'conditional_logic' => array(
                        array(
                            'field' => 'mepr_action',
                            'value' => 'end_main', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '!=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        ),
                        array(
                            'field' => 'services',
                            'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                            'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                        )
                )
            ),
        );
        
        $scgateways = scrt_payment_methods();
        foreach($scgateways as $k=>$v) {
            $fields[1]['fields'][] = array(
                'select' =>array(
                    'class'		    => 'mepr',
                    'id'			=> 'mepr_gateway_'.$k,
                    'label'		    => sprintf(__('%s Gateway','ncs-cart'), $v),
                    'description'	=> sprintf(__('Select a MemberPress gateway to associate with subscriptions processed by %s','ncs-cart'), $v),
                    'type'		    => 'select',
                    'value'		    => '',
                    'class_size'    => '',
                    'selections'    => $this->get_gateways(),
                    'conditional_logic' => array(
                            array(
                                'field' => 'mepr_action',
                                'value' => 'start', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                                'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                            ),
                            array(
                                'field' => 'services',
                                'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                                'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                            )
                    )
                ),
            );
        }
        
        $fields[1]['fields'][] = array(
            'checkbox' =>array(
                'class'		=> '',
                'description'	=> 'Send the new member an email with their username and a link to create a new password',
                'id'			=> 'mepr_send_notification',
                'label'		=> __('Send New User Notification','ncs-cart'),
                'placeholder'	=> '',
                'type'		=> 'checkbox',
                'value'		=> '',
                'class_size' => '',
                'conditional_logic' => array (
                    array(
                        'field' => 'mepr_action',
                        'value' => 'start', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    ),
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                ),
            ),
        );
        
        $fields[1]['fields'][] = array(
            'checkbox' =>array(
                'class'		=> '',
                'description'	=> 'Send the new member a membership welcome email',
                'id'			=> 'mepr_send_welcome',
                'label'		=> __('Send Welcome Email','ncs-cart'),
                'placeholder'	=> '',
                'type'		=> 'checkbox',
                'value'		=> '',
                'class_size' => '',
                'conditional_logic' => array (
                    array(
                        'field' => 'mepr_action',
                        'value' => 'start', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    ),
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                ),
            ),
        );
        
        $fields[1]['fields'][] = array(
            'checkbox' =>array(
                'class'		=> '',
                'description'	=> '',
                'id'			=> 'mepr_send_cancelled_email',
                'label'		=> __('Send Cancelled Notification','ncs-cart'),
                'placeholder'	=> '',
                'type'		=> 'checkbox',
                'value'		=> '',
                'class_size' => '',
                'conditional_logic' => array (
                    array(
                        'field' => 'mepr_action',
                        'value' => 'start', // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '!=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    ),
                    array(
                        'field' => 'services',
                        'value' => $this->service_name, // Optional, defaults to "". Should be an array if "IN" or "NOT IN" operators are used.
                        'compare' => '=', // Optional, defaults to "=". Available operators: =, <, >, <=, >=, IN, NOT IN
                    )
                ),
            ),
        );
        
        return $fields;
    }
    
    public function get_gateways() {
        $arr = array('manual'=>__('Manual', 'ncs-cart'));
        $mepr_options = \MeprOptions::fetch();
        $pms = array_keys($mepr_options->integrations);
        foreach($pms as $pm_id):
          $obj = $mepr_options->payment_method($pm_id);
          if( $obj instanceof \MeprBaseRealGateway ):
            $arr[$obj->id] = sprintf(__('%1$s (%2$s)', 'memberpress'),$obj->label,$obj->name);
          endif;
        endforeach;
        return $arr;  
    }
    
    public function maybe_update_subscription_amount($txn) {
        $amt = $this->get_first_transaction_amount($txn);
        if($txn->total != $amt) {
            remove_action('mepr-txn-store', array($this, 'maybe_update_subscription_amount'), 10);
            $txn->set_gross($amt);
            $id = $txn->store();
            add_action('mepr-txn-store', array($this, 'maybe_update_subscription_amount'), 10);
        }
    }
        
    public function add_remove_to_memberpress($int, $sc_product_id, $order) {
        
        $sc_order = new ScrtOrder();
        $sc_sub = new ScrtSubscription();
        
        $method = get_post_meta($order['id'], '_sc_pay_method', true);
        
        $membership_id  = $int['mepr_membership'];
        $action         = $int['mepr_action'];
                
        $order['gateway']   = !isset($int['mepr_gateway_'.$method]) ? 'manual' : $int['mepr_gateway_'.$method];
        $order['method'] = $method;
        
        // setup order and subscription objects
        if(get_post_type($order['id']) == 'sc_order') {
            $sc_order = new ScrtOrder($order['id']);
            if($sc_order->subscription_id) {
                $sc_sub = new ScrtSubscription($sc_order->subscription_id);
            }
        } else {
            $sc_sub = new ScrtSubscription($order['id']);
            if($sc_sub->id){
                $sc_order = $sc_sub->first_order();
                $order['id'] = $sc_order->id;
            }
        }
        
        if($sc_sub->id && $sc_sub->product_id == $sc_product_id && $sc_sub->option_id == $order['option_id']) {
            $order['sc_sub_id'] = $sc_sub->id;
            $order['subscr_id'] = $sc_sub->subscription_id;
        } else {
            $order['subscr_id'] = false;
            $sc_sub = new ScrtSubscription();
        }
        
        // set the right plan ID if this is a bump plan
        if($order['order_type'] == 'bump' && !get_post_meta($sc_order->id, '_sc_mepr_main_updated', true)) {
            $amount = $sc_order->main_offer_amt;
                        
            // correct the amount of the main order transaction if one exists
            if($txnid = get_post_meta($sc_order->id, '_sc_mepr_trans_id', true)){
                $txn = new \MeprTransaction($txnid);
                $txn->amount     = $amount;
                $txn->total      = $txn->amount;
                $id = $txn->store();
                update_post_meta($sc_order->id, '_sc_mepr_main_updated', 1);
            }
        }
        
        if ($action == 'start') {      
            // setup plan info for subscriptions
            if ($sc_sub->id && $order['subscr_id']){
                $order['plan'] = (object) $sc_sub->get_data();
            }

            $order['trans_num'] = $sc_order->transaction_id;
            if (!$order['trans_num'] || !($sc_sub->id && $sc_sub->product_id == $sc_product_id && $sc_sub->option_id == $order['option_id']) ) {
                $order['trans_num'] = 'sc-txn-'.$order['id'];
            }

            $order['user_account'] = ($order['user_account']) ? $order['user_account'] : $sc_order->find_user_id();
            if (!$order['user_account']) {
                $order['user_account'] = self::create_member($int, $sc_product_id, $order);
            }

            self::create_transaction($int, $sc_product_id, $order);

        } else if ($action == 'end') {
            if($membership_id) {
                $user_id = $sc_order->find_user_id();
                $txn = $this->active_product_transaction($user_id, $membership_id);
                if($txn) {
                    if($txn->subscription_id) {
                        self::record_cancel_subscription($txn->subscription_id, $int, $sc_sub->id);
                    } else {
                        $order['trans_id'] = $txn->id;
                        self::expire_trans($sc_order->id, $order);
                    } 
                } else {
                   sc_log_entry($sc_order->id, 'Unable to end MemberPress subscription, subscription not found.'); 
                }
            } else {
                sc_log_entry($sc_order->id, 'Unable to end MemberPress subscription, membership not selected.');
            }
        }
    }
    
    public function active_product_transaction($user_id, $product_id, $exclude_expired = true) {
        
        $txns = \MeprTransaction::get_all_complete_by_user_id(
            $user_id, // user_id
            'product_id, created_at DESC', // order_by
            '', // limit
            false, // count
            $exclude_expired, // exclude_expired
            true, // include_confirmations
            true // allow custom where clause override
        );
                
        foreach($txns as $txn) {
            if($txn->product_id == $product_id) {
                return $txn;
            }
        }

        return false;
    }
    
    private static function record_cancel_subscription($sub_id, $int, $scsub) {
        $sub = new \MeprSubscription($sub_id);

        if(!$sub) { return false; }

        // Seriously ... if sub was already cancelled what are we doing here?
        if($sub->status == \MeprSubscription::$cancelled_str) { return $sub; }

        $sub->status = \MeprSubscription::$cancelled_str;
        $sub->store();
        
        sc_log_entry($scsub, 'MemberPress subscription: '.$sub_id.' expired');

        if(isset($int['mepr_send_cancelled_email']) && $int['mepr_send_cancelled_email']) {
          \MeprUtils::send_cancelled_sub_notices($sub);
        }
        
        return $sub;
    }
    
    private static function expire_trans($scid, $order) {
        $txn = new \MeprTransaction($order['trans_id']);
        $yesterday = strtotime('-1 day', strtotime('now'));
        $txn->expires_at = gmdate('c', $yesterday);
        $id = $txn->store();
        sc_log_entry($scid, 'MemberPress transaction expired');
    }
    
    private static function create_member($int, $sc_product_id, $order) {
        
        $creds = sc_generate_login_creds($order['email']);
        $_POST['member'] = array(
            'user_login' => $creds['username'],
            'user_email' => $order['email'],
            'first_name' => $order['first_name'],
            'last_name' => $order['last_name'],
            'user_pass' => $creds['password'],
        );
        
        if(isset($int['mepr_send_notification']) && $int['mepr_send_notification']) {
            $_POST['member']['send_notification'] = $int['mepr_send_notification'];
        }
        
        if(isset($int['mepr_send_welcome']) && $int['mepr_send_welcome']) {
            $_POST['transaction']['send_welcome'] = $int['mepr_send_welcome'];
        }
        
        $_POST['transaction']['amount'] = ($order['order_type'] != 'bump') ? get_post_meta($order['id'], '_sc_amount', true) : $order['amount'];      
        $_POST['transaction']['product_id'] = $int['mepr_membership'];
        $_POST['transaction']['status'] = 'complete';
        $_POST['transaction']['gateway'] = $order['gateway'];
        
        $member = new \MeprUser();
        $member->load_from_array($_POST['member']);
        $member->send_notification = isset($_POST['member']['send_notification']);

        // Just here in case things fail so we can show the same password when the new_member page is re-displayed
        $member->password = $_POST['member']['user_pass'];
        $member->user_email = sanitize_email($_POST['member']['user_email']);        

        try {
            $member->set_password($_POST['member']['user_pass']);
            $member->store();
            
            update_post_meta($order['id'], '_sc_user_account', $member->ID);
            $order['user_account'] = $member->ID;

            // Needed for autoresponders - call before storing txn
            \MeprHooks::do_action('mepr-signup-user-loaded', $member);

            if($member->send_notification) {
                $member->send_password_notification('new');
            }
            
            // perform auto login
            sc_maybe_auto_login_user($member->ID, $order['id']);

            $message = __('New member created', 'ncs-cart');
            sc_log_entry($order['id'], $message);

            return $member->ID;
            
        } catch(Exception $e) {
            sc_log_entry($order['id'], $e->getMessage());
            return false;
        }
    }
    
    public static function create_membership($int, $order) {
        $sub = new \MeprSubscription();
        $user_id = $order['user_account'];
        $order['product_id'] = $int['mepr_membership'];
        if(self::create_or_update($sub, $user_id, $order)) {
          $sub = new \MeprSubscription($sub->id);
          $user = $sub->user();
          $sub->user_login = $user->user_login;
          $message = sprintf(__('A MemberPress subscription (ID: %s) was created successfully', 'ncs-cart'), $sub->id);
          
          // add MP sub ID to subscription ID only
          if(isset($order['sc_sub_id'])){
              update_post_meta($order['sc_sub_id'], '_sc_mepr_sub_id', $sub->id);
          }
        }
        else {
          $message = __('There was a problem creating a MemberPress subscription', 'ncs-cart');
        }
        sc_log_entry($order['id'], $message);
        return $sub;
    }
    
    private static function create_or_update($sub, $user_id, $order) {
        
        $plan = $order['plan'];
        
        $subscr_id = $order['subscr_id'];
        $user_login = $order['email'];
        $product_id = $order['product_id'];
        $price = $plan->sub_amount;
        $status = 'active';
        $gateway = $order['gateway'];
        $tax_amount = '0.00';
        $trial_days = $plan->free_trial_days;
        $trial_amount = $plan->sign_up_fee;
        $period = $plan->sub_frequency;
        $period_type = $plan->sub_interval.'s'; // e.g. days, weeks, months, years
        $limit_cycles = ($plan->sub_installments > 1);
        $limit_cycles_num = $plan->sub_installments;
                    
        $created_at = date("Y-m-d H:i:s");
        $sub->user_id = $user_id;
        $sub->subscr_id = wp_unslash($subscr_id);
        $sub->product_id = $product_id;
        $product = new \MeprProduct($product_id);

        $sub->price = isset($price) ? $price : $product->price;
        $sub->period = isset($period) ? (int) $period : (int) $product->period;
        $sub->period_type = isset($period_type) ? (string) $period_type : (string) $product->period_type;
        $sub->limit_cycles = isset($limit_cycles) ? (boolean) $limit_cycles : $product->limit_cycles;
        $sub->limit_cycles_num = isset($limit_cycles_num) ? (int) $limit_cycles_num : (int) $product->limit_cycles_num;
        $sub->limit_cycles_action = isset($limit_cycles_action) ? $limit_cycles_action : $product->limit_cycles_action;
        $sub->limit_cycles_expires_after = isset($limit_cycles_expires_after) ? (int) $limit_cycles_expires_after : (int) $product->limit_cycles_expires_after;
        $sub->limit_cycles_expires_type = isset($limit_cycles_expires_type) ? (string) $limit_cycles_expires_type : (string) $product->limit_cycles_expires_type;
        //$sub->tax_amount = \MeprUtils::format_currency_us_float( $tax_amount );
        //$sub->tax_rate = \MeprUtils::format_currency_us_float( $tax_rate );
        $sub->total = $sub->price;
        $sub->status = $status;
        $sub->gateway = $gateway;
        $sub->trial = isset($trial_days) ? (boolean) $trial_days : false;
        $sub->trial_days = (int) $trial_days;
        $sub->trial_amount = \MeprUtils::format_currency_us_float( $trial_amount );
        if(isset($created_at) && (empty($created_at) || is_null($created_at))) {
          $sub->created_at = \MeprUtils::ts_to_mysql_date(time());
        }
        else {
          $sub->created_at = \MeprUtils::ts_to_mysql_date(strtotime($created_at));
        }
        return $sub->store();
    }
    
	private static function create_transaction( $int, $sc_product_id, $order ) {
        $membership_id = $int['mepr_membership'];        
        $membership = new \MeprProduct($membership_id);
        
        $expires_at = $membership->get_expires_at();
        $gateway = $order['gateway'];
        
        if(isset($order['subscr_id']) && $order['subscr_id']) {
          if(!$sub = \MeprSubscription::get_one_by_subscr_id($order['subscr_id'])) {
            $sub = self::create_membership($int, $order);
          }
           
          $expires_at = $sub->get_expires_at();
          if(isset($sub->trial_days) && $sub->trial_days > 0) {
            $datepay = strtotime($sub->created_at . "+" . $sub->trial_days . " days");
            if(date("Y-m-d") < date("Y-m-d", $datepay)){            
                $expires_at = $datepay;    
            }
          }
        }

        $amt = ($order['order_type'] != 'bump') ? get_post_meta($order['id'], '_sc_amount', true) : $order['amount'];
        $amt = (float) $amt;

        $trans_num = $order['trans_num'];
        
        $txn = \MeprTransaction::get_one_by_trans_num($trans_num);
        if($txn == null) {
            $txn = new \MeprTransaction();
        }

        $txn->trans_num  = sanitize_file_name($trans_num);
        $txn->user_id    = $order['user_account'];
        $txn->product_id = $membership_id;
        $txn->set_gross($amt);
        $txn->status     = 'complete';
        $txn->gateway    = $gateway;
        $txn->expires_at  = (is_null($expires_at)) ? \MeprUtils::mysql_lifetime() : gmdate('c', $expires_at);
        $txn->created_at = gmdate('c');        
        $txn->send_welcome = (isset($int['mepr_send_welcome']) && $int['mepr_send_welcome'] !='');
        
        if(isset($sub)){
            $txn->subscription_id = $sub->id;
        }
        
        $id = $txn->store();
        
        // Don't store the MP ID for one-time charge bumps 
        if($order['order_type'] != 'bump' && $order['option_id'] != 'bump') {
            update_post_meta($order['id'], '_sc_mepr_trans_id', $id);
        }

        if($txn->status==\MeprTransaction::$complete_str) {
            \MeprEvent::record('transaction-completed', $txn);

            // This is a recurring payment
            if(($sub = $txn->subscription()) && $sub->txn_count > 1) {
                \MeprEvent::record('recurring-transaction-completed', $txn);
            } elseif(!$sub) {
                \MeprEvent::record('non-recurring-transaction-completed', $txn);
            }

            if ($txn->send_welcome) {
                \MeprUtils::send_signup_notices($txn);
            }
        }

        \MeprHooks::do_action( 'mepr-signup', $txn );

        $message = __('MemberPress transaction created, ID:', 'ncs-cart');
        sc_log_entry($order['id'], $message . ' ' . $txn->id);
	}
    
    public function get_memberships() {
        $courses = array(''=>__('-- None Found --', 'ncs-cart') );
		$args = array( 'post_type' => 'memberpressproduct', 'posts_per_page' => -1 );
        $the_query = new \WP_Query( $args );
        if ( $the_query->have_posts() ) {
            $courses = array(''=>__('-- Select --', 'ncs-cart'));
            while ( $the_query->have_posts() ) {
                $the_query->the_post();
                $courses[ get_the_ID() ] = get_the_title();
            }            
        }
        wp_reset_query();
		return $courses;
	}
    
    public function maybe_add_paypal_txn($data, $order_info) {
		
        $sub_id = $order_info['subscription_id'];
        if(!$mpid = get_post_meta($sub_id, '_sc_mepr_sub_id', true)){
            if(!$mpid = get_post_meta($order_info['order_id'], '_sc_mepr_sub_id', true)){
                return;
            }
        }

        // only create renewal txns if gateway ID not missing from custom vars
        $data['custom'] = stripslashes ( $data['custom'] );
        $custom_vars = (array)json_decode($data['custom']);
		
        if(isset($custom_vars['gateway_id'])) {
                        
            $payment_status_types         = array('denied','expired','failed');            
            if(!isset($data['subscr_id']) || empty($data['subscr_id']) || in_array(strtolower($data['payment_status']), $payment_status_types)) {
                \MeprPayPalStandardGateway::record_payment_failure();
                return;
            }

            $sub = \MeprSubscription::get_one_by_subscr_id($data['subscr_id']);
            if($sub !== false) {

                $timestamp = isset($data['payment_date']) ? strtotime($data['payment_date']) : time();
				$existing = \MeprTransaction::get_one_by_trans_num($data['txn_id']);
				if($existing == null) {
					$txn = new \MeprTransaction();
					
					$expires_at = $sub->get_expires_at();
					$order = sc_setup_order($sub_id);
					
					if(isset($order->free_trial_days) && $order->free_trial_days > 0) {
						$datepay = strtotime(get_the_time( 'Y-m-d', $sub_id ) ."+".$order->free_trial_days." days");
						if(date("Y-m-d") < date("Y-m-d", $datepay)){            
							$expires_at = $datepay;    
						}
					}

					$txn->created_at = gmdate('c');
					$txn->user_id    = $sub->user_id;
					$txn->product_id = $sub->product_id;
					$txn->gateway    = $sub->gateway;
					$txn->expires_at = gmdate('c', $expires_at);
					$txn->trans_num  = $data['txn_id'];
					$txn->status     = 'complete';
					$txn->subscription_id = $sub->id;
					$amt = $this->get_first_transaction_amount($txn);
					$txn->set_gross($amt);
					$txn->store();

					\MeprUtils::send_transaction_receipt_notices($txn);
                }
            }
        } 
    }
    
    public function get_first_transaction_amount($txn) {      
        
        // leave if not a subscription
        if(!$txn->subscription_id) {
            return $txn->amount;
        }
        
        // leave if this isn't the first txn
        $order = ScrtOrder::get_by_trans_id($txn->trans_num);
        $sub = new ScrtSubscription($order->subscription_id);
        $order = $sub->first_order();
        
        if($txn->trans_num != $order->transaction_id) {
            return $txn->amount;
        }     
        
        // leave if bump integrations haven't run yet
        if (!get_post_meta($order->id, '_sc_mepr_main_updated', true)) {
            return $txn->amount;
        }
        
        if($items = sc_get_order_items($order)) {
            foreach($items as $item) {
                if(isset($item['subscription_id']) && $item['subscription_id'] == $sub->id) {
                    return $item['total_amount'];
                }
            }
        }
        
        return (float) $txn->amount;
    }
}