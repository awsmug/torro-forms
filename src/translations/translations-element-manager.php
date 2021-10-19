<?php
/**
 * Translations for the element manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager;

/**
 * Translations for the element manager class.
 *
 * @since 1.0.0
 */
class Translations_Element_Manager extends Translations_Manager {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* --- Element --- */

			'db_insert_error'                     => __( 'Could not insert element into the database.', 'torro-forms' ),
			'db_update_error'                     => __( 'Could not update element in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                   => __( 'Could not delete element metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                   => __( 'Could not update element metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'           => __( 'Could not fetch element from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                      => __( 'Could not fetch element from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'          => __( 'Could not delete element from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                     => __( 'Could not delete element from the database.', 'torro-forms' ),
			'meta_delete_all_error'               => __( 'Could not delete the element metadata. The element itself was deleted successfully though.', 'torro-forms' ),

			/* --- REST Elements controller --- */

			'rest_invalid_id'                     => __( 'Invalid element ID.', 'torro-forms' ),
			'rest_item_exists'                    => __( 'Cannot create an existing element.', 'torro-forms' ),
			'rest_invalid_page_number'            => _x( 'The page number requested is larger than the number of pages available.', 'element endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'              => __( 'Sorry, you are not allowed to edit elements.', 'torro-forms' ),
			'rest_cannot_read_items'              => __( 'Sorry, you are not allowed to view elements.', 'torro-forms' ),
			'rest_cannot_edit_item'               => __( 'Sorry, you are not allowed to edit this element.', 'torro-forms' ),
			'rest_cannot_read_item'               => __( 'Sorry, you are not allowed to view this element.', 'torro-forms' ),
			'rest_cannot_create_items'            => __( 'Sorry, you are not allowed to create elements.', 'torro-forms' ),
			'rest_cannot_delete_item'             => __( 'Sorry, you are not allowed to delete this element.', 'torro-forms' ),
			'rest_cannot_publish_item'            => __( 'Sorry, you are not allowed to publish this element.', 'torro-forms' ),
			'rest_item_id_description'            => __( 'Unique identifier for the element.', 'torro-forms' ),
			'rest_item_title_description'         => __( 'The element label.', 'torro-forms' ),
			'rest_collection_include_description' => __( 'Limit result set to specific element IDs.', 'torro-forms' ),
			'rest_collection_exclude_description' => __( 'Ensure result set excludes specific element IDs.', 'torro-forms' ),
			'rest_collection_orderby_description' => __( 'Sort collection by element attribute.', 'torro-forms' ),
			'rest_collection_order_description'   => __( 'Order element sort attribute ascending or descending.', 'torro-forms' ),
			'rest_collection_title_description'   => __( 'Limit result set to elements with a specific label.', 'torro-forms' ),

		);
	}
}
