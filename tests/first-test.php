<?php

class FirstTest extends PHPUnit_Extensions_SeleniumTestCase{
	public function setUp(){
		$this->setHost( 'localhost' );
		$this->setPort( 4444 );
		$this->setBrowser( 'firefox' );
		$this->setBrowserUrl( 'http://plugins/' );
	}
	
	public function test(){
		$this->open( "wp-login.php" );
		$this->type( "id=user_login", "test" );
		$this->type( "id=user_pass", "test" );
		$this->click( "id=wp-submit" );
	    $this->waitForPageToLoad( "30000" );
		
		$this->open("/survey/kopie-umfrage-melle-2/");
	    $this->click("name=surveyval_response[101][]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][8]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][16]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][24]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][32]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][40]");
	    $this->click("document.surveyval.elements['surveyval_response[101][]'][48]");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->select("name=surveyval_response[105]", "label=7");
	    $this->type("name=surveyval_response[106]", "111");
	    $this->select("name=surveyval_response[109]", "label=7");
	    $this->type("name=surveyval_response[110]", "111");
	    $this->select("name=surveyval_response[113]", "label=3");
	    $this->type("name=surveyval_response[114]", "25");
	    $this->type("name=surveyval_response[115]", "2");
	    $this->type("name=surveyval_response[117]", "180");
	    $this->type("name=surveyval_response[118]", "110");
	    $this->select("name=surveyval_response[119]", "label=Ja");
	    $this->select("name=surveyval_response[120]", "label=Weniger als 10");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][8]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][15]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][22]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][29]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][36]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][46]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][57]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][66]");
	    $this->click("document.surveyval.elements['surveyval_response[122][]'][75]");
	    $this->click("name=surveyval_submission_back");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission_back");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->type("name=surveyval_response[110]", "11167");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->type("name=surveyval_response[110]", "1112");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission_back");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission_back");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->type("name=surveyval_response[117]", "18011");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->type("name=surveyval_response[117]", "180");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->click("name=surveyval_submission");
	    $this->waitForPageToLoad("30000");
	    $this->assertEquals("Danke für die Teilnahme an der Umfrage!", $this->getText("//div[@id='surveyval-thank-participation']"));
	}
}
