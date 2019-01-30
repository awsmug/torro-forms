# WP Media Picker

This jQuery plugin for WordPress can be used to transform an input field into a flexible and compatible media field with attachment selection and preview.

![Plugin Screenshot](https://raw.githubusercontent.com/felixarntz/wp-media-picker/master/screenshot.png)

## Features

* transforms a simple input field in the WordPress admin into a media picker with buttons to open the WordPress media modal
* handles the custom media modal behavior as well as how the value is stored in the input field (you're free to choose between ID and URL)
* renders a preview of the attachment if it is an image, video or audio file
* is based on jQuery UI Widget for a standardized API
* uses WordPress Core technology wherever possible
* fully compatible with WordPress Core media modal, so it can be used on post editing screens too
* customizable with numerous settings which can be defined in the function call or as data attributes
* since it only uses WordPress Core strings for the labels, it has probably been translated into your language already

## Installation and Setup

### Install the plugin

The preferred method to install this package is to use Bower.
```
bower install wp-media-picker
```

### Enqueue script and stylesheet

To include the script and stylesheet, enqueue the script and stylesheet like so:
```php
<?php
wp_enqueue_media();
wp_enqueue_script( 'wp-media-picker', 'PATHTOMEDIAPICKER/wp-media-picker.min.js', array( 'jQuery', 'jquery-ui-widget', 'media-editor', 'underscore' ), '0.7.0', true );
wp_enqueue_style( 'wp-media-picker', 'PATHTOMEDIAPICKER/wp-media-picker.min.css', array(), '0.7.0' );

```

Make sure to use the proper hook to enqueue the assets, for example in the `admin_enqueue_scripts` hook. Furthermore the dependencies in the above code sample must explicitly be included, otherwise the plugin will not work.

### Initialize the plugin on your fields

To turn your raw and boring input fields into really exciting media picker fields, you simply need to run the main plugin function `wpMediaPicker()` on your jQuery elements. For example:

```js
jQuery( '.custom-media-field' ).wpMediaPicker();
```

### Implement additional AJAX function

If you want to store media URLs in the fields (by default the plugin stores media IDs), you need to implement an additional AJAX function somewhere in your code (for example in your theme's `functions.php`) for the plugin to work properly - don't worry, it's a pretty simple function. You could just copy the following code snippet:

```php
<?php
function mytheme_ajax_get_attachment_by_url() {
  if ( ! isset( $_REQUEST['url'] ) ) {
    wp_send_json_error();
  }

  $id = attachment_url_to_postid( $_REQUEST['url'] );
  if ( ! $id ) {
    wp_send_json_error();
  }

  $_REQUEST['id'] = $id;

  wp_ajax_get_attachment();
  die();
}
add_action( 'wp_ajax_get-attachment-by-url', 'mytheme_ajax_get_attachment_by_url', 15 );
```

## Plugin Settings

The plugin supports numerous settings so that you can tweak how your fields work. There are two ways to apply settings to a field: Either specify the settings (as an object) when initializing the plugin in Javascript, or apply them as data attributes on the field.

Here you find a list of all available settings:

`store`:
* Determines how the attachment is stored in the input field.
* Accepts 'id' or 'url'
* Default: 'id'

`query`:
* Alters the attachments query in the media library (for example to only show images, use `{ type: 'image' }`).
* Accepts an object in JSON format
* Default: empty object

`multiple`:
* Whether to allow multiple attachments to be selected at once (requires custom handling, see Hooks section below).
* Accepts a boolean
* Default: false

`filterable`:
* Whether the library is filterable, and if so what filters should be shown.
* Accepts 'all', 'uploaded' or 'unattached'
* Default: 'all'

`searchable`:
* Whether the library is searchable.
* Accepts a boolean
* Default: true

`editable`:
* Whether the library content is editable.
* Accepts a boolean
* Default: false

`displaySettings`:
* Whether to show the attachment display settings.
* Accepts a boolean
* Default: false

`displayUserSettings`:
* Whether to update the user settings when editing attachment display settings.
* Accepts a boolean
* Default: false

`change`:
* An optional callback function to run when the attachment has changed.
* Accepts a function
* Default: false

`clear`:
* An optional callback function to run when the attachment selection has been cleared.
* Accepts a function
* Default: false

`label_add`:
* Sets the text for the add button on the field.
* Accepts a string
* Default: 'Add Media'

`label_replace`:
* Sets the text for the replace button on the field.
* Accepts a string
* Default: 'Replace'

`label_remove`:
* Sets the text for the remove button on the field.
* Accepts a string
* Default: 'Remove'

`label_modal`:
* Sets the title text for the media modal.
* Accepts a string
* Default: 'Add Media'

`label_button`:
* Sets the button text for the media modal.
* Accepts a string
* Default: 'Add Media'

## Plugin Methods

There are a number of methods that you can call by using a construct like `jQuery( '{{SELECTOR}}' ).wpMediaPicker( '{{NAME_OF_FUNCTION}}' )`.

`open()`:
* Opens the media modal.

`close()`:
* Closes the media modal (without making a selection).

`attachment()`:
* Dynamic getter/setter method for the attachment object.
* Accepts an attachment object (only for the setter functionality)

`value()`:
* Dynamic getter/setter method for the field value.
* Accepts an integer (for `store: 'id'`) or a string (for `store: 'url'`) (only for the setter functionality)

`frame()`:
* Getter method for the media frame instance.

## Hooks

Besides a regular `change` event fired on the input when its value has changed, there are two custom events that are fired on the document:

`wpMediaPicker.updateField`:
* Fired when the attachment is updated.
* Passes two values, the attachment object (or null if the attachment was removed), and the media picker instance

`wpMediaPicker.insert`:
* Fired when attachments have been selected and are inserted. This allows for custom handling of multiple attachments being selected at the same time. Out-of-the-box the media picker will still always use the first image available for the current field.
* Passes two values, the array of attachment objects seleted, and the media picker instance

## Contribute

I'm always grateful for contributions, whether it is about enhancements or bugfixes, especially since the plugin is at an early stage. If you encounter bugs, compatibility issues or totally missing functionality that must be in this plugin, I would appreciate if you [created an issue](https://github.com/felixarntz/wp-media-picker/issues). Or even better, if you can, do it yourself and [open a pull-request](https://github.com/felixarntz/wp-media-picker/pulls).
