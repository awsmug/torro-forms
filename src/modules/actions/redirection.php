<?php
/**
 * Redirection action class
 *
 * @package TorroForms
 * @since 1.0.0
 */

namespace awsmug\Torro_Forms\Modules\Actions;

use awsmug\Torro_Forms\DB_Objects\Forms\Form;
use awsmug\Torro_Forms\DB_Objects\Submissions\Submission;
use WP_Error;

/**
 * Class for an action that redirects the user.
 *
 * @since 1.0.0
 */
class Redirection extends Action {

	/**
	 * Bootstraps the submodule by setting properties.
	 *
	 * @since 1.0.0
	 */
	protected function bootstrap() {
		$this->slug  = 'redirection';
		$this->title = __( 'Redirection', 'torro-forms' );
	}

	/**
	 * Checks whether the access control is enabled for a specific form.
	 *
	 * @since 1.0.0
	 *
	 * @param Form $form Form object to check.
	 * @return bool True if the access control is enabled, false otherwise.
	 */
	public function enabled( $form ) {
		$redirect_type = $this->get_form_option( $form->id, 'type', 'redirect_none' );

		return 'redirect_none' !== $redirect_type;
	}

	/**
	 * Handles the action for a specific form submission.
	 *
	 * @since 1.0.0
	 *
	 * @param Submission $submission Submission to handle by the action.
	 * @param Form       $form       Form the submission applies to.
	 * @return bool|WP_Error True on success, error object on failure.
	 */
	public function handle( $submission, $form ) {
		$redirect_type = $this->get_form_option( $form->id, 'type', 'redirect_none' );

		$redirect_url = '';
		switch ( $redirect_type ) {
			case 'redirect_url':
				$redirect_url = $this->get_form_option( $form->id, 'url' );
				break;
			case 'redirect_page':
				$redirect_page = (int) $this->get_form_option( $form->id, 'page' );
				if ( ! empty( $redirect_page ) ) {
					$redirect_url = get_permalink( $redirect_page );
				}
		}

		if ( ! empty( $redirect_url ) ) {
			add_filter(
				"{$this->module->get_prefix()}handle_form_submission_redirect_url",
				function() use ( $redirect_url ) {
					return $redirect_url;
				},
				100,
				0
			);
		}

		return true;
	}

	/**
	 * Returns the available meta fields for the submodule.
	 *
	 * @since 1.0.0
	 *
	 * @return array Associative array of `$field_slug => $field_args` pairs.
	 */
	public function get_meta_fields() {
		$meta_fields = $this->_get_meta_fields();

		unset( $meta_fields['enabled'] );

		$meta_fields['type'] = array(
			'type'         => 'select',
			'label'        => __( 'Redirect Type', 'torro-forms' ),
			'description'  => __( 'Select to which type of content to redirect the user.', 'torro-forms' ),
			'choices'      => array(
				'redirect_none' => __( 'No Redirection', 'torro-forms' ),
				'redirect_page' => __( 'Page Redirection', 'torro-forms' ),
				'redirect_url'  => __( 'URL Redirection', 'torro-forms' ),
			),
			'wrap_classes' => array( 'has-torro-tooltip-description' ),
		);

		$page_count = (int) wp_count_posts( 'page' )->publish;
		if ( $page_count > 15 ) {
			$meta_fields['page'] = array(
				'type'          => 'autocomplete',
				'label'         => __( 'Redirect Page', 'torro-forms' ),
				'description'   => __( 'Specify the page to redirect to.', 'torro-forms' ),
				'input_classes' => array( 'regular-text' ),
				'wrap_classes'  => array( 'has-torro-tooltip-description' ),
				'autocomplete'  => array(
					'rest_placeholder_search_route' => 'wp/v2/pages?search=%search%',
					'rest_placeholder_label_route'  => 'wp/v2/pages/%value%',
					'value_generator'               => '%id%',
					'label_generator'               => '%title.rendered%',
				),
			);
		} else {
			$pages = get_posts(
				array(
					'posts_per_page' => 15,
					'post_type'      => 'page',
					'post_status'    => 'publish',
				)
			);

			$page_choices = array();
			foreach ( $pages as $page ) {
				$page_choices[ $page->ID ] = get_the_title( $page->ID );
			}

			$meta_fields['page'] = array(
				'type'         => 'select',
				'label'        => __( 'Redirect Page', 'torro-forms' ),
				'description'  => __( 'Specify the page to redirect to.', 'torro-forms' ),
				'choices'      => $page_choices,
				'wrap_classes' => array( 'has-torro-tooltip-description' ),
			);
		}

		$meta_fields['page']['dependencies'] = array(
			array(
				'prop'     => 'display',
				'callback' => 'get_data_by_map',
				'fields'   => array( 'type' ),
				'args'     => array(
					'map' => array(
						'redirect_none' => false,
						'redirect_page' => true,
						'redirect_url'  => false,
					),
				),
			),
		);

		$meta_fields['url'] = array(
			'type'          => 'url',
			'label'         => __( 'Redirect URL', 'torro-forms' ),
			'description'   => __( 'Enter the URL to redirect to.', 'torro-forms' ),
			'placeholder'   => 'https://',
			'input_classes' => array( 'regular-text' ),
			'wrap_classes'  => array( 'has-torro-tooltip-description' ),
			'dependencies'  => array(
				array(
					'prop'     => 'display',
					'callback' => 'get_data_by_map',
					'fields'   => array( 'type' ),
					'args'     => array(
						'map' => array(
							'redirect_none' => false,
							'redirect_page' => false,
							'redirect_url'  => true,
						),
					),
				),
			),
		);

		return $meta_fields;
	}
}
