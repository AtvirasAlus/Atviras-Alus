<?php

/**
 *support for jqery swf upload
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0
*/

//define constants
define( 'WP_ADMIN',true);
define( 'DOING_AJAX',true);
$_SERVER['PHP_SELF'] = "/wp-admin/upload-img.php";

require_once '../../../../wp-load.php';
 
// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
if ( is_ssl( ) && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
elseif ( empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']) )
	$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
if ( empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_REQUEST['logged_in_cookie']) )
	$_COOKIE[LOGGED_IN_COOKIE] = $_REQUEST['logged_in_cookie'];
unset($current_user);

header( 'Last-Modified:'.gmdate( 'D,d M Y H:i:s').' GMT' );
header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset'));

if ( !current_user_can( 'ims_add_galleries') )
	wp_die(__( 'You do not have permission to upload files.'));

check_admin_referer( 'media-form' );

$file_id 		= 'async-upload';
$post_id	= $_REQUEST['post_id'];
$name 		= $_FILES[$file_id]['name'];
$cols			= (int)$_REQUEST['cols'];

require_once ABSPATH . 'wp-admin/includes/file.php';
$file = wp_handle_upload( $_FILES[$file_id] , array( 'test_form' => false) );

if ( isset($file['error']) )
	$file = new WP_Error( 'upload_error', $file['error'] );

if ( is_wp_error($file) ){
	echo '<td colspan="'.$cols.'"><div class="error-div">
	<a class="dismiss" href="#" >' . __( 'Dismiss') . '</a>
	<strong>' . sprintf(__( '&#8220;%s&#8221; has failed to upload due to an error' ), esc_html( $name ) ) . '</strong><br />' .
	esc_html($file->get_error_message( )) . '</div></td>';
	exit;
}

$name_parts = pathinfo($name);
$name = trim( substr( $name, 0, -(1 + strlen($name_parts['extension'])) ) );

$content = '';
$url = $file['url'];
$type = $file['type'];
$file = $file['file'];
$title = $name;

global $current_user;
require_once ABSPATH . 'wp-admin/includes/image.php';
if ( $image_meta = @wp_read_image_metadata($file) ){
	if ( trim( $image_meta['title'] ) && ! is_numeric( sanitize_title( $image_meta['title'] ) ) )
		$title = $image_meta['title'];
	if ( trim( $image_meta['caption'] ) )
		$content = $image_meta['caption'];
	if ( !trim( $image_meta['credit'] ) )
		$image_meta['credit'] = $current_user->display_name;
}

global $ImStore;

$orininfo = @getimagesize( $file );
$image_meta['color'] = __( 'Unknown', $ImStore->domain );
if( isset($orininfo['channels']) ){
	switch( $orininfo['channels'] ){ 
		case 1:$image_meta['color'] = 'BW'; break;
		case 3:$image_meta['color'] = 'RGB'; break;
		case 4:$image_meta['color'] = 'CMYK'; break;
	}
} 


// Construct the attachment array
$attachment = array(
	'guid' => $url,
	'menu_order' => '',
	'post_title' => $title,
	'post_status' => 'publish',
	'post_type' => 'ims_image',
	'post_parent' => $post_id,
	'post_mime_type' => $type,
	'post_excerpt' => $content,
);

require_once ABSPATH . 'wp-admin/includes/post.php';
$id = wp_insert_post( $attachment );

if ( is_wp_error($id) ){
	echo '<td colspan="'.$cols.'"><div class="error-div">
	<a class="dismiss" href="#">' . __( 'Dismiss') . '</a>
	<strong>' . sprintf(__( '&#8220;%s&#8221; has failed to upload due to an error' ), esc_html( $name ) ) . '</strong><br />' .
	esc_html( $id->get_error_message( ) ) . '</div></td>';
	exit;
}

$filedata = wp_generate_attachment_metadata( $id, $file );
$filedata['image_meta'] = $image_meta;

if( update_post_meta( $id, '_wp_attachment_metadata', $filedata ) ){
	echo apply_filters( "ims_async_upload", $id, $filedata, $attachment );
	if( !get_post_meta( $post_id, '_ims_folder_path' ) )
		update_post_meta( $post_id, '_ims_folder_path', "/". trim( $_REQUEST['folderpath'] , "/" ) );
}