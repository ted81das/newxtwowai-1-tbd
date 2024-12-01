<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-n8n-trigger-data-template">
	<div class="flowmattic-n8n-trigger-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input type="text" class="w-100" readonly value="{{{webhookURL}}}" />
			<div class="fm-application-instructions">
				<p>{{{ otherTriggerApps[ application ].instructions }}}</p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture Webhook Data', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Capture Webhook Data', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-n8n-action-data-template">
	<div class="flowmattic-n8n-action-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'n8n.io Webhook URL', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="n8n_webhook" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.n8n_webhook ) { #>{{{ actionAppArgs.n8n_webhook }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Copy webhook URL from your n8n.io workflow, and enter it here', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs api-parameters w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Data Parameters', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="n8n_parameters">
				<#
				if ( 'undefined' !== typeof n8n_parameters && ! _.isEmpty( n8n_parameters ) ) {
					_.each( n8n_parameters, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-api-request-parameters">
							<div class="fm-dynamic-input-field">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="text" placeholder="key" value="{{{key}}}" />
							</div>
							<div class="fm-dynamic-input-field">
								<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" name="dynamic-field-value[]" placeholder="value">{{{value}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
									<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
								</svg>
							</a>
						</div>
						<#
					} );
				} else {
					#>
					<div class="fm-dynamic-input-wrap fm-api-request-parameters">
						<div class="fm-dynamic-input-field">
							<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="text" placeholder="key" value="" />
						</div>
						<div class="fm-dynamic-input-field">
							<textarea rows="1" class="fm-textarea fm-dynamic-inputs dynamic-field-input w-100" autocomplete="off" name="dynamic-field-value[]" placeholder="value"></textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
						<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="#333333" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" fill="none" d="M20 22H4C2.9 22 2 21.1 2 20V4C2 2.9 2.9 2 4 2H20C21.1 2 22 2.9 22 4V20C22 21.1 21.1 22 20 22Z"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M6 6L18 18"></path>
								<path stroke-linejoin="round" stroke-linecap="round" stroke-miterlimit="10" stroke-width="1" stroke="#333333" d="M18 6L6 18"></path>
							</svg>
						</a>
					</div>
					<#
				}
				#>
				<div class="dynamic-input-add-more fm-api-parameters-add-more">
					<a href="javascript:void(0);" class="btn flowmattic-button btn-small btn-success btn-add-more-parameters"><?php echo esc_attr__( 'Add More', 'flowmattic' ); ?></a>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-n8n-response-template">
<div class="fm-response-body-wrapper">
	<a href="javascript:void(0);" class="fm-response-data-toggle webhook-data-toggle toggle">
		<span class="fm-response-toggle-icon">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" data-reactroot="">
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" fill="none" d="M19 22H5C3.34 22 2 20.66 2 19V5C2 3.34 3.34 2 5 2H19C20.66 2 22 3.34 22 5V19C22 20.66 20.66 22 19 22Z"></path>
				<path stroke-linejoin="round" stroke-linecap="round" stroke-width="1" stroke="#221b38" d="M6.5 9.5L12 15L17.5 9.5"></path>
			</svg>
		</span>
		<?php echo esc_attr__( 'Webhook Response', 'flowmattic' ); ?>
	</a>
	<div class="fm-response-body webhook-response-body w-100" style="display:none;">
		<table class="fm-webhook-response-data-table w-100">
			<thead>
				<tr>
					<th class="w-50">Key</th>
					<th class="w-50">Value</th>
				</tr>
			</thead>
			<tbody>
			<#
			var data = {};

			if ( 'undefined' !== typeof capturedData.webhook_capture ) {
				data = capturedData.webhook_capture;
			} else {
				data = capturedData;
			}

			_.each( data, function( value, key ) {
				key = FlowMatticWorkflow.UCWords( key );
				#>
				<tr>
					<td>
						<input class="fm-response-form-input w-100" type="text" value="{{{ key }}}" readonly />
					</td>
					<td>
						<textarea class="fm-response-form-input w-100" rows="1" readonly>{{{ value }}}</textarea>
					</td>
				<#
			} );
			#>
			</tbody>
		</table>
	</div>
</script>
