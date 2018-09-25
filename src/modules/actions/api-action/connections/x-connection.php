<?php
/**
 * X API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via X header authentication.
 *
 * @since 1.1.0
 */
class X_Connection extends Connection {

	/**
	 * The authorization header name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $header_name = '';

	/**
	 * The authorization token.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $token = '';

	/**
	 * Gets the definitions for the fields required to provide authentication data.
	 *
	 * @since 1.1.0
	 *
	 * @return Array of $field_slug => $field_definition pairs.
	 */
	public static function get_authenticator_fields() {
		return array(
			'header_name' => array(
				'type'        => 'text',
				'label'       => __( 'Authorization Header Name', 'torro-forms' ),
				'description' => __( 'Enter the name of the authorization header that is sent to verify API requests. It will be prefixed with &#8220;X-&#8221;.', 'torro-forms' ),
				'default'     => 'Authorization',
				'readonly'    => true,
			),
			'token'       => array(
				'type'          => 'text',
				'label'         => __( 'Authorization Token', 'torro-forms' ),
				'description'   => __( 'Enter the authorization token for the API.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
			),
		);
	}
}
