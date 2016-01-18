<?php
/**
 * Torro Forms Main Extension Class
 *
 * This class is the base for every Torro Forms Extension.
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

abstract class Torro_Extension extends Torro_Instance {

	/**
	 * Settings fields
	 *
	 * @since 1.0.0
	 */
	protected $settings_fields = array();

	/**
	 * Settings
	 *
	 * @since 1.0.0
	 */
	protected $settings;


	/**
	 * Item name for EDD
	 *
	 * @since 1.0.0
	 */
	protected $item_name;

	/**
	 * Plugin file path for EDD
	 *
	 * @since 1.0.0
	 */
	protected $plugin_file;

	/**
	 * Plugin version
	 *
	 * @since 1.0.0
	 */
	protected $version;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Checking and starting
	 *
	 * @since 1.0.0
	 */
	public function check_and_start() {
		//TODO: check requirements in manager
		if ( true === $this->check_requirements() ) {
			$this->settings = torro_get_settings( $this->name );
			$this->base_init();
		}
	}

	/**
	 * Function for Checks
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected function check_requirements() {
		return true;
	}

	/**
	 * Running Scripts if functions are existing in child Class
	 *
	 * @since 1.0.0
	 */
	protected function base_init() {
		if ( method_exists( $this, 'includes' ) ) {
			$this->includes();
		}

		add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
	}

	public function plugin_updater() {
		// retrieve our license key from the DB

		$license_key = $this->settings[ 'serial' ];

		if( !empty( $license_key ) ){
			$license_key = trim( $license_key );

			$edd_updater = new EDD_SL_Plugin_Updater( 'http://torro-forms.com', $this->plugin_file, array(
				'version'   => $this->version,
				// current version number
				'license'   => $license_key,
				// license key (used get_option above to retrieve from DB)
				'item_name' => $this->item_name,
				// name of this plugin
				'author'    => 'Awesome UG'
				// author of this plugin
			) );
		}
	}

	/**
	 * Add Settings to Settings Page
	 */
	public function init_settings() {
		if ( 0 === count( $this->settings_fields ) || empty( $this->settings_fields ) ) {
			return false;
		}

		$headline = array(
			'headline' => array(
				'title'       => $this->title,
				'description' => sprintf( __( 'Setup the "%s" Extension.', 'torro-forms' ), $this->title ),
				'type'        => 'title'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		torro()->settings()->get( 'extensions' )->add_subsettings_field_arr( $this->name, $this->title, $settings_fields );

		$settings_name = 'extensions_' . $this->name;

		$settings_handler = new Torro_Settings_Handler( $settings_name, $this->settings_fields );
		$this->settings   = $settings_handler->get_field_values();
	}
}
