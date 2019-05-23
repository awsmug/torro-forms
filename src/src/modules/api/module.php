<?php
/**
 * Protectors module class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\API;

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
		$this->slug        = 'api';
		$this->title       = __( 'API', 'torro-forms' );
		$this->description = __( 'Adjust API settings for Torro Forms.', 'torro-forms' );

		$this->submodule_base_class = API::class;
		$this->default_submodules   = array(
			'submissions' => Submissions::class,
		);
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
	 * Sets up all action and filter hooks for the service.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => 'init',
			'callback' => array( $this, 'register_defaults' ),
			'priority' => 100,
			'num_args' => 0,
		);
	}
}
