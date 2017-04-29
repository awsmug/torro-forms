<?php
/**
 * Post types class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Type_Managers\Post_Type_Manager;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;

/**
 * Class for managing post types.
 *
 * @since 1.0.0
 */
class Post_Types extends Post_Type_Manager {
	use Hook_Service_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $prefix The instance prefix.
	 */
	public function __construct( $prefix ) {
		parent::__construct( $prefix );

		$this->setup_hooks();
	}

	/**
	 * Registers the form post type.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function register_form_post_type() {

	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		$this->actions = array(
			array(
				'name'     => 'init',
				'callback' => array( $this, 'register_form_post_type' ),
				'priority' => 1,
				'num_args' => 0,
			),
		);
	}
}
