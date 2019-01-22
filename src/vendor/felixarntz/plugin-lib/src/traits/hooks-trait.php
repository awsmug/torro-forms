<?php
/**
 * Hooks abstraction trait
 *
 * @package Leaves_And_Love\Plugin_Lib
 * @since 1.0.0
 */

namespace Leaves_And_Love\Plugin_Lib\Traits;

use ReflectionMethod;

if ( ! trait_exists( 'Leaves_And_Love\Plugin_Lib\Traits\Hooks_Trait' ) ) :

	/**
	 * Trait for Hooks API.
	 *
	 * This is a wrapper for the Hooks API that supports private methods.
	 *
	 * @since 1.0.0
	 */
	trait Hooks_Trait {
		/**
		 * Reference for the internal hook closures.
		 *
		 * @since 1.0.0
		 * @var array
		 */
		private $hook_map = array();

		/**
		 * Hooks a function or method to a specific action.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag             The name of the action to hook the $function_to_add callback to.
		 * @param callable $function_to_add The callback to be run when the action is run.
		 * @param int      $priority        Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
		 * @return true
		 */
		protected function add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			$mapped = $this->maybe_map_hook( $tag, $function_to_add, $priority, $accepted_args );

			return add_action( $tag, $mapped, $priority, $accepted_args );
		}

		/**
		 * Checks if any action has been registered for a hook.
		 *
		 * @since 1.0.0
		 *
		 * @param string        $tag               The name of the action hook.
		 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
		 * @param int           $priority          Optional. The priority to check for the callback. Must
		 *                                         be provided if the callback is a private class method.
		 *                                         Default 10.
		 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
		 *                   anything registered. When checking a specific function, the priority of that
		 *                   hook is returned, or false if the function is not attached. When using the
		 *                   $function_to_check argument, this function may return a non-boolean value
		 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
		 *                   return value.
		 */
		protected function has_action( $tag, $function_to_check = false, $priority = 10 ) {
			if ( $function_to_check ) {
				$mapped = $this->maybe_map_hook( $tag, $function_to_check, $priority, false );

				return has_action( $tag, $mapped );
			}

			return has_action( $tag );
		}

		/**
		 * Removes a function from a specified action hook.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag                The action hook to which the function to be removed is hooked.
		 * @param callable $function_to_remove The name of the function which should be removed.
		 * @param int      $priority           Optional. The priority of the function. Default 10.
		 * @return bool    Whether the function existed before it was removed.
		 */
		protected function remove_action( $tag, $function_to_remove, $priority = 10 ) {
			$mapped = $this->maybe_map_hook( $tag, $function_to_remove, $priority, false );

			return remove_action( $tag, $mapped, $priority );
		}

		/**
		 * Hooks a function or method to a specific filter action.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag             The name of the filter to hook the $function_to_add callback to.
		 * @param callable $function_to_add The callback to be run when the filter is applied.
		 * @param int      $priority        Optional. Used to specify the order in which the functions
		 *                                  associated with a particular action are executed. Default 10.
		 *                                  Lower numbers correspond with earlier execution,
		 *                                  and functions with the same priority are executed
		 *                                  in the order in which they were added to the action.
		 * @param int      $accepted_args   Optional. The number of arguments the function accepts. Default 1.
		 * @return true
		 */
		protected function add_filter( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
			$mapped = $this->maybe_map_hook( $tag, $function_to_add, $priority, $accepted_args );

			return add_filter( $tag, $mapped, $priority, $accepted_args );
		}

		/**
		 * Checks if any filter has been registered for a hook.
		 *
		 * @since 1.0.0
		 *
		 * @param string        $tag               The name of the filter hook.
		 * @param callable|bool $function_to_check Optional. The callback to check for. Default false.
		 * @param int           $priority          Optional. The priority to check for the callback. Must
		 *                                         be provided if the callback is a private class method.
		 *                                         Default 10.
		 * @return false|int If $function_to_check is omitted, returns boolean for whether the hook has
		 *                   anything registered. When checking a specific function, the priority of that
		 *                   hook is returned, or false if the function is not attached. When using the
		 *                   $function_to_check argument, this function may return a non-boolean value
		 *                   that evaluates to false (e.g.) 0, so use the === operator for testing the
		 *                   return value.
		 */
		protected function has_filter( $tag, $function_to_check = false, $priority = 10 ) {
			if ( $function_to_check ) {
				$mapped = $this->maybe_map_hook( $tag, $function_to_check, $priority, false );

				return has_filter( $tag, $mapped );
			}

			return has_filter( $tag );
		}

		/**
		 * Removes a function from a specified filter hook.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag                The filter hook to which the function to be removed is hooked.
		 * @param callable $function_to_remove The name of the function which should be removed.
		 * @param int      $priority           Optional. The priority of the function. Default 10.
		 * @return bool    Whether the function existed before it was removed.
		 */
		protected function remove_filter( $tag, $function_to_remove, $priority = 10 ) {
			$mapped = $this->maybe_map_hook( $tag, $function_to_remove, $priority, false );

			return remove_filter( $tag, $mapped, $priority );
		}

		/**
		 * Builds a unique ID for storage and retrieval.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag      Used in counting how many hooks were applied.
		 * @param callable $function Used for creating unique id.
		 * @param int|bool $priority Used in counting how many hooks were applied. If === false
		 *                           and $function is an object reference, we return the unique
		 *                           id only if it already has one, false otherwise.
		 * @return string|false Unique ID for usage as array key or false if $priority === false
		 *                      and $function is an object reference, and it does not already have
		 *                      a unique id.
		 */
		private function get_hook_id( $tag, $function, $priority ) {
			return _wp_filter_build_unique_id( $tag, $function, $priority );
		}

		/**
		 * Maps a hook to a closure that inherits the class' internal scope.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $id            Unique hook ID.
		 * @param callable $function      The callback to run when the hook is run.
		 * @param int|bool $accepted_args Optional. The number of arguments the callback accepts.
		 *                                Default 1.
		 * @return Closure The callable attached to the hook.
		 */
		private function map_hook( $id, $function, $accepted_args = 1 ) {
			if ( empty( $this->hook_map[ $id ] ) ) {
				if ( false === $accepted_args ) {
					return $function;
				}

				$this->hook_map[ $id ] = function() use ( $function, $accepted_args ) {
					return call_user_func_array( $function, array_slice( func_get_args(), 0, $accepted_args ) );
				};
			}

			return $this->hook_map[ $id ];
		}

		/**
		 * Maps a hook to a closure if the callback is a private class method.
		 *
		 * @since 1.0.0
		 *
		 * @param string   $tag           The name of the hook.
		 * @param callable $function      The callback to run when the hook is run.
		 * @param int|bool $priority      Used to specify the order in which the functions
		 *                                associated with a particular hook are executed.
		 * @param int|bool $accepted_args Optional. The number of arguments the callback accepts.
		 *                                Default 1.
		 * @return callable The callback to use for the actual hook.
		 */
		private function maybe_map_hook( $tag, $function, $priority, $accepted_args = 1 ) {
			if ( ! is_array( $function ) || is_string( $function[0] ) || $this !== $function[0] ) {
				return $function;
			}

			$reflection = new ReflectionMethod( $function[0], $function[1] );
			if ( $reflection->isPublic() ) {
				return $function;
			}

			return $this->map_hook( $this->get_hook_id( $tag, $function, $priority ), $function, $accepted_args );
		}
	}

endif;
