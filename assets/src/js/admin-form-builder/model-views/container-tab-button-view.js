( function( torroBuilder, torro ) {
	'use strict';

	/**
	 * Container tab button view.
	 *
	 * @class
	 * @augments torro.Builder.BaseModelView
	 */
	torroBuilder.ContainerTabButtonView = torroBuilder.BaseModelView.extend({

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
		className: 'torro-form-canvas-tab add-button',

		/**
		 * Template function.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {function}
		 */
		template: torro.template( 'container-tab-button' ),

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
				'aria-selected': false === this.collection.props.get( 'selection' ) ? 'true' : 'false'
			};
		},

		/**
		 * View events.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @type {object}
		 */
		events: {
			'click': 'addContainer'
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
		 * Adds a new container.
		 *
		 * @since 1.0.0
		 * @access public
		 */
		addContainer: function() {
			this.collection.add({});
		},

		/**
		 * Sets the aria-selected attribute depending on whether this is the currently selected tab.
		 *
		 * @since 1.0.0
		 * @access public
		 *
		 * @param {Backbone.Model} props Collection properties.
		 */
		_toggleSelection: function( props ) {
			if ( false === props.get( 'selection' ) ) {
				this.$el.attr( 'aria-selected', 'true' );
			} else {
				this.$el.attr( 'aria-selected', 'false' );
			}
		}
	});

})( window.torro.Builder, window.torro );
