<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-cloudflare-action-data-template">
	<div class="flowmattic-metabox-data-form">
		<div class="fm-application-metabox-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( ' Global API Key', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input autocomplete="off" type="password" class="dynamic-field-input w-100 form-control" required name="api_key" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.api_key }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Enter your Global API Key here. Login to your Cloudflare account. You will get your <strong>Global API Key</strong> by navigating to <a href="https://dash.cloudflare.com/profile/api-tokens" target="_blank" rel="noopener">My Profile > API Tokens</a></p>
				</div>
			</div>
			<div class="form-group w-100">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Email', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control" name="email" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.email }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions">
					<p>Enter email address associated with your Cloudflare account.</p>
				</div>
			</div>
		</div>
		<div class="form-group dynamic-inputs w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Cloudflare Account ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control" name="account_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.account_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-cloudflare-accounts btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
			<div class="fm-application-instructions">
				<p>Select Cloudflare account.</p>
			</div>
		</div>
		<div class="fm-cloudflare-app-fields">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-create_domain-data-template">
	<div class="form-group">
		<h4 class="fm-input-title"> Domain Name</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="domain_name" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.domain_name ) { #>{{{ actionAppArgs.domain_name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name to be added to Cloudflare. e.g example.com</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Fetch existing DNS records?</h4>
		<select name="jump_start" class="widget-select form-control w-100" title="Select option">
		  <?php
		  $options = array(
		  	'yes' => esc_html__( 'Yes', 'flowmattic' ),
			'no'  => esc_html__( 'No', 'flowmattic' ),
		  );

		  foreach ( $options as $jump_start => $title ) {
			?>
			<option
			  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $jump_start; ?>' === actionAppArgs.jump_start ) { #>selected<# } #>
			  value="<?php echo $jump_start; ?>">
			  <?php echo $title; ?>
			</option>
			<?php
		  }
		  ?>
		</select>
		<div class="fm-application-instructions">
			<p>Automatically attempt to fetch existing DNS records.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Zone Type</h4>
		<select name="type" class="widget-select form-control w-100" title="Select Zone Type">
		  <?php
		  $options = array(
		  	'full'    => esc_html__( 'Full', 'flowmattic' ),
			'partial' => esc_html__( 'Partial', 'flowmattic' ),
		  );

		  foreach ( $options as $type => $title ) {
			?>
			<option
			  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $type; ?>' === actionAppArgs.type ) { #>selected<# } #>
			  value="<?php echo $type; ?>">
			  <?php echo $title; ?>
			</option>
			<?php
		  }
		  ?>
		</select>
		<div class="fm-application-instructions">
			<p>A full zone implies that DNS is hosted with Cloudflare. A partial zone is typically a partner-hosted zone or a CNAME setup.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-delete_domain-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"> Domain Name Zone ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="zone_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.zone_id ) { #>{{{ actionAppArgs.zone_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-zones btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name zone ID or select from the list.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-create_dns_record-data-template">
	<#
	var time = Date.now();
	#>
	<div class="form-group w-100">
		<h4 class="fm-input-title"> Domain Name Zone ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="zone_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.zone_id ) { #>{{{ actionAppArgs.zone_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-zones btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name zone ID or select from the list.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">DNS Type</h4>
		<select name="type" class="widget-select form-control w-100" title="Select DNS Type">
		  <?php
		  $options = array(
			  'A'      => 'A',
			  'AAAA'   => 'AAAA',
			  'CNAME'  => 'CNAME',
			  'HTTPS'  => 'HTTPS',
			  'TXT'    => 'TXT',
			  'SRV'    => 'SRV',
			  'LOC'    => 'LOC',
			  'MX'     => 'MX',
			  'NS'     => 'NS',
			  'CERT'   => 'CERT',
			  'DNSKEY' => 'DNSKEY',
			  'DS'     => 'DS',
			  'NAPTR'  => 'NAPTR',
			  'SMIMEA' => 'SMIMEA',
			  'SSHFP'  => 'SSHFP',
			  'SVCB'   => 'SVCB',
			  'TLSA'   => 'TLSA',
			  'URI'    => 'URI',
		  );

		  foreach ( $options as $type => $title ) {
			?>
			<option
			  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $type; ?>' === actionAppArgs.type ) { #>selected<# } #>
			  value="<?php echo $type; ?>">
			  <?php echo $title; ?>
			</option>
			<?php
		  }
		  ?>
		</select>
		<div class="fm-application-instructions">
			<p>Choose your DNS record type.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Name</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="name" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.name ) { #>{{{ actionAppArgs.name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>DNS record name (or @ for the zone apex). Eg. subdomain</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Value</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="value" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.value ) { #>{{{ actionAppArgs.value }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>DNS record content. Eg. 127.0.0.1</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">TTL Value</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="ttl" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.ttl ) { #>{{{ actionAppArgs.ttl }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Time to live, <strong>in seconds</strong>, of the DNS record. Must be between 60 and 86400, or 1 for 'automatic'. Eg. 3600</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Priority</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="priority" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.priority ) { #>{{{ actionAppArgs.priority }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Required for MX, SRV and URI records; unused by other record types. Records with lower priorities are preferred. Eg. 10</p>
		</div>
	</div>
	<div class="form-group dynamic-inputs w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Proxied', 'flowmattic' ); ?></h4>
		<input id="fm-checkbox-proxied-{{{ time }}}" class="fm-checkbox form-control" name="proxied" type="checkbox" value="Enable" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.proxied && 'Yes' === actionAppArgs.proxied ) { #>checked<# } #> /> <label for="fm-checkbox-proxied-{{{ time }}}"><?php esc_attr_e( 'Whether the record is receiving the performance and security benefits of Cloudflare', 'flowmattic' ); ?></label>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-update_dns_record-data-template">
	<#
	var time = Date.now();
	#>
	<div class="form-group w-100">
		<h4 class="fm-input-title"> Domain Name Zone ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="zone_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.zone_id ) { #>{{{ actionAppArgs.zone_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-zones btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name zone ID or select from the list.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"> DNS Record ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="dns_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.dns_id ) { #>{{{ actionAppArgs.dns_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-dns btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the DNS record ID or select from the list.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">DNS Type</h4>
		<select name="type" class="widget-select form-control w-100" title="Select DNS Type">
		  <?php
		  $options = array(
			  'A'      => 'A',
			  'AAAA'   => 'AAAA',
			  'CNAME'  => 'CNAME',
			  'HTTPS'  => 'HTTPS',
			  'TXT'    => 'TXT',
			  'SRV'    => 'SRV',
			  'LOC'    => 'LOC',
			  'MX'     => 'MX',
			  'NS'     => 'NS',
			  'CERT'   => 'CERT',
			  'DNSKEY' => 'DNSKEY',
			  'DS'     => 'DS',
			  'NAPTR'  => 'NAPTR',
			  'SMIMEA' => 'SMIMEA',
			  'SSHFP'  => 'SSHFP',
			  'SVCB'   => 'SVCB',
			  'TLSA'   => 'TLSA',
			  'URI'    => 'URI',
		  );

		  foreach ( $options as $type => $title ) {
			?>
			<option
			  <# if ( 'undefined' !== typeof actionAppArgs && '<?php echo $type; ?>' === actionAppArgs.type ) { #>selected<# } #>
			  value="<?php echo $type; ?>">
			  <?php echo $title; ?>
			</option>
			<?php
		  }
		  ?>
		</select>
		<div class="fm-application-instructions">
			<p>Choose your DNS record type.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Name</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="name" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.name ) { #>{{{ actionAppArgs.name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>DNS record name (or @ for the zone apex). Eg. subdomain</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Value</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="value" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.value ) { #>{{{ actionAppArgs.value }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>DNS record content. Eg. 127.0.0.1</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">TTL Value</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="ttl" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.ttl ) { #>{{{ actionAppArgs.ttl }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Time to live, <strong>in seconds</strong>, of the DNS record. Must be between 60 and 86400, or 1 for 'automatic'. Eg. 3600</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title">Priority</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="priority" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.priority ) { #>{{{ actionAppArgs.priority }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Required for MX, SRV and URI records; unused by other record types. Records with lower priorities are preferred. Eg. 10</p>
		</div>
	</div>
	<div class="form-group dynamic-inputs w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Proxied', 'flowmattic' ); ?></h4>
		<input id="fm-checkbox-proxied-{{{ time }}}" class="fm-checkbox form-control" name="proxied" type="checkbox" value="Enable" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.proxied && 'Yes' === actionAppArgs.proxied ) { #>checked<# } #> /> <label for="fm-checkbox-proxied-{{{ time }}}"><?php esc_attr_e( 'Whether the record is receiving the performance and security benefits of Cloudflare', 'flowmattic' ); ?></label>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-delete_dns_record-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"> Domain Name Zone ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="zone_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.zone_id ) { #>{{{ actionAppArgs.zone_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-zones btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name zone ID or select from the list.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"> DNS Record ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="dns_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.dns_id ) { #>{{{ actionAppArgs.dns_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-dns btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the DNS record ID or select from the list.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-purge_cache-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"> Domain Name Zone ID</h4>
		<div class="d-flex">
			<div class="fm-dynamic-input-field w-100">
				<input class="form-control dynamic-field-input w-100" name="zone_id" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.zone_id ) { #>{{{ actionAppArgs.zone_id }}}<# } #>">
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
			<div class="refresh-cloudflare-zones btn btn-refresh btn-outline-secondary">
				<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name zone ID or select from the list.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-search_domain-data-template">
	<div class="form-group">
		<h4 class="fm-input-title"> Domain Name</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="domain_name" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.domain_name ) { #>{{{ actionAppArgs.domain_name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name to search in your Cloudflare account. e.g example.com</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-cloudflare-search_zone_by_domain-data-template">
	<div class="form-group">
		<h4 class="fm-input-title"> Domain Name</h4>
		<div class="fm-dynamic-input-field">
			<input class="form-control dynamic-field-input w-100" name="domain_name" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.domain_name ) { #>{{{ actionAppArgs.domain_name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the domain name to search for zone in your Cloudflare account. e.g example.com</p>
		</div>
	</div>
</script>
