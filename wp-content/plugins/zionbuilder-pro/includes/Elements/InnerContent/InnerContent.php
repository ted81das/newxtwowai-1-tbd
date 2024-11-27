<?php

namespace ZionBuilderPro\Elements\InnerContent;

use ZionBuilder\Elements\Element;
use ZionBuilderPro\Utils;
use ZionBuilderPro\Repeater;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class InnerContent
 *
 * @package ZionBuilderPro\Elements
 */
class InnerContent extends Element {
	/**
	 * Get type
	 *
	 * Returns the unique id for the element
	 *
	 * @return string The element id/type
	 */
	public function get_type() {
		return 'inner-content';
	}

	/**
	 * Get name
	 *
	 * Returns the name for the element
	 *
	 * @return string The element name
	 */
	public function get_name() {
		return __( 'Inner Content', 'zionbuilder-pro' );
	}

	/**
	 * Get Category
	 *
	 * Will return the element category
	 *
	 * @return string
	 */
	public function get_category() {
		return 'content';
	}

	/**
	 * Get keywords
	 *
	 * Returns the keywords for this element
	 *
	 * @return array The list of element keywords
	 */
	public function get_keywords() {
		return [ 'inner content', 'post' ];
	}

	/**
	 * Get Element Icon
	 *
	 * Returns the icon used in add elements panel for this element
	 *
	 * @return string The element icon
	 */
	public function get_element_icon() {
		return 'element-pagination';
	}


	/**
	 * Registers the element options
	 *
	 * @param \ZionBuilder\Options\Options $options The Options instance
	 *
	 * @return void
	 */
	public function options( $options ) {
		/**
		 * General
		 */
		$options->add_option(
			'tag',
			[
				'type'        => 'select',
				'default'     => 'div',
				'description' => esc_html__( 'Select the HTML tag to use for this element. If you want to add a custom tag, make sure to only use letters and numbers', 'zionbuilder' ),
				'title'       => esc_html__( 'HTML tag', 'zionbuilder' ),
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
	}


	/**
	 * Get wrapper tag
	 *
	 * @param Options $options
	 * @return void
	 */
	public function get_wrapper_tag( $options ) {
		return $options->get_value( 'tag', 'div' );
	}

	/**
	 * Render
	 *
	 * Will render the element based on options
	 *
	 * @param mixed $options
	 *
	 * @return void
	 */
	public function render( $options ) {
		the_content();
	}
}
