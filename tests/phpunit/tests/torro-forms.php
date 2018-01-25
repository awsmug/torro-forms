<?php

namespace awsmug\Torro_Forms\Tests;

use Torro_Forms;
use awsmug\Torro_Forms\Logger;

class Tests_Torro_Forms extends Unit_Test_Case {

	/**
	 * @dataProvider data_services
	 */
	public function test_services( $service_slug, $class_name ) {
		$torro = new Torro_Forms( WP_PLUGIN_DIR . '/torro-forms/torro-forms.php', '' );

		// Trick the plugin into thinking the action hasn't fired yet.
		$orig_action_count = $GLOBALS['wp_actions']['torro_loaded'];
		unset( $GLOBALS['wp_actions']['torro_loaded'] );

		$torro->load();

		// Reset original state.
		$GLOBALS['wp_actions']['torro_loaded'] = $orig_action_count;

		$this->assertInstanceOf( $class_name, $torro->$service_slug() );
	}

	public function data_services() {
		return array(
			array(
				'forms',
				'awsmug\Torro_Forms\DB_Objects\Forms\Form_Manager',
			),
			array(
				'form_categories',
				'awsmug\Torro_Forms\DB_Objects\Form_Categories\Form_Category_Manager',
			),
			array(
				'containers',
				'awsmug\Torro_Forms\DB_Objects\Containers\Container_Manager',
			),
			array(
				'elements',
				'awsmug\Torro_Forms\DB_Objects\Elements\Element_Manager',
			),
			array(
				'element_choices',
				'awsmug\Torro_Forms\DB_Objects\Element_Choices\Element_Choice_Manager',
			),
			array(
				'element_settings',
				'awsmug\Torro_Forms\DB_Objects\Element_Settings\Element_Setting_Manager',
			),
			array(
				'submissions',
				'awsmug\Torro_Forms\DB_Objects\Submissions\Submission_Manager',
			),
			array(
				'submission_values',
				'awsmug\Torro_Forms\DB_Objects\Submission_Values\Submission_Value_Manager',
			),
			array(
				'post_types',
				'awsmug\Torro_Forms\DB_Objects\Post_Type_Manager',
			),
			array(
				'taxonomies',
				'awsmug\Torro_Forms\DB_Objects\Taxonomy_Manager',
			),
			array(
				'form_uploads',
				'awsmug\Torro_Forms\Components\Form_Upload_Manager',
			),
			array(
				'template_tag_handlers',
				'awsmug\Torro_Forms\Components\Template_Tag_Handler_Manager',
			),
			array(
				'admin_pages',
				'Leaves_And_Love\Plugin_Lib\Components\Admin_Pages',
			),
			array(
				'extensions',
				'Leaves_And_Love\Plugin_Lib\Components\Extensions',
			),
			array(
				'modules',
				'awsmug\Torro_Forms\Modules\Module_Manager',
			),
			array(
				'options',
				'Leaves_And_Love\Plugin_Lib\Options',
			),
			array(
				'cache',
				'Leaves_And_Love\Plugin_Lib\Cache',
			),
			array(
				'db',
				'awsmug\Torro_Forms\DB',
			),
			array(
				'meta',
				'Leaves_And_Love\Plugin_Lib\Meta',
			),
			array(
				'assets',
				'awsmug\Torro_Forms\Assets',
			),
			array(
				'template',
				'Leaves_And_Love\Plugin_Lib\Template',
			),
			array(
				'ajax',
				'Leaves_And_Love\Plugin_Lib\AJAX',
			),
			array(
				'error_handler',
				'awsmug\Torro_Forms\Error_Handler',
			),
			array(
				'apiapi',
				'APIAPI\Core\APIAPI',
			),
			array(
				'logger',
				'Psr\Log\LoggerInterface',
			),
		);
	}

	public function test_custom_logger() {
		require_once TORRO_TEST_ROOT . '/includes/null-logger.php';

		$torro = new Torro_Forms( WP_PLUGIN_DIR . '/torro-forms/torro-forms.php', '' );

		add_filter( 'torro_set_logger', function() {
			return new Null_Logger();
		});

		$this->assertInstanceOf( Null_Logger::class, $torro->logger() );
	}

	public function test_custom_logger_invalid() {
		$torro = new Torro_Forms( WP_PLUGIN_DIR . '/torro-forms/torro-forms.php', '' );

		add_filter( 'torro_set_logger', function() {
			return new \stdClass();
		});

		$this->assertInstanceOf( Logger::class, $torro->logger() );
	}
}
