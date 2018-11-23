<?php
/**
 * Access Controls module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Access_Controls;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use awsmug\Torro_Forms\Error;

/**
 * Class for the Access Controls module.
 *
 * @since 1.0.0
 */
class Module extends Module_Base implements Submodule_Registry_Interface {
	use Submodule_Registry_Trait;

	/**
	 * Bootstraps the module by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug        = 'access_controls';
		$this->title       = __( 'Access Controls', 'torro-forms' );
		$this->description = __( 'Access controls allow to limit who has permissions to view and submit a form.', 'torro-forms' );

		$this->submodule_base_class = Access_Control::class;
		$this->default_submodules   = array(
			'user_identification' => User_Identification::class,
			'members'             => Members::class,
			'timerange'           => Timerange::class,
			'submission_count'    => Submission_Count::class,
		);
	}

	/**
	 * Determines whether the current user can access a specific form or submission.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|Error      $result     Whether a user can access the form. Can be an error object to show a specific message to the user.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool|Error True if the form or submission can be accessed, false or error object otherwise.
	 */
	protected function can_access( $result, $form, $submission = null ) {
		if ( ! $result || is_wp_error( $result ) ) {
			return $result;
		}

		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! $access_control->enabled( $form ) ) {
				continue;
			}

			$sub_result = $access_control->can_access( $form, $submission );
			if ( ! $sub_result || is_wp_error( $sub_result ) ) {
				return $sub_result;
			}
		}

		return $result;
	}

	/**
	 * Sets additional data for a submission when it is created.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission New submission object.
	 * @param Form       $form       Form object the submission belongs to.
	 * @param array      $data       Submission POST data.
	 */
	protected function set_submission_data( $submission, $form, $data ) {
		foreach ( $this->submodules as $slug => $access_control ) {
			if ( ! is_a( $access_control, Submission_Modifier_Access_Control_Interface::class ) ) {
				continue;
			}

			if ( ! $access_control->enabled( $form ) ) {
				continue;
			}

			$access_control->set_submission_data( $submission, $form, $data );
		}
	}

	/**
	 * Registers the default access controls.
	 *
	 * The function also executes a hook that should be used by other developers to register their own access controls.
	 *
	 * @since 1.0.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default access controls have been registered.
		 *
		 * This action should be used to register custom access controls.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $access_controls Form setting manager instance.
		 */
		do_action( "{$this->get_prefix()}register_access_controls", $this );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->filters[] = array(
			'name'     => "{$this->get_prefix()}can_access_form",
			'callback' => array( $this, 'can_access' ),
			'priority' => 10,
			'num_args' => 3,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}create_new_submission",
			'callback' => array( $this, 'set_submission_data' ),
			'priority' => 10,
			'num_args' => 3,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 1,
		);
	}
}
