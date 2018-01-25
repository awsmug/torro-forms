<?php

namespace awsmug\Torro_Forms\Tests;

use WP_UnitTest_Factory;

class Factory extends WP_UnitTest_Factory {

	public $form;
	public $container;
	public $element;
	public $element_choice;
	public $element_setting;
	public $submission;
	public $submission_value;

	public function __construct() {
		parent::__construct();

		$this->form             = new Factory_For_Form( $this );
		$this->container        = new Factory_For_Container( $this );
		$this->element          = new Factory_For_Element( $this );
		$this->element_choice   = new Factory_For_Element_Choice( $this );
		$this->element_setting  = new Factory_For_Element_Setting( $this );
		$this->submission       = new Factory_For_Submission( $this );
		$this->submission_value = new Factory_For_Submission_value( $this );
	}
}
