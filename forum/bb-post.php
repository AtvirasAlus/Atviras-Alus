<?php
require('./bb-load.php');

bb_auth('logged_in');

if ( $throttle_time = bb_get_option( 'throttle_time' ) )
	if ( isset($bb_current_user->data->last_posted) && time() < $bb_current_user->data->last_posted + $throttle_time && !bb_current_user_can('throttle') )
		bb_die(__('Slow down; you move too fast.'));

if ( !$post_content = trim($_POST['post_content']) )
	bb_die(__('You need to actually submit some content!'));

if ( isset($_POST['topic']) && $forum_id = (int) $_POST['forum_id'] ) {
	if ( !bb_current_user_can('write_posts') )
		bb_die(__('You are not allowed to post.  Are you logged in?'));

	if ( !bb_current_user_can( 'write_topic', $forum_id ) )
		bb_die(__('You are not allowed to write new topics.'));

	bb_check_admin_referer( 'create-topic' );

	$topic = trim( $_POST['topic'] );
	$tags  = trim( $_POST['tags']  );

	if ('' == $topic)
		bb_die(__('Please enter a topic title'));

	$topic_id = bb_new_topic( $topic, $forum_id, $tags );

} elseif ( isset($_POST['topic_id'] ) ) {
	$topic_id = (int) $_POST['topic_id'];
	bb_check_admin_referer( 'create-post_' . $topic_id );
}

if ( !bb_current_user_can( 'write_post', $topic_id ) )
	bb_die(__('You are not allowed to post.  Are you logged in?'));

if ( !topic_is_open( $topic_id ) )
	bb_die(__('This topic has been closed'));

$post_id = bb_new_post( $topic_id, $_POST['post_content'] );

$tags  = trim( $_POST['tags']  );
bb_add_topic_tags( $topic_id, $tags );

$link = get_post_link($post_id);

$topic = get_topic( $topic_id, false );

if ( $topic->topic_posts )
	$link = add_query_arg( 'replies', $topic->topic_posts, $link );

// This action used to be bb_post.php, changed to avoid conflict in bb_load_template()
do_action( 'bb-post.php', $post_id );
if ($post_id)
	wp_redirect( $link );
else
	wp_redirect( bb_get_uri(null, null, BB_URI_CONTEXT_HEADER) );
exit;

?>
