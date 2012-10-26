<?php get_header();?>
<?php if (have_posts()) : ?>
<div id="title">
<div class="wrap">
	<?php $post = $posts[0]; // Hack. Set $post so that the_date() works. ?>

		<?php /* If this is a category archive */ if (is_category()) { ?>
		<h2 class="pagetitle"><?php _e('Category Archive for','ml');?> '<?php echo single_cat_title(); ?>'</h2>

		<?php /* If this is a Tag archive */ } elseif (function_exists('is_tag')&& is_tag()) { ?>
		<h2 class="pagetitle"><?php _e('Tag Archive for','ml');?> '<?php echo single_tag_title(); ?>'</h2>

 		<?php /* If this is a daily archive */ } elseif (is_day()) { ?>
		<h2 class="pagetitle"><?php _e('Daily Archive for','ml');?> <?php the_time('F jS, Y'); ?></h2>

		<?php /* If this is a monthly archive */ } elseif (is_month()) { ?>
		<h2 class="pagetitle"><?php _e('Monthly Archive for','ml');?> <?php single_month_title(' '); ?></h2>

		<?php /* If this is a yearly archive */ } elseif (is_year()) { ?>
		<h2 class="pagetitle"><?php _e('Yearly Archive for','ml');?> <?php wp_title(' '); ?></h2>
		<?php /* If this is a paged archive */ } elseif (isset($_GET['paged']) && !empty($_GET['paged'])) { ?>
		<h2 class="pagetitle"><?php _e('Blog Archives','ml');?></h2>

		<?php } ?>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">				
	<div class="nonetWide">
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

	<?php else : include_once(TEMPLATEPATH.'/notfound.php');?>
	<?php endif; ?>
	</div><!-- nonet -->
	<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer();?>