<?php
/**
 * Translations for the Error_Handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Error_Handler as Translations_Error_Handler_Base;

/**
 * Translations for the Error_Handler class.
 *
 * @since 1.0.0
 */
class Translations_Error_Handler extends Translations_Error_Handler_Base {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* translators: %s: comma-separated service names list */
			'missing_services'            => __( 'The following required services have not been passed: %s', 'torro-forms' ),
			/* translators: 1: function name, 2: message */
			'called_incorrectly'          => __( '%1$s was called <strong>incorrectly</strong>. %2$s', 'torro-forms' ),
			/* translators: %s: version number */
			'added_in_version'            => __( '(This message was added in Torro Forms version %s.)', 'torro-forms' ),
			/* translators: 1: function name, 2: version number, 3: other function name */
			'deprecated_function'         => __( '%1$s is <strong>deprecated</strong> since Torro Forms version %2$s. Use %3$s instead.', 'torro-forms' ),
			/* translators: 1: function name, 2: version number */
			'deprecated_function_no_alt'  => __( '%1$s is <strong>deprecated</strong> since Torro Forms version %2$s with no alternative available.', 'torro-forms' ),
			/* translators: 1: argument name, 2: version number, 3: message */
			'deprecated_argument'         => __( '%1$s was called with an argument that is <strong>deprecated</strong> since Torro Forms version %2$s. %3$s', 'torro-forms' ),
			/* translators: 1: argument name, 2: version number */
			'deprecated_argument_no_alt'  => __( '%1$s was called with an argument that is <strong>deprecated</strong> since Torro Forms version %2$s with no alternative available.', 'torro-forms' ),
			/* translators: 1: hook name, 2: version number, 3: other hook name */
			'deprecated_hook'             => __( '%1$s is <strong>deprecated</strong> since Torro Forms version %2$s. Use %3$s instead.', 'torro-forms' ),
			/* translators: 1: hook name, 2: version number */
			'deprecated_hook_no_alt'      => __( '%1$s is <strong>deprecated</strong> since Torro Forms version %2$s with no alternative available.', 'torro-forms' ),
			/* translators: 1: shortcode name, 2: version number, 3: other shortcode name */
			'deprecated_shortcode'        => __( 'The shortcode %1$s is <strong>deprecated</strong> since Torro Forms version %2$s. Use %3$s instead.', 'torro-forms' ),
			/* translators: 1: shortcode name, 2: version number */
			'deprecated_shortcode_no_alt' => __( 'The shortcode %1$s is <strong>deprecated</strong> since Torro Forms version %2$s with no alternative available.', 'torro-forms' ),
		);
	}
}
