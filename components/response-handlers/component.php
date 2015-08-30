<?php
/**
 * Questions Restrictions
 *
 * Component for the Restrictions API
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Restrictions
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class Questions_Responses extends Questions_Component
{

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	public function __construct()
	{
		$this->name = 'questionsresponses';
		$this->title = esc_attr__( 'Responses', 'questions-locale' );
		$this->description = esc_attr__( 'Responses component helps to catch the entered data after sending form.', 'questions-locale' );

	}

	/**
	 * Including files of component
	 */
	public function start()
	{
		$folder = QUESTIONS_COMPONENTFOLDER . 'response-handlers/';

		// Loading base functionalities
		include_once( $folder . 'settings.php' );
		include_once( $folder . 'form-builder-extension.php' );
		include_once( $folder . 'form-process-extension.php' );

		// Response Handlers API
		include_once( $folder . 'class-response-handler.php' );
		include_once( $folder . 'base-response-handlers/email-notifications.php' );
	}
}

af_register_component( 'Questions_Responses' );
