<?php
/*
Plugin Name: Hidden Forums
Description:  Make selected forums completely hidden except to certain members or roles. Uses streamlined code and methods in  bbPress 0.9 to be faster than previous solutions without their quirks.
Plugin URI:  http://bbpress.org/plugins/topic/105
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.0.9
 
Until there is an admin menu you have to create settings yourself manually (sorry).

In the default example below:
1. forums # 500 & 501 are complete hidden 
2. roles KEYMASTER can see ANY hidden forum, ADMINISTRATOR + MODERATOR can see 500 + 501
3. users #1 can see ANY hidden forum,  # 12345 + # 34567  can see 500 + 501

(to get a list of forums by number, use forumname.com?forumlist when this plugin is active)
*/

$hidden_forums['hidden_forums']=array(4);	// hide these forums, list by comma seperated number

$hidden_forums['allow_roles']['all_forums']=array('keymaster','administrator'); 	// these roles can always see ALL forums regardless
$hidden_forums['allow_roles'][4]=array('administrator','vak');	// exact formal role name, *not* ability
$hidden_forums['allow_roles'][4]=array('administrator','vak');	// exact formal role name, *not* ability

$hidden_forums['allow_users']['all_forums']=array(1);		// these users can always see ALL forums regardless
$hidden_forums['allow_users'][500]=array(5432,7654);	// list of users by number
$hidden_forums['allow_users'][501]=array(5432,7654);	// list of users by number

$hidden_forums['label']="[VAK] ";	// text, html, css or image to indicate hidden forums/topics, make it =""; if you don't want any label at all

/*    stop  editing  here    */

add_action('bb_init','hidden_forums_init',200);

function hidden_forums_init() {
global $hidden_forums, $hidden_forums_list, $hidden_forums_array, $bb_views, $bb_current_user, $forum_id, $page;

$id=(!empty($bb_current_user)) ? intval($bb_current_user->ID) : 0;
$hidden_forums_list=array_flip($hidden_forums['hidden_forums']);

if ($id>0) {	// if id=0, don't bother searching allowed exceptions

if ((isset($_GET['listforums']) || isset($_GET['forumlist'])) && 'keymaster'==@reset($bb_current_user->roles)) {echo "<h2>Forum List</h2>"; foreach (get_forums() as $forum) {echo "$forum->forum_id -> $forum->forum_name <br><br>";} exit();}

	if (in_array($id,$hidden_forums['allow_users']['all_forums'])) {$hidden_forums_list=array();}	// don't filter anything
	else {
		$role=@reset($bb_current_user->roles);  			
		if (in_array($role,$hidden_forums['allow_roles']['all_forums'])) {$hidden_forums_list=array();}	// don't filter anything
		else {
			foreach ($hidden_forums['allow_roles'] as $key=>$value) {if (in_array($role,$value)) {unset($hidden_forums_list[$key]);}}
			foreach ($hidden_forums['allow_users'] as $key=>$value) {if (in_array($id,$value)) {unset($hidden_forums_list[$key]);}}
		}
	}
}

if (!empty($hidden_forums_list)) {
	if (is_forum()) {	
		$page = bb_get_uri_page();
		bb_repermalink();
		if (!empty($forum_id) && isset($hidden_forums_list[$forum_id])) {	// user is where they shouldn't be, die
			nocache_headers();
			bb_safe_redirect(bb_get_option('uri'));
			exit;
		}
	}
	
	$hidden_forums_array=$hidden_forums_list;
	$hidden_forums_list=implode(",",array_keys($hidden_forums_list));
 
	$filters=array(	// do not change filter order,  get_topic needs to be first
	'get_topic','get_thread','get_thread_post_ids','get_forums',	
	'get_latest_posts','get_latest_topics','get_latest_forum_posts',	
	'get_recent_user_replies','get_recent_user_threads','get_user_favorites',
	'get_sticky_topics','get_tagged_topics','get_tagged_topic_posts',	
	'bb_recent_search','bb_relevant_search','bb_get_first_post','bb_is_first'	
	);	
	
	if (defined('BACKPRESS_PATH')) { 	// bbPress 1.0 workaround, needs work
		if (!is_topic()) {unset($filters[0]);} else {add_action('get_topic_where','hidden_forums_filter_once',20);}
	} else {
		add_filter('get_forum_where','hidden_forums_filter_and',20);	  // bbPress 1.0 is broken so AND must be forced manually, skip entirely after 1.0a5+
	}	
	foreach ($filters as $filter) {add_filter($filter.'_where','hidden_forums_filter',20);}
	foreach ($bb_views as $key=>$value) {add_action('bb_view_'.$key.'_where','hidden_forums_filter');}
	
}

if (!empty($hidden_forums['label']) && $hidden_forums_list!=array_flip($hidden_forums['hidden_forums'])) {
	add_filter( 'get_forum_name', 'hidden_forums_label',11,2);
	add_filter( 'topic_title', 'hidden_forums_label_topic',11,2);
	add_action('pre_edit_form', 'hidden_forums_label_topic_stop');
}
}

function hidden_forums_filter($where='') {
	global $hidden_forums_list; 
	$prefix=""; if (strpos($where," t.")) {$prefix="t.";} elseif (strpos($where," p.")) {$prefix="p.";}
	return $where.(empty($where) ? " WHERE " : " AND ").$prefix."forum_id NOT IN ($hidden_forums_list) ";
}

function hidden_forums_filter_and($where='') {
	global $hidden_forums_list; 
	$prefix=""; if (strpos($where," t.")) {$prefix="t.";} elseif (strpos($where," p.")) {$prefix="p.";}
	return $where." AND ".$prefix."forum_id NOT IN ($hidden_forums_list) ";
}

function hidden_forums_filter_once($where='') {remove_filter('get_topic_where','hidden_forums_filter',20); return $where;}	// 1.0 workaround

function hidden_forums_label($title,$id) {
	global $hidden_forums;
	 return (!isset($_GET['action']) && (in_array($id,$hidden_forums['hidden_forums'])) ? $hidden_forums['label'] : "").$title;		
}	

function hidden_forums_label_topic($title,$id) {
	global $hidden_forums, $topic; 
	if ($id==$topic->topic_id) {$forum_id=$topic->forum_id;} else {$get_topic=get_topic($id); $forum_id=$get_topic->forum_id;}
	return ((in_array($forum_id,$hidden_forums['hidden_forums'])) ? $hidden_forums['label'] : "").$title;		
}	

function hidden_forums_label_topic_stop() {remove_filter( 'topic_title', 'hidden_forums_label_topic',11);}
?>