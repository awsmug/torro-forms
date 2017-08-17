/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v3 (http://www.gnu.org/licenses/gpl-3.0.html)
 */
( function( $ ) {
	'use strict';

	function checkRightContainerOffset() {
		var offset = 0;
		var $postbox = $( '#postbox-container-1' );

		if ( $( 'body' ).hasClass( 'admin-bar' ) ) {
			if ( document.documentElement.clientWidth <= 782 ) {
				offset = 46;
			} else {
				offset = 32;
			}
		}

		if ( $postbox.height() < $( window ).height() - offset ) {
			if ( $( window ).scrollTop() + offset >= $( '#titlediv' ).offset().top - 10 ) {
				$postbox.addClass( 'fixed' );
			} else {
				$postbox.removeClass( 'fixed' );
			}
		} else {
			$postbox.removeClass( 'fixed' );
		}
	}

	checkRightContainerOffset();
	$( window ).on( 'scroll', checkRightContainerOffset );

}( window.jQuery ) );
