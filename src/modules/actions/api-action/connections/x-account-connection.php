<?php
/**
 * X-Account API connection class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via X-Account header authentication.
 *
 * @since 1.1.0
 */
class X_Account_Connection extends Connection {

	/**
	 * The account placeholder name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $placeholder_name = 'account';

	/**
	 * The account identifier.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $account = '';

	/**
	 * The authorization header name.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $header_name = 'Authorization';

	/**
	 * The authorization token.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $token = '';
}
