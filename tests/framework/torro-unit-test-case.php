<?php
/**
 * @package TorroForms
 * @subpackage Tests
 */

class Torro_UnitTestCase extends WP_UnitTestCase {
	private $names = array( 'John Doe', 'Jane Doe', 'Mr WordPress', 'Wapuuman' );

	public function go_to_forms() {
		$this->go_to( admin_url( 'edit.php?post_type=torro_form' ) );
	}

	public function create_full_form( $title = 'Test Form' ) {
		$form = torro()->forms()->create( array(
			'title'	=> $title,
		) );

		$container = torro()->containers()->create( $form->id, array(
			'label'	=> 'Page 1',
			'sort'	=> 0,
		) );

		$element = torro()->elements()->create( $container->id, array(
			'type'	=> 'textfield',
			'label'	=> 'Your Name',
			'sort'	=> 0,
		) );

		$element = torro()->elements()->create( $container->id, array(
			'type'	=> 'onechoice',
			'label'	=> 'What is your favorite sport?',
			'sort'	=> 1,
		) );

		$element_answer = torro()->element_answers()->create( $element->id, array(
			'answer'	=> 'Soccer',
			'sort'		=> 0,
		) );

		$element_answer = torro()->element_answers()->create( $element->id, array(
			'answer'	=> 'Football',
			'sort'		=> 1,
		) );

		$element_answer = torro()->element_answers()->create( $element->id, array(
			'answer'	=> 'Tennis',
			'sort'		=> 2,
		) );

		return $form->id;
	}

	public function create_full_form_results( $form_id, $count = 10 ) {
		$container_ids = torro()->containers()->query( array(
			'form_id'	=> $form_id,
		) );
		$container_ids = array_map( array( $this, '_object_to_id' ), $container_ids );

		$elements = torro()->elements()->query( array(
			'container_id'	=> $container_ids,
		) );

		$result_ids = array();

		for ( $i = 0; $i < $count; $i++ ) {
			$result = torro()->results()->create( $form_id, array(
				'user_id'		=> 1,
				'timestamp'		=> current_time( 'timestamp' ),
				'remote_addr'	=> '',
				'cookie_key'	=> '',
			) );

			foreach ( $elements as $element ) {
				$value = '';
				if ( 'textfield' === $element->type ) {
					$value = $this->names[ rand( 0, count( $this->names ) - 1 ) ];
				} else {
					$value = 'Soccer';
				}
				$result_value = torro()->result_values()->create( $result->id, array(
					'element_id'	=> $element->id,
					'value'			=> $value,
				) );
			}

			$result_ids[] = $result->id;
		}

		return $result_ids;
	}

	public function _object_to_id( $obj ) {
		return $obj->id;
	}
}
