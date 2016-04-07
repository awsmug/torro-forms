<?php

/**
 * Form base class
 * Init Forms with this class to get information about it
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *          Copyright 2015 rheinschmiede (contact@awesome.ug)
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

class Torro_Form extends Torro_Instance_Base {

	/**
	 * Title of form
	 *
	 * @var string
	 * @since 1.0.0
	 */
	protected $title;

	/**
	 * All containers of the form
	 *
	 * @var Torro_Container[]
	 * @since 1.0.0
	 */
	protected $containers = array();

	/**
	 * All elements of the form
	 *
	 * @var Torro_Form_Element[]
	 * @since 1.0.0
	 */
	protected $elements = array();

	/**
	 * All elements of the form
	 *
	 * @var Torro_Participant[]
	 * @since 1.0.0
	 */
	protected $participants = array();

	protected $container_index = -1;

	/**
	 * Constructor
	 *
	 * @param int $id The id of the form
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id = null ) {
		parent::__construct( $id );
	}

	/**
	 * Setting Container id
	 *
	 * @param int $container_id
	 *
	 * @return Torro_Container
	 * @since 1.0.0
	 */
	public function set_current_container( $container_id = null ) {
		if ( $container_id ) {
			for ( $i = 0; $i < count( $this->containers ); $i ++ ) {
				if ( $container_id === $this->containers[ $i ]->id ) {
					$this->container_index = $i;
					break;
				}
			}
		} else {
			$this->container_index = 0;
		}

		return $this->get_current_container();
	}

	/**
	 * Returning the current container
	 *
	 * @return Torro_Container
	 * @since 1.0.0
	 */
	public function get_current_container() {
		if ( 0 > $this->container_index || ! isset( $this->containers[ $this->container_index ] ) ) {
			return new Torro_Error( 'no_current_container', __( 'No current container is set.', 'torro-forms' ), __METHOD__ );
		}

		return $this->containers[ $this->container_index ];
	}

	public function get_current_container_id() {
		$container = $this->get_current_container();
		if ( is_wp_error( $container ) ) {
			return $container;
		}
		return $container->id;
	}

	public function get_previous_container_id() {
		$previous_index = $this->container_index - 1;
		if ( 0 > $previous_index || ! isset( $this->containers[ $previous_index ] ) ) {
			return new Torro_Error( 'no_previous_container', __( 'No previous container is set.', 'torro-forms' ), __METHOD__ );
		}

		return $this->containers[ $previous_index ]->id;
	}

	public function get_next_container_id() {
		$next_index = $this->container_index - 1;
		if ( 0 > $next_index || ! isset( $this->containers[ $next_index ] ) ) {
			return new Torro_Error( 'no_next_container', __( 'No next container is set.', 'torro-forms' ), __METHOD__ );
		}

		return $this->containers[ $next_index ]->id;
	}

	public function get_html( $form_action_url, $container_id = null, $response = array(), $errors = array() ) {
		$container = $this->set_current_container( $container_id );

		$html = '<form class="torro-form" action="' . $form_action_url . '" method="POST" method="post" enctype="multipart/form-data" novalidate>';
		$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'torro-form-' . $this->id ) . '" />';
		$html .= '<input type="hidden" name="torro_form_id" value="' . $this->id . '" />';

		$html .= $container->get_html( $response, $errors );

		$html .= $this->get_navigation();

		$html .= '</form>';

		return $html;
	}

	/**
	 * Checks if a user has participated on a Form
	 *
	 * @param null $user_id
	 *
	 * @return boolean $has_participated
	 * @since 1.0.0
	 */
	public function has_participated( $user_id = null ) {
		global $wpdb, $current_user;

		// Setting up user ID
		if ( null === $user_id ) {
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		}

		// Setting up Form ID
		if ( null === $this->id ) {
			return false;
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id=%d AND user_id=%s", $this->id, $user_id );

		$count = absint( $wpdb->get_var( $sql ) );

		if ( 0 === $count ) {
			return false;
		}

		return true;
	}

	/**
	 * Checks if form has analyzable elements
	 *
	 * @return bool
	 * @since 1.0.0
	 */
	public function has_analyzable_elements() {
		foreach ( $this->elements as $element ) {
			if ( ! $element->is_analyzable() ) {
				continue;
			} else {
				return true;
			}
		}

		return false;
	}

	/**
	 * Saving response
	 *
	 * @param int   $form_id
	 * @param array $response
	 *
	 * @return boolean $saved
	 * @since 1.0.0
	 */
	public function save_response( $response ) {
		global $wpdb, $current_user;

		get_currentuserinfo();
		$user_id = $current_user->ID;

		if ( ! $user_id ) {
			$user_id = - 1;
		}

		// Adding new element
		$wpdb->insert( $wpdb->torro_results, array(
			'form_id'   => $this->id,
			'user_id'   => $user_id,
			'timestamp' => time()
		) );

		$result_id         = $wpdb->insert_id;
		$this->response_id = $result_id;

		foreach ( $response[ 'containers' ] as $container_id => $container ) {
			foreach ( $container[ 'elements' ] as $element_id => $values ) {
				if ( ! is_array( $values ) ) {
					$values = array( $values );
				}

				foreach ( $values as $value ) {
					$wpdb->insert( $wpdb->torro_result_values, array(
						'result_id'  => $result_id,
						'element_id' => $element_id,
						'value'      => $value
					) );
				}
			}
		}

		return $result_id;
	}

	public function move( $invalid ) {
		return new Torro_Error( 'cannot_move_form', __( 'A form cannot be moved.', 'torro-forms' ) );
	}

	public function copy( $args = array() ) {
		$defaults = array(
			'terms'					=> true,
			'meta'					=> true,
			'comments'				=> true,
			'containers'			=> true,
			'elements'				=> true,
			'element_answers'		=> true,
			'element_settings'		=> true,
			'participants'			=> true,
			'as_draft'				=> false,
		);
		foreach ( $defaults as $key => $default ) {
			if ( ! isset( $args[ $key ] ) ) {
				$args[ $key ] = $default;
			}
		}

		$post_data = get_post( $this->id, ARRAY_A );
		if ( ! $post_data ) {
			return new Torro_Error( 'form_post_not_exist', __( 'Could not copy form post data.', 'torro-forms' ), __METHOD__ );
		}

		$post_data['ID'] = '';
		if ( $args['as_draft'] ) {
			$post_data['post_status'] = 'draft';
		}
		$post_data['post_date'] = $post_data['post_modified'] = $post_data['post_date_gmt'] = $post_data['post_modified_gmt'] = '';

		$post_id = wp_insert_post( $post_data );
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		} elseif ( ! $post_id ) {
			return new Torro_Error( 'cannot_copy_form', __( 'Could not copy form post data.', 'torro-forms' ), __METHOD__ );
		}

		if ( $args['terms'] ) {
			$taxonomies = get_object_taxonomies( get_post( $this->id ), 'names' );
			foreach ( $taxonomies as $taxonomy ) {
				$terms = wp_get_object_terms( $this->id, $taxonomy, array( 'fields' => 'ids' ) );
				wp_set_object_terms( $post_id, $terms, $taxonomy );
			}
		}

		if ( $args['meta'] ) {
			$forbidden = array( '_edit_lock', '_edit_last' );
			$meta = get_post_meta( $this->id );
			foreach ( $meta as $meta_key => $meta_values ) {
				if ( in_array( $meta_key, $forbidden, true ) ) {
					continue;
				}
				foreach ( $meta_values as $meta_value ) {
					add_post_meta( $post_id, $meta_key, $meta_value );
				}
			}
		}

		if ( $args['comments'] ) {
			$new_ids = array();

			$comments = get_comments( array( 'post_id' => $this->id ) );
			foreach ( $comments as $comment ) {
				$comment = get_object_vars( $comment );
				$comment['comment_post_ID'] = $post_id;
				$old_id = $comment['comment_ID'];
				unset( $comment['comment_ID'] );
				$new_id = wp_insert_comment( $comment );
				$new_ids[ $old_id ] = $new_id;
			}

			foreach ( $new_ids as $old_id => $new_id ) {
				$comment = get_comment( $new_id, ARRAY_A );
				if ( 0 === absint( $comment['comment_parent'] ) ) {
					continue;
				}
				$comment['comment_parent'] = $new_ids[ $comment['comment_parent'] ];
				wp_update_comment( $comment );
			}
		}

		if ( $args['containers'] ) {
			$new_container_ids = array();
			foreach ( $this->containers as $container ) {
				if ( ! $container->id ) {
					continue;
				}

				$old_id = $container->id;
				$new_container = $container->copy( $post_id );
				if ( ! is_wp_error( $new_container ) ) {
					$new_container_ids[ $old_id ] = $new_container->id;
				}
			}

			if ( $args['elements'] ) {
				$new_element_ids = array();
				$element_answers = array();
				$element_settings = array();
				foreach ( $this->elements as $element ) {
					if ( ! $element->id ) {
						continue;
					}
					if ( ! $element->container_id || ! isset( $new_container_ids[ $element->container_id ] ) ) {
						continue;
					}

					$element_answers = array_merge( $element_answers, $element->answers );
					$element_settings = array_merge( $element_settings, $element->settings );

					$old_id = $element->id;
					$new_element = $element->copy( $new_container_ids[ $element->container_id ] );
					if ( ! is_wp_error( $new_element ) ) {
						$new_element_ids[ $old_id ] = $new_element->id;
					}
				}

				if ( $args['element_answers'] ) {
					$new_element_answer_ids = array();
					foreach ( $element_answers as $element_answer ) {
						if ( ! $element_answer->id ) {
							continue;
						}
						if ( ! $element_answer->element_id || ! isset( $new_element_ids[ $element_answer->element_id ] ) ) {
							continue;
						}

						$old_id = $element_answer->id;
						$new_element_answer = $element_answer->copy( $new_element_ids[ $element_answer->element_id ] );
						if ( ! is_wp_error( $new_element_answer ) ) {
							$new_element_answer_ids[ $old_id ] = $new_element_answer->id;
						}
					}
				}

				if ( $args['element_settings'] ) {
					$new_element_setting_ids = array();
					foreach ( $element_settings as $element_setting ) {
						if ( ! $element_setting->id ) {
							continue;
						}
						if ( ! $element_setting->element_id || ! isset( $new_element_ids[ $element_setting->element_id ] ) ) {
							continue;
						}

						$old_id = $element_setting->id;
						$new_element_setting = $element_setting->copy( $new_element_ids[ $element_setting->element_id ] );
						if ( ! is_wp_error( $new_element_setting ) ) {
							$new_element_setting_ids[ $old_id ] = $new_element_setting->id;
						}
					}
				}
			}
		}

		if ( $args['participants'] ) {
			foreach ( $this->participants as $participant ) {
				if ( ! $participant->id ) {
					continue;
				}

				$old_id = $participant->id;
				$new_participant = $participant->copy( $post_id );
			}
		}

		do_action( 'form_copy', $this->id, $post_id, $this );

		return torro()->forms()->get( $post_id );
	}

	/**
	 * Getting navigation for form
	 *
	 * @param $actual_step
	 * @param $next_step
	 *
	 * @return string
	 * @since 1.0.0
	 */
	protected function get_navigation() {
		$html = '';

		// If there was a step before, show previous button
		if ( ! is_wp_error( $this->get_previous_container_id() ) ) {
			$html .= '<input type="submit" name="torro_submission_back" value="' . esc_attr__( 'Previous Step', 'torro-forms' ) . '"> ';
		}

		if ( ! is_wp_error( $this->get_next_container_id() ) ) {
			$html .= '<input type="submit" name="torro_submission" value="' . esc_attr__( 'Next Step', 'torro-forms' ) . '">';
		} else {
			ob_start();
			do_action( 'torro_form_send_button_before', $this->id );
			$html .= ob_get_clean();

			$html .= '<input type="submit" name="torro_submission" value="' . esc_attr__( 'Send', 'torro-forms' ) . '">';

			ob_start();
			do_action( 'torro_form_send_button_after', $this->id );
			$html .= ob_get_clean();
		}

		return $html;
	}

	protected function init() {
		$this->manager_method = 'forms';
		$this->valid_args = array(
			'title'		=> 'string',
		);
	}

	/**
	 * Populating class variables
	 *
	 * @param int $id The id of the form
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	protected function populate( $id ) {
		if ( is_object( $id ) && isset( $id->ID ) ) {
			$this->id = absint( $id->ID );
		} else {
			$this->id = absint( $id );
		}

		if ( ! $this->exists() ) {
			return false;
		}

		$form        = get_post( $this->id );
		$this->title = $form->post_title;

		$query_args = array(
			'form_id'	=> $this->id,
			'number'	=> -1,
		);

		$this->containers = torro()->containers()->query( $query_args );
		$this->elements = torro()->elements()->query( $query_args );
		$this->participants = torro()->participants()->query( $query_args );
	}

	protected function exists_in_db() {
		if ( get_post( $this->id ) ) {
			return true;
		}
		return false;
	}

	protected function save_to_db() {
		$post_data = array();
		$func = 'wp_insert_post';
		if ( ! empty( $this->id ) ) {
			$post_data = get_post( $this->id, ARRAY_A );
			$func = 'wp_update_post';
		}

		$post['post_type'] = 'torro-forms';
		$post['post_title'] = $this->title;

		return call_user_func( $func, $post_data, true );
	}

	/**
	 * Delete form
	 *
	 * @since 1.0.0
	 */
	protected function delete_from_db() {
		if ( empty( $this->id ) ) {
			return new Torro_Error( 'cannot_delete_empty', __( 'Cannot delete container without ID.', 'torro-forms' ), __METHOD__ );
		}

		foreach ( $this->containers as $container ) {
			$container->delete();
		}

		foreach ( $this->participants as $participant ) {
			$participant->delete();
		}

		$this->delete_responses_from_db();

		return wp_delete_post( $this->id, true );
	}

	/**
	 * Deleting all results of the Form
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	private function delete_responses_from_db() {
		global $wpdb;

		$sql     = $wpdb->prepare( "SELECT id FROM $wpdb->torro_results WHERE form_id = %s", $this->id );
		$results = $wpdb->get_results( $sql );

		// Putting results in array
		if ( is_array( $results ) ) {
			foreach ( $results as $result ) {
				$wpdb->delete( $wpdb->torro_result_values, array( 'result_id' => $result->id ) );
			}
		}

		return $wpdb->delete( $wpdb->torro_results, array( 'form_id' => $this->id ) );
	}
}
