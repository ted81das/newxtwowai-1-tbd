<?php 

/**
 * The Template for displaying an order bump on an order form
 * This template can be overridden by copying it to yourtheme/studiocart/order-form/submit-button.php.
 */
    
$scp = $args['scp'];
do_action('sc_before_buy_button', $scp); ?>            
    
<button id="<?php echo $args['id']; ?>" data-form-wrapper="sc-payment-form-<?php echo $scp->ID; ?>-<?php echo $args['scuid']; ?>" type="button" class="btn btn-primary btn-block">
    <?php if(!isset($scp->button_subtext)): ?>
    <svg class="spinner" width="24" height="24" viewBox="0 0 24 24">
        <g fill="none" fill-rule="nonzero">
            <path class="ring_thumb" fill="#FCECEA" d="M17.945 3.958A9.955 9.955 0 0 0 12 2c-2.19 0-4.217.705-5.865 1.9L5.131 2.16A11.945 11.945 0 0 1 12 0c2.59 0 4.99.82 6.95 2.217l-1.005 1.741z"></path>
            <path class="ring_track" fill="#FCECEA" d="M5.13 2.16L6.136 3.9A9.987 9.987 0 0 0 2 12c0 5.523 4.477 10 10 10s10-4.477 10-10a9.986 9.986 0 0 0-4.055-8.042l1.006-1.741A11.985 11.985 0 0 1 24 12c0 6.627-5.373 12-12 12S0 18.627 0 12c0-4.073 2.029-7.671 5.13-9.84z" style="opacity: 0.35"></path>
        </g>
    </svg>
    <?php endif; ?>
    <span class="text">
        <?php if($scp->button_icon && $scp->button_icon_pos != 'right'): ?>
            <?php echo wp_specialchars_decode($scp->button_icon, 'ENT_QUOTES'); ?>
        <?php endif; ?>
        
        <?php echo esc_html($scp->button_text); ?>
        
        <?php if($scp->button_icon && $scp->button_icon_pos == 'right'): ?>
            <?php echo wp_specialchars_decode($scp->button_icon, 'ENT_QUOTES' ); ?>
        <?php endif; ?>
    </span>

    <?php if(isset($scp->button_subtext)): ?>
        <span class="sub-text"><?php echo esc_html($scp->button_subtext); ?></span>
    <?php endif; ?>
</button>
<?php do_action('sc_after_buy_button', $scp); ?> 