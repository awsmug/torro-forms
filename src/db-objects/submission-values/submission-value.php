<?php
/**
 * Submission value class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submission_Values;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Class representing a submission value.
 *
 * @since 1.0.0
 *
 * @property int    $submission_id
 * @property int    $element_id
 * @property string $field
 * @property string $value
 *
 * @property-read int $id
 */
class Submission_Value extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Submission value ID.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the submission this submission value is part of.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $submission_id = 0;

	/**
	 * Element ID this submission value applies to.
	 *
	 * @since 1.0.0
	 * @var int
	 */
	protected $element_id = 0;

	/**
	 * Element field this submission value is for.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $field = '';

	/**
	 * Submission value.
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $value = '';

	/**
	 * Returns the parent submission for the submission value.
	 *
	 * @since 1.0.0
	 *
	 * @return Submission|null Parent submission, or null if none set.
	 */
	public function get_submission() {
		if ( empty( $this->submission_id ) ) {
			return null;
		}

		return $this->manager->get_parent_manager( 'submissions' )->get( $this->submission_id );
	}
}
