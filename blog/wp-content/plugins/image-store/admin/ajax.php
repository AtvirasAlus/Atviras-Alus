<?php 
/**
 *Ajax events for admin area
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
header('Cache-control:no-cache,no-store,must-revalidate,max-age=0');

//define constants
define('WP_ADMIN',true);
define('DOING_AJAX',true);

//load wp
require_once '../../../../wp-load.php';

//make sure that the request came from the same domain	
if(stripos($_SERVER['HTTP_REFERER'],get_bloginfo('siteurl')) === false) 
	die();


/**
 *Move price list to trash
 *
 *@return void
 *@since 0.5.0
*/
function ajax_imstore_pricelist_delete(){
	if(!current_user_can("ims_change_pricing"))return;
	check_ajax_referer("ims_ajax");
	wp_delete_post(intval($_GET['listid']),true);
	die();
}

/**
 *Delete post
 *
 *@return void
 *@since 2.0.0
*/
function ajax_imstore_delete_post(){
	if(!current_user_can("ims_change_pricing"))return;
	check_ajax_referer("ims_ajax");
	
	$metadata = get_post_meta((int)$_GET['postid'],'_wp_attachment_metadata');
	if($metadata[0]['sizes'] && !empty($_GET['deletefile'])){
		foreach($metadata[0]['sizes'] as $size)
			@unlink(WP_CONTENT_DIR.$folder.'/'.$size['file']);
		@unlink(WP_CONTENT_DIR.$metadata[0]['file']);
		@unlink(WP_CONTENT_DIR.str_replace('_resized/','',$metadata[0]['file']));
	}
	wp_delete_post((int)$_GET['postid'],true);
	die();
}

/**
 *Update post
 *
 *@return void
 *@since 2.0.0
*/
function ajax_imstore_update_post(){
	if(!current_user_can("ims_manage_galleries")) return;
	check_ajax_referer("ims_ajax");
	$post = array(
		'ID' => $_GET['imgtid'],
		'menu_order' => $_GET['order'],
		'post_title' => $_GET['imgtitle'],
		'post_excerpt' => $_GET['caption'],
	);
	wp_update_post($post);
	die();
}

