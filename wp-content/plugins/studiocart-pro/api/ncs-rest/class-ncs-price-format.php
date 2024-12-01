<?php

class NCS_Price_Format{

    public $thousand_sep = '';
    public $decimal_sep  = '';
    
    public function __construct(){
        $this->thousand_sep = get_option( '_sc_thousand_separator' );
        $this->decimal_sep = get_option( '_sc_decimal_separator' );
        $this->sc_api_format_price();
    }

    public function sc_api_format_price(){

        if($this->thousand_sep != '' || $this->decimal_sep != ''){

            update_option('sc_price_formatted', time());
            
            $this->update_sc_product_amount();
            $this->update_sc_us_path_amount();
            $this->update_sc_order_amount();
            $this->update_order_items_amount();
            $this->update_order_itemmeta_amount();
            $this->update_sc_subscriptions_amount();

            update_option('sc_price_formatted','yes');
            
        }

        
    }

    public function check_price_format($price){
        $update = false;

        if($price && ($this->thousand_sep != ',' || $this->decimal_sep != '.')){

            if(strpos($price, $this->thousand_sep) !== false){
                $parts = explode($this->thousand_sep, $price);

                if(count($parts) > 1){
                    foreach($parts as $k => $v){
                        if($this->decimal_sep && strpos($v, $this->decimal_sep) !== false){
                            $decparts = explode($this->decimal_sep, $v);
                            $parts[$k] = $decparts[0];
                        }
                    }

                    $groupLengths = array_map('strlen', $parts);
                    if (max($groupLengths) == 3) {
                        $update = true;
                        $price = str_replace($this->thousand_sep, '', $price);
                    }
                }
            }

            if(strpos($price, $this->decimal_sep) !== false){
                $update = true;
                $price = str_replace($this->decimal_sep, '.', $price);
                
            }
        }

        if($update){
            return number_format($price,9,'.','');
        }

        return false;

        /* if(strpos($amount, $thousand_sep) !== false){
            $amount = str_replace($thousand_sep, '', $amount);
            $filtered = filter_var($amount, FILTER_VALIDATE_FLOAT);
            if ($filtered !== false) {
                if (floor($filtered) !== $filtered) {
                    return $amount;
                }
            }
        }
        return false;*/
    }

