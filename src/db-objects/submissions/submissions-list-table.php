<?php
/**
 * Submissions list table class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Table;

/**
 * Class for managing the submissions list table.
 *
 * @since 1.0.0
 */
class Submissions_List_Table extends Models_List_Table {

	/**
	 * Handles the ID column output.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_id( $submission ) {
		$primary_property = $this->manager->get_primary_property();
		$submission_id = $submission->$primary_property;

		$title = '#' . $submission_id;

		$capabilities = $this->manager->capabilities();
		if ( ! empty( $this->_args['model_page'] ) && $capabilities && $capabilities->user_can_edit( null, $submission_id ) ) {
			$edit_url   = add_query_arg( $primary_property, $submission_id, $this->_args['model_page'] );
			$aria_label = sprintf( $this->manager->get_message( 'list_table_title_edit_label' ), $title );

			$title = sprintf( '<a href="%1$s" class="row-title" aria-label="%2$s">%3$s</a>', esc_url( $edit_url ), esc_attr( $aria_label ), $title );
		}

		echo '<strong>' . $title . '</strong>';
	}

	/**
	 * Handles the form ID column output.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_form_id( $submission ) {
		if ( empty( $submission->form_id ) ) {
			return;
		}

		$form = $this->manager->get_parent_manager( 'forms' )->get( $submission->form_id );
		if ( ! $form ) {
			return;
		}

		printf( '<a href="%1$s">%2$s</a>', esc_url( add_query_arg( 'form_id', $form->id, $this->_args['models_page'] ) ), $form->title );
	}

	/**
	 * Handles the user ID column output.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_user_id( $submission ) {
		if ( empty( $submission->user_id ) ) {
			return;
		}

		$user = get_userdata( $submission->user_id );
		if ( ! $user || ! $user->exists() ) {
			return;
		}

		printf( '<a href="%1$s">%2$s</a>', esc_url( add_query_arg( 'user_id', $user->ID, $this->_args['models_page'] ) ), $user->display_name );
	}

	/**
	 * Handles the timestamp column output.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_timestamp( $submission ) {
		if ( empty( $submission->timestamp ) ) {
			return;
		}

		echo date_i18n( get_option( 'date_format' ), $submission->timestamp );
	}

	/**
	 * Returns the available columns for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Columns as `$slug => $label` pairs.
	 */
	protected function build_columns() {
		$columns = parent::build_columns();

		$columns['id']        = _x( 'ID', 'submission column label', 'torro-forms' );
		$columns['form_id']   = _x( 'Form', 'submission column label', 'torro-forms' );
		$columns['user_id']   = _x( 'User', 'submission column label', 'torro-forms' );
		$columns['timestamp'] = _x( 'Date', 'submission column label', 'torro-forms' );

		/**
		 * Filters the payment columns.
		 *
		 * @since 1.0.0
		 *
		 * @param array $columns Associative array of `$column_slug => $column_title` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}submission_admin_columns", $columns );
	}

	/**
	 * Returns the sortable columns for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Sortable columns as `$slug => $orderby` pairs. $orderby
	 *               can be a plain string or an array with the first element
	 *               being the field slug and the second being true to make the
	 *               initial sorting order descending.
	 */
	protected function build_sortable_columns() {
		$sortable_columns = parent::build_sortable_columns();

		$sortable_columns['timestamp'] = array( 'timestamp', true );

		/**
		 * Filters the submission sortable columns.
		 *
		 * @since 1.0.0
		 *
		 * @param array $sortable_columns Associative array of `$column_slug => $column_sort_data` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}submission_admin_sortable_columns", $sortable_columns );
	}

	/**
	 * Returns the available views for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $current  Slug of the current view, passed by reference. Should be
	 *                         set properly in the method.
	 * @param string $list_url Optional. The URL to the list page. Default empty.
	 * @return array Views as `$slug => $data` pairs. The $data array must have keys 'url'
	 *               and 'label' and may additionally have 'class' and 'aria_label'.
	 */
	protected function build_views( &$current, $list_url = '' ) {
		$capabilities = $this->manager->capabilities();

		$current = 'all';
		$total = 0;

		$views = array();

		$user_id = 0;
		if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) || ! $capabilities->current_user_can( 'read_others_items' ) ) {
			$user_id = get_current_user_id();
		} else {
			$user_counts = $this->manager->count( get_current_user_id() );

			if ( isset( $_REQUEST['user_id'] ) && get_current_user_id() === absint( $_REQUEST['user_id'] ) ) {
				$current = 'mine';
			}

			$views['mine'] = array(
				'url'   => add_query_arg( 'user_id', get_current_user_id(), $list_url ),
				'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_mine', true ), $user_counts['_total'] ), number_format_i18n( $user_counts['_total'] ) ),
			);
		}

		$counts = $this->manager->count( $user_id );

		foreach ( $counts as $status => $number ) {
			if ( '_total' === $status ) {
				continue;
			}

			$views[ $status ] = array(
				'url'   => add_query_arg( 'status', $status, $list_url ),
				'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_status_' . $status, true ), $number ), number_format_i18n( $number ) ),
			);

			$total += $number;
		}

		if ( isset( $_REQUEST['status'] ) ) {
			$current = $_REQUEST['status'];
		}

		if ( isset( $user_counts ) && absint( $user_counts['_total'] ) === absint( $total ) ) {
			unset( $views['mine'] );
		}

		if ( ! empty( $views ) ) {
			$views = array_merge( array(
				'all' => array(
					'url'   => $list_url,
					'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_all', true ), $total ), number_format_i18n( $total ) ),
				),
			), $views );
		}

		/**
		 * Filters the submission views.
		 *
		 * @since 1.0.0
		 *
		 * @param array $views Associative array of `$view_slug => $view_data` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}submission_admin_views", $views );
	}

	/**
	 * Returns the available bulk actions for the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Actions as `$slug => $data` pairs. The $data array must have the key
	 *               'label'.
	 */
	protected function build_bulk_actions() {
		$actions = parent::build_bulk_actions();

		/**
		 * Filters the submission bulk actions.
		 *
		 * @since 1.0.0
		 *
		 * @param array $actions Associative array of `$action_slug => $action_data` pairs.
		 */
		return apply_filters( "{$this->manager->get_prefix()}submission_admin_bulk_actions", $actions );
	}

	/**
	 * Returns the available row actions for a given item in the list table.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission    The submission for which to return row actions.
	 * @param int        $submission_id The submission ID.
	 * @param string     $view_url      Optional. The URL to view the submission in the frontend. Default empty.
	 * @param string     $edit_url      Optional. The URL to edit the submission in the backend. Default empty.
	 * @param string     $list_url      Optional. The URL to the list page. Default empty.
	 * @return array Row actions as `$id => $link` pairs.
	 */
	protected function build_row_actions( $submission, $submission_id, $view_url = '', $edit_url = '', $list_url = '' ) {
		$actions = parent::build_row_actions( $submission, $submission_id, $view_url, $edit_url, $list_url );

		/**
		 * Filters the submission row actions.
		 *
		 * @since 1.0.0
		 *
		 * @param array      $actions       Associative array of `$action_slug => $action_link` pairs.
		 * @param Submission $submission    Current submission object.
		 * @param int        $submission_id Current submission ID.
		 */
		return apply_filters( "{$this->manager->get_prefix()}submission_admin_row_actions", $actions, $submission, $submission_id );
	}

	/**
	 * Builds query parameters for the current request.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param int $number Maximum number of models to query.
	 * @param int $offset Offset to query models.
	 * @return array Associative array of query parameters.
	 */
	protected function build_query_params( $number, $offset ) {
		$query_params = parent::build_query_params( $number, $offset );

		$capabilities = $this->manager->capabilities();

		if ( isset( $_REQUEST['form_id'] ) ) {
			$query_params['form_id'] = absint( $_REQUEST['form_id'] );
		}

		if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) || ! $capabilities->current_user_can( 'read_others_items' ) ) {
			$query_params['user_id'] = get_current_user_id();
		} elseif ( isset( $_REQUEST['user_id'] ) ) {
			$query_params['user_id'] = absint( $_REQUEST['user_id'] );
		}

		if ( ! empty( $_REQUEST['status'] ) ) {
			$query_params['status'] = array_map( 'sanitize_key', (array) $_REQUEST['status'] );
		}

		return $query_params;
	}
}
