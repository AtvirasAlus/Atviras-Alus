<div id="sidebar" <?php if(is_home() && have_posts()) { echo ''; } else { echo 'class="trio"';} ?>>
<ul>
    <?php
        //default
        $sidebar_index=1;

        //homepage
        if(is_home() && have_posts()) {
            $sidebar_index=2;
        }?>

<?php if ( function_exists('dynamic_sidebar') && dynamic_sidebar($sidebar_index) ) : else : ?>
<!-- <?php if(is_home()) viala_ShowAbout(); ?> -->
<?php if(!is_home()) viala_ShowRecentPosts();?>

<?php if(!is_home()) {     ?>
<li class="sidebox">
	<h2><?php _e('Archives','ml'); ?></h2>
	<ul><?php wp_get_archives('type=monthly&show_post_count=true'); ?></ul>
</li>

<li class="sidebox">
	<h2><?php _e('Categories','ml'); ?></h2>
	<ul>
		<?php
		if (function_exists('wp_list_categories'))
		{
			wp_list_categories('show_count=1&hierarchical=1&title_li=');
		}
		else
		{
			wp_list_cats('optioncount=1');
		}
		?>
	</ul>
</li>
	<?php if (function_exists('wp_tag_cloud')) {	?>
<li class="sidebox">
	<h2><?php _e('Tags','ml'); ?></h2>
	<p>
		<?php wp_tag_cloud(); ?>
	</p>
</li>
	<?php } ?>

<li class="sidebox">
	<h2><?php _e('Pages','ml'); ?></h2>
	<ul><?php wp_list_pages('title_li=' ); ?></ul>
</li>

<?php } elseif(is_home()) {  ?>



<?php } ?>

  <?php endif; ?>
</ul>
</div><!-- end sidebar -->