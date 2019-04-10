<?php
/**
 * Template: element-imagechoice.php
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

	<div<?php echo torro()->template()->attrs( $choices_attrs ); ?>>
		<?php foreach ( $choices as $index => $choice ) : ?>
			<?php
			$choice_input_attrs = $input_attrs;
			$choice_label_attrs = $label_attrs;

			$choice_input_attrs['id']  = str_replace( '%index%', $index + 1, $choice_input_attrs['id'] );
			$choice_label_attrs['id']  = str_replace( '%index%', $index + 1, $choice_label_attrs['id'] );
			$choice_label_attrs['for'] = str_replace( '%index%', $index + 1, $choice_label_attrs['for'] );
			?>
			<div<?php echo torro()->template()->attrs( $choice_attrs ); ?>>
				<label<?php echo torro()->template()->attrs( $choice_label_attrs ); ?>>
					<div class="torro-imagechoice-image">
						<input type="radio"<?php echo torro()->template()->attrs( $choice_input_attrs ); ?> value="<?php echo torro()->template()->esc_attr( $choice ); ?>"<?php echo $value === $choice ? ' checked' : ''; ?>>
						<?php echo $images[ $choice ]['img']; ?>
					</div>
					<?php if ( ! empty( $images[ $choice ]['title'] ) && $title_after_image ): ?>
						<div class="torro-imagechoice-title"><?php echo $images[ $choice ]['title']; ?></div>
					<?php endif; ?>
				</label>
			</div>
		<?php endforeach; ?>

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
</fieldset>
