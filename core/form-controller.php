<?php
/**
 * Core: Torro_Form_Controller class
 *
 * @package TorroForms
 * @subpackage Core
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms form controller class
 *
 * Handles form submissions in the frontend.
 *
 * @since 1.0.0-beta.1
 */
class Torro_Form_Controller {

	/**
	 * Instance object
	 *
	 * @var object $instance
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Cache for form controller
	 *
	 * @var Torro_Form_Controller_Cache
	 * @since 1.0.0
	 */
	private $cache = null;

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

	private $container_id;

	/**
	 * Form object
	 *
	 * @var Torro_Form
	 * @since 1.0.0
	 */
	private $form = null;

	/**
	 * Are we in a preview?
	 *
	 * @var bool
	 * @since 1.0.0
	 */
	private $is_preview = false;

	private $response = array();

	private $errors = array();

	/**
	 * Initializes the form controller.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'parse_request', array( $this, 'wp_request_set_form' ), 100 );
		add_action( 'parse_request', array( $this, 'control' ), 110 );

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
	 * Return the current container id
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_container_id() {
		return $this->container_id;
	}



	/**
	 * Returns the content of the form
	 *
	 * @return int
	 * @since 1.0.0
	 */
	public function get_content() {
		return $this->content;
	}

	/**
	 * Getting form response
	 *
	 * @return array
	 */
	public function get_form_response(){
		return $this->response;
	}

