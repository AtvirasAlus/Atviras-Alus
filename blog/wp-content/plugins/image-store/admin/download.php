<?php 


/**
 * Image store - download image
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2011
 * @since 0.5.0
*/


//define constants
define('WP_ADMIN',true);
define('DOING_AJAX',true);

//load wp
require_once '../../../../wp-load.php';

//make sure that the request came from the same domain	
if(!wp_verify_nonce($_REQUEST["_wpnonce"],"ims_download_img"))
	die();

class ImStoreDownloadImage{
	
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function __construct(){
		
		if(empty($_REQUEST['img'])) die();
		
		global $ImStore; 
		if(is_admin()) $this->attachment = get_post_meta($ImStore->admin->decrypt_id($_REQUEST['img']),'_wp_attachment_metadata',true);
		else $this->attachment = get_post_meta($ImStore->store->decrypt_id($_REQUEST['img']),'_wp_attachment_metadata',true);
		if($this->attachment['sizes'][$_GET['sz']]['url']) 
			$this->image_dir = str_ireplace(WP_CONTENT_URL,WP_CONTENT_DIR,$this->attachment['sizes'][$_GET['sz']]['url']);
		elseif($this->attachment['sizes']['preview']['url']) 
			$this->image_dir = str_ireplace(WP_CONTENT_URL,WP_CONTENT_DIR,$this->attachment['sizes']['preview']['url']);
		
		if(!file_exists($this->image_dir)) die(); 
		$this->display_image();
		
	}
	
	
	/**
	 * display image
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function display_image(){
		global $wpdb; 
		
		$realname	= basename($this->image_dir);
		$filetype 	= wp_check_filetype($realname);
		$filename 	= $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = " . $_REQUEST['img']) ; 
		$filename	= ($filename)?$filename:$realname;
		
		header('Expires: 0');
		header('Pragma: no-cache');
		header('Cache-control: private');
		header('Last-Modified: ' . gmdate('D,d M Y H:i:s') . ' GMT');
		header('Cache-Control: no-cache,must-revalidate,max-age=0');
		header('Content-Description: File Transfer');
		header("Content-Transfer-Encoding: binary");
		header('Content-Type: ' . $filetype['type']);
		header('Content-Disposition: attachment; filename=' . $filename);

		
		switch($filetype['ext']){
			case "jpg":
			case "jpeg":
				$image = imagecreatefromjpeg($this->image_dir);
				break;
			case "gif":
				$image = imagecreatefromgif($this->image_dir);
				break;
			case "png":
				$image = imagecreatefrompng($this->image_dir);
				break;
		}
		
		//gray scale
		if($_REQUEST['c'] == 'ims_bw'){
			imagefilter($image,IMG_FILTER_GRAYSCALE);
			imagefilter($image,IMG_FILTER_BRIGHTNESS,+10);
		}
		
		//sepia
		if($_REQUEST['c'] == 'ims_sepia'){
			imagefilter($image,IMG_FILTER_GRAYSCALE); 
			imagefilter($image,IMG_FILTER_BRIGHTNESS,-10);
			imagefilter($image,IMG_FILTER_COLORIZE,35,25,10);
		}
		
		//create new image
		switch($filetype['ext']) {
			case "jpg":
			case "jpeg":
				imagejpeg($image,NULL,100);
				break;
			case "gif":
				imagegif($image);
				break;
			case "png":
				imagepng($image,NULL,9);
				break;
		}
		
		imagedestroy($image);
		die();
	}


}

//do that thing you do 
$ImStoreImage = new ImStoreDownloadImage();
?>