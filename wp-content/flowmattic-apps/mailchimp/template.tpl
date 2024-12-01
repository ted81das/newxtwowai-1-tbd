<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<?php
$access_token = '';

if ( isset( $_GET['workflow-id'] ) ) {
	// Get the authentication data.
	$workflow_id         = $_GET['workflow-id'];
	$authentication_data = flowmattic_get_auth_data( 'mailchimp', $workflow_id );
}

$button_title = esc_attr__( 'Authenticate Mailchimp', 'flowmattic' );
$button_class = 'btn-primary flowmattic-button';
$instructions = esc_attr__( 'Connect your Mailchimp account. Your credentials are stored securely in your site.', 'flowmattic' );

if ( isset( $authentication_data['auth_data'] ) ) {
	$button_title = esc_attr__( 'Connected to Mailchimp', 'flowmattic' );
	$button_class = 'btn-outline-primary';
	$instructions = esc_attr__( 'Mailchimp account is connected. Your credentials are stored securely in your site.', 'flowmattic' );
}

$mailchimp_lists = array();

if ( isset( $authentication_data['auth_data']['access_token'] ) ) {
	$mailchimp_lists = get_transient( 'flowmattic-mailchimp-lists' );

	if ( false === $mailchimp_lists ) {
		$mailchimp_auth_data = $authentication_data['auth_data'];
		$access_token = $mailchimp_auth_data['access_token'];
		$data_center  = $mailchimp_auth_data['data_center'];

		$args = array(
			'headers' => array(
				'Authorization' => 'OAuth ' . $access_token,
				'User-Agent'    => 'FlowMattic',
			),
			'timeout' => 20,
		);

		// Get all available spreadsheets.
		$request = wp_remote_get( 'https://' . $data_center . '.api.mailchimp.com/3.0/lists', $args );
		$request = wp_remote_retrieve_body( $request );
		$mailchimp_lists = json_decode( $request, true );

		set_transient( 'flowmattic-mailchimp-lists', $mailchimp_lists, HOUR_IN_SECONDS * 8 );
	}
}
?>
<script type="text/html" id="flowmattic-application-mailchimp-trigger-data-template">
	<div class="flowmattic-mailchimp-trigger-data">
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
<script type="text/html" id="flowmattic-mailchimp-account-template">
	<div class="flowmattic-mailchimp-data-form">
		<div class="fm-application-mailchimp-data">
			<div class="form-group w-100">
				<h4 class="input-title"><?php echo esc_attr__( 'Account Details', 'flowmattic' ); ?></h4>
				<div class="fm-form-capture-button m-t-20">
					<a href="javascript:void(0);" class="btn <?php echo $button_class; ?> flowmattic-auth-mailchimp-connection-button mailchimp-button-text">
						<?php echo $button_title; ?>
					</a>
					<p class="pt-2 mb-0"><?php echo $instructions; ?></p>
				</div>
			</div>
		</div>
		<div class="form-group list-select w-100">
			<div class="fm-select-list">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Select Audience List', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="d-flex">
					<select name="mailchimp-list" class="mailchimp-list-select mw-100 w-100" required title="Select Audience List" data-live-search="true">
						<?php
						if ( ! empty( $mailchimp_lists['lists'] ) ) {
							foreach ( $mailchimp_lists['lists'] as $index => $list ) {
								?>
								<option
									<# if ( 'undefined' !== typeof audienceList && '<?php echo esc_attr( $list["id"] ); ?>' === audienceList ) { #>selected<# } #>
									value="<?php echo esc_attr( $list["id"] ); ?>"
									data-subtext="ID: <?php echo esc_attr( $list["id"] ); ?>">
									<?php echo esc_attr( $list['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</select>
					<div class="refresh-lists btn btn-refresh btn-outline-secondary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
					</div>
				</div>
			</div>
		</div>
		<div class="fm-mailchimp-action-data"></div>
	</div>
</script>
<script type="text/html" id="flowmattic-mailchimp-new_member-action-template">
	<div class="fm-application-mailchimp-data">
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'Contact Details', 'flowmattic' ); ?></h4>
			<div class="form-row">
				<h5>Email <span class="badge outline bg-danger">Required</span></h5>
				<div class="fm-dynamic-input-field">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="email_address" required value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.email_address }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="row form-row">
				<div class="col-md-6 form-row m-t-20">
					<h5>First Name</h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="first_name" value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.first_name }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
				<div class="col-md-6 form-row m-t-20">
					<h5>Last Name</h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="last_name" value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.last_name }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
			</div>
			<div class="form-row m-t-20">
				<h5><strong>Subscription Status</strong></h5>
				<div class="fm-status-input-field">
					<select name="status" class="status-select form-control w-100" title="Select Subscription Status" data-live-search="true">
						<?php
						$status_options = array(
							'subscribed'    => esc_attr( 'Subscribed', 'flowmattic' ),
							'unsubscribed'  => esc_attr( 'Unsubscribed', 'flowmattic' ),
							'cleaned'       => esc_attr( 'Cleaned', 'flowmattic' ),
							'pending'       => esc_attr( 'Pending', 'flowmattic' ),
							'transactional' => esc_attr( 'Transactional', 'flowmattic' ),
						);

						foreach ( $status_options as $status => $title ) {
							?>
							<option
								<# if ( 'undefined' !== typeof mailchimpArgs.status && '<?php echo $status; ?>' === mailchimpArgs.status ) { #>selected<# } #>
								value="<?php echo $status; ?>">
								<?php echo $title; ?>
							</option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-mailchimp-new_member_tag-action-template">
	<div class="fm-application-mailchimp-data">
		<div class="form-group w-100">
			<div class="row form-row">
				<h4 class="input-title"><?php echo esc_attr__( 'Member Tag Details', 'flowmattic' ); ?></h4>
				<div class="form-row m-t-20">
					<h5>Member Email<span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="member" required value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.member }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
				<div class="form-row m-t-20">
					<h5>Tags <span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" required type="search" name="tags" value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.tags }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<label><?php echo esc_attr( 'Enter comma (,) separated list of tags. Eg. Tag 1, Tag 2', 'flowmattic' ); ?></label>
				</div>
				<div class="form-row m-t-20">
					<h5><strong>Is Syncing</strong></h5>
					<div class="fm-dynamic-input-field">
						<input id="fm-checkbox-is-syncing" type="checkbox" class="form-control" name="is_syncing" <# if ( 'undefined' !== typeof mailchimpArgs && 'Yes' === mailchimpArgs.is_syncing ) { #>checked<# } #>>
						<label for="fm-checkbox-is-syncing"><?php echo esc_attr( 'When checked, automations based on the tags in the request will not fire', 'flowmattic' ); ?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-mailchimp-remove_member_tag-action-template">
	<div class="fm-application-mailchimp-data">
		<div class="form-group w-100">
			<div class="row form-row">
				<h4 class="input-title"><?php echo esc_attr__( 'Member Tag Details', 'flowmattic' ); ?></h4>
				<div class="form-row m-t-20">
					<h5>Member Email<span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="member" required value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.member }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
				<div class="form-row m-t-20">
					<h5>Tags <span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" required type="search" name="tags" value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.tags }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<label><?php echo esc_attr( 'Enter comma (,) separated list of tags. Eg. Tag 1, Tag 2', 'flowmattic' ); ?></label>
				</div>
				<div class="form-row m-t-20">
					<h5><strong>Is Syncing</strong></h5>
					<div class="fm-dynamic-input-field">
						<input id="fm-checkbox-is-syncing" type="checkbox" class="form-control" name="is_syncing" <# if ( 'undefined' !== typeof mailchimpArgs && 'Yes' === mailchimpArgs.is_syncing ) { #>checked<# } #>>
						<label for="fm-checkbox-is-syncing"><?php echo esc_attr( 'When checked, automations based on the tags in the request will not fire', 'flowmattic' ); ?></label>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-mailchimp-new_member_note-action-template">
	<div class="fm-application-mailchimp-data">
		<div class="form-group w-100">
			<div class="row form-row">
				<h4 class="input-title"><?php echo esc_attr__( 'Member Note Details', 'flowmattic' ); ?></h4>
				<div class="form-row m-t-20">
					<h5>Member Email<span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="member" required value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.member }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
				<div class="form-row m-t-20">
					<h5>Note Content <span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<textarea class="dynamic-field-input w-100 form-control" required autocomplete="off" name="note"><# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.note }}}<# } #></textarea>
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<label><?php echo esc_attr( 'The content of the note. Note length is limited to 1,000 characters', 'flowmattic' ); ?></label>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-mailchimp-delete_list_member-action-template">
	<div class="fm-application-mailchimp-data">
		<div class="form-group w-100">
			<div class="row form-row">
				<h4 class="input-title"><?php echo esc_attr__( 'Member Details', 'flowmattic' ); ?></h4>
				<div class="form-row m-t-20">
					<h5>Member Email<span class="badge outline bg-danger">Required</span></h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="member" required value="<# if ( 'undefined' !== typeof mailchimpArgs ) { #>{{{ mailchimpArgs.member }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
