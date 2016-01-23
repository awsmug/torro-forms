<?php
/**
 * Results base class
 *
 * Class for handling results from database
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 rheinschmiede (contact@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Results {
	/**
	 * Form Id
	 *
	 * @var int $form_id
	 * @since 1.0.0
	 */
	protected $form_id;

	/**
	 * Results
	 *
	 * @var array $results
	 * @since 1.0.0
	 */
	protected $results;

	/**
	 * Count
	 *
	 * @var int $num_rows
	 * @since 1.0.0
	 */
	protected $num_rows;

	/**
	 * Contaims Column Names and SQL added by User
	 *
	 * @var array $added_columns
	 * @since 1.0.0
	 */
	protected $added_columns = array();

	/**
	 * Initializes the Class.
	 *
	 * @param int $form_id
	 * @since 1.0.0
	 */
	public function __construct( $form_id ) {
		// Checking if form exists
		if( false === get_post_status( $form_id ) ) {
			return false;
		}

		$this->form_id = $form_id;

		return true;
	}

	/**
	 * Getting responses of a Form
	 *
	 * @param array $filter
	 *
	 * @return array $responses
	 * @since 1.0.0
	 */
	public function results( $filter = array() ) {
		global $wpdb;

		$filter = wp_parse_args( $filter, array(
			'start_row'		=> 0,
			'num_rows'		=> null,
			'element_ids'	=> null,
			'user_ids'		=> null,
			'result_ids'	=> null,
			'filter'		=> null,
			'orderby'		=> null,
			'order'			=> null,
			'column_name'	=> 'element_id', // label, element_id
		    'refresh_view'	=> true,
		) );

		$form = new Torro_Form( $this->form_id );
		$form_elements = $form->get_elements();

		if ( 0 === count( $form_elements ) ) {
			return false;
		}

		$sql_count = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id = %s", $this->form_id );

		if ( 0 === absint( $wpdb->get_var( $sql_count ) ) ) {
			$this->num_rows = 0;
			return false;
		}

		$view_name = "{$wpdb->prefix}torro_results_{$this->form_id}_view";

		$params = array(
			'view_name'		=> $view_name,
			'element_ids'	=> $filter['element_ids'],
		);

		$column_titles = false;
		if ( true === $filter['refresh_view'] ) {
			$column_titles = $this->create_view( $params );
		}

		if ( false === $column_titles ) {
			return false;
		}

		$sql_filter = "SELECT * FROM {$view_name}";
		$sql_filter_values = array();

		$where_is_set = false;

		if ( null !== $filter['filter'] ) {
			if ( is_array( $filter['filter'] ) ) {
				$count = 0;
				foreach ( $filter['filter'] as $column_name => $value ) {
					$column_name = esc_sql( $column_name );

					if ( 0 === $count && $where_is_set ) {
						$where_is_set = true;
						$sql_filter .= " WHERE `{$column_name}` = %s";
					} else {
						$sql_filter .= " AND `{$column_name}` = %s";
					}
					$sql_filter_values[] = $value ;
					$count++;
				}
			}
		}

		/**
		 * Filtering Result IDs
		 */
		if ( null !== $filter['result_ids'] ) {
			if ( is_array( $filter['result_ids'] ) ) {
				$count = 0;
				foreach( $filter['result_ids'] as $result_id ) {
					if ( 0 === $count && false === $where_is_set ) {
						$where_is_set = true;
						$sql_filter .= " WHERE `result_id` = %d";
					} elseif ( 0 === $count ) {
						$sql_filter .= " AND `result_id` = %d";
					} else {
						$sql_filter .= " OR `result_id` = %d";
					}
					$sql_filter_values[] = $result_id;
					$count++;
				}
			}
		}

		// Order
		if ( null !== $filter['orderby'] ) {
			if ( in_array( $filter['orderby'], $column_titles  ) ) {
				$filter[ 'orderby' ] = esc_sql( $filter[ 'orderby' ] );
				$sql_filter .= " ORDER BY {$filter[ 'orderby' ]}";
			}
		}

		if ( 'ASC' === $filter['order'] || 'DESC' === $filter['order'] ) {
			$sql_filter .= ' ' . $filter['order'];
		}

		// Limiting
		if ( null !== $filter['start_row'] && null !== $filter['num_rows'] ) {
			$sql_filter .= ' LIMIT %d, %d';
			$sql_filter_values[] = (int) $filter['start_row'];
			$sql_filter_values[] = (int) $filter['num_rows'];
		} elseif ( null !== $filter['num_rows'] ) {
			$sql_filter .= ' LIMIT %d';
			$sql_filter_values[] = (int) $filter['num_rows'];
		}

		if( 0 < count( $sql_filter_values ) ) {
			$sql_filter = $wpdb->prepare( $sql_filter, $sql_filter_values );
		}

		$results = $wpdb->get_results( $sql_filter, ARRAY_A );

		if ( ! $results ) {
			return false;
		}

		switch ( $filter['column_name'] ) {
			case 'label':
				foreach ( $results as $result_key => $result ) {
					foreach( $result as $column_name => $column ) {
						$column_arr = explode( '_', $column_name );

						if ( array_key_exists( 0, $column_arr ) && 'element' === $column_arr[0] ) {
							$element_id = $column_arr[ 1 ];
							$element = torro()->elements()->get_registered( $element_id );

							$column_name_new = $element->replace_column_name( $column_name );

							if ( empty( $column_name_new ) ) {
								$column_name_new = $element->label;
							} else {
								$column_name_new = $element->label . ' - ' . $column_name_new;
							}

							$value = $results[ $result_key ][ $column_name ];
							unset( $results[ $result_key ][ $column_name ] );
							$results[ $result_key ][ $column_name_new ] = $value;
						}
					}
				}

				break;
		}

		$this->num_rows = $wpdb->num_rows;

		return $results;
	}

	/**
	 * Counting Results
	 *
	 * Count results after getting results by results() function
	 *
	 * @return int $num_rows
	 * @since 1.0.0
	 */
	public function count() {
		return $this->num_rows;
	}

	/**
	 * Creating result view
	 *
	 * @param array $params
	 *
	 * @return array $column_titles
	 * @since 1.0.0
	 */
	public function create_view( $params = array() ) {
		global $wpdb;

		$params = wp_parse_args( $params, array(
			'view_name'		=> "{$wpdb->prefix}torro_results_{$this->form_id}_view",
			'element_ids'	=> null,
		) );

		/**
		 * Getting elements
		 */
		$sql_elements = "SELECT id, label FROM $wpdb->torro_elements WHERE form_id=%d";
		$sql_elements_values = array( $this->form_id );

		$sql = $wpdb->prepare( $sql_elements, $sql_elements_values );
		$elements = $wpdb->get_results( $sql );

		/**
		 * Preparing columns for form values
		 */
		$sql_columns = array();
		$column_titles = array( 'id', 'label' );
		$column_titles_assigned = array();

		$column_index = 3;
		foreach ( $elements as $element ) {
			if ( is_array( $params['element_ids'] ) ) {
				if ( ! in_array( $element->id, $params['element_ids'], true ) ) {
					continue;
				}
			}

			$element_obj = torro()->elements()->get_registered( $element->id );

			if ( ! $element_obj ) {
				continue;
			}

			if ( ! $element_obj->is_answerable ) {
				continue;
			}

			if ( false !== $element_obj->add_result_columns( $this ) ) {
				continue;
			}

			$column_name = 'element_' . $element->id;

			if ( ! $element_obj->answer_is_multiple && 0 === count( $element_obj->sections ) ) {
				if ( ! empty( $column_name ) ) {
					// Preventing double assigned Column title
					if ( array_key_exists( $column_name, $column_titles_assigned ) ) {
						$column_titles_assigned[ $column_name ]++;
						$column_name = $column_name . ' (' . $column_titles_assigned[ $column_name ] . ')';
					} else {
						$column_titles_assigned[ $column_name ] = 1;
						$column_name = $column_name;
					}

					$sql_columns[] = $wpdb->prepare( "(SELECT value FROM $wpdb->torro_result_values WHERE result_id=row.id AND element_id = %d) AS '%s'", $element->id, $column_name );
					$column_titles[ $column_index++ ] = $column_name;
				}
			} else {
				foreach ( $element_obj->answers as $answer ) {
					$answer = (object) $answer;
					$column_name = 'element_' . $element->id . '_' . $answer->id;

					// Preventing double assigned Column title
					if ( array_key_exists( $column_name, $column_titles_assigned ) ) {
						$column_titles_assigned[ $column_name ]++;
						$column_name = $column_name . ' (' . $column_titles_assigned[ $column_name ] . ')';
					} else {
						$column_titles_assigned[ $column_name ] = 1;
						$column_name = $column_name;
					}

					$sql_columns[] = $wpdb->prepare( "IF( (SELECT value FROM $wpdb->torro_result_values WHERE result_id=row.id AND element_id = %d AND value='%s') is null, 'no', 'yes' ) AS %s", $element->id, $answer->text, $column_name );
					$column_titles[ $column_index++ ] = $column_name;
				}
			}
		}

		// Adding columns aded by 'add_column' function
		foreach ( $this->added_columns as $column ) {
			$column_name = $column[ 'name' ];

			// Preventing double assigned Column title
			if ( array_key_exists( $column_name, $column_titles_assigned ) ) {
				$column_titles_assigned[ $column_name ]++;
				$column_name = $column_name . ' (' . $column_titles_assigned[ $column_name ] . ')';
			} else {
				$column_titles_assigned[ $column_name ] = 1;
				$column_name = $column_name;
			}

			$added_column_sql = "({$column[ 'sql' ]}) AS {$column_name}";
			$sql_columns[] = $added_column_sql;
			$column_titles[ $column_index++ ] = $column_name;
		}

		/**
		 * Creating Result SQL
		 */
		$sql_columns_string = '';
		if ( 0 < count( $sql_columns ) ) {
			$sql_columns_string = ', ' . implode( ', ', $sql_columns );
		}
		$sql_result = "SELECT id AS result_id, user_id, timestamp{$sql_columns_string} FROM $wpdb->torro_results AS row WHERE form_id=%d";
		$sql_result_values = array( $this->form_id );

		/**
		 * Creating View
		 */
		$view_name = "{$wpdb->prefix}torro_results_{$this->form_id}_view";

		$wpdb->query( "DROP VIEW IF EXISTS {$view_name}" );

		$sql_view = "CREATE VIEW {$view_name} AS {$sql_result}";
		$sql_view = $wpdb->prepare( $sql_view, $sql_result_values );

		$result = $wpdb->query( $sql_view );

		if ( ! $result ) {
			return false;
		}

		return $column_titles;
	}

	/**
	 * Adding column to Result View
	 *
	 * Add individual columns by adding a Column name and SQL. The SQL is legal if it returns exactly one result for the
	 * current dataset. Also there are SQL variables which can be handled in the SQL statement:
	 *
	 *  - row.result_id
	 *  - row.user_id
	 *  - row.timestamp
	 *
	 * Example for adding a User Name column:
	 *
	 *  $results = new Torro_Form_Results( 5944 );
	 *  $torro_results->add_column( 'username', "SELECT user_login FROM {$wpdb->prefix}users WHERE ID=row.user_id" );
	 *  $results = $torro_results->results();
	 *
	 * @param string $column_name The Column name will be created in the Result View
	 * @param string $sql         The SQL statement for getting new field in row
	 */
	public function add_column( $column_name, $sql ) {
		$this->added_columns[] = array(
			'name'	=> esc_sql( $column_name ),
			'sql'	=> $sql
		);
	}

	/**
	 * Get all saved results of an element
	 *
	 * @return mixed $responses The results as array or null if there are no results
	 * @since 1.0.0
	 */
	public function element_results( $element_id, $filter = array() ) {
		$filter = wp_parse_args( $filter, array(
			'start_row'		=> 0,
			'number_rows'	=> null,
			'user_ids'		=> null,
			'result_ids'	=> null,
			'filter'		=> null,
			'orderby'		=> null,
			'order'			=> null,
		) );

		$filter['element_ids'] = array( $element_id );

		$results = $this->results( $filter );

		return $results;
	}
}
