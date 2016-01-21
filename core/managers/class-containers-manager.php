<?php
/**
 * Torro Forms continer manager class
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 2015-04-16
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

final class Torro_Containers_Manager extends Torro_Manager {

	private static $instance = null;

	private $container_id;

	private $container;

	public static function instance( $container_id ) {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		if ( self::$instance->container_id !== $container_id && null != $container_id ) {
			self::$instance->container_id = $container_id;
			self::$instance->container    = new Torro_Container( $container_id );
		}

		return self::$instance;
	}

	protected function init() {
		$this->base_class = 'Torro_Container';
	}

	public function get_elements(){
		return $this->container->elements;
	}
}