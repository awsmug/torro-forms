<?php
/**
 * Template: element-type-dropdown.php
 *
 * Available data: $type, $id, $name, $classes, $description, $required, $answers, $response, $extra_attr
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.4
 */

$aria_required = $required ? ' aria-required="true"' : '';
?>
<select id="<?php echo esc_attr( $id ); ?>" name="<?php echo esc_attr( $name ); ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"<?php echo $extra_attr; ?> aria-describedby="<?php echo esc_attr( $id ); ?>-description <?php echo esc_attr( $id ); ?>-errors"<?php echo $aria_required; ?>>
	<?php foreach ( $answers as $answer ) : ?>
		<option value="<?php echo esc_attr( $answer['value'] ); ?>"<?php echo $answer['value'] === $response ? ' selected' : ''; ?>><?php echo esc_html( $answer['label'] ); ?></option>
	<?php endforeach; ?>
</select>
