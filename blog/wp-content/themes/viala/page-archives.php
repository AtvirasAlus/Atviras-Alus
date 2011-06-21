<?php
/*
Template Name: Archives Page
*/
?>
<?php get_header();?>
<div id="title">
<div class="wrap">
	<h2 class="post-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link:','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
	<p class="post-info"><?php edit_post_link(__('Edit','ml'), ' ', ''); ?></p>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">				
	<div class="duo">
		<div class="entry">
		 <div class="sidebox">
		 <h2><?php _e('by Categories'); ?></h2>
		 <ul>
			<?php wp_list_cats('optioncount=1');    ?>
		 </ul>
		 </div>

		 <div class="sidebox">
		 <h2><?php _e('by Month','ml'); ?></h2>
		 <ul><?php wp_get_archives('type=monthly'); ?></ul>
		 </div>

		 <div class="sidebox">
          <?php if (function_exists('wp_tag_cloud')) {	?>
          	<h2><?php _e('Tags','ml'); ?></h2>
          	<p><?php wp_tag_cloud(); ?></p>
          <?php } ?>
		 </div>

		</div><!-- entry -->
	</div><!-- duo -->
	<div class="septet">
		<div class="entry">
		<h2><?php _e('Last 50 Entries','ml');?></h2>
		<ul>
			<?php $posts = query_posts('showposts=50');?>
			<?php if ($posts) : foreach ($posts as $post) : the_post(); ?>
			<li class="archive <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>"><h4><em><?php the_date(__('d M Y','ml')); ?></em><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link:','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h4></li>
			<?php endforeach; else: ?>
			<p><?php _e('Sorry, no posts matched your criteria.','ml'); ?></p>
			
			<?php endif; ?>
			<?php wp_reset_query(); ?>
		</ul>
		</div>
	</div><!-- septet -->

<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer();?>