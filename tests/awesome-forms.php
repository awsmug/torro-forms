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
		$this->go( '/wp-admin/edit.php?post_type=torro-forms' );
	}

	public function add_form( $name = null )
	{
		$this->go_forms();
		$this->byCssSelector( '.page-title-action' )->click();

		if( null != $name )
		{
			$this->set_post_title( $name );
			$this->save_post();
		}
	}
}