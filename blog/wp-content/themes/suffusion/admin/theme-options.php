<?php
/**
 * Lists out all the options in "Suffusion Theme Options".
 * This file is included in functions.php
 *
 * @package Suffusion
 * @subpackage Admin
 */

global $suffusion_inbuilt_options, $suffusion_intro_options, $suffusion_theme_skinning_options, $suffusion_visual_effects_options, $suffusion_sidebars_and_widgets_options, $suffusion_blog_features_options, $suffusion_templates_options;
include_once(TEMPLATEPATH . "/admin/theme-options-intro.php");
include_once(TEMPLATEPATH . "/admin/theme-options-theme-skinning.php");
include_once(TEMPLATEPATH . "/admin/theme-options-visual-effects.php");
include_once(TEMPLATEPATH . "/admin/theme-options-sidebars-and-widgets.php");
include_once(TEMPLATEPATH . "/admin/theme-options-blog-features.php");
include_once(TEMPLATEPATH . "/admin/theme-options-templates.php");
include_once(TEMPLATEPATH . "/admin/theme-options-custom-types.php");

$suffusion_inbuilt_options = array();
foreach ($suffusion_intro_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_theme_skinning_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_visual_effects_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_sidebars_and_widgets_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_blog_features_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_templates_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}
foreach ($suffusion_custom_types_options as $option) {
	$suffusion_inbuilt_options[] = $option;
}

function suffusion_load_module($option_file, $option_array_name) {
	global $suffusion_inbuilt_options;
	include_once(TEMPLATEPATH.$option_file);
	$option_array = $$option_array_name;
	foreach ($option_array as $option) {
		$suffusion_inbuilt_options[] = $option;
	}
}
?>