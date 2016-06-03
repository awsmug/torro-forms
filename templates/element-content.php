<?php
/**
 * Template: element-content.php
 *
 * Available data: $element_id, $label, $id, $classes, $errors, $required, $type
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.4
 */
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php do_action( 'torro_element_start', $element_id ); ?>

	<?php echo wpautop( $label ); ?>

	<?php do_action( 'torro_element_end', $element_id ); ?>
</div>
