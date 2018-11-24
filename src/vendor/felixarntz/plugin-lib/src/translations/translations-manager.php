<?php
/**
 * Translations for the Manager class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Translations;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\Translations\Translations_Manager' ) ) :

	/**
	 * Translations for the Manager class.
	 *
	 * @since 1.0.0
	 */
	class Translations_Manager extends Translations {
		/**
		 * Initializes the translation strings.
		 *
		 * @since 1.0.0
		 */
		protected function init() {
			$this->translations = array(
				/* --- Model --- */

				'db_insert_error'                                       => $this->__translate( 'Could not insert model into the database.', 'textdomain' ),
				'db_update_error'                                       => $this->__translate( 'Could not update model in the database.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_delete_error'                                     => $this->__translate( 'Could not delete model metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: meta key */
				'meta_update_error'                                     => $this->__translate( 'Could not update model metadata for key &#8220;%s&#8221;.', 'textdomain' ),
				'db_fetch_error_missing_id'                             => $this->__translate( 'Could not fetch model from the database because it is missing an ID.', 'textdomain' ),
				'db_fetch_error'                                        => $this->__translate( 'Could not fetch model from the database.', 'textdomain' ),
				'db_delete_error_missing_id'                            => $this->__translate( 'Could not delete model from the database because it is missing an ID.', 'textdomain' ),
				'db_delete_error'                                       => $this->__translate( 'Could not delete model from the database.', 'textdomain' ),
				'meta_delete_all_error'                                 => $this->__translate( 'Could not delete the model metadata. The model itself was deleted successfully though.', 'textdomain' ),

				/* --- Models list page --- */

				'list_page_items'                                       => $this->__translate( 'Models', 'textdomain' ),
				'list_page_cannot_edit_items'                           => $this->__translate( 'You are not allowed to edit models.', 'textdomain' ),
				'list_page_add_new'                                     => $this->_xtranslate( 'Add New', 'models button', 'textdomain' ),
				/* translators: %s: search query string */
				'list_page_search_results_for'                          => $this->_xtranslate( 'Search results for &#8220;%s&#8221;', 'models', 'textdomain' ),
				'list_page_search_items'                                => $this->__translate( 'Search models', 'textdomain' ),
				'list_page_filter_items_list'                           => $this->__translate( 'Filter models list', 'textdomain' ),
				'list_page_items_list_navigation'                       => $this->__translate( 'Models list navigation', 'textdomain' ),
				'list_page_items_list'                                  => $this->__translate( 'Models list', 'textdomain' ),
				'list_page_confirm_deletion'                            => $this->_xtranslate( 'Are you sure you want to delete this model? You will not be able to restore it afterwards.', 'models list page', 'textdomain' ),
				/* translators: %s: model title */
				'bulk_action_cannot_delete_item'                        => $this->__translate( 'You are not allowed to delete the model &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: model title */
				'bulk_action_delete_item_internal_error'                => $this->__translate( 'An internal error occurred while trying to delete the model &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: formatted model count */
				'bulk_action_delete_has_errors'                         => $this->_n_nooptranslate( '%s model could not be deleted as errors occurred:', '%s models could not be deleted as errors occurred:', 'textdomain' ),
				/* translators: %s: formatted model count */
				'bulk_action_delete_other_items_success'                => $this->_n_nooptranslate( 'The other %s model was deleted successfully.', 'The other %s models were deleted successfully.', 'textdomain' ),
				/* translators: %s: formatted model count */
				'bulk_action_delete_success'                            => $this->_n_nooptranslate( '%s model successfully deleted.', '%s models successfully deleted.', 'textdomain' ),

				/* --- Model edit page --- */

				'edit_page_item'                                        => $this->__translate( 'Edit Model', 'textdomain' ),
				'edit_page_add_new'                                     => $this->_xtranslate( 'Add New', 'model button', 'textdomain' ),
				'edit_page_add_new_item'                                => $this->__translate( 'Add New Model', 'textdomain' ),
				'edit_page_invalid_id'                                  => $this->__translate( 'Invalid model ID.', 'textdomain' ),
				'edit_page_cannot_edit_item'                            => $this->__translate( 'Sorry, you are not allowed to edit this model.', 'textdomain' ),
				'edit_page_cannot_create_item'                          => $this->__translate( 'Sorry, you are not allowed to create a new model.', 'textdomain' ),
				'edit_page_title_label'                                 => $this->__translate( 'Enter model title here', 'textdomain' ),
				'edit_page_title_placeholder'                           => $this->__translate( 'Enter model title here', 'textdomain' ),
				'edit_page_permalink_label'                             => $this->_xtranslate( 'Permalink', 'model permalink label', 'textdomain' ),
				'edit_page_slug_label'                                  => $this->_xtranslate( 'Slug', 'model slug label', 'textdomain' ),
				'edit_page_slug_button_label'                           => $this->_xtranslate( 'Edit Slug', 'model slug button', 'textdomain' ),
				'edit_page_create'                                      => $this->_xtranslate( 'Create', 'model button', 'textdomain' ),
				'edit_page_update'                                      => $this->_xtranslate( 'Update', 'model button', 'textdomain' ),
				'edit_page_delete'                                      => $this->_xtranslate( 'Delete', 'model button', 'textdomain' ),
				'edit_page_view'                                        => $this->_xtranslate( 'View', 'model button', 'textdomain' ),
				'edit_page_preview'                                     => $this->_xtranslate( 'Preview', 'model button', 'textdomain' ),
				'edit_page_preview_changes'                             => $this->_xtranslate( 'Preview Changes', 'model button', 'textdomain' ),
				'edit_page_submit_box_title'                            => $this->_xtranslate( 'Publish', 'model submit box title', 'textdomain' ),
				'edit_page_status_label'                                => $this->_xtranslate( 'Status', 'model status label', 'textdomain' ),
				'edit_page_ok'                                          => $this->_xtranslate( 'OK', 'model slug buttons', 'textdomain' ),
				'edit_page_cancel'                                      => $this->_xtranslate( 'Cancel', 'model slug buttons', 'textdomain' ),
				'edit_page_confirm_deletion'                            => $this->_xtranslate( 'Are you sure you want to delete this model? You will not be able to restore it afterwards.', 'model edit page', 'textdomain' ),
				'action_edit_item_invalid_type'                         => $this->__translate( 'The model type is invalid.', 'textdomain' ),
				'action_edit_item_invalid_status'                       => $this->__translate( 'The model status is invalid.', 'textdomain' ),
				'action_edit_item_cannot_publish'                       => $this->__translate( 'You are not allowed to publish this model.', 'textdomain' ),
				'action_edit_item_internal_error'                       => $this->__translate( 'An internal error occurred while trying to save the model.', 'textdomain' ),
				'action_edit_item_has_errors'                           => $this->__translate( 'Some errors occurred while trying to save the model:', 'textdomain' ),
				'action_edit_item_other_fields_success'                 => $this->__translate( 'All other model data was saved successfully.', 'textdomain' ),
				'action_edit_item_success'                              => $this->__translate( 'Model successfully saved.', 'textdomain' ),
				'action_preview_item_internal_error'                    => $this->__translate( 'An internal error occurred while trying to preview the model.', 'textdomain' ),
				/* translators: %s: model title */
				'action_delete_item_cannot_delete'                      => $this->__translate( 'You are not allowed to delete the model &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: model title */
				'action_delete_item_internal_error'                     => $this->__translate( 'An internal error occurred while trying to delete the model &#8220;%s&#8221;.', 'textdomain' ),
				/* translators: %s: model title */
				'action_delete_item_success'                            => $this->__translate( 'Model &#8220;%s&#8221; successfully deleted.', 'textdomain' ),
				'ajax_item_slug_not_supported'                          => $this->__translate( 'Model slugs are not supported.', 'textdomain' ),
				'ajax_item_slug_not_passed'                             => $this->__translate( 'No model slug was passed.', 'textdomain' ),

				/* --- Models list table --- */

				'list_table_no_items'                                   => $this->__translate( 'No models.', 'textdomain' ),
				'list_table_cb_select_label'                            => $this->__translate( 'Select model', 'textdomain' ),
				/* translators: %s: model title */
				'list_table_cb_select_item_label'                       => $this->__translate( 'Select model &#8220;%s&#8221;', 'textdomain' ),
				/* translators: %s: model title */
				'list_table_title_edit_label'                           => $this->__translate( 'Edit model &#8220;%s&#8221;', 'textdomain' ),
				'list_table_yes'                                        => $this->__translate( 'Yes', 'textdomain' ),
				'list_table_no'                                         => $this->__translate( 'No', 'textdomain' ),
				'list_table_filter_button_label'                        => $this->_xtranslate( 'Filter', 'models list button', 'textdomain' ),
				'list_table_filter_by_date_label'                       => $this->_xtranslate( 'Filter by date', 'models list dropdown label', 'textdomain' ),
				'list_table_all_dates'                                  => $this->__translate( 'All dates', 'textdomain' ),
				/* translators: 1: month name, 2: 4-digit year */
				'list_table_month_year'                                 => $this->_xtranslate( '%1$s %2$s', 'month and year', 'textdomain' ),
				'list_table_column_label_title'                         => $this->_xtranslate( 'Title', 'model column label', 'textdomain' ),
				'list_table_column_label_author'                        => $this->_xtranslate( 'Author', 'model column label', 'textdomain' ),
				'list_table_column_label_date'                          => $this->_xtranslate( 'Date', 'model column label', 'textdomain' ),
				'list_table_no_title'                                   => $this->_xtranslate( '(no title)', 'model title placeholder', 'textdomain' ),
				/* translators: %s: formatted model count */
				'list_table_view_mine'                                  => $this->_nx_nooptranslate( 'Mine <span class="count">(%s)</span>', 'Mine <span class="count">(%s)</span>', 'model view', 'textdomain' ),
				/* translators: %s: formatted model count */
				'list_table_view_status_draft'                          => $this->_nx_nooptranslate( 'Draft <span class="count">(%s)</span>', 'Draft <span class="count">(%s)</span>', 'model view', 'textdomain' ),
				/* translators: %s: formatted model count */
				'list_table_view_status_publish'                        => $this->_nx_nooptranslate( 'Published <span class="count">(%s)</span>', 'Published <span class="count">(%s)</span>', 'model view', 'textdomain' ),
				/* translators: %s: formatted model count */
				'list_table_view_all'                                   => $this->_nx_nooptranslate( 'All <span class="count">(%s)</span>', 'All <span class="count">(%s)</span>', 'model view', 'textdomain' ),
				'list_table_bulk_action_delete'                         => $this->_xtranslate( 'Delete Permanently', 'model bulk action', 'textdomain' ),
				'list_table_row_action_edit_item'                       => $this->__translate( 'Edit model', 'textdomain' ),
				/* translators: %s: model title */
				'list_table_row_action_edit_item_title'                 => $this->__translate( 'Edit model &#8220;%s&#8221;', 'textdomain' ),
				'list_table_row_action_edit'                            => $this->_xtranslate( 'Edit', 'model row action', 'textdomain' ),
				'list_table_row_action_delete_item'                     => $this->__translate( 'Delete model', 'textdomain' ),
				/* translators: %s: model title */
				'list_table_row_action_delete_item_title'               => $this->__translate( 'Delete model &#8220;%s&#8221;', 'textdomain' ),
				'list_table_row_action_delete'                          => $this->_xtranslate( 'Delete', 'model row action', 'textdomain' ),
				'list_table_row_action_view_item'                       => $this->__translate( 'View model', 'textdomain' ),
				/* translators: %s: model title */
				'list_table_row_action_view_item_title'                 => $this->__translate( 'View model &#8220;%s&#8221;', 'textdomain' ),
				'list_table_row_action_view'                            => $this->_xtranslate( 'View', 'model row action', 'textdomain' ),

				/* --- REST Models controller --- */

				'rest_invalid_id'                                       => $this->__translate( 'Invalid model ID.', 'textdomain' ),
				'rest_invalid_author'                                   => $this->_xtranslate( 'Invalid author ID.', 'model endpoint', 'textdomain' ),
				'rest_item_exists'                                      => $this->__translate( 'Cannot create an existing model.', 'textdomain' ),
				'rest_invalid_page_number'                              => $this->_xtranslate( 'The page number requested is larger than the number of pages available.', 'model endpoint', 'textdomain' ),
				'rest_cannot_edit_items'                                => $this->__translate( 'Sorry, you are not allowed to edit models.', 'textdomain' ),
				'rest_cannot_read_items'                                => $this->__translate( 'Sorry, you are not allowed to view models.', 'textdomain' ),
				'rest_cannot_read_others_items'                         => $this->__translate( 'Sorry, you are not allowed to view models by this user.', 'textdomain' ),
				'rest_cannot_edit_item'                                 => $this->__translate( 'Sorry, you are not allowed to edit this model.', 'textdomain' ),
				'rest_cannot_edit_others_item'                       	=> $this->__translate( 'Sorry, you are not allowed to edit a model as this user.', 'textdomain' ),
				'rest_cannot_read_item'                                 => $this->__translate( 'Sorry, you are not allowed to view this model.', 'textdomain' ),
				'rest_cannot_create_items'                              => $this->__translate( 'Sorry, you are not allowed to create models.', 'textdomain' ),
				'rest_cannot_create_others_items'                       => $this->__translate( 'Sorry, you are not allowed to create models as this user.', 'textdomain' ),
				'rest_cannot_delete_item'                               => $this->__translate( 'Sorry, you are not allowed to delete this model.', 'textdomain' ),
				'rest_cannot_publish_item'                              => $this->__translate( 'Sorry, you are not allowed to publish this model.', 'textdomain' ),
				'rest_cannot_view_status'                               => $this->__translate( 'Sorry, you are not allowed to view models of this status.', 'textdomain' ),
				'rest_cannot_view_type'                                 => $this->__translate( 'Sorry, you are not allowed to view models of this type.', 'textdomain' ),
				'rest_item_id_description'                              => $this->__translate( 'Unique identifier for the model.', 'textdomain' ),
				'rest_item_slug_description'                            => $this->__translate( 'A unique alphanumeric identifier for the model.', 'textdomain' ),
				'rest_item_title_description'                           => $this->__translate( 'The title for the model.', 'textdomain' ),
				'rest_item_content_description'                         => $this->__translate( 'The main content of the model.', 'textdomain' ),
				'rest_item_type_description'                            => $this->__translate( 'The type of the model.', 'textdomain' ),
				'rest_item_status_description'                          => $this->__translate( 'The status of the model.', 'textdomain' ),
				'rest_item_author_description'                          => $this->__translate( 'The ID for the author of the model.', 'textdomain' ),
				'rest_item_date_description'                            => $this->__translate( 'The date the model was created.', 'textdomain' ),
				'rest_item_date_date_modified_description'              => $this->__translate( 'The date the model was last modified.', 'textdomain' ),
				'rest_collection_include_description'                   => $this->__translate( 'Limit result set to specific model IDs.', 'textdomain' ),
				'rest_collection_exclude_description'                   => $this->__translate( 'Ensure result set excludes specific model IDs.', 'textdomain' ),
				'rest_collection_orderby_description'                   => $this->__translate( 'Sort collection by model attribute.', 'textdomain' ),
				'rest_collection_order_description'                     => $this->__translate( 'Order model sort attribute ascending or descending.', 'textdomain' ),
				'rest_collection_slug_description'                      => $this->__translate( 'Limit result set to models with one or more specific slugs.', 'textdomain' ),
				'rest_collection_title_description'                     => $this->__translate( 'Limit result set to models with a specific title.', 'textdomain' ),
				'rest_collection_type_description'                      => $this->__translate( 'Limit result set to models with one or more specific types.', 'textdomain' ),
				'rest_collection_status_description'                    => $this->__translate( 'Limit result set to models assigned one or more specific statuses.', 'textdomain' ),
				'rest_collection_author_description'                    => $this->__translate( 'Limit result set to models by a specific author.', 'textdomain' ),
				'rest_collection_date_after_description'                => $this->__translate( 'Limit result set to models created after a given ISO8601 compliant date.', 'textdomain' ),
				'rest_collection_date_before_description'               => $this->__translate( 'Limit result set to models created before a given ISO8601 compliant date.', 'textdomain' ),
				'rest_collection_date_date_modified_after_description'  => $this->__translate( 'Limit result set to models last modified after a given ISO8601 compliant date..', 'textdomain' ),
				'rest_collection_date_date_modified_before_description' => $this->__translate( 'Limit result set to models last modified before a given ISO8601 compliant date.', 'textdomain' ),

				/* --- REST Model types controller --- */

				'rest_invalid_type_slug'                                => $this->__translate( 'Invalid model type slug.', 'textdomain' ),
				'rest_cannot_edit_types'                                => $this->__translate( 'Sorry, you are not allowed to edit models and their types.', 'textdomain' ),
				'rest_cannot_read_types'                                => $this->__translate( 'Sorry, you are not allowed to view models and their types.', 'textdomain' ),
				'rest_cannot_edit_type'                                 => $this->__translate( 'Sorry, you are not allowed to edit models of this type.', 'textdomain' ),
				'rest_cannot_read_type'                                 => $this->__translate( 'Sorry, you are not allowed to view models of this type.', 'textdomain' ),
				'rest_type_slug_description'                            => $this->__translate( 'An alphanumeric identifier for the model type.', 'textdomain' ),
				'rest_type_label_description'                           => $this->__translate( 'The title for the model type.', 'textdomain' ),
				'rest_type_public_description'                          => $this->__translate( 'Whether or not models of that type are publicly accessible.', 'textdomain' ),
				'rest_type_default_description'                         => $this->__translate( 'Whether or not this is the default model type.', 'textdomain' ),

				/* --- REST Model statuses controller --- */

				'rest_invalid_status_slug'                              => $this->__translate( 'Invalid model status slug.', 'textdomain' ),
				'rest_cannot_edit_statuses'                             => $this->__translate( 'Sorry, you are not allowed to edit models and their statuses.', 'textdomain' ),
				'rest_cannot_read_statuses'                             => $this->__translate( 'Sorry, you are not allowed to view models and their statuses.', 'textdomain' ),
				'rest_cannot_edit_status'                               => $this->__translate( 'Sorry, you are not allowed to edit models of this status.', 'textdomain' ),
				'rest_cannot_read_status'                               => $this->__translate( 'Sorry, you are not allowed to view models of this status.', 'textdomain' ),
				'rest_status_slug_description'                          => $this->__translate( 'An alphanumeric identifier for the model status.', 'textdomain' ),
				'rest_status_label_description'                         => $this->__translate( 'The title for the model status.', 'textdomain' ),
				'rest_status_public_description'                        => $this->__translate( 'Whether or not models of that status are publicly accessible.', 'textdomain' ),
				'rest_status_internal_description'                      => $this->__translate( 'Whether or not this status is intended for internal usage only.', 'textdomain' ),
				'rest_status_default_description'                       => $this->__translate( 'Whether or not this is the default model status.', 'textdomain' ),

				/* --- View routing --- */

				'view_routing_base'                                     => $this->_xtranslate( 'models', 'view routing base', 'textdomain' ),
				'view_routing_archive_title'                            => $this->__translate( 'Models', 'textdomain' ),
				/* translators: %s: page number */
				'view_routing_archive_title_page_suffix'                => $this->_xtranslate( 'Page %s', 'model archive title suffix', 'textdomain' ),
				/* translators: %s: model ID */
				'view_routing_singular_fallback_title'                  => $this->__translate( 'Model %s', 'textdomain' ),
			);
		}
	}

endif;
