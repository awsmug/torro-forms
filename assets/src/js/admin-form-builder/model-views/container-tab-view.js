( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container tab view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerTabView = torroBuilder.BaseModelView.extend({

		/**
		 * Element tag name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		tagName: 'button',

		/**
		 * Element class name.
		 *
		 * @since 1.0.0
		 * @access public
		 * @type {string}
		 */
		className: 'torro-form-canvas-tab',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-tab' ),

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
				'type': 'button',
				'id': 'container-tab-' + this.model.get( 'id' ),
				'aria-controls': 'container-panel-' + this.model.get( 'id' ) + ' container-footer-panel-' + this.model.get( 'id' ),
				'aria-selected': false,
				'role': 'tab'
			};
		}
	});

})( window.torro.Builder, window.torro );
