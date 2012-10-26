<?php
/*
Plugin Name: RSS Image Feed 
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/image-feed
Description: RSS Image Feed is not literally producing a feed of images but it adds the first image of the post to the normal feeds of your blog. Those images display even in Firefox and even if you have the excerpt in the feed and not the content.
Version: 2.2
Author: Waldemar Stoffel
Author URI: http://www.waldemarstoffel.com
License: GPL3
Text Domain: image-rss
*/

/*  Copyright 2011  Waldemar Stoffel  (email : stoffel@atelier-fuenf.de)

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/


/* Stop direct call */

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) die('Sorry, you don&#39;t have direct access to this page.');

define( 'RIF_PATH', plugin_dir_path(__FILE__) );

if (!class_exists('A5_Thumbnail')) require_once RIF_PATH.'class-lib/A5_ImageClasses.php';


//Additional links on the plugin page

add_filter('plugin_row_meta', 'rif_register_links',10,2);

function rif_register_links($links, $file) {
	
	global $rif_language_file;
	
	$base = plugin_basename(__FILE__);
	
	if ($file == $base) :
		$links[] = '<a href="http://wordpress.org/extend/plugins/rss-image-feed/faq/" target="_blank">'.__('FAQ', $rif_language_file).'</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LLUFQDHG33XCE" target="_blank">'.__('Donate', $rif_language_file).'</a>';
	
	endif;
	
	return $links;

}

add_filter( 'plugin_action_links', 'rif_plugin_action_links', 10, 2 );

function rif_plugin_action_links( $links, $file ) {
	
	global $rif_language_file;
	
	$base = plugin_basename(__FILE__);
	
	if ($file == $base) array_unshift($links, '<a href="'.admin_url('plugins.php?page=set-feed-imgage-size').'">'.__('Settings', $rif_language_file).'</a>');

	return $links;

}


/**
 *
 * import laguage files
 *
 */
$rif_language_file = 'image-rss'; 

load_plugin_textdomain($rif_language_file, false , basename(dirname(__FILE__)).'/languages');

/**
 *
 * init
 *
 */
add_action('admin_init', 'image_rss_init');

function image_rss_init() {
	
	global $rif_language_file;
	
	register_setting( 'rss_options', 'rss_options', 'rif_validate' );
	
	add_settings_section('image_rss_setting', __('Image Settings', $rif_language_file), 'rif_display_section', 'new_image_size');
	
	add_settings_field('image_size', __('Imagesize:', $rif_language_file), 'rif_display_field', 'new_image_size', 'image_rss_setting');

}

function rif_display_section() {
	
	global $rif_language_file;
	
	echo '<p>'.__('Give here only the longest side of the image. The smaller side will be counted on displaying the image. There will be no cropping.', $rif_language_file).'</p>';

}

function rif_display_field() {
	
	$rss_options = get_option('rss_options');
	
	echo "<input id='image_size' name='rss_options[image_size]' size='6' type='text' value='{$rss_options['image_size']}' />";
	
}

// Setting the default size of the image to 200

register_activation_hook(  __FILE__, 'rif_set_option' );

function rif_set_option() {
	
	$rss_options['image_size']=200;
	
	add_option('rss_options', $rss_options);
	
}

// Deleting the option

register_deactivation_hook(  __FILE__, 'rif_unset_option' );

function rif_unset_option() {
	
	delete_option('rss_options');
	
}

// Installing options page

add_action('admin_menu', 'rif_admin_menu');

function rif_admin_menu() {
	
	add_plugins_page('RSS Image Feed', 'RSS Image Feed', 'administrator', 'set-feed-imgage-size', 'rif_options_page');
	
}

// Calling the options page

function rif_options_page() {
	
	global $rif_language_file;
	
	?>
    
    <div>
    <h2>Feed Images</h2>
    <?php settings_errors(); ?>
	<?php _e('Define the size of the images in your feed.', $rif_language_file); ?>
    
    <form action="options.php" method="post">
	
	<?php settings_fields('rss_options'); ?>
	<?php do_settings_sections('new_image_size'); ?>
    
    <input name="Submit" type="submit" value="<?php esc_attr_e('Save Changes'); ?>" />
    </form></div>
	
	<?php
}

function rif_validate($input) {
	
	$newinput['image_size'] = trim($input['image_size']);
	
	$rss_options = get_option('rss_options');
	
	if(!is_numeric($newinput['image_size']) || strlen($newinput['image_size']) > 4) $newinput['image_size'] = $rss_options['image_size'];

	return $newinput;

}

/* hooking into the feed for content and excerpt */

add_filter('the_excerpt_rss', 'add_image_excerpt');
add_filter('the_content_feed', 'add_image_content');


function add_image_excerpt($output){
	
	if (!empty($output)) $output = get_feed_image().$output;
	
	else $output = get_feed_image();
	
	return $output;

}

function add_image_content($content){
	
	$rif_text = strip_shortcodes(get_the_content());
		
	$content = get_feed_image().$rif_text;
		
	return $content;

}

// extracting the first image of the post

function get_feed_image() {
	
	$rss_options = get_option('rss_options');
	$rif_max = $rss_options['image_size'];
	
	global $rif_language_file, $post;
	
	$img_container = '';
	
	$imagetags = new A5_ImageTags;

	$rif_tags = $imagetags->get_tags($post, $rif_language_file);

	$rif_image_alt = $rif_tags['image_alt'];
	$rif_image_title = $rif_tags['image_title'];
	$rif_title_tag = $rif_tags['title_tag'];
	
	$args = array (
	'content' => get_the_content(),
	'width' => $rif_max,
	'height' => $rif_max
	);
	   
	$rif_image = new A5_Thumbnail;

	$rif_image_info = $rif_image->get_thumbnail($args);
	
	$rif_thumb = $rif_image_info['thumb'];
	
	$rif_width = $rif_image_info['thumb_width'];

	$rif_height = $rif_image_info['thumb_height'];
	
	if ($rif_thumb) :
	
		$eol = "\r\n";
		$tab = "\t\t";
	
		if ($rif_width) $rif_img_tag = '<a href="'.get_permalink().'" title="'.$rif_image_title.'"><img title="'.$rif_image_title.'" src="'.$rif_thumb.'" alt="'.$rif_image_alt.'" width="'.$rif_width.'" height="'.$rif_height.'" /></a>';
			
		else $rif_img_tag = '<a href="'.get_permalink().'" title="'.$rif_image_title.'"><img title="'.$rif_image_title.'" src="'.$rif_thumb.'" alt="'.$rif_image_alt.'" style="maxwidth: '.$rif_max.'; maxheight: '.$rif_max.';" /></a>';
		
		$img_container=$eol.$tab.'<div>'.$eol.$tab.$rif_img_tag.$eol.$tab.'</div>'.$eol.$tab.'<br/>'.$eol.$tab;
		
	endif;
	
	return $img_container;
	
}

?>