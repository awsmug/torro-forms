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
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_id( $submission ) {
		$primary_property = $this->manager->get_primary_property();
		$submission_id    = $submission->$primary_property;

		$title = '#' . $submission_id;

		$capabilities = $this->manager->capabilities();
		if ( ! empty( $this->_args['model_page'] ) && $capabilities && $capabilities->user_can_edit( null, $submission_id ) ) {
			$edit_url   = add_query_arg( $primary_property, $submission_id, $this->_args['model_page'] );
			$aria_label = sprintf( $this->manager->get_message( 'list_table_title_edit_label' ), $title );

			$title = sprintf( '<a href="%1$s" class="row-title" aria-label="%2$s">%3$s</a>', esc_url( $edit_url ), esc_attr( $aria_label ), $title );
		}

		echo '<strong>' . $title . '</strong>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Handles the status column output.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_status( $submission ) {
		if ( 'completed' === $submission->status ) {
			echo '<strong>' . esc_html_x( 'Completed', 'submission status label', 'torro-forms' ) . '</strong>';
		} elseif ( 'progressing' === $submission->status ) {
			echo esc_html_x( 'In Progress', 'submission status label', 'torro-forms' );
		}
	}

	/**
	 * Handles the form ID column output.
	 *
	 * @since 1.0.0
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

		printf( '<a href="%1$s">%2$s</a>', esc_url( add_query_arg( 'form_id', $form->id, $this->_args['models_page'] ) ), wp_kses_data( $form->title ) );
	}

	/**
	 * Handles the user ID column output.
	 *
	 * @since 1.0.0
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

		$form_id = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );

		$url = add_query_arg( 'user_id', $user->ID, $this->_args['models_page'] );
		if ( ! empty( $form_id ) ) {
			$url = add_query_arg( 'form_id', $form_id, $url );
		}

		printf( '<a href="%1$s">%2$s</a>', esc_url( $url ), wp_kses_data( $user->display_name ) );
	}

	/**
	 * Handles the timestamp column output.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission The current submission object.
	 */
	public function column_timestamp( $submission ) {
		if ( empty( $submission->timestamp ) ) {
			return;
		}

		echo esc_html( date_i18n( get_option( 'date_format' ), $submission->timestamp ) );
	}

	/**
	 * Returns the available columns for the list table.
	 *
	 * @since 1.0.0
	 *
	 * @return array Columns as `$slug => $label` pairs.
	 */
	protected function build_columns() {
		$columns = parent::build_columns();

		$columns['id']        = _x( 'ID', 'submission column label', 'torro-forms' );
		$columns['status']    = _x( 'Status', 'submission column label', 'torro-forms' );
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
	 *
	 * @param string $current  Slug of the current view, passed by reference. Should be
	 *                         set properly in the method.
	 * @param string $list_url Optional. The URL to the list page. Default empty.
	 * @return array Views as `$slug => $data` pairs. The $data array must have keys 'url'
	 *               and 'label' and may additionally have 'class' and 'aria_label'.
	 */
	protected function build_views( &$current, $list_url = '' ) {
		$capabilities = $this->manager->capabilities();

		$form_id = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );
		if ( empty( $form_id ) ) {
			$form_id = 0;
		}

		$current = 'all';
		$total   = 0;

		$views = array();

		if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) || ! $capabilities->current_user_can( 'read_others_items' ) ) {
			$user_id = get_current_user_id();
		} else {
			$user_counts = $this->manager->count( get_current_user_id(), $form_id );

			$user_id = filter_input( INPUT_GET, 'user_id', FILTER_VALIDATE_INT );
			if ( empty( $user_id ) ) {
				$user_id = 0;
			}

			if ( get_current_user_id() === absint( $user_id ) ) {
				$current = 'mine';
			}

			$views['mine'] = array(
				'url'   => add_query_arg( 'user_id', get_current_user_id(), $list_url ),
				'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_mine', true ), $user_counts['_total'] ), number_format_i18n( $user_counts['_total'] ) ),
			);
		}

		$counts = $this->manager->count( $user_id, $form_id );

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

		$current_status = filter_input( INPUT_GET, 'status' );
		if ( ! empty( $current_status ) ) {
			$current = $current_status;
		}

		if ( isset( $user_counts ) && absint( $user_counts['_total'] ) === absint( $total ) ) {
			unset( $views['mine'] );
		}

		if ( ! empty( $views ) ) {
			$views = array_merge(
				array(
					'all' => array(
						'url'   => $list_url,
						'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_all', true ), $total ), number_format_i18n( $total ) ),
					),
				),
				$views
			);
		}

		if ( $form_id > 0 ) {
			foreach ( $views as $slug => $data ) {
				$views[ $slug ]['url'] = add_query_arg( 'form_id', $form_id, $data['url'] );
			}
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
	 *
	 * @param int $number Maximum number of models to query.
	 * @param int $offset Offset to query models.
	 * @return array Associative array of query parameters.
	 */
	protected function build_query_params( $number, $offset ) {
		$query_params = parent::build_query_params( $number, $offset );

		$capabilities = $this->manager->capabilities();

		if ( filter_has_var( INPUT_GET, 'form_id' ) ) {
			$query_params['form_id'] = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );
		}

		if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) || ! $capabilities->current_user_can( 'read_others_items' ) ) {
			$query_params['user_id'] = get_current_user_id();
		} elseif ( filter_has_var( INPUT_GET, 'user_id' ) ) {
			$query_params['user_id'] = filter_input( INPUT_GET, 'user_id', FILTER_VALIDATE_INT );
		}

		if ( filter_has_var( INPUT_GET, 'status' ) ) {
			$query_params['status'] = array_map( 'sanitize_key', filter_input( INPUT_GET, 'status', FILTER_DEFAULT, FILTER_FORCE_ARRAY ) );
		}

		$yearmonth = filter_input( INPUT_GET, 'm' );
		if ( ! empty( $yearmonth ) ) {
			$year  = (int) substr( $yearmonth, 0, 4 );
			$month = (int) substr( $yearmonth, 4, 2 );

			$yearmonth = '' . $year . '-' . $month;
			if ( 12 === $month ) {
				$next_yearmonth = '' . ( $year + 1 ) . '-01';
			} else {
				$next_yearmonth = '' . $year . '-' . ( $month + 1 );
			}

			$query_params['timestamp'] = array(
				'greater_than' => (int) strtotime( $yearmonth . '-01' ),
				'lower_than'   => (int) strtotime( $next_yearmonth . '-01' ) - 1,
				'inclusive'    => true,
			);
		}

		$default_orderby = 'timestamp';
		$default_order   = 'DESC';

		$orderby = filter_input( INPUT_GET, 'orderby' );
		$order   = filter_input( INPUT_GET, 'order' );
		if ( ! empty( $orderby ) && ! empty( $order ) ) {
			$query_params['orderby'] = array( $orderby => $order );
		} elseif ( ! empty( $orderby ) ) {
			$query_params['orderby'] = array( $orderby => $default_order );
		} elseif ( ! empty( $order ) ) {
			$query_params['orderby'] = array( $default_orderby => $order );
		} else {
			$query_params['orderby'] = array( $default_orderby => $default_order );
		}

		return $query_params;
	}

	/**
	 * Prints input fields for filtering.
	 *
	 * @since 1.0.0
	 */
	protected function print_filters() {
		$this->timestamp_months_dropdown( 'timestamp' );

		parent::print_filters();
	}

	/**
	 * Displays a monthly timestamp dropdown for filtering.
	 *
	 * @since 1.0.0
	 *
	 * @global WP_Locale $wp_locale
	 *
	 * @param string $timestamp_property The timestamp property.
	 */
	protected function timestamp_months_dropdown( $timestamp_property ) {
		global $wp_locale;

		$where      = '';
		$where_args = array();

		$form_id = filter_input( INPUT_GET, 'form_id', FILTER_VALIDATE_INT );
		if ( ! empty( $form_id ) ) {
			$where       .= ' AND form_id = %d';
			$where_args[] = $form_id;
		}

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			$capabilities = $this->manager->capabilities();
			if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) {
				$where       .= " AND $author_property = %d";
				$where_args[] = get_current_user_id();
			}
		}

		if ( method_exists( $this->manager, 'get_status_property' ) ) {
			$status_property   = $this->manager->get_status_property();
			$internal_statuses = array_keys( $this->manager->statuses()->query( array( 'internal' => true ) ) );

			if ( ! empty( $internal_statuses ) ) {
				$where     .= " AND $status_property NOT IN (" . implode( ',', array_fill( 0, count( $internal_statuses ), '%s' ) ) . ')';
				$where_args = array_merge( $where_args, $internal_statuses );
			}
		}

		$table_name = $this->manager->get_table_name();

		$months = $this->manager->db()->get_results( "SELECT DISTINCT YEAR( FROM_UNIXTIME( $timestamp_property ) ) AS year, MONTH( FROM_UNIXTIME( $timestamp_property ) ) AS month FROM %{$table_name}% WHERE 1=1 $where ORDER BY $timestamp_property DESC", $where_args );

		$month_count = count( $months );

		if ( ! $month_count || ( 1 === $month_count && 0 === (int) $months[0]->month ) ) {
			return;
		}

		$current_month = filter_input( INPUT_GET, 'm', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $current_month ) ) {
			$current_month = 0;
		}

		echo '<label for="filter-by-date" class="screen-reader-text">' . esc_html( $this->manager->get_message( 'list_table_filter_by_date_label' ) ) . '</label>';
		echo '<select id="filter-by-date" name="m">';
		echo '<option value="0"' . selected( $current_month, 0, false ) . '>' . esc_html( $this->manager->get_message( 'list_table_all_dates' ) ) . '</option>';

		foreach ( $months as $row ) {
			if ( 0 === (int) $row->year ) {
				continue;
			}

			$month = zeroise( $row->month, 2 );
			$year  = $row->year;

			printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $year . $month ), selected( $current_month, $year . $month, false ), esc_html( sprintf( $this->manager->get_message( 'list_table_month_year' ), $wp_locale->get_month( $month ), $year ) ) );
		}

		echo '</select>';
	}
}
