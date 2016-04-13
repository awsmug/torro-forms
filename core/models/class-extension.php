<?php
/**
 * Torro Forms Main Extension Class
 *
 * This class is the base for every Torro Forms Extension.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
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

abstract class Torro_Extension extends Torro_Base {

	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = 'extensions';

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

		if ( isset( $this->settings_fields['serial'] ) ) {
			add_action( 'admin_init', array( $this, 'plugin_updater' ), 0 );
		}
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
	 * Returns path to plugin
	 *
	 * @param string $path adds sub path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_path( $path = '' ) {
		return plugin_dir_path( $this->plugin_file ) . ltrim( $path, '/' );
	}

	/**
	 * Returns url to plugin
	 *
	 * @param string $path adds sub path
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_url( $path = '' ) {
		return plugin_dir_url( $this->plugin_file ) . ltrim( $path, '/' );
	}

	/**
	 * Returns asset url path
	 *
	 * @param string $name Name of asset
	 * @param string $mode css/js/png/gif/svg/vendor-css/vendor-js
	 * @param boolean $force whether to force to load the provided version of the file (not using .min conditionally)
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_asset_url( $name, $mode = '', $force = false ) {
		$urlpath = 'assets/';

		$can_min = true;

		switch ( $mode ) {
			case 'css':
				$urlpath .= 'dist/css/' . $name . '.css';
				break;
			case 'js':
				$urlpath .= 'dist/js/' . $name . '.js';
				break;
			case 'png':
			case 'gif':
			case 'svg':
				$urlpath .= 'dist/img/' . $name . '.' . $mode;
				$can_min = false;
				break;
			case 'vendor-css':
				$urlpath .= 'vendor/' . $name . '.css';
				break;
			case 'vendor-js':
				$urlpath .= 'vendor/' . $name . '.js';
				break;
			default:
				return '';
		}

		if ( $can_min && ! $force ) {
			if ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) {
				$urlpath = explode( '.', $urlpath );
				array_splice( $urlpath, count( $urlpath ) - 1, 0, 'min' );
				$urlpath = implode( '.', $urlpath );
			}
		}

		return $this->get_url( $urlpath );
	}
}
