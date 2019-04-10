( function() {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function() {
		var imageChoiceInputs = document.querySelectorAll( '.torro-element-imagechoice .torro-element-label input' );

		for ( var i = 0 ; i < imageChoiceInputs.length ; i++ ) {
			imageChoiceInputs[i].addEventListener( 'change', imageChoiceChange );
		}
	});

	function imageChoiceChange( event ) {
		var input = event.target;
		var choices = input.closest( '.torro-element-imagechoice' ).querySelectorAll( '.torro-imagechoice' );

		for ( var i = 0 ; i < choices.length ; i++ ) {
			choices[i].classList.remove( 'torro-imagechoice-checked' );
		}

		if ( input.checked ) {
			input.closest( '.torro-imagechoice' ).classList.add( 'torro-imagechoice-checked' );
		}
	}
}() );
