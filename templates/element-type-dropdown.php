<?php
/**
 * Template: element-type-dropdown.php
 *
 * Available data: $element_id, $type, $id, $name, $classes, $description, $required, $answers, $response, $has_error, $has_success, $extra_attr
 *
 * @package TorroForms
 * @subpackage Templates
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.4
 */

$aria_describedby = ' aria-describedby="' . esc_attr( $id ) . '-description' . ( $has_error ? ' ' . esc_attr( $id ) . '-errors' : '' ) . '"';
$aria_invalid = $has_error ? ' aria-invalid="true"' : '';
$aria_required = $required ? ' aria-required="true"' : '';
?>
<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $extra_attr . $aria_describedby . $aria_required . $aria_invalid; ?>>
	<?php foreach ( $answers as $answer ) : ?>
		<option value="<?php echo esc_attr( $answer['value'] ); ?>"<?php echo $answer['value'] === $response ? ' selected' : ''; ?>><?php echo esc_html( $answer['label'] ); ?></option>
	<?php endforeach; ?>
</select>
