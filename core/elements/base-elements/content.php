<?php
/**
 * Core: Torro_Form_Element_Content class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0-beta.1
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element class for WYSIWYG content
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Form_Element_Content extends Torro_Form_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'content';
		$this->title = __( 'Content', 'torro-forms' );
		$this->description = __( 'Adds own content to the form.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-text', 'png' );

		$this->input = false;
	}

	protected function get_input_html() {
		return wpautop( $this->label );
	}

	protected function admin_content_html() {
		$element_id = $this->get_admin_element_id();
		$name = $this->get_admin_input_name();

		$html = '<div class="torro-element-content element-tabs-content">';

		$editor_id = 'wp_editor_' . $element_id;
		$settings = array(
			'textarea_name' => $name . '[label]',
		);

		ob_start();
		wp_editor( $this->label, $editor_id, $settings );
		$html .= ob_get_clean();

		$html .= '</div>';

		return $html;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Content' );
