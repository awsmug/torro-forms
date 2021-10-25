=== Torro Forms ===

Plugin Name:       Torro Forms
Plugin URI:        https://torro-forms.com
Author:            Awesome UG
Author URI:        https://www.awesome.ug
Contributors:      mahype, awesome-ug
Requires at least: 4.8
Tested up to:      5.8.1
Requires PHP:      5.6
Stable tag:        1.0.16
Version:           1.0.16
License:           GNU General Public License v2 (or later)
License URI:       http://www.gnu.org/licenses/gpl-2.0.html
Tags:              forms, form builder, surveys, polls, votes, charts, api

Torro Forms is an extendable WordPress form builder with Drag & Drop functionality, chart evaluation and more - with WordPress look and feel.

== Description ==

Torro Forms is a Drag & Drop form builder plugin that is easy to use for administrators, yet flexible to extend for developers. The plugin was made with both user groups in mind to ensure that you can do exactly what you want without getting stuck in complicated setups. In addition, our plugin looks and behaves in the same way that the rest of WordPress does. If you're tired of seeing bloated, "all-fancy" user interfaces that distract you from what you actually want to achieve - be relieved, we are too.

[youtube https://www.youtube.com/watch?v=k-F_6RpV21k]

Torro Forms can serve several purposes. Its functionality goes beyond simple contact forms (although you could of course technically create one if you wanted to). Whether you're interested in a survey solution or whether you need internal forms that you can restrict to a specific group of users - Torro Forms is the way to go. And if you don't find what you've been looking for, be aware that our plugin is extendable via several APIs - we encourage you to do it yourself instead of locking you with what we already provide.

Torro Forms was made with a specific attention to polls and surveys. Form submissions are permanently stored in the database so that they can be browsed, exported and evaluated.

= Key Features =

* **Multi-Page** Forms,
* **Shortcodes** to embed forms and live results,
* **Excel, CSV** export,
* **Actions** like unlimited email-notifications and redirections,
* **Protectors** like Google reCaptcha and honeypot and link counting,
* **Access Controls** for visitors, users or by timerange or number of submissions,
* **Evaluators** for displaying results with bar, pie or donut charts,
* **WP-CLI** commands,
* **REST-API** endpoints,
* **Templating** of elements,
* **PHP API** for developers,
* and much more.

= Links =

* [Website](https://torro-forms.com)
* [Developer Reference](http://developer.torro-forms.com)
* [Twitter](https://twitter.com/torro_forms)
* [GitHub](https://github.com/awsmug/torro-forms)
* [Translations](https://translate.wordpress.org/projects/wp-plugins/torro-forms)

== Installation ==

1. Upload the entire `torro-forms` folder to the `/wp-content/plugins/` directory or download it through the WordPress backend.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= How do I use the plugin? =

You can find instructions on what you can do with Torro Forms and how to use it in our [User Guide](https://torro-forms.com/user-guide/).

= How can I, as a developer, extend the plugin? =

Torro Forms supports the concept of extensions and provides flexible APIs for several areas of it. A good point to start are our [tutorials](http://developer.torro-forms.com/tutorials/), and we also provide a full [code reference](http://developer.torro-forms.com/reference/). The plugin itself can also be found [on GitHub](https://github.com/awsmug/torro-forms) if you wanna have a look at the code itself.

= Where should I submit my support request? =

We preferably take support requests as [issues on Github](https://github.com/awsmug/torro-forms/issues), so I would appreciate if you created an issue for your request there. However, if you don't have an account there and do not want to sign up, you can of course use the [wordpress.org support forums](https://wordpress.org/support/plugin/torro-forms) as well.

= How can I contribute to the plugin? =

If you're a developer and you have some ideas to improve the plugin or to solve a bug, feel free to raise an issue or submit a pull request in the [Github repository for the plugin](https://github.com/awsmug/torro-forms).

You can also contribute to the plugin by translating it. Simply visit [translate.wordpress.org](https://translate.wordpress.org/projects/wp-plugins/torro-forms) to get started.

== Screenshots ==

1. Overview of the form builder
2. Modal to add a new element to a form
3. Submissions list overview
4. Plugin settings screen

== Changelog ==

= 1.0.16 =
* Fixed: Logical error in 'can_access' function. Thanks to reedsutliff!

= 1.0.15 =
* Fixed: Deployment issue.

= 1.0.14 =
* Enhanced: Replaced XLS library, because of PHP errors.

= 1.0.13 =
* Fixed: Readded vendor and node_module directories.

= 1.0.12 =
* Enhaned: Get deployment running.

= 1.0.11 =
* Enhanced: Tested up to 5.8.1.
* Enhanced: Cleaned up repo & updated composer dependency.
* Security: Updated phpspreadsheet lib.

= 1.0.10 =
* Enhaned: Tested up to 5.4.0.

= 1.0.9 =
* Fixed: Updated PHPspreadsheet to 1.11.0.

= 1.0.8 =
* Enhanced: Added filter 'torro_element_export_columns'.
* Enhanced: Moved CSS classes of element settings from input tag to wrapper class.
* Enhanced: Using direct link to file on exporting submissions instead of post link.
* Enhanced: Added pot translation file.

= 1.0.7 =
* Fix: Fixed wrong function name for filter_input fix which caused fatal error

= 1.0.6 =
* Enhanced: Changed standard export type to one column for choices for better readability in mails
* Enhanced: Changed order of reCaptcha to the end of protectors list, because it's 3rd pary
* Enhanced: Added hint if reCaptcha is not configured properly
* Enhanced: Moved description before HTML inputs
* Enhanced: Added role="alert" to errors on forms for better accessibility
* Fixed: Added wrapper for buggy filter_input PHP function
* Fixed: Plugin-Lib throws notice for datetime fields
* Fixed: Compact PHP function in PHP 7.3 throws error
* Fixed: Using query to get form url instead of using REQUEST_URI
* Tweaked: Switched to plugin-lib of felixarntz repo

= 1.0.5 =
* Enhanced: Email notifications element "all_elements" looking now better
* Enhanced: Added filter torro_export_value
* Enhanced: Added actionhooks torro_container_partials_before, toro_container_partials_after
* Enhanced: Added filters torro_element_type_settings_sections, torro_element_type_settings_fields an element_values_validated
* Fixed: Added missing object in filters torro_element_input_classes, torro_element_label_classes, torro_element_wrap_classes, torro_element_description_classes and torro_element_errors_classes
* Fixed: Inserted WordPress password protection query
* Fixed: Bug on remove_error from submission
* Fixed: Not shown phone text field in Admin

= 1.0.4 =
* Enhanced: One Choice elements can be exported in one column
* Enhanced: Added filter for date format on exporting 'torro_submission_export_time_format'
* Fixed: Required fields text can now be edited
* Fixed: Changed wrong option name 'general' to 'general_settings'
* Fixed: Only reset form submission errors on submission, not during a regular… 
* Fixed: Correctly start PHP session for forms rendered via shortcodes, and in… 
* Fixed: Submission Deletion: Cron never runs

= 1.0.3 =

* Fixed: Submissions list table filtering by status or search works correctly again.
* Fixed: Containers, elements, element choices and element settings are deleted correctly in form builder.

= 1.0.2 =

* Fixed: Undefined index in element type

= 1.0.1 =

* Enhanced: Handle form validation and save errors properly and display according feedback to the user
* Enhanced: Ensure metadata is not updated if it has not changed
* Tweaked: Adjust PHPCS arguments on Travis-CI
* Tweaked: Updated to Plugin lib 1.0.3
* Tweaked: Updated WP Coding Standards to use latest stable version
* Tweaked: Fix PHPCS failure
* Tweaked: Coding standards violations
* Fixed: Don't throw PHP fatal errors on failures in actions
* Fixed: Incorrect user form submission counts
* Fixed: Ensure array values are inserted as individual form submission values
* Fixed: Show settings screen subtab labels as necessary to have context for the available settings sections 
* Fixed: Problems with 0 values in elements with choices

= 1.0.0 =

* Initial release

= 1.0.0-rc.1 =

* Fixed: Element headers in the form builder may no longer contain HTML tags, and in addition now have their maximum length limited.
* Fixed: You no longer get a PHP notice triggered when visiting the submissions list page with active filters.

= 1.0.0-beta.11 =

* Enhanced: New `set_props()` and `get_props()` methods available on all model classes
* Enhanced: When editing a submission, the currently active tab is now maintained across pageviews
* Enhanced: Performance of template tag handler processing content has been significantly improved
* Tweaked: An unnecessary database query in the frontend has been removed
* Tweaked: The [`PhpSpreadsheet`](https://github.com/PHPOffice/PhpSpreadsheet) project is now used instead of the deprecated [`PHPExcel`](https://github.com/PHPOffice/PHPExcel) project
* Fixed: Forms now store data of all their pages correctly
* Fixed: Email notifications now display correct output for all element types
* Fixed: Email notifications are now sent using fully valid HTML
* Fixed: Form uploads are now correctly tagged with the specified taxonomy term
* Fixed: It is now possible to freely navigate back to a previous form page if values for required fields have not been provided yet
* Fixed: Ensure modifying a submission now correctly clears the respective caches
* Fixed: Ensure deleting a submission does not lead to a non-existing admin screen
* Fixed: Increase the query limit so that all element choices are displayed in the backend
* Fixed: Empty values are no longer validated against the available choices
* Fixed: Empty values are now allowed when they are actually available as a choice
* Fixed: Ensure user columns are correctly offset in form submission exports
* Fixed: Cron task to delete submissions is now correctly clear when deactivating the plugin
* Fixed: Ensure old element choices data is displayed in both frontend and backend

= 1.0.0-beta.10 =

* Enhanced: Field-specific errors in frontend are now highlighted more obviously
* Fixed: Email notification template tag buttons now work correctly
* Fixed: Dynamic element template tags for email notifications are now applied correctly
* Fixed: Template tags in email notifications are now correctly replaced
* Fixed: Non-required media elements no longer throw an error in the frontend when no file is uploaded
* Fixed: Pages in the form builder can now be deleted properly
* Fixed: Datetime fields now show the currently selected date on opening as expected
* Fixed: Datetime fields no longer have issues with certain locales
* Fixed: Fatal error no longer occurs that could happen when accessing the settings page under certain conditions
* Fixed: Plugin can now be properly deleted through the admin interface

= 1.0.0-beta.9 =

This pre-release is a major rewrite that fully breaks backward-compatibility development-wise. Only user-generated content remains intact. The plugin now requires at least PHP 5.6 and WordPress 4.8. If you have already created extensions for Torro Forms, you need to adjust them in order for them to work with the refactored version. Rest assured that none of this will happen again, but we are still in Beta and the previous versions had some severe architectural issues. Please [read more about it in our blog post](https://torro-forms.com/new-revamped-form-builder-experience/) if you're interested!

* Added: REST API endpoints for managing forms, their content and submissions
* Added: WP-CLI commands for managing forms, their content and submissions
* Added: cache layer for all database requests
* Added: form submissions now live under their own admin submenu
* Added: form submissions can now be edited and created through the backend
* Added: form edit page now uses Backbone for the form builder
* Added: new "Submission Count" access control to limit total form submissions
* Added: new "checkbox" element type
* Added: all settings are exposed via the REST API settings endpoint
* Enhanced: element types can now contain multiple fields
* Enhanced: form stats now scale by storing aggregate results persistently
* Enhanced: form submissions now have a status
* Enhanced: form submissions are now stored in the database immediately which prevents leakage due to cookie or session issues
* Enhanced: form access can now be restricted based on the total submission count
* Enhanced: form access can now be restricted based on a logged-in user's role
* Enhanced: Entries are no longer a submodule, instead their functionality is now located in the submissions list
* Enhanced: Modules API now follows a clean structure throughout all modules
* Enhanced: access controls are a separate module instead of a group of form settings
* Enhanced: spam protection submodules are now part of a new protectors module
* Enhanced: indexes have been added to database tables to speed up queries
* Enhanced: a consistent fields API is used throughout the entire plugin
* Enhanced: meta and settings fields can now have dependencies
* Enhanced: more modern look and feel
* Enhanced: dedicated PSR-3 compatible logger class
* Fixed: installation routine works now properly on multisite setups of any size
* Fixed: it is now possible to properly register any kind of modules and submodules
* Fixed: dynamically loaded editor now works correctly
* Tweaked: 'form' shortcode is now deprecated in favor of 'torro_form'
* Tweaked: 'form_charts' shortcode is now deprecated in favor of 'torro_form_charts'
* Tweaked: 'element_chart' shortcode is now deprecated in favor of 'torro_form_charts'
* Tweaked: form results are now relabelled as form submissions
* Tweaked: element answers are now relabelled as element choices
* Tweaked: result handlers are now relabelled as evaluators
* Tweaked: components are now called modules
* Tweaked: namespaces are used throughout the plugin code
* Tweaked: uses external [`felixarntz/plugin-lib` library](https://github.com/felixarntz/plugin-lib) for standard plugin functionality
* Plus: a lot more improvements and tweaks here or there... It's an entire rewrite, you know.

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

== Upgrade Notice ==

= 1.0.0-beta.9 =

Torro Forms has been completely rewritten for this release. If you're using custom extensions for it, you may wanna consult with the authors of those extensions before upgrading.
