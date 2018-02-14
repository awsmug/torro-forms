<?php
/**
 * Translations class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations' ) ) :

	/**
	 * Base class for a set of translations
	 *
	 * Translation strings cannot be bundled in a library, since WordPress
	 * requires its plugins to handle those. The translations classes allow
	 * to easily make the library strings translation-ready from inside a
	 * specific plugin.
	 *
	 * @since 1.0.0
	 */
	abstract class Translations {
		/**
		 * All translation identifiers and their strings.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $translations = array();

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			$this->init();
		}

		/**
		 * Returns a string for a specific identifier.
		 *
		 * @since 1.0.0
		 *
		 * @param string $identifier Translation string identifier.
		 * @param bool   $noop       Optional. Whether this is a noop string. Default false.
		 * @return string|array Translation string, or array if $noop, or empty string if
		 *                      invalid identifier.
		 */
		public function get( $identifier, $noop = false ) {
			if ( ! isset( $this->translations[ $identifier ] ) ) {
				if ( $noop ) {
					return array(
						0          => '',
						1          => '',
						'singular' => '',
						'plural'   => '',
						'context'  => null,
						'domain'   => '',
					);
				}

				return '';
			}

			return $this->translations[ $identifier ];
		}

		/**
		 * Returns all translation strings.
		 *
		 * @since 1.0.0
		 *
		 * @return array Array of all translation identifiers and their strings.
		 */
		public function get_all() {
			return $this->translations;
		}

		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected abstract function init();

		/**
		 * Dummy method for __() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message    Untranslated string.
		 * @param string $textdomain Textdomain for the translation.
		 * @return string The unmodified string.
		 */
		protected function __translate( $message, $textdomain ) {
			return $message;
		}

		/**
		 * Dummy method for _n() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $singular_message Untranslated string.
		 * @param string $plural_message   Untranslated string.
		 * @param int    $number           Number to determine message.
		 * @param string $textdomain       Textdomain for the translation.
		 * @return string The unmodified singular or plural string.
		 */
		protected function _ntranslate( $singular_message, $plural_message, $number, $textdomain ) {
			if ( $number > 1 ) {
				return $plural_message;
			}

			return $singular_message;
		}

		/**
		 * Dummy method for _x() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $message    Untranslated string.
		 * @param string $context    Context information for translators.
		 * @param string $textdomain Textdomain for the translation.
		 * @return string The unmodified string.
		 */
		protected function _xtranslate( $message, $context, $textdomain ) {
			return $message;
		}

		/**
		 * Dummy method for _nx() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $singular_message Untranslated string.
		 * @param string $plural_message   Untranslated string.
		 * @param int    $number           Number to determine message.
		 * @param string $context          Context information for translators.
		 * @param string $textdomain       Textdomain for the translation.
		 * @return string The unmodified singular or plural string.
		 */
		protected function _nxtranslate( $singular_message, $plural_message, $number, $context, $textdomain ) {
			if ( $number > 1 ) {
				return $plural_message;
			}

			return $singular_message;
		}

		/**
		 * Dummy method for _n_noop() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $singular_message Untranslated string.
		 * @param string $plural_message   Untranslated string.
		 * @param string $textdomain       Textdomain for the translation.
		 * @return array Input for `translate_nooped_plural()`.
		 */
		protected function _n_nooptranslate( $singular_message, $plural_message, $textdomain ) {
			return array(
				0          => $singular_message,
				1          => $plural_message,
				'singular' => $singular_message,
				'plural'   => $plural_message,
				'context'  => null,
				'domain'   => $textdomain,
			);
		}

		/**
		 * Dummy method for _nx_noop() translations.
		 *
		 * @since 1.0.0
		 *
		 * @param string $singular_message Untranslated string.
		 * @param string $plural_message   Untranslated string.
		 * @param string $context          Context information for translators.
		 * @param string $textdomain       Textdomain for the translation.
		 * @return array Input for `translate_nooped_plural()`.
		 */
		protected function _nx_nooptranslate( $singular_message, $plural_message, $context, $textdomain ) {
			return array(
				0          => $singular_message,
				1          => $plural_message,
				2          => $context,
				'singular' => $singular_message,
				'plural'   => $plural_message,
				'context'  => $context,
				'domain'   => $textdomain,
			);
		}
	}

endif;
