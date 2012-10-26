<?php

/**
 * support for jqery swf upload
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0
 */
//post_id and $_FILES are required
if (empty($_REQUEST['post_id']) || empty($_FILES))
	die();

//define constants
define('WP_ADMIN', true);
define('DOING_AJAX', true);

$_SERVER['PHP_SELF'] = "/wp-admin/upload-img.php";
require_once '../../../../wp-load.php';

// Flash often fails to send cookies with the POST or upload, so we need to pass it in GET or POST instead
if (is_ssl() && empty($_COOKIE[SECURE_AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']))
	$_COOKIE[SECURE_AUTH_COOKIE] = $_REQUEST['auth_cookie'];
elseif (empty($_COOKIE[AUTH_COOKIE]) && !empty($_REQUEST['auth_cookie']))
	$_COOKIE[AUTH_COOKIE] = $_REQUEST['auth_cookie'];
if (empty($_COOKIE[LOGGED_IN_COOKIE]) && !empty($_REQUEST['logged_in_cookie']))
	$_COOKIE[LOGGED_IN_COOKIE] = $_REQUEST['logged_in_cookie'];
unset($current_user);

header('Last-Modified:' . gmdate('D,d M Y H:i:s') . ' GMT');
header('Content-Type: text/plain; charset=' . get_option('blog_charset'));

if (!current_user_can('ims_add_galleries'))
	wp_die(__('You do not have permission to upload files.'));

check_admin_referer('media-form');

$cols = (int) $_REQUEST['cols'];
$post_id = $_REQUEST['post_id'];
$filename = $_FILES['async-upload']['name'];

require_once ABSPATH . 'wp-admin/includes/file.php';
$filedata = wp_handle_upload($_FILES['async-upload'], array('test_form' => false));

if (isset($filedata['error']))
	$filedata = new WP_Error('upload_error', $filedata['error']);

if (is_wp_error($filedata)) {
	echo '<td colspan="' . $cols . '"><div class="error-div">
	<a class="dismiss" href="#" >' . __('Dismiss') . '</a>
	<strong>' . sprintf(__('&#8220;%s&#8221; has failed to upload due to an error'), esc_html($filename)) . '</strong><br />' .
	esc_html($filedata->get_error_message()) . '</div></td>';
	exit;
}

global $ImStore;

$filedata['name'] = $filename;
$ImStore->generate_ims_metadata($filedata, $post_id, true);
die();