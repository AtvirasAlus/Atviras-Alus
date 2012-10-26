<?php get_header();?>
<?php if (have_posts()) : ?>
		<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>

<div id="title">
<div class="wrap">
	<h2 class="pagetitle"><?php _e('Search Results for','ml');?> <?php echo "'".$s."'";?></h2>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">
  <div class="posts nonetWide">				
	<?php while (have_posts()) : the_post(); ?>

	<div class="subpost <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>" id="post-<?php the_ID(); ?>">
		<div class="posttitle">
			<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-info"><?php the_date(__('M jS, Y','ml')) ?> <!---<?php _e('by','ml');?> <?php the_author_posts_link() ?>---> <?php edit_post_link(__('Edit','ml'), ' &bull; ', ''); ?> </p>
		</div>

		<div class="entry">
			<?php the_excerpt(); ?>
		</div><!-- entry -->
	<p class="excerptlink"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Continue Reading &raquo;','ml');?> <?php the_title(); ?>"><?php _e('Continue Reading &raquo;','ml');?></a></p>
	<?php wp_link_pages(); ?>
	<p class="postmetadata"><?php if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>'); ?> </p>

	<p class="postmetadata"><?php _e('Posted in','ml');?> <?php the_category(', ') ?> &bull; <?php comments_popup_link(__('No Comments &#187;','ml'), __('1 Comment &#187;','ml'), __('% Comments &#187;','ml'),'',__('Comments Off','ml')); ?></p>
				<?php comments_template(); ?>
</div><!-- subpost -->
	<?php endwhile; ?>

	<div id="postnav">
	<p class="archnav" align="center"><?php posts_nav_link(' - ',__('&#171; Newer Posts','ml'),__('Older Posts &#187;','ml')) ?></p>
	</div>	

	<?php else : include_once(TEMPLATEPATH.'/nosearch.php');?>
	<?php endif; ?>
  </div><!-- posts/nonet -->

	<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer();?>