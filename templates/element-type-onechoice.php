<?php
/**
 * Template: element-type-onechoice.php
 *
 * Available data: $type, $id, $name, $classes, $description, $required, $answers, $response, $extra_attr
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.4
 */
?>
<?php foreach ( $answers as $i => $answer ) : ?>
	<div class="torro_element_radio">
		<?php $checked = $answer['value'] === $response ? ' checked' : ''; ?>
		<input type="radio" id="<?php echo esc_attr( $id ) . '-' . $i; ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" value="<?php echo esc_attr( $answer['value'] ); ?>"<?php echo $checked; ?> aria-describedby="<?php echo esc_attr( $id ); ?>-description <?php echo esc_attr( $id ); ?>-errors">
		<label for="<?php echo esc_attr( $id ) . '-' . $i; ?>">
			<?php echo esc_html( $answer['label'] ); ?>
		</label>
	</div>
<?php endforeach; ?>
