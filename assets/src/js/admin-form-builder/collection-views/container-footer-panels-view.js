( function( torroBuilder ) {
	'use strict';

	/**
	 * Container footer panels view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerFooterPanelsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerFooterPanelView,

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
		className: 'torro-form-container-footer-panels'
	});

})( window.torro.Builder );
