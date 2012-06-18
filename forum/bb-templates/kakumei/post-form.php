<?php if (!bb_is_topic()) : ?>
	<dl id="post-form-title-container">
		<dt><?php _e('Title'); ?>:</dt>
		<dd><input name="topic" type="text" id="topic" size="50" maxlength="100" tabindex="1" /></dd>
		<div class="clear"></div>
	</dl>
<?php endif;
do_action('post_form_pre_post'); ?>
<dl id="post-form-post-container">
	<dt>Žinutės tekstas</dt>
	<dd><textarea name="post_content" cols="50" rows="8" id="post_content" tabindex="3"></textarea></dd>
	<div class="clear"></div>
</dl>
<dl id="post-form-allowed-container" class="allowed">
	<dt>&nbsp;</dt>
	<dd>
			<?php _e('Allowed markup:'); ?> 
		<code>
<?php allowed_markup(); ?>
		</code>
	</dd>
	<div class="clear"></div>
</dl>
<dl id="post-form-tags-container">
	<dt><?php printf(__('Žymos (atskirtos kableliais)'), bb_get_tag_page_link()) ?>:</dt>
	<dd><input id="tags-input" name="tags" type="text" size="50" maxlength="100" value="<?php bb_tag_name(); ?>" tabindex="4" /></dd>
	<div class="clear"></div>
</dl>
<?php if (bb_is_tag() || bb_is_front()) : ?>
	<dl id="post-form-forum-container">
		<dt><?php _e('Forum'); ?>:</dt>
		<dd><?php bb_new_topic_forum_dropdown(); ?></dd>
		<div class="clear"></div>
	</dl>
<?php endif; ?>
<dl id="post-form-submit-container" class="submit">
	<dt>&nbsp;</dt>
	<dd><input type="submit" id="postformsub" name="Submit" value="<?php echo esc_attr__('Skelbti'); ?>" tabindex="4" class="ui-button" /></dd>
	<div class="clear"></div>
</dl>
