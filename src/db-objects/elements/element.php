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
use awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Collection;
use awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Collection;

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

	/**
	 * Returns all element choices that belong to the form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Element_Choice_Collection List of element choices.
	 */
	public function get_element_choices() {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'element_choices' )->get_collection( array(), 0, 'objects' );
		}

		return $this->manager->get_child_manager( 'element_choices' )->query( array(
			'element_id' => $this->id,
		) );
	}

	/**
	 * Returns all element settings that belong to the form.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Element_Setting_Collection List of element settings.
	 */
	public function get_element_settings() {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'element_settings' )->get_collection( array(), 0, 'objects' );
		}

		return $this->manager->get_child_manager( 'element_settings' )->query( array(
			'element_id' => $this->id,
		) );
	}

	/**
	 * Deletes the model from the database.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function delete() {
		$element_choices = $this->get_element_choices();
		foreach ( $element_choices as $element_choice ) {
			$element_choice->delete();
		}

		$element_settings = $this->get_element_settings();
		foreach ( $element_settings as $element_setting ) {
			$element_setting->delete();
		}

		return parent::delete();
	}
}