/**
 *add image to database
 *
 *@return void
 *@since 0.5.0
*/
function ajax_ims_flash_image_data(){
	global $wpdb,$current_user;
	
	if(!current_user_can('ims_add_galleries'))
		return false;
	
	@ini_set('memory_limit','256M');
	@ini_set('max_execution_time',1000);
	
	$galleid 	= $_GET['galleryid'];
	$filename 	= sanitize_file_name($_GET['imagename']);
	$abspath 	= $_GET['filepath'];
	$filetype 	= wp_check_filetype($filename);
	$despath 	= dirname($abspath).'/_resized';
	$relative 	= str_replace(str_replace('\\','/',WP_CONTENT_DIR),'',str_replace('\\','/',$despath.'/'.$filename));
	$guid 		= WP_CONTENT_URL.$relative;
	if(!file_exists($despath)) @mkdir($despath,0775);

	//if image exist dont't load it
	if($wpdb->get_var($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE 1=1 AND guid = %s",$guid))) return;
	include_once(ABSPATH.'wp-admin/includes/image.php');
	
	//resize images
	$img_sizes = get_option('ims_dis_images');
	$img_sizes['thumbnail']['name'] = "thumbnail";
	$img_sizes['thumbnail']['crop'] = '1';
	$img_sizes['thumbnail']['q'] 	= '95';
	$img_sizes['thumbnail']['w'] 	= get_option("thumbnail_size_w");
	$img_sizes['thumbnail']['h'] 	= get_option("thumbnail_size_h");
	
	$downloadsizes = get_option('ims_download_sizes');
	if(is_array($downloadsizes)) $img_sizes += $downloadsizes;
	
	@copy($abspath,"$despath/$filename");
	$orininfo = @getimagesize($abspath);
	
	$metadata['file'] 	= $relative;
	$metadata['width'] 	= $orininfo[0];
	$metadata['height'] = $orininfo[1];
	$metadata['url'] 	= $guid;
	$metadata['path'] 	= "$despath/$filename";
	
	list($uwidth,$uheight) = wp_constrain_dimensions($metadata['width'],$metadata['height'],100,100);
	$metadata['hwstring_small'] = "height='$uheight' width='$uwidth'";
	
	switch($orininfo['channels']){ 
		case 1:$metadata['color'] = 'BW'; break;
		case 3:$metadata['color'] = 'RGB'; break;
		case 4:$metadata['color'] = 'CMYK'; break;
		default:$metadata['color'] = __('Unknown',ImStore::domain);
	}
	
	foreach($img_sizes as $img_size){
		$resized = image_resize($abspath,$img_size['w'],$img_size['h'],$img_size['crop'],null,$despath,$img_size['q']);
		if(!is_wp_error($resized) && $resized && $info = @getimagesize($resized)){
			$imgname = basename($resized);
		}else{
			$info 		= getimagesize($abspath);
			$imgname 	= basename($abspath);
		}
		
		$data = array(
			'file'	=>$imgname,
			'width'	=>$info[0],
			'height'=>$info[1],
			'url'	=> dirname($guid)."/$imgname",
			'path'	=> dirname($abspath)."/$imgname",
		);
		$metadata['sizes'][$img_size['name']] = $data;
		$metadata['image_meta'] = wp_read_image_metadata($abspath);		
	}
	$title = ($metadata['image_meta']['title']) ? $metadata['image_meta']['title'] : $filename;
	$attachment = array(
		'guid' => $guid,
		'post_title' => $title,
		'post_parent' => $galleid,
		'post_type' => 'ims_image',
		'post_status' => 'publish',
		'post_mime_type'=> $filetype['type'],
		'post_excerpt' => $metadata['image_meta']['caption'],
	);
	
	$attach_id = wp_insert_post($attachment);
	
	if(empty($attach_id)) return;
	wp_update_attachment_metadata($attach_id,$metadata);
	
	$hidden 	= implode('|',(array)get_user_option('manageims_gallerycolumnshidden'));
	$imgnonce 	= '&_wpnonce='.wp_create_nonce("ims_edit_image")."&TB_iframe=true&height=570";
	$columns 	= array(
					'cb' => '<input type="checkbox">',
					'imthumb' => __('Thumbnail',ImStore::domain),'immetadata' => __('Metadata',ImStore::domain),
					'imtitle' => __('Title/Caption',ImStore::domain),'imdate' => __('Date',ImStore::domain),
					'imauthor'=> __('Author',ImStore::domain),'imorder'	=> __('Order',ImStore::domain),
					'imageid' => __('ID',ImStore::domain),
				);
	$row = '<tr id="item-'.$attach_id.'" class="iedit">';
	foreach($columns as $key => $column){
		if($hidden) $class = (preg_match("/($hidden)/i",$key))?' hidden':'';
		switch($key){
			case 'cb':
				$row .= '<th class="column-'.$key.' check-column"><input type="checkbox" name="galleries[]" value='.$attach_id.'" /></th>';
				break;
			case 'imthumb':
				$row .= '<td class="column-'.$key.$class.'">';
				$row .= '<a href="'.$attachment['guid'].'" class="thickbox" rel="gallery">';
				$row .= '<img src="'.dirname($attachment['guid']).'/'.$metadata['sizes']['mini']['file'].'" /></a>';
				$row .= '</td>';
				break;
			case 'immetadata':
				$row .= '<td class="column-'.$key.$class.'">';
				$row .= __('Format:',ImStore::domain).str_replace('image/','',$filetype['type']).'<br />';
				$row .= $metadata['width'].' x '.$metadata['height'].__(' pixels',ImStore::domain).'<br />';
				$row .= __('Color:',ImStore::domain).$metadata['color'].'<br />';
				$row .= '<div class="row-actions" id="media-head-'.$attach_id.'">';
				$row .= '<a href="'.IMSTORE_ADMIN_URL.'image-edit.php?editimage='.$attach_id.$imgnonce.'" class="thickbox">'.__('Edit',ImStore::domain).'</a> | ';
				$row .= '<a href="#'.$attach_id.'" rel="update" class="imsupdate">'.__('Update',ImStore::domain).'</a> | ';
				$row .= '<a href="#'.$attach_id.'" rel="trash" class="imstrash">'.__('Trash',ImStore::domain).'</a>';
				$row .= '</div>';
				$row .= '</td>';
				break;
			case 'imtitle':
				$row .= '<td class="column-'.$key.$class.'">';
				$row .= '<input type="text" name="img_title['.$attach_id.']" value="'.$title.'" class="inputxl"/>';
				$row .= '<textarea name="img_excerpt['.$attach_id.']" rows="3" class="inputxl">'.$metadata['image_meta']['caption'].'</textarea>';
				$row .= '</td>';
				break;
			case 'imauthor':
				$row .= '<td class="column-'.$key.$class.'">'.$current_user->display_name.'</td>';
				break;
			case 'imdate':
				//$row .= '<td class="column-'.$key.$class.'">'.date_i18n(get_option('date_format'),strtotime($image->post_date)).'</td>';
				break;
			case 'imorder':
				$row .= '<td class="column-'.$key.$class.'">';
				$row .= '<input type="text" name="menu_order['.$attach_id.']" class="inputxl" />';
				$row .= '</td>';
				break;
			case 'imageid':
				$row .= '<td class="column-'.$key.$class.'">'.sprintf("%05d",$attach_id).'</td>';
				break;
			default:
				$row .= '<td class="column-'.$key.$class.'">&nbsp;</td>';
		}
	}
	echo $row .= '</tr>';
}

/**
 *Change the image status
 *
 *@return void
 *@since 2.0.0
*/
function ajax_imstore_edit_image_status(){
	if(!current_user_can("ims_manage_galleries"))return;
	check_ajax_referer("ims_ajax");
	wp_update_post(array("ID" => trim($_GET['imgid']),'post_status' => $_GET['status']));
	die();
}

/**
*Add images to favorites
*
*@return void
*@since 0.5.0
*/
function ajax_ims_add_images_to_favorites(){
	check_ajax_referer("ims_ajax_favorites");
	global $user_ID,$ImStore; $id = (int)$_GET['galid'];
	if(empty($_GET['imgids']) || empty($id)){
		echo __('Please,select an image',ImStore::domain).'|ims-error'; return;
	}elseif(is_user_logged_in()){
		$new 	= explode(',',$_GET['imgids']);
		foreach ($new as $id) $dec_ids[] = $ImStore->admin->decrypt_id($id);
		$join 	= trim(get_user_meta($user_ID,'_ims_favorites',true).",".implode(',',$dec_ids),','); 
		$ids	= implode(',',array_unique(explode(',',$join)));
		update_user_meta($user_ID,'_ims_favorites',$ids);
	}else{ 
		$new 	= explode(',',$_GET['imgids']);
		foreach ($new as $id) $dec_ids[] = $ImStore->admin->decrypt_id($id);
		$join 	= trim($_COOKIE['ims_favorites_'.COOKIEHASH].",".implode(',',$dec_ids),',');
		$ids	= implode(',',array_unique(explode(',',$join)));
		setcookie('ims_favorites_'.COOKIEHASH,$ids,0,COOKIEPATH);
	}
	if(count($new) < 2) echo __('Image added to favorites',ImStore::domain).'|ims-success';
	else echo sprintf(__('%d images added to favorites',ImStore::domain),count($new)).'|ims-success';
} 

/**
 *Remove images from favorites
 *
 *@return void
 *@since 0.5.0
 */
function ajax_ims_remove_images_from_favorites(){
	check_ajax_referer("ims_ajax_favorites");
	global $user_ID,$ImStore; $id = intval($_GET['galid']);
	if(empty($_GET['imgids']) || empty($id)){
		echo __('Please,select an image',ImStore::domain).'|ims-error'; return;
	}elseif(is_user_logged_in()){
		$new 	= explode(',',$_GET['imgids']);
		foreach ($new as $id) $dec_ids[] = $ImStore->admin->decrypt_id($id);
		$join 	= array_flip(explode(',',trim(get_user_meta($user_ID,'_ims_favorites',true),',')));
		foreach ($dec_ids as $remove) unset($join[$remove]);
		$ids	= implode(',',array_flip($join));
		update_user_meta($user_ID,'_ims_favorites',$ids);
	}else{
		$new 	= explode(',',$_GET['imgids']);
		foreach ($new as $id) $dec_ids[] = $ImStore->admin->decrypt_id($id);
		$join 	= array_flip(explode(',',trim($_COOKIE['ims_favorites_'.COOKIEHASH],',')));
		foreach ($dec_ids as $remove) unset($join[$remove]);
		$ids	= implode(',',array_flip($join));
		setcookie('ims_favorites_'.COOKIEHASH,$ids,0,COOKIEPATH);
	}
	if(count($new) < 2) echo __('Image removed from favorites',ImStore::domain).'|ims-success';
	else echo sprintf(__('%d images removed from favorites',ImStore::domain),count($new)).'|ims-success';
}

/**
 * modify image size mini when thumbnail 
 * is modify by the image edit win
 * 
 * @return void
 * @since 0.5.5
 */
function ajax_ims_edit_image_mini(){
	
	$post_id = intval($_GET['imgid']);
	check_ajax_referer("image_editor-{$post_id}");
	include_once(ABSPATH . 'wp-admin/includes/image-edit.php');

	$post = get_post($post_id);
	@ini_set('memory_limit','256M');
	$img = load_image_to_edit($post_id,$post->post_mime_type);

	if (!is_resource($img)) return $return;

	$fwidth 	= !empty($_REQUEST['fwidth'])?intval($_REQUEST['fwidth']):0;
	$fheight 	= !empty($_REQUEST['fheight'])?intval($_REQUEST['fheight']):0;
	$target 	= !empty($_REQUEST['target'])?preg_replace('/[^a-z0-9_-]+/i','',$_REQUEST['target']):'';
	$scale 		= !empty($_REQUEST['do']) && 'scale' == $_REQUEST['do'];

	if ($scale && $fwidth > 0 && $fheight > 0) {
		$sX = imagesx($img);
		$sY = imagesy($img);

		// check if it has roughly the same w / h ratio
		$diff = round($sX / $sY,2) - round($fwidth / $fheight,2);
		if (-0.1 < $diff && $diff < 0.1) {
			// scale the full size image
			$dst = wp_imagecreatetruecolor($fwidth,$fheight);
			if (imagecopyresampled($dst,$img,0,0,0,0,$fwidth,$fheight,$sX,$sY)) {
				imagedestroy($img);
				$img = $dst;
				$scaled = true;
			}
		}

	} elseif (!empty($_REQUEST['history'])) {
		$changes = json_decode(stripslashes($_REQUEST['history']));
		if ($changes) $img = image_edit_apply_changes($img,$changes);
	} else {
		return $return;
	}
	
	// generate new filename
	$path = get_attached_file($post_id);
	$path_parts = pathinfo52($path);
	$filename = $path_parts['filename'];
	$suffix = time() . rand(100,999);
	
	while(true) {
		$filename = preg_replace('/-e([0-9]+)$/','',$filename);
		$filename .= "-e{$suffix}";
		$new_filename = "{$filename}.{$path_parts['extension']}";
		$new_path = "{$path_parts['dirname']}/$new_filename";
		if (file_exists($new_path)) $suffix++;
		else break;
	}
	
	if (!wp_save_image_file($new_path,$img,$post->post_mime_type,$post_id)) 
		return $return;
	
	$img_size['w'] 		= get_option("mini_size_w");
	$img_size['h']		= get_option("mini_size_h");
	$img_size['crop'] 	= get_option("mini_crop");
	
	//create image
	$resized 	= image_resize($new_path,$img_size['w'],$img_size['h'],$img_size['crop']);
	$info 		= getimagesize($resized);
	$metadata 	= array('file' => basename($resized),'width' => $info[0],'height' => $info[1]);
		
	if ($resized){
		$meta = wp_get_attachment_metadata($post_id);
		$meta['sizes']['mini'] = $metadata;
		wp_update_attachment_metadata($post_id,$meta);
	}
	
	@unlink($new_path);
	@imagedestroy($img);
}


switch($_GET['action']){
	case 'flashimagedata':
		ajax_ims_flash_image_data();
		break;
	case 'deletelist':
		ajax_imstore_pricelist_delete();
		break;
	case 'deleteimage':
		ajax_imstore_delete_post();
		break;
	case 'upadateimage':
		ajax_imstore_update_post();
		break;
	case 'deletepackage':
		ajax_imstore_delete_post();
		break;
	case 'editimstatus':
		ajax_imstore_edit_image_status();
		break;
	case 'favorites':
		ajax_ims_add_images_to_favorites();
		break;
	case 'remove-favorites':
		ajax_ims_remove_images_from_favorites();
		break;
	case 'edit-mini-image':
		ajax_ims_edit_image_mini();
		break;
	default: die();
}

	 
?>