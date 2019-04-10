/*!
 * Torro Forms Version 1.0.4 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function() {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function() {
		addEventListenerAll( '.torro-element-imagechoice .torro-element-label input', imageChoiceChange );
		addEventListenerAll( '.torro-element-range .torro-input input', rangeChange );
		addEventListenerAll( '.torro-element-range .torro-helper-input input', rangeChangeBack );
	});

	function addEventListenerAll ( selector, callback ) {
		var nodes = document.querySelectorAll( selector );

		for ( var i = 0 ; i < nodes.length ; i++ ) {
			nodes[i].addEventListener( 'change', callback );
		}
	}

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

	function rangeChange( event ) {
		var input = event.target;
		var value = input.value;

		var helper_input = input.closest( '.torro-element-range' ).querySelector('.torro-helper-input input' );
		helper_input.value = value;
	}

	function rangeChangeBack( event ) {
		var helper_input = event.target;
		var value = helper_input.value;

		var input = helper_input.closest( '.torro-element-range' ).querySelector('.torro-input input' );

		console.log(input);

		input.value = value;
	}

}() );
