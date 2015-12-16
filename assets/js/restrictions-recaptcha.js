function AF_reCAPTCHA_Widget( tag_id, params ) {
	this.tag_id = tag_id;
	this.params = params;

	var widget_id = grecaptcha.render( this.tag_id, this.params );

	if ( typeof widget_id === 'number' ) {
		this.widget_id = widget_id;
	} else {
		console.error( 'reCAPTCHA widget for tag ' + this.tag_id + ' could not be rendered' );
		this.widget_id = false;
	}
}

AF_reCAPTCHA_Widget.prototype = {
	get_response: function() {
		if ( typeof this.widget_id !== 'number' ) {
			return null;
		}
		return grecaptcha.getResponse( this.widget_id );
	},

	reset: function() {
		if ( typeof this.widget_id !== 'number' ) {
			return;
		}
		grecaptcha.reset( this.widget_id );
	}
};

var af_reCAPTCHA_widget_instances = {};

var af_reCAPTCHA_widgets_init = function() {
	if ( ! _af_recaptcha_settings.sitekey ) {
		return;
	}

	var captcha_tags = document.getElementsByClassName( 'recaptcha-placeholder' );

	for ( var i = 0; i < captcha_tags.length; i++ ) {
		var captcha_tag_id = captcha_tags[ i ].getAttribute( 'id' );

		af_reCAPTCHA_widget_instances[ captcha_tag_id ] = new AF_reCAPTCHA_Widget( captcha_tag_id, {
			'sitekey': _af_recaptcha_settings.sitekey,
			'type': captcha_tags[ i ].getAttribute( 'data-type' ),
			'size': captcha_tags[ i ].getAttribute( 'data-size' ),
			'theme': captcha_tags[ i ].getAttribute( 'data-theme' )
		});
	}

	// compatibility with Contact Form 7
	if ( typeof recaptchaCallback === 'function' ) {
		recaptchaCallback();
	}
};
