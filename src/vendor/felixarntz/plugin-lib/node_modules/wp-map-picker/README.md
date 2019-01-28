# WP Map Picker

This jQuery plugin for WordPress can be used to transform an input field into a flexible map field with a location picker.

![Plugin Screenshot](https://raw.githubusercontent.com/felixarntz/wp-map-picker/master/screenshot.png)

## Features

* transforms a simple input field in the WordPress admin into a map picker where you can both enter a value in an input field (with autocompletion) and automatically show it on the map or select a location on the map and automatically put it in the input field
* handles how the value is stored in the input field (you're free to choose between address and coords)
* uses the Google Maps API v3 to display the map
* is based on jQuery UI Widget for a standardized API
* uses WordPress Core technology wherever possible
* customizable with numerous settings which can be defined in the function call or as data attributes

## Installation and Setup

### Install the plugin

The preferred method to install this package is to use NPM.
```
npm install wp-map-picker
```

### Enqueue script and stylesheet

To include the script and stylesheet, enqueue the script and stylesheet like so:
```php
<?php
$gmaps_url = add_query_arg( 'language', str_replace( '_', '-', get_locale() ), 'https://maps.google.com/maps/api/js' );
wp_enqueue_script( 'google-maps', $gmaps_url, array(), false, true );
wp_enqueue_script( 'wp-map-picker', 'PATHTOMAPPICKER/wp-map-picker.min.js', array( 'jquery', 'jquery-ui-widget', 'jquery-ui-autocomplete', 'google-maps' ), '0.7.1', true );
wp_enqueue_style( 'wp-map-picker', 'PATHTOMAPPICKER/wp-map-picker.min.css', array(), '0.7.1' );

```

Make sure to use the proper hook to enqueue the assets, for example in the `admin_enqueue_scripts` hook. Furthermore the dependencies in the above code sample must explicitly be included, otherwise the plugin will not work. Note that the above example will also load the Google Maps API in the current language set in WordPress.

### Initialize the plugin on your fields

To turn your raw and boring input fields into really exciting map picker fields, you simply need to run the main plugin function `wpMapPicker()` on your jQuery elements. For example:

```js
jQuery( '.custom-map-field' ).wpMapPicker();
```

## Plugin Settings

The plugin supports numerous settings so that you can tweak how your fields work. There are two ways to apply settings to a field: Either specify the settings (as an object) when initializing the plugin in Javascript, or apply them as data attributes on the field.

Here you find a list of all available settings:

`store`:
* Determines how the attachment is stored in the input field
* Accepts 'address' or 'coords'
* Default: 'address'

`storeAdditional`:
* Object with additional input element selectors to set their values automatically; these inputs have to exist in the page as they will not be created manually
* Accepts an object where each property is a selector for one input element and the property value is the type of information to store in that element; valid values are 'address', 'coords', 'latitude', 'longitude' or any other field that the Google Geocoder returns in a response
* Default: false

`zoom`:
* Sets the initial zoom level for the map
* Accepts an integer between 1 and 18
* Default: 15

`draggable`:
* Defines whether the map is draggable or not
* Accepts a boolean
* Default: true

`mapType`:
* Specifies the type of the map
* Accepts 'roadmap', 'satellite', 'terrain' or 'hybrid'
* Default: 'roadmap'

`defaultLocation`:
* Specifies the default location if the input field does not contain any
* Accepts an object with three properties (`lat` for the latitude default, `lng` for the longitude default and `zoom` for the initial zoom level used when the default location is applied)
* Default: `{ lat: '0.0', lng: '0.0', zoom: 2 }`

`decimalSeparator`:
* Defines the decimal separator used for coords
* Accepts '.' or ','
* Default: '.'

`change`:
* An optional callback function to run when the location has changed
* Accepts a function
* Default: false

`clear`:
* An optional callback function to run when the location has been cleared (reset to default)
* Accepts a function
* Default: false

## Plugin Methods

There are a number of methods that you can call by using a construct like `jQuery( '{{SELECTOR}}' ).wpMapPicker( '{{NAME_OF_FUNCTION}}' {{,FUNCTION_PARAMS}} )`.

`clear`:
* Clears the location selection (resets it to the default)

`refresh`:
* Refreshes the Google map; this needs to be run whenever the map becomes visible inside dynamic content, for example an accordion

## Contribute

I'm always grateful for contributions, whether it is about enhancements or bugfixes, especially since the plugin is at an early stage. If you encounter bugs, compatibility issues or totally missing functionality that must be in this plugin, I would appreciate if you [created an issue](https://github.com/felixarntz/wp-map-picker/issues). Or even better, if you can, do it yourself and [open a pull-request](https://github.com/felixarntz/wp-map-picker/pulls).
