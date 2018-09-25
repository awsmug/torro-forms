<?php
/**
 * API connection base class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

use awsmug\Torro_Forms\Modules\Actions\API_Action\API_Action;

/**
 * Base class for an API connection.
 *
 * @since 1.1.0
 */
abstract class Connection {

	/**
	 * The connection slug.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $slug = '';

	/**
	 * The connection title.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $title = '';

	/**
	 * The parent API action.
	 *
	 * @since 1.1.0
	 * @var API_Action|null
	 */
	protected $api_action = null;

	/**
	 * Constructor.
	 *
	 * Sets the connection data.
	 *
	 * @since 1.1.0
	 *
	 * @param API_Action $api_action Parent API action.
	 * @param array      $data       Connection data array.
	 */
	public function __construct( $api_action, $data ) {
		$this->api_action = $api_action;

		foreach ( $data as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->$key = $value;
			}
		}

		if ( empty( $this->slug ) ) {
			$this->slug = sanitize_title( $this->title );
		}
	}

	/**
	 * Gets information about this connection.
	 *
	 * @since 1.1.0
	 *
	 * @param string $field Field slug to get the value for.
	 * @return string Value for the field, or empty string if invalid field.
	 */
	public function get( $field ) {
		if ( 'api_action' === $field || ! isset( $this->$field ) ) {
			return '';
		}

		return $this->$field;
	}

	/**
	 * Gets the array of authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return array Authentication data as $key => $value pairs.
	 */
	public function get_authentication_data() {
		$data = get_object_vars( $this );

		unset( $data['slug'], $data['title'], $data['api_action'] );

		return $data;
	}
}
