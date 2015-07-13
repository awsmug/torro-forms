<?php

/**
 * Form base class
 *
 * Init Forms with this class to get information about it
 *
 * @author  awesome.ug <contact@awesome.ug>
 * @package Questions
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

class Questions_Form extends Questions_Post{

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
     * @todo Getting participiants out of form and hooking in
     * @var array $participiants All elements of the form
     * @since 1.0.0
     */
    public $participiants = array();

	/**
	 * @var int $splitter_count Counter for form splitters
	 * @since 1.0.0
	 */
	public $splitter_count = 0;

	/**
	 * Constructor
	 * @param int $id The id of the form
	 * @since 1.0.0
	 */
	public function __construct( $id = NULL ) {
        parent::__construct( $id );

		if ( NULL != $id ) {
			$this->populate( $id );
		}
	}

	/**
	 * Populating class variables
	 * 
	 * @param int $id The id of the form
	 * @since 1.0.0
	 */
	private function populate( $id ) {

		$this->elements = array();

		$form = get_post( $id );

		$this->id    = $id;
		$this->title = $form->post_title;

		$this->elements = $this->get_elements();
        $this->participiants = $this->get_participiants();
	}

	/**
	 * Getting all element objects
	 * 
	 * @param int $id The id of the form
	 * @return array $elements All element objects of the form
	 * @since 1.0.0
	 */
	public function get_elements( $id = NULL ) {

		global $questions_global, $wpdb;

		if ( NULL == $id ) {
			$id = $this->id;
		}

		if ( '' == $id ) {
			return FALSE;
		}

		$sql     = $wpdb->prepare(
			"SELECT * FROM {$questions_global->tables->questions} WHERE questions_id = %s ORDER BY sort ASC", $id
		);
		$results = $wpdb->get_results( $sql );

		$elements = array();

		// Running all elements which have been found
		if ( is_array( $results ) ):
			foreach ( $results AS $result ):
				if ( class_exists( 'Questions_FormElement_' . $result->type ) ):
					$class       = 'Questions_FormElement_' . $result->type;
					$object      = new $class( $result->id );
					$elements[ ] = $object; // Adding element

					if ( $object->splits_form ):
						$this->splitter_count ++;
					endif;
				else:
					// If class do not exist -> Put in Error message here
				endif;
			endforeach;
		endif;

		return $elements;
	}

    /**
     * Getting participiants
     * @return array All participator ID's
     */
    public function get_participiants(){
        global $wpdb, $questions_global;

        $sql = $wpdb->prepare( "SELECT user_id FROM {$questions_global->tables->participiants} WHERE survey_id = %d", $this->id );
        $participiant_ids = $wpdb->get_col( $sql );

        return $participiant_ids;
    }

    /**
     * Dublicating a form
     * @param bool $copy_meta True if meta have to be copied
     * @param bool $copy_comments True if comments have to be copied
     * @param bool $copy_questions True if elements have to be copied
     * @param bool $copy_answers  True if answers of elements have to be copied
     * @param bool $copy_participiants  True if participiants have to be copied
     * @param bool $draft True if dublicated form have to be a draft
     * @return int
     */
    public function duplicate( $copy_meta = TRUE, $copy_taxonomies = TRUE, $copy_comments = TRUE, $copy_elements = TRUE, $copy_answers = TRUE, $copy_participiants = TRUE, $draft = FALSE ){
        $new_form_id = parent::duplicate( $copy_meta, $copy_taxonomies, $copy_comments, $draft );

        if( $copy_elements ):
            $this->duplicate_elements( $new_form_id, $copy_answers );
        endif;

        if( $copy_participiants ):
            $this->duplicate_participiants( $new_form_id );
        endif;

        do_action( 'questions_duplicate_survey', $this->post, $new_form_id, $this->question_transfers, $this->answer_transfers );

        return $new_form_id;
    }

    /**
     * Dublicate Elements
     * @param int $new_form_id Id of the form where elements have to be copied
     * @param bool $copy_answers True if answers have to be copied
     * @param bool $copy_settings True if settings have to be copied
     * @return bool
     */
    public function duplicate_elements( $new_form_id, $copy_answers = TRUE, $copy_settings = TRUE ){
        global $wpdb, $questions_global;

        if( empty( $new_form_id ) )
            return FALSE;

        // Dublicate answers
        if( is_array( $this->elements ) && count( $this->elements ) ):
            foreach( $this->elements AS $element ):
                $old_question_id = $element->id;

                $wpdb->insert(
                    $questions_global->tables->questions,
                    array(
                        'questions_id'  => $new_form_id,
                        'question'      => $element->question,
                        'sort'          => $element->sort,
                        'type'          => $element->slug
                    ),
                    array(
                        '%d',
                        '%s',
                        '%d',
                        '%s'
                    )
                );

                $new_question_id = $wpdb->insert_id;
                $this->question_transfers[ $old_question_id ] = $new_question_id;

                // Dublicate answers
                if( is_array( $element->answers ) && count( $element->answers ) && $copy_answers ):
                    foreach( $element->answers AS $answer ):
                        $old_answer_id = $answer[ 'id' ];

                        $wpdb->insert(
                            $questions_global->tables->answers,
                            array(
                                'question_id'   => $new_question_id,
                                'answer'          => $answer[ 'text' ],
                                'section'       => $answer[ 'section' ],
                                'sort'          => $answer[ 'sort' ]
                            ),
                            array(
                                '%d',
                                '%s',
                                '%s',
                                '%d'
                            )
                        );

                        $new_answer_id = $wpdb->insert_id;
                        $this->answer_transfers[ $old_answer_id ] = $new_answer_id;

                    endforeach;
                endif;

                // Dublicate Settings
                if( is_array( $element->settings ) && count( $element->settings ) && $copy_settings ):
                    foreach( $element->settings AS $name => $value ):

                        $wpdb->insert(
                            $questions_global->tables->settings,
                            array(
                                'question_id'   => $new_question_id,
                                'name'          => $name,
                                'value'         => $value
                            ),
                            array(
                                '%d',
                                '%s',
                                '%s'
                            )
                        );
                    endforeach;
                endif;

                do_action( 'questions_duplicate_form_question', $element, $new_question_id );

            endforeach;
        endif;
    }

    /**
     * Dublicating participiants
     * @param int $new_form_idint Id of the form where participiants have to be copied
     * @return bool
     */
    public function duplicate_participiants( $new_form_id ){
        global $wpdb, $questions_global;

        if( empty( $new_form_id ) )
            return FALSE;

        // Dublicate answers
        if( is_array( $this->participiants ) && count( $this->participiants ) ):
            foreach( $this->participiants AS $participiant_id ):

                $wpdb->insert(
                    $questions_global->tables->participiants,
                    array(
                        'survey_id' => $new_form_id,
                        'user_id'   => $participiant_id
                    ),
                    array(
                        '%d',
                        '%d',
                    )
                );
            endforeach;
        endif;
    }

    /**
     * Deleting all results of a survey
     * @return mixed
     */
    public function delete_results(){
        global $wpdb, $questions_global;

        $sql     = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->responds} WHERE questions_id = %s", $this->id  );
        $results = $wpdb->get_results( $sql );

        // Putting results in array
        if ( is_array( $results ) ):
            foreach ( $results AS $result ):
                $wpdb->delete( $questions_global->tables->respond_answers, array( 'respond_id' => $result->id ) );
            endforeach;
        endif;

        return $wpdb->delete( $questions_global->tables->responds, array( 'questions_id' => $this->id ) );
    }


    /**
     * Delete form
     * @since 1.0.0
     */
    public function delete() {

        global $wpdb, $questions_global;

        $sql      = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->questions} WHERE questions_id=%d", $this->id );
        $elements = $wpdb->get_col( $sql );

        /**
         * Answers & Settings
         */
        if ( is_array( $elements ) && count( $elements ) > 0 ):
            foreach ( $elements AS $question_id ):
                $wpdb->delete(
                    $questions_global->tables->answers,
                    array( 'question_id' => $question_id )
                );

                $wpdb->delete(
                    $questions_global->tables->settings,
                    array( 'question_id' => $question_id )
                );

                do_action( 'questions_delete_element', $question_id, $this->id );
            endforeach;
        endif;

        /**
         * Questions
         */
        $wpdb->delete(
            $questions_global->tables->questions,
            array( 'questions_id' => $this->id )
        );

        do_action( 'questions_delete_survey', $this->id );

        /**
         * Response Answers
         */
        $sql       = $wpdb->prepare( "SELECT id FROM {$questions_global->tables->responds} WHERE questions_id=%d", $this->id );
        $responses = $wpdb->get_col( $sql );

        if ( is_array( $responses ) && count( $responses ) > 0 ):
            foreach ( $responses AS $respond_id ):
                $wpdb->delete(
                    $questions_global->tables->respond_answers,
                    array( 'respond_id' => $respond_id )
                );

                do_action( 'questions_delete_responds', $respond_id, $this->id );
            endforeach;
        endif;

        /**
         * Responds
         */
        $wpdb->delete(
            $questions_global->tables->responds,
            array( 'questions_id' => $this->id )
        );

        /**
         * Participiants
         */
        $wpdb->delete(
            $questions_global->tables->participiants,
            array( 'survey_id' => $this->id )
        );
    }
}

/**
 * Checks if a survey exists
 * @param int $survey_id Survey id
 * @return boolean $exists TRUE if survey exists, FALSE if not
 */
function qu_form_exists( $form_id ) {

	global $wpdb;

	$sql = $wpdb->prepare(
		"SELECT COUNT( ID ) FROM {$wpdb->prefix}posts WHERE ID = %d and post_type = 'questions'", $form_id
	);
	$var = $wpdb->get_var( $sql );

	if ( $var > 0 ) {
		return TRUE;
	}

	return FALSE;
}
