<?php bb_get_header(); ?>


<?php if ($forums) : ?>
	<div class="inner_container">
		<div class="forum_bcr">
			<?php bb_new_topic_link("Sukurti naują diskusiją"); ?>
		</div>
		<div class="forum_search">
			<?php search_form(); ?>
		</div>
		<div class="clear"></div>
	</div>
	<?php if ($topics || $super_stickies) : ?>
		<div id="discussions" class="inner_container">
			<div class="inner_header"><?php _e('Latest Discussions'); ?></div>
			<div>
				<div class="as-table">
					<div class="as-row table-header">
						<div class="as-cell" style="font-weight: bold;"><?php _e('Topic'); ?></div>
						<div class="as-cell centrify" style="width: 95px; font-weight: bold;"><?php _e('Posts'); ?></div>
						<div class="as-cell centrify" style="width: 130px; font-weight: bold;"><?php _e('Last Poster'); ?></div>
						<div class="as-cell centrify" style="width: 110px; font-weight: bold;"><?php _e('Freshness'); ?></div>
					</div>
					<?php 
					if ($super_stickies) : 
						foreach ($super_stickies as $topic) : 
							?>
							<div<?php topic_class("as-row"); ?>>
								<div class="as-cell"><span class="labels"><?php bb_topic_labels(); ?></span> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a><?php topic_page_links(); ?></div>
								<div class="as-cell centrify"><?php topic_posts(); ?></div>
								<div class="as-cell centrify"><?php topic_last_poster(); ?></div>
								<div class="as-cell centrify"><a href="<?php topic_last_post_link(); ?>" title="<?php topic_time(array('format' => 'datetime')); ?>"><?php topic_time(); ?></a></div>
							</div>
							<?php 
						endforeach;
					endif;
					if ($topics) : 
						foreach ($topics as $topic) : 
							?>
							<div<?php topic_class("as-row"); ?>>
								<div class="as-cell"><span class="labels"><?php bb_topic_labels(); ?></span> <a href="<?php topic_link(); ?>"><?php topic_title(); ?></a><?php topic_page_links(); ?></div>
								<div class="as-cell centrify"><?php topic_posts(); ?></div>
								<div class="as-cell centrify"><?php topic_last_poster(); ?></div>
								<div class="as-cell centrify"><a href="<?php topic_last_post_link(); ?>" title="<?php topic_time(array('format' => 'datetime')); ?>"><?php topic_time(); ?></a></div>
							</div>
							<?php 
						endforeach;
					endif;
					?>
				</div>
				<div class="forum_pages">
					<?php bb_latest_topics_pages(); ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if (bb_forums()) : ?>
		<div id="hottags" class="inner_container">
			<div class="inner_header"><?php _e('Forums'); ?></div>
			<div>
				<div class="as-table">
					<div class="as-row table-header">
						<div class="as-cell" style="font-weight: bold;"><?php _e('Main Theme'); ?></div>
						<div class="as-cell centrify" style="width: 55px; font-weight: bold;"><?php _e('Topics'); ?></div>
						<div class="as-cell centrify" style="width: 90px; font-weight: bold;"><?php _e('Posts'); ?></div>
					</div>
					<?php 
					while (bb_forum()) : 
						?>
						<div<?php bb_forum_class("as-row"); ?>>
							<div class="as-cell"><?php bb_forum_pad('<div class="nest">'); ?><a href="<?php forum_link(); ?>"><?php forum_name(); ?></a><?php forum_description(array('before' => '<div class="forum_desc"> &#8211; ', 'after' => '</div>')); ?><?php bb_forum_pad('</div>'); ?></div>
							<div class="as-cell centrify"><?php forum_topics(); ?></div>
							<div class="as-cell centrify"><?php forum_posts(); ?></div>
						</div>
						<?php 
					endwhile; 
					?>
				</div>
			</div>
			<?php 
			if (bb_is_user_logged_in()) : 
				?>
				<div class="forum_subactions">
					<?php foreach (bb_get_views() as $the_view => $title) : ?>
						<a href="<?php view_link($the_view); ?>"><?php view_name($the_view); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif;?>
		</div>
	<?php endif; ?>
	<div id="hottags" class="inner_container">
		<div class="inner_header"><?php _e('Hot Tags'); ?></div>
		<div class="forum_tags">
			<?php bb_tag_heat_map(); ?>
		</div>
	</div>
<?php else :?>
	<div class="inner_container">
		<div class="forum_bcr">
			<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <?php _e('Add New Topic'); ?>
		</div>
		<div class="forum_search">
			<?php search_form(); ?>
		</div>
		<div class="clear"></div>
	</div>
	<?php post_form(); ?>
<?php endif; ?>
<?php bb_get_footer(); ?>