<?php
/**
 * Torro Forms Restrictions Component
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Restrictions
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
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

final class Torro_Form_Settings_Component extends Torro_Component {
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializes the Component.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->name = 'form-settings';
		$this->title = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form Settings Component', 'torro-forms' );
	}

	/**
	 * Including files of component
	 */
	protected function includes() {
		$folder = torro()->get_path( 'components/form-settings/' );

		// Loading base functionalities
		require_once( $folder . 'settings.php' );
		require_once( $folder . 'form-builder-extension.php' );
		require_once( $folder . 'form-process-extension.php' );

		// Models
		require_once( $folder . 'models/class-form-setting.php' );
		require_once( $folder . 'models/class-access-control.php' );

		// Settings
		require_once( $folder . 'base-form-settings/access-control.php' );
		require_once( $folder . 'base-form-settings/timerange.php' );
		require_once( $folder . 'base-form-settings/recaptcha.php' );

		// Visitors
		require_once( $folder . 'base-form-settings/access-controls/all-visitors.php' );
		require_once( $folder . 'base-form-settings/access-controls/all-members.php' );
		require_once( $folder . 'base-form-settings/access-controls/selected-members.php' );


	}
}

torro()->components()->register( 'Torro_Form_Settings_Component' );
