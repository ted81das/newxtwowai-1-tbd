/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger cloudflare View.
		FlowMatticWorkflow.CloudflareView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-cloudflare-action-data-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),
			events: {
				'click .refresh-cloudflare-accounts': 'captureCloudflareAccounts',
				'click .refresh-cloudflare-zones': 'captureCloudflareAccountZones',
				'click .refresh-cloudflare-dns': 'captureCloudflareZoneDNS',
				'change input[name="api_key"]': 'saveApiKey',
				'blur input[name="api_key"]': 'saveApiKey',
				'change input[name="email"]': 'saveEmail',
				'blur input[name="email"]': 'saveEmail',
				'change input[name="account_id"]': 'saveAccountID',
				'blur input[name="account_id"]': 'saveAccountID',
				'change input[name="zone_id"]': 'saveZoneID',
				'blur input[name="zone_id"]': 'saveZoneID'
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Set handler to avoid multiple ajax accounts.
				window.cloudflareAjax = false;

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );
			},

			render: function() {
				var thisEl = this,
					applicationSettings = {},
					submissionData = {},
					appAction = this.model.get( 'action' ),
					actionTemplate;

				// Set the modal api key.
				if ( 'undefined' !== typeof this.model.get( 'actionAppArgs' ) ) {
					actionAppArgs = this.model.get( 'actionAppArgs' );
					this.model.set( 'api_key', actionAppArgs.api_key );
					this.model.set( 'email', actionAppArgs.email );
					window.cfApiKey = actionAppArgs.api_key;
					window.cfEmail = actionAppArgs.email;

					if ( 'undefined' !== typeof actionAppArgs.account_id ) {
						window.cfAccountID = actionAppArgs.account_id;
					}

					if ( 'undefined' !== typeof actionAppArgs.zone_id ) {
						window.cfZoneID = actionAppArgs.zone_id;
					}
				}

				if ( '' !== appAction ) {
					this.$el.html( this.template( this.model.toJSON() ) );
				}

				if ( jQuery( '#flowmattic-application-cloudflare-' + appAction + '-data-template' ).length ) {
					actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-application-cloudflare-' + appAction + '-data-template' ).html() );
					jQuery( this.$el ).find( '.fm-cloudflare-app-fields' ).html( actionTemplate( this.model.toJSON() ) );
				}

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				return this;
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-cloudflare-form-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-cloudflare-form-data' ).show();
				}
			},

			captureCloudflareAccounts: function( event ) {
				var thisEl = this,
					application = this.model.get( 'application' ),
					templates = '',
					documents = '',
					dropdownEvents = '',
					actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail
					},
					asset = '';

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				if ( 'undefined' === typeof window.cfApiKey ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'API Key Missing!',
							text: 'Please provide Cloudflare API Key.',
							icon: 'error',
							showConfirmButton: true,
							timer: 2500
						}
					);

					return false;
				}

				if ( 'undefined' === typeof window.cfEmail ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Email Missing!',
							text: 'Please provide Cloudflare Account Email.',
							icon: 'error',
							showConfirmButton: true,
							timer: 2500
						}
					);

					return false;
				}

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Getting Cloudflare Accounts',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();

							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_get_cloudflare_accounts', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										response = JSON.parse( response );

										_.each( response.result, function( account, index ) {
											dropdownEvents += '<option value="' + account.id + '" data-subtext="ID: ' + account.id + '">' + account.name + '</option>';
										} );

										window.cloudflareAccounts = dropdownEvents;

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Accounts Retrieved!',
												text: 'Cloudflare accounts are successfully fetched.',
												icon: 'success',
												showConfirmButton: true,
												timer: 2500
											}
										);

										window.cloudflareAjax = false;
									}
								}
							);
						}
					}
				);
			},

			captureCloudflareAccountsSilently: function() {
				var actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail
					},
					dropdownEvents;

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_get_cloudflare_accounts', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							response = JSON.parse( response );

							_.each( response.result, function( account, index ) {
								dropdownEvents += '<option value="' + account.id + '" data-subtext="ID: ' + account.id + '">' + account.name + '</option>';
							} );

							window.cloudflareAccounts = dropdownEvents;
						}
					}
				);
			},

			captureCloudflareAccountZones: function( event ) {
				var thisEl = this,
					application = this.model.get( 'application' ),
					templates = '',
					documents = '',
					dropdownEvents = '',
					actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail,
						account_id: window.cfAccountID
					},
					asset = '';

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				if ( 'undefined' === typeof window.cfAccountID ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Account ID Missing!',
							text: 'Please select Cloudflare Account.',
							icon: 'error',
							showConfirmButton: true,
							timer: 2500
						}
					);

					return false;
				}

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Getting Cloudflare Zones',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();

							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_get_cloudflare_zones', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										response = JSON.parse( response );

										_.each( response.result, function( account, index ) {
											dropdownEvents += '<option value="' + account.id + '" data-subtext="ID: ' + account.id + '">' + account.name + '</option>';
										} );

										window.cloudflareAccountZones = dropdownEvents;

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'Account Zones Retrieved!',
												text: 'Cloudflare account zones are successfully fetched.',
												icon: 'success',
												showConfirmButton: true,
												timer: 2500
											}
										);

										window.cloudflareAjax = false;
									}
								}
							);
						}
					}
				);
			},

			captureCloudflareAccountZonesSilently: function() {
				var actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail,
						account_id: window.cfAccountID
					},
					dropdownEvents;

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_get_cloudflare_zones', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							response = JSON.parse( response );

							_.each( response.result, function( account, index ) {
								dropdownEvents += '<option value="' + account.id + '" data-subtext="ID: ' + account.id + '">' + account.name + '</option>';
							} );

							window.cloudflareAccountZones = dropdownEvents;
						}
					}
				);
			},

			captureCloudflareZoneDNS: function( event ) {
				var thisEl = this,
					application = this.model.get( 'application' ),
					templates = '',
					documents = '',
					dropdownEvents = '',
					actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail,
						account_id: window.cfAccountID,
						zone_id: window.cfZoneID
					},
					asset = '';

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				if ( 'undefined' === typeof window.cfZoneID ) {
					thisEl.swalWithBootstrapButtons.fire(
						{
							title: 'Zone ID Missing!',
							text: 'Please select Cloudflare Domain Zone.',
							icon: 'error',
							showConfirmButton: true,
							timer: 2500
						}
					);

					return false;
				}

				thisEl.swalWithBootstrapButtons.fire(
					{
						title: 'Getting Domain DNS Records',
						showConfirmButton: false,
						didOpen: () => {
							thisEl.swalWithBootstrapButtons.showLoading();

							jQuery.ajax(
								{
									url: ajaxurl,
									type: 'POST',
									data: { action: 'flowmattic_get_cloudflare_zone_dns', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
									success: function( response ) {
										response = JSON.parse( response );

										_.each( response.result, function( dns, index ) {
											dropdownEvents += '<option value="' + dns.id + '" data-subtext="ID: ' + dns.id + '">' + dns.name + ' ( ' + dns.type + ' )' + '</option>';
										} );

										window.cloudflareAccountZoneDNS = dropdownEvents;

										thisEl.swalWithBootstrapButtons.fire(
											{
												title: 'DNS Records Retrieved!',
												text: 'Selected domain zone DNS records are successfully fetched.',
												icon: 'success',
												showConfirmButton: true,
												timer: 2500
											}
										);

										window.cloudflareAjax = false;
									}
								}
							);
						}
					}
				);
			},

			captureCloudflareZoneDNSSilently: function() {
				var actionAppArgs = {
						api_key: window.cfApiKey,
						email: window.cfEmail,
						account_id: window.cfAccountID,
						zone_id: window.cfZoneID
					},
					dropdownEvents;

				const settings = {
					settings: {
						actionAppArgs: actionAppArgs
					}
				};

				jQuery.ajax(
					{
						url: ajaxurl,
						type: 'POST',
						data: { action: 'flowmattic_get_cloudflare_zone_dns', settings: settings, workflow_nonce: flowMatticAppConfig.workflow_nonce },
						success: function( response ) {
							response = JSON.parse( response );

							_.each( response.result, function( dns, index ) {
								dropdownEvents += '<option value="' + dns.id + '" data-subtext="ID: ' + dns.id + '">' + dns.name + ' ( ' + dns.type + ' )' + '</option>';
							} );

							window.cloudflareAccountZoneDNS = dropdownEvents;
						}
					}
				);
			},

			generateDynamicFieldsHTML: function( application, currentInput, stepID ) {
				var accountsHTML = '',
					zonesHTML = '',
					dnsHTML = '';

				if ( stepID !== this.model.get( 'stepID' ) ) {
					return false;
				}

				if ( 'undefined' !== typeof window.cloudflareAccounts && application === this.model.get( 'application' ) && 'account_id' === currentInput[0].name && -1 === window.dynamicFieldOptionsHTML.indexOf( 'Cloudflare Accounts Live Sync' ) ) {
					accountsHTML = '<optgroup label="Cloudflare Accounts Live Sync" data-max-options="1">' + window.cloudflareAccounts + '</optgroup>';
					window.dynamicFieldOptionsHTML = accountsHTML;

					return false;
				}

				if ( 'undefined' !== typeof window.cloudflareAccountZones && application === this.model.get( 'application' ) && 'zone_id' === currentInput[0].name && -1 === window.dynamicFieldOptionsHTML.indexOf( 'Cloudflare Zones Live Sync' ) ) {
					zonesHTML = '<optgroup label="Cloudflare Zones Live Sync" data-max-options="1">' + window.cloudflareAccountZones + '</optgroup>';
					window.dynamicFieldOptionsHTML = zonesHTML;

					return false;
				}

				if ( 'undefined' !== typeof window.cloudflareAccountZoneDNS && application === this.model.get( 'application' ) && 'dns_id' === currentInput[0].name && -1 === window.dynamicFieldOptionsHTML.indexOf( 'Cloudflare Domain DNS Live Sync' ) ) {
					dnsHTML = '<optgroup label="Cloudflare Domain DNS Live Sync" data-max-options="1">' + window.cloudflareAccountZoneDNS + '</optgroup>';
					window.dynamicFieldOptionsHTML = dnsHTML;

					return false;
				}

				if ( window.cloudflareAjax ) {
					return false;
				}

				if ( 'account_id' === currentInput[0].name || 'zone_id' === currentInput[0].name || 'dns_id' === currentInput[0].name ) {
					window.cloudflareAjax = true;
				}

				if ( 'undefined' === typeof window.cloudflareAccounts && 'account_id' === currentInput[0].name ) {
					window.dynamicFieldOptionsHTML = '';
					if ( ( 'undefined' !== typeof window.cfApiKey && '' !== window.cfApiKey.trim() ) && ( 'undefined' !== typeof window.cfEmail && '' !== window.cfEmail.trim() ) ) {
						this.captureCloudflareAccounts();
					}
				}

				if ( 'undefined' === typeof window.cloudflareAccountZones && 'zone_id' === currentInput[0].name ) {
					window.dynamicFieldOptionsHTML = '';
					if ( 'undefined' !== typeof window.cfAccountID && '' !== window.cfAccountID.trim() ) {
						this.captureCloudflareAccountZones();
					}
				}

				if ( 'undefined' === typeof window.cloudflareAccountZoneDNS && 'dns_id' === currentInput[0].name ) {
					window.dynamicFieldOptionsHTML = '';
					if ( 'undefined' !== typeof window.cfZoneID && '' !== window.cfZoneID.trim() ) {
						this.captureCloudflareZoneDNS();
					}
				}
			},

			saveApiKey: function() {
				var apiKey = jQuery( event.target ).val();

				// Set value for this model.
				this.model.set( 'api_key', apiKey );
				window.cfApiKey = apiKey;

				if ( ( 'undefined' !== typeof window.cfApiKey && '' !== window.cfApiKey.trim() ) && ( 'undefined' !== typeof window.cfEmail && '' !== window.cfEmail.trim() ) ) {
					this.captureCloudflareAccountsSilently();
				}

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'api_key', apiKey, this );
			},

			saveEmail: function() {
				var email = jQuery( event.target ).val();

				// Set value for this model.
				this.model.set( 'email', email );
				window.cfEmail = email;

				if ( ( 'undefined' !== typeof window.cfApiKey && '' !== window.cfApiKey.trim() ) && ( 'undefined' !== typeof window.cfEmail && '' !== window.cfEmail.trim() ) ) {
					this.captureCloudflareAccountsSilently();
				}

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'email', email, this );
			},

			saveAccountID: function() {
				var account_id = jQuery( event.target ).val();

				// Set value for this model.
				this.model.set( 'account_id', account_id );
				window.cfAccountID = account_id;

				if ( 'undefined' !== typeof window.cfAccountID && '' !== window.cfAccountID.trim() ) {
					this.captureCloudflareAccountZonesSilently();
				}

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'account_id', account_id, this );
			},

			saveZoneID: function() {
				var zone_id = jQuery( event.target ).val();

				// Set value for this model.
				this.model.set( 'zone_id', zone_id );
				window.cfZoneID = zone_id;

				if ( 'undefined' !== typeof window.cfZoneID && '' !== window.cfZoneID.trim() ) {
					this.captureCloudflareZoneDNSSilently();
				}

				// Set parent model attribute.
				FlowMatticWorkflowEvents.trigger( 'actionAppDataUpdateSingleAttribute', 'zone_id', zone_id, this );
			}
		} );
	} );
}( jQuery ) );
