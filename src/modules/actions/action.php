<?php
/**
 * Action base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Assets;

/**
 * Base class for an action.
 *
 * @since 1.0.0
 */
abstract class Action {

	/**
	 * The action slug. Must match the slug when registering the action.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The action title.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $title = '';

	/**
	 * The actions module instance.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var Module
	 */
	protected $module;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Module $module The actions module instance.
	 */
	public function __construct( $module ) {
		$this->module = $module;

		$this->bootstrap();
	}

	/**
	 * Returns the settings identifier for the action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Action settings identifier.
	 */
	public function get_settings_identifier() {
		return $this->slug;
	}

	/**
	 * Returns the settings subtab title for the action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return string Action settings title.
	 */
	public function get_settings_title() {
		return $this->title;
	}

	/**
	 * Returns the available settings sections for the action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$section_slug => $section_args` pairs.
	 */
	public function get_settings_sections() {
		return array();
	}

	/**
	 * Returns the available settings fields for the action.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_settings_fields() {
		return array();
	}

	/**
	 * Bootstraps the action by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected abstract function bootstrap();
}
