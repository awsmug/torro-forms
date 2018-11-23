<?php
/**
 * Translations for the extensions class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Extensions as Translations_Extensions_Base;

/**
 * Translations for the extensions class.
 *
 * @since 1.0.0
 */
class Translations_Extensions extends Translations_Extensions_Base {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* translators: %s: class name */
			'extension_class_not_exist'    => __( 'The extension class %s does not exist.', 'torro-forms' ),
			/* translators: 1: class name, 2: other class name */
			'extension_class_invalid'      => __( 'The extension class %1$s is invalid, as it does not inherit the %2$s class.', 'torro-forms' ),
			/* translators: %s: extension name */
			'extension_already_registered' => __( 'An extension with the name &#8220;%s&#8221; is already registered.', 'torro-forms' ),
		);
	}
}
