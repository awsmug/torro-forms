<?php
/**
 * Translations for the extensions class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Extensions' ) ) :

	/**
	 * Translations for the extensions class.
	 *
	 * @since 1.0.0
	 */
	class Translations_Extensions extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				/* translators: %s: class name */
				'extension_class_not_exist'    => $this->__translate( 'The extension class %s does not exist.', 'textdomain' ),
				/* translators: 1: class name, 2: other class name */
				'extension_class_invalid'      => $this->__translate( 'The extension class %1$s is invalid, as it does not inherit the %2$s class.', 'textdomain' ),
				/* translators: %s: extension name */
				'extension_already_registered' => $this->__translate( 'An extension with the name &#8220;%s&#8221; is already registered.', 'textdomain' ),
			);
		}
	}

endif;
