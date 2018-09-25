<?php
/**
 * Interface for API actions
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

use APIAPI\Core\Structures\Structure;
use APIAPI\Core\Structures\Route;
use APIAPI\Core\Request\API;

/**
 * Interface for an API action.
 *
 * @since 1.1.0
 */
interface API_Action_Interface {

	/**
	 * Gets the available connection choices.
	 *
	 * @since 1.1.0
	 *
	 * @return array Connection choices as $value => $label pairs.
	 */
	public function get_available_connection_choices();

	/**
	 * Gets the available API connections stored.
	 *
	 * @since 1.1.0
	 *
	 * @return array Array of $connection_slug => $connection pairs.
	 */
	public function get_available_connections();

	/**
	 * Gets the available structure choices.
	 *
	 * @since 1.1.0
	 *
	 * @return array Structure choices as $value => $label pairs.
	 */
	public function get_available_structure_choices();

	/**
	 * Gets the available route choices for a given structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return array Route choices as $value => $label pairs.
	 */
	public function get_available_route_choices( $structure_slug = null );

	/**
	 * Gets the available API structures with their routes.
	 *
	 * @since 1.1.0
	 *
	 * @return Associative array of $structure_slug => $data pairs.
	 */
	public function get_available_structures();

	/**
	 * Gets an API structure.
	 *
	 * The API structure is not scoped for the plugin. If you need the configured variant of the API,
	 * use the api() method. If you don't though, this method is more efficient to use then.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return Structure The API structure.
	 */
	public function api_structure( $structure_slug = null );

	/**
	 * Gets an API route for a structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $route_slug     Optional. Route identifier. Default is the first available route that is part of $structure_slug.
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return Route The API route.
	 */
	public function api_route( $route_slug = null, $structure_slug = null );

	/**
	 * Gets a configured API instance for an API structure.
	 *
	 * @since 1.1.0
	 *
	 * @param string $structure_slug Optional. Structure identifier. Default is the first structure.
	 * @return API The configured API instance.
	 */
	public function api( $structure_slug = null );

	/**
	 * Gets the registered connection types.
	 *
	 * @since 1.1.0
	 * @static
	 *
	 * @return array Associative array of $connection_slug => $data pairs. $data has a 'class' key containing the
	 *               class name to use for connections of that type, and 'authenticator_fields' is an associative
	 *               array of $field_slug => $field_definition pairs.
	 */
	public static function get_registered_connection_types();
}
