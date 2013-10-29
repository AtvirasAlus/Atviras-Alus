<?php

function bb_polls_admin() {
	global $bb_polls, $bb_polls_type, $bb_polls_label;
	bb_polls_strings();			
	?>
		<div style="text-align:right;margin-bottom:-1.5em;">			
			[ <a href="<?php echo add_query_arg('bb_polls_reset','1',remove_query_arg('bb_polls_recount')); ?>">Reset All Settings To Defaults</a> ] 			
		</div>
		
		<h2>bbPress Polls</h2>
		
		<form method="post" name="bb_polls_form" id="bb_polls_form" action="<?php echo remove_query_arg(array('bb_polls_reset','bb_polls_recount')); ?>">
		<input type="hidden" name="bb_polls" value="1" />
			<table class="widefat">
				<thead>
					<tr> <th width="33%">Option</th>	<th>Setting</th> </tr>
				</thead>
				<tbody>
					<?php
					
					foreach($bb_polls_type as $key=>$value) {
					
					// if ($key=="style") {echo "<div id='bb_polls_rollup' style='display:none;'>";}
					
					$bb_polls[$key]=stripslashes_deep($bb_polls[$key]);					
					$colspan= (substr($bb_polls_type[$key],0,strpos($bb_polls_type[$key].",",","))=="array") ? "2" : "1";
						?>
						<tr <?php alt_class('recount'); ?>>
							<td nowrap colspan=<?php echo $colspan; ?>>
							<label for="bb_polls_<?php echo $key; ?>">
							<b><?php  if ($bb_polls_label[$key])  {echo $bb_polls_label[$key];} else {echo ucwords(str_replace("_"," ",$key));} ?></b>
							</label>
							<?php
							if ($colspan<2) {echo "</td><td>";} else {echo "<br />";}
							switch (substr($bb_polls_type[$key],0,strpos($bb_polls_type[$key].",",","))) :
							case 'binary' :
								?><input type="radio" name="<?php echo $key;  ?>" value="1" <?php echo ($bb_polls[$key]==true ? 'checked="checked"' : ''); ?> />Yes 									&nbsp; 
								     <input type="radio" name="<?php echo $key;  ?>" value="0" <?php echo ($bb_polls[$key]==false ? 'checked="checked"' : ''); ?> />No <?php
							break;
							case 'numeric' :
								?><input type="text" maxlength=3 name="<?php echo $key;  ?>" value="<?php echo $bb_polls[$key]; ?>" /> <?php 
							break;
							case 'textarea' :								
								?><textarea rows="9" style="width:98%" name="<?php echo $key;  ?>"><?php echo $bb_polls[$key]; ?></textarea><?php 							
							break;
							default :  // type "input" and everything else we forgot
								$values=explode(",",$bb_polls_type[$key]);
								if (count($values)>2) {
								echo '<select name="'.$key.'">';
								foreach ($values as $value) {echo '<option '; echo ($bb_polls[$key]== $value ? 'selected' : ''); echo '>'.$value.'</option>'; }
								echo '</select>';
								} else {														
								?><input type="text" style="width:98%" name="<?php echo $key;  ?>" value="<?php echo $bb_polls[$key]; ?>" /> <?php 
								}
							endswitch;							
							?>
							</td>
						</tr>
						<?php
					} 
					// echo "</div>";
					?>
				</tbody>
			</table>
			<p class="submit"><input type="submit" name="submit" value="Save bbPress Polls Settings" /></p>
		
		</form>
		<?php
}

function bb_polls_process_post() {
global $bb_polls, $bb_polls_type, $bb_polls_label;
bb_polls_strings();
	if (bb_current_user_can('administrate')) {
		if (isset($_REQUEST['bb_polls_reset'])) {
			unset($bb_polls); 		
			bb_delete_option('bb_polls');
			bb_polls_initialize();			
			bb_admin_notice('<b>bbPress Polls: '.__('All Settings Reset To Defaults.').'</b>'); 	// , 'error' 			
			wp_redirect(remove_query_arg(array('bb_polls_reset')));	// bug workaround, page doesn't show reset settings
		}		
		elseif (isset($_POST['submit']) && isset($_POST['bb_polls'])) {
							
			foreach($bb_polls_type as $key=>$value) {
				if (isset($_POST[$key])) {$bb_polls[$key]=$_POST[$key];}
			}
		
			bb_update_option('bb_polls',$bb_polls);
			bb_admin_notice('<b>bbPress Polls: '.__('All Settings Saved.').'</b>');
			// unset($GLOBALS['bb_polls']); $bb_polls = bb_get_option('bb_polls');
		}
	}
}
add_action( 'bb_admin-header.php','bb_polls_process_post');

