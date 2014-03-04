<div class="wrap surveyval">
	<form name="surveyval_settings" id="surveyval-settings" action="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>" method="POST">
		<?php wp_nonce_field( 'surveyval_save_settings', 'surveyval_save_settings_field' ); ?>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo admin_url( 'admin.php?page=ComponentSurveyValAdmin' ); ?>" class="nav-tab nav-tab-active"><?php _e( 'General', 'surveyval-locale' ); ?></a>
		</h2>
		
		<h3><?php _e( 'Text templates', 'surveyval-locale' ); ?></h3>
		
		<table class="form-table">
			<tr>
				<th class="titledesc"><?php _e( 'Thanks for participating', 'surveyval-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used on thanking members after participating survey.', 'surveyval-locale' ); ?></p>
					<textarea id="surveyval-thankyou-participating-text-template" name="surveyval_thankyou_participating_text_template" class="surveyval-text-template" /><?php echo sv_get_mail_template_text( 'thankyou_participating' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'surveyval-locale' ); ?> %username%, %survey_title%, %site_name%</span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'Invitation text Template', 'surveyval-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used as template if you want to send invitations to Participiants.', 'surveyval-locale' ); ?></p>
					<textarea id="surveyval-invitation-text-template" name="surveyval_invitation_text_template" class="surveyval-text-template" /><?php echo sv_get_mail_template_text( 'invitation' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'surveyval-locale' ); ?> %username%, %survey_title%, %survey_url%, %site_name%</span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'Reinvitation text Template', 'surveyval-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used as template if you want to send reinvitations to Participiants.', 'surveyval-locale' ); ?></p>
					<textarea id="surveyval-reinvitation-text-template" name="surveyval_reinvitation_text_template" class="surveyval-text-template" /><?php echo sv_get_mail_template_text( 'reinvitation' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'surveyval-locale' ); ?> %username%, %survey_title%, %survey_url%, %site_name%</span>
				</td>
			</tr>
		</table>
		<input type="submit" name="surveyval_settings_save" class="button button-primary" value="<?php _e( 'Save Changes', 'surveyval-locale' ); ?>" />
	</form>
</div>