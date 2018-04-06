<?php
/**
 * Form capabilities class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Forms;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Capabilities;

/**
 * Class for handling form capabilities.
 *
 * @since 1.0.0
 */
class Form_Capabilities extends Capabilities {

	/**
	 * Sets the mapping mode for capabilities.
	 *
	 * Capabilities can be dealt with manually, or meta capabilities can be mapped to
	 * base capabilities, or all capabilities can be mapped to other WordPress capabilities.
	 *
	 * By default, mapping is entirely disabled.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array|false $mode The new mapping mode. This can either be set to 'meta'
	 *                                 in order to map meta capabilities only, a plural slug
	 *                                 like 'posts' in order to map to WordPress capabilities
	 *                                 of that slug, an array with individual key mappings, or
	 *                                 false to disable mapping.
	 */
	public function map_capabilities( $mode ) {
		parent::map_capabilities( $mode );

		if ( isset( $this->capability_mappings[ $this->base_capabilities['manage_item_settings'] ] ) && 'manage_item_settings' === $this->capability_mappings[ $this->base_capabilities['manage_item_settings'] ] ) {
			unset( $this->capability_mappings[ $this->base_capabilities['manage_item_settings'] ] );
		}
	}

	/**
	 * Sets the supported capabilities.
	 *
	 * @since 1.0.0
	 */
	protected function set_capabilities() {
		parent::set_capabilities();

		$prefix = $this->get_prefix();

		$singular_slug = $this->manager->get_singular_slug();
		$plural_slug   = $this->manager->get_plural_slug();

		$this->base_capabilities['publish_items'] = sprintf( 'publish_%s', $prefix . $plural_slug );
		$this->meta_capabilities['publish_item']  = sprintf( 'publish_%s', $prefix . $singular_slug );

		$this->base_capabilities['read_private_items']     = sprintf( 'read_private_%s', $prefix . $plural_slug );
		$this->base_capabilities['edit_published_items']   = sprintf( 'edit_published_%s', $prefix . $plural_slug );
		$this->base_capabilities['edit_private_items']     = sprintf( 'edit_private_%s', $prefix . $plural_slug );
		$this->base_capabilities['delete_published_items'] = sprintf( 'delete_published_%s', $prefix . $plural_slug );
		$this->base_capabilities['delete_private_items']   = sprintf( 'delete_private_%s', $prefix . $plural_slug );

		$this->base_capabilities['manage_item_settings'] = sprintf( 'manage_%s_settings', $prefix . $singular_slug );
	}

	/**
	 * Maps capabilities via the `map_meta_cap` filter.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $caps    Required capabilities.
	 * @param string $cap     Capability name.
	 * @param int    $user_id User ID.
	 * @param array  $args    Additional arguments.
	 * @return array Required mapped capabilities.
	 */
	protected function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( empty( $this->capability_mappings ) ) {
			return $caps;
		}

		if ( ! isset( $this->capability_mappings[ $cap ] ) ) {
			if ( ! isset( $caps[0] ) || ! isset( $this->capability_mappings[ $caps[0] ] ) ) {
				return $caps;
			}

			$cap = $caps[0];
		}

		$user_id = absint( $user_id );

		if ( is_callable( $this->capability_mappings[ $cap ] ) ) {
			$mapped_cap = call_user_func( $this->capability_mappings[ $cap ], $user_id, $args );
		} else {
			$mapped_cap = $this->capability_mappings[ $cap ];
		}

		$caps = array( $mapped_cap );

		return $this->map_meta_cap( $caps, $mapped_cap, $user_id, $args );
	}

	/**
	 * Maps a specific item capability.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action  Action name. Either 'read', 'edit' or 'delete'.
	 * @param int    $user_id User ID.
	 * @param int    $args    Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_item_action( $action, $user_id, $args ) {
		/* Require an ID to be passed to this capability check. */
		if ( ! isset( $args[0] ) || ! is_numeric( $args[0] ) ) {
			return 'do_not_allow';
		}

		$item = $this->manager->get( $args[0] );
		if ( null === $item ) {
			return 'do_not_allow';
		}

		$primary_property = $this->manager->get_primary_property();

		$fallback_cap = $action . '_items';

		if ( method_exists( $this->manager, 'get_author_property' ) ) {
			$author_property = $this->manager->get_author_property();

			$author_id = $item->$author_property;
			if ( $author_id !== $user_id ) {
				$fallback_cap = 'read' === $action ? 'edit_others_items' : $action . '_others_items';
			}
		}

		$status = $item->status;
		if ( 'trash' === $status ) {
			$status = get_post_meta( $item->$primary_property, '_wp_trash_meta_status', true );
		}

		if ( $action . '_items' !== $fallback_cap && 'private' === $status ) {
			return $this->base_capabilities[ $action . '_private_items' ];
		}

		if ( 'read' !== $action && in_array( $status, array( 'publish', 'future' ), true ) ) {
			return $this->base_capabilities[ $action . '_published_items' ];
		}

		return $this->base_capabilities[ $fallback_cap ];
	}
}
