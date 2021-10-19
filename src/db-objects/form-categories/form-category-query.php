<?php
/**
 * Form category query class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\DB_Objects\Form_Categories;

use Leaves_And_Love\Plugin_Lib\DB_Objects\Queries\Core_Query;
use WP_Term_Query;

/**
 * Class representing a query for form categories.
 *
 * @since 1.0.0
 */
class Form_Category_Query extends Core_Query {

	/**
	 * Sets up the query for retrieving form categories.
	 *
	 * @since 1.0.0
	 *
	 * @param string|array $query Array or query string of form query arguments.
	 * @return Form_Category_Collection Collection of form categories.
	 */
	public function query( $query ) {
		$query = $this->map_args( $query );

		if ( isset( $query['fields'] ) && 'ids' !== $query['fields'] ) {
			$query['fields'] = 'all';
		}

		return parent::query( $query );
	}

	/**
	 * Instantiates the internal Core query object.
	 *
	 * @since 1.0.0
	 *
	 * @return object Internal Core query object.
	 */
	protected function instantiate_query_object() {
		return new WP_Term_Query();
	}

	/**
	 * Parses the results of the internal Core query into a collection.
	 *
	 * @since 1.0.0
	 *
	 * @return Form_Category_Collection Results as a collection.
	 */
	protected function parse_results_collection() {
		$ids    = null !== $this->original->terms ? $this->original->terms : array();
		$fields = $this->original->query_vars['fields'];

		if ( 'ids' !== $fields ) {
			$ids    = wp_list_pluck( $ids, 'term_id' );
			$fields = 'objects';
		}

		return $this->create_collection( $ids, count( $ids ), $fields );
	}

	/**
	 * Maps form query arguments to regular post query arguments.
	 *
	 * @since 1.0.0
	 *
	 * @param array $args Arguments as `$query_var => $value` pairs.
	 * @return array Mapped arguments.
	 */
	protected function map_args( $args ) {
		$mapped_args = $args;
		foreach ( $args as $query_var => $value ) {
			switch ( $query_var ) {
				case 'title':
					$mapped_args['name'] = $value;
					unset( $mapped_args['title'] );
					break;
				case 'orderby':
					if ( 'title' === $value ) {
						$mapped_args[ $query_var ] = 'name';
					}
					break;
			}
		}

		$mapped_args['hide_empty'] = false;
		$mapped_args['taxonomy']   = $this->manager->get_prefix() . 'form_category';

		return $mapped_args;
	}
}
