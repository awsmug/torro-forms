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
