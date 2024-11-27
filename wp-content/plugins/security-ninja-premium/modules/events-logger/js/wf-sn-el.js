/* globals jQuery:true, ajaxurl:true, wf_sn_el:true, datatables_object:true */
/*
 * Security Ninja - Events Logger add-on
 * (c) Web factory Ltd, 2015
 * Larsik Corp 2020 - 
 */

var eventstable;  // Declare 'eventstable' in the outer scope

jQuery(document).ready(function ($) {
  jQuery(document).ready(function() {
    eventstable = jQuery('#sn-el-datatable').DataTable({
      "processing": true,
      "serverSide": true,
      "ajax": {
        "url": ajaxurl,
        "type": "POST",
        "data": {
          "action": "get_events_data",
          "nonce": datatables_object.nonce
        },
        "error": function(xhr, error, code) {
          var errorMsg = "<strong>Error loading data:</strong><br>" +
                         "Status: " + xhr.status + " (" + xhr.statusText + ")<br>" +
                         "Error: " + error + "<br>" +
                         "Code: " + code + "<br>" +
                         "Response: " + xhr.responseText;
          jQuery('#datatable-error').html(errorMsg).show();
        }
      },
      "columns": [
        { "data": "timestamp", "title": "Time" },
        { "data": "action", "title": "Action" },
        { "data": "user_id", "title": "User" },
        { "data": "description", "title": "Event" },
        { "data": "details", "title": "Details", "orderable": false }
      ],
      "order": [[ 0, "desc" ]],
      "columnDefs": [{
        "targets": 4,
        "data": null,
        "defaultContent": "<button>Detail</button>"
      }]
    });
  });

  /**
   * Child rows in the event log table
   *
   * @var		mixed	#sn-el-datatabl
   */
  $('#sn-el-datatable tbody').on('click', 'button', function () {
    var tr = $(this).closest('tr');
    var row = eventstable.row(tr);

    if (row.child.isShown()) {
        // This row is already open - close it
        row.child.hide();
        tr.removeClass('shown');
        $(this).removeClass('open'); // Remove the 'open' class
    } else {
        // Open this row
        var details = tr.find('.details-content').html();
        row.child(details).show();
        tr.addClass('shown');
        $(this).addClass('open'); // Add the 'open' class
    }
});

  // truncate log table
  $('#sn-el-truncate').on('click', function (e) {
    e.preventDefault();

    var answer = confirm("Are you sure you want to delete all log entries?"); // @i8n
    if (answer) {
      var data = {
        action: 'sn_el_truncate_log',
        _ajax_nonce: wf_sn_el.nonce
      };
      $.post(ajaxurl, data, function (response) {
        if (!response) {
          alert('Bad AJAX response. Please reload the page.'); // @i8n
        } else {
          alert('All log entries have been deleted.'); // @i8n
          window.location.reload();
        }
      });
    }
  });
});