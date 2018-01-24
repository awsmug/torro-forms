/*!
 * Torro Forms Version 1.0.0-beta.8 (http://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( torro, $, elementMappings ) {
	'use strict';

	var apiAction, i;

	for ( i in elementMappings ) {
		apiAction = elementMappings[ i ];
		console.log( apiAction );
	}

}( window.torro, window.jQuery, window.torroAPIElementMappings || [] ) );
