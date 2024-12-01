<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-application-google-drive-data-template">
	<?php
	$access_token     = '';
	$google_auth_data = array();

	if ( isset( $_GET['workflow-id'] ) ) {
		// Get the authentication data.
		$workflow_id      = $_GET['workflow-id'];
		$google_auth_data = flowmattic_get_auth_data( 'google_drive', $workflow_id );
	}

	$button_title = __( 'Sign in with Google' );
	$button_class = 'btn-primary flowmattic-button';

	$google_drive_folders = array();

	if ( isset( $google_auth_data['auth_data']['access_token'] ) ) {
		$access_token        = $google_auth_data['auth_data']['access_token'];
		$google_drive_folders = get_transient( 'flowmattic-google-drive-folders' );

		$button_title = __( 'Connected to Google' );
		$button_class = 'btn-outline-primary';

		if ( false === $google_drive_folders ) {
			$args = array(
				'headers' => array(
					'Authorization' => 'Bearer ' . $access_token,
					'User-Agent'    => 'FlowMattic',
				),
				'timeout' => 20,
			);

			// Get all available drive folders.
			$request_url_param = urlencode( '"application/vnd.google-apps.folder" and "root" in parents' );
			$request = wp_remote_get( 'https://www.googleapis.com/drive/v3/files?q=mimeType=' . $request_url_param, $args );
			$request = wp_remote_retrieve_body( $request );
			$google_drive_folders = json_decode( $request, true );

			set_transient( 'flowmattic-google-drive-folders', $google_drive_folders, HOUR_IN_SECONDS * 2 );
		}
	}
	?>
	<div class="flowmattic-google-drive-form-data">
		<div class="form-group google-drive w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Authenticate Google Drive', 'flowmattic' ); ?></h4>
			<input type="hidden" class="webhook-url-input w-100" readonly value="{{{webhookURL}}}" />
			<input type="hidden" class="google-access-token w-100" readonly value="<?php echo esc_attr( $access_token ); ?>" />
			<a href="javascript:void(0);" class="btn <?php echo esc_attr( $button_class ); ?> flowmattic-drive-connect-button p-0 pe-2" style="background-color: #4285F4; color: #fff;">
				<img src="<?php echo esc_attr( FLOWMATTIC_APP_URL . '/google-drive/g-logo.svg' ); ?>" style="width: 48px;" class="pe-2">
				<span class="google-button-text">
					<?php
						echo esc_attr( $button_title );
					?>
				</span>
			</a>
			<div class="fm-application-instructions">
				<p>
					<?php
					if ( '' !== $access_token ) {
						?>
						{{{ otherActionApps[ application ].connect_note }}}
						<?php
					} else {
						?>
						{{{ otherActionApps[ application ].instructions }}}
						<?php
					}
					?>
				</p>
			</div>
		</div>
		<div class="fm-google-drive-action-data"></div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-folder-template">
	<div class="form-group drive-select w-100">
		<div class="fm-select-drive">
			<h4 class="fm-input-title">
				<?php esc_attr_e( 'Google Drive Folder ID', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="d-flex">
				<div class="d-none">
					<selectGroup name="google-drive" class="google-drive-select w-100" title="Select Drive Folder">
						<?php
						if ( isset( $google_drive_folders['files'] ) ) {
							foreach ( $google_drive_folders['files'] as $index => $drive_folder ) {
								?>
								<option
									<# if ( 'undefined' !== typeof driveFolderID && '<?php echo esc_attr( $drive_folder["id"] ); ?>' === driveFolderID ) { #>selected<# } #>
									value="<?php echo esc_attr( $drive_folder["id"] ); ?>"
									data-subtext="ID: <?php echo esc_attr( $drive_folder["id"] ); ?>">
									<?php echo esc_attr( $drive_folder['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</selectGroup>
				</div>
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="folder_id" required value="<# if ( 'undefined' !== typeof driveFolderID ) { #>{{{ driveFolderID }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-drive-folders btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
		<div class="fm-google-drive-data w-100"></div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-upload_file-action-template">
	<div class="fm-application-google-drive-data">
		<div class="form-group w-100">
			<h4 class="input-title"><?php echo esc_attr__( 'File Details', 'flowmattic' ); ?></h4>
			<div class="form-row">
				<h5>File URL <span class="badge outline bg-danger">Required</span></h5>
				<div class="fm-dynamic-input-field">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="file_url" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.file_url }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="fm-application-instructions pt-2">
					<p class="description">
						<?php echo sprintf( __( 'Public url of the file to be uploaded. Max. size limi: 50MB. Allowed file formats are <a href="%s" target="_blank">listed here</a>', 'flowmattic' ), 'https://support.google.com/drive/answer/37603?hl=en' ); ?>
					</p>
				</div>
			</div>
			<div class="row form-row">
				<div class="form-row m-t-20">
					<h5>File Name</h5>
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="file_name" value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.file_name }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<div class="fm-application-instructions pt-2">
						<p class="description">
							<?php esc_html_e( 'Enter file name to be used to save this file as, in your Google Drive. Leave empty to use the same file name from the file URL.', 'flowmattic' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-create_folder-action-template">
	<div class="fm-application-google-drive-data">
		<div class="form-group w-100">
			<h4 class="input-title">
				<?php echo esc_attr__( 'Folder Name', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="row form-row">
				<div class="form-row">
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="folder_name" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.folder_name }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<div class="fm-application-instructions pt-2">
						<p class="description">
							<?php esc_html_e( 'Enter name of the folder to be created in your Google Drive.', 'flowmattic' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-create_sub_folder-action-template">
	<div class="form-group drive-select w-100">
		<div class="fm-select-drive">
			<h4 class="fm-input-title">
				<?php esc_attr_e( 'Parent Folder ID', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="d-flex">
				<div class="d-none">
					<selectGroup name="google-drive" class="google-drive-select w-100" title="Select Drive Folder">
						<?php
						if ( isset( $google_drive_folders['files'] ) ) {
							foreach ( $google_drive_folders['files'] as $index => $drive_folder ) {
								?>
								<option
									<# if ( 'undefined' !== typeof driveFolderID && '<?php echo esc_attr( $drive_folder["id"] ); ?>' === driveFolderID ) { #>selected<# } #>
									value="<?php echo esc_attr( $drive_folder["id"] ); ?>"
									data-subtext="ID: <?php echo esc_attr( $drive_folder["id"] ); ?>">
									<?php echo esc_attr( $drive_folder['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</selectGroup>
				</div>
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="folder_id" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.folder_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-drive-folders btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
	</div>
	<div class="fm-application-google-drive-data">
		<div class="form-group w-100">
			<h4 class="input-title">
				<?php echo esc_attr__( 'Sub Folder Name', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="row form-row">
				<div class="form-row">
					<div class="fm-dynamic-input-field">
						<input class="dynamic-field-input w-100 form-control" autocomplete="off" type="search" name="folder_name" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.folder_name }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<div class="fm-application-instructions pt-2">
						<p class="description">
							<?php esc_html_e( 'Enter name of the sub-folder to be created in your Google Drive.', 'flowmattic' ); ?>
						</p>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-share_folder-action-template">
	<div class="form-group drive-select w-100">
		<div class="fm-select-drive">
			<h4 class="fm-input-title">
				<?php esc_attr_e( 'Folder ID to Share', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="d-flex">
				<div class="d-none">
					<selectGroup name="google-drive" class="google-drive-select w-100" title="Select Drive Folder">
						<?php
						if ( isset( $google_drive_folders['files'] ) ) {
							foreach ( $google_drive_folders['files'] as $index => $drive_folder ) {
								?>
								<option
									<# if ( 'undefined' !== typeof driveFolderID && '<?php echo esc_attr( $drive_folder["id"] ); ?>' === driveFolderID ) { #>selected<# } #>
									value="<?php echo esc_attr( $drive_folder["id"] ); ?>"
									data-subtext="ID: <?php echo esc_attr( $drive_folder["id"] ); ?>">
									<?php echo esc_attr( $drive_folder['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</selectGroup>
				</div>
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="folder_id" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.folder_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-drive-folders btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Email', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-textarea dynamic-field-input form-control" name="email" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email ) { #>{{{ actionAppArgs.email }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter google email id to which the access will be granted.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title" class="col-form-label">Role</h4>
		<div class="d-flex">
			<select name="role" class="form-control custom-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'reader' === actionAppArgs.role ) { #>selected<# } #> value="reader">Can View</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'writer' === actionAppArgs.role ) { #>selected<# } #> value="writer">Can Write</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'commenter' === actionAppArgs.role ) { #>selected<# } #> value="commenter">Can Comment</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select access level for this shared folder.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title" class="col-form-label">Notify User via Email?</h4>
		<div class="d-flex">
			<select name="notify_user" class="form-control custom-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.notify_user ) { #>selected<# } #> value="yes">Yes</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.notify_user ) { #>selected<# } #> value="no">No</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select whether to notify the user via email or not.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-share_folder_with_anyone-action-template">
	<div class="form-group drive-select w-100">
		<div class="fm-select-drive">
			<h4 class="fm-input-title">
				<?php esc_attr_e( 'Folder ID to Share', 'flowmattic' ); ?>
				<span class="badge outline bg-danger">Required</span>
			</h4>
			<div class="d-flex">
				<div class="d-none">
					<selectGroup name="google-drive" class="google-drive-select w-100" title="Select Drive Folder">
						<?php
						if ( isset( $google_drive_folders['files'] ) ) {
							foreach ( $google_drive_folders['files'] as $index => $drive_folder ) {
								?>
								<option
									<# if ( 'undefined' !== typeof driveFolderID && '<?php echo esc_attr( $drive_folder["id"] ); ?>' === driveFolderID ) { #>selected<# } #>
									value="<?php echo esc_attr( $drive_folder["id"] ); ?>"
									data-subtext="ID: <?php echo esc_attr( $drive_folder["id"] ); ?>">
									<?php echo esc_attr( $drive_folder['name'] ); ?>
								</option>
								<?php
							}
						}
						?>
					</selectGroup>
				</div>
				<div class="fm-dynamic-input-field w-100">
					<input autocomplete="off" type="search" class="dynamic-field-input w-100 form-control" name="folder_id" required value="<# if ( 'undefined' !== typeof actionAppArgs ) { #>{{{ actionAppArgs.folder_id }}}<# } #>">
					<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
				</div>
				<div class="refresh-drive-folders btn btn-refresh btn-outline-secondary">
					<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
				</div>
			</div>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-share_file-action-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title">
			<?php esc_attr_e( 'File ID to Share', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span>
		</h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-textarea dynamic-field-input form-control" name="file_id" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.file_id ) { #>{{{ actionAppArgs.file_id }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter file ID here. You can get the ID from the Google Drive file URL E.g. https://drive.google.com/file/d/14TCiNoXXXXXX.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Email', 'flowmattic' ); ?></h4>
		<div class="fm-form-control">
			<div class="fm-dynamic-input-field">
				<textarea class="w-100 fm-textarea dynamic-field-input form-control" name="email" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.email ) { #>{{{ actionAppArgs.email }}}<# } #></textarea>
				<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
			</div>
		</div>
		<div class="fm-application-instructions">
			<p>Enter google email id to which the access will be granted.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title" class="col-form-label">Role</h4>
		<div class="d-flex">
			<select name="role" class="form-control custom-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'reader' === actionAppArgs.role ) { #>selected<# } #> value="reader">Can View</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'writer' === actionAppArgs.role ) { #>selected<# } #> value="writer">Can Write</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'commenter' === actionAppArgs.role ) { #>selected<# } #> value="commenter">Can Comment</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select access level for this shared folder.</p>
		</div>
	</div>
	<div class="form-group">
		<h4 class="fm-input-title" class="col-form-label">Notify User via Email?</h4>
		<div class="d-flex">
			<select name="notify_user" class="form-control custom-select w-100">
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'yes' === actionAppArgs.notify_user ) { #>selected<# } #> value="yes">Yes</option>
				<option <# if ( 'undefined' !== typeof actionAppArgs && 'no' === actionAppArgs.notify_user ) { #>selected<# } #> value="no">No</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Select whether to notify the user via email or not.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-drive-share_file_with_anyone-action-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title">
			<?php esc_attr_e( 'File ID to Share', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span>
		</h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-textarea dynamic-field-input form-control" name="file_id" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.file_id ) { #>{{{ actionAppArgs.file_id }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter file ID here. You can get the ID from the Google Drive file URL E.g. https://drive.google.com/file/d/14TCiNoXXXXXX.</p>
		</div>
	</div>
</script>