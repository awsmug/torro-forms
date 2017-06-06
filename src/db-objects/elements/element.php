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
use awsmug\Torro_Forms\DB_Objects\Containers\Container;
use awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Collection;
use awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Collection;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Element_Type;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Multi_Field_Element_Type_Interface;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

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
	 * Returns the parent container for the element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Container|null Parent container, or null if none set.
	 */
	public function get_container() {
		if ( empty( $this->container_id ) ) {
			return null;
		}

		return $this->manager->get_parent_manager( 'containers' )->get( $this->container_id );
	}

	/**
	 * Returns all element choices that belong to the element.
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
	 * Returns all element settings that belong to the element.
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
	 * Returns the element type object for this element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Element_Type Element type object.
	 */
	public function get_element_type() {
		if ( empty( $this->type ) ) {
			return null;
		}

		$type = $this->manager->types()->get( $this->type );
		if ( is_wp_error( $type ) ) {
			return null;
		}

		return $type;
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

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool            $include_meta Optional. Whether to include metadata for each model in the collection.
	 *                                      Default true.
	 * @param Submission|null $submission   Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the model.
	 */
	public function to_json( $include_meta = true, $submission = null ) {
		$data = parent::to_json( $include_meta );

		$element_type = $this->get_element_type();
		if ( $element_type ) {
			$data['field_data'] = $element_type->to_json( $this, $submission );
		}

		return $data;
	}

	/**
	 * Validates submission fields for this element.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $values Array of `$field => $value` pairs with the main field having
	 *                      a key of '_main'.
	 * @return array Validated array where each value is either the validated value, or an
	 *               error object on failure.
	 */
	public function validate_fields( $values ) {
		$element_type = $this->get_element_type();
		if ( ! $element_type ) {
			return array();
		}

		$main_value = null;
		if ( isset( $values['_main'] ) ) {
			$main_value = $values['_main'];
			unset( $values['_main'] );
		}

		$validated = array();
		if ( is_a( $element_type, Multi_Field_Element_Type_Interface::class ) ) {
			$validated = $element_type->validate_additional_fields( $values, $this );
		}

		$validated['_main'] = $element_type->validate_field( $main_value, $this );

		return $validated;
	}
}
