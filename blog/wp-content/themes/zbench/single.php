<?php get_header(); ?>
<div id="content">
	<?php the_post(); ?>
	<div <?php post_class('post-single'); ?> id="post-<?php the_ID(); ?>"><!-- post div -->
		<h2 class="title title-single"><?php the_title(); ?></h2>
		<div class="post-info-top">
			<span class="post-info-date"><?php _e('Posted by', 'zbench'); ?> <?php the_author(); ?> <?php _e('on', 'zbench'); ?> <?php the_time(get_option( 'date_format' )); ?> <?php edit_post_link(__('Edit','zbench'), '[', ']'); ?></span>
			<?php if (comments_open()) : ?>
			<span id="addcomment"><a href="#respond"  rel="nofollow" title="Leave a comment ?"><?php _e('Leave a comment', 'zbench'); ?></a><?php comments_number(' (0)', ' (1)', ' (%)'); ?></span>
			<span id="gotocomments"><a href="#comments"  rel="nofollow" title="Go to comments ?"><?php _e('Go to comments', 'zbench'); ?></a></span>
			<?php endif; ?>
		</div>
		<div class="clear"></div>
		<div class="entry">
			<?php the_content(); ?>
			<?php wp_link_pages( array( 'before' => '<div class="page_link"><strong>' . __( 'Pages:', 'zbench' ) . '</strong>' , 'after' => '</div>' ) ); ?>
		</div><!-- END entry -->
		<div class="add-info">
			<?php if(function_exists('st_related_posts')) { st_related_posts('title=<h3>'._e('Related Posts','zbench').'</h3>'); } ?>
		</div>
		<div class="post-info-bottom">
			<span class="post-info-category"><?php the_category(', '); ?></span><span class="post-info-tags"><?php the_tags('', ', ', ''); ?></span>
		</div>
		<div id="nav-below">
			<div class="nav-previous"><?php previous_post_link( '%link', '<span class="meta-nav">&larr;</span> %title' ); ?></div>
			<div class="nav-next"><?php next_post_link( '%link', '%title <span class="meta-nav">&rarr;</span>' ); ?></div>
		</div><!-- #nav-below -->					
	</div><!-- END post -->
	<?php comments_template( '', true ); ?>
</div><!--content-->
<?php get_sidebar(); ?>
<?php get_footer(); ?>