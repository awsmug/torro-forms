<?php

class Tests_Torro extends Torro_UnitTestCase {
	public function test_torro() {
		$this->assertInstanceOf( 'Torro', torro() );
	}

	public function test_forms() {
		$this->assertInstanceOf( 'Torro_Forms_Manager', torro()->forms() );
	}

	public function test_containers() {
		$this->assertInstanceOf( 'Torro_Containers_Manager', torro()->containers() );
	}

	public function test_elements() {
		$this->assertInstanceOf( 'Torro_Elements_Manager', torro()->elements() );
	}

	public function test_element_answers() {
		$this->assertInstanceOf( 'Torro_Element_Answer_Manager', torro()->element_answers() );
	}

	public function test_element_settings() {
		$this->assertInstanceOf( 'Torro_Element_Setting_Manager', torro()->element_settings() );
	}

	public function test_results() {
		$this->assertInstanceOf( 'Torro_Results_Manager', torro()->results() );
	}

	public function test_result_values() {
		$this->assertInstanceOf( 'Torro_Result_Values_Manager', torro()->result_values() );
	}

	public function test_participants() {
		$this->assertInstanceOf( 'Torro_Participants_Manager', torro()->participants() );
	}

	public function test_email_notifications() {
		$this->assertInstanceOf( 'Torro_Email_Notifications_Manager', torro()->email_notifications() );
	}

	public function test_components() {
		$this->assertInstanceOf( 'Torro_Components_Manager', torro()->components() );
	}

	public function test_element_types() {
		$this->assertInstanceOf( 'Torro_Element_Types_Manager', torro()->element_types() );
	}

	public function test_form_settings() {
		$this->assertInstanceOf( 'Torro_Form_Settings_Manager', torro()->form_settings() );
	}

	public function test_settings() {
		$this->assertInstanceOf( 'Torro_Settings_Manager', torro()->settings() );
	}

	public function test_templatetags() {
		$this->assertInstanceOf( 'Torro_TemplateTags_Manager', torro()->templatetags() );
	}

	public function test_actions() {
		$this->assertInstanceOf( 'Torro_Form_Actions_Manager', torro()->actions() );
	}

	public function test_access_controls() {
		$this->assertInstanceOf( 'Torro_Form_Access_Controls_Manager', torro()->access_controls() );
	}

	public function test_resulthandlers() {
		$this->assertInstanceOf( 'Torro_Form_Result_Handlers_Manager', torro()->resulthandlers() );
	}

	public function test_extensions() {
		$this->assertInstanceOf( 'Torro_Extensions_Manager', torro()->extensions() );
	}

	public function test_admin_notices() {
		$this->assertInstanceOf( 'Torro_Admin_Notices', torro()->admin_notices() );
	}

	public function test_ajax() {
		$this->assertInstanceOf( 'Torro_AJAX', torro()->ajax() );
	}

	public function test_is_form() {
		$form_id = self::factory()->form->create();

		$this->go_to( admin_url( 'edit.php?post_type=torro_form' ) );
		$this->assertTrue( torro()->is_form() );

		$this->go_to( admin_url( 'post.php?post=' . $form_id . '&action=edit' ) );
		$this->assertTrue( torro()->is_form() );

		$this->go_to( admin_url( 'options-general.php' ) );
		$this->assertFalse( torro()->is_form() );

		$this->go_to( home_url( '/' ) );
		$this->assertFalse( torro()->is_form() );

		$this->go_to( get_permalink( $form_id ) );
		$this->assertTrue( torro()->is_form() );
	}

	public function test_is_formbuilder() {
		$form_id = self::factory()->form->create();

		$this->go_to( get_permalink( $form_id ) );
		$this->assertFalse( torro()->is_formbuilder() );

		defined( 'WP_ADMIN' ) || define( 'WP_ADMIN', true );

		$this->go_to( admin_url( 'post.php?post=' . $form_id . '&action=edit' ) );
		$this->assertTrue( torro()->is_formbuilder() );
	}

	public function test_is_settingspage() {
		defined( 'WP_ADMIN' ) || define( 'WP_ADMIN', true );

		$this->go_to( admin_url( 'options-general.php' ) );
		$this->assertFalse( torro()->is_settingspage() );

		$this->go_to( admin_url( 'edit.php?post_type=torro_form' ) );
		$this->assertFalse( torro()->is_settingspage() );

		$this->go_to( admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin' ) );
		$this->assertTrue( torro()->is_settingspage() );

		$this->go_to( admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin&tab=form_settings' ) );
		$this->assertTrue( torro()->is_settingspage( 'form_settings' ) );
		$this->assertFalse( torro()->is_settingspage( 'extensions' ) );

		$this->go_to( admin_url( 'edit.php?post_type=torro_form&page=Torro_Admin&tab=form_settings&section=selectedmembers' ) );
		$this->assertTrue( torro()->is_settingspage( 'form_settings' ) );
		$this->assertTrue( torro()->is_settingspage( 'form_settings', 'selectedmembers' ) );
		$this->assertFalse( torro()->is_settingspage( 'form_settings', 'spam_protection' ) );
	}

	public function test_template() {
		ob_start();
		torro()->template( 'test-template' );
		$this->assertContains( 'This is a test template.', ob_get_clean() );

		ob_start();
		torro()->template( 'test-template', array( 'template_suffix' => 'something' ) );
		$this->assertContains( 'This is a test template.', ob_get_clean() );

		ob_start();
		torro()->template( 'test', array( 'template_suffix' => 'template' ) );
		$this->assertContains( 'This is a test template.', ob_get_clean() );

		ob_start();
		torro()->template( 'test' );
		$this->assertEmpty( ob_get_clean() );
	}

	public function test_get_path() {
		$subpath = 'core/form-builder.php';

		$expected = WP_PLUGIN_DIR . '/torro-forms/' . $subpath;

		$this->assertEquals( $expected, torro()->get_path( $subpath ) );
		$this->assertEquals( $expected, torro()->get_path( '/' . $subpath ) );
	}

	public function test_get_url() {
		$subpath = 'core/form-builder.php';

		$expected = WP_PLUGIN_URL . '/torro-forms/' . $subpath;

		$this->assertEquals( $expected, torro()->get_url( $subpath ) );
		$this->assertEquals( $expected, torro()->get_url( '/' . $subpath ) );
	}

	public function test_get_asset_url() {
		$baseurl = WP_PLUGIN_URL . '/torro-forms/assets/';

		$min = ( ! defined( 'SCRIPT_DEBUG' ) || ! SCRIPT_DEBUG ) ? '.min' : '';

		$this->assertEquals( $baseurl . 'dist/css/admin.css', torro()->get_asset_url( 'admin', 'css', true ) );
		$this->assertEquals( $baseurl . 'dist/css/admin' . $min . '.css', torro()->get_asset_url( 'admin', 'css' ) );

		$this->assertEquals( $baseurl . 'dist/img/icon.svg', torro()->get_asset_url( 'icon', 'svg' ) );

		$this->assertEquals( $baseurl . 'dist/vendor/library/script.js', torro()->get_asset_url( 'library/script', 'vendor-js', true ) );
	}
}
