<?php
/*
Plugin Name: bbPress Attachments
Plugin URI: http://bbpress.org/plugins/topic/bb-attachments
Description: Gives members the ability to upload attachments on their posts.
Author: _ck_
Author URI: http://bbShowcase.org
Version: 0.2.9

License: CC-GNU-GPL http://creativecommons.org/licenses/GPL/2.0/

Donate: http://bbshowcase.org/donate/
*/

global $bb_attachments;
$bb_attachments['role']['see']="read"; 		 // minimum role to see list of attachments = read/participate/moderate/administrate
$bb_attachments['role']['inline']="read";    // minimum role to view inline reduced images = read/participate/moderate/administrate
$bb_attachments['role']['download']="participate";  // minimum role to download original = read/participate/moderate/administrate
$bb_attachments['role']['upload']="participate";  // minimum role to upload = participate/moderate/administrate (times out with post edit time)
$bb_attachments['role']['delete']="moderate";  // minimum role to delete = read/participate/moderate/administrate

$bb_attachments['allowed']['extensions']['default']=array('gif','jpeg','jpg','pdf','png','txt');	// anyone who can upload can submit these
$bb_attachments['allowed']['extensions']['moderate']=array('gif','gz','jpeg','jpg','pdf','png','txt','zip');	// only if they can moderate
$bb_attachments['allowed']['extensions']['administrate']=array('bmp','doc','gif','gz','jpeg','jpg','pdf','png','txt','xls','zip');	// only if they can administrate

