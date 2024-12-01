<?php

/**
 * The upgrade paths specific functionality of the plugin.
 *
 * @link       https://ncstudio.co
 * @since      2.7
 *
 * @package    NCS_Cart
 * @subpackage NCS_Cart/upgrade-paths
 */

 class NCS_Cart_Upgrade_Path {
    public $sub;
    public $order;
    public $period_end;
    public $credit;
    public $allowed_statuses;
    public $upgrade_options;

    const SC_SUBSCRIPTION = 'sc_subscription';
    
    public function __construct() {

      $this->allowed_statuses = array('trialing', 'active', 'paused');

      $this->validate_subscription();
        
        $this->post_type = 'sc_upgrade_path';
        $this->upgrade_options = false;

        add_action('init', [$this, 'register_post_type'], 99);
        add_filter('sc-cart-cpt-options', [$this, 'post_type_args'], 10, 2);
        add_action('manage_sc_upgrade_path_posts_custom_column', [$this, 'custom_column'], 10, 2 );
        add_filter('manage_sc_upgrade_path_posts_columns', [$this, 'set_custom_edit_columns'] );
        add_filter('sc_subscription_related_orders', [$this, 'get_upgrade_orders'] );
        add_filter('sc_order_related_orders', [$this, 'get_upgrade_subscriptions'] );
        add_action('sc_account_subscription_action_links', [$this, 'add_change_plans_link'] );

        add_action('wp', [$this, 'checkout_form_mods'], 99);
        //add_filter('sc_pricing_fields',[$this,'add_field_to_product']);

        add_filter('sc_checkout_stripe_subscription_args', [$this,'apply_credit_to_stripe_sub'], 10, 3);
        add_filter('sc_trigger_order_integrations', [$this, 'maybe_suppress_paid_integrations'], 10, 2 ); 
        add_action('sc_subscription_trialing', [$this, 'maybe_change_sub_status'], 1, 2 ); 
        add_action('sc_subscription_active', [$this, 'maybe_trigger_order_paid_integrations'], 1, 2 ); 

        add_action('sc_subscription_detail_modals', [$this, 'show_upgrade_path']);
        add_action('init', [$this, 'maybe_process_upgrade_request']);

    }

    public function add_change_plans_link($sub) {

        $this->sc_set_upgrade_options($sub);

        if(!$this->upgrade_options) {
            return;
        }
        ?>

        | <a href="#" id="sc_switch_sub" title="<?php _e("Change Plans", 'ncs-cart'); ?>" data-item-id="switch-sub" class="sc-open-modal">
            <?php _e("Change Plans", 'ncs-cart'); ?>
        </a>

        <?php

    }

    public function maybe_process_upgrade_request() {
        if (isset($_REQUEST['upgrade_path']) && get_post_meta($_REQUEST['upgrade_path'], '_sc_switch_enabled', true)) {
            
            // check if invalid data posted to checkout form
            if(isset($_REQUEST['type']) && (!isset($_REQUEST['_wpnonce']) || !wp_verify_nonce( $_REQUEST['_wpnonce'], $_REQUEST['type'].'-'.$_REQUEST['sc-s'] )) ) {
                echo 'Something went wrong, please try again.';
                return;
            }

            if(isset($_REQUEST['sc-s']) && $this->status_is_upgradeable($_REQUEST['sc-s'])) {
                global $studiocart;
                add_filter('sc_trigger_order_integrations', [$this, 'maybe_link_previous_sub'], 10, 2 ); 
                add_filter('sc_trigger_subscription_integrations', [$this, 'maybe_link_previous_sub'], 10, 2 ); 
                add_filter('studiocart_plan', [$this, 'check_for_upgrade_or_downgrade'], 10, 2);
                add_action('sc_after_setup_atts_from_post', [$this, 'add_order_parent']);
                add_action('sc_after_load_from_post', [$this, 'remove_shipping'], 1);
                add_action('sc_order_pre_calculate_tax', [$this, 'maybe_apply_credit']);
                add_action('sc_after_load_from_post', [$this, 'add_summary_item']);
                add_filter('_sc_plan', [$this, 'show_new_sub_start_date'], 10, 3);
                add_filter('sc_format_subcription_order_detail',[$this, 'sync_order_detail'], 10, 7);
                add_filter('studiocart_order_form_fields',[$this, 'checkout_validation'],10,2);
                add_filter('studiocart_product',[$this, 'remove_upsell_path'],10,2);
                add_filter('studiocart_product',[$this, 'display_default_form'],10,2);
                remove_action('sc_card_details_fields', 'sc_orderbumps', 15, 2);
                remove_action( 'template_redirect', [$studiocart, 'sc_redirect'] );
            }

            if(isset($_REQUEST['upgrade_type'])) {
                global $studiocart;
                add_action('sc_before_create_main_order', [$this, 'add_customer_info_to_post'], 1);
                remove_action('sc_before_create_main_order', array($studiocart, 'check_product_purchase_limit'));
                remove_action('sc_before_create_main_order', array($studiocart, 'validate_order_form_submission'));
            }
        }
    }

    public function status_is_upgradeable($order_id) {
        return (in_array(get_post_meta($order_id, '_sc_status', true), $this->allowed_statuses) || !get_post_meta($order_id, '_sc_cancel_date', true));
    }

    public function display_default_form($arr) {
        unset($arr['display'], $arr['show_2_step'], $arr['show_optin']);
        return $arr;
    }

    public function get_upgrade_subscriptions($order) {
        if ($order->order_parent && $order->order_type == 'upgrade' || $order->order_type == 'downgrade') :
            $parent = new ScrtSubscription($order->order_parent);
            if(!$parent->id) { return; }
            $label = ($order->order_type == 'upgrade') ? __('Upgraded from') : __('Downgraded from');
            ?>
            <tr>
                <td>
                    <a href="<?php echo get_edit_post_link($parent->id); ?>" class="sc-order-item-name">#<?php echo $parent->id; ?></a>
                </td>
                <td><?php echo $label; ?></td>
                <td><?php echo get_the_date('F d, Y h:i a', $parent->id); ?></td>
                <td class="name"><?php echo $parent->get_status(); ?></td>
                <td class="sub_cost">
                    <div class="view">
                        <span class="sc-Price-amount amount">
                            <?php sc_formatted_price($parent->sub_amount); ?>
                        </span> / 
                        <?php 
                        $frequency = $parent->sub_frequency;
                        $interval = $parent->sub_interval;
                        if($frequency > 1) {
                            echo $frequency . ' ' . sc_pluralize_interval($interval);
                        } else {
                            echo $interval;
                        }
                        ?>
                    </div>
                </td>
            </tr>
            <?php
        endif;

    }

    public function get_upgrade_orders($sub) {
        $child_orders = get_post_meta($sub->id, '_sc_order_child');
        foreach($child_orders as $order) {
                $order = new ScrtOrder($order['id']); ?>
                <tr>
                    <td>
                        <a href="<?php echo get_edit_post_link($order->id); ?>" class="sc-order-item-name">#<?php echo $order->id; ?></a>
                    </td>
                    <td><?php echo $order->order_type; ?></td>
                    <td><?php echo get_the_date('F d, Y h:i a', $order->id); ?></td>
                    <td class="name"><?php echo $order->get_status(); ?></td>
                    <td class="sub_cost">
                        <div class="view">
                            <span class="sc-Price-amount amount">
                                <?php sc_formatted_price($order->amount); ?>
                            </span>
                        </div>
                    </td>
                </tr>
                <?php
        }
    }

    // send the correct prorated amount to Stripe for today's payment
    public function apply_credit_to_stripe_sub($args, $order, $sub) {
        if(isset($order->credit) && $order->credit > 0) {
            //$amt = $args['add_invoice_items'][0]['price_data']['unit_amount'];
            $amt = $order->pre_tax_amount;
            $args['add_invoice_items'][0]['price_data']['unit_amount'] = sc_price_in_cents(sc_format_number($amt) - sc_format_number($order->credit)); 
        }
        return $args;
    }

    public function maybe_suppress_paid_integrations($do_trigger, $order) {
        if($order->status == 'paid' && self::is_upgrade($order->id)) {

            $this->maybe_cancel_previous_sub($order->id);

            if(!self::switch_immediately($order->id)) {

                // trigger pending integrations / emails
                $order_info = $order->get_data();
                $order_info['status'] = 'pending-payment';
                $order_info['pay_method'] == 'cod'; // required for pending integrations to run
                sc_trigger_integrations($order_info['status'], $order_info);

                // Don't run paid integrations
                return false;
            }
        }

        return $do_trigger;
    }

    public function maybe_change_sub_status($status, $sub) {
        if(self::is_upgrade($sub['id'])) {
            //Cancel trialing integrations
            remove_all_actions('sc_subscription_trialing');
            $sub = new ScrtSubscription($sub['id']);                

            // assign correct upgrade status
            if (self::switch_immediately($sub->id)) {
                $sub->sub_status = 'active';
                $sub->status = 'active';
            } else {
                $sub->sub_status = 'pending-payment';
                $sub->status = 'pending-payment';
            }
            $sub->store();
        }
    }

    public function maybe_trigger_order_paid_integrations($status, $sub) {
        if(self::is_upgrade($sub['id']) && !self::switch_immediately($sub['id'])) {
            $sub = new ScrtSubscription($sub['id']);
            $order = $sub->first_order();

            update_post_meta($order->id, '_sc_switch_immediately', true);
            update_post_meta($sub->id, '_sc_switch_immediately', true);
            
            $order->trigger_integrations();
        }
    }

    public static function switch_immediately($id) {
        return get_post_meta($id, '_sc_switch_immediately', true);
    }

    public static function is_upgrade($id) {
        return get_post_meta($id, '_sc_former_sub_id', true);
    }

    public function maybe_link_previous_sub($trigger, $post) {
        $sub_id = intval($_REQUEST['sc-s']);
        update_post_meta($post->id, '_sc_former_sub_id', $sub_id);
        update_post_meta($post->id, '_sc_switch_immediately', $this->is_immediate());
        return $trigger;
    }

    public function maybe_cancel_previous_sub($order) {
        if($sub = self::is_upgrade($order)) {
            
            $now = self::switch_immediately($order);
            $sub = new ScrtSubscription($sub);
            
            if($sub->id && $sub->sub_status != 'canceled') {
                $out = sc_do_cancel_subscription($sub, $sub->subscription_id, $now, $echo=false);
                
                if($out == 'OK') {
                    sc_log_entry($order, sprintf(__("Switching from subscription ID: %s", 'ncs-cart'), $sub->id));
                } else {
                    sc_log_entry($order, sprintf(__("Error switching from subscription ID: %s! Message: %s", 'ncs-cart'), $sub->id, $out));
                }
            }
        }
    }

    public function remove_upsell_path($arr) {
        $arr['upsell_path'] = false;
        return $arr;
    }

    public function add_customer_info_to_post() {
        $sub = new ScrtSubscription($_POST['sc-s']);

        $_POST['email'] = $sub->email;
        $_POST['first_name'] = $sub->first_name;
        $_POST['last_name'] = $sub->last_name;
        $_POST['customerId'] = $sub->customer_id;
        $_POST['phone'] = $sub->phone;
        $_POST['company'] = $sub->company;

        $address_info = array('address1', 'address2', 'city', 'state', 'zip', 'country');
        foreach ($address_info as $info) {
            if (isset($sub->$info)){
                $_POST[$info] = $this->$info;
            }
        }
      }

    public function validate_subscription(){
      if(isset($_REQUEST['sc-s'])){
         $sc_subs_id = sanitize_text_field($_REQUEST['sc-s']);
         $stripe_subs_id = get_post_meta($sc_subs_id,'_sc_subscription_id',true);

         if(!$stripe_subs_id){
         // To Do: Handle error gracefully
         echo 'Subscription not found.';
         exit;
         }
      }
      
    }

    public function dump($data){
      echo '<pre/>';print_r($data);exit;
    }

    public function add_field_to_product($pricing){

         
        $pricing['Switch Heading'] = array(
            'class'		=> 'widefat',
            'id'		=> '_sc_twostep_heading',
            'type'		=> 'html',
            'value'		=> '<div id="rid_sc_switch_heading" class="sc-field sc-row"><h4 style="margin: 0;padding: 0 0 5px;border-bottom: 1px solid #d5d5d5;flex-basis: 100%;">'.__('Plan Switching','ncs-cart').'</h4></div>',
            'class_size'=> '',
        );

        $pricing['Show Plan Change'] = array(
            'class'		    => 'widefat',
            'note'	=> '',
            'description'	=> __('Allow users to switch between different payment plans within their account.','ncs-cart'),
            'id'			=> '_sc_show_change_plan',
            'label'		    => __('Enabled','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'checkbox',
            'value'		    => '',
            'class_size'    => '',
        );

        $pricing['Allow Plan Change'] = array(
            'class'		    => 'widefat',
            'description'	=> __('Override upsell paths involving this product and allow customers to switch between the plans within this product only.','ncs-cart'),
            'note'	=> '',
            'id'			=> '_sc_change_plan',
            'label'		    => __('Internal','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'checkbox',
            'value'		    => '',
            'class_size'    => '',						
            'conditional_logic' => array(
                array(
                    'field' => '_sc_show_change_plan',
                    'value' => true,
                )
            )
        );
        
        $pricing['Upgrading Display'] =array(
            'class'		    => '',
            'description'	=> '',
            'id'			=> '_sc_upgrade_display',
            'label'	    	=> __('Display Restrictions','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'select',
            'value'		    => '',
            'selections'    => array(
                'all' => __('No restrictions','ncs-cart'),
                'upgrades_only' => __('Show upgrades/equal cost plans only','ncs-cart'),
                'downgrades_only' => __('Show downgrades only','ncs-cart'),
            ),
            'class_size'=> '',						
            'conditional_logic' => array(
                array(
                    'field' => '_sc_show_change_plan',
                    'value' => true,
                )
            )
        );

        $pricing['Upgrade Plan'] = array(
            'class'		    => '',
            'description'	=> '',
            'id'			=> 'upgrade_immediately',
            'label'	    	=> __('Upgrade plans','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'select',
            'value'		    => '',
            'selections'    => array(
                'yes' => __( 'immediately'),
                'no' => __( 'at the end of the current billing period'),
            ),
            'class_size'=> '',						
            'conditional_logic' => array(
                array(
                    'field' => '_sc_show_change_plan',
                    'value' => true,
                ),
            )
        );

        $pricing['Downgrade Plan'] = array(
            'class'		    => '',
            'description'	=> '',
            'id'			=> 'downgrade_immediately',
            'label'	    	=> __('Downgrade plans','ncs-cart'),
            'placeholder'	=> '',
            'type'		    => 'select',
            'value'		    => '',
            'selections'    => array(
                'yes' => __( 'immediately'),
                'no' => __( 'at the end of the current billing period'),
            ),
            'class_size'=> '',						
            'conditional_logic' => array(
                array(
                    'field' => '_sc_show_change_plan',
                    'value' => true,
                )
            )
        );   
        return $pricing;
    }

    public function sc_set_upgrade_options($order){

      if(!$this->status_is_upgradeable($order->id)) {
        return;
      }

      if(get_post_type($order->id) == self::SC_SUBSCRIPTION){
        
        if (get_post_meta($order->product_id, '_sc_change_plan', true)) {
          $this->upgrade_options = $this->get_current_product_plans($order);
        }else{
          // $this->dump($order);
          $this->upgrade_options = $this->sc_get_upgrade_path_plans($order);
        }

      }
   }

    public function show_upgrade_path($order){

        global $upgrade_options, $button_color;

        if(!$this->upgrade_options || get_post_type($order->id) != self::SC_SUBSCRIPTION) {
            return;
        }

        $upgrade_options = $this->upgrade_options;

        $button_color = get_post_meta($order->product_id, '_sc_button_color', true);

        $template = NCS_CART_BASE_DIR ."public/templates/my-account/parts/upgrade-options.php";

        include( $template );

    }

   public function get_current_product_plans($order){

      $upgrade_plans = [];
      $plan = [];
      $pay_options = get_post_meta($order->product_id, '_sc_pay_options', true);

      if (!empty($pay_options)) {

         foreach ($pay_options as $pay_option) {
            if ($pay_option['product_type'] == 'recurring') {
               $plan['is_current_plan'] = 0;
               $plan['prod_product'] = $order->product_id;
               $plan['prod_plan'] = $pay_option['option_id'];
               $plan['option_name'] = $pay_option['option_name'];
               if ($this->is_current_plan($order, $plan)) {
                  $plan['is_current_plan'] = 1;
                  $plan['button_text'] = __('Current Plan', "ncs-cart");
               }
               $plan['payment_plan'] = $pay_option;
               $plan['stripe_subscription_id'] = $order->subscription_id;
               $plan['sc_subscription_id'] = $order->ID;
               $plan['args'] = array('prod_id' => $order->product_id, 'plan' => $pay_option['option_id'], 'sc-s' => $order->ID, 'sc_prod' => $order->product_id);
               $upgrade_plans[] = $plan;
            }
         }

         $this->check_upgrade_downgrade($upgrade_plans);
      }
      
      return $upgrade_plans;
        
   }

   public function sc_get_upgrade_path_plans($order) {
      
      global $upgrade_path_id;
      
      $upgrade_available = false;

      $args = array(
         'post_type' => 'sc_upgrade_path',
         'post_status' => 'publish',
         'meta_query' => array(
            array(
               'key' => '_sc_switch_enabled',
               'compare' => 'EXISTS'
            ),
         ) 
      );

      $posts = get_posts($args);
      
      if (!empty($posts)) {
         foreach ($posts as $post) {
            $upgrade_path_options = get_post_meta($post->ID, '_sc_pay_options', true);
            if (!empty($upgrade_path_options)) {
               foreach ($upgrade_path_options as $option) {
                  if ($option['prod_product'] == $order->product_id && $option['prod_plan'] == $order->option_id) {
                     $upgrade_available = true;
                     break;
                  }
               }

               if ($upgrade_available) {
                  $upgrade_path_id = $post->ID;
                  return $this->prepare_upgrade_paths($post->ID,$upgrade_path_options,$order);
               }
              
            }
         }
      }
      return [];
   }


   public function prepare_upgrade_paths($id,$upgrade_path_options,$order){

      $upgrade_available = false;
      $upgrade_plans = [];

      foreach ($upgrade_path_options as $option) {
         $option['is_current_plan'] = 0;
         if ($option['prod_product'] == $order->product_id && $option['prod_plan'] == $order->option_id) {
            $upgrade_available = true;
            $option['is_current_plan'] =  $this->is_current_plan($order, $option);
            $option['button_text'] = __('Current Plan', "ncs-cart");
         }

         $payment_plans = get_post_meta($option['prod_product'], '_sc_pay_options', true);

         $filteredPlan = array_filter($payment_plans, function ($item) use ($option) {
            return isset($item['option_id']) && $item['option_id'] === $option['prod_plan'];
         });

         $option['payment_plan'] = current($filteredPlan);
         $option['option_name'] = $option['option_name'] ?? $option['payment_plan']['option_name'] ?? '';
         $option['stripe_subscription_id'] = $order->subscription_id;
         $option['sc_subscription_id'] = $order->ID;

         //$prod_post = get_post($option['prod_product']);
         $option['upgrade_url'] = $this->get_checkout_url($option['prod_product']);
         $option['args'] = array('prod_id' => $option['prod_product'], 'plan' => $option['payment_plan']['option_id'], 'sc-s' => $order->ID, 'upgrade_path'=>$id);
         $upgrade_plans[] = $option;
      }

      if ($upgrade_available) {
         $this->check_upgrade_downgrade($upgrade_plans);
         return $this->check_filters($upgrade_plans, $id);
      }

   }

    public function get_checkout_url($id) {
        if (get_post_meta($id, '_sc_hide_product_page', true )) {
            $redirect = get_post_meta($id, '_sc_product_page_redirect', true);
            if (!$redirect) {
                $redirect = get_home_url();
            } else {
                $redirect = get_permalink($redirect);
            }
            
            return $redirect;
        }  

        return get_permalink($id);

    }

   public function check_filters($upgrade_plans, $upgrade_path_id) {
    
        if(!$filters = get_post_meta($upgrade_path_id, '_sc_upgrade_filter', true)) {
            return $upgrade_plans;
        }

        for($i=0;$i<count($upgrade_plans);$i++){
            switch($upgrade_plans) {
                case ($upgrade_plans[$i]['is_current_plan']==1):
                    if(!in_array('current', $filters)) { 
                        unset($upgrade_plans[$i]);
                    }
                    continue 2;
                case ($upgrade_plans[$i]['same_cost_as_current']==1):
                    if(!in_array('same', $filters)) { 
                        unset($upgrade_plans[$i]);
                    }
                    continue 2;
                case (isset($upgrade_plans[$i]['args']['type'])):
                    if(!in_array($upgrade_plans[$i]['args']['type'], $filters)){
                        unset($upgrade_plans[$i]);
                    }
                    continue 2;
                default:
                    continue 2;
            }
        }
        return $upgrade_plans;
   }


   public function is_current_plan($order, $upgrade_plan)
   {
      if ($upgrade_plan['prod_product'] == $order->product_id && $upgrade_plan['prod_plan'] == $order->option_id) {
         return true;
      }
      return false;
   }

   public function check_upgrade_downgrade(&$upgrade_plans)
   {
      global $sc_stripe;

      $currentPlans = array_filter($upgrade_plans, function ($plan) {
         return $plan['is_current_plan'] == 1;
      });

      $currentPlan = current($currentPlans);

      foreach ($upgrade_plans as $key => $plan) {
        $upgrade_plans[$key]['same_cost_as_current'] = 0;

         if ($plan['is_current_plan'] == 1) {
            continue;
         }

         if ($this->calculate_subscription_cost($currentPlan['payment_plan'],$currentPlan['prod_product']) > $this->calculate_subscription_cost($plan['payment_plan'],$plan['prod_product'])) {
            $upgrade_plans[$key]['button_text'] = __('Downgrade Plan', 'ncs-cart');
            $upgrade_plans[$key]['args']['type']='downgrade';
         } else {
            $upgrade_plans[$key]['button_text'] = __('Upgrade Plan', "ncs-cart");
            $upgrade_plans[$key]['args']['type'] = 'upgrade';

            if ($this->calculate_subscription_cost($currentPlan['payment_plan'],$currentPlan['prod_product']) == $this->calculate_subscription_cost($plan['payment_plan'],$plan['prod_product'])) {
                $upgrade_plans[$key]['same_cost_as_current'] = 1;
            }

         }
      }
   }

   /**
    * Calculate yearly cost for a plan to determine if a plan is upgradable or downgradable
    * Used in function check_upgrade_downgrade()
    */
   public function calculate_subscription_cost($plan,$prod_id)
   {

      $price = $plan['price'];
      $interval = $plan['interval'];
      $frequency =  $plan['frequency'];

      if(isset($plan['sale_price']) && sc_is_prod_on_sale($prod_id)){
         $price =  $plan['sale_price'];
         $interval = $plan['sale_interval'];
         $frequency = $plan['sale_frequency'];
      }

      if ($interval == 'week') {
         $newPrice = ($price / (7 * $frequency)) * 30 * 12;
      } else if ($interval == 'month') {
         $newPrice = $price / (30.417 * $frequency) * 30 * 12;
      } else if ($interval == 'year') {
         $newPrice = $price / (365 * $frequency) * 30 * 12;
      } else {
        $newPrice = $price / ($frequency) * 30 * 12;
      }

      return $newPrice;
   }

   public function calculate_proration_period_in_days($plan, $credit)
   {

      $price = $plan['price'];
      $interval = $plan['interval'];
      $frequency =  $plan['frequency'];

      if ($interval == 'week') {
         $dailyPrice = ($price / (7 * $frequency));
      } else if ($interval == 'month') {
         $dailyPrice = $price / (30.417 * $frequency);
      } else if ($interval == 'year') {
         $dailyPrice = $price / (365 * $frequency);
      } else {
        $dailyPrice = $price / $frequency;
      }

      return ceil($credit / $dailyPrice);

   }

    /**
     * Skip user details validation for subscription upgrade/downgrade checkout
     */
    public function checkout_validation($fields,$scp){
      $fields['first_name']['required'] = false;
      $fields['last_name']['required'] = false;
      $fields['email']['required'] = false;
      return $fields;
    }

    public function remove_shipping() {
        global $sc_shipping;
        remove_action('sc_after_load_from_post', [$sc_shipping, 'add_shipping'], 10 );
    }

    public function checkout_form_mods() {
        if(isset($_REQUEST['sc-s'])) {
            add_action('sc_checkout_form_open', [$this, 'confirm_checkout_form_sections'], 20);
        }
    }
    
    function confirm_checkout_form_sections() {
        global $scp, $sc_qty;
        $scp->button_text = __('Confirm', 'ncs-cart').' '; 
        $scp->button_icon_pos = '';
        $nonce = wp_create_nonce("sc_purchase_nonce"); 

        add_action('sc_card_details_fields', [$this, 'show_selected_plan'], 1, 3);
        remove_action('sc_card_details_fields', 'sc_payment_plan_options', 1);
        remove_action('sc_card_details_fields', 'sc_payment_plan_options', 5);
        remove_action('sc_card_details_fields', 'sc_do_checkoutform_fields', 5);
        remove_action('sc_card_details_fields', [$sc_qty, 'quantity_field'], 9); 
      //   add_action('sc_after_summary_items', [$this, 'sc_show_pay_method'], 10);

        ?>
        
        <input type="hidden" name="sc_process_payment" value="1">
        <input type="hidden" name="sc_amount" value="">
        <input type="hidden" name="sc_product_id" value="<?php echo $scp->ID; ?>">
        <input type="hidden" name="sc-nonce" value="<?php echo $nonce; ?>">
        <input type="hidden" name="action" value="save_order_to_db">
        <input type="hidden" name="upgrade_type" value="<?php echo sanitize_text_field($_REQUEST['type']); ?>">
        <input type="hidden" name="upgrade_path" value="<?php echo sanitize_text_field($_REQUEST['upgrade_path'] ?? ''); ?>">
        <input type="hidden" id="sc-s" name="sc-s" value="<?php echo sanitize_text_field($_REQUEST['sc-s']); ?>">

        <?php
    }

    function get_filtered_plan($plan, $sale, $pid='') {
        remove_filter('studiocart_plan', [$this, 'check_for_upgrade_or_downgrade'], 10, 2);
        add_filter('studiocart_plan', [$this, 'remove_trial_and_fee'], 10, 2);
        $plan = studiocart_plan($plan, $sale, $pid);
        remove_filter('studiocart_plan', [$this, 'remove_trial_and_fee'], 10, 2);
        add_filter('studiocart_plan', [$this, 'check_for_upgrade_or_downgrade'], 10, 2);
        return $plan;
    }

    function remove_trial_and_fee($option, $sale) {
        unset($option['trial_days'],$option[$sale.'sign_up_fee']);
        return $option;
    }

    function calculate_proration() {
        if($this->sub->sub_status == 'trialing') {
            return 0;
        }

        $credit = (float) $this->sub->sub_amount;
        $last = new DateTime($this->order->date);
        $last->setTimestamp(strtotime($this->order->date));
        $last->modify('midnight');

        $now = new DateTime("today midnight");
        
        $next = new DateTime();
        $next->setTimestamp($this->sub->sub_next_bill_date);
        $next->modify('midnight');
        
        $num = $last->diff($now)->days;
        $den = $last->diff($next)->days;

        return $credit*(($den-$num)/$den);
    }

    /**
     * Check is plan upgrade/downgrade is immediate or after current billing period end
     */
    public function is_immediate(){

      $parameters = array();
      
      if(isset($_REQUEST['upgrade_path'])){
        //$qstr = parse_url($_REQUEST['upgrade_path'], PHP_URL_QUERY);
        //parse_str($qstr, $parameters);
        //if(isset($parameters['upgrade_path'])){
          if(isset($_REQUEST['type']) && in_array($_REQUEST['type'],['upgrade','downgrade'])){
            return get_post_meta($_REQUEST['upgrade_path'], '_sc_switch_'.$_REQUEST['type'], true);
          } else if(isset($_REQUEST['upgrade_type']) && in_array($_REQUEST['upgrade_type'],['upgrade','downgrade'])){
            return get_post_meta($_REQUEST['upgrade_path'], '_sc_switch_'.$_REQUEST['upgrade_type'], true);
          }

      } else if(isset($_REQUEST['sc_prod'])){

          // If upgrade/downgrade plans are coming from the same prosduct
          $pay_options = get_post_meta($_REQUEST['sc_product_id'], '_sc_pay_options',true);
          if(!empty($pay_options)){
            foreach($pay_options as $key => $op){
              if($op['option_id'] == $_REQUEST['sc_product_option']){
                return ($op['upgrade_immediately'] == 'yes') ? true : false;
              }
            }
          }
        }
      //}
      
      return false;
    }

    function check_for_upgrade_or_downgrade($option, $sale) {
        
        if(!isset($_REQUEST['sc-s'])) {
            return $option;
        }

        $this->credit = false;
        // $sub = sanitize_text_field($_REQUEST['sc-s']);
        $sub_id = sanitize_text_field($_REQUEST['sc-s']);
        $sub = get_post_meta($sub_id,'_sc_subscription_id',true);
        $this->sub = ScrtSubscription::get_by_sub_id($sub);
        $this->order = $this->sub->last_order();
        
        $credit = $this->calculate_proration();

        $option = $this->remove_trial_and_fee($option, $sale);
        $plan = $this->get_filtered_plan($option['option_id'], $sale);
        $now = new DateTime("today midnight");
        $next = new DateTime();
 
        // calculate proration discount and set trial for next billing date on new sub 
        if($this->is_immediate() && $credit) {
            $next->setTimestamp($plan->next_bill_date);
            $interval = $now->diff($next);
            $option['trial_days'] = $interval->days;

            if($credit <= $option[$sale.'price']){
                $option[$sale.'sign_up_fee'] = $option[$sale.'price'];
                $this->credit = $credit;
            } else {
                // calculate prorated adjustment period
                $this->credit = $credit;
                $price = isset($option[$sale.'price']) ? $option[$sale.'price'] : $option['price'];  
                
                // multiply new plan billing period if pricing is similar
                if ($this->credit%$price == 0) {
                    $multiplier = $this->credit/$price;
                    $plan->next_bill_date = strtotime(date("Y-m-d", strtotime("+".($option['frequency']*$multiplier)." " . $option['interval'])));
                    
                    $next->setTimestamp($plan->next_bill_date);
                    $interval = $now->diff($next);
                    $option['trial_days'] = $interval->days;
                } else {
                    $option['trial_days'] = $this->calculate_proration_period_in_days($option, $credit);
                }

                // $option['trial_days'] = ceil($credit / intval($option[$sale.'price']) * $option['trial_days']);
                
                $this->credit = $option[$sale.'sign_up_fee'] = $option[$sale.'price'];
                $this->period_end = true;
            }

        } else if(!$this->is_immediate()){
            // use trial to start 1st payment on next billing date on current sub 
            $next->setTimestamp($this->sub->sub_next_bill_date);
            $interval = $now->diff($next);

            // need to revisit, next billing date shouldn't be in the past but handling it like this for now...
            if($next < $now) {
                $option['trial_days'] = 0;
            } else {
                $option['trial_days'] = $interval->days;
            }
        }
        
        return $option;
    }

    function sync_order_detail($text, $terms, $plan='', $trial_days=false, $sign_up_fee=false, $discount=false, $discount_duration=false) {
        if($trial_days && $this->is_immediate()) {
            return sprintf(__('Then %s starting on %s', 'ncs-cart'), $terms, sc_maybe_format_date($plan->next_bill_date));
        } else if($trial_days && !$this->is_immediate()) {
            return sprintf(__('%s starting on %s', 'ncs-cart'), $terms, sc_maybe_format_date($plan->next_bill_date));
        }
        return $text;
    }

    function show_new_sub_start_date($plan, $option, $sale='') {

        $plan = (object) $plan;

        if($plan->frequency > 1) {
            $text = sc_format_price($plan->price) . ' / ' . $plan->frequency . ' ' . sc_pluralize_interval($plan->interval);
            $text_plain = sc_format_price($plan->price, false) . ' / ' . $plan->frequency . ' ' . sc_pluralize_interval($plan->interval);
        } else {
            $text = sc_format_price($plan->price) . ' / ' . $plan->interval; 
            $text_plain = sc_format_price($plan->price, false) . ' / ' . $plan->interval; 
        }
        
        $text_terms = '';

        $installments = $plan->installments;
        if ($installments > 1) {
            $text_terms .=  ' x ' . $installments;
        }

        $text .= $text_terms;
        $text_plain .= $text_terms;

        $plan->text = $text;
        $plan->text_plain = $text;

        $plan->start_text = false;

        if ($this->is_immediate()) {
            
            if ($this->period_end) {
                $this->period_end = $plan->next_bill_date;
            }

            $start = __('immediately','ncs-cart');
            $plan->start_text = ' ' . sprintf(__('You will switch to this plan %s','ncs-cart'), '<b>'.$start.'</b>');
        } else {
            if ($this->period_end) {
                $this->period_end = $plan->next_bill_date;
            } else if (!$this->credit && $plan->trial_days) {
                $start = sc_maybe_format_date($plan->next_bill_date);
                $plan->start_text = ' ' . sprintf(__('You will switch to this plan on %s','ncs-cart'), '<b>'.$start.'</b>');
            }
        }
       
        return (array) $plan;
    }

    function maybe_apply_credit($order) {

        if($this->credit === false) return;

        $discount = $this->credit;

        $item_count = $order->calculate_pre_tax_amount_from_items();

        if($discount == $order->pre_tax_amount) {
            foreach($order->items as $k=>$item) {
                $item['discount_amount'] = $item['subtotal'];
                $item['total_amount'] = $item['subtotal'] - $item['discount_amount'];
                $item = $order->maybe_apply_tax_to_item($item);
                $order->items[$k] = $item;
            }
            $order->credit = $discount;
        } else if($discount < $order->pre_tax_amount) {
            $order->credit = $discount;
            $item_discounts = $order->divide_money_evenly($discount, $item_count);
            $i = 0;
            foreach($order->items as $k=>$item) {
                $item['discount_amount'] = $item['discount_amount'] ?? 0;
                $item['discount_amount'] += $item_discounts[$i];
                $item['total_amount'] = $item['subtotal'] - $item['discount_amount'];
                $item = $order->maybe_apply_tax_to_item($item);
                $order->items[$k] = $item;
                $i++;
            }
            $order->credit = $discount;
        }
    }

    function add_order_parent($order) {
        $sub_id = sanitize_text_field($_REQUEST['sc-s']);
        $order->order_parent = $sub_id;
        $order->order_type = sanitize_text_field($_REQUEST['upgrade_type']);
    }

    function add_summary_item($order) {

        if(isset($order->credit) && $order->credit > 0) {

            if($this->period_end) {
                $label = sprintf(__('Prorated adjustment (%s - %s)','ncs-cart'), sc_maybe_format_date('now', 'M d, Y'), sc_maybe_format_date($this->period_end, 'M d, Y'));
            } else {
                $label = __('Proration credit', 'ncs-cart');
            }

            $list = array();
            $found = false;
            for($i=0; $i<count($order->order_summary_items); $i++) {
                if($i==0) { 
                    $order->order_summary_items[$i]['name'] = __('Subtotal', 'ncs-cart');
                }
                
                // show before tax
                if(!$found && $order->order_summary_items[$i]['type'] == 'tax') {
                    $list[] = array(
                        'name'          => $label,
                        'total_amount'  => $order->credit, 
                        'subtotal'      => $order->credit, 
                        'type'          => 'discount'
                    );
                    $found = true;
                }
                
                $list[] = $order->order_summary_items[$i];
                
                // show at end if no tax
                if(!$found && ($i+1) == count($order->order_summary_items)) {
                    $list[] = array(
                        'name'          => $label,
                        'total_amount'  => $order->credit, 
                        'subtotal'      => $order->credit, 
                        'type'          => 'discount'
                    );
                    $found = true;
                }
            }

            $order->order_summary_items = $list;
        } else {
            $sub = ScrtSubscription::from_order($order);
            $sub = $sub->get_data();

            for($i=0; $i<count($order->order_summary_items); $i++) {
                if($i==0) {
                    $order->order_summary_items[$i]['subtotal'] = $sub['amount'];
                } else if ($order->order_summary_items[$i]['type'] == 'tax') {
                    $order->order_summary_items[$i]['subtotal'] = $sub['tax_amount'];
                }
            }
        }
    }

    function sc_show_pay_method($scp) {
        $sub = $this->sub;
        ?>
<p style="
    /* margin-bottom: 30px; */
    display: flex;
    border-top: 1px solid #ddd;
    padding: 10px 0;
">
    <span id="update-card" class="openmodal update_card" href="javascript:void(0);" data-id="1140" data-item-id="test" style="
    flex-grow: 1;
">Payment Method</span><img decoding="async" class="sc-card-icon" src="https://chloe-miller.local/wp-content/plugins/studiocart-pro/public/images/cc/visa.svg" style="
    margin-right: 10px;
    width: 35px;
    vertical-align: bottom;
">xxxx 4242 (Exp. 4/2024) | <a id="update-card" class="openmodal update_card" href="javascript:void(0);" data-id="1140" data-item-id="test" style="
    margin-left: 5px;
"> change</a>
</p>
        <?php
    }

    function order_summary($post_id) { 
        global $scp, $scuid;         
        $scp->button_text = __('Confirm', 'ncs-cart').' '; 
        $scp->button_icon_pos = '';
        $scp->button_icon = false;
        ?>
        
        <div class="sc-section sc-order-summary">
            <div class="row">
              <div class="form-group col-sm-12">
                 <div class="total">
                     <?php esc_html_e("Due Today", "ncs-cart"); ?> 
                     <span class="price"></span>
                     <small></small>
                 </div>
              </div>
            </div>
            <div class="row">   
              <div class="form-group col-sm-12">
                <?php $terms = $scp->terms_url;
                    $privacy = $scp->privacy_url;
                if( $terms || $privacy || ( $scp->show_optin_cb ) ): ?>
                    <div id="sc-terms">
                <?php endif; ?>
                        
                    <?php 
                    $class = ( isset($_POST["sc_errors"]['_sc_accept_terms']) ) ? 'invalid' : '';
                    // terms and conditions
                    if( $terms ): ?>
                        <?php $terms_text = sprintf(esc_html__("I have read and I accept the %sterms and conditions%s", 'ncs-cart'), '<a href="'. $terms .'" target="_blank" rel="noopener noreferrer">', '</a> <span class="req">*</span>'); ?>
    
                        <div class="checkbox-wrap <?php echo $class; ?>">
                        <label>
                            <input type="checkbox" class="required" id="sc_accept_terms" name="sc_accept_terms" value="yes"> 
                            <span class="item-name"><?php echo apply_filters('sc_checkout_page_terms_text', $terms_text, $scp); ?></span>
                        </label>
                        </div>
                        <?php if (isset($_POST["sc_errors"]["sc_accept_terms"])) : ?>
                             <div class="error"><?php esc_html_e( $_POST["sc_errors"]["sc_accept_terms"], 'ncs-cart' ); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
                        
                    <?php
                    // privacy policy
                    if( $privacy ): ?>
                        <?php $privacy_text = sprintf(esc_html__("I have read and I accept the %sprivacy policy%s", 'ncs-cart'), '<a href="'. $privacy .'" target="_blank" rel="noopener noreferrer">', '</a> <span class="req">*</span>'); ?>
                        <div class="checkbox-wrap <?php echo $class; ?>">
                            <label>
                                <input type="checkbox" class="required" id="sc_accept_privacy" name="sc_accept_privacy" value="yes"> 
                                <span class="item-name"><?php echo apply_filters('sc_checkout_page_privacy_text', $privacy_text, $scp); ?></span>
                            </label>
                        </div>
                        <?php if (isset($_POST["sc_errors"]["sc_accept_privacy"])) : ?>
                             <div class="error"><?php esc_html_e( $_POST["sc_errors"]["sc_accept_privacy"], 'ncs-cart' ); ?></div>
                        <?php endif; ?>
                    <?php endif; ?>
    
                    <?php 
                    // consent
                    if( $scp->show_optin_cb ): ?>
                        <?php  
                        $scp->optin_required = isset($scp->optin_required);
                        $required = apply_filters('sc_consent_required', $scp->optin_required, $scp); ?>
                        <div class="checkbox-wrap <?php echo $class; ?>">
                            <label>
                                <input type="checkbox" id="sc_consent" name="sc_consent" value="yes" <?php if ($required) { echo 'class="required"'; } ?> > 
                                <span class="item-name"><?php echo wp_specialchars_decode( $scp->optin_checkbox_text, 'ENT_QUOTES' ); ?>
                                <?php if ($required) { echo '<span class="req">*</span>'; } ?>
                                </span>
                            </label>
                        </div>
                    <?php endif; ?>
                        
                <?php if( $terms || $privacy || ( $scp->show_optin_cb ) ): ?>
                    </div>
                <?php endif; ?>
    
                <?php ncs_helper()->renderTemplate('order-form/submit-button', ['scp'=>$scp, 'id'=>'sc_card_button', 'scuid'=>$scuid]); ?>
                             
              </div>
            </div>
        </div>
          <?php /*if(isset($_REQUEST['sc-method-change']) && $_REQUEST['sc-method-change'] !=""){ ?>
                
        <?php }*/ ?>
    <?php
    }
    

    public function post_type_args($opts, $cpt_name) {
        if($cpt_name != 'sc_upgrade_path') return $opts;
        $opts['rewrite']['slug'] = 'change_plan';
        return $opts;
    }

    public function set_custom_edit_columns($columns) {
		$columns['shortcode'] = __( 'Shortcode', 'ncs-cart' );
		return $columns;
	}

    public function custom_column( $column, $post_id ) {
		switch ( $column ) {

			case 'shortcode' :
				echo '<code>['.apply_filters('studiocart_slug', 'studiocart') . '-form'.' id='.$post_id.']</code>';
				break;

		}
	}

    function register_post_type() {
        global $is_studiocart;
        $post_type = array(
            'cap_type' 	=> 'sc_product',
            'plural' 	=> __('Upgrade Paths', 'ncs-cart'),
            'single' 	=> __('Upgrade Path', 'ncs-cart'),
            'cpt_name' 	=> 'sc_upgrade_path',
            'supports'  => array( 'title', 'editor', 'thumbnail'  ),
            'public'    => true
        );
        $post_type = apply_filters('sc_register_post_type_'.$post_type['cpt_name'].'_args', $post_type);
        NCS_Cart_Post_Types::register_single_post_type( $post_type['cap_type'], $post_type['plural'], $post_type['single'], $post_type['cpt_name'], $post_type['supports'], $post_type['public'] );
        
        if (get_post_type() == 'sc_upgrade_path') {
            $is_studiocart = true;
        }
    }
            
    function show_selected_plan($post_id, $hide_labels, $plan=false) {
        global $scp;
        $hide_class = (isset($scp->hide_plans)) ? 'hidden' : '';
        $scp->show_coupon_field = 1;
        if (isset($scp->show_coupon_field)) {
            $hide_class .= ' sc-show-coupon';
        }
        
        do_action('sc_orderform_before_payment_plans', $post_id);

        $plan = sanitize_text_field($_REQUEST['plan']);
        $selected_plan = studiocart_plan($plan, 'current', $scp->ID); // Start Debugging from Here

        // $sub = sanitize_text_field($_REQUEST['sc-s']);
        $sub_id = sanitize_text_field($_REQUEST['sc-s']);
        $sub = get_post_meta($sub_id,'_sc_subscription_id',true);
        $sub = ScrtSubscription::get_by_sub_id($sub);
        
        ?>

        <input checked="checked" type="radio" name="sc_product_option" data-installments="1" 
            value="<?php echo $selected_plan->option_id; ?>"
        />

        <div class="sc-section products <?php echo $hide_class; ?>">

            <h3 class="title"><?php esc_html_e('Your selected plan', 'ncs-cart'); ?></h3>

            <?php do_action('sc_coupon_fields', $post_id); ?>

            <p><strong style="display: block;"><?php echo $selected_plan->name; ?></strong>
                <?php echo $selected_plan->text; ?>    
            </p>

            <?php if($selected_plan->start_text) { echo $selected_plan->start_text; } ?></p>
            
            <?php if ( sc_fs()->is__premium_only() && sc_fs()->can_use_premium_code()) {
                do_action('sc_coupon_status', $post_id); 
            } ?>

        </div>
        <?php
    }
}

class NCS_Cart_Upgrade_Path_Metabox_Fields extends NCS_Cart_Post_Metabox_Fields {
    public function __construct() {
        $this->plugin_name = 'ncs-cart';
		$this->post_type = 'sc_upgrade_path';

        add_action( 'wp', array($this, 'set_global'), 99 );
        add_action( 'add_meta_boxes', array($this, 'set_global'), 99 );

        add_action( 'admin_init', array($this, 'add_metaboxes'), 99);
        add_action( 'save_post_'.$this->post_type, array($this, 'validate_meta'), 10, 2 );

		$this->set_meta();

	}

    public function set_global() {
        global $is_studiocart;
        if (get_post_type() == 'sc_upgrade_path' && !$is_studiocart) {
            $is_studiocart = true;
        }
    }
    
    public function set_field_groups($save = false) {  

        $this->fields = array();

        $this->fields['general'] = array(
            'label' => __('General','ncs-cart'),
            array(
                'class'		    => '',
                'description'	=> '',
                'id'			=> '_sc_edit_upgrade_path',
                'label'	    	=> '',
                'placeholder'	=> '',
                'type'		    => 'hidden',
                'value'		    => 1,
                'class_size'    => 'hide'
            ),
            array(
                'class'		=> '',
                'description'	=> '',
                'id'			=> '_sc_switch_enabled',
                'label'		=> __('Enabled','ncs-cart'),
                'placeholder'	=> '',
                'type'		=> 'checkbox',
                'value'		=> '',
                'class_size'		=> '',
            ),

            array(
                'class'		=> 'widefat',
                'id'		=> '_sc_modal_title',
                'label'	    	=> __('Modal title','ncs-cart'),
                'type'		=> 'text',
                'value'		=> '',
                'class_size'=> '',
                'placeholder' => esc_html__('Change Your Plan','ncs-cart'),
            ),

            array(
                'class'		    => '',
                'description'	=> '',
                'id'			=> '_sc_switch_upgrade',
                'label'	    	=> __('Upgrade plans','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'selections'    => array(
                    ''                         => __('at the end of the current billing period','ncs-cart'),
                    'immediately'              => __('immediately','ncs-cart'),
                ),
                'class_size'=> ''
            ),
            array(
                'class'		    => '',
                'description'	=> '',
                'id'			=> '_sc_switch_downgrade',
                'label'	    	=> __('Downgrade plans','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'selections'    => array(
                    'immediately'   => __('immediately','ncs-cart'),
                    ''              => __('at the end of the current billing period','ncs-cart'),
                ),
                'class_size'=> ''
            ),
            array(
                'class'		=> 'widefat',
                'id'		=> '_sc_products',
                'type'		=> 'html',
                'value'		=> '<div id="rid_sc_additional" class="sc-field sc-row"><div class="input-group field-text"><div style="width: 100%;"><h4 style="margin-bottom: 0;padding-bottom: 7px;border-bottom: 1px solid #d5d5d5;font-weight: normal;"><b>'.__('Plan Options', 'ncs-cart').'</b></h4></div></div></div>',
                'class_size'=> '',
                'conditional_logic' => '',
            ),
            array(
                'class'		    => 'sc-selectize multiple',
                'description'	=> '',
                'id'			=> '_sc_upgrade_filter',
                'label'	    	=> __('Options filter','ncs-cart'),
                'placeholder'	=> __('Show all plans','ncs-cart'),
                'type'		    => 'select',
                'value'		    => '',
                'selections'    => array(
                    'current' => __('Current plan','ncs-cart'),
                    'same' => __('Equal cost plans','ncs-cart'),
                    'upgrade' => __('Upgrades','ncs-cart'),
                    'downgrade' => __('Downgrades','ncs-cart'),
                ),
                'class_size'=> '',
            ),
            array(
                'class'         => 'repeater',
                'id'            => '_sc_pay_options',
                'label-add'		=> __('+ Add Product','ncs-cart'),
                'label-edit'    => __('Edit Product','ncs-cart'),
                'label-header'  => __('Product','ncs-cart'),
                'label-remove'  => __('Remove Product','ncs-cart'),
                'title-field'	=> 'name',
                'type'		    => 'repeater',
                'value'         => '',
                'class_size'    => '',
                'fields'        => $this->repeater_fields(),
            )
        );
    }

    private function repeater_fields($save = false) {
        
        return array(
            array('select'=>array(
                'class'		    => 'update-plan-product required',
                'description'	=> '',
                'id'			=> 'prod_product',
                'label'	    	=> __('Select Product','ncs-cart'),
                'placeholder'	=> '',
                'type'		    => 'select',
                'value'		    => '',
                'selections'    => $this->product_options(),
                'class_size'=> '',
            )),            
            array('select'=>array(
                'class'		    => 'widefat update-plan ob-{val}',
                'description'	=> '',
                'id'			=> 'prod_plan',
                'label'		    => __('Price Option','ncs-cart'),
                'placeholder'	=> '',
                'value'		    => '',
                'selections'    => $this->get_plans('_sc_pay_options', 'prod_product'),
                'class_size'    => '',
                'step'          => 'any',
                'type'          => 'select',
            )),
            array('text'=>array(
                'class'		=> 'widefat',
                'description'	=> __('Overrides the default description of this price option','ncs-cart'),
                'id'			=> 'option_name',
                'label'		=> __('Price Label','ncs-cart'),
                'placeholder'	=> __('Use default','ncs-cart'),
                'type'		=> 'text',
                'value'		=> '',
                'class_size'    => '',
            )),
        );
    }
}

$sc_up_mb_fields = new NCS_Cart_Upgrade_Path_Metabox_Fields();
$sc_upsell_paths = new NCS_Cart_Upgrade_Path();