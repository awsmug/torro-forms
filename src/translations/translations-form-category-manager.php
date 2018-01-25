<?php
/**
 * Translations for the form category manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager;

/**
 * Translations for the form category manager class.
 *
 * @since 1.0.0
 */
class Translations_Form_Category_Manager extends Translations_Manager {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* --- Form Category --- */

			'db_insert_error'                     => __( 'Could not insert form category into the database.', 'torro-forms' ),
			'db_update_error'                     => __( 'Could not update form category in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                   => __( 'Could not delete form category metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                   => __( 'Could not update form category metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'           => __( 'Could not fetch form category from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                      => __( 'Could not fetch form category from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'          => __( 'Could not delete form category from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                     => __( 'Could not delete form category from the database.', 'torro-forms' ),
			'meta_delete_all_error'               => __( 'Could not delete the form category metadata. The form category itself was deleted successfully though.', 'torro-forms' ),

			/* --- REST Form Categories controller --- */

			'rest_invalid_id'                     => __( 'Invalid form category ID.', 'torro-forms' ),
			'rest_item_exists'                    => __( 'Cannot create an existing form category.', 'torro-forms' ),
			'rest_invalid_page_number'            => _x( 'The page number requested is larger than the number of pages available.', 'form category endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'              => __( 'Sorry, you are not allowed to edit form categories.', 'torro-forms' ),
			'rest_cannot_read_items'              => __( 'Sorry, you are not allowed to view form categories.', 'torro-forms' ),
			'rest_cannot_edit_item'               => __( 'Sorry, you are not allowed to edit this form category.', 'torro-forms' ),
			'rest_cannot_read_item'               => __( 'Sorry, you are not allowed to view this form category.', 'torro-forms' ),
			'rest_cannot_create_items'            => __( 'Sorry, you are not allowed to create form categories.', 'torro-forms' ),
			'rest_cannot_delete_item'             => __( 'Sorry, you are not allowed to delete this form category.', 'torro-forms' ),
			'rest_cannot_publish_item'            => __( 'Sorry, you are not allowed to publish this form category.', 'torro-forms' ),
			'rest_item_id_description'            => __( 'Unique identifier for the form category.', 'torro-forms' ),
			'rest_item_slug_description'          => __( 'A unique alphanumeric identifier for the form category.', 'torro-forms' ),
			'rest_item_title_description'         => __( 'The form category title.', 'torro-forms' ),
			'rest_collection_include_description' => __( 'Limit result set to specific form category IDs.', 'torro-forms' ),
			'rest_collection_exclude_description' => __( 'Ensure result set excludes specific form category IDs.', 'torro-forms' ),
			'rest_collection_orderby_description' => __( 'Sort collection by form category attribute.', 'torro-forms' ),
			'rest_collection_order_description'   => __( 'Order form category sort attribute ascending or descending.', 'torro-forms' ),
			'rest_collection_slug_description'    => __( 'Limit result set to form categories with one or more specific slugs.', 'torro-forms' ),
			'rest_collection_title_description'   => __( 'Limit result set to form categories with a specific title.', 'torro-forms' ),

		);
	}
}
