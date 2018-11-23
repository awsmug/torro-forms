<?php
/**
 * Error class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms;

use WP_Error;

/**
 * Class for handling errors.
 *
 * @since 1.0.0
 */
class Error extends WP_Error {

	/**
	 * Whether the error was handled already.
	 *
	 * @since 1.0.0
	 * @var bool
	 */
	protected $error_handled = false;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 *
	 * @param string $error_code    Error code.
	 * @param string $error_message Error message.
	 * @param string $method        Name of the method or function the error occurred in.
	 * @param string $version       Optional. Version the error message was added. Default '1.0.0'.
	 * @param array  $data          Optional. Further error data. Default empty array.
	 */
	public function __construct( $error_code, $error_message, $method, $version = '1.0.0', $data = array() ) {
		parent::__construct(
			$error_code,
			$error_message,
			array_merge(
				array(
					'method'  => $method,
					'version' => $version,
				),
				$data
			)
		);
	}

	/**
	 * Magic caller.
	 *
	 * Ensures unhandled errors still trigger proper messages without causing a fatal error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $method_name Method name.
	 * @param array  $arguments   Method arguments.
	 * @return Error Pass-through error instance.
	 */
	public function __call( $method_name, $arguments ) {
		if ( ! $this->error_handled ) {
			/* translators: %s: method name */
			$this->handle_error( sprintf( __( 'After the error occurred, it was tried to call method %s on the unhandled error object.', 'torro-forms' ), '<code>' . $method_name . '()</code>' ) );

			$this->error_handled = true;
		}

		return $this;
	}

	/**
	 * Magic getter.
	 *
	 * Ensures unhandled errors still trigger proper messages without causing a fatal error.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property Property name.
	 * @return Error Pass-through error instance.
	 */
	public function __get( $property ) {
		if ( ! $this->error_handled ) {
			/* translators: %s: property name */
			$this->handle_error( sprintf( __( 'After the error occurred, it was tried to access property %s on the unhandled error object.', 'torro-forms' ), '<code>$' . $property . '</code>' ) );

			$this->error_handled = true;
		}

		return $this;
	}

	/**
	 * Handles the error by triggering a warning.
	 *
	 * @since 1.0.0
	 *
	 * @param string $access_message Message about how the error handler was triggered.
	 */
	protected function handle_error( $access_message ) {
		$data    = $this->get_error_data();
		$message = $this->get_error_message() . ' ' . $access_message;

		torro()->error_handler()->doing_it_wrong( $data['method'], $message, $data['version'] );
	}
}
