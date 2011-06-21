<?php

/**
 *support for jqery swf upload
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 0.5.0
*/

//dont cache file
header('Expires:0');
header('Pragma:no-cache');
header('Cache-control:private');
header('Last-Modified:'.gmdate('D,d M Y H:i:s').' GMT');
header('Cache-control:no-cache,no-store,must-revalidate,max-age=0');;

if(($_POST['domain'] != $_SERVER['SERVER_NAME']) || empty($_REQUEST['userid'])) die();

//define constants
define('WP_ADMIN',true);
define('DOING_AJAX',true);

//use to process big images
ini_set('memory_limit','256M');
ini_set('set_time_limit','1000');

//load wp
require_once '../../../../wp-load.php';

$i		= wp_nonce_tick();
$uid	= $_REQUEST['userid'];
$nonce 	= $_REQUEST['_wpnonce'];

global $current_user;
wp_set_current_user($uid);

if(!current_user_can("ims_add_galleries"))  die();


if(($nonce == (substr(wp_hash($i.'ims_ajax'.$uid,'nonce'),-12,10) 
|| substr(wp_hash(($i - 1)."ims_ajax".$uid,'nonce'),-12,10))) && !empty($_FILES)){

	$relpath = getenv("SCRIPT_NAME");
	$abspath = str_replace("\\","/",__FILE__);
	$docroot = str_replace($relpath,"",$abspath).'/';
	$special_chars = array("?","[","]","/","\\","=","<",">",":",";",",","'","\"","&","$","#","*","(",")","|","~","`","!","{","}",chr(0));

	$tempfile 		= $_FILES['Filedata']['tmp_name'];
	$filename 		= str_replace($special_chars,'',$_FILES['Filedata']['name']);
	$filename 		= preg_replace('/[\s-]+/','-',$filename);
	$targetpath 	= str_replace(array('//','/wp-admin/'),'/',$docroot.$_REQUEST['folder']);
	$targetfile 	= $targetpath.'/'.$filename;
	
	if(!file_exists($targetpath)){
		@mkdir($targetpath,0775,true);
	}
	
	if(preg_match('/(png|jpg|jpeg|gif)$/i',$filename)){
		if(!file_exists($targetfile)){
			move_uploaded_file($tempfile,$targetfile);
			@chmod($targetfile,0775);
			@unlink(tempfile);
			echo $targetfile; return;
		}else{
			@unlink(tempfile);
			echo "x";
			return;
		}
	}
	@unlink(tempfile);
	return;
}
die();
?>