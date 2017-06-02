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
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\Meta_Manager_Trait;
use Leaves_And_Love\Plugin_Lib\DB_Objects\Traits\REST_API_Manager_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;
use awsmug\Torro_Forms\Translations\Translations_Submission_Manager;
use awsmug\Torro_Forms\DB;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;

/**
 * Manager class for submissions.
 *
 * @since 1.0.0
 *
 * @method Submission_Capabilities capabilities()
 * @method DB                      db()
 * @method Cache                   cache()
 * @method Meta                    meta()
 * @method Error_Handler           error_handler()
 */
class Submission_Manager extends Manager {
	use Capability_Manager_Trait, Meta_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait, Manager_With_Children_Trait;

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string                          $prefix       The instance prefix.
	 * @param array                           $services     {
	 *     Array of service instances.
	 *
	 *     @type Submission_Capabilities $capabilities  The capabilities instance.
	 *     @type DB                      $db            The database instance.
	 *     @type Cache                   $cache         The cache instance.
	 *     @type Meta                    $meta          The meta instance.
	 *     @type Error_Handler           $error_handler The error handler instance.
	 * }
	 * @param Translations_Submission_Manager $translations Translations instance.
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
		$this->meta_type   = $this->singular_slug;

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

		$results = $this->db()->get_results( "SELECT status, COUNT( * ) AS num_models FROM %{$this->table_name}% $where GROUP BY status", $where_args );

		$total = 0;
		$counts = array_fill_keys( array( 'completed', 'progressing' ), 0 );
		foreach ( $results as $row ) {
			$counts[ $row->status ] = $row->num_models;
			$total += $row->num_models;
		}

		$counts['_total'] = $total;

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

		$this->add_meta_database_table();
	}

	/**
	 * Sets some automatic properties on a submission if they aren't set already.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param null       $ret        Return value from the filter.
	 * @param Submission $submission The submission to modify.
	 * @return null The unmodified pre-filter value.
	 */
	protected function maybe_set_automatic_properties( $ret, $submission ) {
		if ( empty( $submission->timestamp ) ) {
			$submission->timestamp = current_time( 'timestamp', 1 );
		}

		return $ret;
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 * @access protected
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$prefix        = $this->get_prefix();
		$singular_slug = $this->get_singular_slug();

		$this->filters[] = array(
			'name'     => "{$prefix}pre_add_{$singular_slug}",
			'callback' => array( $this, 'maybe_set_automatic_properties' ),
			'priority' => 100,
			'num_args' => 2,
		);
	}
}
