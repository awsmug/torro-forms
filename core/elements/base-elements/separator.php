<?php
/**
 * Core: Torro_Form_Element_Separator class
 *
 * @package TorroForms
 * @subpackage CoreElements
 * @version 1.0.0beta1
 * @since 1.0.0beta1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Element class for a separator
 *
 * @since 1.0.0beta1
 */
final class Torro_Form_Element_Separator extends Torro_Form_Element {
	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		parent::init();

		$this->type = $this->name = 'separator';
		$this->title = __( 'Separator', 'torro-forms' );
		$this->description = __( 'Adds a optical separator (<hr>) between elements.', 'torro-forms' );
		$this->icon_url = torro()->get_asset_url( 'icon-separator', 'png' );

		$this->input = false;
	}

	protected function get_input_html() {
		$html = '<hr />';

		return $html;
	}
}

torro()->elements()->register( 'Torro_Form_Element_Separator' );
