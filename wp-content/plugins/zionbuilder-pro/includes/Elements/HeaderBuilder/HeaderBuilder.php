<?php

namespace ZionBuilderPro\Elements\HeaderBuilder;

use ZionBuilder\Elements\Element;
use ZionBuilderPro\Plugin;
use ZionBuilder\Options\Helpers;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * The header builder element allows you to easily create headers for your website
 */
class HeaderBuilder extends Element {

	/**
	 * Get name
	 *
	 * Returns the name for the element
	 *
	 * @return string The element name
	 */
	public function get_name() {
		return __( 'Header builder', 'zionbuilder-pro' );
	}

	/**
	 * Get type
	 *
	 * Returns the unique id for the element
	 *
	 * @return string The element id/type
	 */
	public function get_type() {
		return 'zion_header_builder';
	}

	/**
	 * Is wrapper
	 *
	 * Returns true if the element can contain other elements ( f.e. section, column )
	 *
	 * @return boolean The element icon
	 */
	public function is_wrapper() {
		return true;
	}

	/**
	 * Registers the element options
	 *
	 * @param \ZionBuilder\Options\Options $options The Options instance
	 *
	 * @return void
	 */
	public function options( $options ) {
		$options->add_option(
			'tag',
			[
				'type'        => 'select',
				'default'     => 'header',
				'description' => esc_html__( 'Select the HTML tag to use for this element. If you want to add a custom tag, make sure to only use letters and numbers', 'zionbuilder-pro' ),
				'title'       => esc_html__( 'HTML tag', 'zionbuilder-pro' ),
				'addable'     => true,
				'filterable'  => true,
				'options'     => [
					[
						'id'   => 'section',
						'name' => 'Section',
					],
					[
						'id'   => 'div',
						'name' => 'Div',
					],
					[
						'id'   => 'footer',
						'name' => 'Footer',
					],
					[
						'id'   => 'header',
						'name' => 'Header',
					],
					[
						'id'   => 'article',
						'name' => 'Article',
					],
					[
						'id'   => 'main',
						'name' => 'Main',
					],
					[
						'id'   => 'aside',
						'name' => 'Aside',
					],
				],
			]
		);

		$background = $options->add_group(
			'background',
			[
				'type'      => 'panel_accordion',
				'title'     => __( 'Background', 'zionbuilder-pro' ),
				'collapsed' => true,
			]
		);

		$background->add_group(
			'background-config',
			[
				'type' => 'background',
				'sync' => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default',
			]
		);

		$spacing_group = $options->add_group(
			'size_and_spacings',
			[
				'type'      => 'panel_accordion',
				'title'     => esc_html__( 'Spacing', 'zionbuilder-pro' ),
				'collapsed' => true,
			]
		);

		$spacing_group->add_group(
			'margin',
			[
				'title'          => esc_html__( 'Margin', 'zionbuilder-pro' ),
				'type'           => 'box_model',
				'position-type'  => 'margin',
				'position-title' => esc_html__( 'Margin', 'zionbuilder-pro' ),
				'sync'           => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default',
			]
		);

		$spacing_group->add_group(
			'padding',
			[
				'title'          => esc_html__( 'Padding', 'zionbuilder-pro' ),
				'type'           => 'box_model',
				'position-type'  => 'padding',
				'position-title' => esc_html__( 'Padding', 'zionbuilder-pro' ),
				'sync'           => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default',
			]
		);

		$sizing_group = $options->add_group(
			'size',
			[
				'type'      => 'panel_accordion',
				'title'     => __( 'Size', 'zionbuilder-pro' ),
				'collapsed' => true,
			]
		);

		$sizing_group->add_option(
			'width',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Width', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.width',
			]
		);

