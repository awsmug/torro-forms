( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container panels view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerPanelsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerPanelView,

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'div',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-container-panels drag-drop-area',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		emptyTemplate: torro.template( 'empty-container-drag-drop' )
	});

})( window.torro.Builder, window.torro );
