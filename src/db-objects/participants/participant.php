<?php
/**
 * Participant class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Participants;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Sitewide_Model_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;

/**
 * Class representing a participant.
 *
 * @since 1.0.0
 *
 * @property int $form_id
 * @property int $user_id
 *
 * @property-read int $id
 */
class Participant extends Model {
	use Sitewide_Model_Trait;

	/**
	 * Participant ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $id = 0;

	/**
	 * ID of the form this participant responded to.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $form_id = 0;

	/**
	 * Participant user ID.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var int
	 */
	protected $user_id = 0;

	/**
	 * Returns the parent form for the participant.
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
}
