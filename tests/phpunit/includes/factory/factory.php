<?php

class Torro_UnitTest_Factory extends WP_UnitTest_Factory {
	public $form;
	public $container;
	public $element;
	public $element_answer;
	public $element_setting;
	public $participant;
	public $result;
	public $result_value;

	public function __construct() {
		parent::__construct();

		$this->form = new Torro_UnitTest_Factory_For_Form( $this );
		$this->container = new Torro_UnitTest_Factory_For_Container( $this );
		$this->element = new Torro_UnitTest_Factory_For_Element( $this );
		$this->element_answer = new Torro_UnitTest_Factory_For_Element_Answer( $this );
		$this->element_setting = new Torro_UnitTest_Factory_For_Element_Setting( $this );
		$this->participant = new Torro_UnitTest_Factory_For_Participant( $this );
		$this->result = new Torro_UnitTest_Factory_For_Result( $this );
		$this->result_value = new Torro_UnitTest_Factory_For_Result_value( $this );
	}
}
