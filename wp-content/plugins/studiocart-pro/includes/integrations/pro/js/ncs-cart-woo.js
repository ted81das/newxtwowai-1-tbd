var search_woo_item = null;
jQuery(document).on('keyup', '.wc-product-search-custom', function(){
	console.log(jQuery(this));
	let term = jQuery(this).find('input').val();

	let selectize_cont = jQuery(this).prev('select');

	let data = {
		'action': 'woocommerce_json_search_products_and_variations',
		'term':term,
		'security': sc_woo_inti.search_products_nonce,
	};

	search_woo_item = jQuery.ajax({
		type: 'GET',
		data: data,
		url: sc_reg_vars.sc_ajax_url,
		beforeSend : function()    {           
			if(search_woo_item != null) {
				search_woo_item.abort();
			}
		},
		success: function(response) {
			let $select = selectize_cont.selectize();
			let selectize = $select[0].selectize;
			selectize.clearOptions();
			let terms = [];
			if ( response ) {
				jQuery.each( response, function( id, text ) {
					terms.push( { id: id, text: text } );
					selectize.addOption({text: text, value: id});
					selectize.refreshOptions();
				});
			}
		},
		error:function(e){
		  // Error
		}
	});
});