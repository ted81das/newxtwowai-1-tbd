<?php

namespace ZionBuilderPro\Elements\Rating;

use ZionBuilder\Elements\Element;
use ZionBuilderPro\Utils;
use ZionBuilderPro\Plugin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * @package ZionBuilderPro\Elements
 */
class Rating extends Element {
	/**
	 * Get type
	 *
	 * Returns the unique id for the element
	 *
	 * @return string The element id/type
	 */
	public function get_type() {
		return 'rating';
	}

	/**
	 * Get name
	 *
	 * Returns the name for the element
	 *
	 * @return string The element name
	 */
	public function get_name() {
		return __( 'Rating', 'zionbuilder-pro' );
	}

	/**
	 * Get Category
	 *
	 * Will return the element category
	 *
	 * @return string
	 */
	public function get_category() {
		return 'pro';
	}

	/**
	 * Get keywords
	 *
	 * Returns the keywords for this element
	 *
	 * @return array The list of element keywords
	 */
	public function get_keywords() {
		return [ 'rating', 'review', 'score' ];
	}

	public function options( $options ) {
		$options->add_option(
			'number_of_icons',
			[
				'type'    => 'number',
				'default' => 5,
				'layout'  => 'inline',
				'title'   => esc_html__( 'Number of icons', 'zionbuilder-pro' ),
				'max'     => 20,
			]
		);

		$options->add_option(
			'rating_value',
			[
				'type'    => 'number',
				'default' => 50,
				'layout'  => 'inline',
				'title'   => esc_html__( 'Fill percent', 'zionbuilder-pro' ),
			]
		);

		$options->add_option(
			'icon',
			[
				'type'        => 'icon_library',
				'title'       => __( 'Icon', 'zionbuilder' ),
				'description' => __( 'Choose an icon', 'zionbuilder' ),
				'default'     => [
					'family'  => 'Font Awesome 5 Free Solid',
					'name'    => 'star',
					'unicode' => 'uf005',
				],
			]
		);

		$options->add_option(
			'unfilled_color',
			[
				'type'        => 'colorpicker',
				'title'       => __( 'Unfilled color', 'zionbuilder-pro' ),
				'description' => __( 'Set the color for the unfilled icons', 'zionbuilder-pro' ),
				'default'     => '#000',
				'layout'      => 'inline',
				'display'     => 'simple',
			]
		);

		$options->add_option(
			'fill_color',
			[
				'type'        => 'colorpicker',
				'title'       => __( 'Fill color', 'zionbuilder-pro' ),
				'description' => __( 'Set the color for the filled icons', 'zionbuilder-pro' ),
				'default'     => '#18208d',
				'layout'      => 'inline',
				'display'     => 'simple',
			]
		);

		$options->add_option(
			'distance_between_icons',
			[
				'type'   => 'number_unit',
				'layout' => 'inline',
				'title'  => esc_html__( 'Distance between icons', 'zionbuilder-pro' ),
				'sync'   => '_styles.icon_styles.styles.%%RESPONSIVE_DEVICE%%.default.letter-spacing',
			]
		);

		$options->add_option(
			'icon_size',
			[
				'type'   => 'number_unit',
				'layout' => 'inline',
				'title'  => esc_html__( 'Icon size', 'zionbuilder-pro' ),
				'sync'   => '_styles.icon_styles.styles.%%RESPONSIVE_DEVICE%%.default.font-size',
			]
		);
	}

	/**
	 * Enqueue Scripts
	 *
	 * Loads the scripts necessary for the current element
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		$this->enqueue_editor_script( Plugin::instance()->scripts->get_script_url( 'elements/Rating/editor', 'js' ) );
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
		$this->enqueue_element_style( Utils::get_file_url( 'dist/elements/Rating/frontend.css' ) );
	}

	public function css() {
		$unfilled_color = $this->options->get_value( 'unfilled_color' );
		$fill_color     = $this->options->get_value( 'fill_color' );
		$rating_value   = $this->options->get_value( 'rating_value' );
		$letter_spacing = $this->options->get_value( 'distance_between_icons' );
		$css_id         = $this->get_css_selector();

		return "{$css_id} .zb-el-ratingWrapper {
				background-image: linear-gradient(90deg, {$fill_color} {$rating_value}%, {$unfilled_color} {$rating_value}%);
			}
		";
	}

	public function before_render( $options ) {
		$this->render_attributes->add( 'wrapper', 'itemtype', 'https://schema.org/Rating' );
		$this->render_attributes->add( 'wrapper', 'itemscope', '' );
		$this->render_attributes->add( 'wrapper', 'itemprop', 'reviewRating' );
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
		$icon            = $options->get_value( 'icon' );
		$number_of_icons = $options->get_value( 'number_of_icons' );
		$rating_value    = $this->options->get_value( 'rating_value', 0 );

		$this->attach_icon_attributes( 'icon', $icon );
		$icon_html = $this->get_render_tag(
			'span',
			'icon',
			'',
			[
				'class' => 'zb-el-ratingIcon',
			]
		);

		// Accessibility and seo
		$rating_value = $rating_value / 100 * $number_of_icons;
		$rating_text  = sprintf( esc_html__( 'Rated %1$s out of %2$s', 'zionbuilder-pro' ), $rating_value, $number_of_icons );

		echo '<meta itemprop="worstRating" content="0">';
		echo '<meta itemprop="bestRating" content="' . $number_of_icons . '">';
		echo '<div class="zb-el-ratingWrapper" itemprop="ratingValue" content="' . $rating_value . '" role="img" aria-label="' . $rating_text . '" >';
		for ( $i = 0; $i < $number_of_icons; $i++ ) {
			echo $icon_html;
		}
		echo '</div>';
	}

	public function on_register_styles() {
		$this->register_style_options_element(
			'rating_wrapper_styles',
			[
				'title'                   => esc_html__( 'Icons wrapper', 'zionbuilder-pro' ),
				'selector'                => '{{ELEMENT}} .zb-el-ratingWrapper',
				'allow_custom_attributes' => false,
				'allow_class_assignments' => false,
			]
		);

		$this->register_style_options_element(
			'icon_styles',
			[
				'title'                   => esc_html__( 'Icon styles', 'zionbuilder-pro' ),
				'selector'                => '{{ELEMENT}} .zb-el-ratingIcon',
				'allow_custom_attributes' => false,
				'allow_class_assignments' => false,
			]
		);
	}
}
