<?php

namespace ZionBuilderPro\Repeater;

use ZionBuilderPro\Repeater;
use ZionBuilder\Plugin as FreePlugin;
use ZionBuilderPro\Plugin;
use ZionBuilder\Elements\Style;

class RepeaterElement {
	private $element           = null;
	private $repeater_provider = null;

	public function __construct( $element ) {
		$this->element = $element;
	}

	public function render_element( $extra_data ) {
		$element_instance = $this->element;

		// If this is a repeater provider, set the query
		if ( Repeater::is_repeater_provider( $element_instance ) ) {
			$provider_config = Repeater::get_repeater_provider_config( $element_instance );
			Plugin::$instance->repeater->set_active_provider( $provider_config );
		}

		// Check to see if this is the main repeater consumer
		if ( Repeater::is_repeater_consumer( $element_instance ) ) {
			$active_provider = Plugin::$instance->repeater->get_active_provider();
			if ( ! $active_provider ) {
				return;
			}

			// Set current loop
			$consumer_config = Repeater::get_repeater_consumer_config( $element_instance );
			$active_provider->start_loop( $consumer_config );

			$index = 0;

			while ( $active_provider->have_items() ) {
				$active_provider->the_item();

				$cloned_element = $this->setup_repeated_element( $element_instance, $index );
				$cloned_element->render_element( $extra_data );

				$active_provider->next();
				$active_provider->reset_item();
				$index++;
			}

			// Reset consumer
			$active_provider->stop_loop();
		} else {
			// This can only be a repeater provider. We just need to set the provider data and render normally
			$this->element->do_element_render( $extra_data );
		}

		// If this is a repeater provider, reset the query
		if ( Repeater::is_repeater_provider( $element_instance ) ) {
			Plugin::$instance->repeater->reset_active_provider();
		}
	}

	/**
	 * Change all repeated element instances and replace HTML ids with css classes
	 *
	 * @param Element $element_instance
	 * @param integer $index
	 *
	 * @return Element
	 */
	private function setup_repeated_element( $element_instance, $index ) {
		$element_css_id = $element_instance->get_element_css_id();
		$css_class      = sprintf( '%s_%s', $element_css_id, $index );

		// Create a clone
		$element_data            = $element_instance->data;

		$element_data['uid']     = $css_class;
		$cloned_element_instance = FreePlugin::instance()->renderer->register_element_instance( $element_data );
		$cloned_element_instance->is_clone = true;

		$clone_children = $cloned_element_instance->get_children();
		if ( is_array( $clone_children ) ) {
			foreach ( $clone_children as $child_index => $child_element ) {
				$child_element_instance = FreePlugin::instance()->renderer->get_element_instance( $child_element['uid'] );

				if ($child_element_instance) {
					$cloned_child                                   = $this->setup_repeated_element( $child_element_instance, $index );
					$cloned_element_instance->content[$child_index] = $cloned_child->data;
				}
			}
		}

		// Set CSS class
		$cloned_element_instance->render_attributes->add( 'wrapper', 'class', $element_css_id );
		$cloned_element_instance->render_attributes->add( 'wrapper', 'class', $css_class );
		$cloned_element_instance->render_attributes->add( 'wrapper', 'id', $css_class );

		// Check for dynamic background image
		if ( $cloned_element_instance->options->get_value( '_styles.wrapper.styles.default.default.__dynamic_content__.background-image', null ) ) {
			$background_styles        = $cloned_element_instance->options->get_value( '_styles.wrapper.styles.default.default' );

			// Check for background gradient. We also need to add the gradient inline in order for this to work properly with css blend mode
			$background_gradient_data = $cloned_element_instance->options->get_value( '_styles.wrapper.styles.default.default.background-gradient', null );
			$styles_with_dynamic_data = Plugin::instance()->dynamic_content_manager->apply_dynamic_content( $background_styles );

			if ( ! empty( $styles_with_dynamic_data['background-image'] ) ) {
				$background_image_config = [];
				$gradient_config = Style::compile_gradient( $background_gradient_data );

				// Add the gradient
				if ( ! empty( $gradient_config ) ) {
					$background_image_config[] = $gradient_config;
				}

				$background_image_config[] = "url('{$styles_with_dynamic_data['background-image']}')";
				$background_value = implode( ', ', $background_image_config );

				$cloned_element_instance->render_attributes->add( 'wrapper', 'style', "background-image: {$background_value}" );
			}
		}

		return $cloned_element_instance;
	}
}
