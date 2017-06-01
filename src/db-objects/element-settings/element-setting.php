<?php
/**
 * Element setting class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Settings;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Elements\Element;

/**
 * Class representing an element setting.
 *
 * @since 1.0.0
 *
 * @property int    $element_id
 * @property string $name
 * @property string $value
 *
 * @property-read int $id
 */
class Element_Setting extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Element setting ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the element this element setting is part of.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $element_id = 0;

	/**
	 * Element setting name.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $name = '';

	/**
	 * Element setting value.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $value = '';

	/**
	 * Returns the parent element for the element setting.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Element|null Parent element, or null if none set.
	 */
	public function get_element() {
		if ( empty( $this->element_id ) ) {
			return null;
		}

		return $this->manager->get_parent_manager( 'elements' )->get( $this->element_id );
	}
}
