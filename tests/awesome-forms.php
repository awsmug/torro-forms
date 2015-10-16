<?php

require_once( 'wp-testsuite/testsuite.php' );

class AwesomeForms_Tests extends WP_Tests
{
	public function go_forms_settings()
	{
		$this->go( '/wp-admin/admin.php?page=QuestionsAdmin' );
	}

	public function go_forms()
	{
		$this->go( '/wp-admin/edit.php?post_type=questions' );
	}

	public function add_form()
	{
		$this->go_forms();
		$this->byClass( '.page-title-action' ).click();
	}
}