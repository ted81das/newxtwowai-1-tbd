<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-notion-data-template">
	<div class="flowmattic-notion-form-data">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Database ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="w-100">
					<select class="w-100 form-control database_id_trigger" required name="database_id" title="Choose Database" data-live-search="true"></select>
				</div>
				<div class="refresh-databases-trigger btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
			<div class="fm-application-instructions">
				<p><?php echo esc_attr__( 'If you don\'t see your expected database, please check that it is shared with the same integration that you\'re using to authenticate with.', 'flowmattic' ); ?></p>
			</div>
		</div>
		<div class="fm-webhook-capture-button">
			<a href="javascript:void(0);" class="btn btn-primary flowmattic-button flowmattic-api-poll-button">
				<#
				if ( 'undefined' !== typeof capturedData ) {
					#>
					<?php echo esc_attr__( 'Re-capture response', 'flowmattic' ); ?>
					<#
				} else {
					#>
					<?php echo esc_attr__( 'Save & Capture response', 'flowmattic' ); ?>
					<#
				}
				#>
			</a>
		</div>
		<div class="fm-webhook-capture-data fm-response-capture-data">
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-application-notion-action-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group data-auth_option data-auth_connect w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="workflow-api-connect form-control w-100 d-block" required title="Choose Connect" data-live-search="true">
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
		<div class="notion-action-data"></div>
	</div>
