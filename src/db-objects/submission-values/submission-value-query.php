<?php
/**
 * Submission value query class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Submission_Values;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

/**
 * Class representing a query for submission values.
 *
 * @since 1.0.0
 */
class Submission_Value_Query extends Query {
	/**
	 * Constructor.
	 *
	 * Sets the manager instance and assigns the defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission_Value_Manager $manager The manager instance for the model query.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->query_var_defaults['form_id']       = '';
		$this->query_var_defaults['submission_id'] = '';
		$this->query_var_defaults['element_id']    = '';
	}

	/**
	 * Parses the SQL join value.
	 *
	 * @since 1.0.0
	 *
	 * @return string Join value for the SQL query.
	 */
	protected function parse_join() {
		$join = parent::parse_join();

		if ( ! empty( $this->query_vars['form_id'] ) ) {
			$table_name            = $this->manager->get_table_name();
			$submission_table_name = $this->manager->get_parent_manager( 'submissions' )->get_table_name();

			$join .= " INNER JOIN %{$submission_table_name}% ON ( %{$table_name}%.submission_id = %{$submission_table_name}%.id )";
		}

		return $join;
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

		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'submission_id', 'submission_id', '%d', 'absint', true );
		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'element_id', 'element_id', '%d', 'absint', true );

		if ( ! empty( $this->query_vars['form_id'] ) ) {
			$table_name            = $this->manager->get_table_name();
			$submission_table_name = $this->manager->get_parent_manager( 'submissions' )->get_table_name();

			list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'form_id', 'form_id', '%d', 'absint', true );
			$where['form_id']     = str_replace( "%{$table_name}%", "%{$submission_table_name}%", $where['form_id'] );
		}

		return array( $where, $args );
	}
}
