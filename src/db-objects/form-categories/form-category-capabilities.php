<?php
/**
 * Form category capabilities class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Form_Categories;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Capabilities;

/**
 * Class for handling form category capabilities.
 *
 * @since 1.0.0
 */
class Form_Category_Capabilities extends Capabilities {

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
		$this->capability_mappings = array();

		if ( $mode ) {
			$this->capability_mappings[ $this->meta_capabilities['edit_item'] ]   = array( $this, 'map_edit_item' );
			$this->capability_mappings[ $this->meta_capabilities['delete_item'] ] = array( $this, 'map_delete_item' );
			$this->capability_mappings[ $this->meta_capabilities['assign_item'] ] = array( $this, 'map_assign_item' );

			if ( is_string( $mode ) && 'meta' !== $mode ) {
				foreach ( $this->base_capabilities as $name => $real_name ) {
					if ( in_array( $mode, array( 'posts', 'pages' ), true ) ) {
						if ( 'read_items' === $name || 'read_others_items' === $name ) {
							$this->capability_mappings[ $real_name ] = 'read';
							continue;
						}

						if ( 'create_items' === $name ) {
							$this->capability_mappings[ $real_name ] = sprintf( 'edit_%s', $mode );
							continue;
						}
					}

					if ( in_array( $mode, array( 'categories', 'post_tags' ), true ) ) {
						if ( 'assign_items' === $name ) {
							$this->capability_mappings[ $real_name ] = 'edit_posts';
							continue;
						}

						$this->capability_mappings[ $real_name ] = 'manage_' . $mode;
						continue;
					}

					$this->capability_mappings[ $real_name ] = str_replace( '_items', '_' . $mode, $name );
				}
			} elseif ( is_array( $mode ) ) {
				foreach ( $this->base_capabilities as $name => $real_name ) {
					if ( ! isset( $mode[ $name ] ) ) {
						continue;
					}

					$this->capability_mappings[ $real_name ] = $mode[ $name ];
				}
			}
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

		$this->base_capabilities = array(
			'manage_items' => sprintf( 'manage_%s', $prefix . $plural_slug ),
			'edit_items'   => sprintf( 'edit_%s', $prefix . $plural_slug ),
			'delete_items' => sprintf( 'delete_%s', $prefix . $plural_slug ),
			'assign_items' => sprintf( 'assign_%s', $prefix . $plural_slug ),
		);

		$this->meta_capabilities = array(
			'edit_item'   => sprintf( 'edit_%s', $prefix . $singular_slug ),
			'delete_item' => sprintf( 'delete_%s', $prefix . $singular_slug ),
			'assign_item' => sprintf( 'assign_%s', $prefix . $singular_slug ),
		);
	}

	/**
	 * Maps the item assigning capability.
	 *
	 * @since 1.0.0
	 *
	 * @param int   $user_id  User ID.
	 * @param array $args     Additional arguments.
	 * @return string Mapped capability name.
	 */
	protected function map_assign_item( $user_id, $args ) {
		return $this->map_item_action( 'assign', $user_id, $args );
	}
}
