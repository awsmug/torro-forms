<?php
/**
 * Core: Torro_TemplateTags_Global class
 *
 * @package TorroForms
 * @subpackage CoreTemplateTags
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Torro Forms global template tags handler class
 *
 * Handles template tags for global scope.
 *
 * @since 1.0.0-beta.1
 */
final class Torro_Templatetags_Global extends Torro_TemplateTags {
	/**
	 * Instance
	 *
	 * @var null|Torro_Templatetags_Global
	 * @since 1.0.0
	 */
	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initializing.
	 *
	 * @since 1.0.0
	 */
	protected function __construct() {
		parent::__construct();
	}

	protected function init() {
		$this->title = __( 'Global', 'torro-forms' );
		$this->name = 'basetags';
		$this->description = __( 'Global Templatetags', 'torro-forms' );
	}

	/**
	 * Adding all tags of class
	 */
	public function tags() {
		$this->add_tag( 'sitetitle', __( 'Site Title', 'torro-forms' ), __( 'Adds the site title.', 'torro-forms' ), array( $this, 'sitetitle' ) );
		$this->add_tag( 'sitetagline', __( 'Site Tagline', 'torro-forms' ), __( 'Adds the sites tagline.', 'torro-forms' ), array( $this, 'sitetagline') );
		$this->add_tag( 'adminemail', __( 'Admin Email', 'torro-forms' ), __( 'Adds the admin email-address.', 'torro-forms' ), array( $this, 'adminemail') );
		$this->add_tag( 'userip', __( 'User IP', 'torro-forms' ), __( 'Adds the sites user IP.', 'torro-forms' ), array( $this, 'userip' ) );
	}

	/**
	 * %sitename%
	 */
	public function sitetitle() {
		return get_bloginfo( 'name' );
	}

	/**
	 * %sitename%
	 */
	public function sitetagline() {
		return get_bloginfo( 'description' );
	}

	/**
	 * %sitename%
	 */
	public function adminemail() {
		return get_option( 'admin_email' );
	}

	/**
	 * %sitename%
	 */
	public function userip() {
		return $_SERVER['REMOTE_ADDR'];
	}
}

torro()->templatetags()->register( 'Torro_Templatetags_Global' );