    public function update_sc_product_amount(){

        global $wpdb;
        $query = "SELECT ID from $wpdb->posts WHERE post_type='sc_product'";
        $result = $wpdb->get_results($query,ARRAY_A);
        $productIds = array_column($result,'ID');

        if(!empty($productIds)){

            foreach($productIds as $product_id){

                // Product payment options
                $update_pay_options = false;
                $payOptions = get_post_meta($product_id,'_sc_pay_options',true);
                if(!empty($payOptions)){

                    foreach($payOptions as $key => $option){
                        
                        //_sc_pay_options[$id]['price']
                        if(isset($option['price']) && $option['price']){
                            $amount = $this->check_price_format($option['price']);
                            if($amount){
                                $payOptions[$key]['price'] = $amount;
                                $update_pay_options = true;
                            }
                        }
        
                        //_sc_pay_options[$id]['sale_price']
                        if(isset($option['sale_price']) && $option['sale_price']){
                            $amount = $this->check_price_format($option['sale_price']);
                            if($amount){
                                $payOptions[$key]['sale_price'] = $amount;
                                $update_pay_options = true;
                            }
                        }
        
                        //_sc_pay_options[$id]['sign_up_fee']
                        if(isset($option['sign_up_fee']) && $option['sign_up_fee']){
                            $amount = $this->check_price_format($option['sign_up_fee']);
                            if($amount){
                                $payOptions[$key]['sign_up_fee'] = $amount;
                                $update_pay_options = true;
                            }
                        }
        
                        //_sc_pay_options[$id]['sale_sign_up_fee']
                        if(isset($option['sale_sign_up_fee']) && $option['sale_sign_up_fee']){
                            $amount = $this->check_price_format($option['sale_sign_up_fee']);
                            if($amount){
                                $payOptions[$key]['sale_sign_up_fee'] = $amount;
                                $update_pay_options = true;
                            }
                        }
                    }
                    
                    if($update_pay_options){
                        update_post_meta($product_id,'_sc_pay_options',$payOptions);
                    }

                }

                //Product Custom fields
                $update_custom_fields = false;
                $customFields = get_post_meta($product_id,'_sc_custom_fields',true);
                if(!empty($customFields)){
                    foreach($customFields as $ckey => $custom_field){
                        if(isset($custom_field['qty_price'])){
                            $amount = $this->check_price_format($custom_field['qty_price']);
                            if($amount){
                                $customFields[$key]['qty_price'] = $amount;
                                $update_custom_fields = true;
                            }
                           
                        }
                    }

                    if($update_custom_fields){
                        update_post_meta($product_id,'_sc_custom_fields',$customFields);
                    }
                }


                // Product Coupons
                $update_coupons = false;
                $coupons = get_post_meta($product_id,'_sc_coupons',true);
                if(!empty($coupons)){
                    foreach($coupons as $ckey => $coupon){
                        if(isset($coupon['amount'])){
                            $amount = $this->check_price_format($coupon['amount']);
                            if($amount){
                                $coupons[$key]['amount'] = $amount;
                                $update_coupons = true;
                            }
                           
                        }

                        if(isset($coupon['amount_recurring'])){
                            $amount = $this->check_price_format($coupon['amount_recurring']);
                            if($amount){
                                $coupons[$key]['amount_recurring'] = $amount;
                                $update_coupons = true;
                            }
                           
                        }
                    }
                    if($update_coupons){
                        update_post_meta($product_id,'_sc_coupons',$coupons);
                    }
                }


                //_sc_ob_price
                $obPrice = get_post_meta($product_id,'_sc_ob_price',true);
                if($obPrice){
                    $amount = $this->check_price_format($obPrice);
                    if($amount){
                        $coupons[$key]['amount'] = $amount;
                        update_post_meta($product_id,'_sc_ob_price',$amount);
                    }
                }

                //_sc_order_bump_options[$id]['ob_price']
                $update_bump_options = false;
                $bumpOptions = get_post_meta($product_id,'_sc_order_bump_options',true);
                if(!empty($bumpOptions)){
                    foreach($bumpOptions as $bkey => $boption){
                        if(isset($boption['ob_price'])){
                            $amount = $this->check_price_format($boption['ob_price']);
                            if($amount){
                                $bumpOptions[$bkey]['ob_price'] = $amount;
                                $update_bump_options = true;
                            }
                        }
                    }

                    if($update_bump_options){
                        update_post_meta($product_id,'_sc_order_bump_options',$bumpOptions);
                    }
                }

            }
        }

    }

    public function update_sc_us_path_amount(){

        global $wpdb;
        $query = "SELECT ID from wp_posts WHERE post_type='sc_us_path'";
        $result = $wpdb->get_results($query,ARRAY_A);
        $usIds = array_column($result,'ID');

        if(!empty($usIds)){

            foreach($usIds as $us_id){
                for($i = 1; $i<=5; $i++){
                    $usPrice = get_post_meta($us_id,'_sc_us_price_'.$i,true);
                    if($usPrice){
                        $amount = $this->check_price_format($usPrice);
                        if($amount){
                            update_post_meta($us_id,'_sc_us_price_'.$i,$amount);
                        }
                    }

                    $dsPrice = get_post_meta($us_id,'_sc_ds_price_'.$i,true);
                    if($dsPrice){
                        $amount = $this->check_price_format($dsPrice);
                        if($amount){
                            update_post_meta($us_id,'_sc_ds_price_'.$i,$amount);
                        }
                    }

                }
                
            }
        }
    }

