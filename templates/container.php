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

<?php
/**
 * Allows to print additional content before partials rendered.
 *
 * @since 1.0.5
 *
 * @param int   $form_id  Form ID
 * @param array $elements Array with elements
 */
do_action( "{$this->get_prefix()}container_partials_before", $form_id, $elements );
?>

<?php foreach ( $elements as $element ) : ?>
	<?php torro()->template()->get_partial( 'element', $element ); ?>
<?php endforeach; ?>

<?php
/**
 * Allows to print additional content after partials rendered.
 *
 * @since 1.0.5
 *
 * @param int   $form_id  Form ID
 * @param array $elements Array with elements
 */
do_action( "{$this->get_prefix()}container_partials_after", $form_id, $elements );
?>
