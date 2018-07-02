<?php
/**
 * Form Settings module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Form_Settings;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;

/**
 * Class for the Form Settings module.
 *
 * @since 1.1.0
 */
class Module extends Module_Base  implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'form_settings';
		$this->title       = __( 'Form Settings', 'torro-forms' );
		$this->description = __( 'Form settings control the general behavior of forms.', 'torro-forms' );

		$this->submodule_base_class = Setting::class;
		$this->default_submodules   = array(
			'labels'   => labels::class,
			'privacy'  => privacy::class,
			'advanced' => advanced::class,
		);
	}

	/**
	 * Registers the default settings.
	 *
	 * The function also executes a hook that should be used by other developers to register their own evaluators.
	 *
	 * @since 1.1.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default evaluators have been registered.
		 *
		 * This action should be used to register custom evaluators.
		 *
		 * @since 1.1.0
		 *
		 * @param Module $evaluators Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_settings", $this );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.1.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
