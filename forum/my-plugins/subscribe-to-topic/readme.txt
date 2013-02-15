=== Subscribe To Topic  ===
Tags: email,notify,subscribe, _ck_
Contributors: _ck_
Requires at least: 0.9
Tested up to: 0.9
Stable tag: trunk
Donate link: http://bbshowcase.org/donate/

Allows members to track and/or receive email notifications (instant, daily, weekly) for new posts on topics.

== Description ==

Now members can subscribe to get new post notifications via email or simply track topics without emails.

Checks are done to make sure members are not emailed more than once per minute for the same topic and not until they have read the topic again.

If you have an active site, it is important to consider that some ISPs may decide to block 
your forum's emails  simply based on the volume of emails when you use a plugin like this.
Make sure your website has proper SPF, Sender ID and DomainKeys to help delay blocking
but if it's a very large site you'll probably have to pay a whitelisting service to stop blocks eventually.

== Installation ==

* Install, activate.

* View some topics and subscribe to them via the topic meta at the top.

* Check profile  page to see it working.  No edits required unless you want to disable features like the additional view.

* No admin menu yet, only "instant" email option available for now - "daily" and "weekly" coming later.

== Frequently Asked Questions ==

= How can I add a graphic icon to the subscribe area in the topic meta? =

* edit your css as desired, the "Subscribe To Topic" line has an id of #subscribe_to_topic

== License ==

* CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

== Donate ==

* http://bbshowcase.org/donate/

== Changelog ==

= Version 0.0.1 (2008-12-26) =

* early alpha release for testing, feedback and bug reports

= Version 0.0.2 (2009-01-05) =

* bug fix for those who do not use pretty-permalinks

= Version 0.0.3 (2009-03-20) =

* bug fix for missing subscriptions tab for regular members

= Version 0.0.4 (2009-04-10) =

* use proper capabilities prefix
* use bb_mail instead of direct mail function

= Version 0.0.5 (2009-06-05) =

* optional checkbox in new post area for subscriptions (on by default)
* optional "simple mode" which uses a link instead of a dropdown (off by default)
* ability to change database name

== To Do ==

* bcc research to send only one email notify to many members
* daily, weekly email summaries (cron support?)
* 3rd party emailer support when php mail not available
* templating system to allow custom email layout
* admin menu
