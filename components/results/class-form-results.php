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
	 * Counted Results
	 *
	 * @var int $count
	 * @since 1.0.0
	 */
	protected $count;

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
		$this->count_results();

		return TRUE;
	}

	/**
	 * Counting Results
	 *
	 * @return null|string
	 * @since 1.0.0
	 */
	public function count_results()
	{
		global $wpdb, $af_global;

		if( '' === $this->count )
		{
			$sql = $wpdb->prepare( "SELECT COUNT(*) FROM {$af_global->tables->results}" );
			$this->count = $wpdb->get_var( $sql );
		}

		return $this->count;
	}

	/**
	 * Getting responses of a Form
	 *
	 * @param bool|int $element_id Get responses of a special element
	 * @param boolean  $userdata   Adding user specified data to response array
	 *
	 * @return array $responses
	 * @since 1.0.0
	 */
	public function get_results( $filter = array() )
	{
		global $wpdb, $af_global;

		$filter = wp_parse_args( $filter, array(
			'start'       => 0,
			'number_rows' => NULL,
			'element_ids' => NULL,
			'user_ids'    => NULL,
			'result_ids'  => NULL,
			'filter'       => NULL,
			'orderby'     => NULL,
			'order'       => NULL,
			'column_name' => 'label', // label, element_id
		) );

		/**
		 * Getting elements
		 */
		$sql_elements = "SELECT id, label FROM {$af_global->tables->elements} WHERE form_id=%d";
		$sql_elements_values = array( $this->form_id );

		// Filtering Response Ids
		if( is_array( $filter[ 'result_ids' ] ) )
		{
			$sql_elements_string_values = array();
			foreach( $filter[ 'result_ids' ] AS $key => $response_id )
			{
				$sql_elements_string_values[] = ' id=%d';
				$sql_elements_values[] = (int) $response_id;
			}

			$sql_elements .= ' AND (' . implode( ' OR ', $sql_elements_string_values  ) . ' )';
		}

		$sql = $wpdb->prepare( $sql_elements, $sql_elements_values );
		$elements = $wpdb->get_results( $sql );

		/**
		 * Preparing columns for form values
		 */
		$sql_columns = array();
		$column_titles = array();
		$column_index = 3;
		foreach( $elements AS $element )
		{
			$element_obj = af_get_element( $element->id );

			switch( $filter[ 'column_name' ] )
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
				$sql_columns[] = $wpdb->prepare( "(SELECT value FROM {$af_global->tables->result_values} WHERE result_id=r.id AND element_id = %d) AS '%s'", $element->id, $column_name );
				$column_titles[ $column_name ] = $column_index++;
			}
			else
			{
				$i = 0;

				foreach( $element_obj->answers AS $answer )
				{
					$answer = (object) $answer;

					switch( $filter[ 'column_name' ] )
					{
						case 'element_id':
							$column_name = 'element_' . $element->id  . '_' . $i++;
							break;
						default:
							$column_name = $element->label . ' - ' . $answer->text;
							break;
					}

					$sql_columns[] = $wpdb->prepare( "IF( (SELECT value FROM {$af_global->tables->result_values} WHERE result_id=r.id AND element_id = %d AND value='%s') is NULL, 'no', 'yes' ) AS %s", $element->id, $answer->text, $column_name );
					$column_titles[ $column_name ] = $column_index++;
				}
			}
		}
		$sql_columns = implode( ', ', $sql_columns );

		/**
		 * Creating Result SQL
		 */
		$sql_result = "SELECT id AS result_id, user_id, {$sql_columns} FROM {$af_global->tables->results} AS r WHERE form_id=%d";
		$sql_result_values = array( $this->form_id );

		if( NULL !== $filter[ 'filter' ] )
		{
			if( is_array( $filter[ 'filter' ] ) )
			{
				foreach( $filter[ 'filter' ] AS $key => $value )
				{
					$sql_result .= ' AND %d=%s';
					$sql_result_values[] = $column_titles[ $key ] ;
					$sql_result_values[] = $value ;
				}
			}
		}

		if( NULL !== $filter[ 'orderby' ] )
		{
			if( array_key_exists( $filter[ 'orderby' ], $column_titles  ) )
			{
				$sql_result .= ' ORDER BY %d';
				$sql_result_values[] = $column_titles[ $filter[ 'orderby' ] ];
			}
		}

		if( 'ASC' == $filter[ 'order' ] || 'DESC' == $filter[ 'order' ] )
		{
			$sql_result .= ' ' . $filter[ 'order' ];
		}

		// Limiting
		if( NULL !== $filter[ 'start' ] && NULL !== $filter[ 'number_rows' ] )
		{
			$sql_result .= ' LIMIT %d, %d';
			$sql_result_values[] = (int) $filter[ 'start' ];
			$sql_result_values[] = (int) $filter[ 'number_rows' ];
		}
		elseif( NULL === $filter[ 'start' ] && NULL !== $filter[ 'number_rows' ] )
		{
			$sql_result .= ' LIMIT %d';
			$sql_result_values[] = (int) $filter[ 'number_rows' ];
		}

		$sql_string = $wpdb->prepare( $sql_result, $sql_result_values );

		print_r( $sql_string );

		$results = $wpdb->get_results( $sql_string );

		return $results;
	}

	/**
	 * Get all saved results of an element
	 *
	 * @return mixed $responses The results as array or NULL if there are no results
	 * @since 1.0.0
	 */
	public function get_element_results( $element_id, $filter = array() )
	{
		global $wpdb, $af_global;

		$filter = wp_parse_args( $filter, array(
			'start'       => NULL,
			'number_rows' => NULL,
			'result_ids'=> NULL,
			'orderby'     => 'result_id',
			'order'       => 'ASC'
		) );

		$element = af_get_element( $element_id );

		// Exit if element not exists
		if( FALSE == $element )
		{
			return NULL;
		}

		$sql_string = "SELECT r.id, r.form_id, a.result_id, a.value FROM {$af_global->tables->results} AS r, {$af_global->tables->result_values} AS a WHERE r.id=a.result_id AND a.element_id=%d AND r.form_id=%d";
		$sql_values = array( $element_id ,$this->form_id );

		// Filtering Response Ids
		if( is_array( $filter[ 'result_ids' ] ) )
		{
			$sql_string_values = array();
			foreach( $filter[ 'result_ids' ] AS $key => $result_id )
			{
				$sql_string_values[] = ' r.id=%d';
				$sql_values[] = (int) $result_id;
			}

			$sql_string .= ' AND (' . implode( ' OR ', $sql_string_values  ) . ' )';
		}

		// Limiting
		if( NULL != $filter[ 'start' ] && NULL != $filter[ 'number_rows' ] )
		{
			$sql_string .= ' LIMIT %d, %d';
			$sql_values[] = (int) $filter[ 'start' ];
			$sql_values[] = (int) $filter[ 'number_rows' ];
		}
		elseif( NULL == $filter[ 'start' ] && NULL != $filter[ 'number_rows' ] )
		{
			$sql_string .= ' LIMIT %d';
			$sql_values[] = (int) $filter[ 'number_rows' ];
		}

		// Ordering
		switch( $filter[ 'orderby' ] )
		{
			case 'result_id':
				$sql_string .= ' ORDER BY r.id';
				break;

			case 'value':
				$sql_string .= ' ORDER BY a.value';
				break;
		}

		if( 'ASC' == $filter[ 'order' ] || 'DESC' == $filter[ 'order' ] )
		{
			$sql_string .= ' ' . $filter[ 'order' ];
		}

		$sql = $wpdb->prepare( $sql_string , $sql_values );
		$responses = $wpdb->get_results( $sql );

		if( NULL == $responses )
		{
			return NULL;
		}

		$result_answers = array(
			'label'    => $element->label,
			'sections' => FALSE,
			'array'    => $element->answer_is_multiple
		);

		print_r( $responses );

		/**
		 * Ordering Results for Element
		 */
		if( is_array( $element->answers ) && count( $element->answers ) > 0 )
		{
			// If element has predefined answers
			foreach( $element->answers AS $answer_id => $answer )
			{
				if( $element->answer_is_multiple )
				{
					foreach( $responses AS $response )
					{
						if( $answer[ 'text' ] == $response->value )
						{
							$result_answers[ 'results' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__( 'Yes' );
						}
						elseif( !isset( $result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] ) )
						{
							$result_answers[ 'results' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__( 'No' );
						}
					}
				}
				else
				{
					foreach( $responses AS $response )
					{
						if( $answer[ 'text' ] == $response->value )
						{
							$result_answers[ 'results' ][ $response->result_id ] = $response->value;
						}
					}
				}
			}
		}
		else
		{
			// If element has no predefined answers
			if( is_array( $responses ) && count( $responses ) > 0 )
			{
				foreach( $responses AS $response )
				{
					$result_answers[ 'results' ][ $response->result_id ] = $response->value;
				}
			}
		}

		if( is_array( $result_answers ) && count( $result_answers ) > 0 )
		{
			return $result_answers;
		}
		else
		{
			return NULL;
		}
	}

	/**
	 * Gettiung all user ids of a Form
	 *
	 * @return array $responses All user ids formatted for response array
	 * @since 1.0.0
	 */
	public function get_response_user_ids()
	{

		global $wpdb, $af_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$af_global->tables->results} WHERE form_id = %s", $this->form_id );
		$results = $wpdb->get_results( $sql );

		$responses = array();
		$responses[ 'label' ] = __( 'User ID', 'af-locale' );
		$responses[ 'sections' ] = FALSE;
		$responses[ 'array' ] = FALSE;
		$responses[ 'responses' ] = array();

		// Putting results in array
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$responses[ 'responses' ][ $result->id ] = $result->user_id;
			endforeach;
		endif;

		return $responses;
	}

	/**
	 * Gettiung all user names of a Form
	 *
	 * @return array $responses All user names formatted for response array
	 * @since 1.0.0
	 */
	public function get_response_user_names()
	{
		global $wpdb, $af_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$af_global->tables->results} WHERE form_id = %s", $this->form_id );
		$results = $wpdb->get_results( $sql );

		$responses = array();
		$responses[ 'label' ] = __( 'Username', 'af-locale' );
		$responses[ 'sections' ] = FALSE;
		$responses[ 'array' ] = FALSE;
		$responses[ 'responses' ] = array();

		// Putting results in array
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$user = get_user_by( 'id', $result->user_id );
				$responses[ 'responses' ][ $result->id ] = $user->user_login;
			endforeach;
		endif;

		return $responses;
	}

	/**
	 * Gettiung all timestrings of a Form
	 *
	 * @param string $timeformat
	 *
	 * @return array $responses All timestrings formatted for response array
	 * @since 1.0.0
	 */
	public function get_response_timestrings( $timeformat = 'd.m.Y H:i' )
	{

		global $wpdb, $af_global;

		$sql = $wpdb->prepare( "SELECT * FROM {$af_global->tables->results} WHERE form_id = %s", $this->form_id );
		$results = $wpdb->get_results( $sql );

		$responses = array();
		$responses[ 'label' ] = __( 'Date/Time', 'af-locale' );
		$responses[ 'sections' ] = FALSE;
		$responses[ 'array' ] = FALSE;
		$responses[ 'responses' ] = array();

		// Putting results in array
		if( is_array( $results ) ):
			foreach( $results AS $result ):
				$responses[ 'responses' ][ $result->id ] = date_i18n( $timeformat, $result->timestamp );
			endforeach;
		endif;

		return $responses;
	}
}