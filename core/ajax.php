<?php
/**
 * The AJAX handler class.
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Core
 * @version 1.0.0
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
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

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
	);

	private $nonces = array();

	private function __construct() {
		add_action( 'admin_init', array( $this, 'add_actions' ) );
	}

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

	public function get_nonce( $action ) {
		if ( ! isset( $this->nonces[ $action ] ) ) {
			$this->nonces[ $action ] = wp_create_nonce( $this->get_nonce_action( $action ) );
		}
		return $this->nonces[ $action ];
	}

	private function get_nonce_action( $action ) {
		return 'torro_ajax_' . $action;
	}

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
		$new_form_id = $form->delete_responses();

		$entries = torro()->resulthandlers()->get( 'entries' );
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

	public function get_editor_html( $data ) {
		if ( ! isset( $data['widget_id'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_widget_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'widget_id' ) );
		}

		if ( ! isset( $data['editor_id'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_editor_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'editor_id' ) );
		}

		if ( ! isset( $data['field_name'] ) ) {
			return new Torro_Error( 'ajax_get_editor_html_field_name_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'field_name' ) );
		}

		$widget_id = $data['widget_id'];
		$editor_id = $data['editor_id'];
		$field_name = $data['field_name'];
		$message = isset( $data['message'] ) ? $data['message'] : '';

		$html = Torro_WPEditorBox::editor( $message, $editor_id, $field_name );

		$response = array(
			'widget_id' => $widget_id,
			'editor_id' => $editor_id,
			'html'      => $html,
		);

		return $response;
	}

	public function ajax_get_email_notification_html( $data ) {
		$id = time();
		$editor_id = 'email_notification_message-' . $id;

		$html = torro()->actions()->get( 'emailnotifications' )->get_notification_settings_html( $id, __( 'New Email Notification' ) );

		$response = array(
			'id'		=> $id,
			'editor_id'	=> $editor_id,
			'html'		=> $html,
		);

		return $response;
	}

	public function ajax_check_fngrprnt( $data ) {
		global $wpdb, $ar_form_id, $torro_skip_fingerrint_check;

		if ( ! isset( $data['torro_form_id'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_torro_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'torro_form_id' ) );
		}

		if ( ! isset( $data['fngrprnt'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_form_process_error', __( 'Error on processing form.', 'torro-forms' ) );
		}

		if ( ! isset( $data['action_url'] ) ) {
			return new Torro_Error( 'ajax_check_fngrprnt_action_url_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'action_url' ) );
		}

		$content = '';

		$ar_form_id = $data['torro_form_id'];
		$fingerprint = $data['fngrprnt'];

		$sql = $wpdb->prepare( "SELECT COUNT(*) FROM $wpdb->torro_results WHERE form_id=%d AND cookie_key=%s", $ar_form_id, $fingerprint );
		$count = absint( $wpdb->get_var( $sql ) );

		if ( 0 === $count ) {
			$torro_skip_fingerrint_check = true;

			$torro_form_process = new Torro_Form_Controller( $ar_form_id, $data['action_url'] );
			$content .= $torro_form_process->show_form();
		} else {
			$content .= '<div class="form-message error">' . esc_html__( 'You have already entered your data.', 'torro-forms' ) . '</div>';
		}

		$response = array(
			'html'	=> $content,
		);

		return $response;
	}

	public function ajax_add_participants_allmembers( $data ) {
		$users = get_users( array( 'orderby' => 'ID' ) );

		$response = array();

		foreach ( $users as $user ) {
			$response[] = array(
				'id'			=> $user->ID,
				'user_nicename'	=> $user->user_nicename,
				'display_name'	=> $user->display_name,
				'user_email'	=> $user->user_email,
			);
		}

		return $response;
	}

	public function ajax_invite_participants( $data ) {
		global $wpdb;

		if ( ! isset( $data['form_id'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_form_id_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'form_id' ) );
		}

		if ( ! isset( $data['subject_template'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_subject_template_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'subject_template' ) );
		}

		if ( ! isset( $data['text_template'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_text_template_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'text_template' ) );
		}

		if ( ! isset( $data['invitation_type'] ) ) {
			return new Torro_Error( 'ajax_invite_participants_invitation_type_missing', sprintf( __( 'Field %s is missing.', 'torro-forms' ), 'invitation_type' ) );
		}

		$response = array(
			'sent'	=> false,
			'users'	=> array(),
		);

		$form_id = $data['form_id'];
		$subject_template = $data['subject_template'];
		$text_template = $data['text_template'];

		$sql = "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %d";
		$sql = $wpdb->prepare( $sql, $form_id );
		$user_ids = $wpdb->get_col( $sql );

		if ( 'reinvite' === $data['invitation_type'] ) {
			$user_ids_new = '';
			if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
				foreach ( $user_ids as $user_id ) {
					if ( ! torro_user_has_participated( $form_id, $user_id ) ) {
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

			$content = str_replace( '%site_name%', get_bloginfo( 'name' ), $text_template );
			$content = str_replace( '%survey_title%', $post->post_title, $content );
			$content = str_replace( '%survey_url%', get_permalink( $post->ID ), $content );

			$subject = str_replace( '%site_name%', get_bloginfo( 'name' ), $subject_template );
			$subject = str_replace( '%survey_title%', $post->post_title, $subject );
			$subject = str_replace( '%survey_url%', get_permalink( $post->ID ), $subject );

			foreach ( $users as $user ) {
				$data['users'][] = $user->ID;
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

				torro_mail( $user_email, $subject_user, stripslashes( $content_user ) );
			}

			$response['sent'] = true;
		}

		return $response;
	}

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
		$results = $form_results->results();
		$num_results = $form_results->count();

		$response = array(
			'html'	=> torro()->resulthandlers()->get( 'entries' )->show_results( $form_id, $start, $length, $num_results ),
		);

		return $response;
	}

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

		if ( ! torro_form_exists( $form_id ) ) {
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

				$result_id = '';
				$user_id = '';
				$timestamp = '';
				$extra_info = '';

				foreach ( $result as $column_name => $value ) {
					switch ( $column_name ) {
						case 'result_id':
							$result_id = $value;
							break;
						case 'user_id':
							if ( ! empty( $value ) ) {
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
}

Torro_AJAX::instance();
