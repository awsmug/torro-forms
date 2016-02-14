<?php

/**
 * Form base class
 *
 * Init Forms with this class to get information about it
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package TorroForms
 * @version 2015-04-16
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 rheinschmiede (contact@awesome.ug)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License, version 2, as
 * published by the Free Software Foundation.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Torro_Form extends Torro_Post {
	/**
	 * @var int $id Form Id
	 * @since 1.0.0
	 */
	public $id;

	/**
	 * @var string $title Title of form
	 * @since 1.0.0
	 */
	public $title;

	/**
	 * @var array $containers All containers of the form
	 * @since 1.0.0
	 */
	public $containers = array();

	/**
	 * Container object
	 *
	 * @var Torro_Container
	 *
	 * @since 1.0.0
	 */
	private $container = null;

	/**
	 * Container id
	 *
	 * @var int $container_id
	 *
	 * @since 1.0.0
	 */
	private $container_id = null;

	/**
	 * Previous container id
	 *
	 * @var int $container_id
	 *
	 * @since 1.0.0
	 */
	private $prev_container_id = null;

	/**
	 * Next form container id
	 *
	 * @var int $container_id
	 *
	 * @since 1.0.0
	 */
	private $next_container_id = null;

	/**
	 * @var array $elements All elements of the form
	 * @since 1.0.0
	 */
	public $elements = array();

	/**
	 * @todo  Getting participants out of form and hooking in
	 * @var array $participants All elements of the form
	 * @since 1.0.0
	 */
	public $participants = array();

	/**
	 * @var int $splitter_count Counter for form splitters
	 * @since 1.0.0
	 */
	public $splitter_count = 0;

	/**
	 * @var array Internal variable for transfering elements on duplicating
	 * @since 1.0.0
	 */
	private $element_transfers = array();

	/**
	 * @var array Internal variable for transfering answers on duplicating
	 * @since 1.0.0
	 */
	private $answer_transfers = array();

	/**
	 * Constructor
	 *
	 * @param int $id The id of the form
	 *
	 * @since 1.0.0
	 */
	public function __construct( $id ) {
		parent::__construct( $id );

		$this->populate( $id );
	}

	/**
	 * Populating class variables
	 *
	 * @param int $id The id of the form
	 *
	 * @since 1.0.0
	 */
	private function populate( $id ) {
		$this->id = $id;

		if ( ! $this->exists() ) {
			return false;
		}

		$form        = get_post( $this->id );
		$this->title = $form->post_title;

		$this->containers   = $this->__get_containers();
		$this->elements     = $this->__get_elements();
		$this->participants = $this->__get_participants();

		return true;
	}

	/**
	 * Checks if a Form exists
	 *
	 * @return boolean $exists true if Form exists, false if not
	 * @since 1.0.0
	 */
	public function exists() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT COUNT( ID ) FROM {$wpdb->prefix}posts WHERE ID = %d AND post_type = 'torro-forms'", $this->id );
		$var = $wpdb->get_var( $sql );

		if ( $var > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * Getting containers
	 *
	 * @return array $containters
	 * @since 1.0.0
	 */
	private function __get_containers() {
		global $wpdb;

		$sql     = $wpdb->prepare( "SELECT id FROM {$wpdb->torro_containers} WHERE form_id=%d ORDER BY sort ASC", $this->id );
		$results = $wpdb->get_results( $sql );

		if ( 0 === $wpdb->num_rows ) {
			return array();
		}

		$containers = array();
		foreach ( $results AS $container ) {
			$containers[] = new Torro_Container( $container->id );
		}

		return $containers;
	}

	/**
	 * Getting elements
	 *
	 * @return array $elements
	 * @since 1.0.0
	 */
	private function __get_elements() {
		global $wpdb;

		$sql     = $wpdb->prepare( "SELECT id,type FROM {$wpdb->torro_elements} WHERE form_id=%d", $this->id );
		$results = $wpdb->get_results( $sql );

		$elements = array();
		foreach ( $results AS $element ) {
			$elements[] = torro()->elements()->get_registered( $element->id, $element->type );
		}

		return $elements;
	}

	/**
	 * Initializing participants
	 *
	 * @return array All participator ID's
	 * @since 1.0.0
	 */
	private function __get_participants() {
		global $wpdb;

		$sql     = $wpdb->prepare( "SELECT id FROM {$wpdb->torro_participants} WHERE form_id = %d", $this->id );
		$results = $wpdb->get_col( $sql );

		if ( 0 === $wpdb->num_rows ) {
			return array();
		}

		$participants = array();
		foreach ( $results AS $participant_id ) {
			$participants[] = new Torro_Participant( $participant_id );
		}

		return $participants;
	}

	/**
	 * Getting Containers
	 *
	 * @return array $containers
	 * @since 1.0.0
	 */
	public function get_containers() {
		return $this->containers;
	}

	/**
	 * Returning current container
	 * @return Torro_Container
	 */
	public function get_container(){
		return $this->container;
	}

	/**
	 * Setting Container id
	 *
	 * @param int $container_id
	 *
	 * @return Torro_Container
	 *
	 * @since 1.0.0
	 */
	public function set_container( $container_id = null )
	{
		if( null !== $container_id )
		{
			$prev_container_id = null;
			$next_container_id = null;

			for( $i = 0; $i < count( $this->containers ); $i++ )
			{
				if( $i > 0 )
				{
					$prev_container_id = $this->containers[ $i - 1 ]->id;
				}
				if( $i < count( $this->containers ) - 1 )
				{
					$next_container_id = $this->containers[ $i + 1 ]->id;
				}
				if( $container_id === $this->containers[ $i ]->id )
				{
					$this->container_id = $container_id;
					$this->container = $this->containers[ $i ];
					$this->prev_container_id = $prev_container_id;
					$this->next_container_id = $next_container_id;
					return $this->container;
				}
			}
		}
		else
		{
			$this->container_id = $this->containers[ 0 ]->id;
			$this->container = $this->containers[ 0 ];

			if( isset( $this->containers[ 1 ] ) ){
				$this->next_container_id = apply_filters( 'torro_forms_next_container_id', $this->containers[ 1 ]->id, $this );
			}

			return $this->container;
		}
	}

	/**
	 * Geting Participants
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function get_participants() {
		return $this->participants;
	}

	public function get_html( $form_action_url, $container_id = null, $response = array(), $errors = array() )
	{
		$this->set_container( $container_id );

		$html  = '<form class="torro-form" action="' . $form_action_url . '" method="POST" novalidate>';
		$html .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( 'torro-form-' . $this->id ) . '" />';
		$html .= '<input type="hidden" name="torro_form_id" value="' . $this->id . '" />';

		if( ! isset( $response[ $this->id ] ) )
		{
			$response[ $this->id ] = null;
		}
		if( ! isset( $errors[ $this->id ] ) )
		{
			$errors[ $this->id ] = null;
		}

		$html .= $this->container->get_html( $response[ $this->id ], $errors[ $this->id ] );

		$html .= $this->get_navigation();

		$html .= '</form>';
		return $html;
	}

	/**
	 * Getting navigation for form
	 *
	 * @param $actual_step
	 * @param $next_step
	 * @return string
	 * @since 1.0.0
	 */
	private function get_navigation() {
		$html = '';

		// If there was a step before, show previous button
		if ( null !== $this->prev_container_id ) {
			$html .= '<input type="submit" name="torro_submission_back" value="' . esc_attr__( 'Previous Step', 'torro-forms' ) . '"> ';
		}

		if( null !== $this->next_container_id ){
			$html .= '<input type="submit" name="torro_submission" value="' . esc_attr__( 'Next Step', 'torro-forms' ) . '">';
		}else{
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

		foreach ( $response as $element_id => $answers ) {
			if ( ! is_array( $answers ) ) {
				$answers = array( $answers );
			}

			foreach ( $answers as $answer ) {
				$wpdb->insert( $wpdb->torro_result_values, array(
					'result_id'  => $result_id,
					'element_id' => $element_id,
					'value'      => $answer
				) );
			}
		}

		return $result_id;
	}

	/**
	 * Duplicating a form
	 *
	 * @param bool $copy_meta         True if meta have to be copied
	 * @param bool $copy_comments     True if comments have to be copied
	 * @param bool $copy_elements     True if elements have to be copied
	 * @param bool $copy_answers      True if answers of elements have to be copied
	 * @param bool $copy_participants True if participants have to be copied
	 * @param bool $draft             True if duplicated form have to be a draft
	 *
	 * @return int
	 */
	public function duplicate( $copy_meta = true, $copy_taxonomies = true, $copy_comments = true, $copy_elements = true, $copy_answers = true, $copy_participants = true, $draft = false ) {
		$new_form_id = parent::duplicate( $copy_meta, $copy_taxonomies, $copy_comments, $draft );

		if ( $copy_elements ) {
			$this->duplicate_elements( $new_form_id, $copy_answers );
		}

		if ( $copy_participants ) {
			$this->duplicate_participants( $new_form_id );
		}

		do_action( 'form_duplicate', $this->post, $new_form_id, $this->element_transfers, $this->answer_transfers );

		return $new_form_id;
	}

	/**
	 * Duplicate Elements
	 *
	 * @param int  $new_form_id   Id of the form where elements have to be copied
	 * @param bool $copy_answers  True if answers have to be copied
	 * @param bool $copy_settings True if settings have to be copied
	 *
	 * @return bool
	 */
	public function duplicate_elements( $new_form_id, $copy_answers = true, $copy_settings = true ) {
		global $wpdb;

		if ( empty( $new_form_id ) ) {
			return false;
		}

		// Duplicate answers
		if ( is_array( $this->elements ) && count( $this->elements ) ) {
			foreach ( $this->elements as $element ) {
				$old_element_id = $element->id;

				// Todo: Have to be replaced by element object
				$wpdb->insert( $wpdb->torro_elements, array(
					'form_id' => $new_form_id,
					'label'   => $element->label,
					'sort'    => $element->sort,
					'type'    => $element->type
				), array(
					               '%d',
					               '%s',
					               '%d',
					               '%s',
				               ) );

				$new_element_id                             = $wpdb->insert_id;
				$this->element_transfers[ $old_element_id ] = $new_element_id;

				// Duplicate answers
				if ( is_array( $element->answers ) && count( $element->answers ) && $copy_answers ) {
					foreach ( $element->answers as $answer ) {
						$old_answer_id = $answer[ 'id' ];

						// Todo: Have to be replaced by answer object
						$wpdb->insert( $wpdb->torro_element_answers, array(
							'element_id' => $new_element_id,
							'answer'     => $answer[ 'text' ],
							'section'    => $answer[ 'section' ],
							'sort'       => $answer[ 'sort' ]
						), array(
							               '%d',
							               '%s',
							               '%s',
							               '%d',
						               ) );

						$new_answer_id                            = $wpdb->insert_id;
						$this->answer_transfers[ $old_answer_id ] = $new_answer_id;
					}
				}

				// Duplicate Settings
				if ( is_array( $element->settings ) && count( $element->settings ) && $copy_settings ) {
					// Todo: Have to be replaced by detting object
					foreach ( $element->settings as $name => $value ) {
						$wpdb->insert( $wpdb->torro_settings, array(
							'element_id' => $new_element_id,
							'name'       => $name,
							'value'      => $value
						), array(
							               '%d',
							               '%s',
							               '%s',
						               ) );
					}
				}

				do_action( 'torro_duplicate_form_element', $element, $new_element_id );
			}
		}
	}

	/**
	 * Duplicating participants
	 *
	 * @param int $new_form_idint Id of the form where participants have to be copied
	 *
	 * @return bool
	 */
	public function duplicate_participants( $new_form_id ) {
		global $wpdb;

		if ( empty( $new_form_id ) ) {
			return false;
		}

		// Duplicate answers
		if ( is_array( $this->participants ) && count( $this->participants ) ) {
			foreach ( $this->participants as $participant_id ) {
				$wpdb->insert( $wpdb->torro_participants, array(
					'form_id' => $new_form_id,
					'user_id' => $participant_id
				), array(
					               '%d',
					               '%d',
				               ) );
			}
		}
	}

	/**
	 * Delete form
	 *
	 * @since 1.0.0
	 */
	public function delete() {
		global $wpdb;

		/**
		 * Responses
		 */
		$this->delete_responses();

		$sql      = $wpdb->prepare( "SELECT id FROM $wpdb->torro_elements WHERE form_id=%d", $this->id );
		$elements = $wpdb->get_col( $sql );

		/**
		 * Answers & Settings
		 */
		if ( is_array( $elements ) && count( $elements ) > 0 ) {
			foreach ( $elements as $element_id ) {
				$wpdb->delete( $wpdb->torro_element_answers, array( 'element_id' => $element_id ) );
				$wpdb->delete( $wpdb->torro_settings, array( 'element_id' => $element_id ) );

				do_action( 'form_delete_element', $element_id, $this->id );
			}
		}

		/**
		 * Elements
		 */
		$wpdb->delete( $wpdb->torro_elements, array( 'form_id' => $this->id ) );

		do_action( 'form_delete', $this->id );

		/**
		 * Participiants
		 */
		$wpdb->delete( $wpdb->torro_participants, array( 'form_id' => $this->id ) );
	}

	/**
	 * Deleting all results of the Form
	 *
	 * @return mixed
	 */
	public function delete_responses() {
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

	/**
	 * Checks if a user has participated on a Form
	 *
	 * @param int  $form_id
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

	public function get_elements() {
		return $this->elements;
	}
}
