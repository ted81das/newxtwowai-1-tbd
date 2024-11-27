/* globals jQuery:true, ajaxurl:true, sn_block_ui:true, secninScheduledScanner:true */
/*
 * Security Ninja - Scheduled Scanner add-on
 * (c) Web factory 2015
 * Updated 2020 - Larsik Corp
 */

function fetchLogs() {

  var data = {
    action: 'sn_ss_get_logs',
    nonce: secninScheduledScanner.nonce
  };

  jQuery.post(ajaxurl, data, function (response) {
    if (response.success) {
      jQuery('#wf-sn-ss-log tbody').html(response.data);
    } else {
      jQuery('#wf-sn-ss-log tbody').html('<tr><td colspan="4"><span class="no-logs">' + response.data + '</span></td></tr>');
    }
  });
}




jQuery(document).ready(function ($) {

fetchLogs();


  // run tests, via ajax
  jQuery('#sn-ss-test').on('click', function () {
    if (!confirm('Please remember to save settings before testing them. Continue?')) {
      return;
    }
    var data = {
      action: 'sn_ss_cs_test',
      nonce: secninScheduledScanner.nonce
    };
    jQuery('#wf-ss-output').show();

    var startTime = Date.now(); // Capture start time



    var $output = jQuery("#wf-ss-output");
    jQuery.ajax({
      url: ajaxurl,
      type: 'POST',
      data: data,
      success: function (response) {
        clearInterval(timer);
        $output.empty();
        $output.append('<p>Success: ' + response.data.message + '</p>');
      },
      error: function () {
        clearInterval(timer);
        $output.empty();
      }
    });



    // Update timer every second
    var timer = setInterval(function () {
      var elapsedSeconds = Math.floor((Date.now() - startTime) / 1000);
      $("#wf-ss-output #sn-timer").text('Time elapsed: ' + elapsedSeconds + ' '+ 'seconds');
    }, 1000);



  }); // run tests


  // Truncate log table
  $('#wf-sn-ss-truncate-log').on('click', function () {
    var answer = confirm("Are you sure you want to delete all log entries?"); // @i8n
    if (answer) {
      var data = {
        action: 'sn_ss_truncate_log',
        nonce: secninScheduledScanner.nonce
      };
      $.post(ajaxurl, data, function (response) {
        if (!response) {
          alert('Bad AJAX response. Please reload the page.');
        } else {
          fetchLogs(); // Fetch logs again after truncation
        }
      });
    }
    return false;
  });

  // security ninja results details
  $(document).on('click', '.ss-details-sn', function () {
    var $this = $(this);
    var data = {
      'action': 'sn_ss_sn_details',
      'nonce': secninScheduledScanner.nonce,
      'row_id': $this.attr('data-row-id')
    };
    var timestamp = $this.attr('data-timestamp');

    // Disable all .ss-details-sn elements and show spinner
    $('.ss-details-sn').prop('disabled', true);
    $this.append('<span class="spinner is-active"></span>');

    $.post(ajaxurl, data, function (response) {
      // Remove spinner and re-enable all .ss-details-sn elements
      $('.spinner').remove(); // Remove spinner from all elements
      $('.ss-details-sn').prop('disabled', false); // Re-enable all elements

      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        $('#sn-scheduled-scanner').html(response)
          .dialog({ title: 'Security Ninja results from ' + timestamp })
          .dialog('open');
      }
    }).fail(function() {
      // Handle AJAX error
      alert('An error occurred. Please try again.');
      $('.spinner').remove(); // Remove spinner on error
      $('.ss-details-sn').prop('disabled', false); // Re-enable all elements
    });

    return false;
  });


  // Core Scanner results details
  $(document).on('click', '.ss-details-cs', function () {
    var $this = $(this);
    var data = {
      action: 'sn_ss_cs_details',
      row_id: $this.attr('data-row-id'),
      nonce: secninScheduledScanner.nonce
    };
    var timestamp = $this.attr('data-timestamp');

    // Disable all .ss-details-cs elements and show spinner
    $('.ss-details-cs').prop('disabled', true);
    $this.append('<span class="spinner is-active"></span>');

    $.post(ajaxurl, data, function (response) {
      // Remove spinner and re-enable all .ss-details-cs elements
      $('.spinner').remove();
      $('.ss-details-cs').prop('disabled', false);
      
      if (!response) {
        alert('Bad AJAX response. Please reload the page.');
      } else {
        jQuery('#sn-scheduled-scanner').html(response)
          .dialog({
            title: 'Core Scanner results from ' + timestamp
          })
          .dialog('open');
      }
    }).fail(function() {
      // Handle AJAX error
      alert('An error occurred. Please try again.');
      $('.spinner').remove();
      $('.ss-details-cs').prop('disabled', false);
    });

    return false;
  });




  // prepare dialog

  jQuery('#sn-scheduled-scanner').dialog({
    'dialogClass': 'wp-dialog',
    'modal': true,
    'resizable': true,
    'zIndex': 9999,
    'width': 900,
    'height': '550',
    'hide': 'fade',
    'show': 'fade',
    'autoOpen': false,
    'closeOnEscape': true
  });


});