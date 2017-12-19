<?php
/**
 * Element setting CLI command class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Settings;

use Leaves_And_Love\Plugin_Lib\DB_Objects\CLI_Models_Command;

/**
 * Class to access element settings via WP-CLI.
 *
 * @since 1.0.0
 */
class CLI_Element_Settings_Command extends CLI_Models_Command {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->obj_fields = array( 'id', 'element_id', 'name', 'value' );
	}
}
