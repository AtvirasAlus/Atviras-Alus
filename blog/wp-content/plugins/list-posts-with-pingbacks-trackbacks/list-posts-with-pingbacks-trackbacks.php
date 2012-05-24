<?php
/*
Plugin Name: List Posts with Pingbacks and Tracks 
Plugin URI: http://thisismyurl.com/downloads/wordpress/plugins/list-posts-with-pingbacks-and-tracks/
Description: This function is designed to allow you to add a list of popular posts to your website theme based on which posts have pingback and trackbacks.
Author: Christopher Ross
Author URI: http://christopherross.ca
Version: 2.0.0
*/

/*  Copyright 2011  Christopher Ross  (email : info@thisismyurl.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function thisismyurl_listpostswithpingbacks($options='') {
	$ns_options = array(
                    "count" => "10",
                    "before"  => "<li>",
                    "after" => "</li>",
					"show" => true,
					"type" => "both",
					"link" => true,
					"order" => "desc",
					"nofollow" => true,
					"minpr" => "0",
					"format" => "#post# - #link#",
                   );

	$options = explode("&",$options);
	
	foreach ($options as $option) {
	
		$parts = explode("=",$option);
		$ns_options[$parts[0]] =  $parts[1];
	}

	if(strtolower($ns_options['order']) == "desc") {$sqlorder = "ORDER BY comment_date_gmt DESC";}
	if(strtolower($ns_options['order']) == "asc") {$sqlorder = "ORDER BY comment_date_gmt ASC";}
	if(strtolower($ns_options['order']) == "rand") {$sqlorder = "ORDER BY RAND()";}


	if(strtolower($ns_options['type']) == "pingback") {$type = "`comment_type` LIKE '%ping%'";}
	if(strtolower($ns_options['type']) == "trackback") {$type = "`comment_type` LIKE '%track%'";}
	if(strtolower($ns_options['type']) == "both") {$type = "`comment_type` LIKE '%ping%' OR `comment_type` LIKE '%track%'";}

	global $wpdb;  
	$sql = "SELECT *  FROM $wpdb->comments WHERE (".$type.") AND `comment_author_url` NOT LIKE '%".$_SERVER['SERVER_NAME']."%' AND `comment_author_url` != '' ".$sqlorder." LIMIT 0,".($ns_options['count']*5);	
	
	$comments = $wpdb->get_results($sql);

    foreach ($comments as $comment) {  
	
		unset($link);
		unset($url);
		$url = strtolower($comment->comment_author_url);
		$url = str_replace("http:","",$url);
		$url = str_replace("https:","",$url);
		$url = str_replace("www.","",$url);
		$url = str_replace("//","",$url);
		$urlset = explode("/",$url);
		$url = $urlset[0];

		if ( $count < $ns_options['count']) {
			
			$final .= $ns_options['before'].$ns_options['format'].$ns_options['after'];

			if ($ns_options['link']) 	{$link .= "<a href='".$comment->comment_author_url."' title='".$url."'";}
			if ($ns_options['nofollow'] == true) {$link .= " rel='nofollow' ";}
			if ($ns_options['link']) {$link .= ">";}
			$link .= $url;
			if ($ns_options['link']) {$link .= "</a>\n\n";}
	
			$posts = $wpdb->get_results("SELECT ID, post_title,guid FROM $wpdb->posts WHERE ID=".$comment->comment_post_ID);  
			
			foreach ($posts as $mypost) {  
				$post = "<a href='".$mypost->guid."' title='".$mypost->post_title."'>".$mypost->post_title."</a>";
			}
			
			$final = str_replace("#link#",$link,$final);
			$final = str_replace("#post#",$post,$final);

			$count++;
		}
	}
	if ($ns_options['show']==1) {echo $final;} else {return $final;}

}

class thisismyurl_listpostswithpingbacks_widget extends WP_Widget
{
	/* Declares the thisismyurl_listpostswithpingbacks_widget class. */
	function thisismyurl_listpostswithpingbacks_widget(){
		$widget_ops = array('classname' => 'widget_thisismyurl_listpostswithpingbacks', 'description' => __( "List Posts with Pingbacks by Christopher Ross") );
		$this->WP_Widget('thisismyurl_listpostswithpingbacks_widget', __('List Posts with Pingbacks'), $widget_ops, $control_ops);
	}

	/*  Displays the Widget */
	function widget($args, $instance){
		extract( $args );

		# Before the widget
		echo $before_widget;
		echo '<h4 class="widgettitle">Posts with Pingbacks</h4>';
		echo '<ul>'.thisismyurl_listpostswithpingbacks().'</ul>';
		echo $after_widget;

	}

}// END class

function thisismyurl_listpostswithpingbacks_widget_Init() {
	register_widget('thisismyurl_listpostswithpingbacks_widget');
}
add_action('widgets_init', 'thisismyurl_listpostswithpingbacks_widget_Init');

?>