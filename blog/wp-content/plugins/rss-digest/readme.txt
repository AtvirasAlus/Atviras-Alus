=== RSS Digest ===
Contributors: samcharrington
Tags: rss, digest, feed, post, posts, delicious, daily, weekly
Requires at least: 2.8
Tested up to: 3.0
Stable tag: 1.03

RSS Digest is a plugin that creates daily digests from an RSS or Atom feed that you specify.

== Description ==

RSS Digest fetches items from the RSS or Atom feed that you specify and posts them to your blog in a daily or weekly digest. 

A common use is to create a daily digest of items you've posted to social networking sites such as Delicious, Twitter, Facebook, etc.

Unlike the other couple of Wordpress plugins providing similar functionality, RSS Digest features a settings page for easy configuration. (No editing code... Yay!) 

Feature summary:

* Supports RSS and Atom feeds
* Easy to use configuration settings page
* Daily or weekly digest at the time of your choice
* Configurable digest title, category, tags and author
* Digests may be published automatically, or posted as drafts or pending 
* Publishes the description field, allowing you to annotate your imported links
* Based on the latest Wordpress feed fetching technology for enhanced performance and scalability

== Installation ==

Upload the RSS Digest plugin to your blog, Activate it, then Configure it (Settings > RSS Digest) to set your feed information and digest preferences.

Easy peasy.

== Frequently Asked Questions ==

= What feed types does RSS Digest support? =

RSS Digest supports all major versions of RSS and Atom, namely
 
* RSS 0.90
* RSS 0.91 (Netscape)
* RSS 0.91 (Userland)
* RSS 0.92
* RSS 1.0
* RSS 2.0
* Atom 0.3
* Atom 1.0

= Can I use RSS Digest with Wordpress 2.7 or earlier? =

RSS Digest uses the 'fetch_feed' library, which is new to Wordpress 2.8. As a result, RSS Digest currently only works with Wordpress 2.8 or later. 

= Can I remove the text "This digest powered by RSS Digest" from the bottom of each post? =

Yes, but I hope you won't :-) 

Whether the attribution text appears is controlled by the "Give credit to RSS Digest" setting on the configuration page.

Perhaps you'd consider leaving it on for a limited time before removing just to help us get the word out?

= I'd like to see feature XYZ. Can that be added? =

Possibly. Leave a comment on the plugin's homepage with your feature request and we'll see what we can do. 

== Screenshots ==

1. A sample digest.

2. The configuration settings page.

== Changelog ==

= 1.03 =

* FIXED: Removed CSS clear after credit line, which broke some themes and isn't needed in most
* FIXED: Settings page displayed incorrectly

= 1.02 =

* FIXED: Fixed error preventing inline CSS in HTML head from being recognized

= 1.01 =

* OTHER: Improved upgrade from 0.6x to remember items already fetched

= 1.0 =

* FIXED: Plugin now fully compatible with Wordpress 2.9.2
* FIXED: Better timezone handling; display server timezone on options page
* FIXED: URL character handling bug
* FIXED: Rewrote scheduling method, replacing homemade scheduler w/ wordpress cron. Should eliminate duplicate posts seen on some sites.

* ADDED: Added additional debugging information for scheduling system
* ADDED: Greater scheduling granularity (minutes vs quarter-hours)
* ADDED: Display local time on settings page
* ADDED: Choose post status (e.g. publish, draft, pending)
* ADDED: Allow use of post authors without publish status. Note: WP seems to allow plugins to post as users who don't otherwise have the ability to post.
* ADDED: Use timestamps to choose feed items published since last digest
* ADDED: Allow custom tags to be applied to each digest
* ADDED: Clean up options on deactivate
* ADDED: Allow user to specify minimum number of items per digest
* ADDED: Allow user to suppress item descriptions

= 0.6 =

* ADDED: "Preview" option
* ADDED: "Post Now" option
* ADDED: "Reset Settings" option
* ADDED: Ability to append date to title
* FIXED: Author setting not applied correctly
* FIXED: CSS clear after "credit" line
* OTHER: Added debug logger

= 0.5 =

* Initial version.
