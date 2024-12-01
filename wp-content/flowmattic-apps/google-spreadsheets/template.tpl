<?php
/**
 * Underscore.js template
 *
 * @package FlowMattic
 * @since 1.0
 */
?>
<script type="text/html" id="flowmattic-google-spreadsheets-trigger-data-template">
	<div class="flowmattic-google-spreadsheets-trigger-form-data">
		<div class="google-spreadsheets-trigger-data">
			<div class="form-group w-100">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-select-field">
					<select name="connect_id" class="workflow-api-connect google-spreadsheets-connect form-control w-100 d-block" required title="Choose Connect" data-live-search="true">
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
			<div class="form-group w-100 trigger-sheet-id d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Spreadsheet', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="d-flex w-100">
						<div class="w-100">
							<select class="w-100 form-control trigger_sheet_id_input" data-live-search="true" required name="trigger_sheet_id" title="Choose spreadsheet to monitor"></select>
						</div>
						<div class="refresh-trigger-sheets btn btn-refresh btn-outline-secondary">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
						</div>
					</div>
					<div class="fm-application-instructions">
						<p>Select a Spreadsheet to monitor for this workflow.</p>
					</div>
				</div>
			</div>
			<div class="form-group w-100 trigger-spreadsheet-sheet-id d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Sheet', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="d-flex w-100">
						<div class="w-100">
							<select class="w-100 form-control trigger_spreadsheet_sheet_id_input" data-live-search="true" required name="trigger_spreadsheet_sheet_id" title="Choose sheet to monitor"></select>
						</div>
						<div class="refresh-trigger-spreadsheet-sheets btn btn-refresh btn-outline-secondary">
							<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
						</div>
					</div>
					<div class="fm-application-instructions">
						<p>Select a Sheet to monitor for this workflow.</p>
					</div>
				</div>
			</div>
			<div class="form-group w-100 trigger-cell-id d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Cell ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input type="text" class="form-control dynamic-field-input w-100" name="trigger_cell_id" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof trigger_cell_id ) { #>{{{ trigger_cell_id }}}<# } #>">
				</div>
				<div class="fm-application-instructions">
					<p>Enter the cell ID to monitor for changes. Eg: A1, B2, C3, etc.</p>
				</div>
			</div>
			<div class="form-group w-100 trigger-column-id d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Column ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
				<div class="fm-dynamic-input-field">
					<input type="text" class="form-control dynamic-field-input w-100" name="trigger_column_id" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof trigger_column_id ) { #>{{{ trigger_column_id }}}<# } #>">
				</div>
				<div class="fm-application-instructions">
					<p>Enter the column ID to monitor for changes. Eg: A, B, C, etc.</p>
				</div>
			</div>
			<div class="form-group w-100 trigger-after-column-id d-none">
				<h4 class="fm-input-title"><?php esc_attr_e( 'Trigger After Column ID', 'flowmattic' ); ?></h4>
				<div class="fm-dynamic-input-field">
					<input type="text" class="form-control dynamic-field-input w-100" name="trigger_after_column_id" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof trigger_after_column_id ) { #>{{{ trigger_after_column_id }}}<# } #>">
				</div>
				<div class="fm-application-instructions">
					<p>Enter the column ID after which the trigger should be activated. Eg: A, B, C, etc. If column provided, trigger will be activated only after the column value is detected. Leave empty to trigger on all changes.</p>
				</div>
			</div>
			<div class="form-group w-100">
				<div class="fm-application-connect mt-1">
					<#
					if ( '' !== validate_api_response ) {
						#>
						<a href="javascript:void(0);" class="btn btn-outline-success flowmattic-connect-google-spreadsheets-button">
							<?php echo esc_attr__( 'Connected to Google Sheet', 'flowmattic' ); ?>
						</a>
						<#
					} else {
						#>
						<a href="javascript:void(0);" class="btn btn-success flowmattic-button flowmattic-connect-google-spreadsheets-button">
							<?php echo esc_attr__( 'Connect to Google Sheet', 'flowmattic' ); ?>
						</a>
						<#
					}
					#>
					<div class="fm-application-instructions">
						<p>Reconnect to Google Sheet if you want to change the spreadsheet or the account, or you're not receiving alerts in case the connection is expired.</p>
					</div>
				</div>
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
<script type="text/html" id="flowmattic-application-google-spreadsheets-data-template">
	<div class="flowmattic-google-spreadsheets-form-data">
		<?php
		$access_token = '';

		if ( isset( $_GET['workflow-id'] ) ) {
			// Get the authentication data.
			$workflow_id      = $_GET['workflow-id'];
			$google_auth_data = flowmattic_get_auth_data( 'google_spreadsheets', $workflow_id );
		}

		$button_title = __( 'Sign in with Google' );
		$button_class = 'btn-primary flowmattic-button';

		$google_spreadsheets = array();

		if ( isset( $google_auth_data['auth_data']['access_token'] ) ) {
			$access_token        = $google_auth_data['auth_data']['access_token'];
			$google_spreadsheets = get_transient( 'flowmattic-google-spreadsheets-' . $workflow_id );

			$button_title = __( 'Connected to Google' );
			$button_class = 'btn-outline-primary';

			if ( false === $google_spreadsheets ) {
				$args = array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $access_token,
						'User-Agent'    => 'FlowMattic',
					),
					'timeout' => 20,
				);

				// Creates a new spreadsheet.
				// $request = wp_remote_post( 'https://sheets.googleapis.com/v4/spreadsheets/', $args );

				// Get all available spreadsheets.
				$request_url_param = urlencode( '"application/vnd.google-apps.spreadsheet"' );
				$request = wp_remote_get( 'https://www.googleapis.com/drive/v3/files?q=mimeType=' . $request_url_param, $args );
				$request = wp_remote_retrieve_body( $request );
				$google_spreadsheets = json_decode( $request, true );

				set_transient( 'flowmattic-google-spreadsheets-' . $workflow_id, $google_spreadsheets, HOUR_IN_SECONDS * 2 );
			}
		}
		?>
		<div class="form-group fm-api-authentication w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Authentication Type', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<select name="authType" class="workflow-api-authentication-type form-control w-100" required title="Select authentication type">
				<#
				var authenticationTypes = {
					traditional: '<?php esc_attr_e( 'Traditional', 'flowmattic' ); ?>',
					connect: '<?php esc_attr_e( 'FlowMattic Connect', 'flowmattic' ); ?>',
				}
				_.each( authenticationTypes, function( title, type ) {
					#>
					<option <# if ( 'undefined' !== typeof actionAppArgs && actionAppArgs.authType === type ) { #>selected<# } #> value="{{{ type }}}">{{{ title }}}</option>
					<#
				} )
				#>
			</select>
		</div>
		<div class="form-group google-spreadsheets w-100 data-auth_option data-auth_traditional">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Authenticate Google Spreadsheet', 'flowmattic' ); ?></h4>
			<input type="hidden" class="webhook-url-input w-100" readonly value="{{{webhookURL}}}" />
			<input type="hidden" class="google-access-token w-100" readonly value="<?php echo esc_attr( $access_token ); ?>" />
			<a href="javascript:void(0);" class="btn <?php echo esc_attr( $button_class ); ?> flowmattic-spreadsheets-connect-button p-0 pe-2" style="background-color: #4285F4; color: #fff;">
				<img src="<?php echo esc_attr( FLOWMATTIC_APP_URL . '/google-spreadsheets/g-logo.svg' ); ?>" style="width: 48px;" class="pe-2">
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
		<div class="form-group w-100 data-auth_option data-auth_connect">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Choose Connect Account', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
			<div class="fm-dynamic-select-field">
				<select name="connect_id" class="workflow-api-connect form-control w-100 d-block" title="Choose Connect" data-live-search="true">
					<#
					let connectID = ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.connect_id ) ? actionAppArgs.connect_id : '';
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
		<div class="form-group spreadsheet-select w-100">
			<div class="fm-select-spreadsheet">
				<h4 class="fm-input-title">
					<?php esc_attr_e( 'Google Spreadsheet ID', 'flowmattic' ); ?>
					<span class="badge outline bg-danger">Required</span>
				</h4>
				<div class="d-flex">
					<div class="d-none">
						<selectGroup name="google-spreadsheet" class="google-spreadsheet-select w-100">
							<?php
							if ( isset( $google_spreadsheets['files'] ) ) {
								foreach ( $google_spreadsheets['files'] as $index => $spreadsheet ) {
								$spreadsheet_name = str_replace( array( '{{', '}}', ), '', $spreadsheet['name'] );
									?>
									<option
										value="<?php echo esc_attr( $spreadsheet["id"] ); ?>"
										data-subtext="ID: <?php echo esc_attr( $spreadsheet["id"] ); ?>">
										<?php echo esc_attr( $spreadsheet_name ); ?>
									</option>
									<?php
								}
							}
							?>
						</selectGroup>
					</div>
					<div class="fm-dynamic-input-field w-100">
						<input autocomplete="off" type="search" class="dynamic-field-input google-spreadsheet-id-input w-100 form-control" name="spreadsheet_id" required value="<# if ( 'undefined' !== typeof spreadsheetID ) { #>{{{ spreadsheetID }}}<# } #>">
						<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
					</div>
					<div class="refresh-spreadsheets btn btn-refresh btn-outline-secondary">
						<svg width="16" height="16" viewBox="0 0 24 24" fill="#3f3f3f" xmlns="http://www.w3.org/2000/svg" data-reactroot=""><path d="M21.7545 14.2243C21.8784 13.6861 21.5425 13.1494 21.0043 13.0255C20.4661 12.9016 19.9293 13.2374 19.8054 13.7757C19.2633 16.1307 17.6891 18.0885 15.5897 19.1471C14.5148 19.6889 13.2886 20 11.9999 20C9.44375 20 7.49521 18.8312 5.64918 16.765L8.41421 14H2V20.4142L4.23319 18.181C6.29347 20.4573 8.71647 22 11.9999 22C13.6113 22 15.145 21.611 16.4901 20.9329L16.4902 20.9329C19.1108 19.6115 21.0766 17.1692 21.7545 14.2243Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path><path d="M2.24553 9.77546C2.12164 10.3137 2.45752 10.8504 2.99573 10.9743C3.53394 11.0982 4.07067 10.7623 4.19456 10.2241C4.73668 7.86902 6.31094 5.91126 8.41029 4.85269C9.48518 4.31081 10.7114 3.99978 12.0001 3.99978C14.5563 3.99978 16.5048 5.16858 18.3508 7.23472L15.5858 9.99976H22V3.58554L19.7668 5.81873C17.7065 3.54248 15.2835 1.99978 12.0001 1.99978C10.3887 1.99978 8.85498 2.38873 7.50989 3.06683L7.50981 3.06687C4.88916 4.3883 2.92342 6.83054 2.24553 9.77546Z" clip-rule="evenodd" fill-rule="evenodd" undefined="1"></path></svg>
					</div>
				</div>
			</div>
		</div>
		<div class="fm-google-spreadsheet-data w-100"></div>
		<div class="fm-spreadsheet-fields w-100"></div>
		<div class="fm-spreadsheet-rows w-100"></div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-sheets-data-template">
