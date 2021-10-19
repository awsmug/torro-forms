<?php
/**
 * Template: element-multiplechoice.php
 *
 * Available data: $id, $container_id, $label, $sort, $type, $value, $input_attrs, $label_required, $label_attrs, $wrap_attrs, $description, $description_attrs, $errors, $errors_attrs, $before, $after, $choices, $legend_attrs
 *
 * @package TorroForms
 * @since 1.0.0
 */

?>
<fieldset<?php echo torro()->template()->attrs( $wrap_attrs ); ?>>
	<?php if ( ! empty( $before ) ) : ?>
		<?php echo $before; ?>
	<?php endif; ?>

	<legend<?php echo torro()->template()->attrs( $legend_attrs ); ?>>
		<?php echo torro()->template()->esc_kses_basic( $label ); ?>
		<?php echo torro()->template()->esc_kses_basic( $label_required ); ?>
	</legend>

	<div>
		<?php if ( ! empty( $description ) ) : ?>
			<div<?php echo torro()->template()->attrs( $description_attrs ); ?>>
				<?php echo torro()->template()->esc_kses_basic( $description ); ?>
			</div>
		<?php endif; ?>

		<?php foreach ( $choices as $index => $choice ) : ?>
			<?php
			$choice_input_attrs = $input_attrs;
			$choice_label_attrs = $label_attrs;

			$choice_input_attrs['id']  = str_replace( '%index%', $index + 1, $choice_input_attrs['id'] );
			$choice_label_attrs['id']  = str_replace( '%index%', $index + 1, $choice_label_attrs['id'] );
			$choice_label_attrs['for'] = str_replace( '%index%', $index + 1, $choice_label_attrs['for'] );
			?>
			<div class="torro-toggle">
				<input type="checkbox"<?php echo torro()->template()->attrs( $choice_input_attrs ); ?> value="<?php echo torro()->template()->esc_attr( $choice ); ?>"<?php echo in_array( $choice, $value, true ) ? ' checked' : ''; ?>>
				<label<?php echo torro()->template()->attrs( $choice_label_attrs ); ?>>
					<?php echo torro()->template()->esc_kses_basic( $choice ); ?>
				</label>
			</div>
		<?php endforeach; ?>

		<?php if ( ! empty( $errors ) ) : ?>
			<ul<?php echo torro()->template()->attrs( $errors_attrs ); ?> role="alert">
				<?php foreach ( $errors as $error_code => $error_message ) : ?>
					<li><?php echo torro()->template()->esc_kses_basic( $error_message ); ?></li>
				<?php endforeach; ?>
			</ul>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $after ) ) : ?>
		<?php echo $after; ?>
	<?php endif; ?>
</fieldset>
