<?php
/**
 * Basic API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via basic auth.
 *
 * @since 1.1.0
 */
class Basic_Connection extends Connection {

	/**
	 * Connection type.
	 *
	 * @since 1.1.0
	 */
	const TYPE = 'basic';

	/**
	 * The API username.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $username = '';

	/**
	 * The API password.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $password = '';

	/**
	 * Gets the definitions for the fields required to provide authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return Array of $field_slug => $field_definition pairs.
	 */
	public static function get_authenticator_fields() {
		return array(
			'username' => array(
				'type'          => 'text',
				'label'         => __( 'Username', 'torro-forms' ),
				'description'   => __( 'Enter the username for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
			'password' => array(
				'type'          => 'text',
				'label'         => __( 'Password', 'torro-forms' ),
				'description'   => __( 'Enter the password for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
		);
	}
}
