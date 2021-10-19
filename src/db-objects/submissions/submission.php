<?php
/**
 * Submission class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Containers\Container;
use WP_Error;

/**
 * Class representing a submission.
 *
 * @since 1.0.0
 *
 * @property int    $form_id
 * @property int    $user_id
 * @property int    $timestamp
 * @property string $remote_addr
 * @property string $user_key
 * @property string $status
 *
 * @property-read int $id
 */
class Submission extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Submission ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the form this submission applies to.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * User ID of the user who created this submission, if any.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * Timestamp of when the submission was created.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $timestamp = 0;

	/**
	 * IP address of the user who created this submission.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $remote_addr = '';

	/**
	 * Submission user key.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $user_key = '';

	/**
	 * Submission status identifier.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $status = 'completed';

	/**
	 * Internal submission value storage.
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	protected $values = null;

	/**
	 * Internal element value storage.
	 *
	 * @since 1.0.0
	 * @var array|null
	 */
	protected $element_values = null;

	/**
	 * Magic isset-er.
	 *
	 * Checks whether a property is set.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property Property to check for.
	 * @return bool True if the property is set, false otherwise.
	 */
	public function __isset( $property ) {
		if ( 'values' === $property ) {
			return true;
		}

		if ( preg_match( '/^element_([0-9]+)_([a-z_]+)_value$/U', $property, $matches ) ) {
			$values = $this->get_element_values_data();

			if ( isset( $values[ $matches[1] ] ) && isset( $values[ $matches[1] ][ $matches[2] ] ) ) {
				return true;
			}

			return false;
		}

		return parent::__isset( $property );
	}

	/**
	 * Magic getter.
	 *
	 * Returns a property value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property Property to get.
	 * @return mixed Property value, or null if property is not set.
	 */
	public function __get( $property ) {
		if ( 'values' === $property ) {
			if ( is_array( $this->values ) ) {
				return $this->values;
			}

			return $this->get_submission_values_data();
		}

		if ( preg_match( '/^element_([0-9]+)_([a-z_]+)_value$/U', $property, $matches ) ) {
			$values = $this->get_element_values_data();

			if ( isset( $values[ $matches[1] ] ) && isset( $values[ $matches[1] ][ $matches[2] ] ) ) {
				return $values[ $matches[1] ][ $matches[2] ];
			}

			return null;
		}

		return parent::__get( $property );
	}

	/**
	 * Magic setter.
	 *
	 * Sets a property value.
	 *
	 * @since 1.0.0
	 *
	 * @param string $property Property to set.
	 * @param mixed  $value    Property value.
	 */
	public function __set( $property, $value ) {
		if ( 'values' === $property ) {
			$this->set_submission_values_data( $value );
			return;
		}

		if ( preg_match( '/^element_([0-9]+)_([a-z_]+)_value$/U', $property, $matches ) ) {
			if ( ! isset( $this->values ) ) {
				$this->values = $this->get_submission_values_data();
			}

			$original_value = $value;

			$value = (array) $value;

			$indexes_to_remove = array();

			foreach ( $this->values as $index => $item ) {
				$item['field'] = ! empty( $item['field'] ) ? $item['field'] : '_main';

				if ( (int) $matches[1] !== (int) $item['element_id'] || $matches[2] !== $item['field'] ) {
					continue;
				}

				if ( ! empty( $value ) ) {
					$this->values[ $index ]['value'] = array_shift( $value );
				} else {
					$indexes_to_remove[] = $index;
				}
			}

			foreach ( $indexes_to_remove as $index_to_remove ) {
				unset( $this->values[ $index_to_remove ] );
			}

			foreach ( $value as $single_value ) {
				$this->values[] = array(
					'id'         => 0,
					'element_id' => (int) $matches[1],
					'field'      => $matches[2],
					'value'      => $single_value,
				);
			}

			$this->get_element_values_data();

			$this->element_values[ $matches[1] ][ $matches[2] ] = $original_value;
		}

		parent::__set( $property, $value );
	}

	/**
	 * Returns the parent form for the submission.
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
	 * Returns all submission values that belong to the submission.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Optional. Additional query arguments. Default empty array.
	 * @return Submission_Value_Collection List of submission values.
	 */
	public function get_submission_values( $args = array() ) {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'submission_values' )->get_collection( array(), 0, 'objects' );
		}

		$args = wp_parse_args(
			$args,
			array(
				'number'        => -1,
				'submission_id' => $this->id,
			)
		);

		return $this->manager->get_child_manager( 'submission_values' )->query( $args );
	}

	/**
	 * Synchronizes the model with the database by storing the currently pending values.
	 *
	 * If the model is new (i.e. does not have an ID yet), it will be inserted to the database.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_upstream() {
		$result = parent::sync_upstream();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( null !== $this->values ) {
			$manager = $this->manager->get_child_manager( 'submission_values' );

			$ids = array();
			foreach ( $this->values as $item ) {
				$submission_value = null;
				if ( ! empty( $item['id'] ) ) {
					$submission_value = $manager->get( $item['id'] );
					if ( $submission_value && $this->id !== $submission_value->submission_id ) {
						continue;
					}
				}

				if ( ! $submission_value ) {
					$submission_value = $manager->create();
				}

				$submission_value->submission_id = $this->id;
				$submission_value->field         = $item['field'];
				$submission_value->value         = $item['value'];
				if ( ! empty( $item['element_id'] ) ) {
					$submission_value->element_id = $item['element_id'];
				}

				$submission_value->sync_upstream();

				if ( empty( $submission_value->id ) ) {
					continue;
				}

				$ids[] = $submission_value->id;
			}

			if ( ! empty( $ids ) ) {
				$old_values = $this->get_submission_values(
					array(
						'exclude' => $ids,
					)
				);
				foreach ( $old_values as $old_value ) {
					$old_value->delete();
				}
			}

			$this->values = null;
		}

		return $result;
	}

	/**
	 * Synchronizes the model with the database by fetching the currently stored values.
	 *
	 * If the model contains unsynchronized changes, these will be overridden. This method basically allows
	 * to reset the model to the values stored in the database.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function sync_downstream() {
		$result = parent::sync_downstream();

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$this->values = null;

		return $result;
	}

	/**
	 * Deletes the model from the database.
	 *
	 * @since 1.0.0
	 *
	 * @return true|WP_Error True on success, or an error object on failure.
	 */
	public function delete() {
		$submission_values = $this->get_submission_values();
		foreach ( $submission_values as $submission_value ) {
			$submission_value->delete();
		}

		return parent::delete();
	}

	/**
	 * Formats the submission date and time.
	 *
	 * @since 1.0.0
	 *
	 * @param string $format Datetime format string. Will be localized.
	 * @param bool   $gmt    Optional. Whether to return as GMT. Default true.
	 * @return string Formatted date and time.
	 */
	public function format_datetime( $format, $gmt = true ) {
		$timestamp = $this->timestamp;
		if ( ! $gmt ) {
			$timestamp = strtotime( get_date_from_gmt( date( 'Y-m-d H:i:s', $timestamp ) ) );
		}

		return date_i18n( $format, $timestamp );
	}

	/**
	 * Sets the current container for the submission.
	 *
	 * The form ID of the container must match the submission's form ID.
	 *
	 * @since 1.0.0
	 *
	 * @param Container|null $container Container, or null if the current container data should be unset.
	 */
	public function set_current_container( $container ) {
		if ( null === $container ) {
			$this->__set( 'current_container_id', null );
			return true;
		}

		if ( $container->form_id !== $this->form_id ) {
			return false;
		}

		$this->__set( 'current_container_id', $container->id );
		return true;
	}

	/**
	 * Returns the current container for the submission.
	 *
	 * @since 1.0.0
	 *
	 * @return Container|null Current container, or null on failure.
	 */
	public function get_current_container() {
		$container_id = (int) $this->__get( 'current_container_id' );

		if ( ! empty( $container_id ) ) {
			$container = $this->manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->get( $container_id );
		} else {
			$form = $this->get_form();
			if ( ! $form ) {
				return null;
			}

			$container_collection = $form->get_containers(
				array(
					'number'        => 1,
					'orderby'       => array(
						'sort' => 'ASC',
					),
					'no_found_rows' => true,
				)
			);
			if ( 1 > count( $container_collection ) ) {
				return null;
			}

			$container = $container_collection[0];
			$this->set_current_container( $container );
		}

		return $container;
	}

	/**
	 * Returns the next container for the submission, if there is one.
	 *
	 * @since 1.0.0
	 *
	 * @return Container|null Next container, or null if there is none.
	 */
	public function get_next_container() {
		$container = $this->get_current_container();
		if ( ! $container ) {
			return null;
		}

		$form = $this->get_form();
		if ( ! $form ) {
			return null;
		}

		$container_collection = $form->get_containers(
			array(
				'number'        => 1,
				'sort'          => array(
					'greater_than' => $container->sort,
				),
				'orderby'       => array(
					'sort' => 'ASC',
				),
				'no_found_rows' => true,
			)
		);
		if ( 1 > count( $container_collection ) ) {
			return null;
		}

		return $container_collection[0];
	}

	/**
	 * Returns the previous container for the submission, if there is one.
	 *
	 * @since 1.0.0
	 *
	 * @return Container|null Previous container, or null if there is none.
	 */
	public function get_previous_container() {
		$container = $this->get_current_container();
		if ( ! $container ) {
			return null;
		}

		$form = $this->get_form();
		if ( ! $form ) {
			return null;
		}

		$container_collection = $form->get_containers(
			array(
				'number'        => 1,
				'sort'          => array(
					'lower_than' => $container->sort,
				),
				'orderby'       => array(
					'sort' => 'DESC',
				),
				'no_found_rows' => true,
			)
		);
		if ( 1 > count( $container_collection ) ) {
			return null;
		}

		return $container_collection[0];
	}

	/**
	 * Gets all element values set for the submission.
	 *
	 * The returned element values data array is an multi-dimensional associative array
	 * where the keys are element IDs and their inner keys field slugs belonging to the element
	 * with the actual value for the element and field combination as value.
	 *
	 * @since 1.0.0
	 *
	 * @return array Element values data set for the submission.
	 */
	public function get_element_values_data() {
		if ( ! $this->primary_property_value() ) {
			return array();
		}

		if ( ! isset( $this->element_values ) ) {
			$this->element_values = $this->manager->get_element_values_data_for_submission( $this );
		}

		return $this->element_values;
	}

	/**
	 * Adds an error to the submission.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $element_id Element ID to add the error for.
	 * @param string $code       Error code.
	 * @param string $message    Error message.
	 * @return bool True on success, false on failure.
	 */
	public function add_error( $element_id, $code, $message ) {
		if ( ! array_key_exists( 'errors', $this->pending_meta ) ) {
			if ( $this->primary_property_value() ) {
				$this->pending_meta['errors'] = $this->manager->get_meta( $this->primary_property_value(), 'errors', true );
			} else {
				$this->pending_meta['errors'] = array();
			}
		}

		if ( ! is_array( $this->pending_meta['errors'] ) ) {
			$this->pending_meta['errors'] = array();
		}

		if ( ! isset( $this->pending_meta['errors'][ $element_id ] ) || ! is_array( $this->pending_meta['errors'][ $element_id ] ) ) {
			$this->pending_meta['errors'][ $element_id ] = array();
		}

		$this->pending_meta['errors'][ $element_id ][ $code ] = $message;

		return true;
	}

	/**
	 * Removes an error from the submission.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $element_id Element ID to remove an error for.
	 * @param string $code       Error code to remove.
	 * @return bool True on success, false on failure.
	 */
	public function remove_error( $element_id, $code ) {
		if ( ! array_key_exists( 'errors', $this->pending_meta ) ) {
			if ( $this->primary_property_value() ) {
				$this->pending_meta['errors'] = $this->manager->get_meta( $this->primary_property_value(), 'errors', true );
			} else {
				$this->pending_meta['errors'] = array();
			}
		}

		if ( ! is_array( $this->pending_meta['errors'] ) ) {
			return false;
		}

		if ( ! is_array( $this->pending_meta['errors'][ $element_id ] ) ) {
			return false;
		}

		if ( ! isset( $this->pending_meta['errors'][ $element_id ][ $code ] ) ) {
			return false;
		}

		unset( $this->pending_meta['errors'][ $element_id ][ $code ] );

		if ( empty( $this->pending_meta['errors'][ $element_id ] ) ) {
			unset( $this->pending_meta['errors'][ $element_id ] );
		}

		if ( empty( $this->pending_meta['errors'] ) ) {
			$this->pending_meta['errors'] = null;
		}

		return true;
	}

	/**
	 * Gets all errors, for the entire submission or a specific element.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $element_id Optional. If an element ID is given, only errors for that element are returned.
	 * @return array If $element_id is given, the array of `$code => $message` pairs is returned. Otherwise the array
	 *               of `$element_id => $errors` pairs is returned.
	 */
	public function get_errors( $element_id = null ) {
		if ( ! array_key_exists( 'errors', $this->pending_meta ) ) {
			if ( ! $this->primary_property_value() ) {
				return array();
			}

			$errors = $this->manager->get_meta( $this->primary_property_value(), 'errors', true );
		} else {
			$errors = $this->pending_meta['errors'];
		}

		if ( ! is_array( $errors ) ) {
			return array();
		}

		if ( null !== $element_id ) {
			if ( empty( $errors[ $element_id ] ) ) {
				return array();
			}

			return $errors[ $element_id ];
		}

		return $errors;
	}

	/**
	 * Checks whether there are errors, for the entire submission or a specific element.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $element_id Optional. If an element ID is given, only errors for that element are returned.
	 * @return bool True if there are errors, false otherwise.
	 */
	public function has_errors( $element_id = null ) {
		$errors = $this->get_errors( $element_id );

		return ! empty( $errors );
	}

	/**
	 * Resets all errors, for the entire submission or a specific element.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null $element_id Optional. If an element ID is given, only errors for that element are reset.
	 * @return bool True on success, false on failure.
	 */
	public function reset_errors( $element_id = null ) {
		if ( ! array_key_exists( 'errors', $this->pending_meta ) ) {
			if ( ! $this->primary_property_value() ) {
				return false;
			}

			$this->pending_meta['errors'] = $this->manager->get_meta( $this->primary_property_value(), 'errors', true );
		}

		if ( ! is_array( $this->pending_meta['errors'] ) ) {
			if ( null !== $this->pending_meta['errors'] ) {
				unset( $this->pending_meta['errors'] );
			}

			return false;
		}

		if ( null !== $element_id ) {
			if ( ! is_array( $this->pending_meta['errors'] ) || empty( $this->pending_meta['errors'][ $element_id ] ) ) {
				return false;
			}

			unset( $this->pending_meta['errors'][ $element_id ] );

			return true;
		}

		$this->pending_meta['errors'] = null;

		return true;
	}

	/**
	 * Gets submission values data for the submission, to be used with the field manager.
	 *
	 * @since 1.0.0
	 *
	 * @return array Submission values data.
	 */
	protected function get_submission_values_data() {
		$data = array();

		foreach ( $this->get_submission_values() as $submission_value ) {
			$data[] = array(
				'id'         => $submission_value->id,
				'element_id' => $submission_value->element_id,
				'field'      => $submission_value->field,
				'value'      => $submission_value->value,
			);
		}

		return $data;
	}

	/**
	 * Sets submission values data for the submission, to be used with the field manager.
	 *
	 * @since 1.0.0
	 *
	 * @param array $value Submission values data.
	 */
	protected function set_submission_values_data( $value ) {
		if ( ! is_array( $value ) ) {
			return;
		}

		$data = array();
		foreach ( $value as $item ) {
			$data[] = array(
				'id'         => ! empty( $item['id'] ) ? (int) $item['id'] : 0,
				'element_id' => ! empty( $item['element_id'] ) ? (int) $item['element_id'] : 0,
				'field'      => ! empty( $item['field'] ) ? sanitize_key( $item['field'] ) : '',
				'value'      => ! empty( $item['value'] ) ? $item['value'] : '',
			);
		}

		$this->values = $data;
	}

	/**
	 * Returns a list of internal properties that are not publicly accessible.
	 *
	 * When overriding this method, always make sure to merge with the parent result.
	 *
	 * @since 1.0.0
	 *
	 * @return array Property blacklist.
	 */
	protected function get_blacklist() {
		$blacklist = parent::get_blacklist();

		$blacklist[] = 'values';
		$blacklist[] = 'element_values';

		return $blacklist;
	}
}
