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
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Non_Input_Element_Type_Interface;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Types\Choice_Element_Type_Interface;
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
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the container this element is part of.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $container_id = 0;

	/**
	 * Element label.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $label = '';

	/**
	 * Index to sort elements by.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $sort = 0;

	/**
	 * Element type identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $type = '';

	/**
	 * Returns the parent container for the element.
	 *
	 * @since 1.0.0
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
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Element_Choice_Collection List of element choices.
	 */
	public function get_element_choices( $args = array() ) {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'element_choices' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'     => -1,
				'element_id' => $this->id,
			)
		);

		return $this->manager->get_child_manager( 'element_choices' )->query( $args );
	}

	/**
	 * Returns all element settings that belong to the element.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Element_Setting_Collection List of element settings.
	 */
	public function get_element_settings( $args = array() ) {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'element_settings' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'     => -1,
				'element_id' => $this->id,
			)
		);

		return $this->manager->get_child_manager( 'element_settings' )->query( $args );
	}

	/**
	 * Returns the element type object for this element.
	 *
	 * @since 1.0.0
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
	 *
	 * @param bool            $include_meta Optional. Whether to include metadata for each model in the collection.
	 *                                      Default true.
	 * @param Submission|null $submission   Optional. Submission to get the values from, if available. Default null.
	 * @return array Array including all information for the model.
	 */
	public function to_json( $include_meta = true, $submission = null ) {
		$data = parent::to_json( $include_meta );

		/**
		 * Filters the element input classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $input_classes Array of input classes.
		 * @param Element $element       Element object.
		 */
		$input_classes = apply_filters( "{$this->manager->get_prefix()}element_input_classes", array( 'torro-element-input' ), $this );

		/**
		 * Filters the element label classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $label_classes Array of label classes.
		 * @param Element $element       Element object.
		 */
		$label_classes = apply_filters( "{$this->manager->get_prefix()}element_label_classes", array( 'torro-element-label' ), $this );

		/**
		 * Filters the element wrap classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $wrap_classes Array of wrap classes.
		 * @param Element $element      Element object.
		 */
		$wrap_classes = apply_filters( "{$this->manager->get_prefix()}element_wrap_classes", array( 'torro-element-wrap' ), $this );

		/**
		 * Filters the element description classes.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $description_classes Array of description classes.
		 * @param Element $element             Element object.
		 */
		$description_classes = apply_filters( "{$this->manager->get_prefix()}element_description_classes", array( 'torro-element-description' ), $this );

		/**
		 * Filters the element errors classes, for the error messages wrap.
		 *
		 * @since 1.0.0
		 *
		 * @param array   $errors_classes Array of errors classes.
		 * @param Element $element        Element object.
		 */
		$errors_classes = apply_filters( "{$this->manager->get_prefix()}element_errors_classes", array( 'torro-element-errors' ), $this );

		$data = array_merge(
			$data,
			array(
				'value'             => null,
				'input_attrs'       => array(
					'id'    => 'torro-element-' . $this->id,
					'name'  => 'torro_submission[values][' . $this->id . '][_main]',
					'class' => implode( ' ', $input_classes ),
				),
				'label_required'    => '',
				'label_attrs'       => array(
					'id'    => 'torro-element-' . $this->id . '-label',
					'class' => implode( ' ', $label_classes ),
					'for'   => 'torro-element-' . $this->id,
				),
				'wrap_attrs'        => array(
					'id'    => 'torro-element-' . $this->id . '-wrap',
					'class' => implode( ' ', $wrap_classes ),
				),
				'description'       => '',
				'description_attrs' => array(
					'id'    => 'torro-element-' . $this->id . '-description',
					'class' => implode( ' ', $description_classes ),
				),
				'errors'            => array(),
				'errors_attrs'      => array(
					'id'    => 'torro-element-' . $this->id . '-errors',
					'class' => implode( ' ', $errors_classes ),
				),
				'before'            => '',
				'after'             => '',
			)
		);

		if ( has_action( "{$this->manager->get_prefix()}element_before" ) ) {
			ob_start();

			/**
			 * Allows to print additional content before an element in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param int $element_id Element ID.
			 */
			do_action( "{$this->manager->get_prefix()}element_before", $this->id );

			$data['before'] = ob_get_clean();
		}

		if ( has_action( "{$this->manager->get_prefix()}element_after" ) ) {
			ob_start();

			/**
			 * Allows to print additional content after an element in the frontend.
			 *
			 * @since 1.0.0
			 *
			 * @param int $element_id Element ID.
			 */
			do_action( "{$this->manager->get_prefix()}element_after", $this->id );

			$data['after'] = ob_get_clean();
		}

		$element_type = $this->get_element_type();
		if ( $element_type ) {
			$data = $element_type->filter_json( $data, $this, $submission );
		}

		/**
		 * Filters the main element value.
		 *
		 * @since 1.0.0
		 *
		 * @param mixed   $value   Element value.
		 * @param Element $element Element object.
		 */
		$data['value'] = apply_filters( "{$this->manager->get_prefix()}element_value", $data['value'], $this );

		return $data;
	}

	/**
	 * Duplicates the element including all of its contents.
	 *
	 * @since 1.0.0
	 *
	 * @param int $container_id New parent container ID to use for the element.
	 * @return Element|WP_Error New element object on success, error object on failure.
	 */
	public function duplicate( $container_id ) {
		$new_element = $this->manager->create();

		foreach ( $this->to_json() as $key => $value ) {
			if ( 'id' === $key ) {
				continue;
			}

			if ( 'container_id' === $key ) {
				$new_element->container_id = $container_id;
				continue;
			}

			$new_element->$key = $value;
		}

		$status = $new_element->sync_upstream();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		foreach ( $this->get_element_choices() as $element_choice ) {
			$element_choice->duplicate( $new_element->id );
		}

		foreach ( $this->get_element_settings() as $element_setting ) {
			$element_setting->duplicate( $new_element->id );
		}

		return $new_element;
	}

	/**
	 * Validates submission fields for this element.
	 *
	 * @since 1.0.0
	 *
	 * @param array      $values     Array of `$field => $value` pairs with the main field having
	 *                               a key of '_main'.
	 * @param Submission $submission Submission the values belong to.
	 * @return array Validated array where each value is either the validated value, or an
	 *               error object on failure.
	 */
	public function validate_fields( $values, $submission ) {
		$element_type = $this->get_element_type();
		if ( ! $element_type ) {
			return array();
		}

		if ( is_a( $element_type, Non_Input_Element_Type_Interface::class ) ) {
			return array();
		}

		$main_value = null;
		if ( isset( $values['_main'] ) ) {
			$main_value = $values['_main'];
			unset( $values['_main'] );
		}

		$validated = array();
		if ( is_a( $element_type, Multi_Field_Element_Type_Interface::class ) ) {
			$validated = $element_type->validate_additional_fields( $values, $this, $submission );
		}

		$validated['_main'] = $element_type->validate_field( $main_value, $this, $submission );

		/**
		 * Filters the element values.
		 *
		 * @since 1.0.5
		 *
		 * @param array        $validated    Array of element values.
		 * @param Element_Type $element_type Element_Type object.
		 * @param Element      $element      Element object.
		 */
		$validated = apply_filters( "{$this->manager->get_prefix()}element_values_validated", $validated, $this->get_element_type(), $this );

		return $validated;
	}

	/**
	 * Checks whether the element does not expect any input.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the element does not expect any input, false otherwise.
	 */
	public function is_non_input() {
		$element_type = $this->get_element_type();
		if ( ! $element_type ) {
			return false;
		}

		return is_a( $element_type, Non_Input_Element_Type_Interface::class );
	}

	/**
	 * Checks whether the element is evaluable.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the element is evaluable, false otherwise.
	 */
	public function is_evaluable() {
		$element_type = $this->get_element_type();
		if ( ! $element_type ) {
			return false;
		}

		return is_a( $element_type, Choice_Element_Type_Interface::class );
	}

	/**
	 * Checks whether the element contains multiple fields.
	 *
	 * @since 1.0.0
	 *
	 * @return bool True if the element contains multiple fields, false otherwise.
	 */
	public function is_multifield() {
		$element_type = $this->get_element_type();
		if ( ! $element_type ) {
			return false;
		}

		return is_a( $element_type, Multi_Field_Element_Type_Interface::class );
	}
}
