<?php

class Yet_Another_Sample_Class extends Leaves_And_Love_Plugin {
	protected $options;

	protected function load_base_properties() {
		$this->version = '1.0.0';
		$this->prefix = 'asc_';
		$this->vendor_name = 'Leaves_And_Love';
		$this->project_name = 'Yet_Another_Sample_Class';
		$this->minimum_php = '99.0';
		$this->minimum_wp = '4.7';
	}

	protected function load_textdomain() {
		if ( version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {
			return;
		}

		load_plugin_textdomain( 'yet-another-sample-class' );
	}

	protected function load_messages() {
		$this->messages['cheatin_huh']  = __( 'Cheatin&#8217; huh?', 'yet-another-sample-class' );
		$this->messages['outdated_php'] = __( 'Yet Another Sample Class cannot be initialized because your setup uses a PHP version older than %s.', 'yet-another-sample-class' );
		$this->messages['outdated_wp']  = __( 'Yet Another Sample Class cannot be initialized because your setup uses a WordPress version older than %s.', 'yet-another-sample-class' );
	}

	protected function instantiate_services() {
		$this->options = $this->instantiate_library_service( 'Options', $this->prefix );
	}

	protected function add_hooks() {

	}
}
