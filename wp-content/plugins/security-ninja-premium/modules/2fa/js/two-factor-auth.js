/* global two_factor_auth, jQuery */
/**
                 * Generates a new QR code.
                 */
function generateQRCode() {
    jQuery('#generate-qr').prop('disabled', true);
    var $spinner = jQuery('.qr-code .spinner');
    $spinner.addClass('is-active');

    jQuery.ajax({
        url: two_factor_auth.ajaxurl,
        type: 'POST',
        data: {
            action: 'secnin_generate_qr_code',
            nonce: two_factor_auth.nonce,
            temp_token: two_factor_auth.temp_token,
            user_ip: two_factor_auth.user_ip,
            user_agent: two_factor_auth.user_agent
        },
        success: function (response) {
            jQuery('#generate-qr').prop('disabled', false);
            $spinner.removeClass('is-active');
            if (response.success) {
                jQuery('#qr-code-img-err').text('');
                jQuery('#qr-code-img').attr('src', response.data.qr_code);
            } else {
                jQuery('#qr-code-img-err').html('<p class="errmsg">' + 
                    'Error generating QR code. Please try again. Debug info: ' + 
                    response.data.message + '</p>');
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error('AJAX error:', textStatus, errorThrown);
            jQuery('#generate-qr').prop('disabled', false);
            $spinner.removeClass('is-active');
            jQuery('#qr-code-img-err').html('<p class="errmsg">' + 
                'Error generating QR code. Please check your internet connection and try again. Status: ' + 
                jqXHR.status + ', Response Text: ' + jqXHR.responseText + '</p>');
        }
    });
}
/*
// Automatically generate the QR code when the page loads
generateQRCode();

jQuery('#generate-qr').on('click', function(e) {
    e.preventDefault();
    generateQRCode();
});
*/
jQuery(document).ready(function ($) {

    var $form = jQuery('#twofa-form-verify');
    var $input = jQuery('#twofa-code');
    var $button = jQuery('#verify-2fa');

    // Check if the form element exists
    if (jQuery('.qr-code').length) {
        console.log('2FA Debug: Form found, generating QR code');
        generateQRCode();
    } else {
        console.log('2FA Debug: Form not found');
    }

    $input.on('input', function () {
        var inputValue = jQuery(this).val();
        
        // Remove any non-digit characters
        inputValue = inputValue.replace(/\D/g, '');
        
        // Limit to 6 digits
        inputValue = inputValue.slice(0, 6);
        
        // Update input value
        jQuery(this).val(inputValue);
        
        // Enable/disable submit button based on input validity
        $button.prop('disabled', inputValue.length !== 6);
    });

    $form.on('submit', function (e) {
        e.preventDefault();
        var twofacode = $input.val();
        console.log('2FA Debug: Form submitted with code:', twofacode);

        jQuery('#twofa-verify-msg').html('<p class="okmsg">Checking code.</p>');

        $button.prop('disabled', true);
        $input.prop('disabled', true);

        $.ajax({
            url: two_factor_auth.ajaxurl,
            type: 'POST',
            data: {
                action: 'secnin_verify_2fa_code',
                code: twofacode,
                nonce: two_factor_auth.nonce,
                temp_token: two_factor_auth.temp_token,
                user_id: two_factor_auth.user_id,
                user_ip: two_factor_auth.user_ip,
                user_agent: two_factor_auth.user_agent
            },
            success: function (response) {
                
                if (response.success) {
                    jQuery('#twofa-verify-msg').html('<p class="okmsg">' + response.data.message + '</p>');
                    console.log('2FA Debug: Redirecting to', response.data.redir_to);
                    
                    // Perform redirection after a short delay
                    setTimeout(function() {
                        window.location.href = response.data.redir_to;
                    }, 1000); // 1 second delay
                } else {
                    console.log('2FA Debug: AJAX response:', response);
                    $button.prop('disabled', false);
                    $input.prop('disabled', false);
                    jQuery('#twofa-verify-msg').html('<p class="errmsg">' + response.data.message + '</p>');
                }
            },
            error: function (jqXHR) {
                console.error('2FA Debug: AJAX error:', jqXHR);
                jQuery('#twofa-verify-msg').html('<p class="errmsg">Error verifying code. Please check your internet connection and try again. Status: ' + jqXHR.status + ', Response Text: ' + jqXHR.responseText + '</p>');
                $button.prop('disabled', false);
                $input.prop('disabled', false);
            }
        });
    });
});
