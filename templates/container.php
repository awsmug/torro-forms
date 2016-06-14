<?php
/**
 * Template: container.php
 *
 * Available data: $container_id, $title, $hidden_fields, $elements
 *
 * @package TorroForms
 * @subpackage Templates
 * @version 1.0.0-beta.5
 * @since 1.0.0-beta.4
 */
?>
<?php echo $hidden_fields; ?>

<?php if ( $title ) : ?>
	<h2 class="container-title"><?php echo esc_html( $title ); ?></h2>
<?php endif; ?>

<?php foreach ( $elements as $element ) : ?>
	<?php torro()->template( 'element', $element ); ?>
<?php endforeach; ?>
