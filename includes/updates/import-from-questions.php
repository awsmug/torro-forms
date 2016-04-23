<?php

function torro_import_from_questions() {
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	global $wpdb;

	$table_elements_questions = $wpdb->prefix . 'questions_questions';
	$table_responds_questions = $wpdb->prefix . 'questions_responds';
	$table_settings_questions = $wpdb->prefix . 'questions_settings';
	$table_respond_answers_questions = $wpdb->prefix . 'questions_respond_answers';
	$table_participiants_questions = $wpdb->prefix . 'questions_participiants';

	$terms = get_terms( array( 'taxonomy' => 'questions-categories', 'hide_empty' => false ) );

	// Adding terms
	$term_relations = array();
	foreach ( $terms AS $term ) {
		if( ! term_exists( $term->slug, 'torro_form_category' ) ){
			$term_new = wp_insert_term( $term->name, 'torro_form_category', array( 'slug' => $term->slug ) );
			$term_id_new = $term_new[ 'term_id' ];
		} else {
			$term_new = get_term_by( 'slug', $term->slug, 'torro_form_category' );
			$term_id_new = $term_new->term_id;
		}
		$term_relations[ $term->term_id ] = $term_id_new;
		$term_parents_old[ $term->term_id ] = $term->parent;
	}

	// Adding hierarchy
	foreach ( $term_relations AS $term_id_old => $term_id_new ) {
		$term_parent_id = $term_relations[ $term_parents_old[ $term_id_old ] ];
		if ( ! empty( $term_parent_id ) ) {
			wp_update_term( $term_id_new, 'torro_form_category', array( 'parent' => $term_parent_id ) );
		}
	}

	$posts = get_posts( array( 'post_type' => 'questions', 'posts_per_page' => -1, 'post_status' => 'any' ) );
	foreach ( $posts AS $post ){
		/**
		 * Copy form
		 */
		$post_metas = get_post_meta ( $post->ID );
		$post_terms = wp_get_post_terms( $post->ID, 'questions-categories', array( 'fields' => 'all' ) );
		$post_id_questions = $post->ID;

		$form = torro()->forms()->create( array( 'title' => $post->post_title ) );

		/**
		 * Copy postterms
		 */
		$term_ids = array();
		foreach ( $post_terms AS $post_term ) {
			$term_id = $term_relations[ $post_term->term_id ];
			$term_ids[] = $term_id;
		}

		if( count( $term_ids ) > 0 ) {
			wp_set_object_terms( $form->id, $term_ids, 'torro_form_category' );
		}

		/**
		 * Copy postmeta
		 */
		foreach ( $post_metas AS $key => $values ) {
			foreach ( $values AS $value ) {
				// Renaming values
				switch( $key ){
					case 'participiant_restrictions':
						switch( $value ){
							case 'all_visitors':
								$access_control_option = 'allvisitors';
								break;

							case 'no_restrictions':
								$access_control_option = 'allvisitors';
								break;

							case 'all_members':
								$access_control_option = 'allmembers';
								break;

							case 'selectedmembers':
								$access_control_option = 'selectedmembers';
								break;

							default:
								$access_control_option = 'selectedmembers';
								break;
						}

						add_post_meta( $form->id, 'access_controls_option', $access_control_option );
						break;
					default:
						add_post_meta( $form->id, $key, $value );
						break;
				}

			}
		}

		/**
		 * Copy elements
		 */
		// Getting old
		$sql = $wpdb->prepare(  "SELECT * FROM {$table_elements_questions} WHERE questions_id = %d", $post_id_questions );
		$questions = $wpdb->get_results( $sql );

		// Adding first container
		$container_num = 0;
		$label = __( 'Page', 'torro-forms' ) . ' ' . ( $container_num + 1 );
		$container = torro()->containers()->create( $form->id, array( 'label' => $label, 'sort' => $container_num ) );

		$element_relations = array();
		foreach ( $questions AS $question ) {
			switch( $question->type ){
				case 'splitter':
					$container_num++;
					$label = __( 'Page', 'torro-forms' ) . ' ' . ( $container_num + 1 );
					$container = torro()->containers()->create( $form->id, array( 'label' => $label, 'sort' => $container_num ) );
					break;
				case 'Text':
					$element_args = array(
						'label'        => $question->question,
						'sort'         => $question->sort,
						'type'         => 'textfield'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );
					torro_import_questions_settings( $question->id, $element_new->id );
					break;

				case 'Textarea':
					$element_args = array(
						'label'        => $question->question,
						'sort'         => $question->sort,
						'type'         => 'textarea'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );
					torro_import_questions_settings( $question->id, $element_new->id );
					break;

				case 'Description':
					$sql = $wpdb->prepare( "SELECT value FROM {$table_settings_questions} WHERE name = %s AND question_id = %d", 'description', $question->id );
					$label = $wpdb->get_var( $sql );

					$element_args = array(
						'label'        => $label,
						'sort'         => $question->sort,
						'type'         => 'content'
					);
					torro()->elements()->create( $container->id, $element_args );
					break;

				case 'Separator':
					$element_args = array(
							'label'        => $question->question,
							'sort'         => $question->sort,
							'type'         => 'separator'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );
					torro_import_questions_settings( $question->id, $element_new->id );
					break;

				case 'OneChoice':
					$element_args = array(
						'label'        => $question->question,
						'sort'         => $question->sort,
						'type'         => 'onechoice'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );
					torro_import_questions_settings( $question->id, $element_new->id );
					torro_import_questions_answers( $question->id, $element_new->id );
					break;

				case 'MultipleChoice':
					$element_args = array(
						'label'        => $question->question,
						'sort'         => $question->sort,
						'type'         => 'multiplechoice'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );
					torro_import_questions_settings( $question->id, $element_new->id );
					torro_import_questions_answers( $question->id, $element_new->id );
					break;

				case 'Dropdown':
					$element_args = array(
						'label'        => $question->question,
						'sort'         => $question->sort,
						'type'         => 'dropdown'
					);
					$element_new = torro()->elements()->create( $container->id, $element_args );

					torro_import_questions_settings( $question->id, $element_new->id );
					torro_import_questions_answers( $question->id, $element_new->id );
					break;
			}

			$element_relations[ $question->id ] = $element_new->id;
		}

		/**
		 * Copy results
		 */
		$sql = $wpdb->prepare( "SELECT * FROM {$table_responds_questions} WHERE questions_id = %d", $post_id_questions );
		$responds_questions = $wpdb->get_results( $sql );

		foreach ( $responds_questions AS $responds_question ) {
			$result = torro()->results()->create( $form->id, array( 'user_id' => $responds_question->user_id, 'timestamp' => $responds_question->timestamp, 'remote_addr' => $responds_question->remote_addr, 'cookie_key' => $responds_question->cookie_key ) );

			$sql = $wpdb->prepare( "SELECT * FROM {$table_respond_answers_questions} WHERE respond_id = %d", $responds_question->id );
			$responds_questions_answers = $wpdb->get_results( $sql );

			foreach ( $responds_questions_answers AS $responds_questions_answer ) {
				$result_values = torro()->result_values()->create( $result->id, array( 'element_id' => $element_relations[ $responds_questions_answer->question_id ], 'value' => $responds_questions_answer->value ) );
			}
		}

		/**
		 * Copy participants
		 */
		$sql = $wpdb->prepare( "SELECT * FROM {$table_participiants_questions} WHERE survey_id = %d", $post_id_questions );
		$participants_questions = $wpdb->get_results( $sql );

		foreach ( $participants_questions AS $participants_question ){
			$p = torro()->participants()->create( $form->id, array( 'user_id' => $participants_question->user_id ) );
		}
	}

	/**
	 * Copy options
	 */
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_subject', get_option( 'questions_invitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_invite_text', get_option( 'questions_invitation_text_template' ) );

	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from_name', get_option( 'questions_mail_from_name' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_from', get_option( 'questions_mail_from_email' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_subject', get_option( 'questions_reinvitation_subject_template' ) );
	update_option( 'torro_settings_restrictions_selectedmembers_reinvite_text', get_option( 'questions_reinvitation_text_template' ) );
}

function torro_import_questions_answers ( $question_id, $element_id ) {
	global $wpdb;

	$table_answers_questions = $wpdb->prefix . 'questions_answers';

	$sql = $wpdb->prepare( "SELECT * FROM {$table_answers_questions} WHERE question_id = %d", $question_id );
	$answers = $wpdb->get_results( $sql );

	foreach ( $answers AS $answer ) {
		torro()->element_answers()->create( $element_id, array( 'section' => $answer->section, 'answer' => $answer->answer, 'sort' => $answer->sort ) );
	}
}

function torro_import_questions_settings ( $question_id, $element_id ) {
	global $wpdb;

	$table_settings_questions = $wpdb->prefix . 'questions_settings';

	$sql = $wpdb->prepare( "SELECT * FROM {$table_settings_questions} WHERE question_id = %d", $question_id );
	$settings = $wpdb->get_results( $sql );

	foreach ( $settings AS $setting ) {
		switch( $setting->name ){
			case 'validation':
				$name = 'input_type';

				switch( $setting->value ){
					case 'numbers':
						$value = 'number';
						break;

					case 'numbers_decimal':
						$value = 'number_decimal';
						break;

					case 'email_address':
						$value = 'email_address';
						break;

					default:
						$value = 'text';
						break;
				}

				break;

			default:
				$name = $setting->name;
				$value = $setting->value;
				break;
		}

		torro()->element_settings()->create( $element_id, array( 'name' => $name, 'value' => $value ) );
	}
}
