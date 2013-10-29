<?php bb_get_header(); ?>
<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Tags'); ?>
	</div>
	<div class="clear"></div>
</div>
<div class="inner_container">
	<div class="inner_header">Populiariausios forumo žymos</div>
	<div id="hottags" class="forum_tags">
		<?php bb_tag_heat_map( 9, 38, 'pt', 80 ); ?>
	</div>
</div>
<?php bb_get_footer(); ?>