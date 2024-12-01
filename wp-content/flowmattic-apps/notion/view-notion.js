/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger notion View.
		FlowMatticWorkflow.NotionView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-notion-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-notion-action-data-template' ).html() ),
			swalButtons: window.Swal.mixin( {
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none',
				},
				buttonsStyling: false
			} ),
			notionTriggerDatabases: '',

			events: {
				'click .refresh-databases-trigger': 'updateTriggerDatabases',
				'change [name="connect_id"]': 'handleAPIChangeEvent',
				'click .refresh-databases': 'updateDatabases',
				'click .refresh-notion-people': 'updatePeoples',
				'click .refresh-items': 'updateItems',
				'click .refresh-parent-pages': 'updateParentPages',
				'change [name="database_id"]': 'updateDatabasesProperties'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );

				// Listen to the triggerAPIChangeEvent event.
				this.listenTo( FlowMatticWorkflowEvents, 'triggerAPIChangeEvent', this.handleTriggerAPIChangeEvent );

				// Listen to the appTriggerChanged event.
				// this.listenTo(FlowMatticWorkflowEvents, 'appTriggerChanged', this.updateEventInInstructions);
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' ),
					appActionTemplate,
					triggerConnectId = '';

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					// If database id is not set, add the default value.
					if ( '' === thisEl.model.get( 'database_id' ) ) {
						thisEl.model.set( 'database_id', '' );
					}

					// Reset the options.
					window.notionTriggerDatabases = '';

					thisEl.$el.html( thisEl.triggerTemplate( thisEl.model.toJSON() ) );
					thisEl.setTriggerOptions();

					triggerConnectId = thisEl.model.get( 'trigger_connect_id' );
					thisEl.updateTriggerDatabases( 'manual' );

					if ( 'undefined' !== typeof triggerConnectId && 'none' !== triggerConnectId ) {
						thisEl.handleTriggerAPIChangeEvent( triggerConnectId );
					}
				} else {
					thisEl.$el.html( thisEl.actionTemplate( thisEl.model.toJSON() ) );

					if ( jQuery( '#notion-action-' + appAction + '-data-template' ).length ) {
						appActionTemplate = FlowMatticWorkflow.template( jQuery( '#notion-action-' + appAction + '-data-template' ).html() );
						jQuery( thisEl.$el ).find( '.notion-action-data' ).html( appActionTemplate( thisEl.model.toJSON() ) );
					}

					thisEl.setActionOptions();
					thisEl.handleAPIChangeEvent();
				}

				thisEl.$el.find( 'select' ).selectpicker();

				return this;
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-notion-form-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-notion-form-data' ).show();
				}
			},

			updateEventInInstructions: function( event ) {
				var eventTitle = '';

				if ( 'undefined' !== typeof otherTriggerApps['notion'].triggers[ event ] ) {
					eventTitle = otherTriggerApps['notion'].triggers[ event ].title;
					this.$el.find( '.notion-event-selected' ).html( eventTitle );
				}
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-notion-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-notion-action-data' ).show();
				}
			},

			handleAPIChangeEvent: function() {
				var thisEl = this;

				if ( jQuery( thisEl.$el ).find( '[name="connect_id"]' ).val() ) {
					// Update Databases options.
					if ( thisEl.$el.find( '[name="database_id"]' ).length ) {
						thisEl.updateDatabases( 'manual' );
					}

					// Update peoples options.
					if ( thisEl.$el.find( '.people_id_input' ).length ) {
						thisEl.updatePeoples( 'manual' );
					}

					// Update items options.
					if ( thisEl.$el.find( '[name="item_id"]' ).length ) {
						thisEl.updateItems( 'manual' );
					}

					// Update parent pages options.
					if ( thisEl.$el.find( '[name="parent_page_id"]' ).length ) {
						thisEl.updateParentPages( 'manual' );
					}
				}
			},

			handleTriggerAPIChangeEvent: function( connectID ) {
				var thisEl = this,
					databaseID = thisEl.model.get( 'database_id' );

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					if ( 'undefined' !== typeof connectID ) {
						if ( ! window.notionTriggerDatabases ) {
							thisEl.updateTriggerDatabases( 'manual', connectID );
						}

						// Update Databases dropdown.
						if ( window.notionTriggerDatabases ) {
							thisEl.$el.find( '.database_id_trigger select' ).html( window.notionTriggerDatabases );
							thisEl.$el.find( '.database_id_trigger select' ).selectpicker( 'refresh' );
							thisEl.$el.find( '.database_id_trigger select' ).selectpicker( 'val', databaseID );
						}
					}
				}
			},

			updateTriggerDatabases: function( trigger, connectID ) {
				var thisEl = this,
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					databaseID = thisEl.$el.find( '[name="database_id"]' ).val(),
					notionDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-dropdown-template' ).html() );

				if ( 'undefined' === typeof connectID ) {
					connectID = thisEl.model.get( 'trigger_connect_id' );

					// Set the connectID to the current value.
					thisEl.model.set( 'trigger_connect_id', connectID );
				}

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Databases...',
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
					data: { action: 'flowmattic_get_notion_databases', source: 'Trigger', workflow_id: workflowId, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var databasesResponse = {};
						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.databases ) {
							databasesResponse.templates       = response.databases;
							databasesResponse.currentSelected = databaseID;

							window.notionTriggerDatabases = notionDropdownTemplate( databasesResponse );

							thisEl.handleTriggerAPIChangeEvent( connectID );

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

			updateDatabases: function( trigger ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					databaseID = thisEl.$el.find( '[name="database_id"]' ).val(),
					notionDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-dropdown-template' ).html() );

				if ( 'undefined' !== typeof window.notionDatabasesOptions && 'manual' === trigger ) {
					return false;
				}

				// Reset the options.
				window.notionDatabasesOptions = '';
				window.notionDatabases = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Databases...',
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
					data: { action: 'flowmattic_get_notion_databases', workflow_id: workflowId, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var databasesResponse = {};
						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.databases ) {
							databasesResponse.templates       = response.databases;
							databasesResponse.currentSelected = '';

							window.notionDatabases        = response.databases;
							window.notionDatabasesOptions = notionDropdownTemplate( databasesResponse );

							if ( '' !== databaseID ) {
								thisEl.updateDatabasesProperties( 'manual' );
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

			updatePeoples: function( trigger ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					notionDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-dropdown-template' ).html() );

				if ( 'undefined' !== typeof window.notionPeoplesOptions && 'manual' === trigger ) {
					return false;
				}

				// Reset the options.
				window.notionPeoplesOptions = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Users...',
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
					data: { action: 'flowmattic_get_notion_peoples', workflow_id: workflowId, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var peoplesResponse = {};
						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.users ) {
							peoplesResponse.templates       = response.users;
							peoplesResponse.currentSelected = '';

							window.notionPeoplesOptions = notionDropdownTemplate( peoplesResponse );

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

			updateItems: function( trigger ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					databaseID = thisEl.$el.find( '[name="database_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					notionDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-dropdown-template' ).html() );

				// Reset the options.
				window.notionItemsOptions = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Items...',
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
					data: { action: 'flowmattic_get_notion_items', workflow_id: workflowId, connect_id: connectID, database_id: databaseID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var itemsResponse = {};
						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.items ) {
							itemsResponse.templates       = response.items;
							itemsResponse.currentSelected = '';

							window.notionItemsOptions = notionDropdownTemplate( itemsResponse );

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

			updateParentPages: function( trigger ) {
				var thisEl = this,
					connectID = thisEl.$el.find( '[name="connect_id"]' ).val(),
					databaseID = thisEl.$el.find( '[name="database_id"]' ).val(),
					workflowId = jQuery( 'body' ).find( '.workflow-input.workflow-id' ).val(),
					notionDropdownTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-dropdown-template' ).html() );

				// Reset the options.
				window.notionParentPagesOptions = '';

				if ( 'manual' !== trigger ) {
					thisEl.swalButtons.fire(
						{
							title: 'Refreshing Parent Pages...',
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
					data: { action: 'flowmattic_get_notion_parent_pages', workflow_id: workflowId, connect_id: connectID, workflow_nonce: flowMatticAppConfig.workflow_nonce },
					success: function( response ) {
						var parentPagesResponse = {};
						response = JSON.parse( response );

						if ( 'undefined' !== typeof response.parentPages ) {
							parentPagesResponse.templates       = response.parentPages;
							parentPagesResponse.currentSelected = '';

							window.notionParentPagesOptions = notionDropdownTemplate( parentPagesResponse );

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

			updateDatabasesProperties: function() {
				var thisEl = this,
					databaseID = thisEl.$el.find( '[name="database_id"]' ).val(),
					notionFieldsTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-notion-fields-data-template' ).html() ),
					templateFieldsWrapper = jQuery( thisEl.$el ).find( '.flowmattic-notion-action-fields' );

				// If trigger, return false.
				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					return false;
				}

				// If database properties are not available, fetch them.
				if ( '' === window.notionDatabases ) {
					thisEl.updateDatabases( 'manual' );
				}

				templateFieldsWrapper.html( '' );

				var fieldsResponse = {};

				if ( 'undefined' !== typeof window.notionDatabases[ databaseID ] && 'undefined' !== typeof window.notionDatabases[ databaseID ].properties ) {
					fieldsResponse.templateFields = window.notionDatabases[ databaseID ].properties;
					fieldsResponse.templateID = databaseID;
					fieldsResponse.modificationTemplate = 'Properties';
					fieldsResponse.actionAppArgs  = thisEl.model.get( 'actionAppArgs' );
					templateFieldsWrapper.html( notionFieldsTemplate( fieldsResponse ) );

					// Update peoples options.
					if ( thisEl.$el.find( '.people_id_input' ).length ) {
						thisEl.updatePeoples( 'manual' );
					}

					// Update items options.
					if ( thisEl.$el.find( '[name="item_id"]' ).length ) {
						thisEl.updateItems( 'manual' );
					}

					// Update parent pages options.
					if ( thisEl.$el.find( '[name="parent_page_id"]' ).length ) {
						thisEl.updateParentPages( 'manual' );
					}

					templateFieldsWrapper.find( 'select' ).selectpicker();
				}
			},

			generateDynamicFieldsHTML: function( application, currentInput ) {
				var databasesDropdownHTML = '',
					peoplesDropdownHTML = '',
					pagesDropdownHTML = '',
					itemsDropdownHTML = '';

				// Databases.
				if ( application === this.model.get( 'application' ) && currentInput[0].name === 'database_id' && window.dynamicFieldOptionsHTML.indexOf( 'label="Databases"' ) === -1 ) {
					databasesDropdownHTML = '<optgroup label="Databases" data-max-options="1">' + window.notionDatabasesOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = databasesDropdownHTML + window.dynamicFieldOptionsHTML;
				}

				// Items.
				if ( application === this.model.get( 'application' ) && currentInput[0].name === 'item_id' && window.dynamicFieldOptionsHTML.indexOf( 'label="Items"' ) === -1 ) {
					itemsDropdownHTML = '<optgroup label="Items" data-max-options="1">' + window.notionItemsOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = itemsDropdownHTML + window.dynamicFieldOptionsHTML;
				}

				// Pages.
				if ( application === this.model.get( 'application' ) && currentInput[0].name === 'parent_page_id' && window.dynamicFieldOptionsHTML.indexOf( 'label="Pages"' ) === -1 ) {
					pagesDropdownHTML = '<optgroup label="Pages" data-max-options="1">' + window.notionParentPagesOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = pagesDropdownHTML + window.dynamicFieldOptionsHTML;
				}

				// Users.
				if ( application === this.model.get( 'application' ) && jQuery( currentInput ).hasClass( 'people_id_input' ) && window.dynamicFieldOptionsHTML.indexOf( 'label="Users"' ) === -1 ) {
					peoplesDropdownHTML = '<optgroup label="Users" data-max-options="1">' + window.notionPeoplesOptions + '</optgroup>';
					window.dynamicFieldOptionsHTML = peoplesDropdownHTML + window.dynamicFieldOptionsHTML;
				}
			}
		} );
	} );
}( jQuery ) );
