<?php
/*
Plugin Name: Unread Posts
Description:  Indicates previously read topics with new unread posts. Features "mark all topics read". Builds on concepts by fel64 and henrybb with feature and performance improvements. No additional plugins or database tables required.
Plugin URI:  http://bbpress.org/plugins/topic/78
Author: _ck_
Author URI: http://bbshowcase.org
Version: 0.9.3

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
*/

$unread_posts['style']=".unread_posts {color:#0000AA;}"	// optional style for topics read with new posts
			    .".unread_login  {color:#000080;}"	// optional style for topics with new posts since last login
			    .".unread_posts_row a {color:#0000AA;}"	// table row class
			    .".unread_login_row a {color:#000080;}";	// table row class

$unread_posts['indicate_forums']=false;		// should forums also be highlighted if there are new posts (note: causes extra query)

$unread_posts['indicate_last_login']=true;	// should topics be highlighted if new posts since last login (regardless if previously read)

$unread_posts['use_row_class']=false;		// use row class instead of just title cell

$unread_posts['topics_per_user']=100;		// how many topics to watch for each user - on a fast, small forum you could probably do 1000 

/*	stop editing here	*/

function unread_posts_init() {
global $bb_current_user;
if ($bb_current_user->ID  && !is_bb_feed()) {	// only bother with the overhead if a user is logged in        - prep page, arrays, etc.
	if (isset($_GET['mark_all_topics_read']) || $_GET['clear_all_topics_read']) {add_action('bb_send_headers', 'up_mark_all_read');}	//  can't hook to automatically place links for this???
	
	elseif (isset($_GET['update_all_topics_read']) || $_GET['catch_all_topics_read']) {add_action('bb_send_headers', 'up_update_all_read');}	//  this too
	
	elseif (is_topic()) {add_action('topicmeta','up_update_topics_read',200);}	// topic pages is where all the heavy lifting is done

	elseif (in_array(bb_get_location(),array('front-page','forum-page', 'tag-page','search-page','favorites-page','profile-page','view-page'))) {	// where should we affect titles
		global $up_read_topics, $up_last_posts, $unread_posts, $up_last_login;
		$user = bb_get_user($bb_current_user->ID);  		
		if ($unread_posts['indicate_last_login']) {$up_last_login=trim(end(explode("|","|".$user->up_last_login)));
 		if ($up_last_login) {$up_last_login=strtotime($up_last_login);} else {$up_last_login=time()-86400;}}
 		else {$up_last_login=0;} 		
		if ($up_last_login || trim($user->up_read_topics,", ")) {
			$up_read_topics=explode(",",$user->up_read_topics);  settype($up_read_topics,"array"); // unpack once, use many times
			$up_last_posts=explode(",",$user->up_last_posts); settype($up_last_posts,"array");	 // unpack once, use many times						
			if ($unread_posts['use_row_class']) {
				add_filter( 'topic_class', 'up_mark_topic_unread');
				if ($unread_posts['indicate_forums'] && in_array(bb_get_location(),array('front-page','forum-page'))) {add_filter( 'bb_forum_class', 'up_mark_forum_unread');}
			} else {
				add_filter('topic_title', 'up_mark_topic_unread');
				if ($unread_posts['indicate_forums'] && in_array(bb_get_location(),array('front-page','forum-page'))) {add_filter( 'get_forum_name', 'up_mark_forum_unread');}
			}
			add_filter('topic_link', 'up_mark_link_unread');	// props kaviaar
			if ($unread_posts['style']) {add_action('bb_head', 'up_add_css');}					
		}
	}
	
}
} 
add_action('bb_init','unread_posts_init',200);

