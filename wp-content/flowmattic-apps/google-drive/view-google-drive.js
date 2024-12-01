/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger google-drive View.
		FlowMatticWorkflow.Google_DriveView = FlowMatticWorkflow.ActionView.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-google-drive-data-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
				'click .flowmattic-drive-connect-button': 'connectGoogleAccount',
				'click .refresh-drive-folders': 'refreshDriveFolderList',
				'change .form-control[name="folder_id"]': 'updateDriveFolder',
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Listen to the authData capture event.
				this.listenTo( FlowMatticWorkflowEvents, 'authDataCaptured', this.saveAuthData );

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );
			},

			generateDynamicFieldsHTML: function( application, currentInput ) {
				var googleDriveFolders = '',
					googleDriveFoldersOptions = jQuery( this.$el ).find( 'selectGroup[name="google-drive"]' ).html();

				if ( 'undefined' !== typeof googleDriveFoldersOptions && '' !== googleDriveFoldersOptions.trim() ) {
					if ( application === this.model.get( 'application' ) && 'folder_id' === currentInput[0].name && -1 === window.dynamicFieldOptionsHTML.indexOf( 'Google Drive Live Sync' ) ) {
						googleDriveFoldersOptions.replace( '<option class="bs-title-option" value=""></option>', '' );

						googleDriveFolders = '<optgroup label="Google Drive Live Sync" data-max-options="1">' + googleDriveFoldersOptions.trim() + '</optgroup>';

						window.dynamicFieldOptionsHTML = googleDriveFolders + window.dynamicFieldOptionsHTML;
					}
				}
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					capturedData = {},
					submissionData = {};

				jQuery( this.$el ).off();
				this.delegateEvents();

				this.$el.html( this.template( this.model.toJSON() ) );

				if ( 'undefined' !== typeof appAction && appAction ) {
					if ( 'upload_file' === appAction ) {
						driveFolderTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-drive-folder-template' ).html() );
						jQuery( this.$el ).find( '.fm-google-drive-action-data' ).html( driveFolderTemplate( this.model.toJSON() ) );
					}

					appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-drive-' + appAction + '-action-template' ).html() );
					jQuery( this.$el ).find( '.fm-google-drive-action-data' ).append( appActionTemplate( this.model.toJSON() ) );
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				if ( 'undefined' !== typeof window.googleAuthData && '' !== window.googleAuthData ) {
					jQuery( thisEl.$el ).find( '.google-button-text' ).html( 'Connected to Google' );
					jQuery( thisEl.$el ).find( '.google-access-token' ).val( window.googleAuthData.webhook_capture.access_token ).trigger( 'change' );

					FlowMatticWorkflowEvents.trigger( 'updateAppAuthData', 'google_drive', window.googleAuthData );
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			connectGoogleAccount: function( event ) {
				var thisEl = this,
					authWindow,
					button = jQuery( event.target ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					webhookURL = window.webhookURL,
					state = btoa(window.location.href),
					googleURL = 'https://api.flowmattic.com/google?authorize=true&redirect_uri=' + state + '&webhook_url=' + webhookURL,
					application = this.model.get( 'application' ),
					authDataCapture = setInterval( captureGoogleAuthData, 2000 ),
					captureResponse = 1;

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Authenticating <br/>Google Account',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();
						}
					}
				);

				authWindow = window.open( googleURL, 'Connect Google', 'height=800,width=800' );
				authWindow.focus();

				function captureGoogleAuthData() {
					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_capture_data', 'webhook-id': workflowId, 'application': application, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								response = JSON.parse( response );

								if ( 'pending' !== response.status ) {
									clearInterval( authDataCapture );
									thisEl.model.set( 'authData', response );

									FlowMatticWorkflowEvents.trigger( 'authDataCaptured', response );
								} else {
									if ( authWindow.closed ) {
										clearInterval( authDataCapture );

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Authentication Cancelled!',
												text: 'You have cancelled the authentication. Please authenticate your Google account to be able to run this workflow.',
												icon: 'warning',
												showConfirmButton: true,
												timer: 5000
											}
										);

										return false;
									}
								}

								captureResponse = '0';
							}
						}
					);
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			saveAuthData: function( authData ) {
				var thisEl = this,
					application = this.model.get( 'application' ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				if ( 'error' === authData.webhook_capture ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Authentication Cancelled!',
							text: 'You have cancelled the authentication. Please authenticate your Google Drive account to be able to run this automation.',
							icon: 'warning',
							showConfirmButton: true,
							timer: 5000
						}
					);

					return false;
				}

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_save_app_authentication', 'workflow_id': workflowId, 'application': application, workflow_nonce: flowMatticAppConfig.workflow_nonce, authData: authData.webhook_capture },
						success: function( response ) {
							response = JSON.parse( response );

							jQuery( thisEl.$el ).find( '.google-button-text' ).html( 'Connected to Google' );
							jQuery( thisEl.$el ).find( '.google-access-token' ).val( authData.webhook_capture.access_token ).trigger( 'change' );

							// Store auth data for this workflow.
							window.googleAuthData = authData;

							thisEl.swalWithBootstrapButtons.fire(
								{
									title: 'Authentication Successful!',
									text: 'Google Drive is now connected to FlowMattic.',
									icon: 'success',
									showConfirmButton: false,
									timer: 2500
								}
							);
						}
					}
				);
			},

			refreshDriveFolderList: function() {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Refreshing Folders List',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();
							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'refresh_drive_folder_list',  'workflow_id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										var driveSelect = jQuery( thisEl.$el ).find( 'selectGroup.google-drive-select' );
										response = JSON.parse( response );

										if ( 'undefined' !== typeof response.files ) {
											driveSelect.selectpicker( 'destroy' ).html( '' );
											_.each( response.files, function( file, index ) {
												jQuery( driveSelect ).append( '<option value="' + file.id + '" data-subtext="ID: ' + file.id + '">' + file.name  + '</option>')
											} );
										}

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Refresh Successful!',
												text: 'Google Drive list is successfully updated.',
												icon: 'success',
												showConfirmButton: false,
												timer: 2500
											}
										);
									}
								}
							);
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateDriveFolder: function( event ) {
				var thisEl = this,
					driveFolderID = jQuery( event.target ).val(),
					driveTitle = jQuery( event.target ).find( ':selected' ).text(),
					accessToken = jQuery( '.google-access-token' ).val();

				// Set the form as model attribute.
				this.model.set( 'driveFolderID', driveFolderID );

				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'driveFolderID', driveFolderID, this );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			}
		} );
	} );
}( jQuery ) );
