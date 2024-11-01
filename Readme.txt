=== StackOverflow Profile ===
Contributors: dpchiesa
Tags: stack overflow, stackoverflow, widget, stackoverflow answers
Requires at least: 3.3.2
Tested up to: 4.4.1
Stable tag: 2016.01.30
Donate link: http://dinochiesa.github.io/SOProfile-Donate.html
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.txt

== Description ==

The Stackoverflow Profile Widget is a Wordpress widget that displays a
summary of your profile on the Stackoverflow.com website. It shows your
reputation, the number and kind of medals you've achieved, and a
selected list of answers you have posted in StackOverflow. For each
answer, it displays the question's title, the answer's score, and
the time the answer was last edited.

You can limit the number of answer entries to display.  You can sort the
list based on score or date.

The widget uses the StackExchange API, version 2.0, in order to retrieve
information.

It caches the content for a configurable period.


== Requirements ==

- json_decode / json_encode must be available.
- PHP5.2 or higher
- StackOverflow API Key is optional (http://stackapps.com/questions/2/api-hello-world-code)

== Installation ==

1. Upload plugin folder to the wp-content/plugins/ directory.
2. Go to plugin page in Wordpress, and click "Activate"
3. Go to widgets page in Wordpress and drag StackOverflow Answers Widget to a widget area.
4. Set configuration
    a. Set widget title.
    b. User number can be found in profile URL (http://stackoverflow.com/users/[Your User Number]/[Your Username])
    c. Consumer Key can be found by registering your widget (http://stackapps.com/apps/register). This is optional.
    d. Set number of questions to show.
    e. Set how you want to sort the list.

== Screenshots ==

1. This shows the rendering of the Widget in the sidebar of a WP blog.
2. This shows how to activate the widget in the Plugins menu in the WP Admin backend
3. Configuring the settings for the stackoverflow widget in the WP Admin backend.

== Changelog ==

= 2016.01.30 =
* attempt to be more ssl aware, don't force http:// scheme 
* updated "tested up to" to 4.4.1

= 2014.07.03 =
* corrected usage of register_widget() and wp_register_style()
* included a safe-redirect to prevent direct access to the widget

= 2012.06.22 =
* Fixed 2 bugs in soClient.php - wasnt properly filling cache files.

= 2012.06.18 =
* Now adds a "Latest" sort option, which grabs the most recently edited
  answers.  This is in contrast to the "newest" sort option, which
  selects the answers with the most recent creation date.  Also I removed
  the "oldest" sort option, because it was dumb.
* Refactored all interaction with Stackoverflow into a reusable SOEntity
  class and various other derived classes
* Fixed the caching to do it properly, in the SOEntity class.

= 2012.06.15 =
* initial release - starting from  satrun77's widget
* Get current with StackExchange API 2.0
* Efficient CSS via conditional wp_enqueue_style
* cache lifetimes are configurable now
* now renders with a recognizable Stackoverflow-like skin
* Screenshots are now included in the distribution
* General Code cleanup

== Acknowledgements ==

This plugin was initially based on and inspired by the Stackoverflow
Answers Widget from Mohammed Alsharaf. That code is licensed under the
New BSD license.

Under the terms of that license, I am acknowledging the origins of this
code.  See the License-Alsharaf.txt file for full details.

This plugin differs from that one in that:

 - it uses the official Stackexchange API
 - it caches results properly
 - it displays medal counts
 - it uses a nicer skin
 - can display upvoted answers, or any answers.


== Thanks ==

Thanks for your interest!

You can make a donation at http://dinochiesa.github.io/SOProfile-Donate.html

Check out all my plugins:
http://wordpress.org/extend/plugins/search.php?q=dpchiesa


-Dino Chiesa

