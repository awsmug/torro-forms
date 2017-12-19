<?php
/**
 * Translations for the AJAX class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_AJAX as Translations_AJAX_Base;

/**
 * Translations for the AJAX class.
 *
 * @since 1.0.0
 */
class Translations_AJAX extends Translations_AJAX_Base {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* translators: %s: admin_init */
			'ajax_registered_too_late'      => __( 'AJAX actions must be registered before the %s hook.', 'torro-forms' ),
			'ajax_invalid_action_name'      => __( 'Invalid AJAX action name.', 'torro-forms' ),
			'ajax_request_invalid_action'   => __( 'Invalid action.', 'torro-forms' ),
			'ajax_request_invalid_callback' => __( 'Invalid callback.', 'torro-forms' ),
			'ajax_request_invalid_nonce'    => __( 'Invalid nonce.', 'torro-forms' ),
		);
	}
}
