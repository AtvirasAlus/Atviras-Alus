<?php get_header();?>

	<?php if (get_settings('viala_featureid') != "") 
	$FeatureId = get_settings('viala_featureid');?>
	<?php $FullPost = get_settings('viala_fullpost');?>
	<?php $LongPost = get_settings('viala_longpost');?>
	<?php $ExLink = get_settings('viala_exlink');?>

<?php if ( is_home() and !is_paged() ) { ?>
<div id="main">
<div class="wrap">

	<?php $my_query = new WP_Query($query_string . '&showposts=1&cat='.$FeatureId);
	$wp_query->in_the_loop = true;
	while ($my_query->have_posts()) : $my_query->the_post();
	$do_not_duplicate = $post->ID; 
	?>

<!-- first loop -->
<div class="post <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>" id="post-<?php the_ID(); ?>">
<?php $pic = get_post_meta($post->ID, "pic", $single = true);
	if($pic !== '') {
	echo '<div id="pic"><img alt="';
	echo the_title();
	echo '" title="';
	echo the_title();
	echo '" width="440" src="';
	echo $pic;
	echo '"/>';
	} else {
        echo '<div id="head"><div id="headerimage"></div>'; } ?>
	</div><!-- pic -->

<?php   if($LongPost == '1') { ?>
	<div id="feature-info">
	<?php $toc = get_post_meta($post->ID, "toc", $single = true);
	if($toc !== '') {
	echo '<div class="toc">';
	echo $toc;
	echo '</div>';}?>
	<p class="postmetadata"><?php if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>'); ?> </p>
	<p class="postmetadata"><?php _e('Posted in','ml');?> <?php the_category(', ') ?> &bull; <?php comments_popup_link(__('No Comments &#187;','ml'), __('1 Comment &#187;','ml'), __('% Comments &#187;','ml'),'',__('Comments Off','ml')); ?></p>
			<?php comments_template(); ?>
	<?php if($FullPost == '1') { } else {?>
	<p class="excerptlink"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Continue Reading &raquo;','ml');?> <?php the_title(); ?>"><?php if($ExLink != '') { echo apply_filters('the_title', $ExLink);} else { _e('Continue Reading &raquo;','ml'); }?>
	<?php } ?>
</a></p>
	</div><!---feature-info--->
<?php } ?>

	<div class="posttitle">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-info"><?php the_date(__('M jS, Y','ml')) ?> <!---<?php _e('by','ml');?> <?php the_author_posts_link() ?>---> <?php edit_post_link(__('Edit','ml'), ' &bull; ', ''); ?> </p>
				</div>

	<div class="<?php if($LongPost == '1') { echo 'entry-long'; } else { echo 'entry';}?>">
	<?php if($FullPost == '1') { ?>
        <?php the_content(__('Continue Reading &raquo;','ml')); ?>
	</div><!---long entry--->
	<?php } else { ?>
        <?php the_excerpt(__('Continue Reading &raquo;','ml')); ?>
	</div><!-- entry -->
		<?php   if($LongPost != '1') { ?>
	<p class="excerptlink"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Continue Reading &raquo;','ml');?> <?php the_title(); ?>"><?php if($ExLink != '') { echo apply_filters('the_title', $ExLink);} else { _e('Continue Reading &raquo;','ml'); }?>
</a></p>
		<?php } ?>
	<?php } ?>
	<?php wp_link_pages(); ?>

<?php   if($LongPost != '1') { ?>
	<p class="postmetadata"><?php if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>'); ?> </p>
	<p class="postmetadata"><?php _e('Posted in','ml');?> <?php the_category(', ') ?> &bull; <?php comments_popup_link(__('No Comments &#187;','ml'), __('1 Comment &#187;','ml'), __('% Comments &#187;','ml'),'',__('Comments Off','ml')); ?></p>
			<?php comments_template(); ?>
<?php } ?>

</div><!-- post -->
<?php endwhile; ?>
<!-- end first loop -->

</div><!-- wrap -->
</div><!-- end id:main -->
<?php } ?>

<div id="sub">
<div class="wrap">

<?php if (get_settings('viala_subs') != "") { 
	$SubOptions = get_settings('viala_subs');
	} else { 
	$SubOptions = '6';}?>
<?php if ($FeatureId == '0') {$homeCount = $SubOptions; 
	} else { $homeCount = $SubOptions-1;}?>
<?php $subCount = $SubOptions + 3;?>
<?php $pageBase = $paged-2;?>
<?php $nilBase = $paged-1;?>
<?php $vOffset = ($pageBase * $subCount) + $homeCount; ?>

<?php if ($paged < 2 && $homeCount == '0') { ?>
	<p class="postnav" align="center"><a href="?paged=2"><?php _e('Older Posts &#187;','ml') ?></a></p>
	<?php } else {?>

	  
	<?php if ($paged < 2) { ?>
	<?php query_posts( $query_string . '&posts_per_page='.$homeCount );?>
	<?php } elseif ($paged > 1 && $homeCount == '0') {?>
	<?php query_posts( $query_string . '&posts_per_page=4&paged='.$nilBase ); ?>
	<?php } else { ?>
	<?php query_posts( $query_string . '&posts_per_page='.$subCount . '&offset=' . $vOffset ); ?>
	<?php } ?>


<!-- 2nd loop -->	 
	<?php if (have_posts()) : while (have_posts()) : the_post(); 
	if( $post->ID == $do_not_duplicate ) continue;
	update_post_caches($posts); ?>

<div class="subpost trio <?php $cat = get_the_category(); $cat = $cat[0]; echo $cat->category_nicename; ?>" id="post-<?php the_ID(); ?>">
	<div class="posttitle">
		<h2><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to','ml');?> <?php the_title(); ?>"><?php the_title(); ?></a></h2>
		<p class="post-info"><?php the_date(__('M jS, Y','ml')) ?> <!---<?php _e('by','ml');?> <?php the_author_posts_link() ?>---> <?php edit_post_link(__('Edit','ml'), ' &bull; ', ''); ?> </p>
				</div>

	<div class="entry">
	<?php the_excerpt(__('Continue Reading &raquo;','ml')); ?>
	</div><!-- entry -->
	<p class="excerptlink"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Continue Reading &raquo;','ml');?> <?php the_title(); ?>"><?php _e('Continue Reading &raquo;','ml');?></a></p>
	<?php wp_link_pages(); ?>
	<p class="postmetadata"><?php if (function_exists('the_tags')) the_tags(__('Tags: ','ml'), ', ', '<br/>'); ?> </p>

	<p class="postmetadata"><?php _e('Posted in','ml');?> <?php the_category(', ') ?> &bull; <?php comments_popup_link(__('No Comments &#187;','ml'), __('1 Comment &#187;','ml'), __('% Comments &#187;','ml'),'',__('Comments Off','ml')); ?></p>
				<?php comments_template(); ?>
</div><!-- subpost -->
		
	<?php endwhile;?>
		
	<div id="postnav">
	<p class="postnav" align="center"><?php posts_nav_link(' - ',__('&#171; Newer Posts','ml'),__('Older Posts &#187;','ml')) ?></p>
	</div>	
	
<?php else : include_once(TEMPLATEPATH.'/notfound.php');?>
<?php endif; ?>
<!-- end 2nd loop -->

<?php } ?>

<?php get_sidebar();?>

</div>
</div>

<?php get_footer();?>