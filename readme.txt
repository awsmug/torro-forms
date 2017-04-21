=== Torro Forms ===

Plugin Name:       Torro Forms
Plugin URI:        http://torro-forms.com
Author:            Awesome UG
Author URI:        http://www.awesome.ug
Contributors:      mahype, flixos90, awesome-ug
Requires at least: 4.4
Tested up to:      4.7
Stable tag:        1.0.0-beta.8
Version:           1.0.0-beta.8
License:           GNU General Public License v3
License URI:       http://www.gnu.org/licenses/gpl-3.0.html
Tags:              forms, form builder, surveys, polls, votes, charts, api

Torro Forms is an extendable WordPress form builder with Drag & Drop functionality, chart evaluation and more - with WordPress look and feel.

== Description ==

Torro Forms is a Drag & Drop form builder plugin that is easy to use for administrators, yet flexible to extend for developers. The plugin was made with both user groups in mind to ensure that you can do exactly what you want without getting stuck in complicated setups. In addition, our plugin looks and behaves in the same way that the rest of WordPress does. If you're tired of seeing bloated, "all-fancy" user interfaces that distract you from what you actually want to achieve - be relieved, we are too.

[youtube https://www.youtube.com/watch?v=k-F_6RpV21k]

Torro Forms can serve several purposes. Its functionality goes beyond simple contact forms (although you could of course technically create one if you wanted to). Whether you're interested in a survey solution or whether you need internal forms that you can restrict to a specific group of users - Torro Forms is the way to go. And if you don't find what you've been looking for, be aware that our plugin is extendable via several APIs - we encourage you to do it yourself instead of locking you with what we already provide.

Torro Forms was made with a specific attention to polls and surveys. Form submissions are permanently stored in the database so that they can be browsed, exported and evaluated through charts.

= Key Features =

* **Drag & Drop elements** - Drag elements into your working area and edit them. Look and feel of the form builder are the similar to what you would expect from WordPress.
* **Actions** - Use our built-in actions like page redirections to show your message or send out emails to notify users, yourself or others.
* **Charts** - Every element that can be evaluated, for example a multiple choice field, can be displayed as bar charts and will help you analyze your submissions.
* **Excel & CSV Exports** - Export the results of form submissions into as Excel or CSV file. The export can be created from the form overview or in the form builder.
* **Create element types** - Enhance your forms further by creating custom element types. Our easy-to-use Element Types API makes it possible.
* **Create actions** - Create additional kinds of actions which will be executed whenever the form is submitted successfully by using our Actions API.
* **Extend more...** - Like element types and actions, you can extend Torro Forms in many other ways too by extending specific PHP classes. It's just like creating widgets in WordPress.
* **Code completion** - Our flexible API was made with developers in mind: The `torro()` function acts as a root for easy chaining and autocompletion.

= Links =

* [Website](http://torro-forms.com)
* [Twitter](https://twitter.com/torro_forms)
* [GitHub](https://github.com/awsmug/torro-forms)
* [Translations](https://translate.wordpress.org/projects/wp-plugins/torro-forms)

== Installation ==

1. Upload the entire `torro-forms` folder to the `/wp-content/plugins/` directory or download it through the WordPress backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I use the plugin? =

You can find instructions on what you can do with Torro Forms and how to use it in our [User Guide](http://torro-forms.com/user-guide/).

= How can I, as a developer, extend the plugin? =

Torro Forms supports the concept of extensions and provides flexible APIs for several areas of it. A good point to start are our [API resources](http://torro-forms.com/api/). The plugin itself can also be found [on GitHub](https://github.com/awsmug/torro-forms) if you wanna have a look at the code in detail.

= Where should I submit my support request? =

We preferably take support requests as [issues on Github](https://github.com/awsmug/torro-forms/issues), so I would appreciate if you created an issue for your request there. However, if you don't have an account there and do not want to sign up, you can of course use the [wordpress.org support forums](https://wordpress.org/support/plugin/torro-forms) as well.

= How can I contribute to the plugin? =

If you're a developer and you have some ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request in the [Github repository for the plugin](https://github.com/awsmug/torro-forms).

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/torro-forms) to get started.

== Screenshots ==

1. an overview of the form builder
2. the redirection interface
3. the email notifications interface
4. a list of form submission results
5. an example of some form element charts
6. the plugin settings screen

== Changelog ==

= 1.0.0-beta.9 =
* Major refactor: breaks backward-compatibility development-wise, plugin now requires at least PHP 5.6 and WordPress 4.7
* Added: REST API endpoints for managing forms, their content and submissions
* Added: WP-CLI commands for managing forms, their content and submissions
* Added: Cache layer for all database requests
* Enhanced: Element types can now contain multiple fields
* Enhanced: Form stats now scale by storing aggregate results persistently
* Enhanced: Form submissions now have a status
* Enhanced: Indexes have been added to database tables to speed up queries
* Fixed: Installation routine works now properly on multisite setups of any size
* Tweaked: namespaces are used throughout the plugin code
* Tweaked: form results are now relabelled as form submissions
* Tweaked: element answers are now relabelled as element choices
* Tweaked: uses external `felixarntz/plugin-lib` library for standard plugin functionality

= 1.0.0-beta.8 =
* Enhanced: Added password option to textfield element
* Enhanced: Added 'torro_form_action_url' filter
* Enhanced: Added 'torro_element_type_validate_input' filter for additional validations
* Enhanced: Enhanced code
* Enhanced: Added honeypot spam filter
* Enhanced: Added timetrap spam filter
* Enhanced: Added linkcount spam filter
* Enhanced: Added filter 'torro_form_show_saving_error' on whats happening after data could not be saved
* Fixed: Fixed incompatibility on ACF Calendar CSS on Torro Forms Formbuilder
* Fixed: Element PHP Notices on not existing variable $element_id
* Fixed: Element settings fields have now unique element ids
* Fixed: Page 1 couldn't be deleted
* Fixed: Warning if there is no element with an input. 

= 1.0.0-beta.7 =
* Enhanced: Added a new filter for the element data sent to the template
* Enhanced: Introduce element tab slugs for more meaningful hook usage
* Enhanced: Added CSS settings field to elements
* Enhanced: Added placeholder field to text and textarea element
* Enhanced: Removed separator element (can be done by content element)
* Enhanced: Added Referer URL Templatetag
* Enhanced: Added Reply-To field in email notifications
* Enhanced: Changed appearance of multiple answers in Email notifications
* Enhanced: Scripts will only be loaded if there is a torro form
* Enhanced: Added column for form shortcode into form overview
* Enhanced: Allowing setting form element values by $_GET param
* Fixed: Forms are now created and copied as intended (with post status `publish`) by default
* Fixed: Added line breaks between admin settings inputs and description
* Fixed: Label semantic in settings of elements was wrong
* Fixed: JS Error on multiple same answes or if there have been only one answer
* Fixed: Shortcodes in redirection messages working now
* Fixed: Shortcodes didn't worked in pages and posts
* Fixed: Results have not been sent if multiple pages are used
* Fixed: Discription of elements don't have been shown
* Fixed: PHP Error message on empty label key
* Fixed: Recaptcha has not performed
* Fixed: Password-protected form content is not displayed unconditionally
* Tweaked: Removed the method `Torro_Forms_Manager::get_content()` which made no sense in that class
* Tweaked: Added filter for form names and ids
* Tweaked: Changed form element id names
* Tweaked: Excluded content field from element list in email notifications

= 1.0.0-beta.6 =

* Enhanced: Added unit test framework
* Enhanced: Added fields for changing texts on access control
* Enhanced: Added aria-invalid for elements with error
* Enhanced: Using proper escaping for textareas
* Fixed: Chart errors on removing and adding elements
* Fixed: Not working shortcodes in content elements and on result text message
* Fixed: Email notifications component had to be loaded before redirections
* Fixed: Not required fields do not perform further checks on empty fields anymore
* Fixed: Removed "After Submit" section in charts because it's not needed anymore
* Fixed: Added wpautop in text content elements

= 1.0.0-beta.5 =

* Enhanced: Now possible to have multiple forms on one page
* Enhanced: form detection now happens on `wp` hook to prevent an extra query
* Fixed: Form shortcodes work properly now and do not override content
* Fixed: reCAPTCHA now works in form shortcodes as well
* Fixed: if multiple choice is required, make sure at least one input is selected
* Fixed: description is now shown again after being broken in 1.0.0-beta.4
* Fixed: element results warning in form builder backend
* Tweaked: removed unnecessary Options metabox in backend

= 1.0.0-beta.4 =

* Added a template hierarchy for all frontend visuals
* Added a new filter `torro_template_locations`
* Added new filters `torro_form_classes`, `torro_element_classes`, `torro_input_classes`
* Elements and their types are now separate models
* Reworked Form Settings CSS
* Adjusted default values for min/max limits on element types to be empty
* Fixed problems with functions not existing in PHP 5.2
* Fixed problems with shortcodes on start pages
* Fixed problems with shortcodes, embedded in forms which have been included by shortcodes
* Fixed bug with date field on export

= 1.0.0-beta.3 =

* Added option to switch page title on/off
* Showing limitations (min/max) of elements now in frontend
* Moved inline Javascript into Charts JS File
* Remove bundled translations (Getting translations now from WordPress.org)
* Fixed problems on embedding forms for error handling and multiple choice or one choice fields

= 1.0.0-beta.2 =

* Added general settings tab with possibility to change button texts
* Added filters 'torro_form_container_title', 'torro_form_button_previous_step_text', 'torro_form_button_next_step_text' and 'torro_form_button_send_text'
* Fixed not shown container title
* Fixed not deleted results

= 1.0.0-beta.1 =

* First official beta release
