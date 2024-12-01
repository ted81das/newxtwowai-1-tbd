<?php

namespace Studiocart;

if (!defined('ABSPATH'))
	exit;

class WPDomainChecker {
    
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $service_name,$plugin_name,$plugin_title;

	public function __construct() {
        $this->service_name = 'WPDomainChecker';
		$this->plugin_name = 'ncs-cart';
		$this->plugin_title = 'Studiocart';
		if ( defined( 'NCS_CART_VERSION' ) ) {
			$this->version = NCS_CART_VERSION;
		} else {
			$this->version = '1.0';
		}
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    public function init() {
        if ( ! class_exists( 'wdcWhoisFunc' ) ) {
            return;
        }
        add_shortcode('sc_wpdomainchecker',array($this, 'sc_wpdomainchecker_shortcode'));

        add_filter('studiocart_order_form_fields',array($this,'sc_wpdomainchecker_fields'),10,2);
        add_action('sc_card_details_fields', array($this,'sc_wpdomainchecker_options'), 2);
        add_filter('sc_customer_defaults',array($this,'sc_customer_defaults_wpdomainchecker'),10,1);
        add_action('sc_after_load_from_post', array($this,'sc_after_load_from_post_wpdomainchecker'),10,2);
        add_action('sc_order_details', array($this,'sc_extra_info_wpdomainchecker'),10);
        add_action('sc_sub_details', array($this,'sc_extra_info_wpdomainchecker'),10);
        
    }

    function sc_wpdomainchecker_shortcode($atts){
        $url = get_permalink($atts['sc_item_id']);
        $url = $url."?sc_d={domain}";
        $shortcode_atts = "";
        foreach($atts as $att_key => $att):
            $shortcode_atts = $att_key.'="'.$att.'" ';
        endforeach;
        echo do_shortcode('[wpdomainchecker integration="custom" '.$shortcode_atts.' url="'.$url.'"]');
    }

    function sc_wpdomainchecker_fields($fields, $scp){
        if(isset($_GET['sc_d'])){
            $fields['domain_purchased'] = array('name'=>'domain_purchased','label'=>esc_html__('Domain Purchased', 'ncs-cart'),'type'=>'hidden', 'hide_labels'=>true,'value'=>$_GET['sc_d']);
        }
        return $fields;
    }

    function sc_wpdomainchecker_options(){
        if(isset($_GET['sc_d'])){?>
            <div class="sc-section dmona_checked">
                <h3 class="title"><?php esc_html_e("Selected Domain", "ncs-cart"); ?></h3>
                <div class="item">
                    <?php echo $_GET['sc_d']; ?>
                </div>
            </div>
    <?php }
    }

    function sc_customer_defaults_wpdomainchecker($customer_defaults){
        $customer_defaults['domain_checker'] = null;
        return $customer_defaults;
    }

    function sc_after_load_from_post_wpdomainchecker($order,$post){
        if(isset($post['domain_purchased'])){
            $order->domain_checker = $post['domain_purchased'];
        }
    }

    function sc_extra_info_wpdomainchecker($order){
        if ($order->domain_checker): ?>
            <p>
                <strong><?php esc_html_e( 'Domain Purchased:', 'ncs-cart' ); ?></strong><br>
                <?php echo $order->domain_checker; ?>
            </p>
        <?php endif;
    }
}