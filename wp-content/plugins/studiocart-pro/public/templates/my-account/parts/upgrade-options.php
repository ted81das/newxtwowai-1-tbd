<?php
global $upgrade_path_id, $upgrade_options, $button_color;
?>

<?php if (!empty($upgrade_options)): ?>

    <div class="sc-modal" id="switch-sub" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-header">
                <?php $title = ($title = get_post_meta($upgrade_path_id, '_sc_modal_title', true)) ? $title : esc_html__('Change Your Plan','ncs-cart'); ?>
                <h2><?php echo $title; ?></h2>
                <a href="#" class="btn-close closemodal" aria-hidden="true">&times;</a>
            </div>
            
            <div class="modal-body">
                <div class="success-msg" id="successMsg"></div>

                  <?php if($button_color): ?>
                     <style>
                        .upgrade-plan-container button {
                           background: <?php echo $button_color; ?>;
                        }
                     </style>
                  <?php endif; ?>
                  <section class="update-plan-section">
                     <?php if ($content = get_post_field('post_content', $upgrade_path_id)) {
                        echo wpautop($content);
                     } ?>
                     <div class="upgrade-plan-container">
                        <?php foreach ($upgrade_options as $upgrade_plan): 
                           $class = ($upgrade_plan['is_current_plan']) ? 'current' : '' ?>
                           <div class="plan-box <?php echo $class; ?>">
                              <div class="part">

                                 <div class="plan-info">
                                    <div class="plan-name">
                                       <?php echo $upgrade_plan['option_name'] ?>
                                    </div>
                                    <div class="plan-price">
                                       <?php
                                       if(isset($upgrade_plan['payment_plan']['sale_price']) && sc_is_prod_on_sale($upgrade_plan['args']['prod_id'])){

                                          echo '<span class="sc-price">'.sc_format_price($upgrade_plan['payment_plan']['sale_price'], true) . "</span>/";
                                          if ($upgrade_plan['payment_plan']['sale_frequency'] > 1) {
                                             echo $upgrade_plan['payment_plan']['sale_frequency']." ";
                                          }
                                          echo $upgrade_plan['payment_plan']['sale_interval'];

                                          if ($upgrade_plan['payment_plan']['sale_installments'] > 0) {
                                             echo " x ".$upgrade_plan['payment_plan']['sale_installments']." ";
                                          }

                                       }else{

                                          echo '<span class="sc-price">'.sc_format_price($upgrade_plan['payment_plan']['price'], true) . "</span>/";
                                          if ($upgrade_plan['payment_plan']['frequency'] > 1) {
                                             echo $upgrade_plan['payment_plan']['frequency']." ";
                                          }
                                          echo $upgrade_plan['payment_plan']['interval'];

                                          if ($upgrade_plan['payment_plan']['installments'] > 0) {
                                             echo " x ".$upgrade_plan['payment_plan']['installments']." ";
                                          }
                                       }  
                                       ?>
                                    </div>
                                 </div>

                              </div>
                              <div class="part btn">
                                 <?php if ($upgrade_plan['is_current_plan']): ?>
                                    <button class="current">
                                       <?php echo $upgrade_plan['button_text'] ?>
                                    </button>
                                 <?php else: ?>
                                    <form action="<?php echo $upgrade_plan['upgrade_url']; ?>" method='POST'>
                                       <?php foreach($upgrade_plan['args'] as $k=>$v): ?>
                                          <input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" />
                                       <?php endforeach; ?>
                                       <?php wp_nonce_field($upgrade_plan['args']['type'].'-'.$upgrade_plan['args']['sc-s']); ?>
                                       <button type="submit"><?php echo $upgrade_plan['button_text'] ?> &nbsp; ➜</button>
                                    </form>
                                 <?php endif; ?>
                              </div>
                           </div>
                        <?php endforeach; ?>
                     </div>
                  </section>
               <?php endif; ?>

                <div class="sc_preloader" id="sc-preloader">
                    <svg width="48" height="48" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg" stroke="#333333">
                        <g fill="none" fill-rule="evenodd">
                            <g transform="translate(1 1)" stroke-width="2">
                                <circle stroke-opacity=".5" cx="18" cy="18" r="18"/>
                                <path d="M36 18c0-9.94-8.06-18-18-18">
                                    <animateTransform attributeName="transform" type="rotate" from="0 18 18" to="360 18 18" dur="1s" repeatCount="indefinite"/>
                                </path>
                            </g>
                        </g>
                    </svg>
                </div>
            </div>
        </div>
    </div> 
   

   