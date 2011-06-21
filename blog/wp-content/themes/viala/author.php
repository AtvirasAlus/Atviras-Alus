<?php get_header();?>
<div id="title">
<div class="wrap">
<?php
	global $wp_query;
	$curauth = $wp_query->get_queried_object();
?>
<h2><?php _e('All About','ml');?> <?php echo $curauth->nickname; ?></h2>

</div><!-- wrap -->
</div><!-- title -->

<div id="body">
<div class="wrap">
	<div class="duo">
	<dl class="authorlist">
<dd class="avatarimg">
	<?php 
		$author_email = $curauth->user_email;
		if(function_exists('get_avatar')) echo get_avatar($author_email, '120') ;
	?>
</dd>
<dt><?php _e('Full Name','ml');?>:</dt>
<dd><?php echo $curauth->first_name. ' ' . $curauth->last_name ;?></dd>
<?php if($curauth->user_url != 'http//:') { ?>
<dt><?php _e('Website','ml');?>:</dt>
<dd><a href="<?php echo $curauth->user_url; ?>"><?php echo $curauth->user_url; ?></a></dd>
<?php } ?>
</dl>
	</div>

	<div class="septet">
		<?php if ($curauth->description != '') { ?>				
			<p class="intro"><?php echo $curauth->description; ?></p>
		<?php } ?>

			<h2><?php _e('Posts by','ml');?> <?php echo $curauth->nickname; ?>:</h2>
			<ul class="authorposts">
			<!-- The Loop -->
		<?php $posts = query_posts($query_string . '&showposts=50');?>
			<?php if ($posts) : foreach ($posts as $post) : the_post(); ?>
			<li class="archive <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>">
				<h4>
				<em><?php the_date(__('d M Y','ml')); ?></em>
				<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a>
				</h4>
			</li>
			<?php endforeach; ?>
			<?php endif; ?>
			<!-- End Loop -->
		</ul>
		<p class="authornav" align="center"><?php posts_nav_link() ?></p>
	</div>
<?php get_sidebar();?>
</div><!-- wrap -->
</div><!-- body -->
<?php get_footer();?>
