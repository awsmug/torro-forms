=== Torro Forms ===

Plugin Name:       Torro Forms
Plugin URI:        http://torro-forms.com
Author URI:        http://awesome.ug
Author:            Awesome UG
Contributors:      mahype, flixos90, awesome-ug
Requires at least: 4.1.1
Tested up to:      4.4.2
Stable tag:        1.0.0alpha1
Version:           1.0.0alpha1
License:           GPL v3
License URI:       http://www.gnu.org/licenses/gpl-3.0.html
Tags:              forms, form builder, formbuilder, survey, surveys, polls, poll, create poll, custom poll, online poll, custom survey, online survey, votes, voting, wp polls, wp survey, yop poll, online survey, online poll, survey form, data collection, questions

Easy & Extendable WordPress Formbuilder

== Description ==

>**Drag & drop your form in the WordPress way!**
>
>This easy to use WordPress formbuilder serves a chart module and an API for extending the form functionalities.

**Elements**

* **Text** - Simple text input.
* **Textarea** - Multiple line text input.
* **One Choice** - Radiobutton one choice input.
* **Multiple Choice** - Select one ore more values with this input.
* **Dropdown** - Select your answer from a dropdown input.

**Features**

* **Drag&Drop** - Drag&drop elements to your form
* **Split forms** - Split your form into several steps.
* **Bar Chart Results** - Showing results after participating or with shortcodes.
* **Validation** - Validate the entered data in question settings.
* **Participiants** - Add registered users to forms or let everybody participate.
* **Invite participants** - Send a remembering Email to participants.
* **Lightweight CSS** - Easy to overwrite CSS in frontend.
* **Excel&CVS Export** - Get your results as CVS file.
* **Timerange** - Give forms a start and an end date.
* **reCaptcha** - Integrated reCaptcha module.

**API**
* **Element API** - Easy add your own elements.
* **Restriction API** - Who has access to the form?

>Extend the form builder with our easy to use API! You're missing something? Just let us know at https://github.com/awsmug/Questions/issues!

**Languages**

* English
* German
* Dutch (Thanks to [@remcow])
* Italian (Thanks to Giovanni Simiani)
* Persian (Thanks to Hos3in)
* Russian (Thanks to [@kosm])
* Swedish (Thanks to [@elle_])

>Feel free to add your own language! We will add it to the code if you send us language files in your language.

**Bug Reporting**

Please report issues at Github:

* https://github.com/awsmug/Questions/issues

This is an Awesome Plugin.

twitter: http://twitter.com/awsmug - GitHub: https://github.com/awsmug

== Installation ==

1. Upload `questions` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the left menu appears the `Forms` button

== Screenshots ==

1. **Drag&Drop your questions and move them up and down**
2. **Also Drag&Drop answers**
3. **Published form**
4. **Results of a form**
5. **Invite WordPress users or let open who can participate**
6. **Text element setting options**
7. **Setup templates for inviting and remembering mails**

== Changelog ==

= 1.0.0alpha1 =
* Reworked nearly the complete code
* Resetted version to 'alpha 1' because of renaming the software

= 1.0.0beta20 =
* Questions becomes a form builder with chartable results
* Refactoring Core
* Added restrictions API
* Restriction checks on "All Visitors" can be switched off now

= 1.0.0beta19 =
* Added russian language files (Thanks to [@kosm])
* Added italian language files (Thanks to Giovanni Simiani)
* No export if no results
* Refactored code
* Also duplicating terms for form

= 1.0.0beta18 =
* Added timerange
* Added usernames to export
* Reworked admin component
* Fixed settings page
* Fixed UTF8 bug on exporting surveys
* Removed save function from bulk editing
* Fixed bug on participating with selected members

= 1.0.0beta17 =
* Bettered up Chart view (Switched back to Dimple)
* Added result charts to admin
* Ordering charts like ordered in admin
* Added body class on Questions pages
* Added WP Nonce check to forms

= 1.0.0beta16 =
* Changed URL to Chartjs because name of file was not uppercase.

= 1.0.0beta15 =
* Only showing steps if there is more then one step
* Results can be deleted
* Stripping slashes on exporting CSV
* Showing error messages if there is no answer given
* Switched to Charts.js
* Refactored file structure

= 1.0.0beta14 =
* Added missing columns on export if an answer was not given
* Removed WP Editor from Settings API and descriptions because of massive problems with WP Editor and jQuery droppables.

= 1.0.0beta13 =
* Making Questions Multisite-Ready
* Flushing rewrite rules correct
* Added WP Editor to settings API
* Changed description editor to WP Editor
* Moved description text under question, before fields
* Fixed bug on double description output
* Bettered up Plugin CSS
* Enhanced code structure

= 1.0.0beta12 =
* Fixed exporting bug on exporting multiple choice fields results
* Enhanced code structure

= 1.0.0beta11 =
* Added message on reaching PHP max_num_fields
* Enhanced code structure

= 1.0.0beta10 =
* Enhanced code structure
* Added Dutch language files (Thanks to [@remcow])
* Also showing questions if question text not have been filled in
* Reworked Drag&Drop area to be sortable from the first drop
* Preparing data after submitting

= 1.0.0beta9 =
* Fixed restrictions bug

= 1.0.0beta8 =
* Added pariticipiants option "No restrictions"
* Added filters for default on $participants_restrictions
* Added filters for texts
* Added swedish language files (Thanks to [@elle_])
* Added persian language files (Thanks to Hos3in)
* Code enhancements (Thanks to [@bueltge])

= 1.0.0beta7 =
* Fixed problems with errors on further steps
* Made (int) for steps
* Returning shortcode content and not echo it
* Changed collation of tables to utf8_general_ci
* Changed to more secure getting path function
* Fixed text mistake on separator

= 1.0.0beta6 =
* Fixed result charts bug
* Fixed some translation fails

= 1.0.0beta5 =
* Added Questions Shortcode for embedding surveys
* Fixed some translation fails

= 1.0.0beta4 =
* Min length of 0 is possible too now

= 1.0.0beta3 =
* Added bar charts and shortcodes for showing results

= 1.0.0beta2 =
* Fixed bug after sending form as participant

= 1.0.0beta=
* Fist official 1.0.0 beta
