=== bbPress Polls ===
Tags: vote, votes, voting, poll, polls, polling, _ck_
Contributors: _ck_
Requires at least: 0.8.2
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

== Description ==

Now you can allow polls to be added to any topic in bbPress.
There are many powerful options via the admin menu.

== Installation ==

1. Add the "bb-polls.php" file to bbPress "my-plugins/" directory and activate. 
2. Check under the admin menu for "bbPress Polls" options.
3. No template edits required.

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==
* 0.5.9 2009-02-22 added checks for user levels when editing and deleting polls
* 0.5.8 2009-02-02 bug fix to handle single and double quotes in poll questions and voting options
* 0.5.7 2009-01-04 do not modify topic titles when in RSS feeds
* 0.5.6	2008-December-30 optional ability to create poll during new topic submission, optional icon without CSS editing
* 0.5.5	2008-August-27 serious ajax bug with data loss fixed, admin functions externalized
* 0.5.4	2008-March-14 new poll form (and edit form) remotely loaded when in "ajax" mode
* 0.5.3	2008-March-14 javascript payload is now kept as small as possible 
* 0.5.2	2008-March-14 xhtml and non-ajax-edit bug fixes, props zmaroti
* 0.5.0	2008-March-1 basic admin menu  added
* 0.30	enhancement so admin can edit any poll (don't try to change the order of questions, it's a simple edit for now)
* 0.29	enhancement so admin can always delete any poll
* 0.28	enhancement so admin are always offered to start a poll on any topic regardless
* 0.27	bugfix: poll not showing for non-logged in guest and view setting set to "read"
* 0.26	warnings cleanup for better code
* 0.25	experimental double-execute fix for Null
* 0.24	bug fix for opera trying to cache javascript requests - added alert if they try to vote without selection (todo: need to alert on non-ajax) 
* 0.23	javascript fix for internet explorer (has to delay append action a few milliseconds or update won't appear to happen)
* 0.22	voting is now ajax-ish - only non-ajax-ish form is the one to create a poll, might be awhile - cancel button also added to create poll form
* 0.21	many little fixes for IE to work properly, css changes to make IE vs Firefox almost identical 
* 0.20	more text found & moved to array for translations, float removed from default css for Right-to-Left setups, graph bars limited to min & max
* 0.19	first ajax-ish behaviours added for view current voting results and then back to the form - pre-caching forms, but no submit saving ajax yet 
* 0.18	post data fix for refreshed pages (via redirect, nasty but no other way?)
* 0.17	trick bbpress to keep data unserialized until needed for performance (backward compatible)
* 0.16	added __() for automatic translations when possible, all text is now in array near top
* 0.15  	cache performance fixes, extra custom label ability, more css classes, colour tweaks
* 0.14	colour fixes for default theme
* 0.13	more control over who can add/vote/view/edit polls 
* 0.12	poll can now be on first/last/both/all pages & add text to topic titles like [poll]
* 0.11	bug fix for polls on page 1 setting
* 0.10	first public beta
* 0.01	bb-polls is born - no voting yet, just create a poll for testing

== Screenshots ==

1. Poll setup
2. Poll example (after voting)
	
== To Do ==

* polls should be able to close with topic
* allow results to display by number of votes
* display a poll anywhere within bbpress templates
* display all polls on a single page
* better editing / vote count editing 
* see who voted
* better poll styles (colors / graphics)

