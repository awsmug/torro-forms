<?php
/**
 * Template: element.php
 *
 * Available data: $element_id, $label, $id, $classes, $errors, $description, $required, $type
 *
 * @package TorroForms
 * @subpackage Templates
 * @version 1.0.0-beta.7
 * @since 1.0.0-beta.4
 */
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php do_action( 'torro_element_start', $element_id ); ?>

	<label for="<?php echo esc_attr( $id ); ?>">
		<?php echo esc_html( $label ); ?>
		<?php if ( $required ) : ?>
			<span class="required">*</span>
		<?php endif; ?>
	</label>

	<div>
		<?php torro()->template( 'element-type', $type ); ?>

		<?php if ( ! empty( $description ) ) : ?>
			<div id="<?php echo esc_attr( $id ); ?>-description" class="element-description">
				<?php echo $description; ?>
			</div>
		<?php endif; ?>

		<?php if ( 0 < count( $errors ) ) : ?>
			<ul id="<?php echo esc_attr( $id ); ?>-errors" class="error-messages">
				<?php foreach ( $errors as $error ) : ?>
					<li><?php echo $error; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php do_action( 'torro_element_end', $element_id ); ?>
</div>
