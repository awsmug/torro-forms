<?php
/**
 * Translations for the AJAX class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_AJAX' ) ) :

	/**
	 * Translations for the AJAX class.
	 *
	 * @since 1.0.0
	 */
	class Translations_AJAX extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				/* translators: %s: admin_init */
				'ajax_registered_too_late'      => $this->__translate( 'AJAX actions must be registered before the %s hook.', 'textdomain' ),
				'ajax_invalid_action_name'      => $this->__translate( 'Invalid AJAX action name.', 'textdomain' ),
				'ajax_request_invalid_action'   => $this->__translate( 'Invalid action.', 'textdomain' ),
				'ajax_request_invalid_callback' => $this->__translate( 'Invalid callback.', 'textdomain' ),
				'ajax_request_invalid_nonce'    => $this->__translate( 'Invalid nonce.', 'textdomain' ),
			);
		}
	}

endif;
