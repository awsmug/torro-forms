<?php

namespace Leaves_And_Love\Sample_Plugin;

use Leaves_And_Love\Plugin_Lib\Service;
use Leaves_And_Love\Plugin_Lib\Traits\Hooks_Trait;

class Filters extends Service {
	use Hooks_Trait;

	public function __construct( $prefix ) {
		$this->set_prefix( $prefix );
	}

	public function add( $tag, $mode = 'func' ) {
		return $this->add_filter( $tag, $this->get_callback( $mode ) );
	}

	public function has( $tag, $mode = 'func' ) {
		return $this->has_filter( $tag, $this->get_callback( $mode ) );
	}

	public function remove( $tag, $mode = 'func' ) {
		return $this->remove_filter( $tag, $this->get_callback( $mode ) );
	}

	public function get_public_string() {
		return 'public';
	}

	private function get_private_string() {
		return 'private';
	}

	private function get_callback( $mode = 'func' ) {
		if ( 'public' === $mode ) {
			return array( $this, 'get_public_string' );
		}

		if ( 'private' === $mode ) {
			return array( $this, 'get_private_string' );
		}

		return 'sp_get_string';
	}
}
