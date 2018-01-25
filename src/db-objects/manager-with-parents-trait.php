<?php
/**
 * Manager with parents trait
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

/**
 * Trait for managers that support parent managers.
 *
 * @since 1.0.0
 */
trait Manager_With_Parents_Trait {

	/**
	 * Parent managers.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $parent_managers = array();

	/**
	 * Adds a parent manager.
	 *
	 * @since 1.0.0
	 *
	 * @param string  $slug    Parent manager identifier.
	 * @param Manager $manager Parent manager instance.
	 * @return bool True on success, false on failure.
	 */
	public function add_parent_manager( $slug, $manager ) {
		if ( ! is_a( $manager, Manager::class ) ) {
			return false;
		}

		$this->parent_managers[ $slug ] = $manager;

		return true;
	}

	/**
	 * Retrieves a parent manager.
	 *
	 * @since 1.0.0
	 *
	 * @param string $slug Parent manager identifier.
	 * @return Manager Parent manager instance.
	 */
	public function get_parent_manager( $slug ) {
		if ( ! isset( $this->parent_managers[ $slug ] ) ) {
			return null;
		}

		return $this->parent_managers[ $slug ];
	}
}
