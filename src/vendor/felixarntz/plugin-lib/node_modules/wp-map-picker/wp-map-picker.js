/*
 * WP Map Picker -  version 0.7.1
 *
 * Felix Arntz <felix-arntz@leaves-and-love.net>
 */

( function( $, google ) {

	if ( typeof google === 'undefined' || typeof google.maps === 'undefined' ) {
		// if google.maps is not loaded, scaffold the jQuery plugin function and abort
		$.fn.wpMapPicker = function() {
			return this;
		};

		console.error( 'Google Maps API not found' );
		return;
	}

	var _wrap = '<div class="wp-mappicker-container" />';
	var _canvas_wrap = '<div class="wp-mappicker-map-canvas-wrap" />';
	var _canvas = '<div class="wp-mappicker-map-canvas" />';

	var MapPicker = {
		options: {
			store: 'address',
			storeAdditional: false,
			zoom: 15,
			draggable: true,
			mapType: 'roadmap', // roadmap, satellite, terrain or hybrid
			defaultLocation: {
				lat: '0.0',
				lng: '0.0',
				zoom: 2
			},
			decimalSeparator: '.',
			change: false,
			clear: false
		},

		_create: function() {
			var self = this;

			$.extend( self.options, self.element.data() );

			self.defaultLatLng = self._parseLatLng( self.options.defaultLocation );
			self.is_default = true;

			self.element.wrap( _wrap );
			self.canvas_wrap = $( _canvas_wrap ).insertAfter( self.element );
			self.canvas = $( _canvas ).appendTo( self.canvas_wrap );

			self.geocoder = new google.maps.Geocoder();

			self.map = new google.maps.Map( self.canvas[0], {
				center: self.defaultLatLng,
				zoom: self.options.defaultLocation.zoom,
				draggable: self.options.draggable,
				tilt: 0,
				streetViewControl: 0,
				mapTypeId: google.maps.MapTypeId[ self.options.mapType.toUpperCase() ]
			});
			self.marker = new google.maps.Marker({
				position: self.defaultLatLng,
				map: self.map,
				draggable: true
			});

			var value = self.element.val();
			if ( 'coords' === self.options.store ) {
				self._geocodeLatLng( value, self._initMap );
			} else {
				self._geocodeAddress( value, self._initMap );
			}

			if ( 'coords' === self.options.store ) {
				self.element.on( 'change', function() {
					self._geocodeLatLng( self.element.val(), self._updateMap );
				});
			} else {
				self.element.autocomplete({
					source: function( request, response ) {
						self._geocodeAddress( request.term, function( results ) {
							if ( results ) {
								response([
									{
										label: results.formatted_address,
										value: results.formatted_address,
										latlng: results.geometry.location,
										geocoded: results
									}
								]);
							} else {
								response([]);
							}
						});
					},
					select: function( e, ui ) {
						self._updateMap( ui.item.geocoded );
					}
				});
			}

			google.maps.event.addListener( self.map, 'click', function( e ) {
				self._geocodeLatLng( e.latLng, self._updateField );
			});

			google.maps.event.addListener( self.marker, 'dragend', function( e ) {
				self._geocodeLatLng( e.latLng, self._updateField );
			});
		},

		_initMap: function( geocoded ) {
			if ( geocoded ) {
				this.latlng = geocoded.geometry.location;

				this.marker.setPosition( this.latlng );
				this.map.setCenter( this.latlng );

				if ( this.is_default ) {
					this.is_default = false;
					this.map.setZoom( this.options.zoom );
				}
			} else {
				this.latlng = null;

				this.marker.setPosition( this.defaultLatLng );
				this.map.setCenter( this.defaultLatLng );

				if ( ! this.is_default ) {
					this.is_default = true;
					this.map.setZoom( this.options.defaultLocation.zoom );
				}
			}
		},

		_updateMap: function( geocoded ) {
			this._initMap( geocoded );

			if ( this.options.storeAdditional ) {
				this._updateAdditionalFields( geocoded );
			}

			if ( 'function' === typeof this.options.change ) {
				this.options.change.call( this );
			}

			$( document ).trigger( 'wpMapPicker.updateMap', [ geocoded, this ] );
		},

		_updateField: function( geocoded ) {
			if ( geocoded ) {
				this.latlng = geocoded.geometry.location;

				if ( 'coords' === this.options.store ) {
					this.element.val( this._formatLatLng( geocoded.geometry.location ) );
				} else {
					this.element.val( geocoded.formatted_address );
				}
			} else {
				this.latlng = null;

				this.element.val( null );
			}

			if ( this.options.storeAdditional ) {
				this._updateAdditionalFields( geocoded );
			}

			if ( 'function' === typeof this.options.change ) {
				this.options.change.call( this );
			}

			$( document ).trigger( 'wpMapPicker.updateField', [ geocoded, this ] );
		},

		_updateAdditionalFields: function( geocoded ) {
			var keys = Object.keys( this.options.storeAdditional );
			var store, selector, value, oldValue;

			for ( var i in keys ) {
				selector = keys[ i ];
				store = this.options.storeAdditional[ selector ];
				oldValue = $( selector ).val();
				value = null;

				if ( geocoded ) {
					switch ( store ) {
						case 'coords':
							value = this._formatLatLng( geocoded.geometry.location );
							break;
						case 'address':
							value = geocoded.formatted_address;
							break;
						case 'latitude':
							value = this._formatLatOrLng( geocoded.geometry.location.lat() );
							break;
						case 'longitude':
							value = this._formatLatOrLng( geocoded.geometry.location.lng() );
							break;
						default:
							if ( 'undefined' !== typeof geocoded[ store ] ) {
								value = geocoded[ store ];
							}
					}
				}

				if ( value !== oldValue ) {
					$( selector ).val( value ).trigger( 'change' );
				}
			}
		},

		_geocodeLatLng: function( latlng, callback ) {
			if ( ! latlng ) {
				callback.apply( this, [ latlng ] );
				return;
			}

			this._geocodeObject({
				location: this._parseLatLng( latlng )
			}, callback );
		},

		_geocodeAddress: function( address, callback ) {
			if ( ! address ) {
				callback.apply( this, [ address ] );
				return;
			}

			this._geocodeObject({
				address: address
			}, callback );
		},

		_geocodeObject: function( obj, callback ) {
			var self = this;

			self.geocoder.geocode( obj, function( results ) {
				var value;
				if (  null !== results && 'undefined' !== typeof results[0] ) {
					value = results[0];
				}

				callback.apply( self, [ value ] );
			});
		},

		_parseLatLng: function( val ) {
			if ( 'object' === typeof val && 'function' !== typeof val.lat ) {
				val = '' + val.lat + '|' + val.lng;
			} else if ( 'object' === typeof val ) {
				return val;
			}

			if ( 'string' !== typeof val ) {
				return false;
			}

			val = val.split( '|' );
			if ( 2 !== val.length ) {
				return false;
			}

			for ( var i = 0; i < 2; i++ ) {
				val[ i ] = this._parseLatOrLng( val[ i ] );
			}

			return new google.maps.LatLng( val[0], val[1] );
		},

		_parseLatOrLng: function( val ) {
			return parseFloat( val.replace( this.options.decimalSeparator, '.' ) );
		},

		_formatLatLng: function( val ) {
			if ( 'string' === typeof val ) {
				return val;
			}

			return this._formatLatOrLng( val.lat() ) + '|' + this._formatLatOrLng( val.lng() );
		},

		_formatLatOrLng: function( val ) {
			return ( '' + val ).replace( '.', this.options.decimalSeparator );
		},

		clear: function() {
			this._initMap();
			this._updateField();

			if ( 'function' === typeof this.options.clear ) {
				this.options.clear.call( this );
			}
		},

		refresh: function() {
			google.maps.event.trigger( this.map, 'resize' );
			if ( this.latlng ) {
				this.map.setCenter( this.latlng );
			} else {
				this.map.setCenter( this.defaultLatLng );
			}
		}
	};

	$.widget( 'wp.wpMapPicker', MapPicker );
}( jQuery, window.google ) );
