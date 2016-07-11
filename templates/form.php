<?php
/**
 * Template: form.php
 *
 * Available data: $form_id, $title, $action_url, $classes, $hidden_fields, $current_container, $navigation
 *
 * @package TorroForms
 * @subpackage Templates
 * @version 1.0.0-beta.6
 * @since 1.0.0-beta.4
 */
?>
<form id="torro-form-<?php echo $form_id; ?>" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" action="<?php echo esc_url( $action_url ); ?>" method="post" enctype="multipart/form-data" novalidate>
	<?php echo $hidden_fields; ?>

	<?php if ( $current_container ) : ?>
		<?php torro()->template( 'container', $current_container ); ?>
	<?php endif; ?>

	<?php if ( $navigation['prev_button'] ) : ?>
		<input type="submit" name="<?php echo esc_attr( $navigation['prev_button']['name'] ); ?>" value="<?php echo esc_attr( $navigation['prev_button']['label'] ); ?>">
	<?php endif; ?>

	<?php if ( $navigation['next_button'] ) : ?>
		<input type="submit" name="<?php echo esc_attr( $navigation['next_button']['name'] ); ?>" value="<?php echo esc_attr( $navigation['next_button']['label'] ); ?>">
	<?php endif; ?>

	<?php if ( $navigation['submit_button'] ) : ?>
		<?php do_action( 'torro_form_send_button_before', $form_id ); ?>
		<input type="submit" name="<?php echo esc_attr( $navigation['submit_button']['name'] ); ?>" value="<?php echo esc_attr( $navigation['submit_button']['label'] ); ?>">
		<?php do_action( 'torro_form_send_button_after', $form_id ); ?>
	<?php endif; ?>
</form>
