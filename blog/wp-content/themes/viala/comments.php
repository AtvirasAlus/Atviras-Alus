<?php // Do not delete these lines
	if ('comments.php' == basename($_SERVER['SCRIPT_FILENAME']))
		die (__('Please do not load this page directly. Thanks!', 'ml'));

        if (!empty($post->post_password)) { // if there's a password
            if ($_COOKIE['wp-postpass_' . COOKIEHASH] != $post->post_password) {  // and it doesn't match the cookie
				?>

				<p class="nocomments"><?php _e("This post is password protected. Enter the password to view comments."); ?><p>

				<?php
				return;
            }
        }

		/* This variable is for alternating comment background */
		$oddcomment = 'alt';
?>

<!-- You can start editing here. -->

<?php if ($comments) : ?>
	<h3 id="comments"><?php comments_number(__('No Responses', 'ml'), __('One Response', 'ml'), __('% Responses', 'ml') );?> <?php _e('to', 'ml'); ?> &#8220;<?php the_title(); ?>&#8221;</h3>

	<ol class="commentlist">
	<?php $commentcounter = 0; ?>
	<?php foreach ($comments as $comment) : ?>
		<?php $commentcounter++; ?>
		<li class="<?php echo $oddcomment; /* Style differently if comment author is blog author */ if ($comment->comment_author_email == get_the_author_email()) { echo ' authorcomment'; } ?>" id="comment-<?php comment_ID() ?>">
			<div class="cmtinfo"><em><?php edit_comment_link(__('edit this', 'ml'),'',''); ?> <?php _e('on', 'ml'); ?> <?php comment_date(__('d M Y', 'ml')) ?> <?php _e('at', 'ml'); ?> <?php comment_time() ?></em><small class="commentmetadata"><a href="#comment-<?php comment_ID() ?>" title=""><span class="commentnum"><?php echo $commentcounter; ?></span></a></small><cite><?php comment_author_link() ?></cite></div>
			<?php if ($comment->comment_approved == '0') : ?>
			<em><?php _e('Your comment is awaiting moderation.', 'ml'); ?></em><br />
			<?php endif; ?>
			<div class="comment">
				<p class="avatarimg">
					<?php if(function_exists('get_avatar')) echo get_avatar(get_comment_author_email(), '45') ;?>
				</p>
				<?php comment_text() ?>
			</div>
		</li>

	<?php /* Changes every other comment to a different class */
		if ('alt' == $oddcomment) $oddcomment = '';
		else $oddcomment = 'alt';
	?>

	<?php endforeach; /* end for each comment */ ?>

	</ol>

 <?php else : // this is displayed if there are no comments so far ?>

  <?php if ('open' == $post-> comment_status) : ?>
		<!-- If comments are open, but there are no comments. -->

	 <?php else : // comments are closed ?>
		<!-- If comments are closed. -->
		<p class="nocomments"><?php _e('Comments are closed.', 'ml'); ?></p>

	<?php endif; ?>
<?php endif; ?>
<div class="post-links">
<p>
<?php if ($post->ping_status == "open") { ?>
	<span class="trackback"><a href="<?php trackback_url(display); ?>"><?php _e('Trackback URI', 'ml'); ?></a></span> |
<?php } ?>
<?php if ($post-> comment_status == "open") {?>
	<span class="commentsfeed"><?php comments_rss_link(__('Comments RSS', 'ml')); ?></span>
<?php }; ?>
</p>
</div>

<?php if ('open' == $post-> comment_status) : ?>

<h3 id="respond"><?php _e('Leave a Reply', 'ml'); ?></h3>

<?php if ( get_option('comment_registration') && !$user_ID ) : ?>
<p><?php _e('You must be', 'ml'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?redirect_to=<?php the_permalink(); ?>"><?php _e('logged in', 'ml'); ?></a> <?php _e('to post a comment.', 'ml'); ?></p>
<?php else : ?>

<form action="<?php echo get_option('siteurl'); ?>/wp-comments-post.php" method="post" id="commentform">

<?php if ( $user_ID ) : ?>

<!-- Hier stimmt etwas nicht -->

<p><?php _e('Logged in as', 'ml'); ?> <a href="<?php echo get_option('siteurl'); ?>/wp-admin/profile.php"><?php echo $user_identity; ?></a>. <a href="<?php echo get_option('siteurl'); ?>/wp-login.php?action=logout" title="<?php _e('Log out of this account') ?>"><?php _e('Logout', 'ml'); ?> &raquo;</a></p>

<?php else : ?>

<p><input class="textbox" type="text" name="author" id="author" value="<?php echo $comment_author; ?>" size="22" tabindex="1" />
<label for="author"><?php _e('Name', 'ml'); ?> <small><?php if ($req) _e('(required)'); ?></small></label></p>

<p><input class="textbox" type="text" name="email" id="email" value="<?php echo $comment_author_email; ?>" size="22" tabindex="2" />
<label for="email"><?php _e('Mail (hidden)', 'ml'); ?> <small><?php if ($req) _e('(required)'); ?></small></label></p>

<p><input class="textbox" type="text" name="url" id="url" value="<?php echo $comment_author_url; ?>" size="22" tabindex="3" />
<label for="url"><?php _e('Website', 'ml'); ?></label></p>

<?php endif; ?>

<!--<p><small><strong>XHTML:</strong> You can use these tags: <?php echo allowed_tags(); ?></small></p>-->

<p><textarea name="comment" id="comment" cols="100%" rows="10" tabindex="4"></textarea></p>

<p><input name="submit" type="submit" id="submit" tabindex="5" value="<?php _e('Submit Comment', 'ml'); ?>" />
<input type="hidden" name="comment_post_ID" value="<?php echo $id; ?>" />
</p>
<?php do_action('comment_form', $post->ID); ?>

</form>

<?php endif; // If registration required and not logged in ?>

<?php endif; // if you delete this the sky will fall on your head ?>