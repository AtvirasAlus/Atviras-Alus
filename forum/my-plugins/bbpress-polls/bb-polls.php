<?php
/*
Plugin Name: bbPress Polls
Description:  allows users to add polls to topics, with optional ajax-like actions
Plugin URI:  http://bbpress.org/plugins/topic/62
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.5.9

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/
*/

global $bb_polls;

add_action( 'bb_send_headers', 'bb_polls_initialize');	// bb_init
if (defined('BACKPRESS_PATH')) {add_action( 'bb-post.php', 'bb_polls_save_on_new' );}
else {add_action( 'bb_post.php', 'bb_polls_save_on_new' );}

function bb_polls_initialize() {	
	global $bb_polls, $bb_polls_type, $bb_polls_label;
	if (!isset($bb_polls)) {
		$bb_polls = bb_get_option('bb_polls');
		if (empty($bb_polls)) {
			require_once("bb-polls-admin.php");
			bb_polls_strings();
		}
	}
	bb_polls_add_header(); 	// add_action('bb_send_headers', 'bb_polls_add_header');	
	$bb_polls['icon']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/icon.png'; 
		
	$is_topic=is_topic();
	$ask_during_new=($bb_polls['ask_during_new'] && $bb_polls['use_ajax'] && (isset($_GET['new']) || is_forum())) ? true : false;
	if ($is_topic || $ask_during_new) {		// this eventually may have to be added on an admin page
		add_action('bb_head', 'bb_polls_add_css');
		add_action('bb_head','bb_polls_add_javascript');
		add_action('topicmeta','bb_polls_pre_poll',200);		
		if ($ask_during_new) {add_action('post_form','bb_polls_pre_topic',9);}
	} if (!$is_topic && !is_bb_feed()) {
		add_filter('topic_title', 'bb_polls_title',100);		
	}		
}

function bb_polls_pre_topic($topic_id=0, $edit_poll=0) { 
	global $topic; $topic=false; 	// bbpress loop bug
	echo "<h3 style='margin:1em 0 0 0;'>".__('Polls')."</h3><ul style='list-style:none;margin:0 0 1em 0;'>"; bb_polls_pre_poll(0,0); echo "</ul>"; 
}

