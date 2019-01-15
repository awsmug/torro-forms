<?php
/**
 * API-API class for a scoped external API
 *
 * @package APIAPI\Core\Request
 * @since 1.0.0
 */

namespace APIAPI\Core\Request;

if ( ! class_exists( 'APIAPI\Core\Request\Method' ) ) {

	/**
	 * Enumeration for the available request methods.
	 *
	 * @since 1.0.0
	 */
	class Method {
		const GET    = 'GET';
		const POST   = 'POST';
		const PUT    = 'PUT';
		const PATCH  = 'PATCH';
		const DELETE = 'DELETE';
	}

}
