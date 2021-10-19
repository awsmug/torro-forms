/*!
 * Torro Forms Version 1.0.8 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function() {
	'use strict';

	var supported, clipboardButtons, clipboardFields, i;

	function select( e ) {
		var clipboardField = e.srcElement;

		clipboardField.select();
	}

	clipboardFields = document.getElementsByClassName( 'clipboard-field' );
	for ( i = 0; i < clipboardFields.length; i++ ) {
		clipboardFields[ i ].addEventListener( 'focus', select );
	}

	try {
		supported = document.queryCommandSupported( 'copy' );
	} catch ( err ) {
		return;
	}

	if ( ! supported ) {
		return;
	}

	document.body.className = document.body.className.replace( 'no-clipboard', 'clipboard' );

	function copyToClipboard( e ) {
		var clipboardButton, clipboardField, selection;

		clipboardButton = e.target || e.srcElement;
		if ( 'BUTTON' !== clipboardButton.tagName.toUpperCase() ) {
			clipboardButton = clipboardButton.parentNode;
		}

		clipboardField = document.querySelector( clipboardButton.dataset.clipboardTarget );
		selection;

		if ( ! clipboardField ) {
			return;
		}

		clipboardField.select();

		try {
			document.execCommand( 'copy' );
		} catch ( err ) {
			console.warn( 'Could not copy content from ' + clipboardButton.dataset.clipboardTarget + ' to clipboard.' );
		}

		selection = window.getSelection ? window.getSelection() : document.selection;
		if ( selection ) {
			if ( selection.removeAllRanges ) {
				selection.removeAllRanges();
			} else if ( selection.empty ) {
				selection.empty();
			}
		}
	}

	clipboardButtons = document.getElementsByClassName( 'clipboard-button' );
	for ( i = 0; i < clipboardButtons.length; i++ ) {
		clipboardButtons[ i ].addEventListener( 'click', copyToClipboard );
	}
}() );
