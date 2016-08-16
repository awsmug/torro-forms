<?php
/**
 * Template: element-type-onechoice.php
 *
 * Available data: $element_id, $type, $id, $name, $classes, $description, $required, $answers, $response, $has_error, $has_success, $extra_attr
 *
 * @package TorroForms
 * @subpackage Templates
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.4
 */

$aria_describedby = ' aria-describedby="' . esc_attr( $id ) . '-description' . ( $has_error ? ' ' . esc_attr( $id ) . '-errors' : '' ) . '"';
$aria_invalid = $has_error ? ' aria-invalid="true"' : '';
$aria_required = $required ? ' aria-required="true"' : '';
?>
<?php foreach ( $answers as $i => $answer ) : ?>
	<div class="torro_element_radio">
		<?php $checked = $answer['value'] === $response ? ' checked' : ''; ?>
		<input type="radio" id="<?php echo esc_attr( $id ) . '-' . $i; ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" value="<?php echo esc_attr( $answer['value'] ); ?>"<?php echo $checked . $aria_describedby; ?>>
		<label for="<?php echo esc_attr( $id ) . '-' . $i; ?>">
			<?php echo esc_html( $answer['label'] ); ?>
		</label>
	</div>
<?php endforeach; ?>
