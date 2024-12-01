/* global FlowMatticWorkflow, FlowMatticWorkflowEvents, FlowMatticWorkflowApp, FlowMatticWorkflowSteps */
var FlowMatticWorkflow = FlowMatticWorkflow || {};

(function ($) {

	jQuery(document).ready(function () {
		// Workflow Trigger Jetformbuilder View.
		FlowMatticWorkflow.JetformbuilderView = Backbone.View.extend({
			template: FlowMatticWorkflow.template(jQuery('#flowmattic-application-jetformbuilder-data-template').html()),

			events: {
			},

			initialize: function () {
				// Unset the previous captured data.
				window.captureData = false;
			},

			render: function () {
				var thisEl = this;

				thisEl.$el.html(thisEl.template(thisEl.model.toJSON()));

				thisEl.$el.find('select').selectpicker();

				thisEl.setFormOptions();

				return this;
			},

			setFormOptions: function () {
				var elements = jQuery(this.$el).find('.flowmattic-jetformbuilder-form-data'),
					currentFormAction = this.model.get('action');

				elements.hide();

				if ('' !== currentFormAction) {
					jQuery(this.$el).find('.flowmattic-jetformbuilder-form-data').show();
				}
			}
		});
	});
}(jQuery));
