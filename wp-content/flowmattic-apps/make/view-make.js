/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger make View.
		FlowMatticWorkflow.MakeView = Backbone.View.extend( {
			triggerTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-make-trigger-data-template' ).html() ),
			actionTemplate: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-make-action-data-template' ).html() ),

			events: {
			},

			initialize: function() {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function() {
				var thisEl = this;

				if ( 'undefined' === typeof thisEl.model.get( 'actionAppArgs' ) ) {
					thisEl.model.set( 'actionAppArgs', [] );
				}

				if ( 'trigger' === thisEl.model.get( 'type' ) ) {
					thisEl.$el.html( thisEl.triggerTemplate( thisEl.model.toJSON() ) );
					thisEl.setTriggerOptions();
				} else {
					thisEl.$el.html( thisEl.actionTemplate( thisEl.model.toJSON() ) );
					thisEl.setActionOptions();
				}

				thisEl.$el.find( 'select' ).selectpicker();

				return this;
			},

			setTriggerOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-make-trigger-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-make-trigger-data' ).show();
				}
			},

			setActionOptions: function() {
				var elements = jQuery( this.$el ).find( '.flowmattic-make-action-data' ),
					currentAction = this.model.get( 'action' );

				elements.hide();

				if ( '' !== currentAction ) {
					jQuery( this.$el ).find( '.flowmattic-make-action-data' ).show();
				}
			}
		} );
	} );
}( jQuery ) );
