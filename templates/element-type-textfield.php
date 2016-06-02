<?php
/**
 * Template: element-type.php
 *
 * Available data: $type, $id, $name, $classes, $description, $required, $answers, $response, $extra_attr, $limits_text
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.4
 */

$aria_required = $required ? ' aria-required="true"' : '';
?>
<input type="<?php echo esc_attr( $type ); ?>" id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" value="<?php echo esc_attr( $response ); ?>"<?php echo $extra_attr; ?> aria-describedby="<?php echo esc_attr( $id ); ?>-description <?php echo esc_attr( $id ); ?>-errors"<?php echo $aria_required; ?>>

<?php if ( ! empty( $limits_text ) ) : ?>
	<div class="limits-text">
		<?php echo esc_html( $limits_text ); ?>
	</div>
<?php endif; ?>
