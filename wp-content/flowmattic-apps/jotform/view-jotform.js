/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger jotform View.
		FlowMatticWorkflow.JotformView = Backbone.View.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-jotform-data-template' ).html() ),

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

				this.updateFormSubmissionData( this.model.toJSON() );

				this.$el.find( 'select' ).selectpicker();

				this.setFormOptions();

				return this;
			},

			updateFormSubmissionData: function( response ) {
				var responseTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-jotform-response-template' ).html() );

				if ( 'undefined' !== typeof response.capturedData ) {
					this.$el.find( '.fm-webhook-capture-data' ).html( responseTemplate( response ) );
				}

				FlowMatticWorkflowEvents.trigger( 'saveWorkflowDraft' );
			},

			setFormOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-jotform-form-data' ),
					currentFormAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentFormAction ) {
					jQuery( this.$el ).find( '.flowmattic-jotform-form-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
