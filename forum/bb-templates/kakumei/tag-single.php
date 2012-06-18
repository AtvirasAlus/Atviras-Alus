<?php bb_get_header(); ?>
<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <a href="<?php bb_tag_page_link(); ?>"><?php _e('Tags'); ?></a> &raquo; <?php bb_tag_name(); ?>
	</div>
	<div class="clear"></div>
</div>
<?php do_action('tag_above_table'); ?>

<?php if ( $topics ) : ?>
	<div class="inner_container">
		<div class="inner_header">Žymos</div>
		<div>
			<div class="as-table">
				<div class="as-row table-header">
					<div class="as-cell"><?php _e('Topic'); ?></div>
					<div class="as-cell centrify"><?php _e('Posts'); ?></div>
					<div class="as-cell centrify"><?php _e('Last Poster'); ?></div>
					<div class="as-cell centrify"><?php _e('Freshness'); ?></div>
				</div>
				<?php foreach ( $topics as $topic ) : ?>
					<div<?php topic_class("as-row"); ?>>
						<div class="as-cell"><span class="labels"><?php bb_topic_labels(); ?></span> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a><?php topic_page_links(); ?></div>
						<div class="as-cell"><?php topic_posts(); ?></div>
						<div class="as-cell"><?php topic_last_poster(); ?></div>
						<div class="as-cell"><a href="<?php topic_last_post_link(); ?>" title="<?php topic_time(array('format'=>'datetime')); ?>"><?php topic_time(); ?></a></div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="forum_subactions">
			<a href="<?php bb_tag_posts_rss_link(); ?>" class="rss-link"><?php _e('<abbr title="Really Simple Syndication">RSS</abbr> link for this tag') ?></a>
		</div>
	</div>
	<?php tag_pages( array( 'before' => '<div class="nav">', 'after' => '</div>' ) ); ?>
<?php endif; ?>
<?php post_form(); ?>
<?php do_action('tag_below_table'); ?>
<?php manage_tags_forms(); ?>
<?php bb_get_footer(); ?>