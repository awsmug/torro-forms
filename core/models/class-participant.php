<?php

/**
 * Participant base class
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

class Torro_Participant extends Torro_Instance_Base {

	protected $user_id;

	protected $user;

	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	protected function init() {
		$this->table_name = 'torro_participants';
		$this->superior_id_name = 'form_id';
		$this->manager_method = 'participants';
		$this->valid_args = array(
			'user_id'	=> 'int',
		);
	}

	protected function populate( $id ) {
		parent::populate( $id );

		if ( $this->id ) {
			$this->user = $this->populate_user();
		}
	}

	private function populate_user() {
		return get_user_by( 'id', $this->user_id );
	}
}
