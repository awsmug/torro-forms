<?php
/**
 * Template: element-media.php
 *
 * Available data: $id, $container_id, $label, $sort, $type, $value, $input_attrs, $label_required, $label_attrs, $wrap_attrs, $description, $description_attrs, $errors, $errors_attrs, $before, $after, $hidden_name
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
		<?php if ( ! empty( $description ) ) : ?>
			<div<?php echo torro()->template()->attrs( $description_attrs ); ?>>
				<?php echo torro()->template()->esc_kses_basic( $description ); ?>
			</div>
		<?php endif; ?>

		<input type="file"<?php echo torro()->template()->attrs( $input_attrs ); ?>>

		<input type="hidden" name="<?php echo torro()->template()->esc_attr( $hidden_name ); ?>" value="<?php echo torro()->template()->esc_attr( $value ); ?>">

		<?php
		if ( ! empty( $value ) ) :
			$attachment = get_post( $value );
			if ( $attachment && 'image' === substr( $attachment->post_mime_type, 0, 5 ) ) : ?>
				<img src="<?php echo wp_get_attachment_image_url( $attachment->ID, 'full' ); ?>" style="max-width:150px;height:auto;">
			<?php endif;
		endif;
		?>

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
</div>
