<?php
/**
 * Actions module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use APIAPI\Core\APIAPI;

/**
 * Class for the Actions module.
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
		$this->slug        = 'actions';
		$this->title       = __( 'Actions', 'torro-forms' );
		$this->description = __( 'Actions are executed in the moment users submit their form data.', 'torro-forms' );

		$this->submodule_base_class = Action::class;
		$this->default_submodules = array(
			'email_notifications' => Email_Notifications::class,
			'redirection'         => Redirection::class,
		);
	}

	/**
	 * Returns the plugin's API-API instance.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return APIAPI The API-API instance.
	 */
	public function apiapi() {
		return $this->manager()->apiapi();
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 */
	protected function handle( $submission, $form ) {
		foreach ( $this->submodules as $slug => $action ) {
			if ( ! $action->enabled( $form ) ) {
				continue;
			}

			$action_result = $action->handle( $submission, $form );
			// TODO: Log errors.
		}
	}

	/**
	 * Registers the default actions.
	 *
	 * The function also executes a hook that should be used by other developers to register their own actions.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default actions have been registered.
		 *
		 * This action should be used to register custom actions.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $actions Action manager instance.
		 */
		do_action( "{$this->get_prefix()}register_actions", $this );
	}

	/**
	 * Saves the API mappings for the elements of a given form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Form  $form     Form that has been saved.
	 * @param array $mappings Array of ID mappings from the objects that have been saved.
	 */
	protected function save_api_mappings( $form, $id_mappings ) {
		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, API_Action_Interface::class ) ) {
				continue;
			}

			if ( ! $action->enabled( $form ) ) {
				continue;
			}

			$action->save_mappings( $form->id, $id_mappings['elements'] );
		}
	}

	/**
	 * Registers the hooks for the API-API configuration data.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_api_config_data_hook() {
		foreach ( $this->submodules as $slug => $action ) {
			if ( ! is_a( $action, API_Action_Interface::class ) ) {
				continue;
			}

			$action->register_config_data_hook();
		}
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
			'callback' => array( $this, 'handle' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 0,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}save_form",
			'callback' => array( $this, 'save_api_mappings' ),
			'priority' => 10,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_api_config_data_hook' ),
			'priority' => 200,
			'num_args' => 0,
		);
	}
}
