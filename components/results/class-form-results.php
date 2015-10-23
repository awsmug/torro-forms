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

	public function add_column( $name, $values )
	{

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
		$filter = wp_parse_args( $filter, array(
			'start'       => 0,
			'end'         => NULL,
			'element_ids' => NULL,
			'user_ids'    => NULL,
			'response_ids'=> NULL,
			'orderby'     => 'response_id',
			'order'       => 'ASC'
		) );

		$element_filter = array(
			'start' => $filter[ 'start' ],
			'end' => $filter[ 'end' ],
			'user_ids' => $filter[ 'user_ids' ],
			'response_ids' => $filter[ 'response_ids' ]
		);

		$form = new AF_Form( $this->form_id );

		// No elements? Stop!
		if( !is_array( $form->elements ) )
		{
			return FALSE;
		}

		$responses = array();

		/*
		// Adding user data
		if( $userdata )
		{
			$responses[ '_user_id' ] = $this->get_response_user_ids();
			$responses[ '_username' ] = $this->get_response_user_names();
			$responses[ '_datetime' ] = $this->get_response_timestrings();
		}
		*/

		// Running each Element of Form
		foreach( $form->elements AS $element )
		{
			// Has Element data?
			if( !$element->is_input )
			{
				continue;
			}

			// Filtering Elements
			if( is_array( $filter[ 'element_ids' ]  ) && !in_array( $element->id, $filter[ 'element_ids' ] ) )
			{
				continue;
			}

			$responses[ $element->id ] = $this->get_element_results( $element->id , $element_filter );
		}

		return $responses;
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
			'response_ids'=> NULL,
			'orderby'     => 'result_id',
			'order'       => 'ASC'
		) );

		$element = af_get_element( $element_id );

		// Exit if element not exists
		if( FALSE == $element )
		{
			return NULL;
		}

		$sql_string = "SELECT r.id, r.questions_id, a.respond_id, a.value FROM {$af_global->tables->responds} AS r, {$af_global->tables->respond_answers} AS a WHERE r.id=a.respond_id AND a.question_id=%d AND r.questions_id=%d";
		$sql_values = array( $element_id ,$this->form_id );

		// Filtering Response Ids
		if( is_array( $filter[ 'response_ids' ] ) )
		{

			$sql_string_values = array();
			foreach( $filter[ 'response_ids' ] AS $key => $response_id )
			{
				$sql_string_values[] = ' r.id=%d';
				$sql_values[] = (int) $response_id;
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
							$result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__( 'Yes' );
						}
						elseif( !isset( $result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] ) )
						{
							$result_answers[ 'responses' ][ $response->respond_id ][ $answer[ 'text' ] ] = esc_attr__( 'No' );
						}
					}
				}
				else
				{
					foreach( $responses AS $response )
					{
						if( $answer[ 'text' ] == $response->value )
						{
							$result_answers[ 'responses' ][ $response->respond_id ] = $response->value;
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
					$result_answers[ 'responses' ][ $response->respond_id ] = $response->value;
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