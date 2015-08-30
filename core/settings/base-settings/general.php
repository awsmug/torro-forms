<?php
/**
 * General Settings Tab
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package AwesomeForms/Core/Settings
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

if( !defined( 'ABSPATH' ) ){
	exit;
}

class AF_GeneralSettings extends AF_Settings
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		global $post;

		$this->title = __( 'General', 'af-locale' );
		$this->name = 'general';
	}

	public function settings()
	{
		$this->settings = array(
			'disclaimer' => array(
				'title'       => esc_attr( 'Welcome to Awesome Forms!', 'af-locale' ),
				'description' => esc_attr( 'You want to build any forms in a easy way? Awesome Forms will help you to do it in the very easy way with ton of options.', 'af-locale' ),
				'type' => 'disclaimer'
			),
			'modules_title' => array(
				'title'       => esc_attr( 'Form Modules', 'af-locale' ),
				'description' => esc_attr( 'Check the modules of Awesome Forms which have to be activated.', 'af-locale' ),
				'type' => 'title'
			),
			'modules' => array(
				'title'       => esc_attr( 'Modules', 'af-locale' ),
				'type' => 'checkbox',
				'values' => array(
					'charts' => esc_attr( 'Charts', 'af-locale' ),
					'restrictions' => esc_attr( 'Restrictions', 'af-locale' ),
					'response' => esc_attr( 'Response Handling', 'af-locale' )
				),
				'description' => esc_attr( 'You don´t need some of these functions? Switch it off!', 'af-locale' ),
				'defaults' => array( 'charts', 'restrictions', 'response' )
			)
		);
	}
}
af_register_settings( 'AF_GeneralSettings' );