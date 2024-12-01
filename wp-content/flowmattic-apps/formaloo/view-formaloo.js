/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger formaloo View.
		FlowMatticWorkflow.FormalooView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-formaloo-data-template' ).html() ),

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

				this.$el.html( this.template( this.model.toJSON() ) );

				this.updateLeadData( this.model.toJSON() );

				this.$el.find( 'select' ).selectpicker();

				this.setLeadOptions();

				return this;
			},

			updateLeadData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-formaloo-response-template' ).html() );

				if ( 'undefined' !== typeof response.capturedData ) {
					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( response ) );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setLeadOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-formaloo-lead-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-formaloo-lead-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
