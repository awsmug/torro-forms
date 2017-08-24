<?php
/**
 * Evaluators module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Evaluators;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\Modules\Meta_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Settings_Submodule_Interface;
use awsmug\Torro_Forms\Modules\Assets_Submodule_Interface;
use awsmug\Torro_Forms\Assets;

/**
 * Class for the Evaluators module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function bootstrap() {
		$this->slug        = 'evaluators';
		$this->title       = __( 'Evaluators', 'torro-forms' );
		$this->description = __( 'Evaluators allow evaluating form submissions, for example to generate charts and analytics.', 'torro-forms' );

		$this->submodule_base_class = Evaluator::class;
		// TODO: Setup $default_submodules.
	}

	/**
	 * Evaluates a specific form submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission Submission to evaluate.
	 * @param Form       $form       Form the submission applies to.
	 */
	protected function evaluate( $submission, $form ) {
		foreach ( $this->submodules as $slug => $evaluator ) {
			if ( ! $evaluator->enabled( $form ) ) {
				continue;
			}

			$evaluator_result = $evaluator->evaluate( $submission, $form );
			// TODO: Log errors.
		}
	}

	/**
	 * Registers the default evaluators.
	 *
	 * The function also executes a hook that should be used by other developers to register their own evaluators.
	 *
	 * @since 1.0.0
	 * @access protected
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
		 * @since 1.0.0
		 *
		 * @param Module $evaluators Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_evaluators", $this );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}complete_submission",
			'callback' => array( $this, 'evaluate' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
