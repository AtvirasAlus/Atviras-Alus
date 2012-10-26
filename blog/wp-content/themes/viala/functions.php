<?php
// helper functions
  if ( function_exists('wp_list_bookmarks') ) //used to check WP 2.1 or not
    $numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_type='post' and post_status = 'publish'");
	else
    $numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts WHERE post_status = 'publish'");
  if (0 < $numposts) $numposts = number_format($numposts);
	$numcmnts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->comments WHERE comment_approved = '1'");
		if (0 < $numcmnts) $numcmnts = number_format($numcmnts);
// ----------------
if ( function_exists('register_sidebar') ) {
	register_sidebar(array(
		'name' => 'Sidebar',
		'before_widget' => '<li class="sidebox">',
		'after_widget' => '</li>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	));
	register_sidebar(array(
		'name' => 'Homepage Widgets',
		'before_widget' => '<div class="subpost">',
		'after_widget' => '</div>',
		'before_title' => '<h2>',
		'after_title' => '</h2>',
	)); }

if ( function_exists('unregister_sidebar_widget') )
	{
		unregister_sidebar_widget( __('Links') );
	}
	if ( function_exists('register_sidebar_widget') )
	{
		register_sidebar_widget(__('Links'), 'viala_ShowLinks');
	}
	if ( function_exists('register_sidebar_widget') )
	{
		register_sidebar_widget(__('About'), 'viala_ShowAbout');
	}
function viala_ShowAbout() {
	if (is_home() && $paged < 2) {?>
	
	<div class="subpost">
	<h2><?php _e('About','ml');?></h2>
	<p class="about">
	<img src="<?php bloginfo('stylesheet_directory');?>/img/profile.jpg" alt="<?php _e('Profile','ml');?>" /><br/>
	<strong><?php bloginfo('name');?></strong><br/><?php bloginfo('description');?><br/>
	<?php _e('There are','ml');?> <?php global $numposts;echo $numposts; ?> <?php _e('Posts and','ml');?> <?php global $numcmnts;echo $numcmnts;?> <?php _e('Comments so far.','ml');?>
	</p>
	</div>

	<?php } else { ?>

	<h2><?php _e('About','ml');?></h2>
	<p class="about">
	<img src="<?php bloginfo('stylesheet_directory');?>/img/profile.jpg" alt="<?php _e('Profile','ml');?>" /><br/>
	<strong><?php bloginfo('name');?></strong><br/><?php bloginfo('description');?><br/>
	<?php _e('There are','ml');?> <?php global $numposts;echo $numposts; ?> <?php _e('Posts and','ml');?> <?php global $numcmnts;echo $numcmnts;?> <?php _e('Comments so far.','ml');?>
	</p>
<?php 	}
}

function viala_ShowRecentPosts() {
	if (is_home() && $paged < 2) { ?>

	<div class="subpost"><h2><?php _e('Recent Posts','ml');?></h2>
	<ul><?php wp_get_archives('type=postbypost&limit=6');?></ul>
	</div>

	<?php } else { ?>

	<li class="sidebox"><h2><?php _e('Recent Posts','ml');?></h2>
	<ul><?php wp_get_archives('type=postbypost&limit=6');?></ul>
	</li>

<?php 	}
}

function viala_ShowLinks() {
	if (is_home() && $paged < 2) { ?>
<div class="subpost" id="sidelinks">
	<ul>
		<?php
			if(function_exists('wp_list_bookmarks'))
			{
				wp_list_bookmarks();
			}
			else
			{
				get_links_list('name');
			}
		?>
	</ul>
</div>
	
	<?php } else { ?>

<li class="sidebox" id="sidelinks">
	<ul>
		<?php
			if(function_exists('wp_list_bookmarks'))
			{
				wp_list_bookmarks();
			}
			else
			{
				get_links_list('name');
			}
		?>
	</ul>
</li>
<?php  	}
}

function viala_add_theme_page() {
	if ( $_GET['page'] == basename(__FILE__) ) {

	    // save settings
		if ( 'save' == $_REQUEST['action'] ) {

			update_option( 'viala_featureid', $_REQUEST[ 's_featureid' ] );
			update_option( 'viala_fullpost', $_REQUEST[ 's_fullpost' ] );
			update_option( 'viala_longpost', $_REQUEST[ 's_longpost' ] );
			update_option( 'viala_exlink', $_REQUEST[ 's_exlink' ] );
			update_option( 'viala_subs', $_REQUEST[ 's_subs' ] );
			update_option( 'viala_sortpages', $_REQUEST[ 's_sortpages' ] );
			if( isset( $_POST[ 'excludepages' ] ) ) { update_option( 'viala_excludepages', implode(',', $_POST['excludepages']) ); } else { delete_option( 'viala_excludepages' ); }
			// goto theme edit page
			header("Location: themes.php?page=functions.php&saved=true");
			die;

  		// reset settings
		} else if( 'reset' == $_REQUEST['action'] ) {

			delete_option( 'viala_featureid' );
			delete_option( 'viala_fullpost' );
			delete_option( 'viala_longpost' );
			delete_option( 'viala_exlink' );
			delete_option( 'viala_subs' );
			delete_option( 'viala_sortpages' );
			delete_option( 'viala_excludepages' );


			// goto theme edit page
			header("Location: themes.php?page=functions.php&reset=true");
			die;

		}
	}


    add_theme_page(__("Viala Options",'ml'), __("Viala Options",'ml'), 'edit_themes', basename(__FILE__), 'viala_theme_page');

}

function viala_theme_page() {

	// --------------------------
	// Viala theme page content
	// --------------------------

	if ( $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.__('Viala Theme: Settings saved.','ml').'</strong></p></div>';
	if ( $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.__('Viala Theme: Settings reset.','ml').'</strong></p></div>';

?>
<style>
	.wrap { border:#ccc 1px dashed;padding: .5em 1em 1em 1em; margin-top: 1em;}
	.block { margin:1em;padding:1em;line-height:1.4em;}
	table tr td {border:#ddd 1px solid;font-family:Verdana, Arial, Serif;font-size:0.9em;}
	h4 {font-size:1.3em;font-family: Palatino, Georgia, Times, serif;margin:0;padding:10px 0;}
	strong {font-family: Palatino, Georgia, Times, serif;font-size: 1.2em;}
	p.submit {border-top: none; border-bottom: 1px solid #ccc; padding: .5em 0 1.5em 0;}
</style>
<div class="wrap">

<h2 style="position: relative;">Viala 1.3 <span style="font-size: 14px; font-family: 'Lucida Grande', Arial, sans-serif; font-color: #3a3a3a; position: absolute; right: 0; top: 8px;"><?php _e('Theme Page:','ml');?> <a href="http://wordpress.org/extend/themes/viala">Viala</a> <?php _e('Designed & Coded by:','ml');?> <a href="http://design.davidgarlitz.com/" title="David Garlitz's website">David Garlitz</a></span> </h2>

<form method="post" style="margin-top: 7px;">


<!-- blog layout options -->
<fieldset class="options">
<legend><?php _e('Theme Settings','ml');?>: <?php _e('Change the way your blog looks and acts with the many blog settings below','ml');?></legend>

<table width="100%" cellspacing="5" cellpadding="10" class="editform">
<tr>
<td valign="top" colspan="2" style="border:0px;margin:0;padding:0;">
	<input type="hidden" name="action" value="save" />
	<?php ml_input( "save", "submit", "", __("Save Settings",'ml') );?>
</td>
</tr>
<tr valign="top">
<td align="left">
	<?php
	ml_heading(__("List Pages / Navigation",'ml'));
		global $wpdb;
		if (function_exists('wp_list_bookmarks')) //WP 2.1 or greater
			$results = $wpdb->get_results("SELECT ID, post_title from $wpdb->posts WHERE post_type='page' AND post_parent=0 ORDER BY post_title");
		else
			$results = $wpdb->get_results("SELECT ID, post_title from $wpdb->posts WHERE post_status='static' AND post_parent=0 ORDER BY post_title");

		$excludepages = explode(',', get_settings('viala_excludepages'));
		if($results) {
			_e('Exclude the Following Pages from the Top Navigation','ml');echo "<br/><br/>";
			foreach($results as $page)
      {
			  echo '<input type="checkbox" name="excludepages[]" value="' . $page->ID . '"';
        if(in_array($page->ID, $excludepages)==true) { echo ' checked="checked"'; }
				echo ' /> <a href="' . get_permalink($page->ID) . '">' . $page->post_title . '</a><br />';
			}
		}
		echo '<br/>';
		echo "<strong> ";_e('Sort the List Pages by','ml');echo " </strong><br/>";

		ml_input( "s_sortpages", "radio", __("Page Title ?",'ml'), "post_title", get_settings( 'viala_sortpages' ) );
		ml_input( "s_sortpages", "radio", __("Date ?",'ml'), "post_date", get_settings( 'viala_sortpages' ) );
		ml_input( "s_sortpages", "radio", __("Page Order ?",'ml'), "menu_order", get_settings( 'viala_sortpages' ) );
		_e("(Each Page can be given a page order number, from the wordpress admin, edit page area)",'ml');
		echo "<br/>";
?>
</td>
<td>
<?php
	ml_heading( __("Support for Feature Articles ",'ml') );
	_e("You may select a category to run as your 'feature article.' Posts from this category will remain at the top of the home page, while your other posts will be displayed below.",'ml');
	
?>
	<?php
		global $wpdb;
		$id = get_option('viala_featureid');
		$defaults = array(
			'show_option_all' => '', 'show_option_none' => '',
			'orderby' => 'ID', 'order' => 'ASC',
			'show_last_update' => 0, 'show_count' => 0,
			'hide_empty' => 1, 'child_of' => 0,
			'exclude' => '', 'echo' => 1,
			'selected' => 0, 'hierarchical' => 0,
			'name' => 'cat', 'class' => 'postform'
		);
		$r = wp_parse_args( $args, $defaults );
		extract( $r );

		$features_cats = get_categories($r);
	?>		<br/><br/>
			<select name="s_featureid" id="s_featureid">
				<option value="0"><?php _e('NOT SELECTED','ml');?></option>
				<?php
					foreach ($features_cats as $cat) {
					if ($id == $cat->cat_ID)
					{
						$sIsSelected = "selected='true'";
					}
					else
					{
						$sIsSelected = "";
					}
						echo '<option value="' . $cat->cat_ID . '"'. $sIsSelected. '>' . $cat->cat_name . '</option>';
				}?>
			</select><br/><br/>


<?php
	ml_heading( __("Feature Article Options",'ml') );
	ml_input( "s_fullpost", "checkbox", __("Display the full post instead of an excerpt (removes the 'Continue Reading' link)",'ml'), 1, get_settings( 'viala_fullpost' ) );
	ml_input( "s_longpost", "checkbox", __("Adjust the formatting to support longer feature articles (full posts or custom excerpts)",'ml'), 1, get_settings( 'viala_longpost' ) );
?>
<?php
	echo "<p>";_e('Change the text for the \'Continue Reading\' link :','ml');echo "</p>";
?>
	<?php
		global $wpdb;
		$ExLink = get_option('viala_exlink');
 		echo '<input type="text" name="s_exlink" value="'. $ExLink .'"/><br/><br/>';
	?>
			
<?php
	ml_heading( __("Number of Sub Articles.",'ml') );
	_e('Choose the number of articles to display under the feature article.','ml');
?>
	<?php
		global $wpdb;
		$subs = get_option('viala_subs');
		?>      <br/><br/>
			<select name="s_subs" id="s_subs">
				<option value="5" <?php if ($subs == '') { echo 'selected="true"';}else{ echo '';}?>>Default (4)</option>
				<option value="1" <?php if ($subs == '1') { echo 'selected="true"';}else{ echo '';}?>>0</option>
				<option value="2" <?php if ($subs == '2') { echo 'selected="true"';}else{ echo '';}?>>1</option>
				<option value="3" <?php if ($subs == '3') { echo 'selected="true"';}else{ echo '';}?>>2</option>
				<option value="4" <?php if ($subs == '4') { echo 'selected="true"';}else{ echo '';}?>>3</option>
				<option value="5" <?php if ($subs == '5') { echo 'selected="true"';}else{ echo '';}?>>4</option>
				<option value="6" <?php if ($subs == '6') { echo 'selected="true"';}else{ echo '';}?>>5</option>
				<option value="7" <?php if ($subs == '7') { echo 'selected="true"';}else{ echo '';}?>>6</option>
				<option value="8" <?php if ($subs == '8') { echo 'selected="true"';}else{ echo '';}?>>7</option>
				<option value="9" <?php if ($subs == '9') { echo 'selected="true"';}else{ echo '';}?>>8</option>
				<option value="10" <?php if ($subs == '10') { echo 'selected="true"';}else{ echo '';}?>>9</option>
				<option value="11" <?php if ($subs == '11') { echo 'selected="true"';}else{ echo '';}?>>10</option>
				<option value="12" <?php if ($subs == '12') { echo 'selected="true"';}else{ echo '';}?>>11</option>
				<option value="13" <?php if ($subs == '13') { echo 'selected="true"';}else{ echo '';}?>>12</option>
			</select>



</td>

</td>
</tr>
<tr>
<td valign="top" colspan="2" style="border:0px;margin:0;padding:0;">
	<input type="hidden" name="action" value="save" />
	<?php ml_input( "save", "submit", "", __("Save Settings",'ml') );?>
</td>
</tr>
</table>
</fieldset>
</form>

<form method="post">

<fieldset class="options">
<legend><strong><?php _e('Reset','ml');?>:</strong></legend>

<p><?php _e('If for some reason you want to uninstall Viala then press the reset button to clean things up in the database.','ml');?></p>
<p><?php _e('You have to make sure to delete the theme folder, if you want to completely remove the theme.','ml');?></p>
<?php

	ml_input( "reset", "submit", "", __("Reset Settings",'ml') );

?>

</div>
<input type="hidden" name="action" value="reset" />
</form>

<?php
}
add_action('admin_menu', 'viala_add_theme_page');


function ml_input( $var, $type, $description = "", $value = "", $selected="" ) {

	// ------------------------
	// add a form input control
	// ------------------------

 	echo "\n";

	switch( $type ){

	    case "text":

	 		echo "<input name=\"$var\" id=\"$var\" type=\"$type\" style=\"width: 60%\" class=\"textbox\" value=\"$value\" />";

			break;

		case "submit":

	 		echo "<p class=\"submit\"><input name=\"$var\" type=\"$type\" value=\"$value\" /></p>";

			break;

		case "option":

			if( $selected == $value ) { $extra = "selected=\"true\""; }

			echo "<option value=\"$value\" $extra >$description</option>";

		    break;
  		case "radio":

			if( $selected == $value ) { $extra = "checked=\"true\""; }

  			echo "<label><input name=\"$var\" id=\"$var\" type=\"$type\" value=\"$value\" $extra /> $description</label><br/>";

  			break;

		case "checkbox":

			if( $selected == $value ) { $extra = "checked=\"true\""; }

  			echo "<label for=\"$var\"><input name=\"$var\" id=\"$var\" type=\"$type\" value=\"$value\" $extra /> $description</label><br/>";

  			break;

		case "textarea":

		    echo "<textarea name=\"$var\" id=\"$var\" style=\"width: 80%; height: 10em;\" class=\"code\">$value</textarea>";

		    break;
	}

}

function ml_heading( $title ) {

	// ------------------
	// add a table header
	// ------------------

   echo "<h4>" .$title . "</h4>";

}
?>
<?php

define('HEADER_TEXTCOLOR', '');
define('HEADER_IMAGE', '%s/img/rooftop.jpg'); // %s is theme dir uri
define('HEADER_IMAGE_WIDTH', 400);
define('HEADER_IMAGE_HEIGHT', 300);
define( 'NO_HEADER_TEXT', true );

function viala_admin_header_style() {
?>
<style type="text/css">
#headimg {
	background: url(<?php header_image() ?>) no-repeat;
}
#headimg {
	height: <?php echo HEADER_IMAGE_HEIGHT; ?>px;
	width: <?php echo HEADER_IMAGE_WIDTH; ?>px;
}

#headimg h1, #headimg #desc {
	display: none;
}
</style>
<?php
}
function viala_header_style() {
?>
<style type="text/css">
#headerimage {
	background: url(<?php header_image() ?>) no-repeat;
}
</style>
<?php
}
if ( function_exists('add_custom_image_header') ) {
	add_custom_image_header('viala_header_style', 'viala_admin_header_style');
}

load_theme_textdomain('ml');
?>