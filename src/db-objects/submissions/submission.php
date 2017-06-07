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
 * @property string $cookie_key
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
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the form this submission applies to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * User ID of the user who created this submission, if any.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * Timestamp of when the submission was created.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $timestamp = 0;

	/**
	 * IP address of the user who created this submission.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $remote_addr = '';

	/**
	 * Submission cookie key.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $cookie_key = '';

	/**
	 * Submission status identifier.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $status = 'completed';

	/**
	 * Constructor.
	 *
	 * Sets the ID and fetches relevant data.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Submission_Manager $manager The manager instance for the model.
	 * @param object|null        $db_obj  Optional. The database object or null for a new instance.
	 */
	public function __construct( $manager, $db_obj = null ) {
		if ( is_user_logged_in() ) {
			$this->user_id = get_current_user_id();
		}

		if ( ! empty( $_SERVER['REMOTE_ADDR'] ) && preg_match( '/^(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/', $_SERVER['REMOTE_ADDR'] ) ) {
			$this->remote_addr = $_SERVER['REMOTE_ADDR'];
		}

		parent::__construct( $manager, $db_obj );
	}

	/**
	 * Returns the parent form for the submission.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @return Submission_Value_Collection List of submission values.
	 */
	public function get_submission_values() {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'submission_values' )->get_collection( array(), 0, 'objects' );
		}

		return $this->manager->get_child_manager( 'submission_values' )->query( array(
			'submission_id' => $this->id,
		) );
	}

	/**
	 * Returns all submission values that belong to the submission.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $element_id ID of the element to get submission values for.
	 * @return Submission_Value_Collection List of submission values.
	 */
	public function get_submission_values_for_element( $element_id ) {
		if ( empty( $this->id ) ) {
			return $this->manager->get_child_manager( 'submission_values' )->get_collection( array(), 0, 'objects' );
		}

		return $this->manager->get_child_manager( 'submission_values' )->query( array(
			'submission_id' => $this->id,
			'element_id'    => absint( $element_id ),
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
		$submission_values = $this->get_submission_values();
		foreach ( $submission_values as $submission_value ) {
			$submission_value->delete();
		}

		return parent::delete();
	}

	/**
	 * Sets the current container for the submission.
	 *
	 * The form ID of the container must match the submission's form ID.
	 *
	 * @since 1.0.0
	 * @access public
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
	 * @access public
	 *
	 * @return Container|null Current container, or null on failure.
	 */
	public function get_current_container() {
		$container_id = (int) $this->__get( 'current_container_id' );

		if ( ! empty( $container_id ) ) {
			$container = $container_collection = $this->manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->get( $container_id );
		} else {
			$container_collection = $this->manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->query( array(
				'number'        => 1,
				'form_id'       => $this->form_id,
				'orderby'       => array( 'sort' => 'ASC' ),
				'no_found_rows' => true,
			) );
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
	 * @access public
	 *
	 * @return Container|null Next container, or null if there is none.
	 */
	public function get_next_container() {
		$container = $this->get_current_container();
		if ( ! $container ) {
			return null;
		}

		$container_collection = $this->manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->query( array(
			'number'        => 1,
			'form_id'       => $this->form_id,
			'sort'          => array( 'greater_than' => $container->sort ),
			'orderby'       => array( 'sort' => 'ASC' ),
			'no_found_rows' => true,
		) );
		if ( 1 > count( $container_collection ) ) {
			return null;
		}

		return $container_collection[0];
	}

	/**
	 * Returns the previous container for the submission, if there is one.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return Container|null Previous container, or null if there is none.
	 */
	public function get_previous_container() {
		$container = $this->get_current_container();
		if ( ! $container ) {
			return null;
		}

		$container_collection = $this->manager->get_parent_manager( 'forms' )->get_child_manager( 'containers' )->query( array(
			'number'        => 1,
			'form_id'       => $this->form_id,
			'sort'          => array( 'lower_than' => $container->sort ),
			'orderby'       => array( 'sort' => 'DESC' ),
			'no_found_rows' => true,
		) );
		if ( 1 > count( $container_collection ) ) {
			return null;
		}

		return $container_collection[0];
	}

	/**
	 * Adds an error to the submission.
	 *
	 * @since 1.0.0
	 * @access public
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

		if ( ! is_array( $this->pending_meta['errors'][ $element_id ] ) ) {
			$this->pending_meta['errors'][ $element_id ] = array();
		}

		$this->pending_meta['errors'][ $element_id ][ $code ] = $message;

		return true;
	}

	/**
	 * Removes an error from the submission.
	 *
	 * @since 1.0.0
	 * @access public
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

		if ( ! is_array( $this->pending_meta['errors'][ $element_id ][ $code ] ) ) {
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
	 * @access public
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

		if ( ! empty( $element_id ) ) {
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
	 * @access public
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
	 * @access public
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

		if ( ! empty( $element_id ) ) {
			if ( ! is_array( $this->pending_meta['errors'] ) || empty( $this->pending_meta['errors'][ $element_id ] ) ) {
				return false;
			}

			unset( $this->pending_meta['errors'][ $element_id ] );

			return true;
		}

		$this->pending_meta['errors'] = null;

		return true;
	}
}
