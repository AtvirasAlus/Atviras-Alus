=== Unread Posts  ===
Tags: topics, posts, threads, unread, new, new posts, track, notify, _ck_
Contributors: _ck_
Requires at least: 0.8
Tested up to: 0.9
Stable tag: trunk

Indicates previously read topics with new unread posts across most types of pages within bbPress. Features "mark all topics read". 

== Description ==

* This is not just "yet another" Show Unread Posts plugin!
* Builds on concepts by fel64 and henrybb with feature and performance improvements. 
* No additional plugins or db tables required. Produces no overhead for non-members and as little as possible otherwise.
* Users can be given a link to "mark all topics read" and you can adjust the number of topics to track to limit bloat.
* Topics with new posts can be customized via css styles, ie. bold, underline, colors, or even an icon.

To see a demonstration, create an account at http://bbShowcase.org read some posts and wait for some replies.

== Installation ==

* Install, activate, optionally edit the unread css style and number of topics tracked per user within unread-posts.php
* If you'd like to give users the ability to "mark all topics read", simply place the following html in your template:
`<a href="?mark_all_topics_read">Mark all topics as read</a>`
* If you'd rather the "all read" function catches up tracked topics instead of stop tracking them entirely, instead place the following html in your template:
`<a href="?update_all_topics_read">Update all topics read</a>`

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.80 (2008-01-31) =

*   first public release of Unread Posts

= Version 0.8.5 (2008-04-02) =

*   now makes title link jump to last unread post - props kaviaar

= Version 0.8.6 (2008-04-08) =

* now allows optional link to catch-up all topics read instead of deleting the list to clear them all - props kaviaar

= Version 0.8.7 (2008-04-27) =

* now can optionally indicate forums with unread posts if you change setting near top of file to `$unread_posts['indicate_forums']=true;` (this causes one extra query for the front page or forum page)

= Version 0.8.9 (2008-05-11) =

* now can also indicate topics with new posts since last login (in alternate color/style than previously read topics)

= Version 0.9.1 (2008-10-15) =

* minor 1.0 compatibility fix

= Version 0.9.2 (2008-11-16) =

* now adds class 'unread_posts_row' and 'unread_login_row' to table rows for easier CSS styling

= Version 0.9.3 (2008-11-17) =

* can now do row class instead of just titles for forums too, bug fix for old forum method (did it ever work?)