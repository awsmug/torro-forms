<?php

class Test_Container_Service_Trait_Class {
	use Leaves_And_Love\Plugin_Lib\Traits\Container_Service_Trait;

	protected static $service_cache = 'Leaves_And_Love\Plugin_Lib\Cache';
	protected static $service_options = 'Leaves_And_Love\Plugin_Lib\Options';

	public function __construct( $services ) {
		$this->set_services( $services );
	}
}