    public function update_sc_order_amount(){
        global $wpdb;
        $query = "SELECT ID from $wpdb->posts WHERE post_type='sc_order'";
        $result = $wpdb->get_results($query,ARRAY_A);
        $orderIds = array_column($result,'ID');

        foreach($orderIds as $key => $order_id){
            $orderMetakeys = array('_sc_invoice_total','_sc_invoice_subtotal','_sc_amount','_sc_main_offer_amt','_sc_pre_tax_amount','_sc_tax_amount','_sc_shipping_amount','_sc_shipping_tax');

            foreach($orderMetakeys as $meta_key){
                $meta_value = get_post_meta($order_id, $meta_key, true);
                if($meta_value){
                    $amount = $this->check_price_format($meta_value);
                    if($amount){
                        update_post_meta($order_id, $meta_key, $amount);
                    }
                }
            }
           
            $custom_prices = get_post_meta($order_id,'_sc_custom_prices',true);
            if(!empty($custom_prices)){
                foreach($custom_prices as $name => $price){
                    if($price){
                        $amount = $this->check_price_format($price);
                        if($amount){
                            $custom_prices[$name] =  $amount;
                        }
                    }
                }
                update_post_meta($order_id, '_sc_custom_prices', $custom_prices);
            }

            $order_bumps = get_post_meta($order_id,'_sc_order_bumps',true);
            if(!empty($order_bumps)){
                foreach($order_bumps as $obKey => $obValue){
                    if(isset($obValue['amount'])){
                        $amount = $this->check_price_format($obValue['amount']);
                        if($amount){
                            $order_bumps[$obKey]['amount'] =  $amount;
                        }
                    }
                }
                update_post_meta($order_id, '_sc_order_bumps', $order_bumps);
            }
        }
    }

    public function update_order_items_amount(){
        global $wpdb;
        $query = "SELECT order_item_id,total_amount,tax_amount from ".$wpdb->prefix.'ncs_order_items';
        $result = $wpdb->get_results($query,ARRAY_A);
        if(!empty($result)){
            foreach($result as $item_amount){
                $amount = $this->check_price_format($item_amount['total_amount']);
                if($amount && $amount != $item_amount['total_amount']){
                    $updated = $wpdb->update($wpdb->prefix . 'ncs_order_items', ['total_amount' => $amount], ['order_item_id' => $item_amount['order_item_id']]);
                }

                $amount = $this->check_price_format($item_amount['tax_amount']);
                if($amount && $amount != $item_amount['tax_amount']){
                    $updated = $wpdb->update($wpdb->prefix . 'ncs_order_items', ['tax_amount' => $amount], ['order_item_id' => $item_amount['order_item_id']]);
                }
            }
        }
    }

    public function update_order_itemmeta_amount(){
        global $wpdb;
        $query = "SELECT meta_id,meta_value from ".$wpdb->prefix.'ncs_order_itemmeta'." WHERE meta_key IN ('unit_price','subtotal','discount_amount','shipping_amount','sign_up_fee')";
        $result = $wpdb->get_results($query,ARRAY_A);
        if(!empty($result)){
            foreach($result as $item_meta){
                $amount = $this->check_price_format($item_meta['meta_value']);
                if($amount && $amount != $item_meta['meta_value']){
                    $updated = $wpdb->update($wpdb->prefix . 'ncs_order_itemmeta', ['meta_value' => $amount], ['meta_id' => $item_meta['meta_id']]);
                }
            }
        }
    }

    public function update_sc_subscriptions_amount(){
        global $wpdb;
        $query = "SELECT ID from $wpdb->posts WHERE post_type='sc_subscription'";
        $result = $wpdb->get_results($query,ARRAY_A);
        $subscriptionIds = array_column($result,'ID');

        foreach($subscriptionIds as $key => $subs_id){
            $subsMetakeys = array('_sc_sign_up_fee','_sc_tax_amount','_sc_sub_amount','_sc_sub_discount','_sc_main_offer','_sc_main_offer_amt');
            foreach($subsMetakeys as $meta_key){
                $meta_value = get_post_meta($subs_id, $meta_key, true);
                if($meta_value){
                    $amount = $this->check_price_format($meta_value);
                    if($amount && $amount!= $meta_value){
                        update_post_meta($subs_id, $meta_key, $amount);
                    }
                }
            }
        }
    }


}

?>