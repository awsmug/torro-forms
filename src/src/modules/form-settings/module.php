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
use awsmug\Torro_Forms\Modules\Hooks_Submodule_Interface;

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

		$this->submodule_base_class = Form_Setting::class;
		$this->default_submodules   = array(
			'labels'   => Labels::class,
			'privacy'  => Privacy::class,
			'advanced' => Advanced::class,
		);
	}

	/**
	 * Registers the default form settings.
	 *
	 * The function also executes a hook that should be used by other developers to register their own form settings.
	 *
	 * @since 1.1.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default form settings have been registered.
		 *
		 * This action should be used to register custom form settings.
		 *
		 * @since 1.1.0
		 *
		 * @param Module $form_settings Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_form_settings", $this );
	}


	/**
	 * Returns the available settings fields for the module.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	protected function get_settings_fields() {
		$prefix = $this->manager()->get_prefix();
		$fields = array();
		/**
		 * Filters the settings fields in the form settings tab.
		 *
		 * @since 1.0.0
		 *
		 * @param array $fields Array of `$field_slug => $field_data` pairs.
		 */
		return apply_filters( "{$prefix}form_settings_settings_fields", $fields );
	}

	/**
	 * Adds hooks for all registered submodules that support them.
	 *
	 * Submodule hooks should occur at some point after `init`.
	 *
	 * @since 1.1.0
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
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'add_submodule_hooks' ),
			'priority' => PHP_INT_MAX,
			'num_args' => 0,
		);
	}
}
