<?php
/**
 * @package TorroForms
 * @subpackage Tests
 */

class Torro_UnitTestCase extends WP_UnitTestCase {
	protected $sample_forms = array();
	protected $sample_form_results = array();

	public function setUp() {
		parent::setUp();

		$this->sample_forms['contact_form'] = array(
			'title'				=> 'Contact Form',
			'containers'		=> array(
				'page1'				=> array(
					'label'				=> 'Page 1',
					'sort'				=> 0,
					'elements'			=> array(
						'name'				=> array(
							'type'				=> 'textfield',
							'label'				=> 'Your Name',
							'sort'				=> 0,
							'element_settings'	=> array(
								array(
									'name'				=> 'description',
									'value'				=> 'Enter your name.',
								),
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
								array(
									'name'				=> 'maxlength',
									'value'				=> '35',
								),
							),
						),
						'email'				=> array(
							'type'				=> 'textfield',
							'label'				=> 'Your Email',
							'sort'				=> 1,
							'element_settings'	=> array(
								array(
									'name'				=> 'description',
									'value'				=> 'Enter your email address.',
								),
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
								array(
									'name'				=> 'input_type',
									'value'				=> 'email_address',
								),
							),
						),
						'subject'			=> array(
							'type'				=> 'textfield',
							'label'				=> 'Subject',
							'sort'				=> 2,
							'element_settings'	=> array(
								array(
									'name'				=> 'description',
									'value'				=> 'Enter the subject of your message.',
								),
							),
						),
						'message'			=> array(
							'type'				=> 'textarea',
							'label'				=> 'Message',
							'sort'				=> 3,
							'element_settings'	=> array(
								array(
									'name'				=> 'description',
									'value'				=> 'Enter your message.',
								),
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
								array(
									'name'				=> 'maxlength',
									'value'				=> '300',
								),
							),
						),
					),
				),
			),
		);
		$this->sample_forms['quiz'] = array(
			'title'				=> 'Quiz',
			'containers'		=> array(
				'page1'				=> array(
					'label'				=> 'Page 1',
					'sort'				=> 0,
					'elements'			=> array(
						'name'				=> array(
							'type'				=> 'textfield',
							'label'				=> 'Your Name',
							'sort'				=> 0,
							'element_settings'	=> array(
								array(
									'name'				=> 'description',
									'value'				=> 'Enter your name.',
								),
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
							),
						),
						'origin'			=> array(
							'type'				=> 'dropdown',
							'label'				=> 'Where are you from?',
							'sort'				=> 1,
							'element_answers'	=> array(
								array(
									'answer'			=> 'Coruscant',
									'sort'				=> 0,
								),
								array(
									'answer'			=> 'Tatooine',
									'sort'				=> 1,
								),
								array(
									'answer'			=> 'Dagobah',
									'sort'				=> 2,
								),
								array(
									'answer'			=> 'Naboo',
									'sort'				=> 3,
								),
								array(
									'answer'			=> 'Endor',
									'sort'				=> 4,
								),
							),
							'element_settings'	=> array(
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
							),
						),
					),
				),
				'page2'				=> array(
					'label'				=> 'Page 2',
					'sort'				=> 1,
					'elements'			=> array(
						'whoshotfirst'		=> array(
							'type'				=> 'onechoice',
							'label'				=> 'Who shot first?',
							'sort'				=> 0,
							'element_answers'	=> array(
								array(
									'answer'			=> 'Han Solo',
									'sort'				=> 0,
								),
								array(
									'answer'			=> 'Greedo',
									'sort'				=> 1,
								),
							),
							'element_settings'	=> array(
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
							),
						),
					),
				),
				'page3'				=> array(
					'label'				=> 'Page 3',
					'sort'				=> 2,
					'elements'			=> array(
						'whoisajedi'		=> array(
							'type'				=> 'multiplechoice',
							'label'				=> 'Who of these is a Jedi?',
							'sort'				=> 0,
							'element_answers'	=> array(
								array(
									'answer'			=> 'Boba Fett',
									'sort'				=> 0,
								),
								array(
									'answer'			=> 'Obi-Wan Kenobi',
									'sort'				=> 1,
								),
								array(
									'answer'			=> 'Darth Maul',
									'sort'				=> 2,
								),
								array(
									'answer'			=> 'Count Dooku',
									'sort'				=> 3,
								),
								array(
									'answer'			=> 'Luke Skywalker',
									'sort'				=> 4,
								),
							),
							'element_settings'	=> array(
								array(
									'name'				=> 'required',
									'value'				=> '1',
								),
							),
						),
					),
				),
			),
		);

		// Make sure to not pass these directly. The `element_id` values have to be replaced by actual element ids.
		$this->sample_form_results['contact_form'] = array(
			array(
				'cookie_key'	=> '',
				'result_values'	=> array(
					array(
						'element_id'	=> 'name',
						'value'			=> 'John Doe',
					),
					array(
						'element_id'	=> 'email',
						'value'			=> 'johndoe@example.com',
					),
					array(
						'element_id'	=> 'subject',
						'value'			=> 'My Subject',
					),
					array(
						'element_id'	=> 'message',
						'value'			=> 'Hello, please consider this message. Thanks and bye.',
					),
				),
			),
		);
		$this->sample_form_results['quiz'] = array(
			array(
				'cookie_key'	=> 'wookiecookie',
				'result_values'	=> array(
					array(
						'element_id'	=> 'name',
						'value'			=> 'Star Wars Fan',
					),
					array(
						'element_id'	=> 'origin',
						'value'			=> 'Tatooine',
					),
					array(
						'element_id'	=> 'whoshotfirst',
						'value'			=> 'Han Solo',
					),
					array(
						'element_id'	=> 'whoisajedi',
						'value'			=> 'Obi-Wan Kenobi',
					),
					array(
						'element_id'	=> 'whoisajedi',
						'value'			=> 'Luke Skywalker',
					),
				),
			),
			array(
				'cookie_key'	=> 'spockmock',
				'result_values'	=> array(
					array(
						'element_id'	=> 'name',
						'value'			=> 'Star Trek Fan',
					),
					array(
						'element_id'	=> 'origin',
						'value'			=> 'Naboo',
					),
					array(
						'element_id'	=> 'whoshotfirst',
						'value'			=> 'Greedo',
					),
					array(
						'element_id'	=> 'whoisajedi',
						'value'			=> 'Count Dooku',
					),
				),
			),
		);

		add_filter( 'torro_template_locations', array( $this, 'register_test_template_location' ) );
	}

	public function register_test_template_location( $locations ) {
		$locations[50] = dirname( __FILE__ ) . '/templates/';

		return $locations;
	}

	public function go_to( $url ) {
		// This ensures that is_admin() works properly.
		if ( 0 === strpos( $url, admin_url() ) ) {
			$GLOBALS['current_screen'] = new Torro_Screen_Mock();
		} else {
			if ( isset( $GLOBALS['current_screen'] ) ) {
				unset( $GLOBALS['current_screen'] );
			}
			parent::go_to( $url );
		}
	}

	public static function factory() {
		static $factory = null;
		if ( ! $factory || ! $factory instanceof Torro_UnitTest_Factory ) {
			$factory = new Torro_UnitTest_Factory();
		}
		return $factory;
	}
}
