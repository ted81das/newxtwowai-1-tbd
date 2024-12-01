/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Mailchimp View.
		FlowMatticWorkflow.MailchimpView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-mailchimp-trigger-data-template' ).html() ),
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-mailchimp-account-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
				'click .flowmattic-auth-mailchimp-connection-button': 'connectMailchimp',
				'change .flowmattic-mailchimp-data-form .form-control': 'updateMailchimpArgs',
				'change select.mailchimp-list-select': 'setMailchimpList',
				'click .refresh-lists': 'refreshLists',
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Listen to the authData capture event.
				this.listenTo( FlowMatticWorkflowEvents, 'mailchimpAuthDataCaptured', this.saveAuthData );

				// Listen to trigger event change.
				this.listenTo( FlowMatticWorkflowEvents, 'appTriggerChanged', this.updateEventInstructions );
			},

			render: function() {
				var appAction = this.model.get( 'action' ),
					capturedData,
					submissionData = {},
					appActionTemplate,
					listSelect = jQuery( this.$el ).find( 'select.mailchimp-list-select' );

				if ( 'undefined' === typeof this.model.get( 'mailchimpArgs' ) ) {
					this.model.set( 'mailchimpArgs', {} );
				}

				if ( 'trigger' === this.model.get( 'type' ) ) {
					this.$el.html( this.triggerTemplate( this.model.toJSON() ) );
				} else {
					// Set account template.
					this.$el.html( this.template( this.model.toJSON() ) );

					if ( '' !== appAction ) {
						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-mailchimp-' + appAction + '-action-template' ).html() );
						jQuery( this.$el ).find( '.fm-mailchimp-action-data' ).html( appActionTemplate( this.model.toJSON() ) );
					}

					if ( 'undefined' !== typeof window.mailchimpAuthData && '' !== window.mailchimpAuthData ) {
						jQuery( this.$el ).find( '.mailchimp-button-text' ).html( 'Connected to Mailchimp' );
						jQuery( this.$el ).find( '.mailchimp-button-text' ).removeClass( 'btn-primary flowmattic-button' ).addClass( 'btn-outline-primary' );

						this.showLists();
					}

					if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
						capturedData = this.model.get( 'capturedData' );
						submissionData.capturedData = capturedData;
						submissionData.stepID = this.model.get( 'stepID' );

						FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
					}
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			connectMailchimp: function( event ) {
				var thisEl = this,
					authWindow,
					button = jQuery( event.target ),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					webhookURL = window.webhookURL,
					state = btoa(window.location.href),
					mailchimpURL = 'https://api.flowmattic.com/mailchimp?authorize=true&redirect_uri=' + state + '&webhook_url=' + webhookURL,
					authDataCapture = setInterval( captureMailchimpAuthData, 2000 ),
					application = this.model.get( 'application' ),
					captureResponse = 1;

				authWindow = window.open( mailchimpURL, 'Connect Mailchimp', 'height=850,width=800' );
				authWindow.focus();

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Authenticating Mailchimp',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();
						}
					}
				);

				function captureMailchimpAuthData() {
					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_capture_data', application: application, 'webhook-id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce, capture: captureResponse },
							success: function( response ) {
								response = JSON.parse( response );

								if ( 'pending' !== response.status ) {
									clearInterval( authDataCapture );
									thisEl.model.set( 'authData', response );

									// Change button text and style.
									jQuery( event.target ).text( 'Connected to Mailchimp' ).removeClass( 'btn-primary flowmattic-button' ).addClass( 'btn-outline-primary' );

									FlowMatticWorkflowEvents.trigger( 'mailchimpAuthDataCaptured', response );

									thisEl.showLists();
								} else {
									if ( authWindow.closed ) {
										clearInterval( authDataCapture );

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Authentication Cancelled!',
												text: 'You have cancelled the authentication. Please authenticate your Mailchimp account to be able to run this automation.',
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

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_save_app_authentication', 'workflow_id': workflowId, 'application': application, workflow_nonce: flowMatticAppConfig.workflow_nonce, authData: authData.webhook_capture },
						success: function( response ) {
							response = JSON.parse( response );

							// Store auth data for this workflow.
							window.mailchimpAuthData = authData;

							thisEl.swalWithBootstrapButtons.fire(
								{
									title: 'Authentication Successful!',
									text: 'Mailchimp is now connected to FlowMattic.',
									icon: 'success',
									showConfirmButton: false,
									timer: 2500
								}
							);
						}
					}
				);
			},

			updateMailchimpArgs: function() {
				var mailchimpArgs = {};

				_.each( jQuery( this.$el ).find( '.flowmattic-mailchimp-data-form .form-control' ), function( field, index ) {
					var inputName = jQuery( field ).attr( 'name' ),
						inputSubName = jQuery( field ).attr( 'sub-name' ),
						inputValue = jQuery( field ).val();

					if ( jQuery( field ).is( ':checkbox' ) ) {
						inputValue = ( jQuery( field ).is( ':checked' ) ) ? 'Yes' : 'No';
					}

					if ( 'undefined' !== typeof inputSubName ) {
						if ( 'undefined' === typeof mailchimpArgs[ inputName ] ) {
							mailchimpArgs[ inputName ] = {};
							mailchimpArgs[ inputName ][ inputSubName ] = inputValue;
						} else {
							mailchimpArgs[ inputName ][ inputSubName ] = inputValue;
						}
					} else {
						if ( 'undefined' !== typeof inputName ) {
							mailchimpArgs[ inputName ] = inputValue;
						}
					}
				} );

				// Set this modal attribute.
				this.model.set( 'mailchimpArgs', mailchimpArgs );

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'mailchimpArgs', mailchimpArgs, this );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setMailchimpList: function( event ) {
				var audienceList = jQuery( event.target ).val();

				// Set this modal attribute.
				this.model.set( 'audienceList', audienceList );

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'audienceList', audienceList, this );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			refreshLists: function() {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Refreshing Audience Lists',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();
							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_refresh_mailchimp_lists', 'workflow_id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										var listSelect = jQuery( thisEl.$el ).find( 'select.mailchimp-list-select' );
											response = JSON.parse( response ),
											audienceList = thisEl.model.get( 'audienceList' ),
											selected = '';

										if ( 'undefined' !== typeof response.lists ) {
											// Store audience data for this workflow.
											window.mailchimpAudienceList = response.lists;

											listSelect.selectpicker( 'destroy' ).html( '' );
											_.each( response.lists, function( list, index ) {

												if ( 'undefined' !== typeof audienceList ) {
													selected = ( list.id === audienceList ) ? 'selected' : '';
												}

												jQuery( listSelect ).append( '<option value="' + list.id + '" data-subtext="ID: ' + list.id + '" ' + selected + '>' + list.name  + '</option>')
											} );

											setTimeout( function() {
												listSelect.selectpicker();
											}, 500 );
										}

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Refresh Successful!',
												text: 'Audience Lists is successfully updated.',
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

			showLists: function() {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val();

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_refresh_mailchimp_lists', 'workflow_id': workflowId, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							var listSelect = jQuery( thisEl.$el ).find( 'select.mailchimp-list-select' );
								response = JSON.parse( response ),
								audienceList = thisEl.model.get( 'audienceList' ),
								selected = '';

							if ( 'undefined' !== typeof response.lists ) {
								// Store audience data for this workflow.
								window.mailchimpAudienceList = response.lists;

								listSelect.selectpicker( 'destroy' ).html( '' );
								_.each( response.lists, function( list, index ) {

									if ( 'undefined' !== typeof audienceList ) {
										selected = ( list.id === audienceList ) ? 'selected' : '';
									}

									jQuery( listSelect ).append( '<option value="' + list.id + '" data-subtext="ID: ' + list.id + '" ' + selected + '>' + list.name  + '</option>')
								} );

								setTimeout( function() {
									listSelect.selectpicker();
									listSelect.closest( '.form-group' ).show( 'slideTop' );
								}, 500 );
							}
						}
					}
				);

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			updateEventInstructions: function( event ) {
				var eventTitle = '';

				if ( 'undefined' !== typeof otherTriggerApps['mailchimp'].triggers[ event ] ) {
					eventTitle = otherTriggerApps['mailchimp'].triggers[ event ].trigger;
					this.$el.find( '.mailchimp-event-selected' ).html( eventTitle );
				}
			}
		} );
	} );
}( jQuery ) );
