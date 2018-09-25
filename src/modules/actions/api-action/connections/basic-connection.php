<?php
/**
 * Basic API connection class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action\Connections;

/**
 * Class for an API connection via basic auth.
 *
 * @since 1.0.0
 */
class Basic_Connection extends Connection {

	/**
	 * The API username.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $username = '';

	/**
	 * The API password.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $password = '';
}
