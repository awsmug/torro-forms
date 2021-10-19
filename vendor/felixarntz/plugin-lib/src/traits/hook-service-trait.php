<?php
/**
 * Hook service trait
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait' ) ) :

	/**
	 * Trait for a hook service.
	 *
	 * This adds functionality to add and remove hooks to a class.
	 *
	 * @since 1.0.0
	 */
	trait Hook_Service_Trait {
		use Hooks_Trait;

		/**
		 * The actions the service should trigger.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $actions = array();

		/**
		 * The filters the service should trigger.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		protected $filters = array();

		/**
		 * Whether the hooks for the service have been added.
		 *
		 * @since 1.0.0
		 * @var bool
		 */
		protected $hooks_added = false;

		/**
		 * Adds the service hooks.
		 *
		 * @since 1.0.0
		 */
		public function add_hooks() {
			if ( $this->hooks_added ) {
				return false;
			}

			foreach ( $this->actions as $action ) {
				$priority = isset( $action['priority'] ) ? intval( $action['priority'] ) : 10;
				$num_args = isset( $action['num_args'] ) ? absint( $action['num_args'] ) : 1;

				$this->add_action( $action['name'], $action['callback'], $priority, $num_args );
			}

			foreach ( $this->filters as $filter ) {
				$priority = isset( $filter['priority'] ) ? intval( $filter['priority'] ) : 10;
				$num_args = isset( $filter['num_args'] ) ? absint( $filter['num_args'] ) : 1;

				$this->add_filter( $filter['name'], $filter['callback'], $priority, $num_args );
			}

			$this->hooks_added = true;

			return true;
		}

		/**
		 * Removes the service hooks.
		 *
		 * @since 1.0.0
		 */
		public function remove_hooks() {
			if ( ! $this->hooks_added ) {
				return false;
			}

			foreach ( $this->actions as $action ) {
				$priority = isset( $action['priority'] ) ? intval( $action['priority'] ) : 10;

				$this->remove_action( $action['name'], $action['callback'], $priority );
			}

			foreach ( $this->filters as $filter ) {
				$priority = isset( $filter['priority'] ) ? intval( $filter['priority'] ) : 10;

				$this->remove_filter( $filter['name'], $filter['callback'], $priority );
			}

			$this->hooks_added = false;

			return true;
		}

		/**
		 * Sets up all action and filter hooks for the service.
		 *
		 * This method must be implemented and then be called from the constructor.
		 *
		 * @since 1.0.0
		 */
		protected abstract function setup_hooks();
	}

endif;
