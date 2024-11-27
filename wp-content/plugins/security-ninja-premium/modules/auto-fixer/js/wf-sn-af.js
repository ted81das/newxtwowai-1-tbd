/* globals jQuery:true, ajaxurl:true, wf_sn_af:true */
/*
 * Security Ninja - Auto Fixer add-on
 * (c) 2014. Web factory Ltd
 */


 jQuery(document).ready(function($) {

  $(document).on('click', '.sn_af_run_fix', function(e) {
    e.preventDefault();
    var test_id = jQuery(this).data('test-id');

    if (!confirm('Are you sure you want to apply the fix?')) {
      return false;
    }
    jQuery( ".snautofix[data-test-id='" + test_id + "']" ).html('<div class="sn-fixer-loader">Applying the fix.</div>').show();
  
  
    var fix_fields={};

    jQuery( ".snautofix[data-test-id='" + test_id + "'] input" ).each(function() {
      if ($(this).attr('type') == 'checkbox') {
        if ($(this).is(':checked')) {
          fix_fields[$(this).attr('name')] = 'true';
        } else {
          fix_fields[$(this).attr('name')] = 'false';
        }
      } else {
        fix_fields[$(this).attr('name')] = $(this).val();
      }
    });
    $.ajax({
      url: ajaxurl, // WP variable, contains URL pointing to admin-ajax.php
      type: 'POST',
      data: {
        'action': 'sn_af_do_fix',
        '_ajax_nonce': wf_sn_af.nonce_do_fix,
        'test_id': test_id,
        'fields': JSON.stringify(fix_fields)
      },
      success: function( response ) {
        jQuery( ".snautofix[data-test-id='" + test_id + "']" ).html('<p>' + response.data + '<br><b>Please note</b>: analyze the site again to update the result and overall score.</p>');
      },
      error: function( response ) {
        jQuery( ".snautofix[data-test-id='" + test_id + "']" ).html('<p>The fix could not be applied.</p><div class="error">' + response.data + '</div>');
      }
    });





    return false;
  });



  // Listens for click on "Details" on tests page, that trigger an action here
  $(document).on('sn_test_details_dialog_open', function(e, test_id, test_status) {
    data = {
      'action': 'sn_af_get_fix_info',
      '_ajax_nonce': wf_sn_af.nonce_get_fix_info,
      'test_id': test_id,
      'test_status': test_status
    };

    // Only process failed tests or with warnings
    if ( test_status < 10) {
      $.get(ajaxurl, data, function(response) {
        if (response.success) {
          content = response.data;
        } else {
          content = 'Undocumented error. Unable to get fix info. Please reload the page and try again.';
        }

        jQuery( '.testtimedetails.' + test_id + ' .snautofix').html( content ).show();        
      });
    }


  });




});
