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

		parent::__construct( $manager, $db_obj );
	}
}
