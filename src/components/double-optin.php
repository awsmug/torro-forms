<?php
/**
 * Double OptIn class
 *
 * @package TorroForms
 * @since 1.1.0
 */

namespace awsmug\Torro_Forms\Components;
use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;
use Leaves_And_Love\Plugin_Lib\Traits\Hook_Service_Trait;
use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;

/**
 * Class for double opt-in functionalities.
 *
 * @since 1.1.0
 *
 * @method Module_Manager manager()
 */
class Double_OptIn extends Service {
	use Container_Service_Trait, Hook_Service_Trait;

	/**
	 * The module manager service definition.
	 *
	 * @since 1.0.0
	 * @static
	 * @var string
	 */
	protected static $service_manager = Module_Manager::class;

	/**
	 * Template tag handler for email notifications.
	 *
	 * @since 1.1.0
	 * @var Template_Tag_Handler
	 */
	protected $template_tag_handler;

	/**
	 * Constructor.
	 *
	 * @since 1.1.0
	 *
	 * @param string $prefix   Instance prefix.
	 */
	public function __construct( $prefix, $services ) {
		$this->set_prefix( $prefix );
		$this->set_services( $services );

		$this->register_template_tag_handlers();
	}

	/**
	 * Interrupting submission.
	 *
	 * Interrupts submission if the double opt-in option is selected for the form and sends out the email.
	 *
	 * @since 1.1.0
	 *
	 * @param bool       $should_complete Whether the completion process for the form submission should proceed. Default true.
	 * @param Submission $submission      Submission object.
	 * @param Form       $form            Form object.
	 *
	 * @return bool      $should_complete Whether the completion process for the form submission should proceed.
	 */
	public function interrupt_submission( $should_complete, $submission, $form ) {
		return false;
	}

	/**
	 * Sends out email.
	 *
	 * @since 1.1.0
	 *
	 * @param Form     $form            Form object.
	 * @param string   $email_address   Email Adress.
	 */
	public function send_email( $form, $email_address ) {

	}

	/**
	 * Gets Email content.
	 *
	 * @since 1.1.0
	 *
	 * @param Form     $form            Form object.
	 */
	public function get_email( $form ) {

	}

	/**
	 * Registers the template tag handler for email notifications.
	 *
	 * @since 1.1.0
	 */
	protected function register_template_tag_handlers() {
		$prefix = $this->get_prefix();

		$tags = array(
			'sitetitle'          => array(
				'group'       => 'global',
				'label'       => __( 'Site Title', 'torro-forms' ),
				'description' => __( 'Inserts the site title.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'name' );
				},
			),
			'sitetagline'        => array(
				'group'       => 'global',
				'label'       => __( 'Site Tagline', 'torro-forms' ),
				'description' => __( 'Inserts the site tagline.', 'torro-forms' ),
				'callback'    => function() {
					return get_bloginfo( 'description' );
				},
			),
			'siteurl'            => array(
				'group'       => 'global',
				'label'       => __( 'Site URL', 'torro-forms' ),
				'description' => __( 'Inserts the site home URL.', 'torro-forms' ),
				'callback'    => function() {
					return home_url( '/' );
				},
			),
			'adminemail'         => array(
				'group'       => 'global',
				'label'       => __( 'Site Admin Email', 'torro-forms' ),
				'description' => __( 'Inserts the site admin email.', 'torro-forms' ),
				'callback'    => function() {
					return get_option( 'admin_email' );
				},
			),
			'userip'             => array(
				'group'       => 'global',
				'label'       => __( 'User IP', 'torro-forms' ),
				'description' => __( 'Inserts the current user IP address.', 'torro-forms' ),
				'callback'    => function() {
					$validated_ip = filter_var( $_SERVER['REMOTE_ADDR'], FILTER_VALIDATE_IP );
					if ( empty( $validated_ip ) ) {
						return '0.0.0.0';
					}
					return $validated_ip;
				},
			),
			'refererurl'         => array(
				'group'       => 'global',
				'label'       => __( 'Referer URL', 'torro-forms' ),
				'description' => __( 'Inserts the current referer URL.', 'torro-forms' ),
				'callback'    => function() {
					return wp_get_referer();
				},
			),
			'formtitle'          => array(
				'group'       => 'form',
				'label'       => __( 'Form Title', 'torro-forms' ),
				'description' => __( 'Inserts the form title.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return $form->title;
				},
			),
			'formurl'            => array(
				'group'       => 'form',
				'label'       => __( 'Form URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_permalink( $form->id );
				},
			),
			'formediturl'        => array(
				'group'       => 'form',
				'label'       => __( 'Form Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the form.', 'torro-forms' ),
				'callback'    => function( $form ) {
					return get_edit_post_link( $form->id );
				},
			),
			'submissionurl'      => array(
				'group'       => 'submission',
				'label'       => __( 'Submission URL', 'torro-forms' ),
				'description' => __( 'Inserts the URL to the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg( 'torro_submission_id', $submission->id, get_permalink( $form->id ) );
				},
			),
			'submissionediturl'  => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Edit URL', 'torro-forms' ),
				'description' => __( 'Inserts the edit URL for the submission.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					return add_query_arg( array(
						'post_type' => torro()->post_types()->get_prefix() . 'form',
						'page'      => torro()->admin_pages()->get_prefix() . 'edit_submission',
						'id'        => $submission->id,
					), admin_url( 'edit.php' ) );
				},
			),
			'submissiondatetime' => array(
				'group'       => 'submission',
				'label'       => __( 'Submission Date and Time', 'torro-forms' ),
				'description' => __( 'Inserts the submission date and time.', 'torro-forms' ),
				'callback'    => function( $form, $submission ) {
					$date = $submission->format_datetime( get_option( 'date_format' ), false );
					$time = $submission->format_datetime( get_option( 'time_format' ), false );

					/* translators: 1: formatted date, 2: formatted time */
					return sprintf( _x( '%1$s at %2$s', 'concatenating date and time', 'torro-forms' ), $date, $time );
				},
			),
		);

		/**
		 * Filters template tags.
		 *
		 * An array will be returned with all template tags.
		 *
		 * @since 1.1.0
		 *
		 * @param array $tags All template tags in an array.
		 */
		$tags = apply_filters( "{$prefix}_email_notifications_template_tags", $tags );

		$this->template_tag_handler            = new Template_Tag_Handler( $this->slug, $tags, array( Form::class, Submission::class ) );

		$this->module->manager()->template_tag_handlers()->register( $this->template_tag_handler );
	}

	/**
	 * Sets up all action and filter hooks for the service.
	 *
	 * This method must be implemented and then be called from the constructor.
	 *
	 * @since 1.0.0
	 */
	public function setup_hooks() {
		$prefix = $this->get_prefix();

		$this->filters = array(
			array(
				'name'     => "{$prefix}should_complete_submission",
				'callback' => array( $this, 'interrupt_submission' ),
				'priority' => 10,
				'num_args' => 2,
			),

		);
	}
}