function bb_polls_pre_poll($topic_id, $edit_poll=0) { 
global $bb_polls,$topic,$poll_options,$page;
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) :   
$topic_id=bb_polls_check_cache($topic_id);  
$user_id=bb_get_current_user_info( 'id' );
$administrator=bb_current_user_can('administrate');
$minimum_add_level=bb_current_user_can($bb_polls['minimum_add_level']);
$minimum_edit_level=bb_current_user_can($bb_polls['minimum_edit_level']);
$ask_during_new=(empty($topic_id) && $minimum_add_level && $bb_polls['ask_during_new'] && $bb_polls['use_ajax'] && (isset($_GET['new']) || is_forum())) ? true : false;
if (!$edit_poll && isset($_GET['edit_poll'])) {$edit_poll= intval($_GET['edit_poll']);}
if ($edit_poll || ! isset($topic->poll_options)) {	// no saved poll question with options

	if ($administrator || $ask_during_new || 
		($minimum_add_level
		&& !($bb_polls['only_topic_author_can_add'] && $topic->topic_poster!=$user_id)
		&& !($bb_polls['add_within_hours'] && $bb_polls['add_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600) 
		&& !($edit_poll && $minimum_edit_level && $topic->topic_poster==$user_id && 
		        $bb_polls['edit_within_hours'] && $bb_polls['edit_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600)
	 	&& !($bb_polls['close_with_topic'] && $topic->topic_open!=1)
	 	)) {
	
		if (isset($_POST['poll_question'])) {	 // save new poll setup from _post data 
				bb_polls_save_poll_setup($topic_id);						
				bb_polls_show_poll_vote_form($topic_id);
		} else {
 			if (isset($_GET['start_new_poll']) && intval($_GET['start_new_poll'])) { 
 				bb_polls_show_poll_setup_form($topic_id); 
		} else {
 			if (isset($_GET['edit_poll']) && intval($_GET['edit_poll'])) { 
 				bb_polls_show_poll_setup_form($topic_id,1,1);  				
 		} else {	
			// ask if they want to start a new poll
			echo '<li id="bb_polls"><a class="nowrap" onClick="if (window.bb_polls_insert_ajax) {bb_polls_start_new_poll_ajax();return false;}" href="'.add_query_arg( 'start_new_poll', '1').'">'
				.(!empty($bb_polls['use_icon']) && $bb_polls['use_icon']!="no" ? "<img align='absmiddle' border='0' src='".$bb_polls['icon']."' /> " : "")
				.$bb_polls['poll_question'].'</a></li>'; 
		
		} } }	// end new poll question + end show start_new_poll form 

	}    // 1 2 3  checks for allowed settings to start/edit poll 

} else {		// there's a saved poll question with options

	if (isset($_POST['poll_vote'])) {	// save new poll vote from _post data 		  
			bb_polls_add_vote($user_id,$topic_id);			
			bb_polls_show_poll_results($topic_id);
	} else {
		if (isset($_GET['show_poll_results'])) {	// override to show poll results
			bb_polls_show_poll_results($topic_id); 
	} else {	 // obey per page setting	

		if ( $bb_polls['show_poll_on_which_pages']=="all" 
		||  ($page==1 && ($bb_polls['show_poll_on_which_pages']=="first" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		|| ($page==get_page_number( $topic->topic_posts ) && ($bb_polls['show_poll_on_which_pages']=="last" || $bb_polls['show_poll_on_which_pages']=="both" )) 
		) { 	
			if (!$user_id || bb_polls_has_voted($user_id,$topic_id)) {
		 		bb_polls_show_poll_results($topic_id); 	// they voted, show results
			} else {		 
			
				bb_polls_show_poll_vote_form($topic_id);	// let them vote
	} } } }		
}
endif;	
remove_action('topicmeta','bb_polls_pre_poll',200);  // NullFix ?
} 

function bb_polls_check_cache($topic_id) {
global $bb_polls,$topic,$poll_options;  
if (!$topic_id || $topic_id != $topic->topic_id) {if (!isset($topic)) {bb_repermalink();} $topic_id=get_topic_id(); $topic = get_topic($topic_id);}
if (isset($topic->poll_options)) {if (!isset($poll_options)) {$poll_options=$topic->poll_options;}} else {$poll_options=NULL;}
if (isset($poll_options) && !is_array($poll_options)) {$poll_options=unserialize(substr($poll_options,2));}   // trick bb_press to keep poll data unserialized
return intval($topic_id);
}

function bb_polls_has_voted($user_id,$topic_id) {    
global $bb_polls,$topic,$poll_options;
if ($bb_polls['test_mode']) {return false;}  // for testing only, allows multiple votes by anyone
$topic_id=bb_polls_check_cache($topic_id);
if (!$user_id) {$user_id=bb_get_current_user_info( 'id' );}
$votes=''; for ($i=1; $i<=$bb_polls['max_options']; $i++) {if (isset($poll_options['poll_vote_'.$i])) {$votes.=",".$poll_options['poll_vote_'.$i].",";}}
return (strpos($votes,",".$user_id.",")===false ? false : true);
}

function bb_polls_add_vote($user_id,$topic_id) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) :
$topic_id=bb_polls_check_cache($topic_id);
$voted_flag=false; 
if (!bb_polls_has_voted($user_id,$topic_id)) {		
	for ($i=0; $i<=$bb_polls['max_options']; $i++) { 
		$test=0;		
		if (isset($_POST['poll_vote_'.$i])) {$test=intval($_POST['poll_vote_'.$i]);} 
		elseif (isset($_GET['poll_vote_'.$i])) {$test=intval($_GET['poll_vote_'.$i]);}		
		if ($test>0) :					
			if (!isset($poll_options['poll_count_'.$test])) {	// initialise counters					
				$poll_options['poll_count_'.$test]=0; 
				$poll_options['poll_vote_'.$test]='';
				if (!isset($poll_options['poll_count_0'])) {$poll_options['poll_count_0']=0;} 
			}  else  { $poll_options['poll_vote_'.$test].=",";}							
			$poll_options['poll_vote_'.$test].=$user_id;	// add user's vote, single or multiple
			$poll_options['poll_count_'.$test]++;  		// update count for option
			$voted_flag=true;					// set flag to update overall count			
			if (!$poll_options['poll_multiple_choice']) {break;}	// don't allow single choice votes to count multiple choices, only first answer
		endif;			
	}		
	if ($voted_flag) {$poll_options['poll_count_0']++;}  		// update count for overall	
	bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
	return true;
}  
else {return false;}   // has voted already
endif;
}

function bb_polls_show_poll_results($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
$administrator=bb_current_user_can('administrate');
if ($bb_polls['minimum_view_level']=="read" || bb_current_user_can($bb_polls['minimum_view_level']) ) {
$topic_id=bb_polls_check_cache($topic_id);
$output='<div class="poll_question">'.__('poll').': '.$poll_options['poll_question'].'</div>';

if (!$poll_options['poll_multiple_choice'] && isset($poll_options['poll_count_0'])) {$real_vote_count=intval($poll_options['poll_count_0']);}
else {$real_vote_count=0; if ($poll_options['poll_multiple_choice']) {for ($i=1; $i<=$bb_polls['max_options']; $i++) {if (isset($poll_options['poll_count_'.$i])) {$real_vote_count+=intval($poll_options['poll_count_'.$i]);}}}}

for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if (isset($poll_options[$i])) { 		
		$output.= '<div class="poll_label">'.$poll_options[$i].' : ';	
		$test=(isset($poll_options['poll_count_'.$i]) ? intval($poll_options['poll_count_'.$i]) : 0);
		$output.= ' ('.$test.' '.$bb_polls['label_votes_text'].') <br />';
		if ($test) {
			$vote_percent=(round($test/$real_vote_count,2)*100);
			$vote_width=$vote_percent; if ($vote_width < 5) {$vote_width=5;} else {if ($vote_width >98 ) {$vote_width=98;}}
			$output.= ' <div style="width:'.$vote_width.'%" class="poll_option poll_option'.$i.'"> '.$vote_percent.' % </div> ';
		}
		$output.= ' </div>';
	}
}
$test=(isset($poll_options['poll_count_0']) ? intval($poll_options['poll_count_0']) : 0);
$output.= '<p class="poll_footer">'.intval($test).' '.$bb_polls['label_votes_text'].'</p>';
if (isset($_GET['show_poll_results']) || (bb_get_current_user_info( 'id' ) && !bb_polls_has_voted(bb_get_current_user_info( 'id' ),$topic_id) )) {
$output.= '<p class="poll_footer">( <a onClick="if (window.bb_polls_insert_ajax) {bb_polls_show_poll_vote_form_ajax();return false;}" href="'.remove_query_arg(array('start_new_poll','show_poll_results','edit_poll','delete_poll','show_poll_vote_form_ajax','show_poll_setup_form_ajax','bb_polls_cache')).'">'.$bb_polls['label_now_text'].'</a> )</p>';
}
$output.=bb_polls_edit_link();
$output=stripslashes($output);
if ($display) {echo '<li id="bb_polls">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_show_poll_vote_form($topic_id,$display=1) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can($bb_polls['minimum_vote_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
if ($poll_options['poll_multiple_choice']==1) {$poll_type="checkbox";} else {$poll_type="radio";}
$output='<form action="'.remove_query_arg(array('start_new_poll','edit_poll','delete_poll','show_poll_vote_form_ajax','show_poll_setup_form_ajax','bb_polls_cache')).'" method="post" name="bb_polls" onSubmit="if (window.bb_polls_insert_ajax) {bb_polls_add_vote_ajax();return false;}">
	 <div class="poll_question">'.__('poll').': '.$poll_options['poll_question'].'</div>';
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if (isset($poll_options[$i])) {
		if ($poll_options['poll_multiple_choice']==1) {$poll_name="poll_vote_".$i;} else {$poll_name="poll_vote_0";}
		$output.= '<p><input type="'.$poll_type.'" name="'.$poll_name.'" value="'.htmlentities($i, ENT_QUOTES).'" /> '.$poll_options[$i].' </p>';
	}
}
$output.= '<p class="poll_footer"><input class="submit" type="submit"  name="poll_vote" value="'.$bb_polls['label_vote_text'].'" /></p>
	<p class="poll_footer">( <a onClick="if (window.bb_polls_insert_ajax) {bb_polls_show_poll_results_ajax();return false;}"  href="'.add_query_arg( 'show_poll_results', '1').'">'.$bb_polls['label_results_text'].'</a> )</p></form>';
$output.=bb_polls_edit_link();
$output=stripslashes($output);
if ($display) {echo '<li id="bb_polls">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_edit_link() {
global $bb_polls,$topic,$poll_options; $output=''; $can_edit=false; $can_delete=false;
$administrator=bb_current_user_can('administrate');
$user_id=bb_get_current_user_info( 'id' );
if (bb_current_user_can($bb_polls['minimum_edit_level']) 
&& !($bb_polls['only_topic_author_can_add'] && $topic->topic_poster!=$user_id)		
&& !($bb_polls['edit_within_hours'] && $bb_polls['edit_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600)) {$can_edit=true;}
if ($topic->topic_poster==$user_id && bb_current_user_can($bb_polls['minimum_delete_level'])
&& !($bb_polls['edit_within_hours'] && $bb_polls['edit_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600)) {$can_delete=true;}
if ($administrator || $can_delete) { 
$output.= '<a onClick="return confirm('."'".$bb_polls['label_warning_text']."'".')"  href="'
	.add_query_arg('delete_poll','1',remove_query_arg(array('edit_poll','poll_question','show_poll_results','start_new_poll'))).'">'.$bb_polls['label_delete_text'].'</a> | ';
}
if ($administrator || $can_edit) {
$output.= '<a onClick="if (window.bb_polls_insert_ajax) {bb_polls_edit_poll_ajax(); return false;}"  href="'
	.add_query_arg('edit_poll','1',remove_query_arg(array('poll_question','show_poll_results','start_new_poll'))).'">'.$bb_polls['label_edit_text'].'</a>';
}
return $output;
}

function bb_polls_delete_poll() {
global $bb_polls; $can_delete=false;
$administrator=bb_current_user_can('administrate');
$user_id=bb_get_current_user_info( 'id' );
$topic_id=bb_polls_check_cache($topic_id);
$topic=get_topic($topic_id);
if (empty($topic_id) || empty($topic)) {return;}
if ($topic->topic_poster==$user_id && bb_current_user_can($bb_polls['minimum_delete_level'])
&& !($bb_polls['edit_within_hours'] && $bb_polls['edit_within_hours']<(time()-bb_gmtstrtotime($topic->topic_start_time))/3600)) {$can_delete=true;}
if ($administrator || $can_delete) {	bb_delete_topicmeta($topic_id, 'poll_options');}
}

function bb_polls_save_poll_setup($topic_id=0) {
global $bb_polls,$topic,$poll_options;
if (bb_current_user_can('administrate') || bb_current_user_can($bb_polls['minimum_add_level'])) {
$topic_id=bb_polls_check_cache($topic_id);
$poll_options['poll_question']=trim(substr(strip_tags(stripslashes($_POST['poll_question'])),0,$bb_polls['max_length']));
$poll_options['poll_multiple_choice']=intval($_POST['poll_multiple_choice']);
$options=0;
for ($i=1; $i<=$bb_polls['max_options']; $i++) {
	if ($test=trim(substr(strip_tags(stripslashes($_POST['poll_option_'.$i])),0,$bb_polls['max_length']))) {$options++; $poll_options[$options]=$test;}
} // loop
bb_update_topicmeta( $topic_id, 'poll_options', '..'.serialize($poll_options));  // save and trick bb_press to not deserialize unless necessary
// echo get_topic_id( $topic_id )." - ".$topic_id." : ".serialize($poll_options); exit();
}
}

function bb_polls_show_poll_setup_form($topic_id,$display=1,$edit_poll=0) {
global $bb_polls,$topic,$poll_options;
if (($edit_poll==0 && bb_current_user_can($bb_polls['minimum_add_level']))
     || ($edit_poll && bb_current_user_can($bb_polls['minimum_edit_level'])) 
     || bb_current_user_can('administrate')) {
$is_topic=is_topic(); $topic_id=bb_polls_check_cache($topic_id); $output="";
if ($is_topic) {
$output.='<form action="'.remove_query_arg(array('start_new_poll','edit_poll','delete_poll','show_poll_vote_form_ajax','show_poll_setup_form_ajax','bb_polls_cache')).'" method="post">';
}
$output.='<p>'
	.(!empty($bb_polls['use_icon']) && $bb_polls['use_icon']!="no" ? "<img align='absmiddle' border='0' src='".$bb_polls['icon']."' /> " : "")
	.$bb_polls['poll_instructions'].'</p>';			
$output.='<div class="poll_label">'.$bb_polls['label_question_text'].' : <br /><input name="poll_question" type="text" style="width:98%" maxlength="'.$bb_polls['max_length'].'" value="'.htmlentities($poll_options['poll_question'], ENT_QUOTES).'" /></div>';
			
$output.='<div class="poll_label"><span class="nowrap"><input name="poll_multiple_choice" style="vertical-align:middle;height:1.3em;width:1.3em;" type="radio" value="0" ';
$output.=($poll_options['poll_multiple_choice']) ? ' ' : ' checked="checked" ';
$output.=' /> '.$bb_polls['label_single'].'</span> <span class="nowrap"><input name="poll_multiple_choice" style="vertical-align:middle;height:1.3em;width:1.3em;" type="radio" value="1" ';
$output.=($poll_options['poll_multiple_choice']) ? ' checked="checked" ' : ' ';
$output.=' /> '.$bb_polls['label_multiple'].'</span></div>';
			
for ($i=1; $i<=$bb_polls['max_options']; $i++) {			
	if ($i==5 && $bb_polls['max_options']>4 && !$poll_options[5]) {	// more options input fields hidden until asked for
		$output.='<a href="javascript:void(0)" onClick="this.style.display='."'none'".'; document.getElementById('."'poll_more_options'".').style.display='."'block'".'">[+] '.$bb_polls['label_option_text'].'</a><div id="poll_more_options" style="display:none;">';
	}
	$output.='<div class="poll_label">'.$bb_polls['label_option_text'].' #'.$i.' : <br /><input name="poll_option_'.$i.'" type="text" style="width:98%" maxlength="'.$bb_polls['max_length'].'" value="'.htmlentities($poll_options[$i], ENT_QUOTES).'" /></div>';
} // loop 
if ($bb_polls['max_options']>4 && !$poll_options[5]) {$output.='</div>';}
		
if ($is_topic) {
$output.='<p class="poll_footer">';
// <input class="submit" type="button"  value="'.$bb_polls['label_cancel_text'].'" onClick="document.location='."'".remove_query_arg(array('start_new_poll','edit_poll','delete_poll','show_poll_vote_form_ajax','show_poll_setup_form_ajax','bb_polls_cache'))."'".'" /> 
$output.='<input class="submit" type="button"  value="'.$bb_polls['label_cancel_text'].'" onClick="bb_polls.innerHTML=bb_polls_cancel; return false;" /> ';
$output.='<input class="submit" type="submit"  value="'.$bb_polls['label_save_text'].'" /></p></form>';
} else {
$output.=' &nbsp; <input class="submit" type="button"  value="'.$bb_polls['label_cancel_text'].'" onClick="bb_polls.innerHTML=bb_polls_cancel; return false;" /><br />';
}

$output=stripslashes($output);if ($display) {echo '<li id="bb_polls" class="extra-caps-row">'.$output.'</li>';} else {return $output;}
}
}

function bb_polls_title( $title ) {
	global $bb_polls, $topic;
	if (isset($topic->poll_options)) {
		if ($bb_polls['use_icon']=="left") {$title="<img align='absmiddle' border='0' src='".$bb_polls['icon']."' /> ".$title;}
		elseif ($bb_polls['use_icon']=="right") {$title=$title." <img align='absmiddle' border='0' src='".$bb_polls['icon']."' />";}
		if ($bb_polls['label_poll_text']) {$title='['.$bb_polls['label_poll_text'].'] '.$title;}		
	}
	return $title;
} 

function bb_polls_save_on_new($post_id=0) {
	if (isset($_POST['poll_question'])) {	// save new poll setup from _post data 
		bb_polls_save_poll_setup(0);				
	}	
}

function bb_polls_add_header() { 
	if (isset($_POST['poll_question'])) {	// save new poll setup from _post data 
		bb_polls_save_poll_setup(0);				
		// header("HTTP/1.1 307 Temporary redirect");
		wp_redirect($_SERVER["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
		// exit();  // not sure why but this makes it fail?
	}	
	if (isset($_POST['poll_vote'])) {	// save new poll vote from _post data 		  
		bb_polls_add_vote(bb_get_current_user_info( 'id' ),'');		
		// header("HTTP/1.1 307 Temporary redirect");
		wp_redirect($_SERVER["REQUEST_URI"]);	// I *really* don't like this technique but it's the only way to clear post data?
		// exit();  // not sure why but this makes it fail?
	}
	if (isset($_GET['delete_poll']) && intval($_GET['delete_poll'])) { 	
		bb_polls_delete_poll();
		wp_redirect(remove_query_arg(array('start_new_poll','edit_poll','delete_poll','show_poll_vote_form_ajax','show_poll_setup_form_ajax','bb_polls_cache')));	// I *really* don't like this technique but it's the only way to clear post data?
	}				
	if (isset($_GET['show_poll_results_ajax'])) {
		$topic_id=intval($_GET['show_poll_results_ajax']);
		header("Content-Type: application/x-javascript; charset=utf-8");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_results($topic_id,0)).'");';
		exit();
	}
	if (isset($_GET['show_poll_vote_form_ajax'])) {
		$topic_id=intval($_GET['show_poll_vote_form_ajax']);
		header("Content-Type: application/x-javascript; charset=utf-8");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_vote_form($topic_id,0)).'");';
		exit();
	}	
	if (isset($_GET['show_poll_setup_form_ajax'])) {
		$topic_id=intval($_GET['show_poll_setup_form_ajax']);
		header("Content-Type: application/x-javascript; charset=utf-8");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_setup_form($topic_id,0,0)).'");';
		echo 'setTimeout("document.forms.postform.poll_question.focus()",50);';
		exit();
	}
	if (isset($_GET['add_vote_ajax'])) {
		$topic_id=intval($_GET['add_vote_ajax']);
		bb_polls_add_vote(bb_get_current_user_info( 'id' ),$topic_id);
		header("Content-Type: application/x-javascript; charset=utf-8");
		echo 'bb_polls_insert_ajax("'.mysql_escape_string(bb_polls_show_poll_results($topic_id,0)).'");';
		exit();
	}			
} 

function bb_polls_add_javascript($topic_id=0) {
global $bb_polls, $topic;
if ($bb_polls['use_ajax'] && bb_current_user_can($bb_polls['minimum_vote_level']) ) :
$topic_id=bb_polls_check_cache($topic_id);
echo '<scr'.'ipt type="text/javascript" defer="defer">
<!--
var dhead = document.getElementsByTagName("head")[0];
var bb_polls, bb_polls_cancel, bb_polls_script = null, bb_polls_htmldata = null;

function append_dhead(bb_polls_src) {
if (bb_polls_script) {dhead.removeChild(bb_polls_script);}
d = new Date();  bb_polls_src=bb_polls_src+"&bb_polls_cache="+d.getTime();
bb_polls_script = document.createElement("script");
bb_polls_script.src = bb_polls_src;
bb_polls_script.type = "text/javascript";
bb_polls_script.charset = "utf-8";
setTimeout("bb_polls_IE_fix()",20);
}
function bb_polls_IE_fix() {dhead.appendChild(bb_polls_script);}

function bb_polls_insert_ajax(htmldata) {
bb_polls_htmldata = unescape(htmldata);
setTimeout("bb_polls_insert_ajax_delayed()",20);
}
function bb_polls_insert_ajax_delayed() {
	bb_polls=document.getElementById("bb_polls");
	bb_polls_cancel=bb_polls.innerHTML;
	bb_polls.innerHTML=bb_polls_htmldata;
}
';

// only add new poll support if they can add and there's no poll already 
if (bb_current_user_can($bb_polls['minimum_add_level']) && !isset($topic->poll_options)) {	
echo '
function bb_polls_start_new_poll_ajax() {
append_dhead("'.add_query_arg( 'show_poll_setup_form_ajax', $topic_id).'");
}
';}

// only add edit support if they can edit and saved poll question with options	 
if (bb_current_user_can($bb_polls['minimum_edit_level']) && isset($topic->poll_options)) {	
echo '
function bb_polls_edit_poll_ajax() {
append_dhead("'.add_query_arg( 'show_poll_setup_form_ajax', $topic_id).'");
}
';}

// only add vote and view toggle support javascript if they have not yet voted
if (!bb_polls_has_voted(bb_get_current_user_info( 'id' ),$topic_id) ) {	
echo '
function bb_polls_show_poll_results_ajax() {
append_dhead("'.add_query_arg( 'show_poll_results_ajax', $topic_id).'");
}
function bb_polls_show_poll_vote_form_ajax() {
append_dhead("'.add_query_arg( 'show_poll_vote_form_ajax', $topic_id).'");
}
function bb_polls_add_vote_ajax() {
vote="";
if (document.bb_polls.poll_vote_0) {
for (i = 0; i < document.bb_polls.poll_vote_0.length; i++) {
if (document.bb_polls.poll_vote_0[i].checked) {vote=vote+"&poll_vote_0="+document.bb_polls.poll_vote_0[i].value; break;}
} }
for (i=1; i<='.$bb_polls['max_options'].'; i++) {
	test=eval("document.bb_polls.poll_vote_"+i);
	if (test && test.checked) {vote=vote+"&poll_vote_"+i+"="+i;}
}
if (vote.length) {append_dhead("'.add_query_arg( 'add_vote_ajax', $topic_id).'"+vote);}
else {alert("'.$bb_polls['label_nocheck_text'].'"); return false;}
}
';}

echo '
//-->
</scr'.'ipt>';
endif;
}

function bb_polls_add_css() { 
global $bb_polls;
if (is_topic()) {echo '<style type="text/css">'.$bb_polls['style'].'</style>'; }
} 

if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || strpos($_SERVER['REQUEST_URI'],"/bb-admin/")!==false) { // "stub" only load functions if in admin 
	function bb_polls_add_admin_page() {bb_admin_add_submenu(__('Polls'), 'administrate', 'bb_polls_admin');}
	add_action( 'bb_admin_menu_generator', 'bb_polls_add_admin_page' );
	if (isset($_GET['plugin']) && $_GET['plugin']=="bb_polls_admin") {require_once("bb-polls-admin.php");} // load entire core only when needed
}

?>