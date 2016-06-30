<?php
/**
 * Template: element-type-textarea.php
 *
 * Available data: $element_id, $type, $id, $name, $classes, $description, $required, $answers, $response, $has_error, $has_success, $extra_attr, $limits_text
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
<textarea id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $extra_attr . $aria_describedby . $aria_required . $aria_invalid; ?>><?php echo esc_html( $response ); ?></textarea>

<?php if ( ! empty( $limits_text ) ) : ?>
	<div class="limits-text">
		<?php echo esc_html( $limits_text ); ?>
	</div>
<?php endif; ?>
