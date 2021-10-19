<?php
/**
 * Container class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Containers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Elements\Element_Collection;
use WP_Error;

/**
 * Class representing a container.
 *
 * @since 1.0.0
 *
 * @property int    $form_id
 * @property string $label
 * @property int    $sort
 *
 * @property-read int $id
 */
class Container extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Container ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the form this container is part of.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Container label.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $label = '';

	/**
	 * Index to sort containers by.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $sort = 0;

	/**
	 * Returns the parent form for the container.
	 *
	 * @since 1.0.0
	 *
	 * @return Form|null Parent form, or null if none set.
	 */
	public function get_form() {
		if ( empty( $this->form_id ) ) {
			return null;
		}

		return $this->manager->get_parent_manager( 'forms' )->get( $this->form_id );
	}

	/**
	 * Returns all elements that belong to the form.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return awsmug\Torro_Forms\DB_Objects\Elements\Element_Collection List of elements.
	 */
	public function get_elements( $args = array() ) {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'elements' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'       => -1,
				'container_id' => $this->id,
			)
		);

		return $this->manager->get_child_manager( 'elements' )->query( $args );
	}

	/**
	 * Deletes the model from the database.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function delete() {
		$elements = $this->get_elements();
		foreach ( $elements as $element ) {
			$element->delete();
		}

		return parent::delete();
	}

	/**
	 * Returns an array representation of the model.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param bool $include_meta Optional. Whether to include metadata for each model in the collection.
	 *                           Default true.
	 * @return array Array including all information for the model.
	 */
	public function to_json( $include_meta = true ) {
		$data = parent::to_json( $include_meta );

		// $submit_button_label = $this->get_form()->get_form_option( $form_id, 'submit_button_label', '' );
		/* translators: %s: HTML code for required indicator */
		$required_indicator_description = '<span aria-hidden="true">' . sprintf( __( 'Required fields are marked %s.', 'torro-forms' ), '<span class="torro-required-indicator">*</span>' ) . '</span>';

		/**
		 * Filters the required indicator description, which is displayed above or below each form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $required_indicator_description Indicator description HTML string. Default is a description hidden for screen reader users,
		 *                                               explaining the asterisk character to mark a required field.
		 * @param int    $form_id                        Current form ID
		 */
		$data['required_description'] = apply_filters( "{$this->manager->get_prefix()}required_indicator_description", $required_indicator_description, $this->form_id );

		return $data;
	}

	/**
	 * Duplicates the container including all of its contents.
	 *
	 * @since 1.0.0
	 *
	 * @param int $form_id New parent form ID to use for the container.
	 * @return Container|WP_Error New container object on success, error object on failure.
	 */
	public function duplicate( $form_id ) {
		$new_container = $this->manager->create();

		foreach ( $this->to_json() as $key => $value ) {
			if ( 'id' === $key ) {
				continue;
			}

			if ( 'form_id' === $key ) {
				$new_container->form_id = $form_id;
				continue;
			}

			$new_container->$key = $value;
		}

		$status = $new_container->sync_upstream();
		if ( is_wp_error( $status ) ) {
			return $status;
		}

		foreach ( $this->get_elements() as $element ) {
			$element->duplicate( $new_container->id );
		}

		return $new_container;
	}
}
