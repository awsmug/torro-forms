<?php
/**
 * Template: element-onechoice.php
 *
 * Available data: $element_id, $label, $id, $classes, $errors, $required, $type
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.4
 */

$aria_required = $required ? ' aria-required="true"' : '';
?>
<div class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php do_action( 'torro_element_start', $element_id ); ?>

	<fieldset role="radiogroup"<?php echo $aria_required; ?>>
		<legend>
			<?php echo esc_html( $label ); ?>
			<?php if ( $required ) : ?>
				<span class="required">*</span>
			<?php endif; ?>
		</legend>

		<div>
			<?php torro()->template( 'element-type', $type ); ?>

			<?php if ( 0 < count( $errors ) ) : ?>
				<ul id="<?php echo esc_attr( $id ); ?>-errors" class="error-messages">
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo $error; ?></li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>
		</div>
	</fieldset>

	<?php do_action( 'torro_element_end', $element_id ); ?>
</div>
