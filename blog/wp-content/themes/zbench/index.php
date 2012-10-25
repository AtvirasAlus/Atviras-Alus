<?php get_header(); $options = get_option('zBench_options'); ?>
<div id="content">
	<?php if (have_posts()) : while (have_posts()) : the_post();?>
	<div <?php post_class('post'); ?> id="post-<?php the_ID(); ?>"><!-- post div -->
		<h2 class="title<?php if(is_sticky()) {echo " sticky-h2";} ?>"><a href="<?php the_permalink(); ?>" title="<?php printf( esc_attr__('Permalink to %s', 'zbench'), the_title_attribute('echo=0') ); ?>"><?php the_title(); ?></a></h2>
		<div class="post-info-top">
			<span class="post-info-date">
				<?php _e('Posted by', 'zbench'); ?> <?php the_author(); ?>
				<?php _e('on', 'zbench'); ?> <a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( get_the_time() ); ?>" rel="bookmark"><?php the_time(get_option( 'date_format' )); ?></a>
				<?php edit_post_link(__('Edit','zbench'), '[', ']'); ?>
			</span>
			<span id="gotocomments"><?php comments_popup_link(__('No comments', 'zbench'), '1 '.__('comment', 'zbench'), '% '.__('comments', 'zbench')); ?><?php if(function_exists('the_views')) { echo " | ";the_views(); } ?></span>
		</div>
		<div class="clear"></div>
		<div class="entry">
			<?php if ( $options['excerpt_check']=='true' ) { the_excerpt(__('Read more &raquo;','zbench')); } else { the_content(__('Read more &raquo;','zbench')); } ?>
		</div><!-- END entry -->
		<?php if(is_sticky()) { ?>
			<div class="entry"><p><?php _e('This is a sticky post!', 'zbench'); ?> <a href="<?php the_permalink(); ?>" class="more-links"><?php _e('continue reading?', 'zbench'); ?></a></p></div>
		<?php } ?>
	</div><!-- END post -->
	<?php endwhile; else: ?>
	<div class="post post-single">
		<h2 class="title title-single"><?php _e('Error 404 - Not Found', 'zbench'); ?></h2>
		<div class="post-info-top" style="height:1px;"></div>
		<div class="entry">
			<p><?php _e('Sorry, but you are looking for something that isn&#8217;t here.', 'zbench'); ?></p>
			<h3><?php _e('Random Posts', 'zbench'); ?></h3>
			<ul>
				<?php
					$rand_posts = get_posts('numberposts=5&orderby=rand');
					foreach( $rand_posts as $post ) :
				?>
				<li><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
				<?php endforeach; ?>
			</ul>
			<h3><?php _e('Tag Cloud', 'zbench'); ?></h3>
			<?php wp_tag_cloud('smallest=9&largest=22&unit=pt&number=200&format=flat&orderby=name&order=ASC');?>
		</div><!--entry-->
	</div><!--post-->
	<?php endif; ?>
<?php
if(function_exists('wp_page_numbers')) {
	wp_page_numbers();
}
elseif(function_exists('wp_pagenavi')) {
	wp_pagenavi();
} else {
	global $wp_query;
	$total_pages = $wp_query->max_num_pages;
	if ( $total_pages > 1 ) {
		echo '<div id="pagination">';
			posts_nav_link(' | ', __('&laquo; Previous page','zbench'), __('Next page &raquo;','zbench'));
		echo '</div>';
	}
}
?>
</div><!--content-->
<?php get_sidebar(); ?>
<?php get_footer(); ?>