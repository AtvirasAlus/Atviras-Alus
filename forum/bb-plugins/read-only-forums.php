<?php
/*
Plugin Name: Read-Only Forums
Description: Prevent all or certain members from starting topics or just replying in certain forums while allowing posting in others. Moderators and administrators can always post. Note that this does not hide forums, just prevents posting.
Plugin URI:  http://bbpress.org/plugins/topic/103
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.5

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Instructions: tinker with settings below, install, activate
*/

global $read_only_forums,$bb_current_user, $bb_roles;

// edit users (and forums) by NUMBER below until an admin menu can be made

$read_only_forums['deny_all_start_topic']=false;  // true = stop ALL members from starting topics in ALL forums 
$read_only_forums['deny_all_reply']=false;	  // true = stop ALL members from replying to topics in ALL forums

$read_only_forums['deny_forums_start_topic']=array(4);  // which forums should ALL members NOT be able to start topics
$read_only_forums['deny_forums_reply']=array(4);  	  // which forums should ALL members NOT be able to reply to posts

$read_only_forums['allow_members_start_topic']= array(4=>array(), 9=>array());  // allow override for this member=>forums
$read_only_forums['allow_members_reply']=	array(4=>array(), 9=>array()); 	// allow override for this member=>forums

$read_only_forums['deny_members_start_topic']= array(4=>array(), 9=>array()); // deny this specific member=>forums
$read_only_forums['deny_members_reply'] =      array(4=>array(), 9=>array()); // deny this specific member=>forums

$read_only_forums['allow_roles_always']=array('moderator','administrator','keymaster','vak'); // these types of users can always start/reply

$read_only_forums['message_deny_start_topic']=__("Posting in this forum has been restricted.");
$read_only_forums['message_deny_reply'] = __("Posting in this topic has been restricted.");

// stop editing here

function read_only_forums($retvalue, $capability, $args) {

if ($capability!="write_post" && $capability!="write_topic") {return $retvalue;} // not our problem

global $read_only_forums,$bb_current_user;

if (!$bb_current_user->ID) {return $retvalue;}	// not logged in

if (in_array(reset($bb_current_user->roles),$read_only_forums['allow_roles_always'])) {return true;}	// role in override list

if ($capability=='write_topic') {	// $args = forum_id	
	$forum=intval($args[1]);	
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['allow_members_start_topic'])) { return true;}
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['deny_members_start_topic']))  { return false;}

	if (in_array($forum,$read_only_forums['deny_forums_start_topic'])) {return false;} // check specific forum blocks
	if ($read_only_forums['deny_all_start_topic']) {return false;}	// stop all members from starting topics
}

if ($capability=='write_post') {	// $args = topic_id
	$topic=get_topic(intval($args[1])); $forum=$topic->forum_id;
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['allow_members_reply'])) {return true;}
	if (read_only_forums_dig($bb_current_user->ID,$forum,$read_only_forums['deny_members_reply']))  {return false;}
	
	if (in_array($forum,$read_only_forums['deny_forums_reply'])) {return false;} // check specific forum blocks
	if ($read_only_forums['deny_all_reply']) {return false;} // stop all members from replying to topics	
}

return $retvalue;
}

function read_only_forums_dig($user,$forum,$list) {
	if (!is_array($list)) {return false;}	// should never happen
	if (!isset($list[$user])) {return false;} // user not even listed 
	if (!is_array($list[$user])) {if (strpos($list[$user],",")) {$list[$user]=explode(",",$list[$user]);} else {$list[$user]=array(intval($list[$user]));}} // nasty
	if (in_array($forum,$list[$user])) {return true;}
return false;	
}

add_filter('bb_current_user_can','read_only_forums',10,3);

function read_only_post_form($h2='') {	
	if (!bb_is_user_logged_in()) {post_form($h2);} 
	else {	
		global $read_only_forums,$page, $topic, $forum;	
		if (is_topic()) {$args[1]=$topic->topic_id; if (read_only_forums(true, 'write_post', $args)) {post_form($h2);} 
			else {echo $read_only_forums['message_deny_reply'] . "\n";}
		} else { 
		if (is_forum()) {$args[1]=$forum->forum_id; if (read_only_forums(true, 'write_topic', $args)) {post_form($h2);} 
			else {echo $read_only_forums['message_deny_start_topic'] . "\n";}	
		}}
	}
}

function read_only_forums_list_forums() {
if (bb_current_user_can('administrate')) {
echo "<h2>Forum List</h2>"; foreach (get_forums() as $forum) {echo "$forum->forum_id -> $forum->forum_name <br><br>";} exit();
}
} if (isset($_GET['listforums']) || isset($_GET['forumlist'])) {add_action('bb_init','read_only_forums_list_forums');}

/*	not going to use this for now because it prevents overrides
if ($read_only_forums['deny_all_start_topic']) {$bb_roles->remove_cap('member','write_topics');}
if ($read_only_forums['deny_all_reply']) {$bb_roles->remove_cap('member','write_posts');}
*/
?>