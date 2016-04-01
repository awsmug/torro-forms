<?php
/**
 * The AJAX handler class.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0alpha1
 * @since   1.0.0
 * @license GPL 2
 *
 * Copyright 2015 awesome.ug (support@awesome.ug)
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

final class Torro_AJAX {
	/**
	 * Intance
	 *
	 * @var object $instance
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_AJAX
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Actions
	 *
	 * @var array $actions
	 * @since 1.0.0
	 */
	private $actions = array(
		'duplicate_form'				=> array( 'nopriv' => false ),
		'delete_responses'				=> array( 'nopriv' => false ),
		'get_editor_html'				=> array( 'nopriv' => false ),
		'get_email_notification_html'	=> array( 'nopriv' => false ),
		'check_fngrprnt'				=> array( 'nopriv' => true ), // note: this is executed inline, not in a JS file
		'add_participants_allmembers'	=> array( 'nopriv' => false ),
		'invite_participants'			=> array( 'nopriv' => false ),
		'show_entries'					=> array( 'nopriv' => false ),
		'show_entry'					=> array( 'nopriv' => false ),
		'get_invite_text'				=> array( 'nopriv' => false ),
		'get_participants_list'			=> array( 'nopriv' => false ),
		'delete_all_participants'		=> array( 'nopriv' => false ),
		'delete_participant'		    => array( 'nopriv' => false ),
	);

	private $nonces = array();

	/**
	 * Torro_AJAX constructor
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		add_action( 'admin_init', array( $this, 'add_actions' ) );
	}

	/**
	 * Adding actions to WP AJAX engine
	 *
	 * @since 1.0.0
	 */
	public function add_actions() {
		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX ) {
			return;
		}

		$this->actions = apply_filters( 'torro_ajax_actions', $this->actions );
		foreach ( $this->actions as $action => $data ) {
			if ( ! is_array( $data ) ) {
				$data = array();
			}
			$nopriv = ( isset( $data['nopriv'] ) && $data['nopriv'] ) ? true : false;

			add_action( 'wp_ajax_torro_' . $action, array( $this, 'request' ) );
			if ( $nopriv ) {
				add_action( 'wp_ajax_nopriv_torro_' . $action, array( $this, 'request' ) );
			}
		}
	}

	/**
	 * Doing a request
	 *
	 * @since 1.0.0
	 */
	public function request() {
		$action = str_replace( 'torro_', '', $_REQUEST['action'] );

		if ( ! isset( $this->actions[ $action ] ) ) {
			wp_send_json_error( __( 'Invalid action.', 'torro-forms' ) );
		}

		$callback = ( is_array( $this->actions[ $action ] ) && isset( $this->actions[ $action ]['callback'] ) && $this->actions[ $action ]['callback'] ) ? $this->actions[ $action ]['callback'] : array( $this, 'ajax_' . $action );

		if ( ! is_callable( $callback ) ) {
			wp_send_json_error( __( 'Invalid action callback.', 'torro-forms' ) );
		}

		if ( ! isset( $_REQUEST['nonce'] ) ) {
			wp_send_json_error( __( 'Missing nonce.', 'torro-forms' ) );
		}

		if ( ! check_ajax_referer( $this->get_nonce_action( $action ), 'nonce', false ) ) {
			wp_send_json_error( __( 'Invalid nonce.', 'torro-forms' ) );
		}

		$response = call_user_func( $callback, $_REQUEST );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( $response->get_error_message() );
		}

		wp_send_json_success( $response );
	}

	/**
	 * Getting nonce
	 *
	 * @param $action
	 *
	 * @return mixed
	 * @since 1.0.0
	 */
	public function get_nonce( $action ) {
		if ( ! isset( $this->nonces[ $action ] ) ) {
			$this->nonces[ $action ] = wp_create_nonce( $this->get_nonce_action( $action ) );
		}
		return $this->nonces[ $action ];
	}

	/**
	 * Getting nonce action
	 *
	 * @param $action
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_nonce_action( $action ) {
		return 'torro_ajax_' . $action;
	}

	/**
	 * Dublicating form
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_duplicate_form( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_duplicate_form_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		$form_id = absint( $data['form_id'] );

		$form = get_post( $form_id );

		if ( 'torro-forms' !== $form->post_type ) {
			return new Torro_Error( 'ajax_duplicate_form_invalid_form', __( 'The post is not a form.', 'torro-forms' ) );
		}

		$form = new Torro_Form( $form_id );
		$new_form_id = $form->duplicate( true, true, false, true, true, true, true );

		$post = get_post( $new_form_id );

		$response = array(
			'form_id'    => $new_form_id,
			'post_title' => $post->post_title,
			'admin_url'  => admin_url( 'post.php?post=' . $new_form_id . '&action=edit' ),
		);

		return $response;
	}

	/**
	 * Deleting responses of a form
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_delete_responses( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_delete_responses_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		$form_id = absint( $data[ 'form_id' ] );

		$form = get_post( $form_id );

		if ( 'torro-forms' !== $form->post_type ) {
			return new Torro_Error( 'ajax_delete_responses_invalid_form', __( 'The post is not a form.', 'torro-forms' ) );
		}

		$form = new Torro_form( $form_id );
		$form->delete_responses();

		$entries = torro()->resulthandlers()->get_registered( 'entries' );
		if ( is_wp_error( $entries ) ) {
			return new Torro_Error( 'ajax_delete_responses_entries_error', __( 'Error retrieving the entries handler.', 'torro-forms' ) );
		}

		$response = array(
			'form_id'	=> $form_id,
			'deleted'	=> true,
			'html'		=> $entries->show_not_found_notice(),
		);

		return $response;
	}

	/**
	 * Get editor HTML
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_get_editor_html( $data ) {
		if ( ! isset( $data['element_id'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_element_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'element_id' ) );
		}

		if ( ! isset( $data['field_name'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_field_name_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'field_name' ) );
		}

		$editor_id = 'wp_editor_' . $data['element_id'];
		$field_name = $data['field_name'];
		$message = isset( $data['message'] ) ? $data['message'] : '';

		return Torro_AJAX_WP_Editor::get( $message, $editor_id, array(
			'textarea_name'		=> $field_name,
		) );
	}

	/**
	 * Get email notification html
	 *
	 * @param $data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function ajax_get_email_notification_html( $data ) {
		$id = time();
		$editor_id = 'email_notification_message-' . $id;

		$html = torro()->actions()->get_registered( 'emailnotifications' )->get_notification_settings_html( '_AJAX_' . $id, __( 'New Email Notification' ) );

		$response = Torro_AJAX_WP_Editor::get( '', $editor_id );

		$response['id'] = $id;
		$response['html'] = str_replace( '<% wp_editor %>', $response['html'], $html );

		return $response;
	}

	/**
	 * Checking fingerprint of a user
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_check_fngrprnt( $data ) {
		global $wpdb, $torro_skip_fingerrint_check;

		if ( ! isset( $data['torro_form_id'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_torro_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'torro_form_id' ) );
		}

		if ( ! isset( $data['fngrprnt'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_form_process_error', __( 'Error on processing form.', 'torro-forms' ) );
		}

		if ( ! isset( $data['form_action_url'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_form_action_url_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_action_url' ) );
		}

		$content = '';

		$torro_form_id = $data['torro_form_id'];
		$fingerprint = $data['fngrprnt'];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id=%d AND cookie_key=%s", $torro_form_id, $fingerprint );
		$count = absint( $wpdb->get_var( $sql ) );

		if ( 0 === $count ) {
			$torro_skip_fingerrint_check = true;

			$content .= torro()->forms()->get( $torro_form_id )->html( $data['form_action_url'] );

		} else {
			$content .= '<div class="form-message error">' . esc_html__( 'You have already entered your data.', 'torro-forms' ) . '</div>';
		}

		$response = array(
			'html'	=> $content,
		);

		return $response;
	}

	/**
	 * Adding all members to participants list
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_add_participants_allmembers( $data ) {
		global $wpdb;

		if( ! isset( $data[ 'form_id' ] ) ){
			return new Torro_Error( 'torro_ajax_add_participants_allmembers_no_form_id', __( 'No form ID provided.', 'torro-forms') );
		}

		$form_id = $data[ 'form_id' ];
		$users = get_users( array( 'orderby' => 'ID' ) );
		$response = array();

		foreach ( $users as $user ) {
			$sql = $wpdb->prepare( "SELECT count(*) FROM {$wpdb->torro_participants} WHERE form_id = %d AND user_id = %d ", $form_id, $user->ID );

			if( 0 !== (int) $wpdb->get_var( $sql ) ){
				continue;
			}

			$wpdb->insert(
				$wpdb->torro_participants,
				array(
					'form_id' => $form_id,
					'user_id' => $user->ID,
				),
				array(
					'%d',
					'%d'
				)
			);

			$response[ 'participants' ][] = array(
				'id'			=> $user->ID,
				'user_nicename'	=> $user->user_nicename,
				'display_name'	=> $user->display_name,
				'user_email'	=> $user->user_email,
			);
		}

		$html  = torro()->access_controls()->get_registered( 'selectedmembers' )->get_participants_list_html( $form_id );
		$response[ 'html' ] = $html;

		return $response;
	}

	/**
	 * Inivite participants by sending out emails
	 *
	 * @param $data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function ajax_invite_participants( $data ) {
		global $wpdb;

		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['from_name'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_from_name_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'from_name' ) );
		}

		if ( ! isset( $data['from'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_from_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'from' ) );
		}

		if ( ! isset( $data['subject'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_subject_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'subject' ) );
		}

		if ( ! isset( $data['text'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_text_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'text' ) );
		}

		if ( ! isset( $data['invitation_type'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_invitation_type_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'invitation_type' ) );
		}

		$response = array(
			'sent'	=> false,
			'users'	=> array(),
		);

		$form_id = $data['form_id'];
		$from_name = $data['from_name'];
		$from = $data['from'];
		$subject = $data['subject'];
		$text = $data['text'];

		$sql = "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$user_ids = $wpdb->get_col( $sql );

		if ( 'reinvite' === $data[ 'invitation_type' ] ) {
			$user_ids_new = '';
			if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
				foreach ( $user_ids as $user_id ) {
					if ( ! torro()->forms()->get( $form_id )->has_participated( $user_id ) ) {
						$user_ids_new[] = $user_id;
					}
				}
			}
			$user_ids = $user_ids_new;
		}

		$post = get_post( $form_id );

		if ( is_array( $user_ids ) && count( $user_ids ) > 0 ) {
			$users = get_users( array(
				'include'	=> $user_ids,
				'orderby'	=> 'ID',
			) );

			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			$content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );

			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			$subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );

			foreach ( $users as $user ) {
				$response['users'][] = $user->ID;
				if ( ! empty( $user->data->display_name ) ) {
					$display_name = $user->data->display_name;
				} else {
					$display_name = $user->data->user_nicename;
				}

				$user_nicename = $user->data->user_nicename;
				$user_email = $user->data->user_email;

				$subject_user = str_replace( '%displayname%', $display_name, $subject );
				$subject_user = str_replace( '%username%', $user_nicename, $subject_user );

				$content_user = str_replace( '%displayname%', $display_name, $content );
				$content_user = str_replace( '%username%', $user_nicename, $content_user );

				torro_mail( $user_email, $subject_user, stripslashes( $content_user ), $from_name, $from );
			}

			$response['sent'] = true;
		}

		return $response;
	}

	/**
	 * Showing entry list
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_show_entries( $data ) {
		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_show_entries_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['start'] ) ) {
			return new Torro_Error( 'ajax_show_entries_start_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'start' ) );
		}

		if ( ! isset( $data['length'] ) ) {
			return new Torro_Error( 'ajax_show_entries_length_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'length' ) );
		}

		$form_id = $data['form_id'];
		$start = $data['start'];
		$length = $data['length'];

		$form_results = new Torro_Form_Results( $form_id );
		$form_results->results();
		$num_results = $form_results->count();

		$response = array(
			'html'	=> torro()->resulthandlers()->get_registered( 'entries' )->show_results( $form_id, $start, $length, $num_results ),
		);

		return $response;
	}

	/**
	 * Showing single entry
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 * @todo Moving table HTML to entry class
	 */
	public function ajax_show_entry( $data ) {
		global $wpdb;

		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['result_id'] ) ) {
			return new Torro_Error( 'ajax_show_entry_result_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'result_id' ) );
		}

		$form_id = $data['form_id'];
		$result_id = $data['result_id'];

		if ( ! torro()->forms()->get( $form_id)->exists() ) {
			return array(
				'html'	=> __( 'Form not found.', 'torro-forms' ),
			);
		}

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE id = %d", $result_id );
		$count_results = $wpdb->get_var( $sql );

		if ( 0 === $count_results ) {
			return array(
				'html'	=> __( 'Entry not found.', 'torro-forms' ),
			);
		}

		$filter = array(
			'result_ids' => array( $result_id ),
		);

		$form_results = new Torro_Form_Results( $form_id );
		$results = $form_results->results( $filter );

		if ( 0 < $form_results->count() ) {
			foreach ( $results as $result ) {
				if ( ! array_key_exists( 'result_id', $result ) ) {
					return array(
						'html'	=> __( 'Error on getting Result.', 'torro-forms' ),
					);
				}

				$html = '<table id="torro-entry-table" class="widefat">';

				$html .= '<thead>';
				$html .= '<tr>';
				$html .= '<th>' . esc_html__( 'Label', 'torro-forms' ) . '</th>';
				$html .= '<th>' . esc_html__( 'Value', 'torro-forms' ) . '</th>';
				$html .= '</tr>';
				$html .= '</thead>';

				$html .= '<tbody>';

				$extra_info = '';

				foreach ( $result as $column_name => $value ) {
					switch ( $column_name ) {
						case 'result_id':
							$result_id = $value;
							break;
						case 'user_id':
							if ( -1 !== (int) $value ) {
								$user_id = $value;
								$user = get_user_by( 'id', $user_id );
								$extra_info .= ' - ' . esc_attr__( 'User', 'torro-forms' ) . ' ' . $user->user_nicename;
							}
							break;
						case 'timestamp':
							$timestamp = $value;
							$date_string = date_i18n( get_option( 'date_format' ), $timestamp );
							$time_string = date_i18n( get_option( 'time_format' ), $timestamp );
							break;
						default:
							$column_arr = explode( '_', $column_name );
							// On Elements
							if ( array_key_exists( 0, $column_arr ) && 'element' === $column_arr[0] ) {
								$element_id = $column_arr[ 1 ];
								$element = torro()->elements()->get( $element_id );

								$column_name = $element->replace_column_name( $column_name );

								if ( empty( $column_name ) ) {
									$column_name = $element->label;
								}

								if ( 'yes' === $value ) {
									$value = esc_attr__( 'Yes', 'torro-forms' );
								} elseif( 'no' == $value ) {
									$value = esc_attr__( 'No', 'torro-forms' );
								}

								$html .= '<tr>';
								$html .= '<td>' . $column_name . '</td>';
								$html .= '<td>' . $value . '</td>';
								$html .= '</tr>';
							}
							break;
					}
				}
				$html .= '</tbody>';
				$html .= '<tfoot>';
				$html .= '<tr>';
				$html .= '<td colspan="2"><small>' . esc_html__( 'Date', 'torro-forms' ) . ' ' . $date_string . ' - ' . esc_html__( 'Time', 'torro-forms' ) . ' ' . $time_string . $extra_info . '</small></td>';
				$html .= '</tr>';
				$html .= '</tfoot>';
				$html .= '</table>';
			}
		} else {
			$html = __( 'Entry not found.', 'torro-forms' );
		}

		$html .= '<div id="torro-entry-buttons">';
		$html .= '<input type="button" class="button torro-hide-entry" value="' . esc_attr__( 'Back to Results', 'torro-forms' ) . '">';
		$html .= '</div>';

		$response = array(
			'html'	=> $html,
		);

		return $response;
	}

	/**
	 * Getting invitation texts from text templates
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function ajax_get_invite_text(){
		$invite_type = $_POST[ 'invite_type' ];
		$invite_from_name = '';
		$invite_from = '';
		$invite_subject = '';
		$invite_text = '';

		switch( $invite_type ){
			case 'invite':
				$invite_from_name = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'invite_from_name' ];
				$invite_from = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'invite_from' ];
				$invite_subject = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'invite_subject' ];
				$invite_text = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'invite_text' ];
				break;

			case 'reinvite':
				$invite_from_name = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'reinvite_from_name' ];
				$invite_from = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'reinvite_from' ];
				$invite_subject = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'reinvite_subject' ];
				$invite_text = torro()->access_controls()->get_registered( 'selectedmembers' )->settings[ 'reinvite_text' ];
				break;
		}

		$response = array(
			'invite_from_name' => $invite_from_name,
			'invite_from' => $invite_from,
			'invite_subject' => $invite_subject,
			'invite_text' => $invite_text
		);

		return $response;
	}

	/**
	 * Getting larticipant list
	 *
	 * @param $data
	 *
	 * @return array
	 * @since 1.0.0
	 */
	public function ajax_get_participants_list( $data ){
		$form_id = $data[ 'form_id' ];
		$start = $data[ 'start' ];
		$length = $data[ 'length' ];
		$num_results = $data[ 'num_results' ];

		if( empty( $start ) ){
			$start = 0;
		}

		if( empty( $length ) ){
			$length = 10;
		}

		$table = torro()->access_controls()->get_registered( 'selectedmembers' )->get_participants_list_table_html( $form_id, $start, $length );
		$navi = torro()->access_controls()->get_registered( 'selectedmembers' )->get_navigation_html( $form_id, $start, $length, $num_results );

		if( is_wp_error( $table ) ){
			return $table;
		}
		if( is_wp_error( $navi ) ){
			return $navi;
		}

		$response = array(
			'table' => $table,
			'navi' => $navi,
		);

		return $response;
	}

	/**
	 * Deleting all participants from survey
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_delete_all_participants( $data ){
		global $wpdb;

		$form_id = $data[ 'form_id' ];

		if( empty( $form_id ) ){
			return new Torro_Error( 'torro_participants_delete_all_missing_form_id', __( 'Missing form ID.', 'torro-forms') );
		}

		$sql = $wpdb->prepare( "SELECT user_id FROM {$wpdb->torro_participants} WHERE form_id = %d", $form_id );
		$results = $wpdb->get_col( $sql );

		$deleted = $wpdb->delete( $wpdb->torro_participants, array( 'form_id' => $form_id ), array( '%d' ) );

		if( false === $deleted ){
			return new Torro_Error( 'torro_participants_delete_all_failed', __( 'Failed to delete participants.', 'torro-forms') );
		}

		$start = 0;
		$length = 10;
		$num_results = 0;

		$table = torro()->access_controls()->get_registered( 'selectedmembers' )->get_participants_list_table_html( $form_id, $start, $length );
		$navi = torro()->access_controls()->get_registered( 'selectedmembers' )->get_navigation_html( $form_id, $start, $length, $num_results );

		return array(
			'user_ids'  => $results,
			'table'     => $table,
			'navi'      => $navi,
		);
	}

	/**
	 * Deleting a participant
	 *
	 * @param $data
	 *
	 * @return array|Torro_Error
	 * @since 1.0.0
	 */
	public function ajax_delete_participant( $data ){
		global $wpdb;

		$form_id = $data[ 'form_id' ];
		$user_id = $data[ 'user_id' ];

		if( empty( $form_id ) ){
			return new Torro_Error( 'torro_participants_delete_missing_form_id', __( 'Missing form ID.', 'torro-forms') );
		}
		if( empty( $user_id ) ){
			return new Torro_Error( 'torro_participants_delete_missing_user_id', __( 'Missing user ID.', 'torro-forms') );
		}

		$deleted = $wpdb->delete( $wpdb->torro_participants, array( 'form_id' => $form_id, 'user_id' => $user_id ), array( '%d', '%d' ) );

		if( false ===  $deleted ){
			return new Torro_Error( 'torro_participants_delete_all_failed', __( 'Failed to delete participants.', 'torro-forms') );
		}

		$start = 0;
		if( isset( $data[ 'start' ] ) ){
			$start = $data[ 'start' ];
		}

		$length = 10;
		if( isset( $data[ 'length' ] ) ){
			$length = $data[ 'length' ];
		}

		$num_results = 0;
		if( isset( $data[ 'num_results' ] ) ){
			$num_results = $data[ 'num_results' ] -1;
		}

		$table = torro()->access_controls()->get_registered( 'selectedmembers' )->get_participants_list_table_html( $form_id, $start, $length );
		$navi = torro()->access_controls()->get_registered( 'selectedmembers' )->get_navigation_html( $form_id, $start, $length, $num_results );

		return array(
			'user_id' => $user_id,
			'table'     => $table,
			'navi'      => $navi,
		);
	}
}
Torro_AJAX::instance();
