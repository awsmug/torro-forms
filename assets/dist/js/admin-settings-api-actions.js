/*!
 * Torro Forms Version 1.0.0 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( fieldsAPI, $, apiActions ) {
	'use strict';

	var apiAction, i;

	for ( i in apiActions ) {
		apiAction = apiActions[ i ];
		console.log( apiAction );
	}

}( window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActions || [] ) );