<div class="form-group spreadsheet-sheet-select w-100 m-t-20">
	<div class="fm-select-spreadsheet-sheet">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Select Sheet', 'flowmattic' ); ?></h4>
		<div class="d-flex">
			<select name="google-spreadsheet-sheet" class="google-spreadsheet-sheet-select w-100" title="Select Sheet" data-live-search="true">
				<#
				if ( 'undefined' !== typeof sheets ) {
					_.each( sheets, function( sheet, index ) {
						#>
						<option
							<# if ( 'undefined' !== typeof sheetID && sheet.sheetID === sheetID ) { #>selected<# } #>
							value="{{{ sheet.sheetID }}}"
							data-subtext="ID: {{{ sheet.sheetID }}}">
							{{{ sheet.title }}}
						</option>
						<#
					} );
				}
				#>
			</select>
		</div>
	</div>
</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-row-data-template">
<div class="fm-row-data-wrapper">
	<div class="form-group spreadsheet-sheet-row-data w-100">
		<div class="fm-sheet-row-data fm-row-mapping-data no-padding">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Map Row Data', 'flowmattic' ); ?></h4>
			<table class="fm-row-map-data-table w-100">
				<thead>
					<tr>
						<th class="w-50">Column</th>
						<th class="w-50">Value</th>
					</tr>
				</thead>
				<tbody>
				<#
				_.each( values, function( value, key ) {
					var colKey = value,
						colValue = ( 'undefined' !== typeof columnMapping ) ? columnMapping[ colKey ] : '';
					#>
					<tr>
						<td>
							<input class="fm-col-key-input w-100" type="text" value="{{{ colKey }}}" readonly />
						</td>
						<td>
							<div class="fm-dynamic-input-field w-100">
								<textarea name="col-value-<?php echo flowmattic_random_string(); ?>" type="text" class="dynamic-field-input fm-col-value-input w-100" rows="1">{{{ colValue }}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
						</td>
					<#
				} );
				#>
				</tbody>
			</table>
		</div>
	</div>
</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-row-dynamic-data-template">
<div class="spreadsheet-sheet-row-dynamic-data w-100">
	<div class="fm-sheet-row-dynamic-data no-padding">
		<div class="form-group dynamic-inputs w-100">
			<h4 class="fm-input-title"><?php esc_attr_e( 'Map Row Data', 'flowmattic' ); ?></h4>
			<div class="fm-custom-fields-body data-dynamic-fields m-t-20" data-field-name="customColumnMapping">
				<#
				if ( 'undefined' !== typeof customColumnMapping && ! _.isEmpty( customColumnMapping ) && 'false' !== customColumnMapping ) {
					_.each( customColumnMapping, function( value, key ) {
						#>
						<div class="fm-dynamic-input-wrap fm-custom-fields d-flex">
							<div class="fm-dynamic-input-field w-100">
								<input class="fm-dynamic-inputs w-100" name="dynamic-field-key[]" type="hidden" placeholder="column" value="{{{key}}}" />
								<textarea class="fm-dynamic-inputs fm-textarea dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value">{{{value}}}</textarea>
								<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
							</div>
							<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter" style="width: auto;">
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
					<div class="fm-dynamic-input-wrap fm-custom-fields d-flex">
						<div class="fm-dynamic-input-field w-100">
							<input class="fm-dynamic-inputs w-100" autocomplete="off" name="dynamic-field-key[]" type="hidden" placeholder="column" value="" />
							<textarea class="fm-dynamic-inputs fm-textarea dynamic-field-input w-100" name="dynamic-field-value[]" rows="1" placeholder="value"></textarea>
							<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
						</div>
						<a href="javascript:void(0);" class="dynamic-input-remove btn-remove-parameter" style="width: auto;">
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
</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-update_row-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Row Number', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="row_number" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.row_number ) { #>{{{ actionAppArgs.row_number }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-lookup_row-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Search Text', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="search_text" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.search_text ) { #>{{{ actionAppArgs.search_text }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter value to be searched. We will get all the rows matching the value along with their column name and number.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-get_cell_data-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Cell ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="cell_id" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cell_id ) { #>{{{ actionAppArgs.cell_id }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Cell ID to get the data. E.g C15.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-update_cell_data-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Cell ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="cell_id" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cell_id ) { #>{{{ actionAppArgs.cell_id }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Cell ID to update the data. E.g C15.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Cell Data', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" required name="cell_data" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.cell_data ) { #>{{{ actionAppArgs.cell_data }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the data to be updated in the above cell.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-create_column-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Column Name', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="column_name" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.column_name ) { #>{{{ actionAppArgs.column_name }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Column name to be created in the above column ID as column header.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Column Index', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="column_index" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.column_index ) { #>{{{ actionAppArgs.column_index }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>The column position (zero-based) to insert. If not set, append to the right by default.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-new_sheet-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Sheet Title', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="sheet_title" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.sheet_title ) { #>{{{ actionAppArgs.sheet_title }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Sheet title to be created in the above spreadsheet.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Headers', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" name="sheet_headers" rows="1"><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.sheet_headers ) { #>{{{ actionAppArgs.sheet_headers }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the headers for the new sheet. Separate headers with a comma.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Overwrite Existing Sheet', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-select-field">
			<select name="overwrite_sheet" class="form-control dynamic-field-input w-100">
				<option value="no" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.overwrite_sheet && 'no' === actionAppArgs.overwrite_sheet ) { #>selected<# } #>>No</option>
				<option value="yes" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.overwrite_sheet && 'yes' === actionAppArgs.overwrite_sheet ) { #>selected<# } #>>Yes</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Choose whether to overwrite the existing sheet or not. If a worksheet with the specified title exists, its content would be lost. Please, use with caution.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-import_csv_json-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'CSV OR JSON Data', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<textarea class="w-100 fm-dynamic-inputs form-control dynamic-field-input fm-textarea" name="csv_data" rows="1" required><# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.csv_data ) { #>{{{ actionAppArgs.csv_data }}}<# } #></textarea>
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Use CSV Parser module before this step, and choose the data from response. OR Enter the data in JSON format.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Header Row', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-select-field">
			<select name="header_row" class="form-control dynamic-field-input w-100">
				<option value="no" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.header_row && 'no' === actionAppArgs.header_row ) { #>selected<# } #>>No</option>
				<option value="yes" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.header_row && 'yes' === actionAppArgs.header_row ) { #>selected<# } #>>Yes</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Choose whether the CSV or JSON data has headers or not. First item will be considered as headers if selected 'Yes'.</p>
		</div>
	</div>
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Import Type', 'flowmattic' ); ?></h4>
		<div class="fm-dynamic-select-field">
			<select name="import_type" class="form-control dynamic-field-input w-100">
				<option value="append" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.import_type && 'append' === actionAppArgs.import_type ) { #>selected<# } #>>Append</option>
				<option value="overwrite" <# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.import_type && 'overwrite' === actionAppArgs.import_type ) { #>selected<# } #>>Overwrite</option>
			</select>
		</div>
		<div class="fm-application-instructions">
			<p>Choose whether to append the data to the existing sheet or overwrite the existing data.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-get_row_data-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Range', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="range" autocomplete="off" type="search" required value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.range ) { #>{{{ actionAppArgs.range }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the range of cells to get the data. E.g A2:F5. If you want to get data for specific column, enter the column name. E.g A2:A5.</p>
		</div>
	</div>
</script>
<script type="text/html" id="flowmattic-google-spreadsheets-copy_sheet-data-template">
	<div class="form-group w-100">
		<h4 class="fm-input-title"><?php esc_attr_e( 'Destination Spreadsheet ID', 'flowmattic' ); ?> <span class="badge outline bg-danger">Required</span></h4>
		<div class="fm-dynamic-input-field">
			<input type="text" class="form-control dynamic-field-input w-100" name="destination_spreadsheet_id" autocomplete="off" type="search" value="<# if ( 'undefined' !== typeof actionAppArgs && 'undefined' !== typeof actionAppArgs.destination_spreadsheet_id ) { #>{{{ actionAppArgs.destination_spreadsheet_id }}}<# } #>">
			<span class="dynamic-field-button dashicons dashicons-database" title="Replace with captured data"></span>
		</div>
		<div class="fm-application-instructions">
			<p>Enter the destination spreadsheet ID where you want to copy the sheet. Leave it blank to copy the sheet in the same spreadsheet.</p>
		</div>
	</div>
</script>