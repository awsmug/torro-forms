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
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the element this element choice is part of.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $element_id = 0;

	/**
	 * Identifier of the field this element choice belongs to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $field = '';

	/**
	 * Element choice value.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $value = '';

	/**
	 * Index to sort element choices by.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $sort = 0;
}
