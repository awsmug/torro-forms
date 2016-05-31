<?php
/**
 * Components: Torro_Form_Access_Control_All_Members class
 *
 * @package TorroForms
 * @subpackage Components
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Torro_Form_Access_Control_All_Members extends Torro_Form_Access_Control {
	/**
	 * Instance
	 *
	 * @var null|Torro_Form_Access_Control_All_Members
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Initializing.
	 *
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
		$this->title = __( 'All Members', 'torro-forms' );
		$this->name = 'allmembers';

		$this->option_name = __( 'All Members of site', 'torro-forms' );
	}

	/**
	 * Saving data
	 *
	 * @param int $form_id
	 *
	 * @since 1.0.0
	 */
	public function save( $form_id ) {
		/**
		 * Saving access-control options
		 */
		if ( isset( $_POST['form_access_controls_allmembers_same_users'] ) ) {
			$access_controls_same_users = wp_unslash( $_POST['form_access_controls_allmembers_same_users'] );
			update_post_meta( $form_id, 'form_access_controls_allmembers_same_users', $access_controls_same_users );
		} else {
			update_post_meta( $form_id, 'form_access_controls_allmembers_same_users', '' );
		}
	}

	/**
	 * Adds content to the option
	 *
	 * @param int $form_id
	 *
	 * @return string $html
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
		/**
		 * Check User
		 */
		$access_controls_same_users = get_post_meta( $form_id, 'form_access_controls_allmembers_same_users', true );
		$checked = 'yes' === $access_controls_same_users ? ' checked' : '';

		$html  = '<div class="torro-form-options">';
		$html .= '<div class="flex-options" role="group">';
		$html .= '<label for="form_access_controls_allmembers_same_users">' . esc_attr__( 'Forbid multiple entries', 'torro-forms' ) . '</label></td>';
		$html .= '<div><input type="checkbox" name="form_access_controls_allmembers_same_users" value="yes" ' . $checked . '/></div>';
		$html .= '</div>';
		$html .= '</div>';

		ob_start();
		do_action( 'form_access_controls_same_users_userfilters' );
		$html .= ob_get_clean();

		return $html;
	}

	/**
	 * Checks if the user can pass
	 *
	 * @param int $form_id
	 *
	 * @return boolean $has_access
	 * @since 1.0.0
	 */
	public function check( $form_id ) {
		if ( ! is_user_logged_in() ) {
			$this->add_message( 'error', __( 'You have to be logged in to participate.', 'torro-forms' ) );
			return false;
		}

		$access_controls_same_users = get_post_meta( $form_id, 'form_access_controls_allmembers_same_users', true );

		if ( 'yes' === $access_controls_same_users && torro()->forms()->get( $form_id )->has_participated() ) {
			$this->add_message( 'error', __( 'You have already entered your data.', 'torro-forms' ) );
			return false;
		}

		return true;
	}
}

torro()->access_controls()->register( 'Torro_Form_Access_Control_All_Members' );
