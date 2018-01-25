<?php
/**
 * Template: container.php
 *
 * Available data: $id, $form_id, $label, $sort, $required_description, $elements
 *
 * @package TorroForms
 * @since 1.0.0
 */

?>
<?php if ( ! empty( $label ) ) : ?>
	<h2 class="torro-container-title"><?php echo torro()->template()->esc_kses_basic( $label ); ?></h2>
<?php endif; ?>

<?php if ( ! empty( $required_description ) ) : ?>
	<p><?php echo torro()->template()->esc_kses_basic( $required_description ); ?></p>
<?php endif; ?>

<?php foreach ( $elements as $element ) : ?>
	<?php torro()->template()->get_partial( 'element', $element ); ?>
<?php endforeach; ?>
