<?php

/**
 * Adds global Templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package Questions/Core
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

class AF_GlobalTemplateTags extends AF_TemplateTags
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->title = __( 'Global', 'wcsc-locale' );
		$this->name = 'basetags';
		$this->description = __( 'Global Templatetags', 'wcsc-locale' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags()
	{
		$this->add_tag( 'sitetitle', esc_attr( 'Site Title', 'questions-locale' ), esc_attr( 'Adds the Site Title', 'questions-locale' ), array( __CLASS__ , 'sitetitle' ) );
		$this->add_tag( 'sitetagline', esc_attr( 'Site Tagline', 'questions-locale' ), esc_attr( 'Adds the Sites Tagline', 'questions-locale' ), array( __CLASS__, 'sitetagline') );
		$this->add_tag( 'adminemail', esc_attr( 'Admin Email', 'questions-locale' ), esc_attr( 'Adds the Admin Email-Address', 'questions-locale' ), array( __CLASS__, 'adminemail') );
		$this->add_tag( 'userip', esc_attr( 'User IP', 'questions-locale' ), esc_attr( 'Adds the Sites User IP', 'questions-locale' ), array( __CLASS__, 'userip' ) );
	}

	/**
	 * %sitename%
	 */
	public static function sitetitle()
	{
		return get_bloginfo( 'name' );
	}

	/**
	 * %sitename%
	 */
	public static function sitetagline()
	{
		return get_bloginfo( 'description' );
	}

	/**
	 * %sitename%
	 */
	public static function adminemail()
	{
		return get_option( 'admin_email' );;
	}

	/**
	 * %sitename%
	 */
	public static function userip()
	{
		return $_SERVER[ 'REMOTE_ADDR' ];
	}
}
qu_register_templatetags( 'AF_GlobalTemplateTags' );