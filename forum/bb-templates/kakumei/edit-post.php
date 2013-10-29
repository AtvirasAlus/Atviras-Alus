<?php bb_get_header(); ?>
<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Edit Post'); ?>
	</div>
	<div class="forum_search">
		<?php search_form(); ?>
	</div>
	<div class="clear"></div>
</div>
<div class="inner_container">
	<div class="inner_header">
		Žinutės redagavimas
	</div>
<?php edit_form(); ?>
</div>
<?php if (function_exists('bb_attachments')) {bb_attachments();} ?>
<?php bb_get_footer(); ?>
