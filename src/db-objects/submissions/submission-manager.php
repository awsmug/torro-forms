<?php
/**
 * Submission manager class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Manager;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Capability_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;

/**
 * Manager class for submissions.
 *
 * @since 1.0.0
 *
 * @method awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Capabilities capabilities()
 * @method awsmug\Torro_Forms\DB                                             db()
 * @method Leaves_And_Love\Plugin_Lib\Cache                                  cache()
 * @method Leaves_And_Love\Plugin_Lib\Error_Handler                          error_handler()
 */
class Submission_Manager extends Manager {
	use Capability_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait, Manager_With_Children_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                                                          $prefix   The instance prefix.
	 * @param array                                                           $services {
	 *     Array of service instances.
	 *
	 *     @type awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Capabilities $capabilities  The capabilities instance.
	 *     @type awsmug\Torro_Forms\DB                                             $db            The database instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Cache                                  $cache         The cache instance.
	 *     @type Leaves_And_Love\Plugin_Lib\Error_Handler                          $error_handler The error handler instance.
	 * }
	 * @param awsmug\Torro_Forms\Translations\Translations_Submission_Manager $translations Translations instance.
	 */
	public function __construct( $prefix, $services, $translations ) {
		$this->class_name                 = Submission::class;
		$this->collection_class_name      = Submission_Collection::class;
		$this->query_class_name           = Submission_Query::class;
		$this->rest_controller_class_name = REST_Submissions_Controller::class;

		$this->singular_slug = 'submission';
		$this->plural_slug   = 'submissions';

		$this->table_name  = $this->plural_slug;
		$this->cache_group = $this->plural_slug;

		$this->primary_property = 'id';

		parent::__construct( $prefix, $services, $translations );
	}

	/**
	 * Counts all existing models for this manager.
	 *
	 * If the manager supports statuses, individual counts for each status
	 * are returned as well.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param int $user_id Optional. If provided and the manager supports authors,
	 *                     only models by that user are counted. Default 0 (ignored).
	 * @return array Array of `$status => $count` pairs. In addition, the array
	 *               always includes a key called '_total', containing the overall
	 *               count. If the manager does not support statuses, the array
	 *               only contains the '_total' key.
	 */
	public function count( $user_id = 0 ) {
		$user_id = absint( $user_id );

		$cache_key = $this->plural_slug;
		if ( $user_id > 0 ) {
			$cache_key .= '-' . $user_id;
		}

		$counts = $this->cache()->get( $cache_key, 'counts' );
		if ( false !== $counts ) {
			return $counts;
		}

		$where = '';
		$where_args = array();
		if ( $user_id > 0 ) {
			$where = " WHERE user_id = %d";
			$where_args[] = $user_id;
		}

		$total = $this->db()->get_var( "SELECT COUNT( * ) FROM %{$this->table_name}% $where", $where_args );
		$counts = array( '_total' => $total );

		$this->cache()->set( $cache_key, $counts, 'counts' );

		return $counts;
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function add_database_table() {
		$this->db()->add_table( $this->table_name, array(
			"id int(11) unsigned NOT NULL auto_increment",
			"form_id bigint(20) unsigned NOT NULL",
			"user_id bigint(20) unsigned NOT NULL",
			"timestamp int(11) unsigned NOT NULL",
			"remote_addr char(15) NOT NULL",
			"cookie_key char(50) NOT NULL",
			"status char(50) NOT NULL default 'completed'",
			"PRIMARY KEY  (id)",
			"KEY form_id (form_id)",
			"KEY user_id (user_id)",
			"KEY status (status)",
			"KEY status_form_id (status,form_id)",
		) );
	}
}
