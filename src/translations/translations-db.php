<?php
/**
 * Translations for the DB class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_DB as Translations_DB_Base;

/**
 * Translations for the DB class.
 *
 * @since 1.0.0
 */
class Translations_DB extends Translations_DB_Base {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* translators: %s: table name */
			'table_already_exist' => __( 'The table &#8220;%s&#8221; already exists.', 'torro-forms' ),
			'schema_empty'        => __( 'You cannot add a table without a schema.', 'torro-forms' ),
		);
	}
}
