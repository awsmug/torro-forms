<?php
/**
 * Element class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Elements;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;

/**
 * Class representing an element.
 *
 * @since 1.0.0
 *
 * @property int    $container_id
 * @property string $label
 * @property int    $sort
 * @property string $type
 *
 * @property-read int $id
 */
class Element extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Element ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the container this element is part of.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $container_id = 0;

	/**
	 * Element label.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $label = '';

	/**
	 * Index to sort elements by.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $sort = 0;

	/**
	 * Element type identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $type = '';
}
