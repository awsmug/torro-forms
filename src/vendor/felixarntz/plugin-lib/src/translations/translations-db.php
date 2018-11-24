<?php
/**
 * Translations for the DB class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_DB' ) ) :

	/**
	 * Translations for the DB class.
	 *
	 * @since 1.0.0
	 */
	class Translations_DB extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				/* translators: %s: table name */
				'table_already_exist' => $this->__translate( 'The table &#8220;%s&#8221; already exists.', 'textdomain' ),
				'schema_empty'        => $this->__translate( 'You cannot add a table without a schema.', 'textdomain' ),
			);
		}
	}

endif;
