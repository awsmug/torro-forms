<?php
/**
 * Submodule base class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules;

/**
 * Base class for a submodule.
 *
 * @since 1.0.0
 */
abstract class Submodule {

	/**
	 * The submodule slug. Must match the slug when registering the submodule.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The submodule title.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * The submodule description.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $description = '';

	/**
	 * The submodules module instance.
	 *
	 * @since 1.0.0
	 * @var Module
	 */
	protected $module;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Module $module The submodule's parent module instance.
	 */
	public function __construct( $module ) {
		$this->module = $module;

		$this->bootstrap();
	}

	/**
	 * Returns the submodule slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule slug.
	 */
	public function get_slug() {
		return $this->slug;
	}

	/**
	 * Returns the submodule title.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule title.
	 */
	public function get_title() {
		return $this->title;
	}

	/**
	 * Returns the submodule description.
	 *
	 * @since 1.0.0
	 *
	 * @return string Submodule description.
	 */
	public function get_description() {
		return $this->description;
	}

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected abstract function bootstrap();
}
