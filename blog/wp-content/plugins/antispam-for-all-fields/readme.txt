=== Antispam for all fields ===
Contributors: Ramon Fincken
Donate link: http://donate.ramonfincken.com
Tags: spam,antispam,phpbbantispam,anti-spam,wordpressantispam,comment,comments,pingback,trackback,ip,lookup
Requires at least: 2.0.2
Tested up to: 3.3.1
Stable tag: 0.8.0

Plugin to reject spam. Port from same author from http://www.phpbbantispam.com
Actually visits the URL from commenter to spider for spamwords.

== Description ==

Plugin to reject spam. Port from same author from http://www.phpbbantispam.com <br>
Actually visits the URL from commenter to spider for spamwords. <br>
Plugin does a lot more such as (this list does not cover all antispam functions present):<br>
* Count for number of web-URI's in comment<br>
* Count on email, IP, URI compared with allready spammed comments<br>
* Checks trackbacks and pingbacks for a valid IP adres (IP visitor must be same as webserver)<br>
* Detailed information by email about the spammed comment. You can approve the comment later on, or blacklist the IP adres.<br>
* Future feature: Add hidden fields with random names<br>
<small><strong>Need PHP5 on your webserver. Does not work on PHP4 webservers.</strong></small>

<br>
<br>Coding by: <a href="http://www.mijnpress.nl">MijnPress.nl</a> <a href="http://twitter.com/#!/ramonfincken">Twitter profile</a> <a href="http://wordpress.org/extend/plugins/profile/ramon-fincken">More plugins</a>


== Installation ==

1. Upload directory `antispam-for-all-fields` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

= I have a lot of questions and I want support where can I go? =

<a href="http://pluginsupport.mijnpress.nl/">http://pluginsupport.mijnpress.nl/</a> or drop me a tweet to notify me of your support topic over here.<br>
I always check my tweets, so mention my name with @ramonfincken and your problem.


== Changelog ==
= 0.8.0 =
Bugfix: Wrong param order for spamword check, sorry!!
Changed: Update msg only shows in backend

= 0.7.9 =
Added: IP whois link in email to admin
Added: Incorporated "No comments without proper HTTP referer" by "Antispam Extra V 0.2 By Budhiman"
Changed: string_is_spam function
Added: Useragent spam check

= 0.7.8 =
Added:  Lots of (more then 25%) dots in mail and free-email

= 0.7.7 =
Added: Trackback and pingback stage 1, based on Simple trackback validation with topsy blocker Stage 1 

= 0.7.6 =
Added: New spam definitions

= 0.7.5 =
Bugfix: Framework did not work on multisite, is_admin() problem.<br>If anyone could help me with that ? :)

= 0.7.1 =
Changed: Small settings check

= 0.7.0 =
Added: Upgrade manager<br>
Added: Added new word to spamlist<br>
Added: Stopforumspam IP and email check<br>
Added: Protection against random website nofollow random etc. See source code for more info

= 0.6.9 =
Changed: Do not sent an email if IP is blacklisted<br>
Changed: Do IP blacklist check first<br>
Changed: IP blacklist now also checks for trackbacks and trackbacks<br>
Added: Remove all records with same mail address OR same URL for this IP

= 0.6.8 =
"So fine I scipped a number"<br>
Added: if IP-adress is present in your WordPress blacklist (see Settings -> Discussion -> Blacklist), reject comment from that IP-adress<br>
Added: if IP-adress is present in your WordPress blacklist, prevent double enties

= 0.6.6 =
Bugfix: used $this instead of $afaf object for IP purposes, sorry!

= 0.6.5 =
Added: nice wp-die message if a comment is held for moderation<br>
Changed: if you blacklist an IP adress, also delete the comment

= 0.6 =
Bugfix: Private function instead of protected, causing the wordlist to halt on error

= 0.5.2 =
Bugfix: Limit bug (array)..

= 0.5.1 =
Bugfix: Random nonce was given multiple times

= 0.5 =
Bugfix: Counter<br>
Added: GUI, you can set thresholds and edit/add/delete spamwords to search for<br>
Added: Mail with more details<br>
Changed: Core file and admin_menu file<br>
Added: Store comment for 7 days, email contains a link to approve comment or blacklist the IP adres<br>

= 0.4 =
Bugfix: plugin_antispam_for_all_fields_stats for spammed stats<br>
Added: Check for number of websites in comment, if above 10 then spam comment

= 0.3 =
Bugfix: forgot to report status, fix that will run once is included.<br>
Fix triggers when a new comment is submitted.<br>
Added counter<br>
Changed wordlist a bit

= 0.2 =
Implemented visit of URL of commenter to spider for spamwords.

= 0.1 =
First release


== Screenshots ==

1. Settings admin GUI
<a href="http://s.wordpress.org/extend/plugins/antispam-for-all-fields/screenshot-1.png">Fullscreen Screenshot 1</a><br>

2. Email notification
<a href="http://s.wordpress.org/extend/plugins/antispam-for-all-fields/screenshot-2.png">Fullscreen Screenshot 2</a><br>
