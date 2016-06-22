<?php
/**
 * Core: Torro_Element_Type_Content class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element type class for WYSIWYG content
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Element_Type_Content extends Torro_Element_Type {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'content';
		$this->title = __( 'Content', 'torro-forms' );
		$this->description = __( 'Adds own content to the form.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-text', 'png' );

		$this->input = false;
	}

	protected function admin_content_html( $element ) {
		$element_id = $this->get_admin_element_id( $element );
		$name = $this->get_admin_input_name( $element );

		$html = '<div class="torro-element-content element-tabs-content">';

		$editor_id = 'wp_editor_' . $element_id;
		$settings = array(
			'textarea_name' => $name . '[label]',
		);

		ob_start();
		wp_editor( $element->label, $editor_id, $settings );
		$html .= ob_get_clean();

		$html .= '</div>';

		return $html;
	}

	/**
	 * Prepares data to render the element type HTML output.
	 *
	 * @since 1.0.0
	 *
	 * @param Torro_Element $element
	 *
	 * @return array
	 */
	public function to_json( $element ) {
		$data = parent::to_json( $element );

		$data[ 'label' ] = do_shortcode( $data[ 'label' ] );

		return $data;
	}
}

torro()->element_types()->register( 'Torro_Element_Type_Content' );
