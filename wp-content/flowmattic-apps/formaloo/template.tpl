<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-formaloo-data-template">
	<div class="flowmattic-formaloo-lead-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input type="text" class="w-100" readonly value="{{{webhookURL}}}" />
			<div class="fm-application-instructions">
				<ul style="list-style: square;">
					<li>Login to your Formaloo Account.</li>
					<li>Select your formaloo form.</li>
					<li>Navigate to Integrations & Webhooks tab.</li>
					<li>Click on View Webhooks button, a popup dialog box appears.</li>
					<li>Copy the webhook url above & paste it under Endpoint field.</li>
					<li>Turn ON the events that you want to send the data to webhook.</li>
					<li>Save webhook and do a test.</li>
				</ul>
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
<script type="text/html" id="flowmattic-formaloo-response-template">
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
