/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger n8n View.
		FlowMatticWorkflow.N8nView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-n8n-trigger-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-n8n-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this,
					applicationSettings = {},
					submissionData = {};

				if ( 'undefined' === typeof this.model.get( 'actionAppArgs' ) ) {
					this.model.set( 'actionAppArgs', [] );
				}

				if ( 'trigger' === this.model.get( 'type' ) ) {
					this.$el.html( this.triggerTemplate( this.model.toJSON() ) );
					this.updateWebhookData( this.model.toJSON() );
					this.setTriggerOptions();
				} else {
					this.$el.html( this.actionTemplate( this.model.toJSON() ) );
					this.setActionOptions();
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			},

			updateWebhookData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-n8n-response-template' ).html() );

				if ( 'undefined' !== typeof response.capturedData ) {
					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( response ) );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-n8n-trigger-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-n8n-trigger-data' ).show();
				}
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-n8n-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-n8n-action-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
