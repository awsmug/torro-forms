<?php
/**
 * X API connection class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

/**
 * Class for an API connection via X header authentication.
 *
 * @since 1.0.0
 */
class X_Connection extends Connection {

	/**
	 * The authorization header name.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $header_name = 'Authorization';

	/**
	 * The authorization token.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $token = '';
}
