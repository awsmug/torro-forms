<?php
/**
 * Element choice query class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Element_Choices;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

/**
 * Class representing a query for element choices.
 *
 * @since 1.0.0
 */
class Element_Choice_Query extends Query {
	/**
	 * Constructor.
	 *
	 * Sets the manager instance and assigns the defaults.
	 *
	 * @since 1.0.0
	 *
	 * @param Element_Choice_Manager $manager The manager instance for the model query.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->query_var_defaults['orderby']      = array( 'sort' => 'ASC' );
		$this->query_var_defaults['form_id']      = '';
		$this->query_var_defaults['container_id'] = '';
		$this->query_var_defaults['element_id']   = '';
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

		if ( ! empty( $this->query_vars['form_id'] ) || ! empty( $this->query_vars['container_id'] ) ) {
			$table_name         = $this->manager->get_table_name();
			$element_table_name = $this->manager->get_parent_manager( 'elements' )->get_table_name();

			$join .= " INNER JOIN %{$element_table_name}% ON ( %{$table_name}%.element_id = %{$element_table_name}%.id )";

			if ( ! empty( $this->query_vars['form_id'] ) ) {
				$container_table_name = $this->manager->get_parent_manager( 'elements' )->get_parent_manager( 'containers' )->get_table_name();

				$join .= " INNER JOIN %{$container_table_name}% ON ( %{$element_table_name}%.container_id = %{$container_table_name}%.id )";
			}
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

		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'element_id', 'element_id', '%d', 'absint', true );

		if ( ! empty( $this->query_vars['container_id'] ) ) {
			$table_name         = $this->manager->get_table_name();
			$element_table_name = $this->manager->get_parent_manager( 'elements' )->get_table_name();

			list( $where, $args )  = $this->parse_default_where_field( $where, $args, 'container_id', 'container_id', '%d', 'absint', true );
			$where['container_id'] = str_replace( "%{$table_name}%", "%{$element_table_name}%", $where['container_id'] );
		}

		if ( ! empty( $this->query_vars['form_id'] ) ) {
			$table_name           = $this->manager->get_table_name();
			$container_table_name = $this->manager->get_parent_manager( 'elements' )->get_parent_manager( 'containers' )->get_table_name();

			list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'form_id', 'form_id', '%d', 'absint', true );
			$where['form_id']     = str_replace( "%{$table_name}%", "%{$container_table_name}%", $where['form_id'] );
		}

		return array( $where, $args );
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

		return array_merge( $orderby_fields, array( 'sort' ) );
	}
}
