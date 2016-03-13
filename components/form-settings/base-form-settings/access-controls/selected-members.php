<?php
/**
 * Member list Access controll
 *
 * @author  awesome.ug, Author <support@awesome.ug>
 * @package TorroForms/Actions
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

final class Torro_Access_Control_Selected_Members extends Torro_Access_Control {

	/**
	 * Instance
	 *
	 * @var null
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return null|Torro_Access_Control_Selected_Members
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->title = __( 'Selected Members', 'torro-forms' );
		$this->name = 'selectedmembers';

		$this->option_name = __( 'Selected Members of site', 'torro-forms' );

		add_action( 'torro_formbuilder_save', array( $this, 'save' ), 10, 1 );

		$this->settings_fields = array(
			'invitations'			=> array(
				'title'					=> __( 'Invitation Mail Template', 'torro-forms' ),
				'description'			=> __( 'Setup Mail Templates for the Invitation Mail for selected Members.', 'torro-forms' ),
				'type'					=> 'title',
			),
			'invite_from_name'		=> array(
				'title'					=> __( 'From Name', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Name.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_bloginfo( 'name' ),
			),
			'invite_from'			=> array(
				'title'					=> __( 'From Email', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'invite_subject'		=> array(
				'title'					=> __( 'Subject', 'torro-forms' ),
				'description'			=> __( 'The Subject of the Mail.', 'torro-forms' ),
				'type'					=> 'text',
			),
			'invite_text'			=> array(
				'title'					=> __( 'Email Text', 'torro-forms' ),
				'description'			=> __( 'The Text of the Mail.', 'torro-forms' ),
				'type'					=> 'wp_editor',
			),
			'reinvitations'			=> array(
				'title'					=> __( 'Reinvitation Mail Template', 'torro-forms' ),
				'description'			=> __( 'Setup Mail Templates for the Reinvitation Mail for selected Members.', 'torro-forms' ),
				'type'					=> 'title',
			),
			'reinvite_from_name'	=> array(
				'title'					=> __( 'From Name', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Name.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_bloginfo( 'name' ),
			),
			'reinvite_from'			=> array(
				'title'					=> __( 'From Email', 'torro-forms' ),
				'description'			=> __( 'The Mail Sender Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'reinvite_subject'		=> array(
				'title'					=> __( 'Subject', 'torro-forms' ),
				'description'			=> __( 'The Subject of the Email.', 'torro-forms' ),
				'type'					=> 'text',
				'default'				=> get_option( 'admin_email' ),
			),
			'reinvite_text'			=> array(
				'title'					=> __( 'Email Text', 'torro-forms' ),
				'description'			=> __( 'The Text of the Mail.', 'torro-forms' ),
				'type'					=> 'wp_editor',
			)
		);
	}

	/**
	 * Saving form data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		update_post_meta( $form_id, 'invite_from_name', $_POST[ 'invite_from_name' ] );
		update_post_meta( $form_id, 'invite_from', $_POST[ 'invite_from' ] );
		update_post_meta( $form_id, 'invite_subject', $_POST[ 'invite_subject' ] );
		update_post_meta( $form_id, 'invite_text', $_POST[ 'invite_text' ] );

		update_post_meta( $form_id, 'reinvite_from_name', $_POST[ 'reinvite_from_name' ] );
		update_post_meta( $form_id, 'reinvite_from', $_POST[ 'reinvite_from' ] );
		update_post_meta( $form_id, 'reinvite_subject', $_POST[ 'reinvite_subject' ] );
		update_post_meta( $form_id, 'reinvite_text', $_POST[ 'reinvite_text' ] );

		/**
		 * Saving access-control options
		 */
		if ( array_key_exists( 'form_access_controls_selectedmembers_same_users', $_POST ) ) {
			$access_controls_same_users = $_POST['form_access_controls_selectedmembers_same_users'];
			update_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', $access_controls_same_users );
		} else {
			update_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', '' );
		}

		/**
		 * Saving access-control options
		 */
		$add_participants_option = $_POST['form_add_participants_option'];
		update_post_meta( $form_id, 'add_participants_option', $add_participants_option );
	}

	/**
	 * Enqueue Scripts
	 *
	 * @since 1.0.0
	 */
	public function admin_scripts() {
		$translation = array(
			'delete'								=> __( 'Delete', 'torro-forms' ),
			'yes'									=> __( 'Yes', 'torro-forms' ),
			'no'									=> __( 'No', 'torro-forms' ),
			'just_added'							=> __( 'just added', 'torro-forms' ),
			'invitations_sent_successfully'			=> __( 'Invitations sent successfully!', 'torro-forms' ),
			'invitations_not_sent_successfully'		=> __( 'Invitations could not be sent!', 'torro-forms' ),
			'reinvitations_sent_successfully'		=> __( 'Renvitations sent successfully!', 'torro-forms' ),
			'reinvitations_not_sent_successfully'	=> __( 'Renvitations could not be sent!', 'torro-forms' ),
			'added_participants'					=> __( 'participant/s', 'torro-forms' ),
			'nonce_add_participants_allmembers'		=> torro()->ajax()->get_nonce( 'add_participants_allmembers' ),
			'nonce_invite_participants'				=> torro()->ajax()->get_nonce( 'invite_participants' ),
			'nonce_get_invite_text'                 => torro()->ajax()->get_nonce( 'get_invite_text' ),
			'nonce_get_participants_list'           => torro()->ajax()->get_nonce( 'get_participants_list' ),
			'nonce_delete_all_participants'           => torro()->ajax()->get_nonce( 'delete_all_participants' ),
			'nonce_delete_participant'           => torro()->ajax()->get_nonce( 'delete_participant' ),
		);

		wp_enqueue_script( 'torro-access-controls-selected-members', torro()->get_asset_url( 'access-controls-selected-members', 'js' ), array( 'torro-form-edit' ) );
		wp_localize_script( 'torro-access-controls-selected-members', 'translation_sm', $translation );
	}

	/**
	 * Registers and enqueues admin-specific styles.
	 *
	 * @since 1.0.0
	 */
	public function admin_styles() {
		wp_enqueue_style( 'torro-access-controls-selected-members', torro()->get_asset_url( 'access-controls-selected-members', 'css' ), array( 'torro-form-edit' ) );
	}

	/**
	 * Adds content to the option
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content() {
		$html  = '<div id="torro-acl-selectedmembers">';
		$html .= $this->get_participants_options_html();

		$html .= '<div id="torro-invite-actions" class="form-fields">';
		$html .= $this->get_invite_actions_html();
		$html .= $this->get_invitation_html();
		$html .= '</div>';
		$html .= $this->get_participants_list_html();
		$html .= '</div>';

		return $html;
	}

	/**
	 * Add participants HTML
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_participants_options_html(){
		global $post;

		$form_id = $post->ID;

		$options = apply_filters( 'form_add_participants_options', array( 'allmembers' => __( 'all members', 'torro-forms' ) ) );
		$add_participants_option = get_post_meta( $form_id, 'add_participants_option', true );

		$html  = '<div id="torro-add-participants-options" class="form-fields">';
		$html .= '<label for"form_add_participants_option">' . esc_html__( 'Members', 'torro-forms' ) . '</label>';
		$html .= '<select id="form-add-participants-option" name="form_add_participants_option">';
		foreach ( $options as $name => $value ) {
			$selected = '';
			if ( $name === $add_participants_option ) {
				$selected = ' selected="selected"';
			}
			$html .= '<option value="' . $name . '"' . $selected . '>' . $value . '</option>';
		}
		$html .= '</select> ';
		$html .= '<input type="button" class="form-add-participants-button button" id="form-add-participants-button" value="' . esc_attr__( 'Add', 'torro-forms' ) . '" />';
		$html .= '</div>';

		$access_controls_same_users = get_post_meta( $form_id, 'form_access_controls_selectedmembers_same_users', true );
		$checked = 'yes' === $access_controls_same_users ? ' checked' : '';

		$html .= '<div id="torro-user-filter" class="form-fields">';
		$html .= '<label for="form_access_controls_selectedmembers_same_users">' . esc_html__( 'Filter', 'torro-forms' ) . '</label>';
		$html .= '<input type="checkbox" name="form_access_controls_selectedmembers_same_users" value="yes" ' . $checked . '/>';
		$html .= esc_html__( 'Prevent multiple entries from same User', 'torro-forms' );
		$html .= '</div>';

		return $html;
	}

	/**
	 * Invite actions HTML
	 *
	 * @return string
	 * @since 1.0.0
	 */
	private function get_invite_actions_html(){
		$html  = '<div style="clear: both;"></div>';
		$html .= '<label for"torro-invite-participants">' . esc_html__( 'Invitations', 'torro-forms' ) . '</label>';
		$html .= '<input type="button" id="torro-invite-participants-button" name="invite_participants" value="' . esc_html__( 'Invite', 'torro-forms' ) . '" class="button" /> ';
		$html .= '<input type="button" id="torro-reinvite-participants-button" name="reinvite_participants" value="' . esc_html__( 'Reinvite', 'torro-forms' ) . '" class="button" /> ';
		$html .= '<input type="button" id="torro-send-invitations-button" name="send_invitations" value="' . esc_html__( 'Send Invitations', 'torro-forms' ) . '" class="button-primary" /> ';
		$html .= '<input type="button" id="invite-close" class="button" value="' . esc_html__( 'Close', 'torro-forms' ) . '" />';
		$html .= '<div id="invites-send-request-text">' . esc_html__( 'Do you really want to send invitation emails to all members of this list?', 'torro-forms' ) . '</div>';

		return $html;
	}

	/**
	 * Invite email HTML
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_invitation_html(){
		global $post;

		$form_id = $post->ID;

		$editor_settings = array(
			'editor_height' => 400
		);

		$invite_from_name = get_post_meta( $form_id, 'invite_from_name', true );
		$invite_from = get_post_meta( $form_id, 'invite_from', true );
		$invite_subject = get_post_meta( $form_id, 'invite_subject', true );
		$invite_text = get_post_meta( $form_id, 'invite_text', true );

		ob_start();
		wp_editor( $invite_text, 'invite_text', $editor_settings );
		$editor = ob_get_clean();

		$html  = '<div id="torro-invite-email">';
		$html .= '<div>';
		$html .= '<div class="form-fields"><label for="invite_from_name">' . esc_attr__( 'From', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="invite_from_name" value="' . $invite_from_name . '" /></div>';
		$html .= '<div class="form-fields"><label for="invite_from">' . esc_attr__( 'Email', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="invite_from" value="' . $invite_from . '" /></div>';
		$html .= '<div class="form-fields"><label for="invite_subject">' . esc_attr__( 'Subject', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="invite_subject" value="' . $invite_subject . '" /></div>';
		$html .= '</div>';
		$html .= '<div id="torro-invite-text">';
		$html .= $editor;
		$html .= '</div>';
		$html .= '</div>';

		$reinvite_from_name = get_post_meta( $form_id, 'reinvite_from_name', true );
		$reinvite_from = get_post_meta( $form_id, 'reinvite_from', true );
		$reinvite_subject = get_post_meta( $form_id, 'reinvite_subject', true );
		$reinvite_text = get_post_meta( $form_id, 'reinvite_text', true );

		ob_start();
		wp_editor( $reinvite_text, 'reinvite_text', $editor_settings );
		$editor = ob_get_clean();

		$html .= '<div id="torro-reinvite-email">';
		$html .= '<div>';
		$html .= '<div class="form-fields"><label for="reinvite_from_name">' . esc_attr__( 'From', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="reinvite_from_name" value="' . $reinvite_from_name . '" /></div>';
		$html .= '<div class="form-fields"><label for="reinvite_from">' . esc_attr__( 'Email', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="reinvite_from" value="' . $reinvite_from . '" /></div>';
		$html .= '<div class="form-fields"><label for="reinvite_subject">' . esc_attr__( 'Subject', 'torro-forms' ) .'</label>';
		$html .= '<input type="text" name="reinvite_subject" value="' . $reinvite_subject . '" /></div>';
		$html .= '</div>';
		$html .= '<div id="torro-reinvite-text">';
		$html .= $editor;
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Participiants List HTML
	 *
	 * @param int $form_id
	 * @param int $start
	 * @param int $length
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_participants_list_html( $form_id = null, $start = null, $length = null ){
		global $wpdb, $post;

		if( empty( $form_id ) ) {
			$form_id = $post->ID;
		}

		if ( empty( $start ) && array_key_exists( 'torro-entries-start', $_POST ) ) {
			$start = $_POST['torro-entries-start'];
		}

		if( empty( $start ) ){
			$start = 0;
		}

		if ( empty( $length ) && array_key_exists( 'torro-entries-start', $_POST ) ) {
			$length = $_POST['torro-entries-length'];
		}

		if( empty( $length ) ){
			$length = 10;
		}

		$sql = $wpdb->prepare( "SELECT count(*) FROM $wpdb->torro_participants WHERE form_id = %d", $form_id  );
		$num_results = $wpdb->get_var( $sql );

		$html  = '<div id="torro-participants" class="torro-table-nav">';

		$html .= '<div class="torro-slider">';
		$html .= '<div class="torro-slider-left"></div>';
		$html .= '<div class="torro-slider-middle">';
		$html .= $this->get_participants_list_table_html( $form_id, $start, $length );
		$html .= '</div>';
		$html .= '<div class="torro-slider-right"></div>';
		$html .= '</div>';

		$html .= '<div style="clear:both;"></div>';

		$html .= '<div class="torro-slider-navigation">';
		$html .= $this->get_navigation_html( $form_id, $start, $length, $num_results );
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Navigation HTML
	 *
	 * @param int $start
	 * @param int $length
	 * @param int $num_results
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_navigation_html( $form_id, $start, $length, $num_results ){
		$prev = $start - $length;
		$next = $start + $length;
		$count = $num_results <= $length ? $num_results : $length;

		$html = '<div class="torro-nav">';

		$html .= '<div class="torro-nav-prev-link">';
		if ( 0 <= $prev ) {

			$prev_url = $this->get_admin_url( $form_id, array(
				'torro-start'	=> $prev,
				'torro-length'	=> $length,
				'torro-num-results'	=> $num_results,
			) );
			$prev_link = sprintf( __( '<a href="%s" class="torro-nav-button button">Previous</a>', 'torro-forms' ), $prev_url );
			$html .= $prev_link;
		}
		$html .= '</div>';

		if( $num_results > 0 ) {
			$html .= '<div class="torro-nav-info">' . sprintf( esc_attr__( '%s - %s of %s', 'torro-forms' ), $start + 1, $count, $num_results ) . '</div>';
		}

		$html .= '<div class="torro-nav-next-link">';
		if ( $num_results > $next ) {
			$next_url = $this->get_admin_url( $form_id, array(
				'torro-start'	=> $next,
				'torro-length'	=> $length,
				'torro-num-results'	=> $num_results,
			) );
			$next_link = sprintf( __( '<a href="%s" class="torro-nav-button button">Next</a>', 'torro-forms' ), $next_url );
			$html .= $next_link;
		}
		$html .= '</div>';

		$html .= '</div>';

		return $html;
	}

	/**
	 * Returns the edit URL for a form, with optional query arguments
	 *
	 * @return string $url
	 * @since 1.0.0
	 */
	private function get_admin_url( $form_id, $args = array() ) {
		$admin_url = admin_url( 'post.php?post=' . $form_id . '&action=edit' );
		if ( 0 < count( $args ) ) {
			$admin_url = add_query_arg( $args, $admin_url );
		}

		return $admin_url;
	}

	/**
	 * Participants list HTML
	 *
	 * @param int $form_id
	 * @param int $start
	 * @param int $limit
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	private function get_participants_list_table_html( $form_id, $start, $limit ){
		global $wpdb;

		$sql = $wpdb->prepare( "SELECT count(*) FROM $wpdb->torro_participants WHERE form_id = %d", $form_id );
		$num_users = $wpdb->get_var( $sql );

		$sql = $wpdb->prepare( "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %s LIMIT %d, %d", $form_id, $start, $limit );
		$user_ids = $wpdb->get_col( $sql );

		/**
		 * Participiants list
		 */
		$html  = '<table class="wp-list-table widefat">';
		$html .= '<thead>';
		$html .= '<tr>';
		$html .= '<th class="column-one">' . esc_html__( 'ID', 'torro-forms' ) . '</th>';
		$html .= '<th class="column-fifteen">' . esc_html__( 'User nicename', 'torro-forms' ) . '</th>';
		$html .= '<th class="column-twenty">' . esc_html__( 'Display name', 'torro-forms' ) . '</th>';
		$html .= '<th class="column-twentyfive">' . esc_html__( 'Email', 'torro-forms' ) . '</th>';
		$html .= '<th class="column-fifteen">' . esc_html__( 'Status', 'torro-forms' ) . '</th>';
		$html .= '<th class="column-button"><a class="form-remove-all-participants">' . esc_html__( 'Delete all', 'torro-forms' ) . '</a></th>';
		$html .= '</tr>';
		$html .= '</thead>';

		$html .= '<tbody>';

		$form_participants_value = '';

		if ( is_array( $user_ids ) && 0 < count( $user_ids ) ) {
			// Content
			foreach ( $user_ids as $user_id ) {
				$user = get_user_by( 'ID', $user_id );

				if ( torro()->forms()->get( $form_id )->has_participated( $user->ID ) ) {
					$user_css = ' finished';
					$user_text = __( 'finished', 'torro-forms' );
				} else {
					$user_text = __( 'new', 'torro-forms' );
					$user_css = ' new';
				}

				$html .= '<tr class="participant participant-user-' . $user->ID . $user_css . '">';
				$html .= '<td>' . esc_html( $user_id ) . '</td>';
				$html .= '<td>' . esc_html( $user->user_nicename ) . '</td>';
				$html .= '<td>' . esc_html( $user->display_name ) . '</td>';
				$html .= '<td>' . esc_html( $user->user_email ) . '</td>';
				$html .= '<td>' . esc_html( $user_text ) . '</td>';
				$html .= '<td class="column-button"><a class="button form-delete-participant" data-user-id="' . $user->ID . '">' . esc_html__( 'Delete', 'torro-forms' ) . '</a></td>';
				$html .= '</tr>';
			}

			$form_participants_value = implode( ',', $user_ids );
		} else {
			$html .= '<tr class="no-users-found">';
			$html .= '<td colspan="6">' . esc_attr__( 'No Users found.', 'torro-forms' ) . '</td>';
			$html .= '</tr>';
		}

		$html .= '</tbody>';

		$html .= '<tfoot>';
		$html .= '<tr>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		$html .= '<td></td>';
		$html .= '<td>&nbsp;</td>';
		$html .= '</tr>';
		$html .= '</tfoot>';

		$html .= '</table>';

		$html .= '<div id="participants-delete-all-text">' . esc_attr__( 'Do you really want to delete all participants from list?', 'torro-forms' ) . '</div>';
		$html .= '<div id="participant-delete-text">' . esc_attr__( 'Do you really want to delete the participant from list?', 'torro-forms' ) . '</div>';
		$html .= '<input type="hidden" id="participants-start" name="form_participants" value="' . $start . '" />';
		$html .= '<input type="hidden" id="participants-length" name="form_participants" value="' . $limit . '" />';
		$html .= '<input type="hidden" id="participants-num-results" name="form_participants_count" value="' . $num_users . '" />';

		return $html;
	}

	/**
	 * Checks if the user can pass
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function check() {
		$torro_form_id = torro()->forms()->get_current_form_id();

		if ( ! is_user_logged_in() ) {
			$this->add_message( 'error', __( 'You have to be logged in to participate.', 'torro-forms' ) );

			return false;
		}

		if ( ! $this->is_participant() ) {
			$this->add_message( 'error', __( 'You are not allowed to participate.', 'torro-forms' ) );

			return false;
		}

		$access_controls_same_users = get_post_meta( $torro_form_id, 'form_access_controls_selectedmembers_same_users', true );

		if ( 'yes' === $access_controls_same_users && torro()->forms( $torro_form_id )->has_participated() ) {
			$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );

			return false;
		}

		return true;
	}

	/**
	 * Checks if a user can participate
	 *
	 * @param int $form_id
	 * @param int $user_id
	 *
	 * @return boolean $can_participate
	 * @since 1.0.0
	 */
	public function is_participant( $user_id = null ) {
		global $wpdb, $current_user;

		$torro_form_id = torro()->forms()->get_current_form_id();

		// Setting up user ID
		if ( null === $user_id ) {
			get_currentuserinfo();
			$user_id = $user_id = $current_user->ID;
		}

		$sql = $wpdb->prepare( "SELECT user_id FROM $wpdb->torro_participants WHERE form_id = %d", $torro_form_id );
		$user_ids = $wpdb->get_col( $sql );

		if ( ! in_array( $user_id, $user_ids ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Magic function to hide functions for autocomplete
	 *
	 * @param string $name
	 * @param $arguments
	 *
	 * @return mixed|Torro_Error
	 * @since 1.0.0
	 */
	public function __call( $name, $arguments ) {
		switch ( $name ) {
			case 'get_participants_list_table_html':
				$form_id = null;
				$start = null;
				$length = null;

				if( isset( $arguments[0] ) ) $form_id = $arguments[0];
				if( isset( $arguments[1] ) ) $start = $arguments[1];
				if( isset( $arguments[2] ) ) $length = $arguments[2];

				return $this->get_participants_list_table_html( $form_id, $start, $length );
				break;
			case 'get_participants_list_html':
				$form_id = null;
				$start = null;
				$length = null;

				if( isset( $arguments[0] ) ) $form_id = $arguments[0];
				if( isset( $arguments[1] ) ) $start = $arguments[1];
				if( isset( $arguments[2] ) ) $length = $arguments[2];

				return $this->get_participants_list_html( $form_id, $start, $length );
				break;
			case 'get_navigation_html':
				$form_id = null;
				$start = null;
				$length = null;
				$num_results = null;

				if( isset( $arguments[0] ) ) $form_id = $arguments[0];
				if( isset( $arguments[1] ) ) $start = $arguments[1];
				if( isset( $arguments[2] ) ) $length = $arguments[2];
				if( isset( $arguments[3] ) ) $num_results = $arguments[3];

				return $this->get_navigation_html( $form_id, $start, $length, $num_results );
				break;
			default:
				return new Torro_Error( 'torro_form_controller_method_not_exists', sprintf( __( 'This Torro Forms Controller function "%s" does not exist.', 'torro-forms' ), $name ) );
				break;
		}
	}
}
torro()->access_controls()->register( 'Torro_Access_Control_Selected_Members' );