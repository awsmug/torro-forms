<?php
/**
 * Core: Torro_Base class
 *
 * @package TorroForms
 * @subpackage CoreModels
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms base class.
 *
 * This class is the base for every component-like class in the plugin.
 *
 * @since 1.0.0-beta.1
 */
abstract class Torro_Base {
	/**
	 * name of access-control
	 *
	 * @since 1.0.0
	 */
	protected $name;

	/**
	 * Title of access-control
	 *
	 * @since 1.0.0
	 */
	protected $title;

	/**
	 * Description of access-control
	 *
	 * @since 1.0.0
	 */
	protected $description;

	/**
	 * Settings name
	 *
	 * @since 1.0.0
	 */
	protected $settings_name = null;

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
	protected $settings = array();

	/**
	 * Whether this base has been initialized
	 *
	 * @since 1.0.0
	 */
	protected $initialized = false;

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		$this->init();
		$this->init_settings();
	}

	/**
	 * Magic setter function
	 *
	 * @param $key
	 * @param $value
	 *
	 * @since 1.0.0
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'name':
			case 'title':
			case 'description':
				if ( ! $this->initialized ) {
					$this->$key = $value;
				}
				break;
			case 'initialized':
				if ( ! $this->initialized ) {
					$this->initialized = $value;
				}
				break;
			default:
				if ( property_exists( $this, $key ) ) {
					$this->$key = $value;
				}
		}
	}

	/**
	 * Magic getter function
	 *
	 * @param $key
	 *
	 * @return mixed|null
	 * @since 1.0.0
	 */
	public function __get( $key ) {
		if ( property_exists( $this, $key ) ) {
			return $this->$key;
		}
		return null;
	}

	/**
	 * Magic isset function
	 *
	 * @param $key
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function __isset( $key ) {
		if ( property_exists( $this, $key ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Base Element Function
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	protected abstract function init();

	/**
	 * Add Settings to Settings Page
	 *
	 * @since 1.0.0
	 */
	private function init_settings() {
		if( null === $this->settings_name ){
			return false;
		}

		if ( 0 === count( $this->settings_fields ) || empty( $this->settings_fields ) ) {
			return false;
		}

		$headline = array(
			'headline' => array(
				'title'       => $this->title,
				'description' => sprintf( __( 'Setup "%s".', 'torro-forms' ), $this->title ),
				'type'        => 'disclaimer'
			)
		);

		$settings_fields = array_merge( $headline, $this->settings_fields );

		torro()->settings()->get_registered( $this->settings_name )->add_subsettings_field_arr( $this->name, $this->title, $settings_fields );

		$settings_handler = new Torro_Settings_Handler( $this->settings_name . '_' . $this->name, $this->settings_fields );
		$this->settings   = $settings_handler->get_field_values();
	}
}
