<?php
/**
 * Translations for the Error_Handler base handler fallback class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Base_Error_Handler' ) ) :

	/**
	 * Translations for the Error_Handler base handler fallback class.
	 *
	 * Contains untranslated strings only.
	 *
	 * @since 1.0.0
	 */
	class Translations_Base_Error_Handler extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				'missing_services'           => 'The following required services have not been passed: %s',
				'called_incorrectly'         => '%1$s was called <strong>incorrectly</strong>. %2$s',
				'added_in_version'           => '(This message was added in WordPress version %s.)',
				'deprecated_function'        => '%1$s is <strong>deprecated</strong> since WordPress version %2$s. Use %3$s instead.',
				'deprecated_function_no_alt' => '%1$s is <strong>deprecated</strong> since WordPress version %2$s with no alternative available.',
				'deprecated_argument'        => '%1$s was called with an argument that is <strong>deprecated</strong> since WordPress version %2$s. %3$s',
				'deprecated_argument_no_alt' => '%1$s was called with an argument that is <strong>deprecated</strong> since WordPress version %2$s with no alternative available.',
				'deprecated_hook'            => '%1$s is <strong>deprecated</strong> since WordPress version %2$s. Use %3$s instead.',
				'deprecated_hook_no_alt'     => '%1$s is <strong>deprecated</strong> since WordPress version %2$s with no alternative available.',
			);
		}
	}

endif;
