<?php
/**
 * Displays a bio and the posts for a given author. The posts can be shown either
 * as excerpts or full contents
 *
 * @package Suffusion
 * @subpackage Templates
 */

global $suffusion_unified_options;
foreach ($suffusion_unified_options as $id => $value) {
	$$id = $value;
}

get_header();
suffusion_query_posts();
?>

    <div id="main-col">
<?php suffusion_before_begin_content(); ?>
      <div id="content" class="hfeed">
<?php
if ($suf_author_excerpt == 'list') {
	get_template_part('layouts/layout-list');
}
else if ($suf_author_excerpt == 'tiles') {
	suffusion_after_begin_content();
	get_template_part('layouts/layout-tiles');
}
else {
	suffusion_after_begin_content();
	get_template_part('layouts/layout-blog');
}
?>
      </div><!-- content -->
    </div><!-- main col -->
<?php get_footer(); ?>