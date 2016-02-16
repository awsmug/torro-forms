<?php
/**
 * Form Controller
 * This class will controll the form
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2015 awesome.ug (support@awesome.ug)
 *          This program is free software; you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License, version 2, as
 *          published by the Free Software Foundation.
 *          This program is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *          You should have received a copy of the GNU General Public License
 *          along with this program; if not, write to the Free Software
 *          Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form_Controller {

	/**
	 * Instance object
	 *
	 * @var object $instance
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Content of the form
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $content = null;

	/**
	 * Current form id
	 *
	 * @var int $form_id
	 * @since 1.0.0
	 */
	private $form_id = null;

	/**
	 * Form object
	 *
	 * @var Torro_Form
	 * @since 1.0.0
	 */
	private $form = null;

	/**
	 * Determines if we are going forward
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	private $going_forward = false;

	/**
	 * Determines if a response will be saved or not
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	private $save_response = false;

	/**
	 * Is this a torro submit?
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	private $is_submit = false;

	/**
	 * Form action URL
	 *
	 * @var string
	 * @since 1.0.0
	 */
	private $form_action_url = null;

	/**
	 * Response Errors
	 *
	 * @var array
	 * @since 1.0.0
	 */
	private $response_errors = array();

	/**
	 * Initializes the form controller.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'parse_request', array( $this, 'wp_request_set_form' ), 100 );
		add_action( 'parse_request', array( $this, 'control' ), 101 );

		add_action( 'the_post', array( $this, 'add_filter_the_content' ) );
	}

	/**
	 * Singleton
	 *
	 * @return null|Torro_Form_Controller
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Return the current form id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_form_id() {
		return $this->form_id;
	}

	/**
	 * Get response errors
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_response_errors() {
		return $this->response_errors;
	}

	/**
	 * Creating form HTML
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function html() {
		$html = '';

		if ( isset( $_SESSION[ 'torro_response' ][ $this->form_id ][ 'finished' ] ) ) {
			ob_start();
			do_action( 'torro_form_finished', $this->form_id, $_SESSION[ 'torro_response' ][ $this->form_id ][ 'result_id' ] );
			$html .= ob_get_clean();
			session_destroy();
		} else {
			$show_form = apply_filters( 'torro_form_show', true ); // Hook for adding access-controls and so on ...

			if ( false === $show_form ) {
				return;
			}

			// Set global message on top of page
			if ( ! empty( $this->response_errors ) ) {
				$html .= '<div class="torro-element-error">';
				$html .= '<div class="torro-element-error-message"><p>';
				$html .= esc_attr__( 'There are open answers', 'torro-forms' );
				$html .= '</p></div></div>';
			}

			$html .= '<form class="torro-form" action="' . $this->form_action_url . '" method="POST" novalidate>';
			$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'torro-form-' . $this->form_id ) . '" />';

			$step_count = torro()->forms()->get( $this->form_id )->get_step_count();

			// Switch on navigation if there is more than one page
			if ( 0 !== $step_count ) {
				$html .= '<div class="torro-pagination">' . sprintf( __( 'Step <span class="torro-highlight-number">%d</span> of <span class="torro-highlight-number">%s</span>', 'torro-forms' ), $this->actual_step + 1, $step_count + 1 ) . '</div>';
			}

			// Getting all elements of step and running them
			$elements  = torro()->forms()->get( $this->form_id )->get_step_elements( $this->actual_step );
			$next_step = $this->actual_step;

			ob_start();
			do_action( 'torro_form_start', $this->form_id, $this->actual_step, $step_count );
			$html .= ob_get_clean();

			if ( is_array( $elements ) && count( $elements ) > 0 ) {
				foreach ( $elements as $element ) {
					if ( ! $element->splits_form ) {
						$html .= $element->get_html();
					} else {
						$next_step += 1; // If there is a next step, setting up next step var
						break;
					}
				}
			} else {
				return false;
			}

			$html .= $this->get_navigation( $this->actual_step, $next_step );

			ob_start();
			do_action( 'torro_form_end', $this->form_id, $this->actual_step, $step_count );
			$html .= ob_get_clean();

			$html .= '<input type="hidden" name="torro_next_step" value="' . $next_step . '" />';
			$html .= '<input type="hidden" name="torro_actual_step" value="' . $this->actual_step . '" />';
			$html .= '<input type="hidden" name="torro_form_id" value="' . $this->form_id . '" />';

			$html .= '</form>';
		}

		return $html;
	}

	/**
	 * Magic function to hide functions for autocomplete
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed|Torro_Error
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case 'wp_request_set_form':
				return $this->wp_request_set_form( $arguments );
				break;
			case 'control':
				return $this->control();
				break;
			case 'add_filter_the_content':
				return $this->add_filter_the_content();
				break;
			case 'filter_the_content':
				return $this->filter_the_content( $arguments );
				break;
			default:
				return new Torro_Error( 'torro_form_controller_method_not_exists', __( 'This Torro Forms Controller function does not exist.', 'torro-forms' ) );
				break;
		}
	}

	/**
	 * Processing request and setting up form id
	 *
	 * @param array $request
	 *
	 * @since 1.0.0
	 * @return null;
	 */
	private function wp_request_set_form( $request ) {
		$request = $request[ 0 ];

		// We have to be in a post type or leave
		if ( ! isset( $request->query_vars[ 'name' ] ) && ! isset( $request->query_vars[ 'pagename' ] ) ) {
			return;
		}

		// Setting up post variables
		if ( isset( $request->query_vars[ 'post_type' ] ) ) {
			$post_type = $request->query_vars[ 'post_type' ];
			$post_name = $request->query_vars[ 'name' ];
		} else {
			if ( isset( $request->query_vars[ 'name' ] ) ) {
				$post_type = 'post';
				$post_name = $request->query_vars[ 'name' ];
			} elseif ( isset( $request->query_vars[ 'pagename' ] ) ) {
				$post_type = 'page';
				$post_name = $request->query_vars[ 'pagename' ];
			} else {
				// We don't know it, we leave!
				return;
			}
		}

		$args = array(
			'name'        => $post_name,
			'post_type'   => $post_type,
			'numberposts' => 1
		);

		$posts = get_posts( $args );
		$post  = $posts[ 0 ];

		if ( ! has_shortcode( $post->post_content, 'form' ) && 'torro-forms' !== $post_type ) {
			return;
		}

		/**
		 * Getting form ID
		 */
		if ( 'torro-forms' === $post_type ) {
			// Yes we are on e page which dispays a torro form post type!
			if ( is_wp_error( $this->set_form( $post->ID ) ) ) {
				return;
			}
		} else {
			// We are working with shortcodes!
			$pattern = get_shortcode_regex( array( 'form' ) );
			preg_match_all( "/$pattern/", $post->post_content, $matches );
			$short_code_params = $matches[ 3 ];
			$shortcode_atts    = shortcode_parse_atts( $short_code_params[ 0 ] );

			if ( ! isset( $shortcode_atts[ 'id' ] ) ) {
				return;
			}

			if ( is_wp_error( $this->set_form( $shortcode_atts[ 'id' ] ) ) ) {
				return;
			}
		}

		do_action( 'torro_wp_request_set_form', $this->form_id );
		/*

		if ( isset( $_POST[ 'torro_next_step' ] ) ) {
			$this->actual_step = absint( $_POST[ 'torro_next_step' ] );
			$this->previous_step = absint( $_POST[ 'torro_actual_step' ] );
		} elseif ( isset( $_POST[ 'torro_actual_step' ] ) ) {
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] );
			$this->previous_step = absint( $_POST[ 'torro_actual_step' ] );
		} else {
			$this->actual_step = 0;
			$this->previous_step = 0;
		}

		if ( array_key_exists( 'torro_submission_back', $_POST ) ) {
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] ) - 1;
		}

		$this->going_forward = false;
		if ( ! isset( $_POST[ 'torro_submission_back' ] ) ) {
			$this->going_forward = true;
		}

		$this->save_response = false;
		if( absint( $_POST[ 'torro_actual_step' ] ) === absint( $_POST[ 'torro_next_step' ] ) ){
			$this->save_response = true;
		}

		if ( ! $this->is_torro_submit ){
			return;
		}

		$this->form_action_url = $_SERVER[ 'REQUEST_URI' ];
		$response_new   = isset( $_POST[ 'torro_response' ] ) ? $_POST[ 'torro_response' ] : array();
		$response_saved = isset( $_SESSION[ 'torro_response' ][ $this->form_id ] ) ? $_SESSION[ 'torro_response' ][ $this->form_id ] : array();
		$merged_response = $response_new;

		// If there was a saved response merge it with new
		if ( ! empty( $response_saved ) ) {
			$merged_response = $response_saved;

			if ( is_array( $response_new ) && 0 < count( $response_new ) ) {
				foreach ( $response_new as $key => $answer ) {
					$merged_response[ $key ] = torro_prepare_post_data( $answer );
				}
			}
		}

		$_SESSION[ 'torro_response' ][ $this->form_id ] = $merged_response;  // Saving data to Session for further submits

		// Only parse request if not going backwards
		if( ! $this->going_forward ){
			return;
		}

		$validated = $this->validate( $response_new, $this->previous_step );

		if( ! $validated )
		{
			$this->actual_step = absint( $_POST[ 'torro_actual_step' ] );
			return;
		}

		// Saving
		if ( $this->save_response ) {
			$result_id = torro()->forms()->get( $this->form_id )->save_response( $merged_response );

			// After successfull saving
			if ( $result_id ) {
				do_action( 'torro_response_save', $this->form_id, $result_id, $merged_response );

				unset( $_SESSION[ 'torro_response' ][ $this->form_id ] );

				$_SESSION[ 'torro_response' ][ $this->form_id ][ 'result_id' ] = $result_id;
				$_SESSION[ 'torro_response' ][ $this->form_id ][ 'finished' ]  = true;

				header( 'Location: ' . $_SERVER[ 'REQUEST_URI' ] );
				die();
			}
		}

		do_action( 'torro_form_parse_request', $this->form_id, $this->actual_step, $response_new, $merged_response );

		*/
	}

	/**
	 * Setting form id
	 *
	 * @param int $form_id
	 *
	 * @return Torro_Form|Torro_Error
	 * @since 1.0.0
	 */
	private function set_form( $form_id ) {
		if ( torro()->forms()->get( $form_id )->exists() ) {
			$this->form_id = $form_id;
			$this->form    = torro()->forms()->get( $this->form_id );

			return $this->form;
		}

		return new Torro_Error( 'torro_form_controller_form_not_exist', sprintf( __( 'The form with the id %d does not exist.', 'torro-forms' ), $form_id ) );
	}

	/**
	 * Setting up further vars by considering post variables
	 *
	 * @since 1.0.0
	 */
	private function control() {
		$action_url = $_SERVER[ 'REQUEST_URI' ];

		if ( empty( $this->form_id ) ) {
			return false;
		}

		if ( ! isset( $_POST[ 'torro_form_id' ] ) ) {
			/**
			 * Initializing a fresh form
			 */
			$this->content = $this->form->get_html( $action_url );
		} elseif ( isset( $_POST[ 'torro_submission_back' ] ) ) {
			/**
			 * Going back
			 */
			$current_container_id = $this->form->prev_container_id;
		} else {
			/**
			 * Yes we have a submit!
			 */
			if ( ! isset( $_POST[ '_wpnonce' ] ) ) {
				return false;
			}

			if ( ! isset( $_POST[ 'torro_response' ][ 'containers' ] ) ) {
				return false;
			}

			if ( ! wp_verify_nonce( $_POST[ '_wpnonce' ], 'torro-form-' . $this->form_id ) ) {
				wp_die( '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' . 403 );
			}

			$response = $_POST[ 'torro_response' ];
			$errors   = array();

			$containers = $this->form->get_containers();
			foreach ( $containers AS $container ) {
				if ( ! isset( $response[ 'containers' ][ $container->id ] ) ) {
					$errors[ $container->id ][ 'container' ] = sprintf( __( 'Missing Container #%d in form data.', 'torro-forms' ), $container->id );
					continue;
				}

				$elements = $container->get_elements();
				foreach ( $elements AS $element ) {
					if ( ! isset( $response[ 'containers' ][ $container->id ][ 'elements' ][ $element->id ] ) ) {
						$errors[ $container->id ][ 'container' ] = sprintf( __( 'Missing Element #%d of Container #%d with in form data.', 'torro-forms' ), $element->id, $container->id );
						continue;
					}

					$value = $response[ 'containers' ][ $container->id ][ 'elements' ][ $element->id ];

					if ( ! $element->validate( $value ) ) {
						$errors[ $container->id ][ 'elements' ][ $element->id ] = $element->get_validation_errors();
					}
				}
			}

			$this->cache_response( $response );

			/**
			 * Setting up container id
			 */
			if ( count( $errors ) > 0 ) {
				$current_container_id = $response[ 'container_id' ];
			} else {
				if ( ! empty( $this->form->next_container_id ) ) {
					$current_container_id = $this->form->next_container_id;
				} else {

				}
			}

			$this->content = $this->form->get_html( $action_url, $current_container_id, $response, $errors );
			$this->is_submit = true;
		}


	}

	/**
	 * Caching response into session
	 *
	 * @param array $response
	 *
	 * @return bool
	 */
	private function cache_response( $response ){
		if ( ! isset( $_SESSION ) ) {
			if( ! session_start() ){
				return false;
			}
		}

		$cache = $_SESSION[ 'torro_response' ][ $this->form_id ];
		$cache = array_merge( $response, $cache );

		$_SESSION[ 'torro_response' ][ $this->form_id ] = $cache;

		return true;
	}

	/**
	 * Get response cache
	 *
	 * @param int $container_id
	 *
	 * @return bool
	 */
	private function get_response_cache( $container_id = null ){
		if ( ! isset( $_SESSION ) ) {
			return false;
		}

		if( ! empty( $container_id ) ) {
			if( ! isset( $_SESSION[ 'torro_response' ][ $this->form_id ][ $container_id ] ) ){
				return false;
			}

			return $_SESSION[ 'torro_response' ][ $this->form_id ][ $container_id ];
		}

		return $_SESSION[ 'torro_response' ][ $this->form_id ];
	}

	/**
	 * Adding filter for the content to show form
	 *
	 * @since 1.0.0
	 */
	private function add_filter_the_content() {
		add_filter( 'the_content', array( $this, 'filter_the_content' ) );
	}

	/**
	 * The filtered content gets a Form
	 *
	 * @param string $content
	 *
	 * @return string $content
	 * @since 1.0.0
	 */
	private function filter_the_content( $content ) {
		$this->init_vars();

		if ( is_wp_error( $this->content ) ) {
			return $this->content->get_error_message();
		}

		remove_filter( 'the_content', array( $this, 'filter_the_content' ) ); // only show once

		return $this->content;
	}
}

Torro_Form_Controller::instance();