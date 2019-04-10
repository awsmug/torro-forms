<?php
/**
 * Template: element-range.php
 *
 * Available data: $id, $container_id, $label, $sort, $type, $value, $input_attrs, $label_required, $label_attrs, $wrap_attrs, $description, $description_attrs, $errors, $errors_attrs, $before, $after, $helper_input, $helper_input_attrs
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
		<?php echo torro()->template()->esc_kses_basic( $label ); ?>
		<?php echo torro()->template()->esc_kses_basic( $label_required ); ?>
	</label>

	<div>
		<?php if( 'before' === $helper_input ): ?>
			<div class="torro-helper-input">
				<input type="text"<?php echo torro()->template()->attrs( $helper_input_attrs ); ?> />
			</div>
		<?php endif; ?>

		<div class="torro-input">
			<input type="range"<?php echo torro()->template()->attrs( $input_attrs ); ?>>
		</div>

		<?php if( 'after' === $helper_input ): ?>
			<div class="torro-helper-input">
				<input type="text"<?php echo torro()->template()->attrs( $helper_input_attrs ); ?> />
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $description ) ) : ?>
			<div<?php echo torro()->template()->attrs( $description_attrs ); ?>>
				<?php echo torro()->template()->esc_kses_basic( $description ); ?>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<ul<?php echo torro()->template()->attrs( $errors_attrs ); ?>>
				<?php foreach ( $errors as $error_code => $error_message ) : ?>
					<li><?php echo torro()->template()->esc_kses_basic( $error_message ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $after ) ) : ?>
		<?php echo $after; ?>
	<?php endif; ?>
</div>
