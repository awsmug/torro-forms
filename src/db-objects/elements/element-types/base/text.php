<?php
/**
 * Text element type class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Base;

use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;

/**
 * Class representing a text element type.
 *
 * @since 1.0.0
 */
class Text extends Element_Type {

	/**
	 * Bootstraps the element type by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'textfield';
		$this->title       = __( 'Text', 'torro-forms' );
		$this->description = __( 'A single text field element.', 'torro-forms' );
		$this->icon_url    = $this->manager->assets()->get_full_url( 'assets/dist/img/icon-textfield.png' );
	}
}
