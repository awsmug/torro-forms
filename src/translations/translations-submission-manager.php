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
	 */
	protected function init() {
		$this->translations = array(
			/* --- Submission --- */

			'db_insert_error'                         => __( 'Could not insert submission into the database.', 'torro-forms' ),
			'db_update_error'                         => __( 'Could not update submission in the database.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_delete_error'                       => __( 'Could not delete submission metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: meta key */
			'meta_update_error'                       => __( 'Could not update submission metadata for key &#8220;%s&#8221;.', 'torro-forms' ),
			'db_fetch_error_missing_id'               => __( 'Could not fetch submission from the database because it is missing an ID.', 'torro-forms' ),
			'db_fetch_error'                          => __( 'Could not fetch submission from the database.', 'torro-forms' ),
			'db_delete_error_missing_id'              => __( 'Could not delete submission from the database because it is missing an ID.', 'torro-forms' ),
			'db_delete_error'                         => __( 'Could not delete submission from the database.', 'torro-forms' ),
			'meta_delete_all_error'                   => __( 'Could not delete the submission metadata. The submission itself was deleted successfully though.', 'torro-forms' ),

			/* --- Submissions list page --- */

			'list_page_items'                         => __( 'Submissions', 'torro-forms' ),
			'list_page_cannot_edit_items'             => __( 'You are not allowed to edit submissions.', 'torro-forms' ),
			'list_page_add_new'                       => _x( 'Add New', 'submissions button', 'torro-forms' ),
			/* translators: %s: search query string */
			'list_page_search_results_for'            => _x( 'Search results for &#8220;%s&#8221;', 'submissions', 'torro-forms' ),
			'list_page_search_items'                  => __( 'Search submissions', 'torro-forms' ),
			'list_page_filter_items_list'             => __( 'Filter submissions list', 'torro-forms' ),
			'list_page_items_list_navigation'         => __( 'Submissions list navigation', 'torro-forms' ),
			'list_page_items_list'                    => __( 'Submissions list', 'torro-forms' ),
			'list_page_confirm_deletion'                            => _x( 'Are you sure you want to delete this submission? You will not be able to restore it afterwards.', 'submissions list page', 'torro-forms' ),
			/* translators: %s: submission title */
			'bulk_action_cannot_delete_item'          => __( 'You are not allowed to delete the submission &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: submission title */
			'bulk_action_delete_item_internal_error'  => __( 'An internal error occurred while trying to delete the submission &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'bulk_action_delete_has_errors'           => _n_noop( '%s submission could not be deleted as errors occurred:', '%s submissions could not be deleted as errors occurred:', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'bulk_action_delete_other_items_success'  => _n_noop( 'The other %s submission was deleted successfully.', 'The other %s submissions were deleted successfully.', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'bulk_action_delete_success'              => _n_noop( '%s submission successfully deleted.', '%s submissions successfully deleted.', 'torro-forms' ),

			/* --- Submission edit page --- */

			'edit_page_item'                          => __( 'Edit Submission', 'torro-forms' ),
			'edit_page_add_new'                       => _x( 'Add New', 'submission button', 'torro-forms' ),
			'edit_page_add_new_item'                  => __( 'Add New Submission', 'torro-forms' ),
			'edit_page_invalid_id'                    => __( 'Invalid submission ID.', 'torro-forms' ),
			'edit_page_cannot_edit_item'              => __( 'Sorry, you are not allowed to edit this submission.', 'torro-forms' ),
			'edit_page_cannot_create_item'            => __( 'Sorry, you are not allowed to create a new submission.', 'torro-forms' ),
			'edit_page_title_label'                   => __( 'Enter submission title here', 'torro-forms' ),
			'edit_page_title_placeholder'             => __( 'Enter submission title here', 'torro-forms' ),
			'edit_page_permalink_label'               => _x( 'Permalink', 'submission permalink label', 'torro-forms' ),
			'edit_page_slug_label'                    => _x( 'Slug', 'submission slug label', 'torro-forms' ),
			'edit_page_slug_button_label'             => _x( 'Edit Slug', 'submission slug button', 'torro-forms' ),
			'edit_page_create'                        => _x( 'Create', 'submission button', 'torro-forms' ),
			'edit_page_update'                        => _x( 'Update', 'submission button', 'torro-forms' ),
			'edit_page_delete'                        => _x( 'Delete', 'submission button', 'torro-forms' ),
			'edit_page_view'                          => _x( 'View', 'submission button', 'torro-forms' ),
			'edit_page_preview'                       => _x( 'Preview', 'submission button', 'torro-forms' ),
			'edit_page_preview_changes'               => _x( 'Preview Changes', 'submission button', 'torro-forms' ),
			'edit_page_submit_box_title'              => _x( 'Publish', 'submission submit box title', 'torro-forms' ),
			'edit_page_status_label'                  => _x( 'Status', 'submission status label', 'torro-forms' ),
			'edit_page_ok'                            => _x( 'OK', 'submission slug buttons', 'torro-forms' ),
			'edit_page_cancel'                        => _x( 'Cancel', 'submission slug buttons', 'torro-forms' ),
			'edit_page_confirm_deletion'                            => _x( 'Are you sure you want to delete this submission? You will not be able to restore it afterwards.', 'submission edit page', 'torro-forms' ),
			'action_edit_item_invalid_type'           => __( 'The submission type is invalid.', 'torro-forms' ),
			'action_edit_item_invalid_status'         => __( 'The submission status is invalid.', 'torro-forms' ),
			'action_edit_item_cannot_publish'         => __( 'You are not allowed to publish this submission.', 'torro-forms' ),
			'action_edit_item_internal_error'         => __( 'An internal error occurred while trying to save the submission.', 'torro-forms' ),
			'action_edit_item_has_errors'             => __( 'Some errors occurred while trying to save the submission:', 'torro-forms' ),
			'action_edit_item_other_fields_success'   => __( 'All other submission data was saved successfully.', 'torro-forms' ),
			'action_edit_item_success'                => __( 'Submission successfully saved.', 'torro-forms' ),
			'action_preview_item_internal_error'      => __( 'An internal error occurred while trying to preview the submission.', 'torro-forms' ),
			/* translators: %s: submission title */
			'action_delete_item_cannot_delete'        => __( 'You are not allowed to delete the submission &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: submission title */
			'action_delete_item_internal_error'       => __( 'An internal error occurred while trying to delete the submission &#8220;%s&#8221;.', 'torro-forms' ),
			/* translators: %s: submission title */
			'action_delete_item_success'              => __( 'Submission &#8220;%s&#8221; successfully deleted.', 'torro-forms' ),
			'ajax_item_slug_not_supported'            => __( 'Submission slugs are not supported.', 'torro-forms' ),
			'ajax_item_slug_not_passed'               => __( 'No submission slug was passed.', 'torro-forms' ),

			/* --- Submissions list table --- */

			'list_table_no_items'                     => __( 'No submissions.', 'torro-forms' ),
			'list_table_cb_select_label'              => __( 'Select submission', 'torro-forms' ),
			/* translators: %s: submission title */
			'list_table_cb_select_item_label'         => __( 'Select submission &#8220;%s&#8221;', 'torro-forms' ),
			/* translators: %s: submission title */
			'list_table_title_edit_label'             => __( 'Edit submission &#8220;%s&#8221;', 'torro-forms' ),
			'list_table_yes'                          => __( 'Yes', 'torro-forms' ),
			'list_table_no'                           => __( 'No', 'torro-forms' ),
			'list_table_filter_button_label'          => _x( 'Filter', 'submissions list button', 'torro-forms' ),
			'list_table_filter_by_date_label'         => _x( 'Filter by date', 'submissions list dropdown label', 'torro-forms' ),
			'list_table_all_dates'                    => __( 'All dates', 'torro-forms' ),
			/* translators: 1: month name, 2: 4-digit year */
			'list_table_month_year'                   => _x( '%1$s %2$s', 'month and year', 'torro-forms' ),
			'list_table_column_label_title'           => _x( 'Title', 'submission column label', 'torro-forms' ),
			'list_table_column_label_author'          => _x( 'Author', 'submission column label', 'torro-forms' ),
			'list_table_column_label_date'            => _x( 'Date', 'submission column label', 'torro-forms' ),
			'list_table_no_title'                     => _x( '(no title)', 'submission title placeholder', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'list_table_view_mine'                    => _nx_noop( 'Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', 'submission view', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'list_table_view_status_completed'        => _nx_noop( 'Completed <span class="count">(%s)</span>', 'Completed <span class="count">(%s)</span>', 'submission view', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'list_table_view_status_progressing'      => _nx_noop( 'In Progress <span class="count">(%s)</span>', 'In Progress <span class="count">(%s)</span>', 'submission view', 'torro-forms' ),
			/* translators: %s: formatted submission count */
			'list_table_view_all'                     => _nx_noop( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', 'submission view', 'torro-forms' ),
			'list_table_bulk_action_delete'           => _x( 'Delete Permanently', 'submission bulk action', 'torro-forms' ),
			'list_table_row_action_edit_item'         => __( 'Edit submission', 'torro-forms' ),
			/* translators: %s: submission title */
			'list_table_row_action_edit_item_title'   => __( 'Edit submission &#8220;%s&#8221;', 'torro-forms' ),
			'list_table_row_action_edit'              => _x( 'Edit', 'submission row action', 'torro-forms' ),
			'list_table_row_action_delete_item'       => __( 'Delete submission', 'torro-forms' ),
			/* translators: %s: submission title */
			'list_table_row_action_delete_item_title' => __( 'Delete submission &#8220;%s&#8221;', 'torro-forms' ),
			'list_table_row_action_delete'            => _x( 'Delete', 'submission row action', 'torro-forms' ),
			'list_table_row_action_view_item'         => __( 'View submission', 'torro-forms' ),
			/* translators: %s: submission title */
			'list_table_row_action_view_item_title'   => __( 'View submission &#8220;%s&#8221;', 'torro-forms' ),
			'list_table_row_action_view'              => _x( 'View', 'submission row action', 'torro-forms' ),

			/* --- REST Submissions controller --- */

			'rest_invalid_id'                         => __( 'Invalid submission ID.', 'torro-forms' ),
			'rest_item_exists'                        => __( 'Cannot create an existing submission.', 'torro-forms' ),
			'rest_invalid_page_number'                => _x( 'The page number requested is larger than the number of pages available.', 'submission endpoint', 'torro-forms' ),
			'rest_cannot_edit_items'                  => __( 'Sorry, you are not allowed to edit submissions.', 'torro-forms' ),
			'rest_cannot_read_items'                  => __( 'Sorry, you are not allowed to view submissions.', 'torro-forms' ),
			'rest_cannot_edit_item'                   => __( 'Sorry, you are not allowed to edit this submission.', 'torro-forms' ),
			'rest_cannot_read_item'                   => __( 'Sorry, you are not allowed to view this submission.', 'torro-forms' ),
			'rest_cannot_create_items'                => __( 'Sorry, you are not allowed to create submissions.', 'torro-forms' ),
			'rest_cannot_delete_item'                 => __( 'Sorry, you are not allowed to delete this submission.', 'torro-forms' ),
			'rest_cannot_publish_item'                => __( 'Sorry, you are not allowed to publish this submission.', 'torro-forms' ),
			'rest_item_id_description'                => __( 'Unique identifier for the submission.', 'torro-forms' ),
			'rest_collection_include_description'     => __( 'Limit result set to specific submission IDs.', 'torro-forms' ),
			'rest_collection_exclude_description'     => __( 'Ensure result set excludes specific submission IDs.', 'torro-forms' ),
			'rest_collection_orderby_description'     => __( 'Sort collection by submission attribute.', 'torro-forms' ),
			'rest_collection_order_description'       => __( 'Order submission sort attribute ascending or descending.', 'torro-forms' ),

		);
	}
}