</script>
<script type="text/html" id="notion-action-create_database_item-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Database ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control database_id_input" name="database_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.database_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-databases btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Content</h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" name="content"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Add Attachments as Embeded Block?', 'flowmattic' ); ?></h4>
			<div class="d-flex w-100">
				<select name="embed_attachments" class="form-control custom-select w-100">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.embed_attachments ) { #>selected<# } #> value="no">No</option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.embed_attachments ) { #>selected<# } #> value="yes">Yes</option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p><?php echo esc_attr__( 'Whenever possible, like for images, PDFs, should FlowMattic add attachments in the properties to the content as embeded block?', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<div class="flowmattic-notion-action-fields"></div>
</script>
<script type="text/html" id="notion-action-update_database_item-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Database ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control database_id_input" name="database_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.database_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-databases btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Item', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control item_id_input" name="item_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.item_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-items btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Content</h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" name="content"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Add Attachments as Embeded Block?', 'flowmattic' ); ?></h4>
			<div class="d-flex w-100">
				<select name="embed_attachments" class="form-control custom-select w-100">
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.embed_attachments ) { #>selected<# } #> value="no">No</option>
					<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.embed_attachments ) { #>selected<# } #> value="yes">Yes</option>
				</select>
			</div>
			<div class="fm-application-instructions">
				<p><?php echo esc_attr__( 'Whenever possible, like for images, PDFs, should FlowMattic add attachments in the properties to the content as embeded block?', 'flowmattic' ); ?>
			</div>
		</div>
	</div>
	<div class="flowmattic-notion-action-fields"></div>
</script>

<script type="text/html" id="notion-action-get_database_details-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Database ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control database_id_input" name="database_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.database_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-databases btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="notion-action-create_page-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Parent Page', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control parent_page_id_input" name="parent_page_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.parent_page_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-parent-pages btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">New Page Title</h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" name="page_title"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.page_title ) { #>{{{ actionAppArgs.page_title }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Content</h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" name="content"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.content ) { #>{{{ actionAppArgs.content }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="notion-action-find_page_by_title-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group">
			<h4 class="fm-input-title">Page Title <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" required name="page_title"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.page_title ) { #>{{{ actionAppArgs.page_title }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>If you don't see your expected page, please check that it is shared with the same integration that you're using to authenticate with. The last edited matched page will be returned.</p>
		</div>
	</div>
</script>
<script type="text/html" id="notion-action-find_database_item-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Database ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control database_id_input" name="database_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.database_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-databases btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Item Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control item_name_input" name="item_name" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.item_name }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
			</div>
			<div class="fm-application-instructions">
				<p>The last edited page with exact match will be returned.</p>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="notion-action-create_comment-data-template">
	<div class="flowmattic-notion-data-form">
		<div class="form-group w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Page to Comment', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="d-flex w-100">
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" required class="dynamic-field-input w-100 form-control parent_page_id_input" name="parent_page_id" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.parent_page_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-parent-pages btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="form-group">
			<h4 class="fm-input-title">Comment Text</h4>
			<div class="fm-dynamic-input-field">
				<textarea rows="1" class="fm-textarea form-control dynamic-field-input w-100 text-to-convert" name="comment_text"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.comment_text ) { #>{{{ actionAppArgs.comment_text }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-notion-dropdown-template">
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
<script type="text/html" id="flowmattic-notion-fields-data-template">
	<div class="notion-fields-data w-100">
	<fieldset class="px-3 border border-1 pb-2 mb-3">
		<legend class="fs-6 text-muted" style="font-weight: 500; width: auto;padding: 0 10px;float: none;">{{{ modificationTemplate }}}</legend>
		<#
		var fieldIDs = {};
		if ( 'undefined' !== typeof templateFields ) {
			fieldIDs = Object.keys(templateFields).reduce((acc, key) => {
				acc[key] = templateFields[key]['name'].toLowerCase().replace( ' ', '_' ); // or any other transformation
				return acc;
			}, {});

			fieldIDs = Object.values( fieldIDs ).join(',');

			_.each( templateFields, function( field, i ) {
				var fieldID = field.name.toLowerCase().replace( ' ', '_' ),
					fieldName = field.name,
					fieldType = field.type,
					placeholder = field.placeholder,
					fieldValue = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs['variable_field_' + fieldID ] ) ? actionAppArgs['variable_field_' + fieldID ] : '';

				if ( 'created_time' === fieldType || 'last_edited_time' === fieldType || 'created_by' === fieldType || 'last_edited_by' === fieldType ) {
					// Do nothing.
				} else if ( 'select' === fieldType ) {
					var selectOptions = field.select.options;
					#>
					<div class="form-group w-100">
						<h4 class="fm-input-title">{{{ fieldName }}}</h4>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_name" value="{{{ fieldName }}}"/>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_type" value="{{{ fieldType }}}"/>
						<div class="d-flex w-100">
							<select name="variable_field_{{{ fieldID }}}" class="form-control custom-select w-100" title="Choose {{{ fieldName }}}">
								<#
								_.each( selectOptions, function( option, i ) {
									var optionValue = option.name,
										optionId = option.id;
									#>
									<option <# if ( 'undefined' !== typeof actionAppArgs && optionValue === actionAppArgs[ 'variable_field_' + fieldID ] ) { #>selected<# } #> value="{{{ optionValue }}}">{{{ optionValue }}}</option>
									<#
								} );
								#>
							</select>
						</div>
					</div>
					<#
				} else if ( 'people' === fieldType ) {
					#>
					<div class="form-group w-100">
						<h4 class="fm-input-title">{{{ fieldName }}}</h4>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_name" value="{{{ fieldName }}}"/>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_type" value="{{{ fieldType }}}"/>
						<div class="d-flex w-100">
							<div class="fm-dynamic-input-field w-100">
								<textarea name="variable_field_{{{ fieldID }}}" class="fm-textarea dynamic-field-input form-control fm-col-value-input w-100 people_id_input" rows="1" placeholder="{{{ placeholder }}}">{{{ fieldValue }}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<div class="refresh-notion-people btn btn-refresh btn-outline-secondary">
								<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
							</div>
						</div>
					</div>
					<#
				} else {
					#>
					<div class="form-group w-100">
						<h4 class="fm-input-title">{{{ fieldName }}}</h4>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_name" value="{{{ fieldName }}}"/>
						<input class="fm-col-key-input form-control w-100" type="hidden" name="variable_field_{{{ fieldID }}}_type" value="{{{ fieldType }}}"/>
						<div class="fm-dynamic-input-field">
							<textarea name="variable_field_{{{ fieldID }}}" class="fm-textarea dynamic-field-input form-control fm-col-value-input w-100" rows="1" placeholder="{{{ placeholder }}}">{{{ fieldValue }}}</textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
					</div>
					<#
				}
			} );
		}
		#>
	</fieldset>
	</div>
	<input class="form-control" type="hidden" value="{{{ fieldIDs }}}" name="notion_template_fields_{{{ templateID }}}" readonly />
</script>