function bb_polls_strings() {
global $bb_polls, $bb_polls_type, $bb_polls_label;
if (empty($bb_polls)) {

	$bb_polls['minimum_view_level']="read";   // who can view polls = read / participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_vote_level']="participate";   // who can vote on polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_add_level']="participate";   // who can add polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_edit_level']="administrate";   // who can edit polls = participate / moderate / administrate  (watchout for typos)
	$bb_polls['minimum_delete_level']="administrate";   // who can edit polls = participate / moderate / administrate  (watchout for typos)

	$bb_polls['only_topic_author_can_add']=true;   // false=anyone can add a poll to any topic /  true=only the topic starter (admin can always add)
	$bb_polls['ask_during_new']=false;	 // insert poll form/css/javascript on new topic creation pages
	
	$bb_polls['show_poll_on_which_pages']="both";    // show poll only on pages = first / last / both / all
		
	$bb_polls['add_within_hours']=3;   // how many hours later can a poll be added 	(for users/moderator - admin can always add)
	$bb_polls['edit_within_hours']=12;   // how many hours later can poll be edited	(for users/moderator - admin can always edit)		

	$bb_polls['close_with_topic']=true;   // if topic is closed, is poll closed?						// doesn't work yet
	$bb_polls['close_after_days']=365;   // if not closed with topic, close after how many days?				// doesn't work yet

	$bb_polls['max_options']=9;     // default number of poll answer slots offered 
	$bb_polls['max_length']=100;     // how long can the poll question & answers be
	$bb_polls['options_sort']=false;	 // true=show options by most votes, false=show options in original order
	
	$bb_polls['use_ajax']=true;		// true = enables ajax-ish behaviours, still works without javascript  / false = typical page refreshes
	$bb_polls['test_mode']=false;	// if set to "true" allows multiple votes per person for testing purposes only
	$bb_polls['use_icon']="right";

	$bb_polls['style']=
	"#bb_polls {list-style: none; width:35em; line-height:120%; margin:5px 0; padding:5px; border:1px solid #ADADAD;  font-size:90%; color:#000; background:#eee; }
	#bb_polls .submit {cursor: pointer; cursor: hand; text-align:center; padding:2px 5px;}
	#bb_polls .nowrap {white-space:nowrap;}
	#bb_polls p {margin:15px 0;padding:0;}
	#bb_polls .poll_question, #bb_polls .poll_footer {font-weight:bold; text-align:center; color:#2E6E15;}
	#bb_polls .poll_label {font-weight:bold; margin:1em 0 1em 1em;}								
	#bb_polls .poll_option {overflow:hidden; white-space:nowrap; margin:2px 0 -2px 0; text-align:center; font-size:11px; line-height:9px; padding:1px 0 0 0;  border:1px solid #303030; color:#fff; }
	#bb_polls .poll_option1 {background:red;}
	#bb_polls .poll_option2 {background:green;}
	#bb_polls .poll_option3 {background:blue;}
	#bb_polls .poll_option4 {background:orange;}
	#bb_polls .poll_option5 {background:purple;}
	#bb_polls .poll_option6 {background:pink;}
	#bb_polls .poll_option7 {background:olive;}
	#bb_polls .poll_option8 {background:navy;}
	#bb_polls .poll_option9 {background:teal;}	
	#bb_polls .poll_option10 {background:aqua;}
	#bb_polls .poll_option11 {background:maroon;}
	#bb_polls .poll_option12 {background:fuchsia;}
	";			
					
	$bb_polls['poll_question']=__("Would you like to add a poll to this topic for members to vote on?");
	$bb_polls['poll_instructions']=__("You may add a poll question with options for members to vote from.");
	$bb_polls['label_single']=__("you can vote on <u>ONE</u> choice");
	$bb_polls['label_multiple']=__("you can vote on <u>MULTIPLE</u> choices");
	$bb_polls['label_poll_text']=__("poll");    // default "poll" = text to show if on topic title if it has a poll (delete text to disable) // you can even use HTML/CSS
	$bb_polls['label_votes_text']=__("votes");  // default "votes" = text to show for votes
	$bb_polls['label_vote_text']=__("Vote");  // default "VOTE" = text to show for VOTE button	
	$bb_polls['label_save_text']=__("Save");  // default "SAVE" = text to show for SAVE button
	$bb_polls['label_cancel_text']=__("Cancel");  // default "CANCEL" = text to show for CANCEL button
	$bb_polls['label_edit_text']=__("Edit");  // default "EDIT" = text to show for Edit button
	$bb_polls['label_delete_text']=__("Delete");  // default "DELETE" = text to show for Delete button
	$bb_polls['label_option_text']=__("option");  // default "option" = text to show for options
	$bb_polls['label_question_text']=__("poll question");
	$bb_polls['label_results_text']=__("show poll results");
	$bb_polls['label_now_text']=__("vote now");	
	$bb_polls['label_nocheck_text']=__("You haven't selected anything!");	
	$bb_polls['label_warning_text']=__("This cannot be undone. Are you sure to delete?");

bb_update_option('bb_polls',$bb_polls);
}
if ((defined('BB_IS_ADMIN') && BB_IS_ADMIN) || strpos($_SERVER['REQUEST_URI'],"/bb-admin/")!==false) {
	
	$bb_polls_type['minimum_view_level']="read,participate,moderate,administrate"; 
	$bb_polls_type['minimum_vote_level']="participate,moderate,administrate";
	$bb_polls_type['minimum_add_level']="participate,moderate,administrate";  
	$bb_polls_type['minimum_edit_level']="participate,moderate,administrate";
	$bb_polls_type['minimum_delete_level']="participate,moderate,administrate";
	
	$bb_polls_type['only_topic_author_can_add']="binary";
	$bb_polls_type['ask_during_new']="binary";
	
	$bb_polls_type['show_poll_on_which_pages']="first,last,both,all";	
		
	$bb_polls_type['add_within_hours']="1,2,6,12,24,48,72,999999";
	$bb_polls_type['edit_within_hours']="1,2,6,12,24,48,72,999999";

	$bb_polls_type['close_with_topic']="binary";
	$bb_polls_type['close_after_days']="1,2,7,30,365";

	$bb_polls_type['max_options']="3,5,9,15,20";
	$bb_polls_type['max_length']="50,100,200";
	$bb_polls_type['options_sort']="binary";
	
	$bb_polls_type['use_ajax']="binary";
	$bb_polls_type['test_mode']="binary";

	$bb_polls_type['use_icon']="right,left,no";
	$bb_polls_type['style']="textarea";	
					
	$bb_polls_type['poll_question']="text";
	$bb_polls_type['poll_instructions']="text";
	$bb_polls_type['label_single']="text";
	$bb_polls_type['label_multiple']="text";
	$bb_polls_type['label_poll_text']="text";
	$bb_polls_type['label_votes_text']="text";
	$bb_polls_type['label_vote_text']="text";
	$bb_polls_type['label_save_text']="text";
	$bb_polls_type['label_cancel_text']="text";
	$bb_polls_type['label_edit_text']="text";
	$bb_polls_type['label_delete_text']="text";
	$bb_polls_type['label_option_text']="text";
	$bb_polls_type['label_question_text']="text";
	$bb_polls_type['label_results_text']="text";
	$bb_polls_type['label_now_text']="text";
	$bb_polls_type['label_nocheck_text']="text";
	$bb_polls_type['label_warning_text']="text";
	
	
	$bb_polls_label['minimum_view_level']=__("At what level can users SEE polls?");
	$bb_polls_label['minimum_vote_level']=__("At what level can users VOTE on polls?");
	$bb_polls_label['minimum_add_level']=__("At what level can users ADD a poll?");
	$bb_polls_label['minimum_edit_level']=__("At what level can users EDIT a poll?");
	$bb_polls_label['minimum_delete_level']=__("At what level can users DELETE a poll?");

	$bb_polls_label['only_topic_author_can_add']=__("Only the topic starter can add a poll?");
	$bb_polls_label['ask_during_new']=__("Ask for poll during new topic creation? (requires AJAX on)");

	$bb_polls_label['show_poll_on_which_pages']=__("Show poll only on which topic pages?");
		
	$bb_polls_label['add_within_hours']=__("How many hours later can a poll be ADDED?");
	$bb_polls_label['edit_within_hours']=__("How many hours later can a poll be EDITED?");

	$bb_polls_label['close_with_topic']=__("Should polls close when a topic is closed?");
	$bb_polls_label['close_after_days']=__("If not closed with topic, after how many days?");

	$bb_polls_label['max_options']=__("How many poll question slots should be offered?");
	$bb_polls_label['max_length']=__("How many characters can the poll questions be?");
	$bb_polls_label['options_sort']=__("Sort results by number of votes?");
	
	$bb_polls_label['use_ajax']=__("Use AJAX-like actions if javascript enabled?");
	$bb_polls_label['test_mode']=__("Enable TEST MODE (multiple votes per person)?");
	
	$bb_polls_label['use_icon']=__("Append icon to title when poll present?");
	$bb_polls_label['style']=__("Custom CSS style for polls:");	
	
	$bb_polls_label['poll_question']=__("Question to ask to start poll:");
	$bb_polls_label['poll_instructions']=__("Instructions to add poll:");
	$bb_polls_label['label_single']=__("Label for single vote selections:");
	$bb_polls_label['label_multiple']=__("Label for multiple vote selections:");
	$bb_polls_label['label_poll_text']=__("Text to show if a topic title has a poll:");
	$bb_polls_label['label_votes_text']=__("Text to show for votes:");
	$bb_polls_label['label_vote_text']=__("Text to show for VOTE button:");
	$bb_polls_label['label_save_text']=__("Text to show for SAVE button:");
	$bb_polls_label['label_cancel_text']=__("Text to show for CANCEL button:");
	$bb_polls_label['label_edit_text']=__("Text to show for EDIT button:");
	$bb_polls_label['label_delete_text']=__("Text to show for DELETE button:");
	$bb_polls_label['label_option_text']=__("Text to show for each option:");
	$bb_polls_label['label_question_text']=__("Text to show for question label:");
	$bb_polls_label['label_results_text']=__("Text to show for results label:");
	$bb_polls_label['label_now_text']=__("Text to show for VOTE NOW label:");
	$bb_polls_label['label_nocheck_text']=__("No selection warning:");
	$bb_polls_label['label_warning_text']=__("Delete warning:");

}	
}

?>