/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger facebook-lead-ads View.
		FlowMatticWorkflow.Facebook_Lead_AdsView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-facebook-lead-ads-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-facebook-lead-ads-action-template' ).html() ),
			swalButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			}),

			events: {
				'change .facebook-api-connect': 'handleTriggerConnectChange',
				'click .refresh-pages' : 'updatePages',
				'click .refresh-facebook-pages' : 'updatePages',
				'change .page_id_input': 'updatePageForms',
				'click .refresh-forms' : 'updateForms',
				'click .refresh-page-forms' : 'updateForms',
				'click .flowmattic-connect-facebook-button': 'createFacebookLeadAdsWebhook',
				'change .facebook-lead-ads-connect' : 'handleTriggerConnectChange',
				'change .lead_form_id_input' : 'resetConnectFBButton'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' ),
					appActionTemplate = '',
					actionAppArgs = thisEl.model.get( 'actionAppArgs' ),
					appTriggerTemplate;					

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					thisEl.$el.html( thisEl.triggerTemplate( thisEl.model.toJSON() ) );
					thisEl.setTriggerOptions();
					thisEl.handleTriggerConnectChange();
				} else {
					thisEl.$el.html( thisEl.actionTemplate( thisEl.model.toJSON() ) );

					if ( jQuery( '#facebook-lead-ads-action-' + appAction + '-data-template' ).length ) {
						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#facebook-lead-ads-action-' + appAction + '-data-template' ).html() );
						jQuery( thisEl.$el ).find( '.facebook-lead-ads-action-data' ).html( appActionTemplate( thisEl.model.toJSON() ) );
					}

					thisEl.setActionOptions();
					thisEl.handleTriggerConnectChange();
				}

				thisEl.$el.find( 'select' ).selectpicker();

				return this;
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-facebook-trigger-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-facebook-trigger-data' ).show();
				}
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.facebook-lead-ads-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.facebook-lead-ads-action-data' ).show();
				}
			},

			resetConnectFBButton: function() {
				jQuery( this.$el ).find( '.flowmattic-connect-facebook-button' ).text( 'Connect to Facebook' ).removeClass( 'btn-outline-success' ).addClass( 'btn-success flowmattic-button' );
			},

			handleTriggerConnectChange: function( event ) {
				var thisEl = this,
					pageID = thisEl.model.get( 'page_id' ),
					formID = thisEl.model.get( 'form_id' );

				thisEl.updatePages( 'manual' );

				if ( 'undefined' !== typeof pageID ) {
					thisEl.updateForms( 'manual', pageID );
				}

				setTimeout( function() {
					thisEl.$el.find( '.page_id_input select' ).html( window.facebookPagesOptions );
					thisEl.$el.find( '.page_id_input select' ).selectpicker( 'refresh' );
					thisEl.$el.find( '.page_id_input select' ).selectpicker( 'val', pageID );

					jQuery( thisEl.$el ).find( '.trigger-page-alert' ).addClass( 'd-none' );
					jQuery( thisEl.$el ).find( '.trigger-pages' ).removeClass( 'd-none' );

					jQuery( thisEl.$el ).find( '.trigger-forms' ).removeClass( 'd-none' );

					thisEl.$el.find( '.lead_form_id_input select' ).html( window.facebookFormsOptions );
					thisEl.$el.find( '.lead_form_id_input select' ).selectpicker( 'refresh' );
					thisEl.$el.find( '.lead_form_id_input select' ).selectpicker( 'val', formID );
				}, 500 );
			},

			// Register webhook for FacebookLeadAds.
			createFacebookLeadAdsWebhook: function( event ) {
				var thisEl = this,
					connectID = jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val(),
					pageID = jQuery( thisEl.$el ).find( '[name="page_id"]' ).val(),
					formID = jQuery( thisEl.$el ).find( '[name="form_id"]' ).val(),
					webhookURL = window.webhookURL,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					appAction = thisEl.model.get( 'action' );

				if ( '' !== connectID ) {
					thisEl.swalButtons.fire(
						{
							title: 'Registering Facebook Lead Ads Trigger',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalButtons.showLoading();
							}
						}
					);

					jQuery.ajax(
						{
							url: ajaxurl,
							type: 'POST',
							data: { action: 'flowmattic_create_facebook_webhook', webhook_url: webhookURL, webhook_id: workflowId, trigger: appAction, connect_id: connectID, page_id: pageID, form_id: formID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
							success: function( response ) {
								var responseDecode = JSON.parse( response );

								if ( responseDecode.success ) {
									thisEl.model.set( 'validate_facebook_response', response );

									// Set trigger app model attribute.
									FlowMatticWorkflowEvents.trigger( 'triggerAppDataUpdateSingleAttribute', 'validate_facebook_response', response, thisEl );

									// Change button text and style.
									jQuery( event.target ).text( 'Connected to Facebook' ).removeClass( 'btn-success flowmattic-button' ).addClass( 'btn-outline-success' );

									thisEl.swalButtons.fire(
										{
											title: 'Registered Successful!',
											text: 'This workflow trigger is successfully registered for the selected event.',
											icon: 'success',
											showConfirmButton: true,
											timer: 1500
										}
									);

									// Autosave workflow.
									FlowMatticWorkflowEvents.trigger( 'triggerAutosave' );
								} else {
									thisEl.swalButtons.fire(
										{
											title: 'Something Went Wrong!',
											text: responseDecode.message,
											icon: 'warning',
											showConfirmButton: true,
											timer: 3000
										}
									);
								}
							}
						}
					);

					FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
				}
			},

			// Get Pages.
			updatePages: function( trigger ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					facebookDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-facebook-dropdown-template' ).html() );

				if ( 'undefined' !== typeof window.facebookPagesOptions && 'manual' === trigger ) {
					return false;
				}

				// Reset the options.
				window.facebookPagesOptions = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Pages...',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalButtons.showLoading();
							}
						}
					);
				}

				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_facebook_lead_ads_get_pages', workflow_id: workflowId, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var pagesResponse = {};

						if ( '' === response ) {
							return false;
						}

						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.data ) {
							pagesResponse.templates       = response.data;
							pagesResponse.currentSelected = '';

							window.facebookPagesOptions = facebookDropdownTemplate( pagesResponse );
							
							if ( 'trigger' === thisEl.model.get( 'type' ) ) {
								thisEl.handleTriggerConnectChange();
							}

							if ( 'manual' !== trigger ) {
								thisEl.swalButtons.fire(
									{
										title: 'Refresh Successful!',
										icon: 'success',
										showConfirmButton: true,
										timer: 1500
									}
								);
							}
						} else {
							if ( 'manual' !== trigger ) {
								thisEl.swalButtons.fire(
									{
										title: 'Something Went Wrong!',
										html: 'We had some issues retrieving the list. Please try refreshing once, or contact support.<br/> Error Message: <span class="text-danger">' + response.message + '</span>',
										icon: 'warning',
										showConfirmButton: true,
										timer: 3000
									}
								);
							}
						}
					}
				} );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			// Get Forms.
			updatePageForms: function() {
				var pageID = this.$el.find( '[name="page_id"]' ).val();

				// Reset the options.
				delete window.facebookFormsOptions;

				// Call reset connect button.
				this.resetConnectFBButton();

				this.updateForms( 'manual', pageID );
			},

			// Load form field options.
			updateForms: function( trigger, page_id ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					pageID = ( 'undefined' === typeof page_id || '' === page_id ) ? thisEl.$el.find( '[name="page_id"]' ).val() : page_id,
					formID = ( 'undefined' !== thisEl.model.get( 'form_id' ) ) ? thisEl.model.get( 'form_id' ) : thisEl.$el.find( '[name="form_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					facebookDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-facebook-dropdown-template' ).html() );

				// Show the form field.
				jQuery( thisEl.$el ).find( '.trigger-forms' ).removeClass( 'd-none' );

				if ( 'undefined' !== typeof window.facebookFormsOptions && 'manual' === trigger ) {
					return false;
				}

				// Reset the options.
				window.facebookFormsOptions = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Forms...',
							showConfirmButton: false,
							didOpen: () => {
								thisEl.swalButtons.showLoading();
							}
						}
					);
				}

				jQuery.ajax( {
					url: ajaxurl,
					type: 'POST',
					data: { action: 'flowmattic_facebook_lead_ads_get_lead_forms', workflow_id: workflowId, connect_id: connectID, page_id: pageID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var pagesResponse = {};

						if ( '' === response ) {
							return false;
						}

						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.forms ) {
							pagesResponse.templates       = response.forms;
							pagesResponse.currentSelected = '';

							window.facebookFormsOptions = facebookDropdownTemplate( pagesResponse );

							thisEl.$el.find( '.lead_form_id_input select' ).html( window.facebookFormsOptions );
							thisEl.$el.find( '.lead_form_id_input select' ).selectpicker( 'refresh' );
							thisEl.$el.find( '.lead_form_id_input select' ).selectpicker( 'val', formID );

							if ( 'manual' !== trigger ) {
								thisEl.swalButtons.fire(
									{
										title: 'Refresh Successful!',
										icon: 'success',
										showConfirmButton: true,
										timer: 1500
									}
								);
							}
						} else {
							if ( 'manual' !== trigger ) {
								thisEl.swalButtons.fire(
									{
										title: 'Something Went Wrong!',
										html: 'We had some issues retrieving the list. Please try refreshing once, or contact support.',
										icon: 'warning',
										showConfirmButton: true,
										timer: 3000
									}
								);
							}
						}
					}
				} );

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			generateDynamicFieldsHTML: function( application, currentInput ) {
				var pagesDropdownHTML = '',
					formsDropdownHTML = '';

				// Pages.
				if ( application === this.model.get( 'application' ) && currentInput[0].name === 'page_id' && window.dynamicFieldOptionsHTML.indexOf( 'label="Pages"' ) === -1 ) {
					pagesDropdownHTML = '<optgroup label="Pages" data-max-options="1">' + window.facebookPagesOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = pagesDropdownHTML + window.dynamicFieldOptionsHTML;
				}

				// Forms.
				if ( application === this.model.get( 'application' ) && currentInput[0].name === 'form_id' && window.dynamicFieldOptionsHTML.indexOf( 'label="Forms"' ) === -1 ) {
					formsDropdownHTML = '<optgroup label="Forms" data-max-options="1">' + window.facebookFormsOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = formsDropdownHTML + window.dynamicFieldOptionsHTML;
				}
			}
		} );
	} );
}( jQuery ) );
