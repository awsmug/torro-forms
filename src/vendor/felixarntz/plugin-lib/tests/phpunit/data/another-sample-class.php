<?php

class Another_Sample_Class extends Leaves_And_Love_Plugin {
	protected $options;

	public function activate( $network_wide ) {
		// do nothing
	}

	public function deactivate( $network_wide ) {
		// do nothing
	}

	public static function uninstall( $network_wide ) {
		// do nothing
	}

	public function get_activation_hook() {
		return array( $this, 'activate' );
	}

	public function get_deactivation_hook() {
		return array( $this, 'deactivate' );
	}

	public function get_uninstall_hook() {
		return array( __CLASS__, 'uninstall' );
	}

	protected function load_base_properties() {
		$this->version = '1.0.0';
		$this->prefix = 'asc_';
		$this->vendor_name = 'Leaves_And_Love';
		$this->project_name = 'Another_Sample_Class';
		$this->minimum_php = '5.4';
		$this->minimum_wp = '4.7';
	}

	protected function load_textdomain() {
		if ( version_compare( get_bloginfo( 'version' ), '4.6', '>=' ) ) {
			return;
		}

		load_plugin_textdomain( 'another-sample-class' );
	}

	protected function load_messages() {
		$this->messages['cheatin_huh']  = __( 'Cheatin&#8217; huh?', 'another-sample-class' );
		$this->messages['outdated_php'] = __( 'Another Sample Class cannot be initialized because your setup uses a PHP version older than %s.', 'another-sample-class' );
		$this->messages['outdated_wp']  = __( 'Another Sample Class cannot be initialized because your setup uses a WordPress version older than %s.', 'another-sample-class' );
	}

	protected function instantiate_services() {
		$this->options = $this->instantiate_library_service( 'Options', $this->prefix );
	}

	protected function add_hooks() {

	}
}
