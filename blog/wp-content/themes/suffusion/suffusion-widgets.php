<?php
/**
 * Loads up all the widgets defined by Suffusion. Note that this function will not work for versions of WordPress 2.7 or lower
 *
 * @package Suffusion
 * @subpackage Widgets
 */

include_once (TEMPLATEPATH . '/widgets/suffusion-search.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-meta.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-twitter.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-category-posts.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-featured-posts.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-translator.php');
include_once (TEMPLATEPATH . '/widgets/suffusion-subscription.php');

add_action("widgets_init", "load_suffusion_widgets");

function load_suffusion_widgets() {
	register_widget("Suffusion_Search");
	register_widget("Suffusion_Meta");
	register_widget("Suffusion_Follow_Twitter");
	register_widget("Suffusion_Category_Posts");
	register_widget("Suffusion_Featured_Posts");
	register_widget("Suffusion_Google_Translator");
	register_widget("Suffusion_Subscription");
}
?>