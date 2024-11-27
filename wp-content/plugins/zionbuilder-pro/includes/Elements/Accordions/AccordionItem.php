<?php

namespace ZionBuilderPro\Elements\Accordions;

use ZionBuilderPro\Utils;
use \ZionBuilder\Elements\Accordions\AccordionItem as FreeAccordionItem;
use ZionBuilderPro\Plugin;

// Prevent direct access
if ( ! defined( 'ABSPATH' ) ) {
	return;
}

/**
 * Class Text
 *
 * @package ZionBuilder\Elements
 */
class AccordionItem extends FreeAccordionItem {
	/**
	 * Holds a reference to the uid generated for free to pro migration
	 *
	 * @var string
	 */
	private $accordion_content_migration_uid = null;

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
	 * On before init
	 *
	 * Allow the users to add their own initialization process without extending __construct
	 *
	 * @param array<string, mixed> $data The data for the element instance
	 *
	 * @return void
	 */
	public function on_before_init( $data = [] ) {
		$this->accordion_content_migration_uid = uniqid( 'zntempuid' );

		$this->on( 'options/schema/set', [ $this, 'change_options' ] );
	}

	public function change_options() {
		$this->options->remove_option( 'content' );
	}

	/**
	 * Enqueue Scripts
	 *
	 * Loads the scripts necessary for the current element
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		parent::enqueue_scripts();
		
		$this->enqueue_editor_script( Plugin::instance()->scripts->get_script_url( 'elements/Accordions/editor', 'js' ) );
		$this->enqueue_editor_style( Utils::get_file_url( 'dist/elements/Accordions/editor.css' ) );
	}

	/**
	 * Get Children
	 *
	 * Returns an array containing all children of this element.
	 * If the element can have multiple content areas ( for example tabs or accordions ) it will loop trough all areas
	 * and returns all it's children
	 *
	 * @return array<int, mixed>
	 */
	public function get_children() {
		$options             = $this->options;
		$content             = $options->get_value( 'content', __( 'Accordion content', 'zionbuilder-pro' ) );
		$child_elements_data = ! empty( $this->content ) ? $this->content : [];

		// Convert content to element
		if ( ! empty( $content ) && empty( $child_elements_data ) ) {
			$element_data = [
				'element_type' => 'zion_text',
				'uid'          => $this->accordion_content_migration_uid,
				'options'      => [
					'content' => $content,
				],
			];

			// Set the content first
			$child_elements_data = [ $element_data ];
		}

		return $child_elements_data;
	}

	/**
	 * Renders the element based on options
	 *
	 * @param \ZionBuilder\Options\Options $options
	 *
	 * @return void
	 */
	public function render( $options ) {
		$accordions_element = $this->inject( 'accordionsElement' );
		$title              = $options->get_value( 'title' );
		$title_classes      = $accordions_element->get_style_classes_as_string( 'inner_content_styles_title' );
		$content_classes    = $accordions_element->get_style_classes_as_string( 'inner_content_styles_content' );

		$title_tag = $options->get_value( 'title_tag', $accordions_element->options->get_value( 'title_tag', 'div' ) );
		?>

		<<?php echo esc_html( $title_tag ); ?> class="zb-el-accordions-accordionTitle <?php echo esc_attr( $title_classes ); ?>" tabindex="0" role="button">
			<?php echo wp_kses_post( $title ); ?>
			<span class="zb-el-accordions-accordionIcon"></span>
		</<?php echo $title_tag; ?>>
		<div class="zb-el-accordions-accordionContent <?php echo esc_attr( $content_classes ); ?>">

			<div
				class="zb-el-accordions-accordionContent__inner"
			><?php $this->render_children(); ?></div>

		</div>
		<?php
	}
}
