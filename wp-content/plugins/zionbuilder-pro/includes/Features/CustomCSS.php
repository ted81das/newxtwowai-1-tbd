<?php

namespace ZionBuilderPro\Features;

class CustomCSS {
	public function __construct() {
		add_action( 'zionbuilder/schema/advanced_options', [ $this, 'add_custom_css_options' ] );
		add_filter( 'zionbuilder/element/custom_css', [ $this, 'apply_custom_css' ], 10, 3 );
		add_filter( 'zionbuilder/schema/style_options', [ $this, 'add_element_custom_css' ], 10 );

		
	}

	public function add_element_custom_css( $options ) {
		$custom_css_group = $options->get_option( '_styles.custom_css' );

		$custom_css_group->replace_option(
			'custom_css',
			[
				'type'        => 'code',
				'description' => esc_html__( 'Add extra css that will be applied to this selector.', 'zionbuilder' ),
				'title'       => esc_html__( 'Custom css', 'zionbuilder-pro' ),
				'mode'        => 'text/css',
				'css_style'   => [
					[
						'value' => '{{VALUE}}',
					],
				],
			]
		);

		// Remove the PRO label
		unset($custom_css_group->label);

		return $options;
	}

	public function add_custom_css_options( $options ) {
		$custom_css_group = $options->get_option( 'custom-css-group' );

		$custom_css_group->replace_option(
			'_custom_css',
			[
				'type'        => 'code',
				'description' => __( 'Add extra css that will be applied to this element. "[ELEMENT]" can be used to select the current item', 'zionbuilder-pro' ),
				'title'       => esc_html__( 'Custom CSS', 'zionbuilder-pro' ),
				'mode'        => 'text/css',
				'placeholder' => esc_html__( '[ELEMENT] { Your code here }', 'zionbuilder-pro' ),
			]
		);
	}

	public function apply_custom_css( $css, $options, $element_instance ) {
		$custom_css = $options->get_value( '_advanced_options._custom_css' );

		if ( ! empty( $custom_css ) ) {
			$css .= str_replace( '[ELEMENT]', '#' . $element_instance->get_element_css_id(), $custom_css );
		}

		return $css;
	}
}
