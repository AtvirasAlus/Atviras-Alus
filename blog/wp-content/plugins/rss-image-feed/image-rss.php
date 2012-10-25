<?php
/*
Plugin Name: RSS Image Feed 
Plugin URI: http://wasistlos.waldemarstoffel.com/plugins-fur-wordpress/image-feed
Description: RSS Image Feed is not literally producing a feed of images but it adds the first image of the post to the normal feeds of your blog. Those images display even in Firefox and even if you have the excerpt in the feed and not the content.
Version: 2.1
Author: Waldemar Stoffel
Author URI: http://www.waldemarstoffel.com
License: GPL3
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

if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die("Sorry, you don't have direct access to this page."); }


//Additional links on the plugin page

add_filter('plugin_row_meta', 'rif_register_links',10,2);

function rif_register_links($links, $file) {
	
	$base = plugin_basename(__FILE__);
	if ($file == $base) {
		$links[] = '<a href="plugins.php?page=set-feed-imgage-size">'.__('Settings','image-rss').'</a>';
		$links[] = '<a href="http://wordpress.org/extend/plugins/rss-image-feed/faq/" target="_blank">'.__('FAQ','image-rss').'</a>';
		$links[] = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=LLUFQDHG33XCE" target="_blank">'.__('Donate','image-rss').'</a>';
	}
	
	return $links;

}


/**
 *
 * import laguage files
 *
 */
load_plugin_textdomain('image-rss', false , basename(dirname(__FILE__)).'/languages');

/**
 *
 * init
 *
 */
add_action('admin_init', 'image_rss_init');

function image_rss_init() {
	
	register_setting( 'rss_options', 'rss_options', 'rif_validate' );
	
	add_settings_section('image_rss_setting', __('Image Settings', 'image-rss'), 'rif_display_section', 'new_image_size');
	
	add_settings_field('image_size', __('Imagesize:', 'image-rss'), 'rif_display_field', 'new_image_size', 'image_rss_setting');

}

function rif_display_section() {
	
	echo '<p>'.__('Give here only the longest side of the image. The smaller side will be counted on displaying the image. There will be no cropping.', 'image-rss').'</p>';

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
	
	?>
    
    <div>
    <h2>Feed Images</h2>
    
	<?php _e('Define the size of the images in your feed.', 'image-rss'); ?>
    
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
add_filter('the_content_rss', 'add_image_content');


function add_image_excerpt($output){
	
	if (!empty($output)) $output = get_feed_image().$output;
	
	else $output = get_feed_image();
	
	return $output;

}

function add_image_content($content){
	
	if (is_feed()) :
		
		$rif_text = get_the_content();
		
		$rif_text = strip_shortcodes($rif_text);
		
		$content = get_feed_image().$rif_text;
		
	endif;
		
	return $content;

}

// extracting the first image of the post

function get_feed_image() {
	
	$rss_options = get_option('rss_options');
	$irf_max = $rss_options['image_size'];
	
	$irf_thumb = '';
	$irf_content = get_the_content();
	$irf_content = do_shortcode($irf_content);
	$irf_image_title = get_the_title();
	
	$irf_thumb = preg_match_all('/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $irf_content, $matches);
	$irf_thumb = $matches [1] [0];
		
	if (!empty($irf_thumb))	:
	
		$irf_size=@getimagesize($irf_thumb);
		
		if (!empty($irf_size)) :
		
			if (($irf_size[0]/$irf_size[1])>1) :
									   
				$irf_x=$irf_max;
				$irf_y=intval($irf_size[1]/($irf_size[0]/$irf_x));
			
			else :
												   
				$irf_y=$irf_max;
				$irf_x=intval($irf_size[0]/($irf_size[1]/$irf_y));
				
			endif;
			
		endif;
		
		$irf_width_height = (!empty($irf_x)) ? ' width="'.$irf_x.'" height="'.$irf_y.'"' : ' width="'.$irf_x.'"';
		
		$irf_image='<a href="'.get_permalink().'"><img title="'.$irf_image_title.'" src="'.$irf_thumb.'" alt="'.$irf_image_title.'" '.$irf_width_height.' /></a>';
		$img_container='<div>'.$irf_image.'</div><br/>';
		return $img_container;
		
	endif;
	
}

?>