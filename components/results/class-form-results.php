<?php
/**
 * Results base class
 *
 * Class for handling results from database
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package AwesomeForms
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

if( !defined( 'ABSPATH' ) )
{
	exit;
}

class AF_Form_Results
{
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
	public function __construct( $form_id )
	{
		// Checking if form exists
		if( FALSE === get_post_status( $form_id ) )
		{
			return FALSE;
		}

		$this->form_id = $form_id;

		return TRUE;
	}

	/**
	 * Getting responses of a Form
	 *
	 * @param array $filter
	 *
	 * @return array $responses
	 * @since 1.0.0
	 */
	public function results( $filter = array() )
	{
		global $wpdb;

		$filter = wp_parse_args( $filter, array(
			'start_row'       => 0,
			'number_rows' => NULL,
			'element_ids' => NULL,
			'user_ids'    => NULL,
			'result_ids'  => NULL,
			'filter'      => NULL,
			'orderby'     => NULL,
			'order'       => NULL,
			'column_name' => 'label', // label, element_id
		) );

		$view_name = "{$wpdb->prefix}af_results_{$this->form_id}_view";

		$params = array(
			'view_name' => $view_name,
			'element_ids' => $filter[ 'element_ids' ],
			'column_name' => $filter[ 'column_name' ]
		);

		$column_titles = $this->create_view( $params );

		if( FALSE == $column_titles )
		{
			return FALSE;
		}

		$sql_filter = "SELECT * FROM {$view_name}";
		$sql_filter_values = array();

		if( NULL !== $filter[ 'filter' ] )
		{
			if( is_array( $filter[ 'filter' ] ) )
			{
				$count_filter = 0;
				foreach( $filter[ 'filter' ] AS $column_name => $value )
				{
					$column_name = esc_sql( $column_name );

					if( 0 === $count_filter )
					{
						$sql_filter .= " WHERE `{$column_name}` = %s";
					}
					else
					{
						$sql_filter .= " AND `{$column_name}` = %s";
					}
					$sql_filter_values[] = $value ;
					$count_filter++;
				}
			}
		}

		// Order
		if( NULL !== $filter[ 'orderby' ] )
		{
			if( in_array( $filter[ 'orderby' ], $column_titles  ) )
			{
				$filter[ 'orderby' ] = esc_sql( $filter[ 'orderby' ] );
				$sql_filter .= " ORDER BY {$filter[ 'orderby' ]}";
			}
		}

		if( 'ASC' == $filter[ 'order' ] || 'DESC' == $filter[ 'order' ] )
		{
			$sql_filter .= ' ' . $filter[ 'order' ];
		}

		// Limiting
		if( NULL !== $filter[ 'start_row' ] && NULL !== $filter[ 'number_rows' ] )
		{
			$sql_filter .= ' LIMIT %d, %d';
			$sql_filter_values[] = (int) $filter[ 'start_row' ];
			$sql_filter_values[] = (int) $filter[ 'number_rows' ];
		}
		elseif( NULL === $filter[ 'start_row' ] && NULL !== $filter[ 'number_rows' ] )
		{
			$sql_filter .= ' LIMIT %d';
			$sql_filter_values[] = (int) $filter[ 'number_rows' ];
		}

		$sql_string = $wpdb->prepare( $sql_filter, $sql_filter_values );
		$results = $wpdb->get_results( $sql_string, ARRAY_A );

		return $results;
	}

	/**
	 * Creating result view
	 *
	 * @param array $params
	 *
	 * @return array $column_titles
	 * @since 1.0.0
	 */
	public function create_view( $params = array() )
	{
		global $wpdb, $af_global;

		$params = wp_parse_args( $params, array(
			'view_name'   => "{$wpdb->prefix}af_results_{$this->form_id}_view",
			'element_ids' => NULL,
			'column_name' => 'label', // label, element_id
		) );

		/**
		 * Getting elements
		 */
		$sql_elements = "SELECT id, label FROM {$af_global->tables->elements} WHERE form_id=%d";
		$sql_elements_values = array( $this->form_id );

		$sql = $wpdb->prepare( $sql_elements, $sql_elements_values );
		$elements = $wpdb->get_results( $sql );

		/**
		 * Preparing columns for form values
		 */
		$sql_columns = array();
		$column_titles = array( 'id', 'label' );
		$column_index = 3;
		foreach( $elements AS $element )
		{
			if( NULL != $params[ 'element_ids' ] && is_array( $params[ 'element_ids' ] ))
			{
				if( !in_array( $element->id, $params[ 'element_ids' ] ) )
				{
					continue;
				}
			}

			$element_obj = af_get_element( $element->id );

			switch( $params[ 'column_name' ] )
			{
				case 'element_id':
					$column_name = 'element_' . $element->id;
					break;
				default:
					$column_name = $element->label;
					break;
			}

			if( !$element_obj->answer_is_multiple )
			{
				$sql_columns[] = $wpdb->prepare( "(SELECT value FROM {$af_global->tables->result_values} WHERE result_id=row.id AND element_id = %d) AS '%s'", $element->id, $column_name );
				$column_titles[ $column_index++ ] = $column_name ;
			}
			else
			{
				$i = 0;

				foreach( $element_obj->answers AS $answer )
				{
					$answer = (object) $answer;

					switch( $params[ 'column_name' ] )
					{
						case 'element_id':
							$column_name = 'element_' . $element->id  . '_' . $i++;
							break;
						default:
							$column_name = $element->label . ' - ' . $answer->text;
							break;
					}

					$sql_columns[] = $wpdb->prepare( "IF( (SELECT value FROM {$af_global->tables->result_values} WHERE result_id=row.id AND element_id = %d AND value='%s') is NULL, 'no', 'yes' ) AS %s", $element->id, $answer->text, $column_name );
					$column_titles[ $column_index++ ] = $column_name;
				}
			}
		}
		// Adding columns aded by 'add_column' function
		foreach( $this->added_columns AS $column )
		{
			$added_column_sql = "({$column[ 'sql' ]}) AS {$column[ 'name' ]}";

			$sql_columns[] = $added_column_sql;
			$column_titles[ $column_index++ ] = $column[ 'name' ];
		}

		/**
		 * Creating Result SQL
		 */
		$sql_columns = implode( ', ', $sql_columns );
		$sql_result = "SELECT id AS result_id, user_id, timestamp, {$sql_columns} FROM {$af_global->tables->results} AS row WHERE form_id=%d";
		$sql_result_values = array( $this->form_id );

		/**
		 * Creating View
		 */
		$view_name = "{$wpdb->prefix}af_results_{$this->form_id}_view";

		$wpdb->query( "DROP VIEW IF EXISTS {$view_name}" );

		$sql_view = "CREATE VIEW {$view_name} AS {$sql_result}";
		$sql_view = $wpdb->prepare( $sql_view, $sql_result_values );

		$result = $wpdb->query( $sql_view );

		if( FALSE == $result )
		{
			return FALSE;
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
	 *  $results = new AF_Form_Results( 5944 );
	 *  $af_results->add_column( 'username', "SELECT user_login FROM {$wpdb->prefix}users WHERE ID=row.user_id" );
	 *  $results = $af_results->results();
	 *
	 * @param string $column_name The Column name will be created in the Result View
	 * @param string $sql         The SQL statement for getting new field in row
	 */
	public function add_column( $column_name, $sql )
	{
		$this->added_columns[] = array(
			'name' => esc_sql( $column_name ),
			'sql'  => $sql
		);
	}

	/**
	 * Get all saved results of an element
	 *
	 * @return mixed $responses The results as array or NULL if there are no results
	 * @since 1.0.0
	 */
	public function element( $element_id, $filter = array() )
	{
		$filter = wp_parse_args( $filter, array(
			'start_row'       => 0,
			'number_rows' => NULL,
			'user_ids'    => NULL,
			'result_ids'  => NULL,
			'filter'       => NULL,
			'orderby'     => NULL,
			'order'       => NULL,
			'column_name' => 'label', // label, element_id
		) );

		$filter[ 'element_ids' ] = array( $element_id );

		$results = $this->results( $filter );

		return $results;
	}
}