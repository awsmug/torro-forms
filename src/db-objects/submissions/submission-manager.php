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
use Leaves_And_Love\Plugin_Lib\Fixes;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Parents_Trait;
use awsmug\Torro_Forms\DB_Objects\Manager_With_Children_Trait;
use awsmug\Torro_Forms\Translations\Translations_Submission_Manager;
use awsmug\Torro_Forms\DB;
use awsmug\Torro_Forms\Components\Submission_Export_Handler;
use Leaves_And_Love\Plugin_Lib\Cache;
use Leaves_And_Love\Plugin_Lib\Meta;
use Leaves_And_Love\Plugin_Lib\Error_Handler;
use WP_Error;

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
 * @method Submission              create()
 */
class Submission_Manager extends Manager {
	use Capability_Manager_Trait, Meta_Manager_Trait, REST_API_Manager_Trait, Manager_With_Parents_Trait, Manager_With_Children_Trait;

	/**
	 * Submission export handler instance.
	 *
	 * @since 1.0.0
	 * @var Submission_Export_Handler
	 */
	protected $export_handler;

	/**
	 * Temporary storage for all element values loaded for submissions.
	 *
	 * @since 1.0.0
	 * @var array
	 */
	protected $element_values = array();

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
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

		$this->export_handler = new Submission_Export_Handler( $prefix, $this );

