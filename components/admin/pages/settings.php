<div class="wrap questions">
	<form name="questions_settings" id="questions-settings" action="<?php echo $_SERVER[ 'REQUEST_URI' ]; ?>" method="POST">
		<?php wp_nonce_field( 'questions_save_settings', 'questions_save_settings_field' ); ?>
		<h2 class="nav-tab-wrapper">
			<a href="<?php echo admin_url( 'admin.php?page=ComponentQuestionsAdmin' ); ?>" class="nav-tab nav-tab-active"><?php _e( 'General', 'questions-locale' ); ?></a>
		</h2>
		
		<h3><?php _e( 'Text templates', 'questions-locale' ); ?></h3>
		
		<table class="form-table">
			<tr>
				<th class="titledesc"><?php _e( 'Thanks for participating', 'questions-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used on thanking members after participating survey.', 'questions-locale' ); ?></p><br />
					<input class="settings-template-subject" type="text" name="questions_thankyou_participating_subject_template" value="<?php echo qu_get_mail_template_subject( 'thankyou_participating' ); ?>" /><br />
					<textarea id="questions-thankyou-participating-text-template" name="questions_thankyou_participating_text_template" class="questions-text-template" /><?php echo qu_get_mail_template_text( 'thankyou_participating' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'questions-locale' ); ?> %username%, %displayname%, %survey_title%, %site_name%</span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'Invitation text Template', 'questions-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used as template if you want to send invitations to Participiants.', 'questions-locale' ); ?></p>
					<input class="settings-template-subject" type="text" name="questions_invitation_subject_template" value="<?php echo qu_get_mail_template_subject( 'invitation' ); ?>" /><br />
					<textarea id="questions-invitation-text-template" name="questions_invitation_text_template" class="questions-text-template" /><?php echo qu_get_mail_template_text( 'invitation' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'questions-locale' ); ?> %username%, %displayname%, %survey_title%, %survey_url%, %site_name%</span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'Reinvitation text Template', 'questions-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'This text will be used as template if you want to send reinvitations to Participiants.', 'questions-locale' ); ?></p>
					<input class="settings-template-subject" type="text" name="questions_reinvitation_subject_template" value="<?php echo qu_get_mail_template_subject( 'reinvitation' ); ?>" /><br />
					<textarea id="questions-reinvitation-text-template" name="questions_reinvitation_text_template" class="questions-text-template" /><?php echo qu_get_mail_template_text( 'reinvitation' ); ?></textarea>
					<br /><span class="description"><?php _e( 'Usable template tags:', 'questions-locale' ); ?> %username%, %displayname%, %survey_title%, %survey_url%, %site_name%</span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'From Name', 'questions-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'The Name which will be shown on Emails which are send out by Questions.', 'questions-locale' ); ?></p>
					<input class="settings-template-subject" type="text" name="questions_mail_from_name" value="<?php echo qu_get_mail_settings( 'from_name' ); ?>" /><br />
					<br /><span class="description"><?php _e( 'e.g. Michael Jackson', 'questions-locale' ); ?></span>
				</td>
			</tr>
			<tr>
				<th class="titledesc"><?php _e( 'From Email', 'questions-locale' ); ?></th>
				<td class="forminp forminp-textarea">
					<p><?php _e( 'The Email will be used for the reply of the emails, send out by questions.', 'questions-locale' ); ?></p>
					<input class="settings-template-subject" type="text" name="questions_mail_from_email" value="<?php echo qu_get_mail_settings( 'from_email' ); ?>" /><br />
					<br /><span class="description"><?php _e( 'e.g. myname@mydomain.com', 'questions-locale' ); ?></span>
				</td>
			</tr>
		</table>
		<input type="submit" name="questions_settings_save" class="button button-primary" value="<?php _e( 'Save Changes', 'questions-locale' ); ?>" />
	</form>
</div>