$bb_attachments['allowed']['mime_types']['default']=array('text/plain', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'application/x-pdf');  // for anyone that can upload
$bb_attachments['allowed']['mime_types']['moderate']=array('text/plain', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'application/x-pdf', 'application/zip', 'application/x-zip' , 'application/x-gzip');
$bb_attachments['allowed']['mime_types']['administrate']=array('application/octet-stream', 'text/plain', 'text/x-c', 'image/bmp', 'image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'application/pdf', 'application/x-pdf', 'application/zip', 'application/x-zip' , 'application/x-gzip');

$bb_attachments['max']['size']['default']=100*1024;	   // general max for all type/roles, in bytes (ie. 100k)
$bb_attachments['max']['size']['jpg'] =150*1024;	   	   // size limit override by extension, bytes (ie. 200k)
$bb_attachments['max']['size']['png']=150*1024;		   // size limit override by extension, bytes (ie. 200k)
$bb_attachments['max']['size']['moderate']=200*1024;	   // size limit override by role, bytes (ie. 250k) - note this overrides ALL extension limits
$bb_attachments['max']['size']['administrate']=500*1024; // size limit override by role, bytes (ie. 500k) - note this overrides ALL extension limits

$bb_attachments['max']['per_post']['default']=6;		// how many files can be attached per post
$bb_attachments['max']['per_post']['moderate']=10;	// override example$bb_attachments['max']['per_post']['administrate']=20;	// you don't even need to set for every role, this is just an example
$bb_attachments['max']['uploads']['default']=6;		// how many files can be uploaded at a time, in case you want to set per_post high
$bb_attachments['max']['uploads']['moderate']=10;	// and again, this is optional per extra roles

$bb_attachments['max']['filename']['default']=40;	// maximum length of filename before auto-trim
$bb_attachments['max']['filename']['administrate']=80;	// override

$bb_attachments['inline']['width']=590;		// max inline image width in pixels (for display, real width unlimited)
$bb_attachments['inline']['height']=590;		// max inline image height in pixels (for display, real height unlimited)
$bb_attachments['inline']['solution']="resize";	// resize|frame - images can be either resized or CSS framed to meet above requirement
									// only resize is supported at this time
$bb_attachments['inline']['auto']=true;		// auto insert uploaded images into post

$bb_attachments['style']=".bb_attachments_link, .bb_attachments_link img {border:0; text-decoration:none; background:none;} #thread .post li {clear:none;}";

// the following is for Amazon S3 use, get key+secret here: https://aws-portal.amazon.com/gp/aws/developer/account/index.html#AccessKey
$bb_attachments['aws']['enable']=false;			      // Amazon AWS S3 Simple Storage Service - http://amazon.com/s3
$bb_attachments['aws']['key']="12345678901234567890";				  // typically 20 letters+numbers
$bb_attachments['aws']['secret']="1234567890123456789012345678901234567890";	  // must be EXACTLY 40 characters long

// stop editing here (advanced user settings below)

// don't edit the following aws bucket or aws url unless you know what you are doing and have aws experience
// if you rename the bucket, files are NOT moved automatically - you must do it manually via an S3 utility
$bb_attachments['aws']['bucket']=strtolower("bb-attachments.".preg_replace("/^(www?[0-9]*?\.)/i","",$_SERVER['HTTP_HOST']));   

// base url to amazon for retrieval, or may be a cname mirror off your own domain
// http://docs.amazonwebservices.com/AmazonS3/2006-03-01/VirtualHosting.html#VirtualHostingCustomURLs
// cname example: bb-attachments.yoursite.com CNAME bb-attachments.yoursite.com.s3.amazonaws.com
$bb_attachments['aws']['url']="http://".$bb_attachments['aws']['bucket'].".s3.amazonaws.com/";  

$bb_attachments['path']=dirname($_SERVER['DOCUMENT_ROOT'])."/bb-attachments/";  //  make *NOT* WEB ACCESSABLE for security

$bb_attachments['upload_on_new']=true;	// allow uploads directly on new posts (set FALSE if incompatible for some reason)

$bb_attachments['icons']=array('default'=>'default.gif','bmp'=>'img.gif','doc'=>'doc.gif','gif'=>'img.gif','gz'=>'zip.gif','jpeg'=>'img.gif','jpg'=>'img.gif','pdf'=>'pdf.gif','png'=>'img.gif','txt'=>'txt.gif','xls'=>'xls.gif','zip'=>'zip.gif');

$bb_attachments['icons']['url']=bb_get_option('uri').trim(str_replace(array(trim(BBPATH,"/\\"),"\\"),array("","/"),dirname(__FILE__)),' /\\').'/icons/'; 
$bb_attachments['icons']['path']=rtrim(dirname(__FILE__),' /\\').'/icons/'; 

$bb_attachments['title']=" <img class='bb_attachments_link' title='attachments' border=0 align='absmiddle' src='".$bb_attachments['icons']['url'].$bb_attachments['icons']['default']."' />"; // text, html or image to show on topic titles if has attachments

$bb_attachments['max']['php_upload_limit']=0; 	// in bytes, internal php upload limit - only edit if you know what you are doing

$bb_attachments['status']=array("ok","deleted","failed","denied extension","denied mime","denied size","denied count","denied duplicate","denied dimensions");

$bb_attachments['errors']=array("ok","uploaded file exceeds UPLOAD_MAX_FILESIZE in php.ini","uploaded file exceeds MAX_FILE_SIZE in the HTML form",
"uploaded file was only partially uploaded","no file was uploaded","temporary folder missing","failed to write file to disk","file upload stopped by PHP extension");

$bb_attachments['db']="bb_attachments";   //   $bbdb->prefix."attachments";  // database name - force to "bb_attachments" if you need compatibility with an old install

// really stop editing!

if (!is_bb_feed()) {

function bb_attachments_active() {static $is_topic; return isset($is_topic)?$is_topic:$is_topic=in_array(bb_get_location(),array('topic-page','topic-edit-page','forum-page'));}

if (bb_attachments_active() || isset($_GET['new']) || !empty($_FILES) || strpos($_SERVER['QUERY_STRING'],'bb_attachments')!==false || (defined('BB_IS_ADMIN') && BB_IS_ADMIN)) { 
	include('bb-attachments-init.php'); 
} 
if (!bb_attachments_active() && $bb_attachments['title']) {
	add_filter('topic_title', 'bb_attachments_title',200); 
	function bb_attachments_title( $title ) {
		global $bb_attachments, $topic;
		if ($bb_attachments['title'] && isset($topic->bb_attachments) && intval($topic->bb_attachments)>0)  {
			$title=$title.$bb_attachments['title'];			
		}
		return $title;
	} 
} // is_topic
if ($bb_attachments['style']) {
	add_action('bb_head', 'bb_attachments_add_css');	// add css if present (including Kakumei  0.9.0.2 LI fix!)
	function bb_attachments_add_css() { global $bb_attachments;  echo '<style type="text/css">'.$bb_attachments['style'].'</style>'."\n";} // inject css
}

} // is_bb_feed

?>