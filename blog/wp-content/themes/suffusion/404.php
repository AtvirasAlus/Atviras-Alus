<?php
/**
 * Invoked when no matches are found
 *
 * @package Suffusion
 * @subpackage Templates
 */

global $suffusion_unified_options, $suffusion_404_title, $suffusion_404_content;
foreach ($suffusion_unified_options as $id => $value) {
	$$id = $value;
}

get_header();
?>
    <div id="main-col">
  	<div id="content">

    <div class="post">
	<h2>
<?php
if (trim($suf_404_title) == '') {
	echo $suffusion_404_title;
}
else {
	$title = stripslashes($suf_404_title);
	$title = do_shortcode($title);
	echo $title;
}
?></h2>

		<div class="entry">
		<p>
<?php
if (trim($suf_404_content) == '') {
	echo $suffusion_404_content;
}
else {
	$content = stripslashes($suf_404_content);
	$content = wp_specialchars_decode($content, ENT_QUOTES);
	$content = do_shortcode($content);
	echo $content;
}
?>
		</p>
		</div><!--/entry -->

		</div><!--/post -->
      </div><!-- /content -->
    </div><!-- main col -->
<?php get_footer(); ?>
