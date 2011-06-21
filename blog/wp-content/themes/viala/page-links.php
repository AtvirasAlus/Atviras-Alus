<?php
/*
Template Name: Links
*/
?>
<?php get_header(); ?>
<div id="title">
<div class="wrap">
	<h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link:','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
	<p class="post-info"><?php edit_post_link(__('Edit','ml'), ' ', ''); ?></p>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">				
	<div class="nonet">
		<div class="entry">
			<ul>
				<?php wp_list_bookmarks('show_description=1&show_updated=1&before=<li class="archive"><h4>&between=<em>&after=</em></h4></li>');?>
			</ul>
		</div>
	</div><!-- post/nonet -->

<?php get_sidebar(); ?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer(); ?>