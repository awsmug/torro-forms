<?php
/**
 * Template: form.php
 *
 * Available data: $id, $title, $slug, $author, $status, $timestamp, $timestamp_modified, $form_attrs, $hidden_fields, $navigation, $current_container
 *
 * @package TorroForms
 * @since 1.0.0
 */

?>
<form<?php echo torro()->template()->attrs( $form_attrs ); ?>>
	<?php if ( $current_container ) : ?>
		<?php torro()->template()->get_partial( 'container', $current_container ); ?>
	<?php endif; ?>

	<?php if ( ! empty( $navigation['submit_button']['before'] ) ) : ?>
		<?php echo $navigation['submit_button']['before']; ?>
	<?php endif; ?>

	<div class="torro-pager">
		<?php if ( ! empty( $navigation['prev_button'] ) ) : ?>
			<div class="prev">
				<button<?php echo torro()->template()->attrs( $navigation['prev_button']['attrs'] ); ?>><?php echo esc_html( $navigation['prev_button']['label'] ); ?></button>
			</div>
		<?php endif; ?>

		<?php if ( ! empty( $navigation['next_button'] ) ) : ?>
			<div class="next">
				<button<?php echo torro()->template()->attrs( $navigation['next_button']['attrs'] ); ?>><?php echo esc_html( $navigation['next_button']['label'] ); ?></button>
			</div>
		<?php elseif ( ! empty( $navigation['submit_button'] ) ) : ?>
			<div class="next">
				<button<?php echo torro()->template()->attrs( $navigation['submit_button']['attrs'] ); ?>><?php echo esc_html( $navigation['submit_button']['label'] ); ?></button>
			</div>
		<?php endif; ?>
	</div>

	<?php if ( ! empty( $navigation['submit_button']['after'] ) ) : ?>
		<?php echo $navigation['submit_button']['after']; ?>
	<?php endif; ?>

	<?php echo $hidden_fields; ?>
</form>
