<?php
/**
 * Submissions list page class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Models_List_Page;
use Leaves_And_Love\Plugin_Lib\Components\Admin_Pages;

/**
 * Class representing the submissions list page in the admin.
 *
 * @since 1.0.0
 */
class Submissions_List_Page extends Models_List_Page {
	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string             $slug          Page slug.
	 * @param Admin_Pages        $manager       Admin page manager instance.
	 * @param Submission_Manager $model_manager Model manager instance.
	 */
	public function __construct( $slug, $manager, $model_manager ) {
		$this->list_table_class_name = Submissions_List_Table::class;

		$this->edit_page_slug = $manager->get_prefix() . 'edit_submission';

		$this->icon_url = 'dashicons-tag';

		parent::__construct( $slug, $manager, $model_manager );
	}
}
