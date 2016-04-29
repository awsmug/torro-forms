<?php
/**
 * @package TorroForms
 * @subpackage Tests
 */

class Torro_UnitTestCase extends WP_UnitTestCase {
	public function go_to_forms() {
		$this->go_to( admin_url( 'edit.php?post_type=torro_form' ) );
	}
}
