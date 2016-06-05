<?php
/**
 * Template: recaptcha.php
 *
 * Available data: $id, $form_id, $type, $size, $theme, $error
 *
 * @package TorroForms
 * @subpackage Includes
 * @version 1.0.0-beta.4
 * @since 1.0.0-beta.5
 */
?>
<div class="torro-recaptcha">
	<div id="<?php echo $id; ?>" class="recaptcha-placeholder" data-form-id="<?php echo $form_id; ?>" data-type="<?php echo $type; ?>" data-size="<?php echo $size; ?>" data-theme="<?php echo $theme; ?>" style="margin-bottom:20px;"></div>
	<?php if ( ! empty( $error ) ) : ?>
		<ul class="error-messages">
			<li><?php echo $error; ?></li>
		</ul>
	<?php endif; ?>
</div>
