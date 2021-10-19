<?php

namespace Leaves_And_Love\Sample_DB_Objects;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Query;

class Sample_Query extends Query {
	public function __construct( $manager ) {
		$name = $manager->get_sample_name();

		parent::__construct( $manager );

		$query_vars = array(
			'parent',
			'parent_include',
			'parent_exclude',
		);

		foreach ( $query_vars as $query_var ) {
			$this->query_var_defaults[ $query_var ] = '';
		}
	}

	protected function parse_where() {
		list( $where, $args ) = parent::parse_where();

		list( $where, $args ) = $this->parse_default_where_field( $where, $args, 'parent_id', 'parent', '%d', 'absint', false );
		list( $where, $args ) = $this->parse_list_where_field( $where, $args, 'parent_id', 'parent_include', 'parent_exclude', '%d', 'absint' );

		return array( $where, $args );
	}

	protected function parse_single_orderby( $orderby ) {
		if ( 'parent_include' === $orderby ) {
			$table_name = $this->manager->get_table_name();

			$ids = implode( ',', array_map( 'absint', $this->query_vars['parent_include'] ) );
			return "FIELD( %{$table_name}%.parent_id, $ids )";
		}

		return parent::parse_single_orderby( $orderby );
	}

	protected function parse_single_order( $order, $orderby ) {
		if ( 'parent_include' === $orderby ) {
			return '';
		}

		return parent::parse_single_order( $order, $orderby );
	}

	public function get_valid_orderby_fields() {
		$orderby_fields = parent::get_valid_orderby_fields();

		$orderby_fields = array_merge( $orderby_fields, array( 'parent_id', 'parent_include' ) );

		return $orderby_fields;
	}
}
