<?php
class Studiocart_Dashboard_Widget {
    
    public function __construct() {
        add_action('wp_dashboard_setup', array($this, 'register'));
    }
    
    // Function to register the Studiocart dashboard widget
    public function register() {
        wp_add_dashboard_widget(
            'studiocart_dashboard_widget',
            sprintf(__('Monthly Overview for %s', 'ncs-cart'), apply_filters('studiocart_plugin_title', 'Studiocart')),
            array($this, 'render')
        );
    }

    // Function to render the content of the Studiocart dashboard widget
    public function render() {
        global $wpdb;

        $days = 30;

        // Calculate total orders
        $total_orders_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = %s AND post_date >= %s",
            'sc_order',
            date('Y-m-01'),
            //date('Y-m-d', strtotime('-'.$days.' days'))
        );
        $total_orders = $wpdb->get_var($total_orders_query);

        // Calculate total sales
        $total_sales_query = $wpdb->prepare(
            "SELECT SUM(meta_value) FROM $wpdb->postmeta WHERE meta_key = %s AND post_id IN (SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_date >= %s)",
            '_sc_amount',
            'sc_order',
            date('Y-m-01'),
            //date('Y-m-d', strtotime('-'.$days.' days'))
        );
        $total_sales = $wpdb->get_var($total_sales_query);

        // Calculate average order value
        if ($total_orders > 0) {
            $average_order_value = $total_sales / $total_orders;
        } else {
            $average_order_value = 0;
        }

        $pending_orders = $this->get_orders('pending-payment');
        $paid_orders = $this->get_orders('paid');
        $refunded_orders = $this->get_orders('refunded');
        
        ?>
        <div class="studiocart-dashboard-widget">
            <h3><?php _e('Orders this month', 'ncs-cart'); ?> <a href="<?php echo esc_url(admin_url('edit.php?post_type=sc_order&order_type=orders')); ?>"><?php _e('View all orders', 'ncs-cart'); ?></a></h3>
            <ul id="sc-order-stats">
                <li><?php _e('Total Orders', 'ncs-cart'); ?> <b><?php echo $total_orders; ?></b></li>
                <li><?php _e('Total Sales', 'ncs-cart'); ?> <b><?php sc_formatted_price($total_sales); ?></b></li>
                <li><?php _e('Avg. Order Value', 'ncs-cart'); ?> <b><?php sc_formatted_price($average_order_value); ?></b></li>
                <li><?php _e('Pending Orders', 'ncs-cart'); ?> <b><?php echo $pending_orders; ?></b></li>
                <li><?php _e('Paid Orders', 'ncs-cart'); ?> <b><?php echo $paid_orders; ?></b></li>
                <li><?php _e('Refunded Orders', 'ncs-cart'); ?> <b><?php echo $refunded_orders; ?></b></li>
            </ul>

            <h3><?php _e('Subscriptions this month', 'ncs-cart'); ?> <a href="<?php echo esc_url(admin_url('edit.php?post_type=sc_subscription&subscription_type=ongoing')); ?>"><?php _e('View all subscriptions', 'ncs-cart'); ?></a></h3>
            <?php
            // Get subscription-related data
            $subscription_data = $this->get_subscription_data();

            // Output subscription section
            echo '<ul id="sc-order-stats">';
            echo '<li>' . __('Active Subscriptions', 'ncs-cart') . ' <b>' . $subscription_data['active_subscriptions'] . '</b></li>';
            echo '<li>' . __('New Sign-ups', 'ncs-cart') . ' <b>' . $subscription_data['new_sign_ups'] . '</b></li>';
            echo '<li>' . __('Trials', 'ncs-cart') . ' <b>' . $subscription_data['trials'] . '</b></li>';
            echo '<li>' . __('Renewals', 'ncs-cart') . ' <b>' . $subscription_data['renewals'] . '</b></li>';
            echo '<li>' . __('MRR', 'ncs-cart') . ' <b>' . $subscription_data['mrr'] . '</b></li>'; // Assuming MRR is in dollars
            echo '</ul>';
            ?>

            <h3><?php _e('Payment plans this month', 'ncs-cart'); ?> <a href="<?php echo esc_url(admin_url('edit.php?post_type=sc_subscription&subscription_type=installments')); ?>"><?php _e('View all plans', 'ncs-cart'); ?></a></h3>
            <?php $plan_data = $this->get_plan_data(); ?>
            <ul id="sc-order-stats">
                <li><?php _e('Active Plans', 'ncs-cart'); ?> <b><?php echo $plan_data['active_plans']; ?></b></li>
                <li><?php _e('Collected Revenue', 'ncs-cart'); ?>  <b><?php echo $plan_data['collected']; ?></b></li>
                <li><?php _e('Revenue Expected', 'ncs-cart'); ?> <b><?php echo $plan_data['expected']; ?></b> <small>(<?php _e('all active plans', 'ncs-cart'); ?>)</small></li>
                <li><?php _e('Cancelled Plans', 'ncs-cart'); ?> <b><?php echo $plan_data['cancellations']; ?></b></li>
            </ul>

