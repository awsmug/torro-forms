( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container footer panel view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerFooterPanelView = torroBuilder.BaseModelView.extend({

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
		className: 'torro-form-canvas-panel',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-footer-panel' ),

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
				'id': 'container-footer-panel-' + this.model.get( 'id' ),
				'aria-labelledby': 'container-tab-' + this.model.get( 'id' ),
				'aria-hidden': true,
				'role': 'tabpanel'
			};
		}
	});

})( window.torro.Builder, window.torro );