		parent::__construct( $prefix, $services, $translations );

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			$command = new CLI_Submissions_Command( $this );
			$command->add( str_replace( '_', ' ', $this->prefix ) . str_replace( '_', '-', $this->singular_slug ) );
		}
	}

	/**
	 * Returns the submission export handler.
	 *
	 * @since 1.0.0
	 *
	 * @return Submission_Export_Handler Submission export handler instance.
	 */
	public function export_handler() {
		return $this->export_handler();
	}

	/**
	 * Counts all existing models for this manager.
	 *
	 * If the manager supports statuses, individual counts for each status
	 * are returned as well.
	 *
	 * @since 1.0.0
	 *
	 * @param int $user_id Optional. If provided and the manager supports authors,
	 *                     only models by that user are counted. Default 0 (ignored).
	 * @param int $form_id Optional. If provided only submissions for that form are
	 *                     counted. Default 0 (ignored).
	 * @return array Array of `$status => $count` pairs. In addition, the array
	 *               always includes a key called '_total', containing the overall
	 *               count. If the manager does not support statuses, the array
	 *               only contains the '_total' key.
	 */
	public function count( $user_id = 0, $form_id = 0 ) {
		$user_id = absint( $user_id );
		$form_id = absint( $form_id );

		$cache_key = $this->plural_slug;
		if ( $user_id > 0 ) {
			$cache_key .= '-' . $user_id;
		}
		if ( $form_id > 0 ) {
			$cache_key .= '-' . $form_id;
		}

		$counts = $this->cache()->get( $cache_key, 'counts' );
		if ( false !== $counts ) {
			return $counts;
		}

		$where      = array();
		$where_args = array();
		if ( $user_id > 0 ) {
			$where[]      = 'user_id = %d';
			$where_args[] = $user_id;
		}
		if ( $form_id > 0 ) {
			$where[]      = 'form_id = %d';
			$where_args[] = $form_id;
		}

		if ( ! empty( $where ) ) {
			$where = 'WHERE ' . implode( ' AND ', $where );
		} else {
			$where = '';
		}

		$results = $this->db()->get_results( "SELECT status, COUNT( * ) AS num_models FROM %{$this->table_name}% $where GROUP BY status", $where_args );

		$total  = 0;
		$counts = array_fill_keys( array( 'completed', 'progressing' ), 0 );
		foreach ( $results as $row ) {
			$counts[ $row->status ] = $row->num_models;
			$total                 += $row->num_models;
		}

		$counts['_total'] = $total;

		$this->cache()->set( $cache_key, $counts, 'counts' );

		return $counts;
	}

	/**
	 * Gets all element values set for a submission.
	 *
	 * This method queries all submission values that belong to the submission and parses
	 * them into an element values data array. It is an multi-dimensional associative array
	 * where the keys are element IDs and their inner keys field slugs belonging to the element
	 * with the actual value for the element and field combination as value.
	 *
	 * The querying and parsing logic will only be executed once per submission and request,
	 * for performance reasons. On subsequent calls, the non-persistently cached value will be
	 * returned.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission object to get element values for.
	 * @param bool       $force      Optional. Whether to force querying and parsing. Default false.
	 * @return array Element values data set for the submission.
	 */
	public function get_element_values_data_for_submission( $submission, $force = false ) {
		if ( ! isset( $this->element_values[ $submission->id ] ) || $force ) {
			$this->element_values[ $submission->id ] = array();

			foreach ( $submission->get_submission_values() as $submission_value ) {
				$element_id = $submission_value->element_id;
				$field      = ! empty( $submission_value->field ) ? $submission_value->field : '_main';

				if ( ! isset( $this->element_values[ $submission->id ][ $element_id ] ) ) {
					$this->element_values[ $submission->id ][ $element_id ] = array();
				}

				if ( ! empty( $this->element_values[ $submission->id ][ $element_id ][ $field ] ) ) {
					$this->element_values[ $submission->id ][ $element_id ][ $field ]   = (array) $this->element_values[ $submission->id ][ $element_id ][ $field ];
					$this->element_values[ $submission->id ][ $element_id ][ $field ][] = $submission_value->value;
				} else {
					$this->element_values[ $submission->id ][ $element_id ][ $field ] = $submission_value->value;
				}
			}
		}

		return $this->element_values[ $submission->id ];
	}

	/**
	 * Adds the database table.
	 *
	 * @since 1.0.0
	 */
	protected function add_database_table() {
		$this->db()->add_table(
			$this->table_name,
			array(
				'id int(11) unsigned NOT NULL auto_increment',
				'form_id bigint(20) unsigned NOT NULL',
				'user_id bigint(20) unsigned NOT NULL',
				'timestamp int(11) unsigned NOT NULL',
				'remote_addr char(50) NOT NULL',
				'user_key char(50) NOT NULL',
				"status char(50) NOT NULL default 'completed'",
				'PRIMARY KEY  (id)',
				'KEY form_id (form_id)',
				'KEY user_id (user_id)',
				'KEY status (status)',
				'KEY status_form_id (status,form_id)',
			)
		);

		$this->add_meta_database_table();
	}

	/**
	 * Cleans the count cache for a model if relevant changes have been applied.
	 *
	 * @since 1.0.0
	 *
	 * @param object|null $new_db_object The new raw database object, or null if deleted.
	 * @param object|null $old_db_object The old raw database object, or null if added.
	 */
	protected function maybe_clean_count_cache( $new_db_object, $old_db_object ) {
		$delete_user_count = false;
		$delete_form_count = false;

		if ( null === $new_db_object || null === $old_db_object || $new_db_object->status !== $old_db_object->status ) {
			$this->cache()->delete( $this->plural_slug, 'counts' );

			$delete_user_count = true;
			$delete_form_count = true;
		} elseif ( null !== $new_db_object && null !== $old_db_object ) {
			if ( $new_db_object->user_id !== $old_db_object->user_id ) {
				$delete_user_count = true;
			}
			if ( $new_db_object->form_id !== $old_db_object->form_id ) {
				$delete_form_count = true;
			}
		}

		$user_ids = array();
		if ( $delete_user_count ) {
			if ( null !== $new_db_object ) {
				$user_ids[] = absint( $new_db_object->user_id );
			}
			if ( null !== $old_db_object && ! in_array( absint( $old_db_object->user_id ), $user_ids, true ) ) {
				$user_ids[] = absint( $old_db_object->user_id );
			}
		}

		$form_ids = array();
		if ( $delete_form_count ) {
			if ( null !== $new_db_object ) {
				$form_ids[] = absint( $new_db_object->form_id );
			}
			if ( null !== $old_db_object && ! in_array( absint( $old_db_object->form_id ), $form_ids, true ) ) {
				$form_ids[] = absint( $old_db_object->form_id );
			}
		}

		$extra_delete_keys = array();
		foreach ( $user_ids as $user_id ) {
			$extra_delete_keys[] = $this->plural_slug . '-' . $user_id;

			foreach ( $form_ids as $form_id ) {
				$extra_delete_keys[] = $this->plural_slug . '-' . $user_id . '-' . $form_id;
			}
		}
		foreach ( $form_ids as $form_id ) {
			$extra_delete_keys[] = $this->plural_slug . '-' . $form_id;
		}

		foreach ( $extra_delete_keys as $extra_delete_key ) {
			$this->cache()->delete( $extra_delete_key, 'counts' );
		}
	}


	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	protected function setup_hooks() {
		parent::setup_hooks();

		$this->actions[] = array(
			'name'     => 'admin_init',
			'callback' => array( $this, 'schedule_cron_task' ),
			'priority' => 10,
			'num_args' => 0,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}cron_maybe_delete_submissions",
			'callback' => array( $this, 'maybe_delete_submissions' ),
			'priority' => 10,
			'num_args' => 0,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}create_new_submission",
			'callback' => array( $this, 'set_initial_submission_data' ),
			'priority' => 1,
			'num_args' => 2,
		);
		$this->actions[] = array(
			'name'     => "admin_action_{$this->export_handler->get_export_action_name()}",
			'callback' => array( $this->export_handler, 'handle_export_action' ),
			'priority' => 1,
			'num_args' => 0,
		);
		$this->actions[] = array(
			'name'     => "{$this->get_prefix()}after_submissions_list",
			'callback' => array( $this->export_handler, 'render_export_form' ),
			'priority' => 10,
			'num_args' => 0,
		);
	}

	/**
	 * Schedules the cron task for deleting incomplete submissions.
	 *
	 * @since 1.0.0
	 */
	public function schedule_cron_task() {
		$settings = $this->get_parent_manager( 'forms' )->options()->get( 'general_settings', array() );

		if ( isset( $settings['delete_submissions'] ) && $settings['delete_submissions'] ) {
			if ( ! wp_next_scheduled( "{$this->get_prefix()}cron_maybe_delete_submissions" ) ) {
				wp_schedule_event( time(), 'twicedaily', "{$this->get_prefix()}cron_maybe_delete_submissions" );
			}
		}
	}

	/**
	 * Clears the cron task for deleting incomplete submissions.
	 *
	 * @since 1.0.0
	 */
	public function clear_cron_task() {
		$timestamp = wp_next_scheduled( "{$this->get_prefix()}cron_maybe_delete_submissions" );
		if ( $timestamp ) {
			wp_unschedule_event( $timestamp, "{$this->get_prefix()}cron_maybe_delete_submissions" );
		}
	}

	/**
	 * Deletes submissions that have been 'progressing' for too long, if enabled.
	 *
	 * This is executed twice daily via cron.
	 *
	 * @since 1.0.0
	 */
	public function maybe_delete_submissions() {
		$settings = $this->get_parent_manager( 'forms' )->options()->get( 'general_settings', array() );

		$delete = isset( $settings['delete_submissions'] ) ? (bool) $settings['delete_submissions'] : false;
		if ( ! $delete ) {
			return;
		}

		$delete_days = ! empty( $settings['delete_submissions_days'] ) ? (int) $settings['delete_submissions_days'] : 1;

		/**
		 * Filters the limit for the query detecting incomplete submissions to delete.
		 *
		 * This can be used to adjust the value, depending on more or less server power.
		 *
		 * @since 1.0.0
		 *
		 * @param int $limit Limit for the query. Default is 50.
		 */
		$limit = apply_filters( "{$this->get_prefix()}delete_submissions_query_limit", 50 );

		$submissions = $this->query(
			array(
				'number'    => $limit,
				'status'    => 'progressing',
				'timestamp' => array(
					'lower_than' => current_time( 'timestamp', true ) - $delete_days * DAY_IN_SECONDS,
				),
			)
		);
		foreach ( $submissions as $submission ) {
			$submission->delete();
		}
	}

	/**
	 * Sets the initial data for a new submission.
	 *
	 * It also stores the user key in session storage for the most basic user identification.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission object.
	 * @param Form       $form       Form object.
	 */
	public function set_initial_submission_data( $submission, $form ) {
		$submission->form_id   = $form->id;
		$submission->status    = 'progressing';
		$submission->timestamp = current_time( 'timestamp', true );

		if ( is_user_logged_in() ) {
			$submission->user_id = get_current_user_id();
		}

		if ( filter_has_var( INPUT_COOKIE, 'torro_identity' ) ) {
			$submission->user_key = esc_attr( filter_input( INPUT_COOKIE, 'torro_identity' ) );
		} elseif ( isset( $_SESSION ) && ! empty( $_SESSION['torro_identity'] ) ) {
			$submission->user_key = esc_attr( wp_unslash( $_SESSION['torro_identity'] ) );
		} else {
			$remote_addr          = Fixes::php_filter_input( INPUT_SERVER, 'REMOTE_ADDR' );
			$base_string          = ! empty( $remote_addr ) ? $this->anonymize_ip_address( $remote_addr ) . microtime() : microtime();
			$submission->user_key = md5( $base_string );
		}

		if ( ! isset( $_SESSION ) ) {
			if ( headers_sent() ) {
				return;
			}

			session_start();
		}

		$_SESSION['torro_identity'] = $submission->user_key;
	}

	/**
	 * Renders a status select field.
	 *
	 * @since 1.0.0
	 *
	 * @param int|null   $id         Current submission ID, or null if new submission.
	 * @param Submission $submission Current submission object.
	 */
	public function render_status_select( $id, $submission ) {
		$current_status = $submission->status;

		$timestamp = ! empty( $submission->timestamp ) ? $submission->timestamp : current_time( 'timestamp', 'mysql' );

		?>
		<div class="misc-pub-section">
			<div id="date-information">
				<?php esc_html_e( 'Date:', 'torro-forms' ); ?>
				<?php echo esc_html( date_i18n( get_option( 'date_format' ), $timestamp ) ); ?>
			</div>
		</div>
		<div class="misc-pub-section">
			<div id="post-status-select">
				<label for="post-status"><?php echo esc_html( $this->get_message( 'edit_page_status_label' ) ); ?></label>
				<select id="post-status" name="status">
					<option value="completed"<?php selected( $current_status, 'completed' ); ?>><?php echo esc_html_x( 'Completed', 'submission status label', 'torro-forms' ); ?></option>
					<option value="progressing"<?php selected( $current_status, 'progressing' ); ?>><?php echo esc_html_x( 'In Progress', 'submission status label', 'torro-forms' ); ?></option>
				</select>
			</div>
		</div>
		<?php
	}

	/**
	 * Anonymizes an IP address.
	 *
	 * Taken from https://github.com/geertw/php-ip-anonymizer/blob/master/src/IpAnonymizer.php.
	 *
	 * @since 1.0.0
	 *
	 * @param string $address IPv4 or IPv6 address.
	 * @return string Anonymized IP address.
	 */
	protected function anonymize_ip_address( $address ) {
		$packed_address = inet_pton( $address );

		if ( strlen( $packed_address ) === 4 ) {
			return inet_ntop( $packed_address & inet_pton( '255.255.255.0' ) );
		}

		if ( strlen( $packed_address ) === 16 ) {
			return inet_ntop( $packed_address & inet_pton( 'ffff:ffff:ffff:ffff:0000:0000:0000:0000' ) );
		}

		return '';
	}
}
