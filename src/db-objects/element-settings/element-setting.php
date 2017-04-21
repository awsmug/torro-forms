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
}
