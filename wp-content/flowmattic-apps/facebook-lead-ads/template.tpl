<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-facebook-lead-ads-data-template">
	<div class="flowmattic-facebook-form-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="facebook-api-connect form-control w-100 d-block" required title="Choose Connect" data-live-search="true">
                    <#
                    let connectID = ( 'undefined' !== typeof connect_id ) ? connect_id : '';
                    #>
                    <?php
                        $all_connects = wp_flowmattic()->connects_db->get_all();
                        foreach ( $all_connects as $key => $connect_item ) {
                            $connect_id   = $connect_item->id;
                            $connect_name = $connect_item->connect_name;
                            ?>
                            <option <# if ( connectID === '<?php echo esc_attr( $connect_id ); ?>' ) { #>selected<# } #> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
                            <?php
                        }
                    ?>
				</select>
			</div>
		</div>
		<div class="fm-facebook-trigger w-100">
			<div class="card border-light mw-100 mb-3 trigger-page-alert">
				<div class="card-body text-center">
					<div class="alert alert-primary p-4 m-2 text-center" role="alert">
						<?php esc_html_e( 'Please select the connect associated with your Facebook account to load the Pages and forms.', 'flowmattic' ); ?>
					</div>
				</div>
			</div>
			<div class="form-group w-100 trigger-pages d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Page', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="d-flex">
					<div class="w-100">
						<select class="w-100 form-control page_id_input" required name="page_id" title="Choose Page"></select>
					</div>
					<div class="refresh-pages btn btn-refresh btn-outline-secondary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
					</div>
				</div>
				<div class="fm-application-instructions">
					<p><?php echo esc_attr__( 'Select the page.', 'flowmattic' ); ?>
				</div>
			</div>
			<div class="form-group w-100 trigger-forms d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Lead Form', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="d-flex">
					<div class="w-100">
						<select class="w-100 form-control lead_form_id_input" required name="form_id" title="Choose Lead Form"></select>
					</div>
					<div class="refresh-forms btn btn-refresh btn-outline-secondary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
					</div>
				</div>
				<div class="fm-application-instructions">
					<p><?php echo esc_attr__( 'Select the Lead Form.', 'flowmattic' ); ?>
				</div>
			</div>
			<div class="fm-application-connect mt-3 mb-3">
				<#
				if ( 'undefined' !== typeof validate_facebook_response && '' !== validate_facebook_response ) {
					#>
					<a href="javascript:void(0);" class="btn btn-outline-success flowmattic-connect-facebook-button">
						<?php echo esc_attr__( 'Connected to Facebook', 'flowmattic' ); ?>
					</a>	
					<#
				} else {
					#>
					<a href="javascript:void(0);" class="btn btn-success flowmattic-button flowmattic-connect-facebook-button">
						<?php echo esc_attr__( 'Connect to Facebook', 'flowmattic' ); ?>
					</a>
					<#
				}
				#>
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
<script type="text/html" id="flowmattic-application-facebook-lead-ads-action-template">
	<div class="flowmattic-facebook-lead-ads-data-form">
		<div class="form-group data-auth_option data-auth_connect w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="facebook-lead-ads-connect form-control w-100 d-block" required title="Choose Connect" data-live-search="true">
				<?php
					$all_connects = wp_flowmattic()->connects_db->get_all();
					foreach ( $all_connects as $key => $connect_item ) {
						$connect_id   = $connect_item->id;
						$connect_name = $connect_item->connect_name;
						?>
						<option <# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.connect_id === '<?php echo esc_attr( $connect_id ); ?>' ) { #>selected<# } #> value="<?php echo esc_attr( $connect_id ); ?>" data-subtext="ID: <?php echo esc_attr( $connect_id ); ?>"><?php echo esc_attr( $connect_name ); ?></option>
						<?php
					}
				?>
				</select>
			</div>
		</div>
		<div class="facebook-lead-ads-action-data"></div>
	</div>
</script>
<script type="text/html" id="facebook-lead-ads-action-retrieve_leads-data-template">
	<div class="flowmattic-facebook-lead-ads-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Page ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control page_id_input" name="page_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.page_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-facebook-pages btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Form ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control lead_form_id_input" name="form_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.form_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-page-forms btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Number of Leads', 'flowmattic' ); ?></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control lead_limit_input" name="lead_limit" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.lead_limit }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-facebook-dropdown-template">
	<#
	_.each( templates, function( template, key ) {
		var	id = template.id,
			name = ( 'undefined' !== typeof template.name ) ? template.name : id;
		#>
		<option
			value="{{{ id }}}" data-subtext="ID: {{{ id }}}">
			{{{ name }}}
		</option>
		<#
	} );
	#>
</script>