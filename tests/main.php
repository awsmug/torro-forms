<?php

require_once( 'awesome-forms.php' );

class Main_Tests extends AwesomeForms_Tests
{
	
	public function testCreateForm()
	{
		$this->login();
		$this->add_form( 'Svens Automatic Title' );
	}
}
