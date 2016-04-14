<?php

/**
 * Element answer class
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 1.0.0alpha1
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

class Torro_Element_Setting extends Torro_Instance_Base {

	protected $name = '';

	protected $value = '';

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	public function move( $element_id ) {
		return parent::move( $element_id );
	}

	public function copy( $element_id ) {
		return parent::copy( $element_id );
	}

	protected function init() {
		$this->table_name = 'torro_element_settings';
		$this->superior_id_name = 'element_id';
		$this->manager_method = 'element_settings';
		$this->valid_args = array(
			'name'		=> 'string',
			'value'		=> 'string',
		);
	}
}
