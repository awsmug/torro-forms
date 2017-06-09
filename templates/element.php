<?php
/**
 * Template: element.php
 *
 * Available data: $id, $container_id, $label, $sort, $type, $value, $input_attrs, $label_required, $label_attrs, $wrap_attrs, $description, $description_attrs, $errors, $errors_attrs, $before, $after
 *
 * @package TorroForms
 * @since 1.0.0
 */
?>
<div<?php echo torro()->template()->attrs( $wrap_attrs ); ?>>
	<?php if ( ! empty( $before ) ) : ?>
		<?php echo $before; ?>
	<?php endif; ?>

	<label<?php echo torro()->template()->attrs( $label_attrs ); ?>>
		<?php echo esc_html( $label ); ?>
		<?php echo $label_required; ?>
	</label>

	<div>
		<input type="text"<?php echo torro()->template()->attrs( $input_attrs ); ?>>

		<?php if ( ! empty( $description ) ) : ?>
			<div<?php echo torro()->template()->attrs( $description_attrs ); ?>>
				<?php echo $description; ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<ul<?php echo torro()->template()->attrs( $errors_attrs ); ?>>
				<?php foreach ( $errors as $error_code => $error_message ) : ?>
					<li><?php echo $error_message; ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $after ) ) : ?>
		<?php echo $after; ?>
	<?php endif; ?>
</div>
