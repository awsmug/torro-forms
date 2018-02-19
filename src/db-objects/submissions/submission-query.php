<?php
/**
 * Submission query class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submissions;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

/**
 * Class representing a query for submissions.
 *
 * @since 1.0.0
 */
class Submission_Query extends Query {
	/**
	 * Constructor.
	 *
	 * Sets the manager instance and assigns the defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Manager $manager The manager instance for the model query.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->query_var_defaults['orderby']             = array( 'timestamp' => 'DESC' );
		$this->query_var_defaults['form_id']             = '';
		$this->query_var_defaults['user_id']             = '';
		$this->query_var_defaults['remote_addr']         = '';
		$this->query_var_defaults['user_key']            = '';
		$this->query_var_defaults['user_identification'] = '';
		$this->query_var_defaults['status']              = '';
		$this->query_var_defaults['timestamp']           = '';
	}

	/**
	 * Parses the SQL where clause.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array with the first element being the array of SQL where clauses and the second
	 *               being the array of arguments for those where clauses.
	 */
	protected function parse_where() {
		list( $where, $args ) = parent::parse_where();

		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'form_id', 'form_id', '%d', 'absint', true );
		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'user_id', 'user_id', '%d', 'absint', true );
		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'remote_addr', 'remote_addr', '%s', 'sanitize_key', true );
		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'user_key', 'user_key', '%s', 'sanitize_key', true );
		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'status', 'status', '%s', 'sanitize_key', true );

		if ( is_array( $this->query_vars['user_identification'] ) ) {
			$user_identification_fields = $this->get_user_identification_fields();

			$table_name = $this->manager->get_table_name();

			$user_identification = array();
			foreach ( $user_identification_fields as $user_identification_field ) {
				if ( empty( $this->query_vars['user_identification'][ $user_identification_field ] ) ) {
					continue;
				}

				$user_identification[] = "%{$table_name}%.{$user_identification_field} = %s";
				$args[]                = $this->query_vars['user_identification'][ $user_identification_field ];
			}

			if ( ! empty( $user_identification ) ) {
				$where['user_identification'] = '( (' . implode( ') OR (', $user_identification ) . ') )';
			}
		}

		if ( '' !== $this->query_vars['timestamp'] ) {
			$table_name = $this->manager->get_table_name();

			if ( is_array( $this->query_vars['timestamp'] ) ) {
				if ( isset( $this->query_vars['timestamp']['greater_than'] ) ) {
					if ( ! empty( $this->query_vars['timestamp']['inclusive'] ) ) {
						$where['timestamp_greater_than'] = "%{$table_name}%.timestamp >= %d";
					} else {
						$where['timestamp_greater_than'] = "%{$table_name}%.timestamp > %d";
					}
					$args[] = absint( $this->query_vars['timestamp']['greater_than'] );
				}

				if ( isset( $this->query_vars['timestamp']['lower_than'] ) ) {
					if ( ! empty( $this->query_vars['timestamp']['inclusive'] ) ) {
						$where['timestamp_lower_than'] = "%{$table_name}%.timestamp <= %d";
					} else {
						$where['timestamp_lower_than'] = "%{$table_name}%.timestamp < %d";
					}
					$args[] = absint( $this->query_vars['timestamp']['lower_than'] );
				}
			} else {
				$where['timestamp'] = "%{$table_name}%.timestamp = %d";
				$args[]             = absint( $this->query_vars['timestamp'] );
			}
		}

		return array( $where, $args );
	}

	/**
	 * Returns the author identification query fields.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of valid author identification fields.
	 */
	protected function get_user_identification_fields() {
		return array( 'user_id', 'remote_addr', 'user_key' );
	}

	/**
	 * Returns the fields that are valid to be used in orderby clauses.
	 *
	 * @since 1.0.0
	 *
	 * @return array Array of valid orderby fields.
	 */
	public function get_valid_orderby_fields() {
		$orderby_fields = parent::get_valid_orderby_fields();

		return array_merge( $orderby_fields, array( 'timestamp' ) );
	}
}
