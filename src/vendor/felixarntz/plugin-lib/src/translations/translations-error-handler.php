<?php
/**
 * Translations for the Error_Handler class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler' ) ) :

	/**
	 * Translations for the Error_Handler class.
	 *
	 * @since 1.0.0
	 */
	class Translations_Error_Handler extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				/* translators: %s: comma-separated service names list */
				'missing_services'           => $this->__translate( 'The following required services have not been passed: %s', 'textdomain' ),
				/* translators: 1: function name, 2: message */
				'called_incorrectly'         => $this->__translate( '%1$s was called <strong>incorrectly</strong>. %2$s', 'textdomain' ),
				/* translators: %s: version number */
				'added_in_version'           => $this->__translate( '(This message was added in Plugin Name version %s.)', 'textdomain' ),
				/* translators: 1: function name, 2: version number, 3: other function name */
				'deprecated_function'        => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s. Use %3$s instead.', 'textdomain' ),
				/* translators: 1: function name, 2: version number */
				'deprecated_function_no_alt' => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
				/* translators: 1: argument name, 2: version number, 3: message */
				'deprecated_argument'        => $this->__translate( '%1$s was called with an argument that is <strong>deprecated</strong> since Plugin Name version %2$s. %3$s', 'textdomain' ),
				/* translators: 1: argument name, 2: version number */
				'deprecated_argument_no_alt' => $this->__translate( '%1$s was called with an argument that is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
				/* translators: 1: hook name, 2: version number, 3: other hook name */
				'deprecated_hook'            => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s. Use %3$s instead.', 'textdomain' ),
				/* translators: 1: hook name, 2: version number */
				'deprecated_hook_no_alt'     => $this->__translate( '%1$s is <strong>deprecated</strong> since Plugin Name version %2$s with no alternative available.', 'textdomain' ),
			);
		}
	}

endif;
