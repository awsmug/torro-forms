<?php
/**
 * Frontends module class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Frontends;

use awsmug\Torro_Forms\Modules\Hooks_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;

use awsmug\Torro_Forms\DB_Objects\Forms\Form_Frontend_Output_Handler;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Class for the Frontends module.
 *
 * @since 1.1.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.1.0
	 */
	protected function bootstrap() {
		$this->slug        = 'frontends';
		$this->title       = __( 'Frontends', 'torro-forms' );
		$this->description = __( 'Frontends allowing to use different types of Frontends', 'torro-forms' );
		$this->required    = true;

		$this->submodule_base_class = Frontend::class;
		$this->default_submodules   = array(
			'standard' => Frontend_Standard::class,
		);
	}

	/**
	 * Rendering form content.
	 *
	 * @since 1.1.0
	 *
	 * @param Form_Frontend_Output_Handler $output_handler Output handler.
	 * @param Form                         $form           Current form.
	 * @param Submission                   $submission     Current submission.
	 */
	public function render_output( $output_handler, $form, $submission ) {
		$options = $this->manager()->options()->get( 'general_settings', array() );

		$frontend_slug = 'standard';
		if ( array_key_exists( 'frontend_slug', $options ) ) {
			$frontend_slug = $options['frontend_slug'];
		}

		$frontend = $this->submodules[ $frontend_slug ];

		$frontend->render_output( $output_handler, $form, $submission );
	}

	/**
	 * Registers the default access controls.
	 *
	 * The function also executes a hook that should be used by other developers to register their own access controls.
	 *
	 * @since 1.1.0
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
		 * @since 1.1.0
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
	 * Choosing frontend from general settings page.
	 *
	 * @since 1.1.0
	 *
	 * @param array $tabs Associative array of `$field_slug => $field_args` pairs.
	 *
	 * @return array $tabs Filtered associative array of `$field_slug => $field_args` pairs.
	 */
	public function general_settings_fields( $fields ) {
		if ( 1 >= count( $this->submodules ) ) {
			// return $fields;
		}

		$choices = array();
		foreach ( $this->submodules as $submodule ) {
			$choices [ $submodule->get_slug() ] = $submodule->get_title();
		}

		$fields['frontend_slug'] = array(
			'section'     => 'form_behavior',
			'type'        => 'select',
			'label'       => __( 'Frontend', 'torro-forms' ),
			'description' => __( 'The frontend engine which will be used to display forms.', 'torro-forms' ),
			'default'     => 'standard',
			'choices'     => $choices,
		);

		return $fields;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.1.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->filters[] = array(
			'name'     => $this->get_prefix() . 'settings_fields',
			'callback' => array( $this, 'general_settings_fields' ),
			'priority' => 100,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => $this->get_prefix() . 'frontend_output_handler',
			'callback' => array( $this, 'render_output' ),
			'priority' => 100,
			'num_args' => 3,
		);
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
