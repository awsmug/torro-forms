<?php
/**
 * Container query class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Containers;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

/**
 * Class representing a query for containers.
 *
 * @since 1.0.0
 */
class Container_Query extends Query {
	/**
	 * Constructor.
	 *
	 * Sets the manager instance and assigns the defaults.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param Leaves_And_Love\Plugin_Lib\DB_Objects\Manager $manager The manager instance for the model query.
	 */
	public function __construct( $manager ) {
		parent::__construct( $manager );

		$this->query_var_defaults['orderby'] = array( 'sort' => 'ASC' );
		$this->query_var_defaults['form_id'] = '';
	}

	/**
	 * Parses the SQL where clause.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array with the first element being the array of SQL where clauses and the second
	 *               being the array of arguments for those where clauses.
	 */
	protected function parse_where() {
		list( $where, $args ) = parent::parse_where();

		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'form_id', 'form_id', '%d', 'absint', true );

		return array( $where, $args );
	}

	/**
	 * Returns the fields that are valid to be used in orderby clauses.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return array Array of valid orderby fields.
	 */
	public function get_valid_orderby_fields() {
		$orderby_fields = parent::get_valid_orderby_fields();

		return array_merge( $orderby_fields, array( 'sort' ) );
	}
}
