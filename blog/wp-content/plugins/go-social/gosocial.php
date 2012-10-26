<?php 
/*
Plugin Name: goSocial
Plugin URI: http://wizcrew.com/go-social-wordpress-plugin.htm
Description: goSocial plugin is useful to share your post among various Social Media Sharing/Bookmarking websites including Facebook, Twitter, Digg, Google Buzz & more to come.
Version: 1.0
Author: Wizcrew Technologies
Author URI: http://wizcrew.com/
*/
if(!get_option('gosocial_option')){
	
	$options = array(
			'twitter_name'=>'indiascanner',
			'twitter_name_alt'=>'ihelpstudy',
			'position'=>1,
			'twitter'=>1,
			'facebook'=>1,
			'buzz'=>1,
			'digg'=>1
			);
	update_option( 'gosocial_option', $options );
}


function go_social($content){
	$options = get_option('gosocial_option');//get the options from the datebase
	$position = $options['position'];
	$pre = '<div class="faceandtweet">';
	$suff = '</div>';
	$social = '';
	
	//twitter like
	$twitter = '<div class="faceandtweet_retweet" style="float:left; width:110px;"><a href="http://twitter.com/share?url='.get_permalink( ).'" class="twitter-share-button" data-text="'.the_title("","",false ).'" data-count="horizontal" data-via="'.$options['twitter_name'].'" data-related="'.$options['twitter_name_alt'].'">Tweet</a><script type="text/javascript" src="http://platform.twitter.com/widgets.js"></script></div>';

	
	//facebook like
	$facebook = '<div class="faceandtweet_like" style="float:left; width:90px; height:20px;"><iframe src="http://www.facebook.com/plugins/like.php?href='.get_permalink().'&amp;layout=button_count&amp;width=90&amp;show_faces=false&amp;action=like&amp;colorscheme=light&amp;height=20" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:90px; height:20px;" allowTransparency="true"></iframe></div>';

	
	// google buzz
	$buzz = '<div class="faceandtweet_retweet" style="float:left; width:110px;"><a title="" class="google-buzz-button" href="http://www.google.com/buzz/post" data-button-style="small-count"></a><script type="text/javascript" src="http://www.google.com/buzz/api/button.js"></script></div>';

	
	// digg.com
	$digg='<div class="faceandtweet_retweet" style="float:left; width:110px;"><a class="DiggThisButton DiggCompact" href="http://digg.com/submit?url='.get_permalink().'&amp;title='.the_title('','',false).'"></a><script type="text/javascript" src="http://widgets.digg.com/buttons.js"></script></div><div style="clear:both;"></div>';

	if($options['twitter'] == 1) // if twitter selectd
		$social = $social.$twitter;
	if($options['facebook'] == 1)// if facebook selected
		$social = $social.$facebook;
	if($options['buzz'] == 1)//if buzz selected
		$social = $social.$buzz;	
	if($options['digg'] == 1)//if digg selected
		$social = $social.$digg;
	
	$html = $pre.$social.$suff;

	if($position == 1){
		return(is_single()?$html.$content:$content);//show after the title
	}
	elseif($position == 2){
		return(is_single()?$content.$html:$content);//after the post content
	}elseif($position == 3){
		return(is_single()?$html.$content:$content);//show above the title
	}

}

add_filter('the_content','go_social');

function gosocial_admin(){
	
 if (!current_user_can('manage_options'))  {
    wp_die( __('You do not have sufficient permissions to access this pagesd.') );
  }
	if ( isset($_POST['gosocial_submit'] ) ) {
		$options = array();
		$options['facebook'] = ($_POST['facebook'] == "on")?1:0;
		$options['twitter'] = ($_POST['twitter'] == "on")?1:0;
		$options['buzz'] = ($_POST['buzz'] == "on")?1:0;
		$options['digg'] = ($_POST['digg'] == "on")?1:0;
		$options['twitter_name'] = $_POST['twitter_name'];
		$options['twitter_name_alt'] = $_POST['twitter_name_alt'];
		$options['position'] = $_POST['position'];
		
		update_option( 'gosocial_option', $options );// update the options
		echo '<div class="updated fade"><p><strong>'. __('Options saved.') .'</strong></p></div>';
	}
		
	$options = get_option('gosocial_option');//get the options from the datebase
	$position = $options['position'];
	$twitter = ($options['twitter'] == 1 )?'checked="checked"' : '';
	$facebook = ( $options['facebook'] == 1 )?'checked="checked"' : '';
	$buzz = ( $options['buzz'] == 1 )?'checked="checked"' : '';
	$digg = ( $options['digg'] == 1 )?'checked="checked"' : '';
	
	echo '<div class="wrap">';
	echo '<h2>' . __('Go Social Plugins  Options') . '</h2>';
	echo '<form method="post" action="">';
	echo '<fieldset>';
	echo '<p><input type="text" id="twitter_name" name="twitter_name" value="'.$options['twitter_name'].'" /> <label for="twitter_name">'. __("Twitter Name <i>Your Blog/Website's Twitter Account</i>") .'</label></p>';
	echo '<p><input type="text" id="twitter_name_alt" name="twitter_name_alt" value="'.$options['twitter_name_alt'].'" /> <label for="twitter_name_alt">'. __("Another Twitter UserName <i>Twitter username of any other blog/website/friend, whom you want to suggest people to follow</i>") .'</label></p>';
	echo '<p> Postion of go social </p>';
	echo '<p><select name="position" id="position">
		<option value="1"'.(( $position == 1 ) ? ' selected="selected"' : '').'> below title</option>
		<option value="2"'. (( $position == 2 ) ? ' selected="selected"' : '').'> after post content</option>
		</select></p>';
	echo '<h4>Select Social Media Sites Which you Wish to Include</h4><p><input type="checkbox" id="twitter" name="twitter" '. $twitter.' /> <label for="twitter">'. __("Twitter") .'</label></p>';	
	echo '<p><input type="checkbox" id="facebook" name="facebook" '. $facebook.' /> <label for="Facebook">'. __("facebook") .'</label></p>';	
	echo '<p><input type="checkbox" id="buzz" name="buzz" '. $buzz.' /> <label for="buzz">'. __("Goggle Buzz") .'</label></p>';	
	echo '<p><input type="checkbox" id="digg" name="digg" '. $digg.' /> <label for="digg">'. __("Digg") .'</label></p>';	
	echo '<p class="submit">';
	echo '<input type="submit" name="gosocial_submit" value="Save settings" />';
	echo '</fieldset>';
	echo '</form>';
	echo '</div>';


}

function admin_config($admin){ // add option page for admin to configure the plugins
add_options_page('Go Social', 'Go Social', 8, basename(__FILE__), 'gosocial_admin'); 
}
add_action('admin_menu', 'admin_config'); 