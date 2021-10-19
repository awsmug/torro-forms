<?php
/**
 * List table class
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\DB_Objects;

use Leaves_And_Love\Plugin_Lib\Fixes;

if ( ! class_exists( 'Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Table' ) ) :

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

	/**
	 * Class to list models in a WordPress admin list table.
	 *
	 * @since 1.0.0
	 */
	abstract class Models_List_Table extends \WP_List_Table {
		/**
		 * The manager instance.
		 *
		 * @since 1.0.0
		 * @var Manager
		 */
		protected $manager;

		/**
		 * Constructor.
		 *
		 * @since 1.0.0
		 *
		 * @see \WP_List_Table::__construct() for more information on default arguments.
		 *
		 * @param Manager $manager The manager instance.
		 * @param array   $args    An associative array of arguments.
		 */
		public function __construct( $manager, $args = array() ) {
			$this->manager = $manager;

			if ( empty( $args['singular'] ) ) {
				$args['singular'] = $this->manager->get_prefix() . $this->manager->get_singular_slug();
			}

			if ( empty( $args['plural'] ) ) {
				$args['plural'] = $this->manager->get_prefix() . $this->manager->get_plural_slug();
			}

			if ( ! isset( $args['models_page'] ) ) {
				$page = filter_input( INPUT_GET, 'page' );
				if ( ! empty( $page ) ) {
					$args['models_page'] = add_query_arg( 'page', $page, self_admin_url( $this->screen->parent_file ) );
				}
			}

			if ( ! isset( $args['model_page'] ) ) {
				if ( false !== strpos( $args['models_page'], $this->manager->get_plural_slug() ) ) {
					$args['model_page'] = str_replace( $this->manager->get_plural_slug(), $this->manager->get_singular_slug(), $args['models_page'] );
				} else {
					$args['model_page'] = '';
				}
			}

			parent::__construct( $args );
		}

		/**
		 * Checks the current user's permissions.
		 *
		 * @since 1.0.0
		 *
		 * @return bool Whether the current user can edit items.
		 */
		public function ajax_user_can() {
			$capabilities = $this->manager->capabilities();

			return ( $capabilities && $capabilities->user_can_edit() );
		}

		/**
		 * Prepares the list of items for displaying.
		 *
		 * @since 1.0.0
		 */
		public function prepare_items() {
			$per_page = $this->get_items_per_page( 'list_' . $this->_args['plural'] . '_per_page' );
			$paged    = $this->get_pagenum();

			$query_params = $this->build_query_params( $per_page, ( $paged - 1 ) * $per_page );

			$collection = $this->manager->query( $query_params );

			$total = $collection->get_total();

			$this->items = $collection->get_raw();

			$this->set_pagination_args(
				array(
					'total_items' => $total,
					'per_page'    => $per_page,
				)
			);
		}

		/**
		 * Displays a message when there are no items.
		 *
		 * @since 1.0.0
		 */
		public function no_items() {
			echo esc_html( $this->manager->get_message( 'list_table_no_items' ) );
		}

		/**
		 * Displays the search box.
		 *
		 * @since 1.0.0
		 *
		 * @param string $text     The 'submit' button label.
		 * @param string $input_id ID attribute value for the search input field.
		 */
		public function search_box( $text, $input_id ) {
			$query_object  = $this->manager->create_query_object();
			$search_fields = $query_object->get_search_fields();

			if ( empty( $search_fields ) ) {
				return;
			}

			parent::search_box( $text, $input_id );
		}

		/**
		 * Handles the checkbox column output.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The current model object.
		 */
		public function column_cb( $model ) {
			$primary_property = $this->manager->get_primary_property();
			$model_id         = $model->$primary_property;

			$capabilities = $this->manager->capabilities();

			if ( ! $capabilities || ! $capabilities->user_can_edit( null, $model_id ) ) {
				return;
			}

			$screen_reader_label = $this->manager->get_message( 'list_table_cb_select_label' );
			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$title_property      = $this->manager->get_title_property();
				$screen_reader_label = sprintf( $this->manager->get_message( 'list_table_cb_select_item_label' ), $model->$title_property );
			}

			echo '<label for="' . esc_attr( 'cb-select-' . $model_id ) . '" class="screen-reader-text">' . wp_kses_data( $screen_reader_label ) . '</label>';
			echo '<input type="checkbox" id="' . esc_attr( 'cb-select-' . $model_id ) . '" name="' . esc_attr( $this->manager->get_plural_slug() . '[]' ) . '" value="' . esc_attr( $model_id ) . '" />';
		}

		/**
		 * Handles the title column output.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The current model object.
		 */
		public function column_title( $model ) {
			$title_property = $this->manager->get_title_property();
			$title          = ! empty( $model->$title_property ) ? $model->$title_property : $this->manager->get_message( 'list_table_no_title' );

			$primary_property = $this->manager->get_primary_property();
			$model_id         = $model->$primary_property;

			$capabilities = $this->manager->capabilities();

			if ( ! empty( $this->_args['model_page'] ) && $capabilities && $capabilities->user_can_edit( null, $model_id ) ) {
				$edit_url   = add_query_arg( $primary_property, $model_id, $this->_args['model_page'] );
				$aria_label = sprintf( $this->manager->get_message( 'list_table_title_edit_label' ), $title );

				$title = sprintf( '<a href="%1$s" class="row-title" aria-label="%2$s">%3$s</a>', esc_url( $edit_url ), esc_attr( $aria_label ), $title );
			}

			echo '<strong>' . $title . '</strong>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Handles the author column output.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The current model object.
		 */
		public function column_author( $model ) {
			$author_property = $this->manager->get_author_property();

			$user = get_userdata( $model->$author_property );
			if ( ! $user ) {
				return;
			}

			printf( '<a href="%1$s">%2$s</a>', esc_url( add_query_arg( $author_property, $user->ID, $this->_args['models_page'] ) ), $user->display_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		/**
		 * Handles the date column output.
		 *
		 * @since 1.0.0
		 *
		 * @param Model $model The current model object.
		 */
		public function column_date( $model ) {
			$date_property = $this->manager->get_date_property();
			$date          = $model->$date_property;

			if ( empty( $date ) || '0000-00-00 00:00:00' === $date ) {
				return;
			}

			echo esc_html( mysql2date( get_option( 'date_format' ), $date ) );
		}

		/**
		 * Handles the default column output.
		 *
		 * @since 1.0.0
		 *
		 * @param Model  $model       The current model object.
		 * @param string $column_name The current column name.
		 */
		public function column_default( $model, $column_name ) {
			if ( has_action( "{$this->_args['plural']}_list_table_column_{$column_name}" ) ) {
				/**
				 * Fires when a column without a specific callback should be rendered.
				 *
				 * The dynamic parts of the action refer to the manager's plural slug and
				 * to the column name respectively.
				 *
				 * @since 1.0.0
				 *
				 * @param Model   $model   The current model object.
				 * @param Manager $manager The manager instance.
				 */
				do_action( "{$this->_args['plural']}_list_table_column_{$column_name}", $model, $this->manager );
				return;
			}

			if ( ! isset( $model->$column_name ) ) {
				return;
			}

			$value = $model->$column_name;

			if ( is_array( $value ) || is_object( $value ) ) {
				return;
			}

			if ( is_string( $value ) && preg_match( '/^\d\d\d\d-(\d)?\d-(\d)?\d \d\d:\d\d:\d\d$/', $value ) ) {
				if ( '0000-00-00 00:00:00' === $value ) {
					return;
				}

				$value = mysql2date( get_option( 'date_format' ), $value );
			} elseif ( is_int( $value ) ) {
				$value = number_format_i18n( $value );
			} elseif ( is_float( $value ) ) {
				$value = number_format_i18n( $value, 2 );
			} elseif ( is_bool( $value ) ) {
				$value = $value ? $this->manager->get_message( 'list_table_yes' ) : $this->manager->get_message( 'list_table_no' );
			}

			echo esc_html( $value );
		}

		/**
		 * Gets a list of columns.
		 *
		 * @since 1.0.0
		 *
		 * @return array Columns as `$slug => $label` pairs.
		 */
		public function get_columns() {
			$columns = $this->build_columns();

			/**
			 * Filters the list table columns.
			 *
			 * The dynamic part of the filter refers to the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $columns Columns as `$slug => $label` pairs.
			 * @param Manager $manager The manager instance.
			 */
			return apply_filters( "{$this->_args['plural']}_list_table_columns", $columns, $this->manager );
		}

		/**
		 * Gets a list of sortable columns.
		 *
		 * @since 1.0.0
		 *
		 * @return array Sortable columns as `$slug => $orderby` pairs. $orderby
		 *               can be a plain string or an array with the first element
		 *               being the field slug and the second being true to make the
		 *               initial sorting order descending.
		 */
		protected function get_sortable_columns() {
			$columns = $this->build_sortable_columns();

			/**
			 * Filters the sortable list table columns.
			 *
			 * The dynamic part of the filter refers to the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $columns Sortable columns as `$slug => $orderby` pairs.
			 * @param Manager $manager The manager instance.
			 */
			return apply_filters( "{$this->_args['plural']}_list_table_sortable_columns", $columns, $this->manager );
		}

		/**
		 * Generates and display row actions links for the list table.
		 *
		 * @since 1.0.0
		 *
		 * @param Model  $model       The model being acted upon.
		 * @param string $column_name Current column name.
		 * @param string $primary     Primary column name.
		 * @return string The row actions HTML, or an empty string if the current column is not the primary column.
		 */
		protected function handle_row_actions( $model, $column_name, $primary ) {
			if ( $column_name !== $primary ) {
				return '';
			}

			$primary_property = $this->manager->get_primary_property();
			$model_id         = $model->$primary_property;

			$view_url = $model->get_permalink();

			$edit_url = '';
			if ( ! empty( $this->_args['model_page'] ) ) {
				$edit_url = add_query_arg( $primary_property, $model_id, $this->_args['model_page'] );
			}

			$actions = $this->build_row_actions( $model, $model_id, $view_url, $edit_url, $this->_args['models_page'] );

			/**
			 * Filters the list of available row actions.
			 *
			 * The dynamic part of the filter refers to the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $actions  Row actions as `$slug => $data` pairs.
			 * @param Model   $model    The current model.
			 * @param Manager $manager  The manager instance.
			 * @param string  $view_url The URL to view the model in the frontend, if available.
			 * @param string  $edit_url The URL to edit the model in the backend, if available.
			 * @param string  $list_url The URL to the list page.
			 */
			$actions = apply_filters( "{$this->_args['plural']}_list_table_row_actions", $actions, $model, $this->manager, $view_url, $edit_url, $this->_args['models_page'] );

			$links = array();

			$nonce = wp_create_nonce( 'row-' . $this->_args['singular'] . '-' . $model_id );

			foreach ( $actions as $slug => $data ) {
				$class      = ! empty( $data['class'] ) ? ' class="' . esc_attr( $data['class'] ) . '"' : '';
				$aria_label = ! empty( $data['aria_label'] ) ? ' aria-label="' . esc_attr( $data['aria_label'] ) . '"' : '';

				$action_url = $data['url'];
				if ( 0 === strpos( $action_url, $edit_url ) ) {
					$action_url = add_query_arg(
						array(
							'_wpnonce'         => $nonce,
							'_wp_http_referer' => rawurlencode( Fixes::php_filter_input( INPUT_SERVER, 'REQUEST_URI' ) ),
						),
						$action_url
					);
				}

				$links[ $slug ] = sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url( $action_url ), $class, $aria_label, $data['label'] );
			}

			return $this->row_actions( $links );
		}

		/**
		 * Gets an associative array with the list of views available on
		 * this table.
		 *
		 * @since 1.0.0
		 *
		 * @return array Views as `$slug => $link` pairs.
		 */
		protected function get_views() {
			$current = '';

			$views = $this->build_views( $current, $this->_args['models_page'] );

			/**
			 * Filters the list of available views.
			 *
			 * The dynamic part of the filter refers to the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $views    Views as `$slug => $data` pairs.
			 * @param Manager $manager  The manager instance.
			 * @param string  $list_url The URL to the list page.
			 */
			$views = apply_filters( "{$this->_args['plural']}_list_table_views", $views, $this->manager, $this->_args['models_page'] );

			$links = array();

			foreach ( $views as $slug => $data ) {
				if ( $current === $slug ) {
					if ( ! empty( $data['class'] ) ) {
						$data['class'] .= ' current';
					} else {
						$data['class'] = 'current';
					}
				}

				$class      = ! empty( $data['class'] ) ? ' class="' . esc_attr( $data['class'] ) . '"' : '';
				$aria_label = ! empty( $data['aria_label'] ) ? ' aria-label="' . esc_attr( $data['aria_label'] ) . '"' : '';

				$links[ $slug ] = sprintf( '<a href="%1$s"%2$s%3$s>%4$s</a>', esc_url( $data['url'] ), $class, $aria_label, $data['label'] );
			}

			return $links;
		}

		/**
		 * Gets an associative array with the list of bulk actions available
		 * on this table.
		 *
		 * @since 1.0.0
		 *
		 * @return array Bulk actions as `$slug => $label` pairs.
		 */
		protected function get_bulk_actions() {
			$actions = $this->build_bulk_actions();

			/**
			 * Filters the list of available bulk actions.
			 *
			 * The dynamic part of the filter refers to the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $actions Bulk actions as `$slug => $data` pairs.
			 * @param Manager $manager The manager instance.
			 */
			$actions = apply_filters( "{$this->_args['plural']}_list_table_bulk_actions", $actions, $this->manager );

			$options = array();

			foreach ( $actions as $slug => $data ) {
				$options[ $slug ] = $data['label'];
			}

			return $options;
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
			$query_params = array(
				'number' => $number,
				'offset' => $offset,
			);

			$default_orderby = $this->manager->get_primary_property();
			$default_order   = 'ASC';

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$default_orderby = $this->manager->get_title_property();
				$default_order   = 'ASC';
			}

			if ( method_exists( $this->manager, 'get_type_property' ) ) {
				$type_property = $this->manager->get_type_property();

				if ( filter_has_var( INPUT_GET, $type_property ) ) {
					$query_params[ $type_property ] = filter_input( INPUT_GET, $type_property );
				}
			}

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$internal_statuses = array_keys( $this->manager->statuses()->query( array( 'internal' => true ) ) );

				if ( filter_has_var( INPUT_GET, $status_property ) ) {
					$query_params[ $status_property ] = filter_input( INPUT_GET, $status_property, FILTER_DEFAULT, FILTER_FORCE_ARRAY );
				}

				if ( ! empty( $internal_statuses ) ) {
					if ( isset( $query_params[ $status_property ] ) ) {
						$query_params[ $status_property ] = array_diff( $query_params[ $status_property ], $internal_statuses );
					} else {
						$query_params[ $status_property ] = array_diff( array_keys( $this->manager->statuses()->query() ), $internal_statuses );
					}
				}
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				$capabilities = $this->manager->capabilities();

				if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) {
					$query_params[ $author_property ] = get_current_user_id();
				} elseif ( filter_has_var( INPUT_GET, $author_property ) ) {
					$query_params[ $author_property ] = filter_input( INPUT_GET, $author_property );
				}
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$date_property = $this->manager->get_date_property();

				$default_orderby = $date_property;
				$default_order   = 'DESC';

				if ( filter_has_var( INPUT_GET, 'm' ) ) {
					$yearmonth = filter_input( INPUT_GET, 'm' );
					if ( ! empty( $yearmonth ) ) {
						$query_params['date_query'] = array(
							array(
								'year'  => substr( $yearmonth, 0, 4 ),
								'month' => substr( $yearmonth, 4, 2 ),
							),
						);
					}
				}
			}

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

			$query_object = $this->manager->create_query_object();

			$search_fields = $query_object->get_search_fields();
			if ( ! empty( $search_fields ) && filter_has_var( INPUT_GET, 's' ) ) {
				$query_params['search'] = trim( filter_input( INPUT_GET, 's' ) );
			}

			/**
			 * Filters the query parameters for the list table query.
			 *
			 * The dynamic part of the filter refers to the prefix and the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param array   $query_params The query parameters as `$key => $value` pairs.
			 * @param Manager $manager      The manager instance.
			 */
			return apply_filters( "{$this->_args['plural']}_list_table_query_params", $query_params, $this->manager );
		}

		/**
		 * Prints extra controls to be displayed between bulk actions and pagination.
		 *
		 * @since 1.0.0
		 *
		 * @param string $which Either 'top' or 'bottom'.
		 */
		protected function extra_tablenav( $which ) {
			echo '<div class="alignleft actions">';

			if ( 'top' === $which ) {
				ob_start();

				$this->print_filters();

				$output = ob_get_clean();

				if ( ! empty( $output ) ) {
					echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					submit_button( $this->manager->get_message( 'list_table_filter_button_label' ), '', 'filter_action', false, array( 'id' => $this->_args['singular'] . '-query-submit' ) );
				}
			}

			echo '</div>';
		}

		/**
		 * Prints input fields for filtering.
		 *
		 * @since 1.0.0
		 */
		protected function print_filters() {
			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$this->months_dropdown( $this->manager->get_date_property() );
			}

			/**
			 * Fires when the list table filters are printed.
			 *
			 * The dynamic part of the filter refers to the prefix and the manager's plural slug.
			 *
			 * @since 1.0.0
			 *
			 * @param Manager $manager The manager instance.
			 */
			do_action( "{$this->_args['plural']}_list_table_print_filters", $this->manager );
		}

		/**
		 * Displays a monthly dropdown for filtering.
		 *
		 * @since 1.0.0
		 *
		 * @global WP_Locale $wp_locale
		 *
		 * @param string $date_property The date property.
		 */
		protected function months_dropdown( $date_property ) {
			global $wp_locale;

			$where      = '';
			$where_args = array();

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

			$months = $this->manager->db()->get_results( "SELECT DISTINCT YEAR( $date_property ) AS year, MONTH( $date_property ) AS month FROM %{$table_name}% WHERE 1=1 $where ORDER BY $date_property DESC", $where_args );

			$month_count = count( $months );

			if ( ! $month_count || ( 1 === $month_count && 0 === (int) $months[0]->month ) ) {
				return;
			}

			$m = filter_input( INPUT_GET, 'm', FILTER_SANITIZE_NUMBER_INT );
			if ( empty( $m ) ) {
				$m = 0;
			}

			echo '<label for="filter-by-date" class="screen-reader-text">' . esc_html( $this->manager->get_message( 'list_table_filter_by_date_label' ) ) . '</label>';
			echo '<select id="filter-by-date" name="m">';
			echo '<option value="0"' . selected( $m, 0, false ) . '>' . esc_html( $this->manager->get_message( 'list_table_all_dates' ) ) . '</option>';

			foreach ( $months as $row ) {
				if ( 0 === (int) $row->year ) {
					continue;
				}

				$month = zeroise( $row->month, 2 );
				$year  = $row->year;

				printf( '<option value="%1$s" %2$s>%3$s</option>', esc_attr( $year . $month ), selected( $m, $year . $month, false ), esc_html( sprintf( $this->manager->get_message( 'list_table_month_year' ), $wp_locale->get_month( $month ), $year ) ) );
			}

			echo '</select>';
		}

		/**
		 * Returns the available columns for the list table.
		 *
		 * @since 1.0.0
		 *
		 * @return array Columns as `$slug => $label` pairs.
		 */
		protected function build_columns() {
			$columns       = array();
			$columns['cb'] = '<input type="checkbox" />';

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$columns['title'] = $this->manager->get_message( 'list_table_column_label_title' );
			}

			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$columns['author'] = $this->manager->get_message( 'list_table_column_label_author' );
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$columns['date'] = $this->manager->get_message( 'list_table_column_label_date' );
			}

			return $columns;
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
			$sortable_columns = array();

			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$sortable_columns['title'] = $this->manager->get_title_property();
			}

			if ( method_exists( $this->manager, 'get_date_property' ) ) {
				$sortable_columns['date'] = array( $this->manager->get_date_property(), true );
			}

			return $sortable_columns;
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

			$current = 'all';
			$total   = 0;

			$views = array();

			$user_id = 0;
			if ( method_exists( $this->manager, 'get_author_property' ) ) {
				$author_property = $this->manager->get_author_property();

				if ( ! $capabilities || ! $capabilities->current_user_can( 'edit_others_items' ) ) {
					$user_id = get_current_user_id();
				} else {
					$user_counts = $this->manager->count( get_current_user_id() );

					if ( filter_has_var( INPUT_GET, $author_property ) && get_current_user_id() === (int) filter_input( INPUT_GET, $author_property, FILTER_SANITIZE_NUMBER_INT ) ) {
						$current = 'mine';
					}

					$views['mine'] = array(
						'url'   => add_query_arg( $author_property, get_current_user_id(), $list_url ),
						'label' => sprintf( translate_nooped_plural( $this->manager->get_message( 'list_table_view_mine', true ), $user_counts['_total'] ), number_format_i18n( $user_counts['_total'] ) ),
					);
				}
			}

			$counts = $this->manager->count( $user_id );

			if ( method_exists( $this->manager, 'get_status_property' ) ) {
				$status_property = $this->manager->get_status_property();

				$available_statuses = $this->manager->statuses()->query();
				foreach ( $counts as $status => $number ) {
					if ( '_total' === $status || ! isset( $available_statuses[ $status ] ) ) {
						continue;
					}

					if ( $available_statuses[ $status ]->internal ) {
						continue;
					}

					$total += $number;

					$view_label = $available_statuses[ $status ]->view_status_label;
					if ( empty( $view_label ) ) {
						$view_label = $this->manager->get_message( 'list_table_view_status_' . $status, true );
						if ( empty( $view_label ) ) {
							continue;
						}
					}

					$views[ $status ] = array(
						'url'   => add_query_arg( $status_property, $status, $list_url ),
						'label' => sprintf( translate_nooped_plural( $view_label, $number ), number_format_i18n( $number ) ),
					);
				}

				$current_status = filter_input( INPUT_GET, $status_property );
				if ( ! empty( $current_status ) && isset( $views[ $current_status ] ) ) {
					$current = $current_status;
				}
			} else {
				$total = $counts['_total'];
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

			return $views;
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
			$actions = array();

			$capabilities = $this->manager->capabilities();
			if ( $capabilities && $capabilities->user_can_delete() ) {
				$actions['delete'] = array(
					'label' => $this->manager->get_message( 'list_table_bulk_action_delete' ),
				);
			}

			return $actions;
		}

		/**
		 * Returns the available row actions for a given item in the list table.
		 *
		 * @since 1.0.0
		 *
		 * @param Model  $model    The model for which to return row actions.
		 * @param int    $model_id The model ID.
		 * @param string $view_url Optional. The URL to view the model in the frontend. Default empty.
		 * @param string $edit_url Optional. The URL to edit the model in the backend. Default empty.
		 * @param string $list_url Optional. The URL to the list page. Default empty.
		 * @return array Row actions as `$id => $link` pairs.
		 */
		protected function build_row_actions( $model, $model_id, $view_url = '', $edit_url = '', $list_url = '' ) {
			$actions = array();

			$title = null;
			if ( method_exists( $this->manager, 'get_title_property' ) ) {
				$title_property = $this->manager->get_title_property();
				$title          = $model->$title_property;
			}

			$capabilities = $this->manager->capabilities();
			if ( ! empty( $edit_url ) && $capabilities ) {
				if ( $capabilities->user_can_edit( null, $model_id ) ) {
					$aria_label = $this->manager->get_message( 'list_table_row_action_edit_item' );
					if ( null !== $title ) {
						$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_edit_item_title' ), $title );
					}

					$actions['edit'] = array(
						'url'        => $edit_url,
						'label'      => $this->manager->get_message( 'list_table_row_action_edit' ),
						'aria_label' => $aria_label,
					);
				}

				if ( $capabilities->user_can_delete( null, $model_id ) ) {
					$aria_label = $this->manager->get_message( 'list_table_row_action_delete_item' );
					if ( null !== $title ) {
						$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_delete_item_title' ), $title );
					}

					$actions['delete'] = array(
						'url'        => add_query_arg( 'action', 'delete', $edit_url ),
						'label'      => $this->manager->get_message( 'list_table_row_action_delete' ),
						'aria_label' => $aria_label,
						'class'      => 'submitdelete',
					);
				}
			}

			if ( ! empty( $view_url ) ) {
				$show_view = true;

				if ( method_exists( $this->manager, 'get_status_property' ) ) {
					$status_property = $this->manager->get_status_property();

					$public_statuses = $this->manager->statuses()->get_public();

					if ( ! in_array( $model->$status_property, $public_statuses, true ) && ( ! $capabilities || ! $capabilities->user_can_edit( null, $model_id ) ) ) {
						$show_view = false;
					}
				}

				if ( $show_view ) {
					$aria_label = $this->manager->get_message( 'list_table_row_action_view_item' );
					if ( null !== $title ) {
						$aria_label = sprintf( $this->manager->get_message( 'list_table_row_action_view_item_title' ), $title );
					}

					$actions['view'] = array(
						'url'        => $view_url,
						'label'      => $this->manager->get_message( 'list_table_row_action_view' ),
						'aria_label' => $aria_label,
					);
				}
			}

			return $actions;
		}
	}

endif;
