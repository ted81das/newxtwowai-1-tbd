/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Lemon Squeezy View.
		FlowMatticWorkflow.Lemon_SqueezyView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-lemon-squeezy-trigger-data-template' ).html() ),
			swalWithBootstrapButtons: window.Swal.mixin({
				customClass: {
					confirmButton: 'btn btn-primary shadow-none me-xxl-3',
					cancelButton: 'btn btn-danger shadow-none'
				},
				buttonsStyling: false
			} ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;

				// Listem to dynamic field popup, and append the custom data from API.
				this.listenTo( FlowMatticWorkflowEvents, 'generateDynamicFieldsHTML', this.generateDynamicFieldsHTML );

				this.listenTo( FlowMatticWorkflowEvents, 'appTriggerChanged', this.updateEventInInstructions );
			},

			render: function() {
				var thisEl = this,
					appAction = thisEl.model.get( 'action' ),
					appTriggerTemplate;

				if ( 'undefined' === typeof thisEl.model.get( 'actionAppArgs' ) ) {
					let actionAppArgs = {
						include: ''
					};

					thisEl.model.set( 'actionAppArgs', actionAppArgs );
				}

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					thisEl.$el.html( thisEl.triggerTemplate( thisEl.model.toJSON() ) );
					
					if ( jQuery( '#lemon-squeezy-trigger-' + appAction + '-data-template' ).length ) {
						appTriggerTemplate = FlowMatticWorkflow.template( jQuery( '#lemon-squeezy-trigger-' + appAction + '-data-template' ).html() );
						jQuery( thisEl.$el ).find( '.lemon-squeezy-trigger-data' ).html( appTriggerTemplate( thisEl.model.toJSON() ) );
					}

					thisEl.setTriggerOptions();
				}

				thisEl.$el.find( 'select' ).selectpicker();

				return thisEl;
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-lemon-squeezy-trigger-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-lemon-squeezy-trigger-data' ).show();
				}
			},

			updateEventInInstructions: function( event ) {
				var eventTitle = '';

				if ( 'undefined' !== typeof otherTriggerApps['lemon_squeezy'].triggers[ event ] ) {
					eventTitle = otherTriggerApps['lemon_squeezy'].triggers[ event ].title;
					this.$el.find( '.lemon-squeezy-event-selected' ).html( event );
				}
			}
		} );
	} );
}( jQuery ) );