<?php bb_get_header(); ?>
<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <?php view_name(); ?>
	</div>
	<div class="forum_search">
		<?php search_form(); ?>
	</div>
	<div class="clear"></div>
</div>

<?php if ( $topics || $stickies ) : ?>
	<div id="discussions" class="inner_container">
		<div class="inner_header"><?php view_name(); ?></div>
		<div>
			<div class="as-table" id="latest">
				<div class="as-row table-header">
					<div class="as-cell" style="font-weight: bold;"><?php _e('Topic'); ?></div>
					<div class="as-cell centrify" style="font-weight: bold;"><?php _e('Posts'); ?></div>
					<div class="as-cell centrify" style="font-weight: bold;"><?php _e('Last Poster'); ?></div>
					<div class="as-cell centrify" style="font-weight: bold;"><?php _e('Freshness'); ?></div>
				</div>
				<?php if ( $stickies ) : foreach ( $stickies as $topic ) : ?>
					<div<?php topic_class("as-row"); ?>>
						<div class="as-cell"><span class="labels"><?php bb_topic_labels(); ?></span> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a></div>
						<div class="as-cell centrify"><?php topic_posts(); ?></div>
						<div class="as-cell centrify"><?php topic_last_poster(); ?></div>
						<div class="as-cell centrify"><a href="<?php topic_last_post_link(); ?>" title="<?php topic_time(array('format'=>'datetime')); ?>"><?php topic_time(); ?></a></div>
					</div>
				<?php endforeach; endif; ?>
				<?php if ( $topics ) : foreach ( $topics as $topic ) : ?>
					<div<?php topic_class("as-row"); ?>>
						<div class="as-cell"><span class="labels"><?php bb_topic_labels(); ?></span> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a><?php topic_page_links(); ?></div>
						<div class="as-cell centrify"><?php topic_posts(); ?></div>
						<div class="as-cell centrify"><?php topic_last_poster(); ?></div>
						<div class="as-cell centrify"><a href="<?php topic_last_post_link(); ?>" title="<?php topic_time(array('format'=>'datetime')); ?>"><?php topic_time(); ?></a></div>
					</div>
				<?php endforeach; endif; ?>
			</div>
		</div>
		<div class="forum_pages">
			<?php view_pages(); ?>
		</div>
		<div class="forum_subactions">
			<a href="<?php bb_view_rss_link(); ?>" class="rss-link"><?php _e('<abbr title="Really Simple Syndication">RSS</abbr> feed for this view'); ?></a>
		</div>
	</div>
<?php endif; ?>

<?php bb_get_footer(); ?>