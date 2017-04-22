<?php
/**
 * Translations for the participant manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Translations;

use Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager;

/**
 * Translations for the participant manager class.
 *
 * @since 1.0.0
 */
class Translations_Participant_Manager extends Translations_Manager {

	/**
	 * Initializes the translation strings.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function init() {
		$this->translations = array(
			/* --- Participant --- */

			'db_insert_error'                     => __( 'Could not insert participant into the database.', 'torro-forms' ),
			'db_update_error'                     => __( 'Could not update participant in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                   => __( 'Could not delete participant metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                   => __( 'Could not update participant metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'           => __( 'Could not fetch participant from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                      => __( 'Could not fetch participant from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'          => __( 'Could not delete participant from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                     => __( 'Could not delete participant from the database.', 'torro-forms' ),
			'meta_delete_all_error'               => __( 'Could not delete the participant metadata. The participant itself was deleted successfully though.', 'torro-forms' ),

			/* --- REST Participants controller --- */

			'rest_invalid_id'                     => __( 'Invalid participant ID.', 'torro-forms' ),
			'rest_item_exists'                    => __( 'Cannot create an existing participant.', 'torro-forms' ),
			'rest_invalid_page_number'            => _x( 'The page number requested is larger than the number of pages available.', 'participant endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'              => __( 'Sorry, you are not allowed to edit participants.', 'torro-forms' ),
			'rest_cannot_read_items'              => __( 'Sorry, you are not allowed to view participants.', 'torro-forms' ),
			'rest_cannot_edit_item'               => __( 'Sorry, you are not allowed to edit this participant.', 'torro-forms' ),
			'rest_cannot_read_item'               => __( 'Sorry, you are not allowed to view this participant.', 'torro-forms' ),
			'rest_cannot_create_items'            => __( 'Sorry, you are not allowed to create participants.', 'torro-forms' ),
			'rest_cannot_delete_item'             => __( 'Sorry, you are not allowed to delete this participant.', 'torro-forms' ),
			'rest_cannot_publish_item'            => __( 'Sorry, you are not allowed to publish this participant.', 'torro-forms' ),
			'rest_item_id_description'            => __( 'Unique identifier for the participant.', 'torro-forms' ),
			'rest_item_slug_description'          => __( 'A unique alphanumeric identifier for the participant.', 'torro-forms' ),
			'rest_item_title_description'         => __( 'The participant title.', 'torro-forms' ),
			'rest_collection_include_description' => __( 'Limit result set to specific participant IDs.', 'torro-forms' ),
			'rest_collection_exclude_description' => __( 'Ensure result set excludes specific participant IDs.', 'torro-forms' ),
			'rest_collection_orderby_description' => __( 'Sort collection by participant attribute.', 'torro-forms' ),
			'rest_collection_order_description'   => __( 'Order participant sort attribute ascending or descending.', 'torro-forms' ),
			'rest_collection_slug_description'    => __( 'Limit result set to participants with one or more specific slugs.', 'torro-forms' ),
			'rest_collection_title_description'   => __( 'Limit result set to participants with a specific title.', 'torro-forms' ),

		);
	}
}