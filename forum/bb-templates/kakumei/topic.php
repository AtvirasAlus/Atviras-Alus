<?php bb_get_header(); ?>

<div class="inner_container">
	<div class="forum_bcr">
		<a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a><?php bb_forum_bread_crumb(); ?>
	</div>
	<div class="forum_search">
		<?php search_form(); ?>
	</div>
	<div class="clear"></div>
</div>

<div class="inner_container" role="main">
	<div class="inner_header">
		<span id="topic_labels"><?php bb_topic_labels(); ?></span>
		<?php topic_title(); ?>
	</div>
	<div>
		<div class="topic_info_line">
			<?php topic_posts_link(); ?>
			<div class="clear"></div>
		</div>
		<div class="topic_info_line">
			<span>Pradėta prieš:</span>
			<p><?=get_topic_start_time()?></p>
			<div class="clear"></div>
		</div>
		<div class="topic_info_line">
			<span>Autorius:</span>
			<p><?=get_topic_author()?></p>
			<div class="clear"></div>
		</div>
		<?php if (1 < get_topic_posts()) : ?>
			<div class="topic_info_line">
				<span><a href="<?=esc_attr(get_topic_last_post_link())?>">Paskutinis parašė:</a></span>
				<p><?=get_topic_last_poster()?></p>
				<div class="clear"></div>
			</div>
		<?php endif; ?>
		<?php do_action('topicmeta'); ?>
	</div>
	<div>
		<?php topic_tags(); ?>
	</div>
</div>
<?php do_action('under_title'); ?>
<?php if ($posts) : ?>
<div class="inner_container" role="main">
	<div style="text-align: center; margin-bottom: 10px;"><?php topic_pages(); ?></div>
	<div id="ajax-response"></div>
	<div id="thread" class="list:post">
		<?php foreach ($posts as $bb_post) : $del_class = post_del_class(); ?>
			<div id="post-<?php post_id(); ?>"<?php alt_class('post', $del_class); ?>>
				<?php bb_post_template(); ?>
			</div>
		<?php endforeach; ?>
	</div>
	<div style="text-align: center; margin-bottom: 10px;"><?php topic_pages(); ?></div>
	<div class="rss-link" style="text-align: right;">
		<a href="<?php topic_rss_link(); ?>" class="rss-link">
			<?php _e('<abbr title="Really Simple Syndication">RSS</abbr> feed for this topic') ?>
		</a>
	</div>
</div>
<?php endif; ?>

	<?php if (topic_is_open($bb_post->topic_id)) : ?>
	<?php post_form(); ?>
<?php else : ?>
	<div><?php _e('Topic Closed') ?></div>
	<div><?php _e('This topic has been closed to new replies.') ?></div>
<?php endif; ?>
	
<?php if (bb_current_user_can('delete_topic', get_topic_id()) || bb_current_user_can('close_topic', get_topic_id()) || bb_current_user_can('stick_topic', get_topic_id()) || bb_current_user_can('move_topic', get_topic_id())) : ?>
	<div class="inner_container">
	<?php bb_topic_admin(); ?>
	</div>
<?php endif; ?>

<?php bb_get_footer(); ?>