<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-emaillistvalidation-data-template">
	<div class="flowmattic-emaillistvalidation-data-form">
		<div class="fm-application-emaillistvalidation-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Account Details', 'flowmattic' ); ?></h4>
				<div class="row form-row font-15">
					<div class="col-md-12">
						<h5>API Key <span class="badge outline bg-danger">Required</span></h5>
						<div class="fm-dynamic-input-field">
							<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" required name="api_key" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.api_key }}}<# } #>">
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
					</div>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Verify Email', 'flowmattic' ); ?></h4>
				<div class="form-row">
					<h5>Email <span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="email" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.email }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
