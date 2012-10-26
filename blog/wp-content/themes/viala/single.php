<?php get_header();?>
<?php if (have_posts()) : ?>
<div id="title" class="<?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>">
<div class="wrap">

	<?php while (have_posts()) : the_post(); ?>
	<div class="posttitle">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-info"><?php the_date(__('M jS, Y','ml')) ?> <?php _e('by','ml');?> <?php the_author_posts_link() ?> <?php edit_post_link(__('Edit','ml'), '&bull; ', ''); ?> </p>
	</div>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">
	<div class="duo toc">
		<div><!-- leave this for IE -->
	<?php $toc = get_post_meta($post->ID, "toc", $single = true);
	if($toc !== '') {
	echo $toc;
	} else {
        echo '<p class="postmetadata">';
	echo _e('Posted in','ml');
        echo '&nbsp;';
	echo the_category(', ');
	echo '</p><p class="postmetadata">';
	if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>');
	echo '</p>'; } ?>
		</div><!-- IE div -->
	</div>	<!-- duo/toc-->			
	<div class="post septet <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>" id="post-<?php the_ID(); ?>">
		<div class="entry">
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
		</div>
	<?php if($toc !== '') { ?>
		<p class="postmetadata">
            	   <?php if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>'); ?> </p>
		
		<p class="postmetadata"><?php _e('Posted in','ml');?> <?php the_category(', ') ?></p>
	<?php } ?>
	<div class="singlenav">
		<div class="alignleft"><?php previous_post_link('&laquo; %link') ?></div>
		<div class="alignright"><?php next_post_link('%link &raquo;') ?></div>
	</div>
		<?php comments_template(); ?>
	</div>
	<?php endwhile; ?>
	<?php else : include_once(TEMPLATEPATH.'/notfound.php');?>
 <?php endif; ?>

<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer();?>