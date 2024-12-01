/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

( function( $ ) {

	jQuery( document ).ready( function() {
		// Workflow Trigger Emaillistvalidation View.
		FlowMatticWorkflow.EmaillistvalidationView = FlowMatticWorkflow.ActionView.extend( {
			template: FlowMatticWorkflow.template( jQuery( '#flowmattic-application-emaillistvalidation-data-template' ).html() ),
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
			},

			render: function() {
				var appAction = this.model.get( 'action' ),
					capturedData,
					submissionData = {};

				if ( '' !== appAction ) {
					this.$el.html( this.template( this.model.toJSON() ) );
					this.$el.find( 'select' ).selectpicker();
				}

				if ( 'undefined' !== typeof this.model.get( 'capturedData' ) ) {
					capturedData = this.model.get( 'capturedData' );
					submissionData.capturedData = capturedData;
					submissionData.stepID = this.model.get( 'stepID' );

					FlowMatticWorkflowEvents.trigger( 'eventResponseReceived', submissionData, submissionData.stepID );
				}

				return this;
			}
		} );
	} );
}( jQuery ) );
