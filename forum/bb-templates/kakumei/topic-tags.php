<div id="topic-tags" style="margin-top: 20px;">
	<div class="inner_header"><?php _e('Tags'); ?></div>
	<?php if ( bb_get_topic_tags() ) : ?>
		<?php bb_list_tags(); ?>
	<?php else : ?>
		<p><?php printf(__('No <a href="%s">tags</a> yet.'), bb_get_tag_page_link() ); ?></p>
	<?php endif; ?>
	<?php tag_form(); ?>
</div>
