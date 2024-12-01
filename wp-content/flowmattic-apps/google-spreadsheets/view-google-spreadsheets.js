/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger google-spreadsheets View.
		FlowMatticWorkflow.Google_SpreadsheetsView = FlowMatticWorkflow.ActionView.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-trigger-data-template' ).html() ),
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-google-spreadsheets-data-template' ).html() ),
			sheetsTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-sheets-data-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
				'change select.google-spreadsheets-connect': 'refreshTriggerSpreadsheets',
				'click .refresh-trigger-sheets': 'refreshTriggerSpreadsheetsManually',
				'change .trigger_sheet_id_input select': 'updateTriggerSheets',
				'click .refresh-trigger-spreadsheet-sheets': 'updateTriggerSheetsManually',
				'click .flowmattic-connect-google-spreadsheets-button': 'subscribeGoogleSheetWebhook',
				'click .flowmattic-spreadsheets-connect-button': 'connectGoogleAccount',
				'click .refresh-spreadsheets': 'refreshSpreadsheets',
				'change .google-spreadsheet-id-input': 'updateSheets',
				'change .google-spreadsheet-sheet-select': 'updateSheet',
				'change .fm-row-map-data-table textarea': 'setColumnMapping',
				'change .workflow-api-authentication-type': 'displayAuthType'
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
				var googleSpreadsheets = '',
					googleSpreadsheetsOptions = jQuery( this.$el ).find( 'selectGroup[name="google-spreadsheet"]' ).html();

				if ( 'undefined' !== typeof googleSpreadsheetsOptions && '' !== googleSpreadsheetsOptions.trim() ) {
					if ( application === this.model.get( 'application' ) && ( 'spreadsheet_id' === currentInput[0].name || 'destination_spreadsheet_id' === currentInput[0].name ) && -1 === window.dynamicFieldOptionsHTML.indexOf( 'Google Spreadsheets Live Sync' ) ) {
						googleSpreadsheetsOptions.replace( '<option class="bs-title-option" value=""></option>', '' );

						googleSpreadsheets = '<optgroup label="Google Spreadsheets Live Sync" data-max-options="1">' + googleSpreadsheetsOptions.trim() + '</optgroup>';

						window.dynamicFieldOptionsHTML = googleSpreadsheets + window.dynamicFieldOptionsHTML;
					}
				}
			},

			render: function() {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					appActionTemplate = '',
					capturedData = {},
					submissionData = {},
					sheetsData,
					sheetsTemplateData = {},
					appTriggerTemplate,
					actionAppArgs = {};

				jQuery( this.$el ).off();

				this.delegateEvents();

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					// If trigger_sheet_id is not set, add the default value.
					if ( '' === thisEl.model.get( 'trigger_sheet_id' ) ) {
						thisEl.model.set( 'trigger_sheet_id', '' );
					}

					// If trigger_spreadsheet_sheet_id is not set, add the default value.
					if ( '' === thisEl.model.get( 'trigger_spreadsheet_sheet_id' ) ) {
						thisEl.model.set( 'trigger_spreadsheet_sheet_id', '' );
					}

					// Set the simple response to no.
					this.model.set( 'simple_response', 'No' );

					// If api key is not provided, create blank option.
					if ( 'undefined' === typeof this.model.get( 'connect_id' ) ) {
						this.model.set( 'connect_id', '' );
					}

					// If api key is not validated already, create blank option.
					if ( 'undefined' === typeof this.model.get( 'validate_api_response' ) ) {
						this.model.set( 'validate_api_response', '' );
					}

					thisEl.$el.html( thisEl.triggerTemplate( thisEl.model.toJSON() ) );

					// Show trigger after column id field.
					if ( 'new_row' === appAction ) {
						jQuery( thisEl.$el ).find( '.trigger-after-column-id' ).removeClass( 'd-none' );
					}

					// Show cell ID field.
					if ( 'update_specific_cell_data' === appAction ) {
						jQuery( thisEl.$el ).find( '.trigger-cell-id' ).removeClass( 'd-none' );
					}

					// Show column ID field.
					if ( 'update_specific_column_data' === appAction ) {
						jQuery( thisEl.$el ).find( '.trigger-column-id' ).removeClass( 'd-none' );
					}

					thisEl.setTriggerOptions();
				} else {
					if ( 'undefined' === typeof thisEl.model.get( 'actionAppArgs' ) ) {
						actionAppArgs.authType = 'traditional';
						thisEl.model.set( 'actionAppArgs', actionAppArgs );
					} else {
						actionAppArgs = thisEl.model.get( 'actionAppArgs' );

						if ( 'undefined' === typeof actionAppArgs.authType ) {
							actionAppArgs.authType = 'traditional';
							thisEl.model.set( 'actionAppArgs', actionAppArgs );
						}
					}

					this.$el.html( this.template( this.model.toJSON() ) );

					// If sheet is selected, load the respected template.
					if( 'undefined' !== typeof this.model.get( 'sheetsData' ) ) {
						sheetsData = this.model.get( 'sheetsData' );
						sheetsTemplateData.sheets = sheetsData.sheets;
						sheetsTemplateData.sheetID = ( 'undefined' !== typeof this.model.get( 'sheetID' ) ) ? this.model.get( 'sheetID' ) : '';

						jQuery( thisEl.$el ).find( '.fm-google-spreadsheet-data' ).html( thisEl.sheetsTemplate( sheetsTemplateData ) );
						jQuery( thisEl.$el ).find( '.google-spreadsheet-sheet-select' ).trigger( 'change' );
						jQuery( thisEl.$el ).find( 'select' ).selectpicker();
					}

					if ( 'undefined' !== typeof window.googleAuthData && '' !== window.googleAuthData ) {
						jQuery( thisEl.$el ).find( '.google-button-text' ).html( 'Connected to Google' );
						jQuery( thisEl.$el ).find( '.google-access-token' ).val( window.googleAuthData.webhook_capture.access_token ).trigger( 'change' );
					}

					if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
						capturedData = this.model.get( 'capturedData' );
						submissionData.capturedData = capturedData;
						submissionData.stepID = this.model.get( 'stepID' );

						FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
					}

					if ( 'update_row' === appAction || 'delete_row' === appAction || 'clear_row' === appAction ) {
						appAction = 'update_row';
					}

					if ( jQuery( '#flowmattic-google-spreadsheets-' + appAction + '-data-template' ).length ) {
						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-' + appAction + '-data-template' ).html() );
						jQuery( this.$el ).find( '.fm-spreadsheet-fields' ).html( appActionTemplate( this.model.toJSON() ) );
					}

					jQuery( thisEl.$el ).find( '.data-auth_option' ).hide();
					if ( 'undefined' !== typeof actionAppArgs.authType ) {
						jQuery( thisEl.$el ).find( '.data-auth_' + actionAppArgs.authType ).show();
					}

					// Refresh the spreadsheets list.
					thisEl.refreshSpreadsheets( 'manual' );

					// If action is new_sheet, hide the sheet ID field.
					if ( 'new_sheet' === appAction ) {
						jQuery( thisEl.$el ).find( '.fm-google-spreadsheet-data' ).hide();
					}
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-smartsheet-trigger-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-smartsheet-trigger-data' ).show();
				}

				if ( '' !== this.model.get( 'connect_id' ) ) {
					jQuery( this.$el ).find( '[name="connect_id"]' ).trigger( 'change' );
					jQuery( this.$el ).find( '.trigger-sheet-id' ).removeClass( 'd-none' );

					if ( '' !== this.model.get( 'trigger_sheet_id' ) ) {
						jQuery( this.$el ).find( '.trigger-spreadsheet-sheet-id' ).removeClass( 'd-none' );
					}
				}

				// Set the simple response to no.
				this.model.set( 'simple_response', 'No' );
				jQuery( this.$el ).find( '[name="simple_reponse"]' ).val('No').trigger( 'change' );
				FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'simple_response', 'No', this );
			},

			displayAuthType: function( event ) {
				var authType = jQuery( event.target ).val();
				if ( 'undefined' !== typeof authType ) {
					jQuery( this.$el ).find( '.data-auth_option' ).hide();
					jQuery( this.$el ).find( '.data-auth_' + authType ).show();
				}
			},

			connectGoogleAccount: function( event ) {
				var thisEl = this,
					authWindow,
					button = jQuery( event.target ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					webhookURL = window.webhookURL,
					state = btoa( window.location.href ),
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
							text: 'You have cancelled the authentication. Please authenticate your Google Spreadsheets account to be able to run this automation.',
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
									text: 'Google Spreadsheets is now connected to FlowMattic.',
									icon: 'success',
									showConfirmButton: false,
									timer: 2500
								}
							);
						}
					}
				);
			},

			refreshSpreadsheets: function( manual ) {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					authData = this.model.get( 'authData' ),
					authType = jQuery( thisEl.$el ).find( '[name="authType"]' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				if ( 'undefined' !== typeof authData ) {
					authData = authData.webhook_capture;
				}

				if ( 'manual' !== manual ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Refreshing Spreadsheets List',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalWithBootstrapButtons.showLoading();
							}
						}
					);
				}

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'refresh_spreadsheets',  'workflow_id': workflowId, authData: authData, authType: authType, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							var spreadsheetSelect = jQuery( thisEl.$el ).find( 'selectGroup.google-spreadsheet-select' ),
								spreadsheets = '';

							response = JSON.parse( response );

							if ( 'undefined' !== typeof response.files ) {
								_.each( response.files, function( file, index ) {
									spreadsheets += '<option value="' + file.id + '" data-subtext="ID: ' + file.id + '">' + file.name  + '</option>';
								} );
							}

							jQuery( spreadsheetSelect ).html( spreadsheets );

							if ( 'manual' !== manual ) {
								thisEl.swalWithBootstrapButtons.fire(
									{
										title: 'Refresh Successful!',
										text: 'Google Spreadsheets list is successfully updated.',
										icon: 'success',
										showConfirmButton: false,
										timer: 2500
									}
								);
							}
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			refreshTriggerSpreadsheetsManually: function() {
				this.refreshTriggerSpreadsheets( 'manual' );
			},

			refreshTriggerSpreadsheets: function( manual ) {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				if ( 'undefined' === typeof connectID ) {
					return false;
				}

				if ( 'manual' === manual ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Refreshing Spreadsheets List',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalWithBootstrapButtons.showLoading();
							}
						}
					);
				}

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'refresh_spreadsheets',  'workflow_id': workflowId, connect_id: connectID, from: 'trigger', workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							var spreadsheetSelect = jQuery( thisEl.$el ).find( '.trigger_sheet_id_input select' ),
								spreadsheets = '',
								currentSelected = thisEl.model.get( 'trigger_sheet_id' );

							response = JSON.parse( response );

							if ( 'undefined' !== typeof response.files ) {
								_.each( response.files, function( file, index ) {
									spreadsheets += '<option value="' + file.id + '" data-subtext="ID: ' + file.id + '">' + file.name  + '</option>';
								} );
							}

							jQuery( spreadsheetSelect ).html( spreadsheets );

							thisEl.$el.find( '.trigger_sheet_id_input' ).selectpicker( 'refresh' );
							thisEl.$el.find( '.trigger_sheet_id_input' ).selectpicker( 'val', currentSelected );
							thisEl.$el.find( '.trigger_sheet_id_input' ).trigger( 'change' );

							if ( 'manual' === manual ) {
								thisEl.swalWithBootstrapButtons.fire(
									{
										title: 'Refresh Successful!',
										text: 'Google Spreadsheets list is successfully updated.',
										icon: 'success',
										showConfirmButton: false,
										timer: 2500
									}
								);
							}
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateTriggerSheetsManually: function() {
				this.updateTriggerSheets( 'manual' );
			},

			updateTriggerSheets: function( manual) {
				var thisEl = this,
					spreadsheetID = jQuery( thisEl.$el ).find( '.trigger_sheet_id_input select' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				if ( '' === spreadsheetID ) {
					return false;
				}

				if ( 'manual' === manual ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Getting Sheets From Selected Spreadsheet',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalWithBootstrapButtons.showLoading();
							}
						}
					);
				}

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'refresh_spreadsheets_sheets',  'workflow_id': workflowId, spreadsheetID: spreadsheetID, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							var spreadsheetSelect = jQuery( thisEl.$el ).find( '.trigger_spreadsheet_sheet_id_input select' ),
								spreadsheets = '',
								currentSelected = thisEl.model.get( 'trigger_spreadsheet_sheet_id' );

							response = JSON.parse( response );

							if ( 'undefined' !== typeof response.sheets ) {
								_.each( response.sheets, function( sheetData, index ) {
									spreadsheets += '<option value="' + sheetData.properties.sheetId + '" data-subtext="ID: ' + sheetData.properties.sheetId + '">' + sheetData.properties.title  + '</option>';
								} );
							}

							jQuery( spreadsheetSelect ).html( spreadsheets );

							thisEl.$el.find( '.trigger_spreadsheet_sheet_id_input' ).selectpicker( 'refresh' );
							thisEl.$el.find( '.trigger_spreadsheet_sheet_id_input' ).selectpicker( 'val', currentSelected );

							if ( 'manual' === manual ) {
								thisEl.swalWithBootstrapButtons.fire(
									{
										title: 'Sheets Loaded Successful!',
										text: 'All the available sheets in selected spreadsheet are successfully loaded.',
										icon: 'success',
										showConfirmButton: false,
										timer: 2500
									}
								);
							}
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			subscribeGoogleSheetWebhook: function( event ) {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val(),
					spreadsheetID = jQuery( thisEl.$el ).find( '.trigger_sheet_id_input select' ).val(),
					sheetID = jQuery( thisEl.$el ).find( '.trigger_spreadsheet_sheet_id_input select' ).val();
					
				if ( '' === connectID ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Choose FlowMattic Connect',
							text: 'Please connect your Google account and choose the Connect',
							icon: 'warning',
							showConfirmButton: true,
							timer: 5000
						}
					);

					return false;
				}

				if ( '' === spreadsheetID ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Choose Spreadsheet',
							text: 'Please choose the Google Spreadsheet to subscribe to.',
							icon: 'warning',
							showConfirmButton: true,
							timer: 5000
						}
					);

					return false;
				}

				if ( '' === sheetID ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Choose Sheet',
							text: 'Please choose the Google Spreadsheet sheet to subscribe to.',
							icon: 'warning',
							showConfirmButton: true,
							timer: 5000
						}
					);

					return false;
				}

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Subscribing to Google Sheet',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();

							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_subscribe_google_sheet',  'workflow_id': workflowId, connect_id: connectID, spreadsheetID: spreadsheetID, sheetID: sheetID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										response = JSON.parse( response );

										if ( 'success' === response.status ) {
											// Set trigger app model attribute.
											thisEl.model.set( 'validate_api_response', 'true' );
											FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'validate_api_response', 'true', thisEl );

											// Change button text and style.
											jQuery( event.target ).text( 'Connected to Google Sheets' ).removeClass( 'btn-primary btn-success flowmattic-button' ).addClass( 'btn-outline-success' );

											thisEl.swalWithBootstrapButtons.fire(
												{
													title: 'Subscribed Successfully!',
													text: 'You have successfully subscribed to the selected Google Spreadsheet sheet.',
													icon: 'success',
													showConfirmButton: false,
													timer: 2500
												}
											);
										} else {
											var errorMessage = 'There was an error subscribing to the Google Spreadsheet. Please try again.';
											// Check if the error message exist, and it has word 'not unique'.
											if ( 'undefined' !== typeof response.message && -1 !== response.message.indexOf( 'not unique' ) ) {
												errorMessage = 'You have already subscribed to this Google Spreadsheet.';

												// Set trigger app model attribute.
												thisEl.model.set( 'validate_api_response', 'true' );
												FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'validate_api_response', 'true', thisEl );

												// Change button text and style.
												jQuery( event.target ).text( 'Connected to Google Sheets' ).removeClass( 'btn-primary btn-success flowmattic-button' ).addClass( 'btn-outline-success' );
											}

											thisEl.swalWithBootstrapButtons.fire(
												{
													title: 'Subscription Failed!',
													text: errorMessage,
													icon: 'error',
													showConfirmButton: false,
													timer: 2500
												}
											);
										}
									}
								}
							);
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateSheets: function( event ) {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					spreadsheetID = jQuery( event.target ).val(),
					accessToken = jQuery( '.google-access-token' ).val(),
					authType = jQuery( thisEl.$el ).find( '[name="authType"]' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				if ( '' === spreadsheetID ) {
					return false;
				}

				if ( 'new_sheet' === appAction ) {
					return false;
				}

				// Set the form as model attribute.
				this.model.set( 'spreadsheetID', spreadsheetID );

				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'spreadsheetID', spreadsheetID, this );

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Getting Sheets From Selected Spreadsheet',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();
							var accessURL = 'https://sheets.googleapis.com/v4/spreadsheets/' + spreadsheetID + '?includeGridData=false';
							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_fetch_sheet_url',  'workflow_id': workflowId, access_url: accessURL, accessToken: accessToken, authType: authType, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( data ) {
										var sheetsData = {};

										// Parse the data.
										data = JSON.parse( data );

										// sheetsData.properties.title = data.properties.title;
										sheetsData.sheets = [];

										_.each( data.sheets, function( sheetData, index ) {
											var columns,
												columnHeadings = [];

											sheetsData.sheets.push( {
												sheetID: sheetData.properties.sheetId,
												title: sheetData.properties.title
											} );
										} );

										FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'sheetsData', sheetsData, thisEl );
										thisEl.model.set( 'sheetsData', sheetsData );

										jQuery( thisEl.$el ).find( '.fm-google-spreadsheet-data' ).html( thisEl.sheetsTemplate( sheetsData ) );
										jQuery( thisEl.$el ).find( 'select' ).selectpicker();

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Sheets Loaded Successful!',
												text: 'All the available sheets in selected spreadsheet are successfully loaded.',
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

			updateSheet: function( event ) {
				var thisEl = this,
					appAction = this.model.get( 'action' ),
					sheetId = parseInt( jQuery( event.target ).val() ),
					sheetName = jQuery( event.target ).find( ':selected' ).text(),
					sheetTitle = '',
					sheetsData = thisEl.model.get( 'sheetsData' ),
					rowData,
					rowDataValues,
					rowDataTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-row-data-template' ).html() ),
					rowDynamicDataTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-row-dynamic-data-template' ).html() ),
					spreadsheetID = this.model.get( 'spreadsheetID' ),
					accessToken = jQuery( this.$el ).find( '.google-access-token' ).val(),
					authType = jQuery( thisEl.$el ).find( '[name="authType"]' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				sheetName = sheetName.trim();
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'sheetID', sheetId, this );
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'sheetTitle', sheetName, this );

				// If appAction is not new_row or update_row, return false.
				if ( -1 === [ 'new_row', 'update_row' ].indexOf( appAction ) ) {
					return false;
				}

				if ( ! event.isTrigger ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Getting Sheet Data From Selected Sheet',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalWithBootstrapButtons.showLoading();
								var accessURL = 'https://sheets.googleapis.com/v4/spreadsheets/' + spreadsheetID + '?includeGridData=true&ranges=sheetTitle!A1:ZZ1';
								jQuery.ajax(
									{
										url: ajaxurl,
										type: 'POST',
										data: { action: 'flowmattic_fetch_sheet_url',  'workflow_id': workflowId, access_url: accessURL, sheetName: sheetName, accessToken: accessToken, authType: authType, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
										success: function( data ) {
											var sheetsData = {};
	
											// Parse the data.
											data = JSON.parse( data );

											// sheetsData.properties.title = data.properties.title;
											sheetsData.sheets = [];

											_.each( data.sheets, function( sheetData, index ) {
												var columns,
													columnHeadings = [];

												if ( sheetId !== sheetData.properties.sheetId ) {
													return;
												}

												if ( 'undefined' !== typeof sheetData.data[0].rowData ) {
													columns = sheetData.data[0].rowData[0].values;

													_.each( columns, function( rowDataValues, index ) {
														if ( 'undefined' !== typeof rowDataValues.formattedValue && '' !== rowDataValues.formattedValue ) {
															columnHeadings.push( rowDataValues.formattedValue );
														}
													} );
												}

												if ( '' !== columnHeadings && ! _.isEmpty( columnHeadings ) ) {
													if ( 'undefined' !== typeof columnHeadings ) {
														FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'rowData', columnHeadings, thisEl );

														jQuery( thisEl.$el ).find( '.fm-spreadsheet-rows' ).html( rowDataTemplate( { values: columnHeadings, columnMapping: thisEl.model.get( 'columnMapping' ) } ) );
													}
												} else {
													jQuery( thisEl.$el ).find( '.fm-spreadsheet-rows' ).html( rowDynamicDataTemplate( thisEl.model.toJSON() ) );
												}

												sheetsData.sheets.push( {
													sheetID: sheetData.properties.sheetId,
													title: sheetData.properties.title,
													columnHeadings: columnHeadings
												} );
											} );

											thisEl.swalWithBootstrapButtons.fire(
												{
													title: 'Sheets Data Loaded Successful!',
													text: 'Selected sheets data is successfully loaded.',
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
				} else {
					this.updateSheetInBackground();
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateSheetInBackground: function() {
				var thisEl = this,
					sheetId = thisEl.model.get( 'sheetID' ),
					sheetName = thisEl.model.get( 'sheetTitle' ),
					sheetTitle = '',
					sheetsData = thisEl.model.get( 'sheetsData' ),
					rowData,
					rowDataValues,
					rowDataTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-row-data-template' ).html() ),
					rowDynamicDataTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-google-spreadsheets-row-dynamic-data-template' ).html() ),
					spreadsheetID = this.model.get( 'spreadsheetID' ),
					accessToken = jQuery( this.$el ).find( '.google-access-token' ).val(),
					authType = jQuery( thisEl.$el ).find( '[name="authType"]' ).val(),
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val();

				sheetName = ( 'undefined' !== typeof sheetName ) ? sheetName.trim() : '';

				var accessURL = 'https://sheets.googleapis.com/v4/spreadsheets/' + spreadsheetID + '?includeGridData=true&ranges=sheetTitle!A1:ZZ1';
				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_fetch_sheet_url',  'workflow_id': workflowId, sheetName: sheetName, access_url: accessURL, accessToken: accessToken, authType: authType, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( data ) {
							var sheetsData = {};

							// Parse the data.
							data = JSON.parse( data );

							// sheetsData.properties.title = data.properties.title;
							sheetsData.sheets = [];

							_.each( data.sheets, function( sheetData, index ) {
								var columns,
									columnHeadings = [];

								if ( sheetId !== sheetData.properties.sheetId ) {
									return;
								}

								if ( 'undefined' !== typeof sheetData.data[0].rowData ) {
									columns = sheetData.data[0].rowData[0].values;

									_.each( columns, function( rowDataValues, index ) {
										if ( 'undefined' !== typeof rowDataValues.formattedValue && '' !== rowDataValues.formattedValue ) {
											columnHeadings.push( rowDataValues.formattedValue );
										}
									} );
								}

								if ( '' !== columnHeadings && ! _.isEmpty( columnHeadings ) ) {
									if ( 'undefined' !== typeof columnHeadings ) {
										FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'rowData', columnHeadings, thisEl );

										jQuery( thisEl.$el ).find( '.fm-spreadsheet-rows' ).html( rowDataTemplate( { values: columnHeadings, columnMapping: thisEl.model.get( 'columnMapping' ) } ) );
									}
								} else {
									jQuery( thisEl.$el ).find( '.fm-spreadsheet-rows' ).html( rowDynamicDataTemplate( thisEl.model.toJSON() ) );
								}

								sheetsData.sheets.push( {
									sheetID: sheetData.properties.sheetId,
									title: sheetData.properties.title,
									columnHeadings: columnHeadings
								} );
							} );
						}
					}
				);
			},

			setColumnMapping: function() {
				var columnMapping = {};

				_.each( jQuery( this.$el ).find( '.fm-row-map-data-table tbody tr' ), function( item, index ) {
					var inputkey = jQuery( item ).find( '.fm-col-key-input' ).val(),
						inputVal = jQuery( item ).find( '.fm-col-value-input' ).val();

						columnMapping[ inputkey ] = inputVal;
				} );

				this.model.set( 'columnMapping', columnMapping );

				// Unset the custom mapping.
				this.model.set( 'customColumnMapping', [] );

				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'columnMapping', columnMapping, this );
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'customColumnMapping', [], this );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			}
		} );
	} );
}( jQuery ) );
