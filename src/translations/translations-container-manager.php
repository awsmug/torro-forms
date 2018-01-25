<?php
/**
 * Translations for the container manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager;

/**
 * Translations for the container manager class.
 *
 * @since 1.0.0
 */
class Translations_Container_Manager extends Translations_Manager {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->translations = array(
			/* --- Container --- */

			'db_insert_error'                     => __( 'Could not insert container into the database.', 'torro-forms' ),
			'db_update_error'                     => __( 'Could not update container in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                   => __( 'Could not delete container metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                   => __( 'Could not update container metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'           => __( 'Could not fetch container from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                      => __( 'Could not fetch container from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'          => __( 'Could not delete container from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                     => __( 'Could not delete container from the database.', 'torro-forms' ),
			'meta_delete_all_error'               => __( 'Could not delete the container metadata. The container itself was deleted successfully though.', 'torro-forms' ),

			/* --- REST Containers controller --- */

			'rest_invalid_id'                     => __( 'Invalid container ID.', 'torro-forms' ),
			'rest_item_exists'                    => __( 'Cannot create an existing container.', 'torro-forms' ),
			'rest_invalid_page_number'            => _x( 'The page number requested is larger than the number of pages available.', 'container endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'              => __( 'Sorry, you are not allowed to edit containers.', 'torro-forms' ),
			'rest_cannot_read_items'              => __( 'Sorry, you are not allowed to view containers.', 'torro-forms' ),
			'rest_cannot_edit_item'               => __( 'Sorry, you are not allowed to edit this container.', 'torro-forms' ),
			'rest_cannot_read_item'               => __( 'Sorry, you are not allowed to view this container.', 'torro-forms' ),
			'rest_cannot_create_items'            => __( 'Sorry, you are not allowed to create containers.', 'torro-forms' ),
			'rest_cannot_delete_item'             => __( 'Sorry, you are not allowed to delete this container.', 'torro-forms' ),
			'rest_cannot_publish_item'            => __( 'Sorry, you are not allowed to publish this container.', 'torro-forms' ),
			'rest_item_id_description'            => __( 'Unique identifier for the container.', 'torro-forms' ),
			'rest_item_title_description'         => __( 'The container label.', 'torro-forms' ),
			'rest_collection_include_description' => __( 'Limit result set to specific container IDs.', 'torro-forms' ),
			'rest_collection_exclude_description' => __( 'Ensure result set excludes specific container IDs.', 'torro-forms' ),
			'rest_collection_orderby_description' => __( 'Sort collection by container attribute.', 'torro-forms' ),
			'rest_collection_order_description'   => __( 'Order container sort attribute ascending or descending.', 'torro-forms' ),
			'rest_collection_title_description'   => __( 'Limit result set to containers with a specific label.', 'torro-forms' ),

		);
	}
}
