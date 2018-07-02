( function( torro, fieldsAPI, $, apiActions ) {
	'use strict';

	var apiAction, i;

	for ( i in apiActions ) {
		apiAction = apiActions[ i ];
		console.log( apiAction );
	}

}( window.torro, window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActions || [] ) );
