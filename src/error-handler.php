<?php
/**
 * Error handler class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\Error_Handler as Error_Handler_Base;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Error_Handler' ) ) :

/**
 * Class for error handling
 *
 * This class handles errors triggered by incorrect plugin usage.
 *
 * @since 1.0.0
 *
 * @codeCoverageIgnore
 */
class Error_Handler extends Error_Handler_Base {

	/**
	 * Marks a shortcode as deprecated and inform when it has been used.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $shortcode   The shortcode that was called.
	 * @param string $version     The version of the plugin that deprecated the shortcode.
	 * @param string $replacement Optional. The shortcode that should have been called. Default null.
	 */
	public function deprecated_shortcode( $shortcode, $version, $replacement = null ) {
		do_action( 'deprecated_shortcode_run', $shortcode, $replacement, $version );

		if ( WP_DEBUG && apply_filters( 'deprecated_shortcode_trigger_error', true ) ) {
			if ( ! is_null( $replacement ) ) {
				trigger_error( sprintf( $this->get_translation( 'deprecated_shortcode' ), $shortcode, $version, $replacement ) );
			} else {
				trigger_error( sprintf( $this->get_translation( 'deprecated_shortcode_no_alt' ), $shortcode, $version ) );
			}
		}
	}
}

endif;
