<?php

/**
 * Adds global Templatetags
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
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

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_GlobalTemplateTags extends Torro_TemplateTags {
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->title = __( 'Global', 'torro-forms' );
		$this->name = 'basetags';
		$this->description = __( 'Global Templatetags', 'torro-forms' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags() {
		$this->add_tag( 'sitetitle', __( 'Site Title', 'torro-forms' ), __( 'Adds the Site Title', 'torro-forms' ), array( __CLASS__ , 'sitetitle' ) );
		$this->add_tag( 'sitetagline', __( 'Site Tagline', 'torro-forms' ), __( 'Adds the Sites Tagline', 'torro-forms' ), array( __CLASS__, 'sitetagline') );
		$this->add_tag( 'adminemail', __( 'Admin Email', 'torro-forms' ), __( 'Adds the Admin Email-Address', 'torro-forms' ), array( __CLASS__, 'adminemail') );
		$this->add_tag( 'userip', __( 'User IP', 'torro-forms' ), __( 'Adds the Sites User IP', 'torro-forms' ), array( __CLASS__, 'userip' ) );
	}

	/**
	 * %sitename%
	 */
	public static function sitetitle() {
		return get_bloginfo( 'name' );
	}

	/**
	 * %sitename%
	 */
	public static function sitetagline() {
		return get_bloginfo( 'description' );
	}

	/**
	 * %sitename%
	 */
	public static function adminemail() {
		return get_option( 'admin_email' );
	}

	/**
	 * %sitename%
	 */
	public static function userip() {
		return $_SERVER[ 'REMOTE_ADDR' ];
	}
}

torro_register_templatetags( 'Torro_GlobalTemplateTags' );
