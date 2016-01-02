<?php
/**
 * Torro Forms Class for main global $torro_global
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

global $torro_global;

class Torro_Global {
	var $tables;
	var $components = array();
	var $settings = array();
	var $element_types = array();
	var $actions = array();
	var $restrictions = array();
	var $result_handlers = array();
	var $chart_creators = array();
	var $templatetags = array();

	public function __construct() {
		$this->tables();
	}

	public function tables() {
		global $wpdb;

		$this->tables = new stdClass;

		$this->tables->elements = $wpdb->prefix . 'torro_elements';
		$this->tables->element_answers = $wpdb->prefix . 'torro_element_answers';
		$this->tables->results = $wpdb->prefix . 'torro_results';
		$this->tables->result_values = $wpdb->prefix . 'torro_result_values';
		$this->tables->settings = $wpdb->prefix . 'torro_settings';
		$this->tables->participiants = $wpdb->prefix . 'torro_participiants';
		$this->tables->email_notifications = $wpdb->prefix . 'torro_email_notifications';

		$this->tables = apply_filters( 'torro_forms_tables', $this->tables );
	}

	public function add_component( $name, $object ) {
		if ( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Component' ) ) {
			return false;
		}

		$this->components[ $name ] = $object;

		return true;
	}

	public function add_settings( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Settings' ) ) {
			return false;
		}

		$this->settings[ $name ] = $object;

		return true;
	}

	public function add_form_element( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Form_Element' ) ) {
			return false;
		}

		$this->element_types[ $name ] = $object;

		return true;
	}

	public function add_restriction( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Restriction' ) ) {
			return false;
		}

		$this->restrictions[ $name ] = $object;

		return true;
	}

	public function add_action( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Action' ) ) {
			return false;
		}

		$this->actions[ $name ] = $object;

		return true;
	}

	public function add_result_handler( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Action' ) ) {
			return false;
		}

		$this->result_handlers[ $name ] = $object;

		return true;
	}

	public function add_chartscreator( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_Chart_Creator' ) ) {
			return false;
		}

		$this->chart_creators[ $name ] = $object;

		return true;
	}

	public function add_templatetags( $name, $object ) {
		if( empty( $name ) ) {
			return false;
		}

		if ( ! is_a( $object, 'Torro_TemplateTags' ) ) {
			return false;
		}

		$this->templatetags[ $name ] = $object;

		return true;
	}
}

$torro_global = new Torro_Global();
