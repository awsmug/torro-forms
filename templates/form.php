<?php
/**
 * Template: form.php
 *
 * Available data: $form_id, $title, $action_url, $hidden_fields, $current_container, $navigation
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.3
 * @since 1.0.0-beta.4
 */
?>
<form id="torro-form-<?php echo $form_id; ?>" class="torro-form" action="<?php echo esc_url( $action_url ); ?>" method="POST" method="post" enctype="multipart/form-data" novalidate>
	<?php echo $hidden_fields; ?>

	<?php torro()->template( 'container', $current_container ); ?>

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