		$sizing_group->add_option(
			'min-width',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Min Width', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.min-width',
			]
		);

		$sizing_group->add_option(
			'max-width',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Max Width', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.max-width',
			]
		);

		$sizing_group->add_option(
			'height',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Height', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.height',
			]
		);

		$sizing_group->add_option(
			'min-height',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Min Height', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.min-height',
			]
		);

		$sizing_group->add_option(
			'max-height',
			[
				'type'  => 'number_unit',
				'title' => esc_html__( 'Max Height', 'zionbuilder-pro' ),
				'width' => 33.3,
				'min'   => 0,
				'sync'  => '_styles.wrapper.styles.%%RESPONSIVE_DEVICE%%.default.max-height',
			]
		);

		$sticky_header_group = $options->add_group(
			'sticky_options_group',
			[
				'type'      => 'panel_accordion',
				'title'     => esc_html__( 'Sticky', 'zionbuilder-pro' ),
				'collapsed' => true,
			]
		);

		$sticky_header_group->add_option(
			'enable_sticky_header',
			[
				'type'    => 'checkbox_switch',
				'columns' => 2,
				'title'   => esc_html__( 'Enable sticky header', 'zionbuilder-pro' ),
				'default' => false,
				'layout'  => 'inline',
			]
		);

		$sticky_header_group->add_option(
			'sticky_header_position',
			[
				'type'             => 'select',
				'columns'          => 2,
				'title'            => esc_html__( 'Sticky header position', 'zionbuilder-pro' ),
				'layout'           => 'inline',
				'default'          => 'top',
				'options'          => [
					[
						'id'   => 'top',
						'name' => 'top',
					],
					[
						'id'   => 'bottom',
						'name' => 'bottom',
					],
				],
				'render_attribute' => [
					[
						'tag_id'    => 'wrapper',
						'attribute' => 'class',
						'value'     => 'zb-headerSticky--{{VALUE}}',
					],
				],
			]
		);

		$sticky_header_group->add_option(
			'sticky_threshold',
			[
				'type'        => 'number',
				'columns'     => 2,
				'title'       => esc_html__( 'Enable sticky after scrolling', 'zionbuilder-pro' ),
				'placeholder' => 0,
				'layout'      => 'inline',
			]
		);

		$sticky_header_group->add_option(
			'sticky_appear_animation',
			[
				'type'        => 'radio_image',
				'title'       => __( 'Appear animation', 'zionbuilder' ),
				'description' => __( 'Set the desired appear animation when the header is sticked.' ),
				'default'     => '',
				'columns'     => 3,
				'use_search'  => true,
				'search_text' => __( 'Search animation', 'zionbuilder' ),
				'options'     => Helpers::get_animations(),
				'layout'      => 'inline',
			]
		);

		// $overlay_options_group = $options->add_group(
		//  'overlay_options_group',
		//  [
		//      'type'      => 'panel_accordion',
		//      'title'     => esc_html__( 'Overlay', 'zionbuilder-pro' ),
		//      'collapsed' => true,
		//  ]
		// );

		// $overlay_options_group->add_option(
		//  'enable_overlay',
		//  [
		//      'type'             => 'checkbox_switch',
		//      'columns'          => 2,
		//      'title'            => esc_html__( 'Enable overlay', 'zionbuilder-pro' ),
		//      'default'          => false,
		//      'layout'           => 'inline',
		//      'render_attribute' => [
		//          [
		//              'tag_id'    => 'wrapper',
		//              'attribute' => 'class',
		//              'value'     => 'zb-headerOverlay',
		//          ],
		//      ],
		//  ]
		// );

		// $overlay_options_group->add_option(
		//  'overlay_bg_color',
		//  [
		//      'type'      => 'colorpicker',
		//      'display'   => 'simple',
		//      'layout'    => 'inline',
		//      'title'     => esc_html__( 'Background color', 'zionbuilder-pro' ),
		//      'css_style' => [
		//          [
		//              'selector' => '{{ELEMENT}}.zb-headerOverlay',
		//              'value'    => 'background-color: {{VALUE}}',
		//          ],
		//      ],
		//  ]
		// );

		// $overlay_options_group->add_option(
		//  'overlay_z_index',
		//  [
		//      'type'      => 'number',
		//      'title'     => esc_html__( 'Z-index', 'zionbuilder-pro' ),
		//      'layout'    => 'inline',
		//      'css_style' => [
		//          [
		//              'selector' => '{{ELEMENT}}.zb-headerOverlay',
		//              'value'    => 'z-index: {{VALUE}}',
		//          ],
		//      ],
		//  ]
		// );

		// old
		// $options->add_option(
		//  'inner_content_width',
		//  [
		//      'type'             => 'select',
		//      'description'      => esc_html__( 'Select the desired inner content type', 'zionbuilder-pro' ),
		//      'title'            => esc_html__( 'Inner content width', 'zionbuilder-pro' ),
		//      'default'          => '',
		//      'options'          => [
		//          [
		//              'id'   => '',
		//              'name' => esc_html__( 'Boxed', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'full',
		//              'name' => esc_html__( 'Full size', 'zionbuilder-pro' ),
		//          ],
		//      ],
		//      'render_attribute' => [
		//          [
		//              'tag_id'    => 'inner_content_styles',
		//              'attribute' => 'class',
		//              'value'     => 'zb-flex-width--{{VALUE}}',
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'inner_content_width_value',
		//  [
		//      'type'               => 'dynamic_slider',
		//      'description'        => esc_html__( 'Set the desired Inner Content width', 'zionbuilder-pro' ),
		//      'default_step'       => 1,
		//      'default_shift_step' => 5,
		//      'title'              => esc_html__( 'Inner content width', 'zionbuilder-pro' ),
		//      'responsive_options' => true,
		//      'default'            => [
		//          'default' => '1120px',
		//      ],
		//      'options'            => [
		//          [
		//              'min'        => 0,
		//              'max'        => 100,
		//              'step'       => 1,
		//              'shift_step' => 5,
		//              'unit'       => '%',
		//          ],
		//          [
		//              'min'        => 0,
		//              'max'        => 2000,
		//              'step'       => 1,
		//              'shift_step' => 25,
		//              'unit'       => 'px',
		//          ],
		//          [
		//              'unit' => 'auto',
		//          ],
		//      ],
		//      'dependency'         => [
		//          [
		//              'option' => 'inner_content_width',
		//              'value'  => [ '' ],
		//          ],
		//      ],
		//      'css_style'          => [
		//          [
		//              'selector' => '{{ELEMENT}} .zb-section__innerWrapper',
		//              'value'    => 'max-width: {{VALUE}}',
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'columns_gap',
		//  [
		//      'type'               => 'select',
		//      'description'        => esc_html__( 'Set the desired columns gap. This will affect all inner columns.', 'zionbuilder-pro' ),
		//      'title'              => esc_html__( 'Columns gap', 'zionbuilder-pro' ),
		//      'default'            => 'default',
		//      'responsive_options' => true,
		//      'options'            => [
		//          [
		//              'id'   => 'default',
		//              'name' => esc_html__( 'Default (15px)', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'xsmall',
		//              'name' => esc_html__( 'Extra small (5px)', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'small',
		//              'name' => esc_html__( 'Small (10px)', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'large',
		//              'name' => esc_html__( 'Large (25px)', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'xlarge',
		//              'name' => esc_html__( 'Extra Large (40px)', 'zionbuilder-pro' ),
		//          ],
		//          [
		//              'id'   => 'no',
		//              'name' => esc_html__( 'No gap', 'zionbuilder-pro' ),
		//          ],
		//      ],
		//      'render_attribute'   => [
		//          [
		//              'tag_id'    => 'inner_content_styles',
		//              'attribute' => 'class',
		//              'value'     => 'zb-sct-clm-gap{{RESPONSIVE_DEVICE_CSS}}--{{VALUE}}',
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'tag',
		//  [
		//      'type'        => 'select',
		//      'description' => esc_html__( 'Select the HTML tag to use for this element. If you want to add a custom tag, make sure to only use letters and numbers', 'zionbuilder-pro' ),
		//      'title'       => esc_html__( 'HTML tag', 'zionbuilder-pro' ),
		//      'default'     => 'section',
		//      'addable'     => true,
		//      'filterable'  => true,
		//      'options'     => [
		//          [
		//              'id'   => 'section',
		//              'name' => 'Section',
		//          ],
		//          [
		//              'id'   => 'div',
		//              'name' => 'Div',
		//          ],
		//          [
		//              'id'   => 'footer',
		//              'name' => 'Footer',
		//          ],
		//          [
		//              'id'   => 'header',
		//              'name' => 'Header',
		//          ],
		//          [
		//              'id'   => 'article',
		//              'name' => 'Article',
		//          ],
		//          [
		//              'id'   => 'main',
		//              'name' => 'Main',
		//          ],
		//          [
		//              'id'   => 'aside',
		//              'name' => 'Aside',
		//          ],
		//      ],
		//  ]
		// );
		// $options->add_option(
		//  'inner_content_layout',
		//  [
		//      'type'                    => 'custom_selector',
		//      'description'             => esc_html__( 'Select the desired content orientation.', 'zionbuilder-pro' ),
		//      'title'                   => esc_html__( 'Content orientation', 'zionbuilder-pro' ),
		//      'default'                 => 'row',
		//      'show_responsive_buttons' => true,
		//      'sync'                    => '_styles.inner_content_styles.styles.%%RESPONSIVE_DEVICE%%.default.flex-direction',
		//      'options'                 => [
		//          [
		//              'name' => __( 'vertical', 'zionbuilder-pro' ),
		//              'id'   => 'column',
		//          ],
		//          [
		//              'name' => __( 'horizontal', 'zionbuilder-pro' ),
		//              'id'   => 'row',
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'inner_content_column_alignment_horizontal',
		//  [
		//      'type'                    => 'custom_selector',
		//      'description'             => esc_html__( 'Inner content horizontal alignment layout', 'zionbuilder-pro' ),
		//      'title'                   => esc_html__( 'Inner content horizontal alignment', 'zionbuilder-pro' ),
		//      'show_responsive_buttons' => true,
		//      'sync'                    => '_styles.inner_content_styles.styles.%%RESPONSIVE_DEVICE%%.default.align-items',
		//      'options'                 => [
		//          [
		//              'name' => __( 'Left', 'zionbuilder-pro' ),
		//              'id'   => 'flex-start',
		//              'icon' => 'justify-start',
		//          ],
		//          [
		//              'name' => __( 'center', 'zionbuilder-pro' ),
		//              'id'   => 'center',
		//              'icon' => 'justify-center',
		//          ],
		//          [
		//              'name' => __( 'Right', 'zionbuilder-pro' ),
		//              'id'   => 'flex-end',
		//              'icon' => 'justify-end',
		//          ],
		//          [
		//              'name' => __( 'stretch', 'zionbuilder-pro' ),
		//              'id'   => 'stretch',
		//              'icon' => 'align-stretch-reversed',
		//          ],
		//          [
		//              'name' => __( 'baseline', 'zionbuilder-pro' ),
		//              'id'   => 'baseline',
		//              'icon' => 'align-baseline-reversed',
		//          ],
		//      ],
		//      'dependency'              => [
		//          [
		//              'option' => 'inner_content_layout',
		//              'value'  => [ 'column' ],
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'inner_content_column_alignment_vertical',
		//  [
		//      'type'                    => 'custom_selector',
		//      'description'             => esc_html__( 'Inner content vertical alignment layout', 'zionbuilder-pro' ),
		//      'title'                   => esc_html__( 'Inner content vertical alignment', 'zionbuilder-pro' ),
		//      'columns'                 => 5,
		//      'show_responsive_buttons' => true,
		//      'sync'                    => '_styles.inner_content_styles.styles.%%RESPONSIVE_DEVICE%%.default.justify-content',
		//      'options'                 => [
		//          [
		//              'name' => __( 'Top', 'zionbuilder-pro' ),
		//              'id'   => 'flex-start',
		//              'icon' => 'align-start',
		//          ],
		//          [
		//              'name' => __( 'center', 'zionbuilder-pro' ),
		//              'id'   => 'center',
		//              'icon' => 'align-center',
		//          ],
		//          [
		//              'name' => __( 'Bottom', 'zionbuilder-pro' ),
		//              'id'   => 'flex-end',
		//              'icon' => 'align-end',
		//          ],
		//          [
		//              'name' => __( 'space-between', 'zionbuilder-pro' ),
		//              'id'   => 'space-between',
		//              'icon' => 'justify-sp-btw-reverse',
		//          ],
		//          [
		//              'name' => __( 'space-around', 'zionbuilder-pro' ),
		//              'id'   => 'space-around',
		//              'icon' => 'justify-sp-around-reverse',
		//          ],
		//      ],
		//      'dependency'              => [
		//          [
		//              'option' => 'inner_content_layout',
		//              'value'  => [ 'column' ],
		//          ],
		//      ],
		//  ]
		// );
		// $options->add_option(
		//  'inner_content_row_alignment_horizontal',
		//  [
		//      'type'                    => 'custom_selector',
		//      'description'             => esc_html__( 'Inner content horizontal alignment layout', 'zionbuilder-pro' ),
		//      'title'                   => esc_html__( 'Inner content horizontal alignment', 'zionbuilder-pro' ),
		//      'show_responsive_buttons' => true,
		//      'sync'                    => '_styles.inner_content_styles.styles.%%RESPONSIVE_DEVICE%%.default.justify-content',
		//      'options'                 => [
		//          [
		//              'name' => __( 'Left', 'zionbuilder-pro' ),
		//              'id'   => 'flex-start',
		//              'icon' => 'justify-start',
		//          ],
		//          [
		//              'name' => __( 'center', 'zionbuilder-pro' ),
		//              'id'   => 'center',
		//              'icon' => 'justify-center',
		//          ],
		//          [
		//              'name' => __( 'Right', 'zionbuilder-pro' ),
		//              'id'   => 'flex-end',
		//              'icon' => 'justify-end',
		//          ],
		//          [
		//              'name' => __( 'space-between', 'zionbuilder-pro' ),
		//              'id'   => 'space-between',
		//              'icon' => 'justify-sp-btw',
		//          ],
		//          [
		//              'name' => __( 'space-around', 'zionbuilder-pro' ),
		//              'id'   => 'space-around',
		//              'icon' => 'justify-sp-around',
		//          ],
		//      ],
		//      'dependency'              => [
		//          [
		//              'option' => 'inner_content_layout',
		//              'value'  => [ 'row' ],
		//          ],
		//      ],
		//  ]
		// );

		// $options->add_option(
		//  'inner_content_row_alignment_vertical',
		//  [
		//      'type'                    => 'custom_selector',
		//      'description'             => esc_html__( 'Inner content vertical alignment layout', 'zionbuilder-pro' ),
		//      'title'                   => esc_html__( 'Inner content vertical alignment', 'zionbuilder-pro' ),
		//      'columns'                 => 5,
		//      'show_responsive_buttons' => true,
		//      'sync'                    => '_styles.inner_content_styles.styles.%%RESPONSIVE_DEVICE%%.default.align-items',
		//      'options'                 => [
		//          [
		//              'name' => __( 'Top', 'zionbuilder-pro' ),
		//              'id'   => 'flex-start',
		//              'icon' => 'align-start',
		//          ],
		//          [
		//              'name' => __( 'center', 'zionbuilder-pro' ),
		//              'id'   => 'center',
		//              'icon' => 'align-center',
		//          ],
		//          [
		//              'name' => __( 'Bottom', 'zionbuilder-pro' ),
		//              'id'   => 'flex-end',
		//              'icon' => 'align-end',
		//          ],
		//          [
		//              'name' => __( 'stretch', 'zionbuilder-pro' ),
		//              'id'   => 'stretch',
		//              'icon' => 'align-stretch',
		//          ],
		//          [
		//              'name' => __( 'baseline', 'zionbuilder-pro' ),
		//              'id'   => 'baseline',
		//              'icon' => 'align-baseline',
		//          ],
		//      ],
		//      'dependency'              => [
		//          [
		//              'option' => 'inner_content_layout',
		//              'value'  => [ 'row' ],
		//          ],
		//      ],
		//  ]
		// );

		// $shape_dividers = $options->add_group(
		//  'shape_dividers',
		//  [
		//      'type'      => 'panel_accordion',
		//      'title'     => __( 'Shape Dividers', 'zionbuilder-pro' ),
		//      'collapsed' => true,
		//  ]
		// );

		// $shape_dividers->add_option(
		//  'shapes',
		//  [
		//      'type'  => 'shape_dividers',
		//      'title' => __( 'Add a mask to your element', 'zionbuilder-pro' ),
		//  ]
		// );
	}

	public function get_wrapper_tag( $options ) {
		return $options->get_value( 'tag', 'header' );
	}

	/**
	 * Sets wrapper css classes
	 *
	 * @param \ZionBuilder\Options\Options $options
	 *
	 * @return void
	 */
	public function before_render( $options ) {
		$sticky                  = $options->get_value( 'enable_sticky_header', false );
		$sticky_threshold        = $options->get_value( 'sticky_threshold', 0 );
		$sticky_appear_animation = $options->get_value( 'sticky_appear_animation', 0 );

		// Add the grid section class
		$this->render_attributes->add(
			'wrapper',
			'data-zb-header-config',
			wp_json_encode(
				[
					'sticky'                  => $sticky,
					'stickyThreshold'         => $sticky_threshold,
					'sticky_appear_animation' => $sticky_appear_animation,
				]
			)
		);

		// add the sticky css class if the stick doesn't have a scroll threshold
		if ( $sticky && $sticky_threshold === 0 ) {
			$this->render_attributes->add( 'wrapper', 'class', 'zb-headerSticky' );
		}
	}


	/**
	 * Get style elements
	 *
	 * Returns a list of elements/tags that for which you
	 * want to show style options
	 *
	 * @return void
	 */
	public function on_register_styles() {
		$this->register_style_options_element(
			'inner_content_styles',
			[
				'title'      => esc_html__( 'Inner Content', 'zionbuilder-pro' ),
				'selector'   => '{{ELEMENT}} .zb-section__innerWrapper',
				'render_tag' => 'inner_content_styles',
			]
		);
	}

	/**
	 * Enqueue element scripts for both frontend and editor
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->enqueue_editor_script( Plugin::instance()->scripts->get_script_url( 'elements/HeaderBuilder/editor', 'js' ) );
		wp_enqueue_script( 'zb-element-header-builder', Plugin::instance()->scripts->get_script_url( 'elements/HeaderBuilder/frontend', 'js' ), array(), Plugin::instance()->get_version(), true );
		// $this->enqueue_element_script( Plugin::instance()->scripts->get_script_url( 'elements/HeaderBuilder/frontend', 'js' ) );

		$sticky_appear_animation = $this->options->get_value( 'sticky_appear_animation', 0 );
		if ( $sticky_appear_animation ) {
			// wp_enqueue_script( 'zionbuilder-animatejs' );
			wp_enqueue_style( 'zion-frontend-animations' );
		}
	}

	/**
	 * Enqueue element styles for both frontend and editor
	 *
	 * If you want to use the ZionBuilder cache system you must use
	 * the enqueue_editor_style(), enqueue_element_style() functions
	 *
	 * @return void
	 */
	public function enqueue_styles() {
		// Using helper methods will go through caching policy
		$this->enqueue_element_style( Plugin::instance()->scripts->get_script_url( 'elements/HeaderBuilder/frontend', 'css' ) );
	}

	/**
	 * Renders the element based on options
	 *
	 * @param \ZionBuilder\Options\Options $options
	 *
	 * @return void
	 */
	public function render( $options ) {
		$this->render_children();
	}
}
