( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container panel view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerPanelView = torroBuilder.BaseModelView.extend({

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
		template: torro.template( 'container-panel' ),

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
				'id': 'container-panel-' + this.model.get( 'id' ),
				'aria-labelledby': 'container-tab-' + this.model.get( 'id' ),
				'aria-hidden': this.model.get( 'id' ) === this.collection.props.get( 'selection' ) ? 'false' : 'true',
				'role': 'tabpanel'
			};
		},

		/**
		 * Initializes the view.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		initialize: function() {
			this.listenTo( this.collection.props, 'change:selection', this._toggleSelection );
		},

		/**
		 * Sets the aria-hidden attribute depending on whether this is a panel for the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( this.model.get( 'id' ) === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-hidden', 'false' );
			} else {
				this.$el.attr( 'aria-hidden', 'true' );
			}
		}
	});

})( window.torro.Builder, window.torro );
