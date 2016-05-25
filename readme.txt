=== Torro Forms ===

Plugin Name:       Torro Forms
Plugin URI:        http://torro-forms.com
Author:            Awesome UG
Author URI:        http://www.awesome.ug
Contributors:      mahype, flixos90, awesome-ug
Requires at least: 4.4
Tested up to:      4.5.2
Stable tag:        1.0.0-beta.3
Version:           1.0.0-beta.3
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
* **Create elements** - Enhance your forms further by creating custom elements. Our easy-to-use Elements API makes it possible.
* **Create actions** - Create additional kinds of actions which will be executed whenever the form is submitted successfully by using our Actions API.
* **Extend more...** - Like elements and actions, you can extend Torro Forms in many other ways too by extending specific PHP classes. It's just like creating widgets in WordPress.
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
