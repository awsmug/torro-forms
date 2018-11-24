<?php
/**
 * Element choice class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Choices;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;

/**
 * Class representing an element choice.
 *
 * @since 1.0.0
 *
 * @property int    $element_id
 * @property string $field
 * @property string $value
 * @property int    $sort
 *
 * @property-read int $id
 */
class Element_Choice extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Element choice ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the element this element choice is part of.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $element_id = 0;

	/**
	 * Identifier of the field this element choice belongs to.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $field = '';

	/**
	 * Element choice value.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $value = '';

	/**
	 * Index to sort element choices by.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $sort = 0;

	/**
	 * Returns the parent element for the element choice.
	 *
	 * @since 1.0.0
	 *
	 * @return Element|null Parent element, or null if none set.
	 */
	public function get_element() {
		if ( empty( $this->element_id ) ) {
			return null;
		}

		return $this->manager->get_parent_manager( 'elements' )->get( $this->element_id );
	}

	/**
	 * Duplicates the element choice.
	 *
	 * @since 1.0.0
	 *
	 * @param int $element_id New parent element ID to use for the element choice.
	 * @return Element_Choice|WP_Error New element choice object on success, error object on failure.
	 */
	public function duplicate( $element_id ) {
		$new_element_choice = $this->manager->create();

		foreach ( $this->to_json() as $key => $value ) {
			if ( 'id' === $key ) {
				continue;
			}

			if ( 'element_id' === $key ) {
				$new_element_choice->element_id = $element_id;
				continue;
			}

			$new_element_choice->$key = $value;
		}

		$status = $new_element_choice->sync_upstream();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		return $new_element_choice;
	}
}
