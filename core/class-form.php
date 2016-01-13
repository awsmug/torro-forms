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
		$this->elements = array();

		$form = get_post( $id );

		$this->id = $id;
		$this->title = $form->post_title;

		$this->elements = $this->get_elements();
		$this->participants = $this->get_participants();
	}

	/**
	 * Getting all element objects
	 *
	 * @param int $id The id of the form
	 *
	 * @return array $elements All element objects of the form
	 * @since 1.0.0
	 */
	public function get_elements( $id = null ) {
		global $wpdb;

		if ( null === $id ) {
			$id = $this->id;
		}

		$id = absint( $id );

		if ( 0 === $id ) {
			return false;
		}

		$results = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $wpdb->torro_elements WHERE form_id = %d ORDER BY sort ASC", $id ) );

		$elements = array();

		// Running all elements which have been found
		if ( is_array( $results ) ) {
			foreach( $results as $result ) {
				if ( ( $element = torro()->elements()->get( $result->id, $result->type ) ) ) {
					$elements[] = $element; // Adding element

					if ( $element->splits_form ) {
						$this->splitter_count++;
					}
				}
			}
		}

		return $elements;
	}

	/**
	 * Getting participants
	 *
	 * @return array All participator ID's
	 */
	public function get_participants() {
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %d", $this->id );
		$participant_ids = $wpdb->get_col( $sql );

		return $participant_ids;
	}

	/**
	 * Getting elements of a Form
	 *
	 * @param int $form_id
	 * @param int $step
	 *
	 * @return array $elements
	 * @since 1.0.0
	 */
	public function get_step_elements( $step = 0 ) {
		$actual_step = 0;

		$elements = array();
		foreach ( $this->elements as $element ) {
			$elements[ $actual_step ][] = $element;
			if ( $element->splits_form ) {
				$actual_step++;
			}
		}

		if ( $actual_step < $step ) {
			return false;
		}

		return $elements[ $step ];
	}

	/**
	 * Get number of splits in the Form
	 *
	 * @param int $form_id
	 *
	 * @return int $splitter_count
	 * @since 1.0.0
	 */
	public function get_step_count() {
		return absint( $this->splitter_count );
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
			$user_id = -1;
		}

		// Adding new element
		$wpdb->insert( $wpdb->torro_results, array(
			'form_id'	=> $this->id,
			'user_id'	=> $user_id,
			'timestamp'	=> time()
		) );

		$result_id = $wpdb->insert_id;
		$this->response_id = $result_id;

		foreach ( $response as $element_id => $answers ) {
			if ( ! is_array( $answers ) ) {
				$answers = array( $answers );
			}

			foreach( $answers as $answer ) {
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
	 * @param bool $copy_meta          True if meta have to be copied
	 * @param bool $copy_comments      True if comments have to be copied
	 * @param bool $copy_elements      True if elements have to be copied
	 * @param bool $copy_answers       True if answers of elements have to be copied
	 * @param bool $copy_participants True if participants have to be copied
	 * @param bool $draft              True if duplicated form have to be a draft
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

				$wpdb->insert( $wpdb->torro_elements, array(
					'form_id'	=> $new_form_id,
					'label'		=> $element->label,
					'sort'		=> $element->sort,
					'type'		=> $element->name
				), array(
					'%d',
					'%s',
					'%d',
					'%s',
				) );

				$new_element_id = $wpdb->insert_id;
				$this->element_transfers[ $old_element_id ] = $new_element_id;

				// Duplicate answers
				if ( is_array( $element->answers ) && count( $element->answers ) && $copy_answers ) {
					foreach ( $element->answers as $answer ) {
						$old_answer_id = $answer[ 'id' ];

						$wpdb->insert( $wpdb->torro_element_answers, array(
							'element_id'	=> $new_element_id,
							'answer'		=> $answer[ 'text' ],
							'section'		=> $answer[ 'section' ],
							'sort'			=> $answer[ 'sort' ]
						), array(
							'%d',
							'%s',
							'%s',
							'%d',
						) );

						$new_answer_id = $wpdb->insert_id;
						$this->answer_transfers[ $old_answer_id ] = $new_answer_id;
					}
				}

				// Duplicate Settings
				if ( is_array( $element->settings ) && count( $element->settings ) && $copy_settings ) {
					foreach ( $element->settings as $name => $value ) {
						$wpdb->insert( $wpdb->torro_settings, array(
							'element_id'	=> $new_element_id,
							'name'			=> $name,
							'value'			=> $value
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
					'form_id'	=> $new_form_id,
					'user_id'	=> $participant_id
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

		$sql = $wpdb->prepare( "SELECT id FROM $wpdb->torro_elements WHERE form_id=%d", $this->id );
		$elements = $wpdb->get_col( $sql );

		/**
		 * Answers & Settings
		 */
		if( is_array( $elements ) && count( $elements ) > 0 ) {
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

		$sql = $wpdb->prepare( "SELECT id FROM $wpdb->torro_results WHERE form_id = %s", $this->id );
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

/**
 * Checks if a Form exists
 *
 * @param int $form_id Form ID
 *
 * @return boolean $exists true if Form exists, false if not
 */
function torro_form_exists( $form_id ) {
	global $wpdb;

	$sql = $wpdb->prepare( "SELECT COUNT( ID ) FROM {$wpdb->prefix}posts WHERE ID = %d and post_type = 'torro-forms'", $form_id );
	$var = $wpdb->get_var( $sql );

	if ( $var > 0 ) {
		return true;
	}

	return false;
}
