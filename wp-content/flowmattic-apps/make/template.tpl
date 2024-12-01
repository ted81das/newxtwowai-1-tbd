<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-make-trigger-data-template">
	<div class="flowmattic-make-trigger-data">
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
<script type="text/html" id="flowmattic-application-make-action-data-template">
	<div class="flowmattic-make-action-data">
		<div class="form-group webhook-url w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Make Webhook URL', 'flowmattic' ); ?></h4>
			<div class="fm-dynamic-input-field">
				<input type="text" class="form-control dynamic-field-input w-100" name="make_webhook" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.make_webhook ) { #>{{{ actionAppArgs.make_webhook }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="fm-application-instructions">
				<p>
					<?php echo esc_attr__( 'Copy webhook URL from your Make workflow, and enter it here', 'flowmattic' ); ?>
				</p>
			</div>
		</div>
		<div class="form-group dynamic-inputs api-parameters w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Data Parameters', 'flowmattic' ); ?></h4>
			<div class="fm-api-request-parameters-body m-t-20 data-dynamic-fields" data-field-name="make_parameters">
				<#
				if ( 'undefined' !== typeof make_parameters && ! _.isEmpty( make_parameters ) ) {
					_.each( make_parameters, function( value, key ) {
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