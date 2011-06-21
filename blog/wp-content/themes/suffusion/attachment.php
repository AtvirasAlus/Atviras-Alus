<?php
get_header();
?>
<div id="main-col">
	<div id="content">
<?php
global $post;
if (have_posts()) {
	while (have_posts()) {
		the_post();
		$original_post = $post;
?>
	<div <?php post_class(array('post', 'fix'));?> id="post-<?php the_ID(); ?>">
<?php suffusion_after_begin_post(); ?>
		<div class="entry-container fix">
			<div class="entry fix">
<?php
	suffusion_attachment();
	suffusion_content();
?>
			</div><!--/entry -->
<?php
		// Due to the inclusion of Ad Hoc Widgets the global variable $post might have got changed. We will reset it to the original value.
		$post = $original_post;
		suffusion_after_content();
?>
		</div><!-- .entry-container -->
		<?php suffusion_before_end_post(); ?>

		<?php comments_template(); ?>
	</div><!--/post -->
<?php
	}
}
else {
?>
        <div class="post fix">
		<p><?php _e('Sorry, no posts matched your criteria.', 'suffusion'); ?></p>
        </div><!--post -->

<?php
}
?>
	</div><!-- content -->
</div><!-- main col -->
<?php
get_footer();
?>