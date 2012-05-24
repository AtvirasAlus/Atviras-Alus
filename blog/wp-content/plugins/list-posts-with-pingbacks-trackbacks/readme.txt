=== Plugin Name ===
Contributors: christopherross
Plugin URI: http://thisismyurl.com/downloads/wordpress/plugins/list-posts-with-pingbacks-and-tracks/
Tags: pingbacks, trackbacks, posts, popular, incoming links, plugin, post
Donate link:  http://regentware.com/donate/
Requires at least: 2.0.0
Tested up to: 3.2.0
Stable tag: 2.0.0

This function is designed to allow you to add a list of popular posts to your website theme based on which posts have pingback and trackbacks.

== Description ==

This function is designed to allow you to add a list of popular posts to your website theme based on which posts have pingback and trackbacks.

The plugin allows you to select the number of links to show, control how they are shown and include a link to the third party websites (with or without nofollow links) as a thank you for linking to your articles.

== Screenshots ==


== Installation ==

To install the plugin, please upload the folder to your plugins folder and active the plugin.

== Updates ==
Updates to the plugin will be posted here, to [thisismyurl.com]
(http://thisismyurl.com/downloads/wordpress/plugins/list-posts-with-pingbacks-and-tracks/)

== Frequently Asked Questions ==

= How do I display the results? =

Insert the following code into your WordPress theme files: 

= General result s=
Without passing any parameters, the plugin will return ten results or fewer depending on how many posts you have.

thisismyurl_listpostswithpingbacks();

= Specific number of results =
If you would like to return a specific number of results as your maximum:

thisismyurl_listpostswithpingbacks('count=10');

= Altering the before and after values =
By default the plugin wraps your code in list item (&lt;li&gt;) tags but you can specify how to format the results using the following code:


thisismyurl_listpostswithpingbacks('before=&lt;p&gt;&amp;after=&lt;/p&gt;');

= Pingback vs. Trackback =
You can choose to return Pingbacks, Trackbacks or both using the attributes:

pingback
trackback
both

thisismyurl_listpostswithpingbacks('type=both');



= Minimum Pagerank to be listed =
If you would like you can specify a minimum Google PageRank for the incoming website to reach to be included in the list, this cuts down on link spam.

thisismyurl_listpostswithpingbacks('minpr=4');


= Include a Link? =
If you would like to include a link to the website that linked to you, the link=true will do if for you or, you can use link=false to not show the link.

thisismyurl_listpostswithpingbacks('link=true');					
= Follow the Link? =
If you'd like to add a nofollow to the link, you can set it to true.

thisismyurl_listpostswithpingbacks('nofollow =true');	
		
= Format the Link =
The plugin understands two basic options, #post# and #link#. Using the format function you can reorder the appearance of the output.

thisismyurl_listpostswithpingbacks('format = #post# - #link#');


= Change Sort Order =
You can alter the search order of displayed links by changing the order attribute to desc (the default), asc or rand to randomize the order displayed.

thisismyurl_listpostswithpingbacks('order=desc');

			
= Echo vs. Return =
Finally, if you'd like to copy the results into a variable you can return the results as follows:

thisismyurl_listpostswithpingbacks('show=false');


== Donations ==
If you would like to donate to help support future development of this tool, please visit [thisismyurl.com]
(http://thisismyurl.com/downloads/wordpress/plugins/list-posts-with-pingbacks-and-tracks/)


== Change Log ==

= 2.0.0 =

* upgraded for WordPress 3.2
* added Widget
* removed admin interface
* changed ListPostsWithPingbacksandTrackbacks to thisismyurl_listpostswithpingbacks for compatibility

= 1.1.1 = 

* removed update routines

= 0.0.2 =
Fix to readme.txt file
		
= 0.1.0 =
Fix to readme.txt file
Addition of Admin menus

= 0.1.1 =
Added the order function

= 0.1.2 =
Fixed menu length 
Fix to readme.txt file

= 1.0.0 =
Official Release

= 1.1.0 =
Added new menus
option to check PR before displaying

= 1.1.5 =
Add new common wp-admin interface


= 1.2 =
Add new admin options