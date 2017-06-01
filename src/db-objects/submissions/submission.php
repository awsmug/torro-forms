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
	 * @return Container_Collection List of submission values.
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
}
