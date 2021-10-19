<?php
/**
 * Protectors module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Protectors;

use awsmug\Torro_Forms\Modules\Module as Module_Base;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Interface;
use awsmug\Torro_Forms\Modules\Submodule_Registry_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use APIAPI\Core\APIAPI;

/**
 * Class for the Protectors module.
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
		$this->slug        = 'protectors';
		$this->title       = __( 'Protectors', 'torro-forms' );
		$this->description = __( 'Protectors increase security by preventing your form from spam.', 'torro-forms' );

		$this->submodule_base_class = Protector::class;
		$this->default_submodules   = array(
			'honeypot'  => Honeypot::class,
			'timetrap'  => Timetrap::class,
			'linkcount' => Linkcount::class,
			'recaptcha' => reCAPTCHA::class,
		);
	}

	/**
	 * Verifies a request by ensuring that it is not spammy.
	 *
	 * @since 1.0.0
	 *
	 * @param bool|WP_Error   $verified   Either a boolean or an error object must be returned. Default true.
	 * @param array           $data       Submission POST data.
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if a new submission.
	 * @return bool|WP_Error A possibly modified $verified value.
	 */
	protected function verify_request( $verified, $data, $form, $submission = null ) {
		if ( ! $verified ) {
			return $verified;
		}

		// Protectors are only applied before submission completion.
		if ( ! $this->is_final_submit_request( $form, $submission ) ) {
			return $verified;
		}

		foreach ( $this->submodules as $slug => $protector ) {
			if ( ! $protector->enabled( $form ) ) {
				continue;
			}

			$sub_verified = $protector->verify_request( $data, $form, $submission );
			if ( ! $sub_verified || is_wp_error( $sub_verified ) ) {
				return $sub_verified;
			}
		}

		return $verified;
	}

	/**
	 * Renders the output for the protector before the Submit button.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id Form ID.
	 */
	protected function render_output( $form_id ) {
		$form = $this->manager()->forms()->get( $form_id );

		foreach ( $this->submodules as $slug => $protector ) {
			if ( ! $protector->enabled( $form ) ) {
				continue;
			}

			$protector->render_output( $form );
		}
	}

	/**
	 * Registers the default protectors.
	 *
	 * The function also executes a hook that should be used by other developers to register their own protectors.
	 *
	 * @since 1.0.0
	 */
	protected function register_defaults() {
		foreach ( $this->default_submodules as $slug => $class_name ) {
			$this->register( $slug, $class_name );
		}

		/**
		 * Fires when the default protectors have been registered.
		 *
		 * This action should be used to register custom protectors.
		 *
		 * @since 1.0.0
		 *
		 * @param Module $protectors Action manager instance.
		 */
		do_action( "{$this->get_prefix()}register_protectors", $this );
	}

	/**
	 * Checks whether the current request is the final submit request for a submission completion.
	 *
	 * @since 1.0.0
	 *
	 * @param Form            $form       Form object.
	 * @param Submission|null $submission Submission object, or null if no submission is set.
	 * @return bool True if there is a next container, false otherwise.
	 */
	protected function is_final_submit_request( $form, $submission = null ) {
		if ( $submission ) {
			$next_container = $submission->get_next_container();
			return null === $next_container;
		}

		$containers = $form->get_containers();
		return 1 >= count( $containers );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}verify_form_submission_request",
			'callback' => array( $this, 'verify_request' ),
			'priority' => 10,
			'num_args' => 4,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}form_submit_button_before",
			'callback' => array( $this, 'render_output' ),
			'priority' => 10,
			'num_args' => 1,
		);
		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 0,
		);
	}
}
