<?php get_header();?>

	<?php if ($posts) : foreach ($posts as $post) : start_wp(); ?>
	<?php $attachment_link = get_the_attachment_link($post->ID, true, array(450, 800)); // This also populates the iconsize for the next line ?>
	<?php $_post = &get_post($post->ID); $classname = ($_post->iconsize[0] <= 128 ? 'small' : '') . 'attachment'; // This lets us style narrow icons specially ?>

<div id="title">
<div class="wrap">
	<div class="posttitle">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-info">
		<?php _e('Posted in','ml');?> <?php the_category(', ') ?>  <?php _e('on','ml');?> <?php the_date(__('M jS, Y','ml')) ?> <?php edit_post_link(__('Edit','ml'), '&bull; ', ''); ?> <?php comments_popup_link(__('No Comments &#187;','ml'), __('1 Comment &#187;','ml'), __('% Comments &#187;','ml'),'',__('Comments Off','ml')); ?> </p>
	</div>
</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">				
	<div class="post nonet" id="post-<?php the_ID(); ?>">
		<div class="entry">
			<p class="<?php echo $classname; ?>"><?php echo $attachment_link; ?><br /><?php echo basename($post->guid); ?></p>
			<?php the_content(); ?>
	
				<?php trackback_rdf(); ?>
			
		</div>
			<?php comments_template(); ?>
	</div>
		<?php endforeach; else : include_once(TEMPLATEPATH.'/notfound.php');?>
		
	<?php endif; ?>
<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->

<?php get_footer();?>