function up_mark_forum_unread($item) { 
global $bbdb,$bb_current_user, $forum, $unread_posts,$up_last_login,$up_forums,$up_last_login_forums;
if (!isset($up_forums)) {			// unfortunately requires an extra query, data impossible to store
	$user = bb_get_user($bb_current_user->ID);  
	if ($user->up_read_topics) {$up_forums=@$bbdb->get_col("SELECT DISTINCT forum_id FROM $bbdb->topics WHERE topic_id IN (".trim($user->up_read_topics,", ").") AND topic_last_post_id  NOT IN (".trim($user->up_last_posts,", ").") ");}
	if (is_array($up_forums)) {$up_forums=array_flip($up_forums);} else 	{$up_forums=array();}	
if ($unread_posts['indicate_last_login'] && !isset($up_last_login_forums)) {		// unfortunately requires an extra query, data impossible to store		
	$up_last_login_forums=@$bbdb->get_col("SELECT DISTINCT forum_id FROM $bbdb->topics WHERE  topic_time >= '".gmdate('Y-m-d H:i:s',$up_last_login)."' "
					.(($user->up_read_topics) ? "AND  topic_id NOT IN (".trim($user->up_read_topics,", ").")" : "") );
	if (is_array($up_last_login_forums)) {$up_last_login_forums=array_flip($up_last_login_forums);} else {$up_last_login_forums=array();}	
}
}
if ($unread_posts['use_row_class']) {
if (isset($up_forums[$forum->forum_id])) {$item=rtrim($item,'" ').' unread_posts_row"';}
elseif ($unread_posts['indicate_last_login'] && isset($up_last_login_forums[$forum->forum_id])) {$item=rtrim($item,'" ').' unread_login_row"';}
} else {
if (isset($up_forums[$forum->forum_id])) {$item = '<span class="unread_posts">' . $item . '</span>';}
elseif ($unread_posts['indicate_last_login'] && isset($up_last_login_forums[$forum->forum_id])) {$item = '<span class="unread_login">' . $item . '</span>';}
}
return $item;
}

function up_mark_topic_unread($item)  {
global $topic, $unread_posts, $up_read_topics, $up_last_posts,$up_last_login;		
	$up_key=array_search($topic->topic_id ,$up_read_topics);	
	if ($unread_posts['use_row_class']) {
	if ($up_key!=false &&  $up_last_posts[$up_key]!=$topic->topic_last_post_id) {$item[]="unread_posts_row";}
	elseif ($up_last_login && $up_key==false && strtotime($topic->topic_time)>=$up_last_login) {$item[]="unread_login_row";}	
	} else {
	if ($up_key!=false &&  $up_last_posts[$up_key]!=$topic->topic_last_post_id) {$item = '<span class="unread_posts">' . $item . '</span>';}
	elseif ($up_last_login && $up_key==false && strtotime($topic->topic_time)>=$up_last_login) {$item = '<span class="unread_login">' . $item . '</span>';}	
	}
return $item;
}

function up_add_css() {global $unread_posts; echo '<style type="text/css">'.$unread_posts['style'].'</style>'; } 

function up_last_login($user_id) {bb_update_usermeta($user_id, "up_last_login",bb_current_time('mysql')."|".substr(bb_get_usermeta($user_id, 'up_last_login'),0,19));}
add_action('bb_user_login', 'up_last_login' );

function up_mark_link_unread($link)  {			// props kaviaar - makes title links jump to last unread post
global $topic, $up_read_topics, $up_last_posts;	
	$up_key=array_search($topic->topic_id ,$up_read_topics);	
	if ($up_key!=false &&  $up_last_posts[$up_key]!=$topic->topic_last_post_id) {$link = get_post_link($up_last_posts[$up_key]);}
 return $link;
}

function up_mark_all_read() {	// actually, just delete all it's meta and start fresh - eventually this could be made to just remove topics in one sub-forum only
global $bb_current_user;	
	bb_delete_usermeta($bb_current_user->ID, "up_read_topics");
	bb_delete_usermeta($bb_current_user->ID, "up_last_posts");
	up_last_login($bb_current_user->ID);	// trick last login to current time to force no-highlighting (ruins real last login though)			
	wp_redirect(remove_query_arg(array("mark_all_topics_read","clear_all_topics_read")));
} 

