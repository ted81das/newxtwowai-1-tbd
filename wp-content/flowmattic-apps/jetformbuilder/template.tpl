<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-jetformbuilder-data-template">
	<div class="flowmattic-jetformbuilder-form-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input type="text" class="w-100" readonly value="{{{webhookURL}}}" />
			<div class="fm-application-instructions">
				<p>
					<ul style="list-style: square;">
						<li>Go to the <a href="<?php echo admin_url( 'edit.php?post_type=jet-form-builder' ); ?>" target="_blank"><strong>JetFormBuilder Forms</strong></a></li>
						<li>Edit the Form you want to use for this workflow.</li>
						<li>In <strong>Post Submit Actions</strong> section, click on <strong>New Action</strong> button</li>
						<li>Choose <strong>Webhook</strong> from the list of actions</li>
						<li>Copy the webhook URL above and paste it under <strong>Webhook URL</strong> field</li>
						<li>Save the form and click <strong>capture response</strong> button below and send a test submission to the form</li>
						<li>For more info please refer this <strong><a href="https://jetformbuilder.com/features/call-webhook/" target="_blank">Documentation</a></strong></li>
					</ul>
				</p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-webhook-capture-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture Response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Capture Response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
