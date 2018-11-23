<?php
/**
 * REST embed limits trait
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;

/**
 * Trait to limit embed content for REST controllers that support it.
 *
 * @since 1.0.0
 */
trait REST_Embed_Limits_Trait {

	/**
	 * Gets the limit for embedding resources from another collection endpoint.
	 *
	 * The default limits are:
	 * * 10 for 'forms'
	 * * 50 for 'containers'
	 * * 500 for 'elements'
	 * * 10000 for 'element_choices'
	 * * 10000 for 'element_settings'
	 * * 10 for everything else
	 *
	 * You can override individual limits by defining a constant like
	 * `TORRO_REST_EMBED_LIMIT_{$plural_slug}` where $plural_slug is the upper-case
	 * underscore-delimited plural slug of the respective resource.
	 *
	 * @since 1.0.0
	 *
	 * @param string $plural_slug Non-prefixed plural slug of a resource that is available as
	 *                            a Torro Forms REST endpoint.
	 * @return int Number of items to request at once.
	 */
	protected function get_embed_limit( $plural_slug ) {
		$constant_name = 'TORRO_REST_EMBED_LIMIT_' . strtoupper( $plural_slug );

		if ( defined( $constant_name ) ) {
			return (int) constant( $constant_name );
		}

		switch ( $plural_slug ) {
			case 'forms':
				return 10;
			case 'containers':
				return 50;
			case 'elements':
				return 500;
			case 'element_choices':
			case 'element_settings':
				return 10000;
		}

		return 10;
	}
}
