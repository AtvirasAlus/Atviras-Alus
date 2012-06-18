<?php if ( $topic_title ) : ?>
	<dl id="post-form-title-container">
		<dt><?php _e('Topic:'); ?></dt>
		<dd><input name="topic" type="text" id="topic" size="50" maxlength="80"  value="<?php echo esc_attr( get_topic_title() ); ?>" /></dd>
		<div class="clear"></div>
	</dl>
<?php endif;
do_action( 'edit_form_pre_post' ); ?>
<dl id="post-form-post-container">
	<dt>Žinutės tekstas:</dt>
	<dd><textarea name="post_content" cols="50" rows="8" id="post_content"><?php echo apply_filters('edit_text', get_post_text() ); ?></textarea></dd>
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
<dl id="post-form-submit-container" class="submit">
	<dt>
		&nbsp;
		<input type="hidden" name="post_id" value="<?php post_id(); ?>" />
		<input type="hidden" name="topic_id" value="<?php topic_id(); ?>" />
	</dt>
	<dd><input type="submit" name="Submit" value="<?php echo esc_attr__( 'Redaguoti' ); ?>" class="ui-button" /></dd>
	<div class="clear"></div>
</dl>