	/**
	 * Getting form errors
	 *
	 * @return array
	 */
	public function get_form_errors(){
		return $this->errors;
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
			case 'control':
			case 'add_filter_the_content':
			case 'filter_the_content':
				return call_user_func_array( array( $this, $name ), $arguments );
			default:
				return new Torro_Error( 'torro_form_controller_method_not_exists', sprintf( __( 'This Torro Forms Controller function "%s" does not exist.', 'torro-forms' ), $name ) );
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
		// No query vars > We are should be at start page
		if ( ! isset( $request->query_vars[ 'name' ] ) && ! isset( $request->query_vars[ 'pagename' ] )  && ! isset( $request->query_vars[ 'p' ] ) ) {
			if( ! isset( $_POST['torro_form_id'] ) )
				return;

			$args['post_type'] = 'torro_form';
			$args['include'] = $_POST['torro_form_id'];
		} else {
			if ( isset( $request->query_vars[ 'post_type' ] ) ) {
				$args[ 'post_type' ] = $request->query_vars[ 'post_type' ];
			}

			if ( isset( $request->query_vars[ 'name' ] ) ) {
				$args[ 'name' ] = $request->query_vars[ 'name' ];
			}

			if ( isset( $request->query_vars[ 'p' ] ) ) {
				$args[ 'include' ] = array( $request->query_vars[ 'p' ] );
			}

			if ( isset( $request->query_vars[ 'pagename' ] ) ) {
				$args[ 'name' ]      = $request->query_vars[ 'pagename' ];
				$args[ 'post_type' ] = 'page';
			}
		}

		$args[ 'post_status' ] = 'any';

		$posts = get_posts( $args );

		if( ! isset( $posts[ 0 ] ) ){
			return;
		}

		$post = $posts[ 0 ];

		if ( ! has_shortcode( $post->post_content, 'form' ) && 'torro_form' !== $post->post_type ) {
			return;
		}

		/**
		 * Getting form ID
		 */
		if ( 'torro_form' === $post->post_type ) {
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

		do_action( 'torro_formcontroller_set_form', $this->form_id );
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
		$form = torro()->forms()->get( $form_id );
		if ( is_wp_error( $form ) ) {
			return new Torro_Error( 'torro_form_controller_form_not_exist', sprintf( __( 'The form with the id %d does not exist.', 'torro-forms' ), $form_id ) );
		}

		$this->form_id = $form_id;
		$this->form    = $form;
		$this->cache = new Torro_Form_Controller_Cache( $this->form_id );

		return $this->form;
	}

	/**
	 * Setting up further vars by considering post variables
	 *
	 * @since 1.0.0
	 */
	private function control() {
		$action_url = $_SERVER['REQUEST_URI'];

		if ( empty( $this->form_id ) ) {
			return;
		}

		$torro_form_show = apply_filters( 'torro_form_show', true );

		if( true !== $torro_form_show ) {
			$this->content = $torro_form_show;
			return;
		}

		if ( ! isset( $_POST['torro_form_id'] ) || $this->cache->is_finished() ) {
			/**
			 * Initializing a fresh form
			 */
			$this->cache->reset();
		} elseif ( isset( $_POST['torro_submission_back'] ) ) {
			/**
			 * Going back
			 */
			$response = wp_unslash( $_POST['torro_response'] );

			$this->container_id = $response['container_id'];
			$this->form->set_current_container( $this->container_id );

			$prev_container_id = $this->form->get_previous_container_id();

			if ( is_wp_error( $prev_container_id ) ) {
				$prev_container_id = $this->form->get_current_container_id();
				if ( is_wp_error( $prev_container_id ) ) {
					$this->content = __( 'Internal Error. No previous page exists.', 'torro-forms' );
					return;
				}
			}

			$this->form->set_current_container( $prev_container_id );
			$this->container_id = $prev_container_id;

			$form_response = array();
			$cached_response = $this->cache->get_response();

			if( isset( $cached_response['containers'][ $prev_container_id ]['elements'] ) ) {
				$form_response = $cached_response['containers'][ $prev_container_id ]['elements'];
			}

			$this->response = $form_response;
		} else {
			/**
			 * Yes we have a submit!
			 */
			if ( ! isset( $_POST['_wpnonce'] ) ) {
				return;
			}

			if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'torro-form-' . $this->form_id ) ) {
				wp_die( '<h1>' . __( 'Cheatin&#8217; uh?' ) . '</h1>' . 403 );
			}

			$response = wp_unslash( $_POST['torro_response'] );

			$this->container_id = absint( $response['container_id'] );
			$this->form->set_current_container( $this->container_id );

			$errors = array();
			$containers = $this->form->containers;

			foreach ( $containers as $container ) {
				if ( $container->id !== $this->container_id ){
					continue;
				}

				$errors[ $container->id ] = array();

				$elements = $container->elements;

				foreach ( $elements as $element ) {
					$type = $element->type_obj;
					if( ! $type->input ){
						continue;
					}

					$value = '';
					if ( $type->upload ) {
						if ( isset( $_FILES[ 'torro_response_containers_' . $container->id . '_elements_' . $element->id ] ) ) {
							$value = $_FILES[ 'torro_response_containers_' . $container->id . '_elements_' . $element->id ];
						}
					} else {
						if ( isset( $response['containers'][ $container->id ]['elements'][ $element->id ] ) ) {
							$value = $response['containers'][ $container->id ]['elements'][ $element->id ];
						}
					}

					$value = $element->validate( $value );
					if ( is_wp_error( $value ) ) {
						$errors[ $container->id ]['elements'][ $element->id ] = $value->get_error_messages();
					} else {
						$response['containers'][ $container->id ]['elements'][ $element->id ] = $value;
					}
				}
			}

			$this->cache->add_response( $response );
			$is_submit = is_wp_error( $this->form->get_next_container_id() ); // we're in the last step
			$status = apply_filters( 'torro_response_status', true, $this->form_id, $this->container_id, $is_submit );

			/**
			 * There was no error!
			 */
			if ( $status && count( $errors[ $this->container_id ] ) === 0 ) {
				$next_container_id = $this->form->get_next_container_id();
				if ( ! is_wp_error( $next_container_id ) ) {
					$this->container_id = $next_container_id;
				} else {
					$result_id = $this->save_response();

					if ( false === $result_id ) {
						$this->content = new Torro_Error( 'torro_save_response_error', __( 'Your response couldn\'t be saved.', 'torro-forms' ) );
						return;
					}

					$html  = '<div id="torro-thank-submitting">';
					$html .= '<p>' . esc_html__( 'Thank you for submitting!', 'torro-forms' ) . '</p>';
					$html .= '</div>';

					do_action( 'torro_response_saved', $this->form_id, $result_id, $this->cache->get_response() );
					$this->content = apply_filters( 'torro_response_saved_content', $html, $this->form_id, $result_id, $this->cache->get_response() );

					$this->cache->delete_response();
					$this->cache->set_finished();

					return;
				}
			}

			$form_response = array();
			$response = $this->cache->get_response();

			if( isset( $response[ 'containers' ][ $this->container_id ][ 'elements' ] ) ) {
				$form_response = $response[ 'containers' ][ $this->container_id ][ 'elements' ];
			}
			$this->response = $form_response;

			$this->errors = array();
			if( isset( $errors[ $this->container_id ]['elements'] )) {
				$this->errors = $errors[ $this->container_id ]['elements'];

				do_action( 'torro_submission_has_errors', $this->errors );
			}
		}
	}

	/**
	 * Saving Response (Inserting to DB)
	 *
	 * @return bool|int
	 * @since 1.0.0
	 */
	private function save_response(){
		return $this->form->save_response( $this->cache->get_response() );
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
		$post = get_post();

		if ( 'torro_form' !== $post->post_type ) {
			return $content;
		}

		if ( is_wp_error( $this->content ) ) {
			return $this->content->get_error_message();
		} elseif ( ! $this->content ) {
			$this->content = $this->form->get_html( $_SERVER['REQUEST_URI'], $this->container_id, $this->response, $this->errors );
		}

		remove_filter( 'the_content', array( $this, 'filter_the_content' ) ); // only show once

		return $this->content;
	}
}

Torro_Form_Controller::instance();
