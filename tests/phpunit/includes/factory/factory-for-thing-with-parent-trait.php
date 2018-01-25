<?php

namespace awsmug\Torro_Forms\Tests;

trait Factory_For_Thing_With_Parent_Trait {

	protected $parent_id_field_name;

	public function update_parent( $id, $parent_id ) {
		return $this->update_object( $id, array( $parent_id_field_name => $parent_id ) );
	}
}
