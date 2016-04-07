<?php
/**
 * Result value base class
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

class Torro_Result_Value extends Torro_Instance_Base {

	protected $element_id;

	protected $value;

	protected $element = null;

	/**
	 * Torro_Container constructor.
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->table_name = 'torro_result_values';
		$this->superior_id_name = 'result_id';
		$this->manager_method = 'result_values';
		$this->valid_args = array(
			'element_id'	=> 'int',
			'value'			=> 'string',
		);
	}

	/**
	 * Populating object
	 *
	 * @param int $id
	 *
	 * @since 1.0.0
	 */
	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$this->element = torro()->elements()->get( $this->element_id );
		}
	}
}
