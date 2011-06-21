<?php
require('admin.php');

if ( !bb_current_user_can('manage_tags') )
	bb_die(__('You are not allowed to manage tags.'));

$old_id = (int) $_POST['id' ];
$tag = $_POST['tag'];

bb_check_admin_referer( 'merge-tag_' . $old_id );

if ( ! $tag = bb_get_tag( $tag ) )
	bb_die(__('Tag specified not found.'));

if ( ! bb_get_tag( $old_id ) )
	bb_die(__('Tag to be merged not found.'));

if ( $merged = bb_merge_tags( $old_id, $tag->tag_id ) ) {
	printf(__("Number of topics from which the old tag was removed: %d <br />\n"),  $merged['old_count']);
    printf(__("Number of topics to which the new tag was added: %d <br />\n"),$merged['diff_count']);
	printf(__("Number of rows deleted from tags table:%d <br />\n"),$merged['destroyed']['tags']);
	printf(__('<a href="%s">New Tag</a>'), bb_get_tag_link());
} else {
   die(printf(__("Something odd happened when attempting to merge those tags.<br />\n<a href=\"%s\">Try Again?</a>"), wp_get_referer()));
}
?>
