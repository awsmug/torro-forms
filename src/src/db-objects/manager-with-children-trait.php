<?php
/**
 * Manager with children trait
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

/**
 * Trait for managers that support child managers.
 *
 * @since 1.0.0
 */
trait Manager_With_Children_Trait {

	/**
	 * Child managers.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $child_managers = array();

	/**
	 * Adds a child manager.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $slug    Child manager identifier.
	 * @param Manager $manager Child manager instance.
	 * @return bool True on success, false on failure.
	 */
	public function add_child_manager( $slug, $manager ) {
		if ( ! is_a( $manager, Manager::class ) ) {
			return false;
		}

		$this->child_managers[ $slug ] = $manager;

		return true;
	}

	/**
	 * Retrieves a child manager.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Child manager identifier.
	 * @return Manager Child manager instance.
	 */
	public function get_child_manager( $slug ) {
		if ( ! isset( $this->child_managers[ $slug ] ) ) {
			return null;
		}

		return $this->child_managers[ $slug ];
	}
}
