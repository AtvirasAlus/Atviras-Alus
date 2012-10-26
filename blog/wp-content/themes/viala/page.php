<?php get_header();?>
<?php if (have_posts()) : ?>
<div id="title">
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
	<div class="duo">
		<div class="entry"><!-- leave this for IE -->
		<?php if($post->post_parent) {
			$brothers = wp_list_pages("title_li=&child_of=".$post->post_parent."&echo=0"); 
			$parent = wp_list_pages("title_li=&include=".$post->post_parent."&echo=0"); 
			} else {
			$children = wp_list_pages("title_li=&child_of=".$post->ID."&echo=0");}?>
				<?php if($parent) { ?>
					<ul><h2 class="parent"><?php echo $parent; ?></h2>
				<?php } ?>
				<?php if ($brothers) { ?>
					<ul>
					<?php echo $brothers; ?>
					</ul>
					</ul>
				<?php } ?>
				<?php if ($children){?>
					<p class="post-info"><?php _e('This page has the following sub pages:','ml');?></p>
					<ul><?php echo $children; ?></ul>
				<?php }?>

		</div><!-- entry -->
	</div><!-- duo -->
				
	<div class="post <?php if ($parent == "" && $children == "" ){ echo 'nonet';} else { echo 'septet';}?>" id="post-<?php the_ID(); ?>">
				
		<div class="entry">
			<?php the_content(); ?>
			<?php wp_link_pages(); ?>
					</div>
		<?php comments_template(); ?>
	</div><!-- septet -->
			
		<?php endwhile; ?>
	<?php else : include_once(TEMPLATEPATH.'/notfound.php');?>
	<?php endif; ?>
	<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->

<?php get_footer();?>