<?php
/**
 * Frontends module class
 *
 * @package TorroForms
 * @since 1.2.0
 */

namespace awsmug\Torro_Forms\Modules\Frontends;

use awsmug\Torro_Forms\Modules\Hooks_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;

/**
 * Class for the Frontends module.
 *
 * @since 1.2.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.2.0
	 */
	protected function bootstrap() {
		$this->slug        = 'frontends';
		$this->title       = __( 'Frontends', 'torro-forms' );
		$this->description = __( 'Frondends allowing to use different types of Frontends', 'torro-forms' );

		$this->submodule_base_class = Frontend::class;
		$this->default_submodules   = array(
			'react' => React::class,
		);
	}

	/**
	 * Registers the default access controls.
	 *
	 * The function also executes a hook that should be used by other developers to register their own access controls.
	 *
	 * @since 1.20.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default frontends have been registered.
		 *
		 * This action should be used to register custom frontends.
		 *
		 * @since 1.2.0
		 *
		 * @param Module $frontends Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_frontends", $this );
	}

	/**
	 * Adds hooks for all registered submodules that support them.
	 *
	 * Submodule hooks should occur at some point after `init`.
	 *
	 * @since 1.2.0
	 */
	protected function add_submodule_hooks() {
		foreach ( $this->submodules as $slug => $submodule ) {
			if ( ! $submodule instanceof Hooks_Submodule_Interface ) {
				continue;
			}

			$submodule->add_hooks();
		}
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.2.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'add_submodule_hooks' ),
			'priority' => PHP_INT_MAX,
			'num_args' => 0,
		);
	}
}
