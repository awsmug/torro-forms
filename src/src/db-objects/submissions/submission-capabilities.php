<?php
/**
 * Submission capabilities class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Capabilities;

/**
 * Class for handling submission capabilities.
 *
 * @since 1.0.0
 */
class Submission_Capabilities extends Capabilities {

	/**
	 * Sets the supported capabilities.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function set_capabilities() {
		parent::set_capabilities();

		$prefix      = $this->get_prefix();
		$plural_slug = $this->manager->get_plural_slug();

		$this->base_capabilities['read_others_items']   = sprintf( 'read_others_%s', $prefix . $plural_slug );
		$this->base_capabilities['edit_others_items']   = sprintf( 'edit_others_%s', $prefix . $plural_slug );
		$this->base_capabilities['delete_others_items'] = sprintf( 'delete_others_%s', $prefix . $plural_slug );
	}

	/**
	 * Maps capabilities via the `map_meta_cap` filter.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param array  $caps    Required capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id User ID.
	 * @param array  $args    Additional arguments.
	 * @return array Required mapped capabilities.
	 */
	protected function map_meta_cap( $caps, $cap, $user_id, $args ) {
		$mapped_caps = parent::map_meta_cap( $caps, $cap, $user_id, $args );

		// In addition to the regular post capabilities, require 'edit_users' to deal with someone else's submissions.
		if ( in_array( $cap, array( $this->base_capabilities['read_others_items'], $this->base_capabilities['edit_others_items'], $this->base_capabilities['delete_others_items'] ), true ) ) {
			$mapped_caps[] = 'edit_users';
		}

		return $mapped_caps;
	}

	/**
	 * Maps a specific item capability.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $action  Action name. Either 'read', 'edit' or 'delete'.
	 * @param int    $user_id User ID.
	 * @param int    $args    Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_item_action( $action, $user_id, $args ) {
		$capability = parent::map_item_action( $action, $user_id, $args );

		if ( 'do_not_allow' !== $capability ) {
			$item = $this->manager->get( $args[0] );

			if ( $item->user_id !== $user_id ) {
				$capability = $this->base_capabilities[ $action . '_others_items' ];
			}
		}

		return $capability;
	}
}
