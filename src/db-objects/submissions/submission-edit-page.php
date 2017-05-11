<?php
/**
 * Submission edit page class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Model_Edit_Page;

/**
 * Class representing the submission edit page in the admin.
 *
 * @since 1.0.0
 */
class Submission_Edit_Page extends Model_Edit_Page {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                            $slug          Page slug.
	 * @param Leaves_And_Love\Plugin_Lib\Components\Admin_Pages $manager       Admin page manager instance.
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager     $model_manager Model manager instance.
	 */
	public function __construct( $slug, $manager, $model_manager ) {
		$this->list_page_slug = $manager->get_prefix() . 'edit_submissions';

		parent::__construct( $slug, $manager, $model_manager );
	}

	/**
	 * Handles a request to the page.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function handle_request() {
		global $parent_file;

		$parent_file = 'inp_list_invoices';

		parent::handle_request();
	}

	/**
	 * Adds tabs, sections and fields to the submission edit page.
	 *
	 * This method should call the methods `add_tabs()`, `add_section()` and
	 * `add_field()` to populate the page.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_page_content() {

	}
}
