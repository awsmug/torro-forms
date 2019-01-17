<?php
/**
 * Route select field class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Modules\Actions\API_Action;

use Leaves_And_Love\Plugin_Lib\Fields\Select;

/**
 * Class for a (very custom) field mappings field.
 *
 * @since 1.1.0
 */
class Route_Select_Field extends Select {

	/**
	 * Field type identifier.
	 *
	 * @since 1.1.0
	 * @var string
	 */
	protected $slug = 'routeselect';

	/**
	 * Validates a single value for the field.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $value Value to validate. When null is passed, the method
	 *                     assumes no value was sent.
	 * @return mixed|WP_Error The validated value on success, or an error
	 *                        object on failure.
	 */
	protected function validate_single( $value = null ) {
		if ( empty( $value ) ) {
			if ( $this->multi ) {
				return array();
			}

			return '';
		}

		// Skip actual strict validation from parent class, since choices are dynamic.
		if ( $this->multi ) {
			return array_map( 'trim', (array) $value );
		}

		return trim( $value );
	}
}
