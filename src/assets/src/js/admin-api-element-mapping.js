( function( torro, $, elementMappings ) {
	'use strict';

	var apiAction, i;

	for ( i in elementMappings ) {
		apiAction = elementMappings[ i ];
		console.log( apiAction );
	}

}( window.torro, window.jQuery, window.torroAPIElementMappings || [] ) );
