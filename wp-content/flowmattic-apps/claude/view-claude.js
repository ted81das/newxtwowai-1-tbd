/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Action Claude View.
		FlowMatticWorkflow.ClaudeView = FlowMatticWorkflow.ActionView.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-claude-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var appAction = this.model.get( 'action' );

				if ( '' !== appAction ) {
					this.$el.html( this.template( this.model.toJSON() ) );

					if ( jQuery( '#flowmattic-claude-' + appAction + '-data-template' ).length ) {
						actionTemplate = FlowMatticWorkflow.template( jQuery( '#flowmattic-claude-' + appAction + '-data-template' ).html() );
						jQuery( this.$el ).find( '.flowmattic-claude-action-fields' ).html( actionTemplate( this.model.toJSON() ) );
					}

					this.$el.find( 'select' ).selectpicker();
				}

				this.$el.find( 'select' ).selectpicker();

				return this;
			}
		} );
	} );
}( jQuery ) );
