( function( torroBuilder ) {
	'use strict';

	/**
	 * Container tabs view.
	 *
	 * @class
	 * @augments torro.Builder.BaseCollectionView
	 */
	torroBuilder.ContainerTabsView = torroBuilder.BaseCollectionView.extend({

		/**
		 * Model view class for the collection view.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {function}
		 */
		modelView: torroBuilder.ContainerTabView,

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
		className: 'torro-form-container-tabs',

		/**
		 * Element attributes.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @returns {object} Default attributes.
		 */
		attributes: function() {
			return {
				'role': 'tablist'
			};
		}
	});

})( window.torro.Builder );
