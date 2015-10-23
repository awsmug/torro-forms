<?php
/**
 * Result Handler abstraction class
 *
 * Motherclass for all Result Handlers
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Restrictions
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
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

abstract class AF_ResultHandler
{
	/**
	 * Name of Result Component
	 *
	 * @since 1.0.0
	 */
	var $name;

	/**
	 * Title of Result Component
	 *
	 * @since 1.0.0
	 */
	var $title;

	/**
	 * Description of Result Component
	 *
	 * @since 1.0.0
	 */
	var $description;

	/**
	 * Function to register element in Awesome Forms
	 *
	 * After registerung was successfull the new element will be shown in the elements list.
	 *
	 * @return boolean $is_registered Returns TRUE if registering was succesfull, FALSE if not
	 * @since 1.0.0
	 */
	public function _register()
	{
		global $af_global;

		if( TRUE == $this->initialized )
		{
			return FALSE;
		}

		if( !is_object( $af_global ) )
		{
			return FALSE;
		}

		if( '' == $this->name )
		{
			$this->name = get_class( $this );
		}

		if( '' == $this->title )
		{
			$this->title = ucwords( get_class( $this ) );
		}

		if( '' == $this->description )
		{
			$this->description = esc_attr__( 'This is a Awesome Forms Result Handler.', 'af-locale' );
		}

		if( array_key_exists( $this->name, $af_global->restrictions ) )
		{
			return FALSE;
		}

		if( !is_array( $af_global->result_handlers ) )
		{
			$af_global->result_handlers = array();
		}

		add_action( 'init', array( $this, 'init_settings' ), 15 );

		$this->initialized = TRUE;

		return $af_global->add_result_handler( $this->name, $this );
	}

	/**
	 * Getting Results
	 *
	 * @param int   $form_id
	 * @param array $filter
	 */
	protected function get_results( $form_id, $filter = array() )
	{
		$filter = wp_parse_args( $filter, array(
			'start'       => 0,
			'end'         => FALSE,
			'element_ids' => FALSE,
			'user_ids'    => FALSE
		) );

		$results = new AF_Form_Results( $form_id );
		$results->get_responses();
	}
}

/**
 * Register a new Result Handler
 *
 * @param $restriction_class
 *
 * @return bool|null Returns false on failure, otherwise null.
 */
function af_register_result_handler( $result_handler__class )
{
	if( class_exists( $result_handler__class ) )
	{
		$result_handler = new $result_handler__class();

		return $result_handler->_register();
	}

	return FALSE;
}