            <h3><?php _e('Quick Links', 'ncs-cart'); ?></h3>
            <ul>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=ncs-cart-reports')); ?>"><?php _e('View Reports', 'ncs-cart'); ?></a></li>
                <li><a href="<?php echo esc_url(admin_url('edit.php?post_type=sc_product')); ?>"><?php _e('Manage Products', 'ncs-cart'); ?></a></li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=ncs-cart-contacts-page')); ?>"><?php _e('View Contacts', 'ncs-cart'); ?></a></li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=sc-docs')); ?>"><?php _e('Resources', 'ncs-cart'); ?></a></li>
                <li><a href="<?php echo esc_url(admin_url('admin.php?page=sc-admin')); ?>"><?php _e('Settings', 'ncs-cart'); ?></a></li>
            </ul>

            <h3><?php _e('Support & Documentation', 'ncs-cart'); ?></h3>
            <p><?php printf(__('Visit our %sdocumentation%s for help.', 'ncs-cart'), '<a href="#">', '</a>'); ?></p>
        </div>
        <?php
    }

    private function get_orders($status) {

        global $wpdb;

        // Calculate pending orders
        $pending_orders_query = $wpdb->prepare(
            "SELECT COUNT(*) FROM $wpdb->postmeta 
            WHERE meta_key = %s 
            AND meta_value = %s 
            AND post_id IN (
                SELECT ID FROM $wpdb->posts 
                WHERE post_type = %s 
                AND post_date >= %s 
                AND ID NOT IN (
                    SELECT post_id FROM $wpdb->postmeta 
                    WHERE meta_key = %s
                )
            )",
            '_sc_status',
            $status,
            'sc_order',
            date('Y-m-01'),
            '_sc_renewal'
        );
        return $wpdb->get_var($pending_orders_query);
    }

    private function get_subscription_data() {
        global $wpdb;
    
        // Initialize subscription data
        $subscription_data = array(
            'active_subscriptions' => 0,
            'new_sign_ups' => 0,
            'trials' => 0,
            'cancellations' => 0,
            'renewals' => 0,
            'mrr' => 0
        );
    
        // Query to get active subscriptions
        $subscriptions_query = new WP_Query(array(
            'post_type' => 'sc_subscription',
            'post_status' => 'active',
            'meta_query' => array(
                array(
                    'key' => '_sc_sub_installments',
                    'value' => -1,
                    'compare' => '=',
                )
            ),
            'posts_per_page' => -1 // Retrieve all active subscriptions
        ));
    
        // Update active subscriptions count
        $subscription_data['active_subscriptions'] = $subscriptions_query->found_posts;
    
        // Update new sign-ups count
        $subscription_data['new_sign_ups'] = $this->get_subscription_count('active');
        $subscription_data['trials'] = $this->get_subscription_count('trialing');
        $subscription_data['cancellations'] = $this->get_subscription_count('canceled');
        $subscription_data['renewals'] = $this->get_renewal_count();
        $subscription_data['mrr'] = $this->get_mrr($subscriptions_query);                   
            
        // Return subscription data
        return $subscription_data;
    }

    private function get_plan_data() {
        global $wpdb;
    
        // Initialize plan data
        $subscription_data = array(
            'active_plans' => 0,
            'collected' => 0,
            'expected' => 0,
            'cancellations' => 0
        );
    
        // Query to get active plans
        $subscriptions_query = new WP_Query(array(
            'post_type' => 'sc_subscription',
            'post_status' => 'active',
            'meta_query' => array(
                array(
                    'key' => '_sc_sub_installments',
                    'value' => 1,
                    'compare' => '>=',
                )
            ),
            'posts_per_page' => -1 // Retrieve all active subscriptions
        ));
    
        // Update active subscriptions count
        $subscription_data['active_plans'] = $subscriptions_query->found_posts;
        $collected = $this->get_collected_amount();
    
        // Update new sign-ups count
        $subscription_data['cancellations'] = $this->get_subscription_count('canceled', 'plan');
        $subscription_data['collected'] = sc_format_price($collected);
        $subscription_data['expected'] = $this->get_expected_amount($subscriptions_query, $collected);                   
            
        // Return subscription data
        return $subscription_data;
    }

    private function get_expected_amount($installment_plans, $collected) {
         // Initialize total revenue
        $total_revenue = 0;

        // Loop through each installment plan
        if ($installment_plans->have_posts()) {
            while ($installment_plans->have_posts()) {
                $installment_plans->the_post();

                // Get the total number of installments and installment amount
                $total_installments = intval(get_post_meta(get_the_ID(), '_sc_sub_installments', true));
                $installment_amount = intval(get_post_meta(get_the_ID(), '_sc_sub_amount', true));

                // Calculate the expected revenue from this installment plan
                $expected = $total_installments * $installment_amount;
                $total_revenue += $expected;
            }
        }

        // Reset post data
        wp_reset_postdata();

        // Return the total expected revenue
        return sc_format_price($total_revenue - $collected);
    }

    private function get_collected_amount() {
        global $wpdb;
    
        // Prepare the query to sum up the order amounts
        $query = $wpdb->prepare(
            "SELECT SUM(order_amount.meta_value)
            FROM {$wpdb->posts} AS orders
            JOIN {$wpdb->postmeta} AS subscription_id ON orders.ID = subscription_id.post_id
            JOIN {$wpdb->postmeta} AS order_amount ON orders.ID = order_amount.post_id
            WHERE subscription_id.meta_key = '_sc_subscription_id' 
            AND orders.post_type = 'sc_order'
            AND order_amount.meta_key = '_sc_amount'
            AND orders.post_status = %s
            AND orders.post_date >= %s
            AND subscription_id.meta_value IN (
                SELECT posts.ID
                FROM {$wpdb->posts} AS posts
                JOIN {$wpdb->postmeta} AS sub_installments ON posts.ID = sub_installments.post_id
                AND sub_installments.meta_key = '_sc_sub_installments'
                AND sub_installments.meta_value != '-1'
                AND posts.post_status = 'active'
            )",
            'paid',
            date('Y-m-01')
        );
    
        // Execute the query and fetch the total amount collected
        $total_amount = $wpdb->get_var($query);
    
        // Return the total amount collected
        return $total_amount ? $total_amount : 0;
    }
    

    private function get_subscription_count($status = 'active', $plan = false) {
    
        // Calculate the first day of the current month
        $first_day_of_month = date('Y-m-01');

        if($plan) {
            $value = '1';
            $compare = '>=';
        } else {
            $value = '-1';
            $compare = '=';
        }

        // Query to get new sign-ups (subscriptions created in the current month)
        $new_sign_ups_query = new WP_Query(array(
            'post_type' => 'sc_subscription',
            'post_status' => $status,
            'date_query' => array(
                'after' => $first_day_of_month,
            ),
            'posts_per_page' => -1, // Retrieve all subscriptions created in the current month
            'meta_query' => array(
                array(
                    'key' => '_sc_sub_installments',
                    'value' => $value,
                    'compare' => $compare,
                )
            ),
        ));
    
        // Update new sign-ups count
        return $new_sign_ups_query->found_posts;
    }

    private function get_renewal_count() {
        $args = array(
            'post_type'      => 'sc_order',
            'posts_per_page' => -1,
            'meta_query'     => array(
                array(
                    'key'   => '_sc_renewal_order',
                    'compare'      => 'EXISTS',
                ),
            ),
        );
    
        $query = new WP_Query($args);
    
        // Get the total number of renewals
        $renewal_count = $query->found_posts;
    
        return $renewal_count;
        
    }

    private function get_mrr($subscriptions_query) {
        // Calculate MRR (Monthly Recurring Revenue) for each interval type
        $total_mrr = 0;
        if ($subscriptions_query->have_posts()) {
            while ($subscriptions_query->have_posts()) {
                $subscriptions_query->the_post();
                
                // Get subscription details
                $subscription_id = get_the_ID();
                $subscription_amount = floatval(get_post_meta($subscription_id, '_sc_sub_amount', true));
                $payment_interval = get_post_meta($subscription_id, '_sc_sub_interval', true);
                $payment_frequency = floatval(get_post_meta($subscription_id, '_sc_frequency', true));
                $payment_frequency = ($payment_frequency) ? $payment_frequency : 1;
                
                // Calculate monthly revenue based on payment interval and frequency
                $monthly_revenue = 0;
                if(is_numeric($subscription_amount) && is_numeric($payment_frequency)){
                    switch ($payment_interval) {
                        case 'day':
                            $monthly_revenue = $subscription_amount * (30 / $payment_frequency);
                            break;
                        case 'week':
                            $monthly_revenue = $subscription_amount * (4.34524 / $payment_frequency); // Approximate number of weeks in a month
                            break;
                        case 'month':
                            $monthly_revenue = $subscription_amount * $payment_frequency;
                            break;
                        case 'year':
                            $monthly_revenue = $subscription_amount / 12 * $payment_frequency;
                            break;
                    }
                }
                
                // Add the monthly revenue to the total MRR
                $total_mrr += $monthly_revenue;
            }
        }
        return sc_format_price($total_mrr);
    }
}

// Instantiate the class to register the dashboard widget
new Studiocart_Dashboard_Widget();
?>