function up_update_all_read() {	// catches up all topics tracked instead of purging list - props kaviaar
global $bbdb,$bb_current_user;
	$user = bb_get_user($bb_current_user->ID);
	up_last_login($bb_current_user->ID);	// trick last login to current time to force no-highlighting (ruins real last login though)	
	$up_read_topics=trim($user->up_read_topics," ,");
	if ($up_read_topics) {
		$up_last_posts=$bbdb->get_col("SELECT topic_last_post_id FROM $bbdb->topics WHERE topic_id IN ($up_read_topics) ORDER BY field(topic_id,$up_read_topics)");		
		bb_update_usermeta($bb_current_user->ID, "up_read_topics",$up_read_topics);  // needs to resave because of trim
		bb_update_usermeta($bb_current_user->ID, "up_last_posts",implode(",",$up_last_posts));
	}	
	wp_redirect(remove_query_arg(array("update_all_topics_read","catch_all_topics_read")));	
} 

function up_update_topics_read() {
global  $bbdb, $bb_current_user, $topic, $unread_posts;
	$user = bb_get_user($bb_current_user->ID);  
		
	$up_read_topics=explode(",",$user->up_read_topics);  settype($up_read_topics,"array"); 
	$up_last_posts=explode(",",$user->up_last_posts); settype($up_last_posts,"array");	
	
	$up_key=array_search($topic->topic_id ,$up_read_topics);	
	
	if ($up_key===false) {
		$up_read_topics[]=$topic->topic_id;
		$up_last_posts[]=$topic->topic_last_post_id;
		$up_key=-2;							// flag to save save both topics and last post
	} elseif ($up_last_posts[$up_key]!=$topic->topic_last_post_id) {
		$up_last_posts[$up_key]=$topic->topic_last_post_id;		
		$up_key=-1;							// flag to save just last post update
	}
	if ($up_key==-2 && count($up_read_topics)>$unread_posts['topics_per_user']) {		// trim arrays since we are going to do a full save anyway
		$up_read_topics=array_slice($up_read_topics,25-$unread_posts['topics_per_user']);	// offset by 25 so we aren't constantly trimming
		$up_last_posts=array_slice($up_last_posts,25-$unread_posts['topics_per_user']);
	}			

/* how we would simply do it if bbpress wasn't abusing mysql calls
if ($up_key<-1)  {bb_update_usermeta($bb_current_user->ID, "up_read_topics",implode(",",$up_read_topics));}
if ($up_key<0)     {bb_update_usermeta($bb_current_user->ID, "up_last_posts",implode(",",$up_last_posts));}
*/
// how we have to do it instead to save mysql calls - we don't even need the results cached 
// oh, because usermeta has no keys to track duplicates, we cannot use ON DUPLICATE KEY :-( woe
if ($up_key<-1)  {
	if (isset($user->up_read_topics)) {
	$bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".implode(",",$up_read_topics)."' WHERE user_id = $bb_current_user->ID AND meta_key = 'up_read_topics' LIMIT 1");
	} else {
	$bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ($bb_current_user->ID, 'up_read_topics', '".implode(",",$up_read_topics)."')");
	}
}
if ($up_key<0)   {
	if (isset($user->up_read_topics)) {
	$bbdb->query("UPDATE $bbdb->usermeta SET meta_value = '".implode(",",$up_last_posts)."' WHERE user_id = $bb_current_user->ID AND meta_key = 'up_last_posts' LIMIT 1");
	} else {
	$bbdb->query("INSERT INTO $bbdb->usermeta  (user_id, meta_key, meta_value)  VALUES ($bb_current_user->ID, 'up_last_posts', '".implode(",",$up_last_posts)."')");
	}
}
} 
?>