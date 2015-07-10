=== Questions ===
Contributors: mahype, awesome-ug
Donate link: http://awesome.ug/donate
Tags: survey, surveys, polls, poll, create poll, custom poll, online poll, custom survey, online survey, votes, voting, wp polls, wp survey, yop poll, online survey, online poll, survey form, data collection, questions
Requires at least: 4.1.1
Tested up to: 4.2.2
Stable tag: 1.0.0

Drag & drop your survey in the WordPress way!

== Description ==

>**Drag & drop your survey in the WordPress way!**
>
>It never felt better to create your survey like with this plugin. Do your survey and own the survey data. This is not software as a service, Questions is a totally independent and free plugin.

**Elements to drop**

* **Text** - Simple text input.
* **Textarea** - Multiple line text input.
* **One Choice** - Radiobutton one choice input.
* **Multiple Choice** - Select one ore more values with this input.
* **Dropdown** - Select your answer from a dropdown input.

**Features**

* **Drag&Drop** - Drag&drop questions to your survey, also sort answers by drag&drop.
* **Timerange** - Give surveys a start and an end date.
* **Bar Chart Results** - Showing results after participating or with shortcodes.
* **Validation** - Validate the entered data in question settings.
* **Participiants** - Add registered users to survey or let everybody participate.
* **Remember Mail** - Send a remembering Email to participiants.
* **Lightweight CSS** - Easy to overwrite CSS in frontend.
* **CVS Export** - Get your results as CVS file.

**Languages**

* English
* German
* Dutch (Thanks to Remco Wesselius)
* Persian (Thanks to Hos3in)
* Swedish (Thanks to Elger Lindgren)

>Feel free to add your own language! We will add it to the code if you send us language files in your language.

**Bug Reporting**

Please report issues at Github:

* https://github.com/awsmug/Questions/issues

This is an Awesome Plugin.

twitter: http://twitter.com/awsmug - GitHub: https://github.com/awsmug

== Installation ==

1. Upload `questions` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. In the left menu appears the `Surveys` button

== Screenshots ==

1. **Drag&Drop your questions and move them up and down**
2. **Also Drag&Drop answers**
3. **Published survey**
4. **Results of a survey**
5. **Inviting WordPress users or let open who can participate**
6. **Text element setting options**
7. **Setup templates for inviting and remembering mails**

== Changelog ==

= 1.0.0 beta 19 =
* Added russian language files (Thanks to [@kosm])
* No export on no results possible

= 1.0.0 beta 18 =
* Added timerange
* Added usernames to export
* Reworked admin component
* Fixed settings page
* Fixed UTF8 bug on exporting surveys
* Removed save function from bulk editing
* Fixed bug on participating with selected members

= 1.0.0 beta 17 =
* Bettered up Chart view (Switched back to Dimple)
* Added result charts to admin
* Ordering charts like ordered in admin
* Added body class on Questions pages
* Added WP Nonce check to forms

= 1.0.0 beta 16 =
* Changed URL to Chartjs because name of file was not uppercase.

= 1.0.0 beta 15 =
* Only showing steps if there is more then one step
* Results can be deleted
* Stripping slashes on exporting CSV
* Showing error messages if there is no answer given
* Switched to Charts.js
* Refactored file structure

= 1.0.0 beta 14 =
* Added missing columns on export if an answer was not given
* Removed WP Editor from Settings API and descriptions because of massive problems with WP Editor and jQuery droppables.

= 1.0.0 beta 13 =
* Making Questions Multisite-Ready
* Flushing rewrite rules correct
* Added WP Editor to settings API
* Changed description editor to WP Editor
* Moved description text under question, before fields
* Fixed bug on double description output
* Bettered up Plugin CSS
* Enhanced code structure

= 1.0.0 beta 12 =
* Fixed exporting bug on exporting multiple choice fields results
* Enhanced code structure

= 1.0.0 beta 11 =
* Added message on reaching PHP max_num_fields
* Enhanced code structure

= 1.0.0 beta 10 =
* Enhanced code structure
* Added Dutch language files (Thanks to [@remcow])
* Also showing questions if question text not have been filled in
* Reworked Drag&Drop area to be sortable from the first drop
* Preparing data after submitting

= 1.0.0 beta 9 =
* Fixed restrictions bug

= 1.0.0 beta 8 =
* Added pariticipiants option "No restrictions"
* Added filters for default on $participiants_restrictions
* Added filters for texts
* Added swedish language files (Thanks to [@elle_])
* Added persian language files (Thanks to Hos3in)
* Code enhancements (Thanks to [@bueltge])

= 1.0.0 beta 7 =
* Fixed problems with errors on further steps
* Made (int) for steps
* Returning shortcode content and not echo it
* Changed collation of tables to utf8_general_ci
* Changed to more secure getting path function
* Fixed text mistake on separator

= 1.0.0 beta 6 =
* Fixed result charts bug
* Fixed some translation fails

= 1.0.0 beta 5 =
* Added Questions Shortcode for embedding surveys
* Fixed some translation fails

= 1.0.0 beta 4 =
* Min length of 0 is possible too now

= 1.0.0 beta 3 =
* Added bar charts and shortcodes for showing results

= 1.0.0 beta 2 =
* Fixed bug after sending form as participiant

= 1.0.0 beta =
* Fist official 1.0.0 beta
