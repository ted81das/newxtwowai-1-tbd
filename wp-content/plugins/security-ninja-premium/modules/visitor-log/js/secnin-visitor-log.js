/* globals jQuery:true, ajaxurl:true, secnin_vl:true */
/*
* Security Ninja PRO
* (c) 2021 WP SECURITY NINJA 
*/

/**
 * Function that runs when window regains focus
 *
 * @var		mixed	WindowGotFocus
 * @global
 */
var WindowGotFocus = function() {
  jQuery('#secnin-visitor-log-container').removeClass('unfocused');
  jQuery('#secnin_enable_live').prop("disabled", false);
  jQuery('#secnin-visitor-log-unfocused').fadeOut();
  jQuery(window).one("blur", WindowLostFocus);
  updateVisitorTable();
}

/**
 * Function that runs when window looses focus
 *
 * @var		mixed	WindowLostFocus
 * @global
 */
var WindowLostFocus = function()  { 
  jQuery('#enablecontainer #loadingspinner').removeClass('is-active');
  jQuery('#secnin-visitor-log-unfocused').fadeIn();
  jQuery('#secnin-visitor-log-container').addClass('unfocused');
  jQuery('#secnin_enable_live').prop("disabled", true);
  jQuery(window).one("focus", WindowGotFocus); 
  clearTimeout( secninUpdateTimeout );
}

var secninUpdateTimeout;

/**
 * Function to update the latest visitors
 *
 * @var		mixed	updateVisitorTable
 * @global
 */
var updateVisitorTable = function () {
    
  // Only updates if the window has focus
  if (document.hasFocus()) {
    
    var checkenabled = false;
    // Is the update enabled?
    if (jQuery('#secnin_enable_live').prop('checked') === true) {
      checkenabled = true;
    }
    
    if ( checkenabled ) {
      var latestid= parseInt( jQuery('#secnin_vl_latestid').text() );
      var data = {
        action: 'secnin_get_visitors',
        current: latestid,
        _ajax_nonce: secnin_vl.vl_nonce
      };
      jQuery('#loadingspinner').addClass('is-active');
      jQuery('#secnin-visitor-log .newrow').removeClass('newrow');
      jQuery.ajax({
        url: ajaxurl,
        type: 'GET',
        data: data,
        success: function (response) {
          jQuery('#loadingspinner').removeClass('is-active');

          if (response.data.current) {
              jQuery('#secnin_vl_latestid').text(response.data.current);
          }
          
          // Update visits if we have any
          if (response.data.visits) {
            var visits = response.data.visits;
            visits.forEach((newrow) => {
              var rows = jQuery(newrow);
              rows.hide();
              //  jQuery('#secnin-visitor-log tbody tr:first-child').after(rows);
              jQuery('#secnin-visitor-log tbody').prepend(rows);
              rows.fadeIn("slow");
            });
            
            // Deletes more than x rows - keeping it trim
            jQuery("#secnin-visitor-log > tbody > tr").slice(0, 50);

          }
        },
        error: function () {
          
        }
      });
    }
    
    
  } // document.hasFocus
  else {
    // Document does not have focus
    
  }
  clearTimeout( secninUpdateTimeout );
  secninUpdateTimeout = setTimeout(updateVisitorTable, 10000); // run again
};



jQuery(document).ready(function ($) {

  jQuery(window).one('focus', WindowGotFocus);

  jQuery(document).on('click', '.secnin-banip', function(e) {
    e.preventDefault();

    if (!confirm( secnin_vl.areyousureblockip )) { 
      return false;
    }

    var data = {
      'action': 'secnin_vl_banip',
      '_ajax_nonce': jQuery(this).data('nonce'),
      'banip': jQuery(this).data('banip')
    };

    $.post(ajaxurl, data, function(response) {
      if (response.success) {
        updateVisitorTable();
      }
    });

    return false;
  });

  // First call to get the ball started
  updateVisitorTable();
});