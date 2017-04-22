<?php
/**
 * Translations for the submission manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager;

/**
 * Translations for the submission manager class.
 *
 * @since 1.0.0
 */
class Translations_Submission_Manager extends Translations_Manager {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			/* --- Submission --- */

			'db_insert_error'                     => __( 'Could not insert submission into the database.', 'torro-forms' ),
			'db_update_error'                     => __( 'Could not update submission in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                   => __( 'Could not delete submission metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                   => __( 'Could not update submission metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'           => __( 'Could not fetch submission from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                      => __( 'Could not fetch submission from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'          => __( 'Could not delete submission from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                     => __( 'Could not delete submission from the database.', 'torro-forms' ),
			'meta_delete_all_error'               => __( 'Could not delete the submission metadata. The submission itself was deleted successfully though.', 'torro-forms' ),

			/* --- REST Submissions controller --- */

			'rest_invalid_id'                     => __( 'Invalid submission ID.', 'torro-forms' ),
			'rest_item_exists'                    => __( 'Cannot create an existing submission.', 'torro-forms' ),
			'rest_invalid_page_number'            => _x( 'The page number requested is larger than the number of pages available.', 'submission endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'              => __( 'Sorry, you are not allowed to edit submissions.', 'torro-forms' ),
			'rest_cannot_read_items'              => __( 'Sorry, you are not allowed to view submissions.', 'torro-forms' ),
			'rest_cannot_edit_item'               => __( 'Sorry, you are not allowed to edit this submission.', 'torro-forms' ),
			'rest_cannot_read_item'               => __( 'Sorry, you are not allowed to view this submission.', 'torro-forms' ),
			'rest_cannot_create_items'            => __( 'Sorry, you are not allowed to create submissions.', 'torro-forms' ),
			'rest_cannot_delete_item'             => __( 'Sorry, you are not allowed to delete this submission.', 'torro-forms' ),
			'rest_cannot_publish_item'            => __( 'Sorry, you are not allowed to publish this submission.', 'torro-forms' ),
			'rest_item_id_description'            => __( 'Unique identifier for the submission.', 'torro-forms' ),
			'rest_item_slug_description'          => __( 'A unique alphanumeric identifier for the submission.', 'torro-forms' ),
			'rest_item_title_description'         => __( 'The submission title.', 'torro-forms' ),
			'rest_collection_include_description' => __( 'Limit result set to specific submission IDs.', 'torro-forms' ),
			'rest_collection_exclude_description' => __( 'Ensure result set excludes specific submission IDs.', 'torro-forms' ),
			'rest_collection_orderby_description' => __( 'Sort collection by submission attribute.', 'torro-forms' ),
			'rest_collection_order_description'   => __( 'Order submission sort attribute ascending or descending.', 'torro-forms' ),
			'rest_collection_slug_description'    => __( 'Limit result set to submissions with one or more specific slugs.', 'torro-forms' ),
			'rest_collection_title_description'   => __( 'Limit result set to submissions with a specific title.', 'torro-forms' ),

		);
	}
}
