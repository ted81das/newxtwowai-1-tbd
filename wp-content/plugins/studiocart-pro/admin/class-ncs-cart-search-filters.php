<?php
class NCS_Cart_Admin_Filters {
    private $meta_filters;

    public function __construct($meta_filters) {     

        add_action('restrict_manage_posts', array($this, 'subscriptions_type_filter'));
        add_action('pre_get_posts', array($this, 'apply_subscription_type_filter'));
        
        add_action('restrict_manage_posts', array($this, 'order_type_filter'));
        add_action('pre_get_posts', array($this, 'apply_order_type_filter'));

        $this->meta_filters = $meta_filters;
        add_action('restrict_manage_posts', array($this, 'custom_posts_filter'));
        add_action('pre_get_posts', array($this, 'apply_custom_posts_filter_with_meta'));
        add_filter('posts_search', array($this, 'custom_posts_search'), 10, 2); // Add filter for custom search
    }

    // Display custom posts filter dropdown
    public function subscriptions_type_filter() {
        global $typenow, $wpdb;
        // Check if we're on the subscriptions page
        if ($typenow == 'sc_subscription') {
            $selected = isset($_GET['subscription_type']) ? $_GET['subscription_type'] : '';

            // Output the dropdown
            ?>
            <select name="subscription_type" id="subscription_type">
                <option value="">All types</option>
                <option value="ongoing" <?php selected($selected, 'ongoing'); ?>>Ongoing Subscriptions</option>
                <option value="installments" <?php selected($selected, 'installments'); ?>>Payment Plans</option>
                <option value="pending_cancellation" <?php selected($selected, 'pending_cancellation'); ?>>Pending Cancellation</option>
            </select>
            <?php
        }
    }

    // Apply sub filter when the dropdown value is set
    public function apply_subscription_type_filter($query) {
        global $pagenow, $typenow;

        // Check if we're on the subscriptions page and the filter value is set
        if ($pagenow == 'edit.php' && $typenow == 'sc_subscription' && isset($_GET['subscription_type']) && $_GET['subscription_type'] != '') {
            $subscription_type = $_GET['subscription_type'];

            // Add meta query based on the selected subscription type
            if ($subscription_type == 'ongoing') {
                $query->set('meta_key', '_sc_sub_installments');
                $query->set('meta_value', '-1');
            } else if ($subscription_type == 'installments') {
                $query->set('meta_key', '_sc_sub_installments');
                $query->set('meta_value', 1);
                $query->set('meta_compare', '>');
            } else if ($subscription_type == 'pending_cancellation') {
                $query->set('meta_query', array(
                    array(
                        'key' => '_sc_cancel_date',
                        'compare' => 'EXISTS',
                    ),
                    array(
                        'key' => '_sc_status',
                        'value' => 'canceled',
                        'compare' => '!=',
                    ),
                ));
            }
        }
    }

    // Display custom posts filter dropdown
    public function order_type_filter() {
        global $typenow, $wpdb;
        // Check if we're on the subscriptions page
        if ($typenow == 'sc_order') {
            $selected = isset($_GET['order_type']) ? $_GET['order_type'] : '';

            // Output the dropdown
            ?>
            <select name="order_type" id="order_type">
                <option value="">All types</option>
                <option value="orders" <?php selected($selected, 'orders'); ?>>Initial orders</option>
                <option value="renewals" <?php selected($selected, 'renewals'); ?>>Renewals</option>
            </select>
            <?php
        }
    }

    // Apply sub filter when the dropdown value is set
    public function apply_order_type_filter($query) {
        global $pagenow, $typenow;

        // Check if we're on the subscriptions page and the filter value is set
        if ($pagenow == 'edit.php' && $typenow == 'sc_order' && isset($_GET['order_type']) && $_GET['order_type'] != '') {
            $order_type = $_GET['order_type'];

            // Add meta query based on the selected subscription type
            if ($order_type == 'renewals') {
                $query->set('meta_key', '_sc_renewal_order');
                $query->set('meta_value', '1');
            } else {
                $query->set('meta_query', array(
                    array(
                        'key' => '_sc_renewal_order',
                        'compare' => 'NOT EXISTS',
                    ),
                ));
            }
        }
    }

    // Display custom posts filter dropdown
    public function custom_posts_filter() {
        global $typenow, $wpdb;
        foreach ($this->meta_filters as $meta_filter) {
            if (in_array($typenow, $meta_filter['post_types'])) {

                $meta_key = $meta_filter['key'];
                $default_option = isset($meta_filter['default_option']) ? $meta_filter['default_option'] : 'All ' . $meta_key;
                
                $meta_values = $wpdb->get_col($wpdb->prepare(
                    "SELECT DISTINCT meta_value FROM $wpdb->postmeta WHERE meta_key = %s",
                    $meta_key
                ));

                if ($meta_values) {
                    echo '<select name="' . esc_attr($meta_key) . '">';
                    echo '<option value="">' . esc_html($default_option) . '</option>'; // Default option
                    foreach ($meta_values as $meta_value) {
                        echo '<option value="' . esc_attr($meta_value) . '"';
                        if (isset($_GET[$meta_key]) && $_GET[$meta_key] == $meta_value) {
                            echo ' selected="selected"';
                        }
                        if ($meta_key == '_sc_pay_method') {
                            $meta_value = ucwords(str_replace('_', ' ', $meta_value));
                        }
                        echo '>' . esc_html($meta_value) . '</option>';
                    }
                    echo '</select>';
                }
            }
        }
    }

    // Apply custom posts filter with meta
    public function apply_custom_posts_filter_with_meta($query) {
        global $pagenow, $typenow;
        foreach ($this->meta_filters as $meta_filter) {
            if (in_array($typenow, $meta_filter['post_types'])) {
                $meta_key = $meta_filter['key'];

                if (isset($_GET[$meta_key]) && $_GET[$meta_key] != '') {
                    $query->set('meta_key', $meta_key);
                    $query->set('meta_value', $_GET[$meta_key]);
                }
            }
        }
    }

    // Custom posts search
    public function custom_posts_search($search, $wp_query) {
        global $wpdb;

        // Check if it's the admin and if we are searching
        if (is_admin() && $wp_query->is_search && isset($_GET['s'])) {
            $search_term = $_GET['s'];
            $search = '';

            // Add the custom meta field to the search query
            if (!empty($search_term)) {
                $search .= " AND (
                    {$wpdb->posts}.post_title LIKE '%{$search_term}%'
                    OR {$wpdb->posts}.post_content LIKE '%{$search_term}%'
                    OR EXISTS (
                        SELECT * FROM {$wpdb->postmeta}
                        WHERE post_id = {$wpdb->posts}.ID
                        AND (meta_key = '_sc_email' AND meta_value LIKE '%{$search_term}%')
                    )
                ) ";
            }
        }

        return $search;
    }
}

// Usage:
$post_types = array('sc_subscription', 'sc_order', 'sc_collection');
$meta_filters = array(
    array(
        'key' => '_sc_pay_method',
        'default_option' => 'All Payment Methods',
        'post_types' => $post_types
    ),
    array(
        'key' => '_sc_product_name',
        'default_option' => 'All Products',
        'post_types' => $post_types
    )
);

new NCS_Cart_Admin_Filters($meta_filters, $post_types);

