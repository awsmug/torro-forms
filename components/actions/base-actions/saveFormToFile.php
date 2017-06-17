<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class SaveFormToFile_Action extends Torro_Form_Action {
	/**
	 * Instance
	 *
	 * @var null|SaveFormToFile_Action
	 * @since 1.0.0
	 */
	private static $instance = null;

	/**
	 * Singleton
	 *
	 * @return SaveFormToFile_Action
	 * @since 1.0.0
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initializing action
	 *
	 * @since 1.0.0
	 */
	protected function init() {
		$this->title = __( 'SaveFormToFile', 'torro-forms' );
		$this->name  = 'SaveFormToFile';
	}
	
	/**
	 * Handles SaveFormToFile
	 *
	 * @param $form_id
	 * @param $response_id
	 * @param $response
	 *
	 * @since 1.0.0
	 */
	public function handle( $form_id, $response_id, $response ) {
		
        $write_to_file = get_post_meta( $form_id, 'write_to_file', true );
        $filename_text = get_post_meta( $form_id, 'filename_text', 'druckanfrage.csv' );
		if( 'yes' == $write_to_file ) {
			
			$filename_result=$filename_text;
		//	$filename_result="druckanfrage.csv";
		
		//create file if it not exist yet with the actual labels
			if(!is_file($filename_result)){
				$labels = '';
				$form = torro()->forms()->get( $form_id );
				foreach ( $form->elements as $element_form ) {
					if( ! empty( $element_form->label ) && false !== $element_form->type_obj->input ) {
						$labels .= $element_form->label;
						$labels .= ",";
					}
				}
				$labels = substr($labels,0,strlen($labels)-1);
				$labels .= "\n";
				file_put_contents($filename_result,$labels,FILE_APPEND);
			}
			
		//append actual data in file
			$total_count = torro()->results()->query( array(
				'number'	=> -1,
				'count'		=> true,
				'form_id'	=> $form_id,
			) );
			
			$results = torro()->results()->query( array(
				'number'	=> 1,
				'offset'	=> 0,
				'form_id'	=> $form_id,
				'orderby'   => 'timestamp',
				'order'     => 'desc'
			) );
			$content = '';
			foreach ( $results as $result ) {
				foreach ( $result->values as $result_value ) {
					$content .= $result_value->value;
					$content .= ",";
				}
				$content = substr($content,0,strlen($content)-1);
				$content .= "\n";
				file_put_contents($filename_result,$content,FILE_APPEND);
			}
        }
	}

	/**
	 * Option content
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function option_content( $form_id ) {
        $write_to_file = get_post_meta( $form_id, 'write_to_file', true );
        $filename_text = get_post_meta( $form_id, 'filename_text', 'druckanfrage.csv' );
        
        if( 'yes' == $write_to_file )
            $checked = ' checked="checked"';
    
        $html = '<p>Write new form values into a file after form was submitted.</p>';
        $html.= '<input type="checkbox" name="write_to_file" value="yes"' . $checked . '> Enable write to file';
        $html.= '<br>';
		$html.= 'Filename: <input type="text" name="filename_text" value="'. $filename_text . '"><br>';
              
        return $html;
	}
	
    /**
     * Saving the data of the option content in the formbuilder
     *
     * @param int $form_id
     */
    public function save( $form_id ) {
		$write_to_file = wp_unslash( $_POST['write_to_file'] );
		update_post_meta( $form_id, 'write_to_file', $write_to_file );
		
		$filename_text = wp_unslash( $_POST['filename_text'] );
		update_post_meta( $form_id, 'filename_text', $filename_text );
    }
}

torro()->actions()->register( 'SaveFormToFile_Action' );
