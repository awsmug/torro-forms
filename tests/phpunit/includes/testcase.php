<?php
/**
 * @package TorroForms
 */

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTestCase;

class Unit_Test_Case extends WP_UnitTestCase {

	private static $_sample_forms = array();

	public function register_test_template_location( $template_service = null ) {
		if ( ! $template_service ) {
			$template_service = torro()->templates();
		}

		$template_service->register_location( 'tests', dirname( __FILE__ ) . '/templates/' );
	}

	public function unregister_test_template_location( $template_service = null ) {
		if ( ! $template_service ) {
			$template_service = torro()->templates();
		}

		$template_service->unregister_location( 'tests' );
	}

	public function go_to( $url ) {
		// This ensures that is_admin() works properly.
		if ( 0 === strpos( $url, admin_url() ) ) {
			$GLOBALS['current_screen'] = new Screen_Mock();

			$_GET  = array();
			$_POST = array();
			foreach ( array( 'query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow' ) as $v ) {
				if ( isset( $GLOBALS[ $v ] ) ) {
					unset( $GLOBALS[ $v ] );
				}
			}
			$parts = parse_url( $url );
			if ( isset( $parts['scheme'] ) ) {
				$req = isset( $parts['path'] ) ? $parts['path'] : '';
				if ( isset( $parts['query'] ) ) {
					$req .= '?' . $parts['query'];
					// Parse the url query vars into $_GET.
					parse_str( $parts['query'], $_GET );
				}
			} else {
				$req = $url;
			}

			$_SERVER['REQUEST_URI'] = $req;
			unset( $_SERVER['PATH_INFO'] );
		} else {
			foreach ( array( 'post', 'current_screen' ) as $v ) {
				if ( isset( $GLOBALS[ $v ] ) ) {
					unset( $GLOBALS[ $v ] );
				}
			}

			parent::go_to( $url );

			if ( ! isset( $GLOBALS['post'] ) && isset( $_GET['p'] ) ) {
				$GLOBALS['post'] = get_post( $_GET['p'] );
			}
		}
	}

	public static function factory() {
		static $factory = null;

		if ( ! $factory || ! $factory instanceof Factory ) {
			$factory = new Factory();
		}

		return $factory;
	}

	protected static function get_sample_form( $slug ) {
		if ( isset( self::$_sample_forms[ $slug ] ) ) {
			return self::$_sample_forms[ $slug ];
		}

		switch ( $slug ) {
			case 'contact':
				self::$_sample_forms[ $slug ] = self::get_sample_contact_form();
				break;
			case 'quiz':
				self::$_sample_forms[ $slug ] = self::get_sample_quiz_form();
				break;
			default:
				return false;
		}

		return self::$_sample_forms[ $slug ];
	}

	private static function get_sample_contact_form() {
		return array(
			'title'      => 'Contact Form',
			'containers' => array(
				'page1' => array(
					'label'    => 'Page 1',
					'sort'     => 0,
					'elements' => array(
						'name'    => array(
							'type'             => 'textfield',
							'label'            => 'Your Name',
							'sort'             => 0,
							'element_settings' => array(
								array(
									'name'  => 'description',
									'value' => 'Enter your name.',
								),
								array(
									'name'  => 'required',
									'value' => '1',
								),
								array(
									'name'  => 'maxlength',
									'value' => '35',
								),
							),
						),
						'email'   => array(
							'type'             => 'textfield',
							'label'            => 'Your Email',
							'sort'             => 1,
							'element_settings' => array(
								array(
									'name'  => 'description',
									'value' => 'Enter your email address.',
								),
								array(
									'name'  => 'required',
									'value' => '1',
								),
								array(
									'name'  => 'input_type',
									'value' => 'email_address',
								),
							),
						),
						'subject' => array(
							'type'             => 'textfield',
							'label'            => 'Subject',
							'sort'             => 2,
							'element_settings' => array(
								array(
									'name'  => 'description',
									'value' => 'Enter the subject of your message.',
								),
							),
						),
						'message' => array(
							'type'             => 'textarea',
							'label'            => 'Message',
							'sort'             => 3,
							'element_settings' => array(
								array(
									'name'  => 'description',
									'value' => 'Enter your message.',
								),
								array(
									'name'  => 'required',
									'value' => '1',
								),
								array(
									'name'  => 'maxlength',
									'value' => '300',
								),
							),
						),
					),
				),
			),
		);
	}

	private static function get_sample_quiz_form() {
		return array(
			'title'      => 'Quiz',
			'containers' => array(
				'page1' => array(
					'label'    => 'Page 1',
					'sort'     => 0,
					'elements' => array(
						'name'   => array(
							'type'             => 'textfield',
							'label'            => 'Your Name',
							'sort'             => 0,
							'element_settings' => array(
								array(
									'name'  => 'description',
									'value' => 'Enter your name.',
								),
								array(
									'name'  => 'required',
									'value' => '1',
								),
							),
						),
						'origin' => array(
							'type'             => 'dropdown',
							'label'            => 'Where are you from?',
							'sort'             => 1,
							'element_choices'  => array(
								array(
									'value' => 'Coruscant',
									'sort'  => 0,
								),
								array(
									'value' => 'Tatooine',
									'sort'  => 1,
								),
								array(
									'value' => 'Dagobah',
									'sort'  => 2,
								),
								array(
									'value' => 'Naboo',
									'sort'  => 3,
								),
								array(
									'value' => 'Endor',
									'sort'  => 4,
								),
							),
							'element_settings' => array(
								array(
									'name'  => 'required',
									'value' => '1',
								),
							),
						),
					),
				),
				'page2' => array(
					'label'    => 'Page 2',
					'sort'     => 1,
					'elements' => array(
						'whoshotfirst' => array(
							'type'             => 'onechoice',
							'label'            => 'Who shot first?',
							'sort'             => 0,
							'element_choices'  => array(
								array(
									'value' => 'Han Solo',
									'sort'  => 0,
								),
								array(
									'value' => 'Greedo',
									'sort'  => 1,
								),
							),
							'element_settings' => array(
								array(
									'name'  => 'required',
									'value' => '1',
								),
							),
						),
					),
				),
				'page3' => array(
					'label'    => 'Page 3',
					'sort'     => 2,
					'elements' => array(
						'whoisajedi' => array(
							'type'             => 'multiplechoice',
							'label'            => 'Who of these is a Jedi?',
							'sort'             => 0,
							'element_choices'  => array(
								array(
									'value' => 'Boba Fett',
									'sort'  => 0,
								),
								array(
									'value' => 'Obi-Wan Kenobi',
									'sort'  => 1,
								),
								array(
									'value' => 'Darth Maul',
									'sort'  => 2,
								),
								array(
									'value' => 'Count Dooku',
									'sort'  => 3,
								),
								array(
									'value' => 'Luke Skywalker',
									'sort'  => 4,
								),
							),
							'element_settings' => array(
								array(
									'name'  => 'required',
									'value' => '1',
								),
							),
						),
					),
				),
			),
		);
	}
}
