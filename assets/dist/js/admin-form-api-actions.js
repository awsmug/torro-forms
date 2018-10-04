/*!
 * Torro Forms Version 1.0.3 (https://torro-forms.com)
 * Licensed under GNU General Public License v2 (or later) (http://www.gnu.org/licenses/gpl-2.0.html)
 */
( function( torro, fieldsAPI, $, apiActionsData ) {
	'use strict';

	var apiActions = apiActionsData.actions;
	var apiAction;
	var i;

	for ( i in apiActions ) {
		apiAction = apiActions[ i ];
		console.log( apiAction );
	}

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActionsData ) );
