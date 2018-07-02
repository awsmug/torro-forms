( function( fieldsAPI, $, apiActions ) {
	'use strict';

	var apiAction, i;

	for ( i in apiActions ) {
		apiAction = apiActions[ i ];
		console.log( apiAction );
	}

}( window.pluginLibFieldsAPI, window.jQuery, window.torroAPIActions || [] ) );
