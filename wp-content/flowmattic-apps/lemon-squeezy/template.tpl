<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-lemon-squeezy-trigger-data-template">
	<div class="flowmattic-lemon-squeezy-trigger-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Webhook URL', 'flowmattic' ); ?></h4>
			<input type="text" autocomplete="off" class="w-100" readonly value="{{{webhookURL}}}" />
			<div class="fm-application-instructions">
				<p>
					<ul style="list-style: square;">
						<li>Login to your <strong><a href="https://app.lemonsqueezy.com/" target="_blank">Lemon Squeezy account</a></strong></li>
						<li>Navigate to the <strong><a href="https://app.lemonsqueezy.com/settings/webhooks" target="_blank">Webhooks</a></strong> section available under Settings.</li>
						<li>Click on the plus icon to add a new webhook.</li>
						<li>Copy the above webhook URL and paste it into the Callback URL field provided in the Lemon Squeezy webhook settings.</li>
						<li>Enter any random string as Signing Secrete</li>
						<li>Now select the <strong class="lemon-squeezy-event-selected"></strong> from the Events, and then click on the <strong>Save Webhook</strong> button to save it.</li>
						<li>Click on the below <strong>Capture Webhook Response</strong> button and do a test record so that the webhook response can be captured here.</li>
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