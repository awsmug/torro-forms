<?php
/**
 * Form CLI command class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\CLI_Models_Command;

/**
 * Class to access forms via WP-CLI.
 *
 * @since 1.0.0
 */
class CLI_Forms_Command extends CLI_Models_Command {

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Manager $manager The manager instance.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->obj_fields = array( 'id', 'title', 'slug', 'author', 'status', 'timestamp', 'timestamp_modified' );
	}

	/**
	 * Returns command information for the aggregate command that includes the other commands.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $name Base command name.
	 * @return array Command information.
	 */
	protected function get_general_args( $name ) {
		return array(
			'shortdesc' => 'Manage Torro Forms forms.',
		);
	}
}
