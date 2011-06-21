<?php

/**
*galleries page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 0.5.0
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();
	
$pagenowurl .= "?post=".$_REQUEST['post']."&action=".$_REQUEST['action'];
if(!empty($_POST['screen_options'])){
	global $user_ID;
	update_user_meta($user_ID,$_POST['screen_options']['option'],$_POST['screen_options']['value']);
	wp_redirect( $pagenowurl );	
};

//print_r($this->opts);
global $status_labels; 
$columns 		= get_column_headers('ims_gallery');
$page			= (empty($_GET['p']))?1:(int)$_GET['p'];
$imgnonce 		= '&_wpnonce='.wp_create_nonce("ims_edit_image")."&TB_iframe=true&height=570";
$is_trash		= (isset($_GET['status'])) &&($_GET['status'] == 'trash');
$orderby 		= (empty($this->meta['_ims_sortby'][0]))?$this->opts['imgsortorder']:$this->meta['_ims_sortby'][0];
$order 			= (empty($this->meta['_ims_order'][0]))?$this->opts['imgsortdirect']:$this->meta['_ims_order'][0];

$images = query_posts(array(
	'order'	=> $order,
	'orderby' => $orderby,
	'paged' => $_REQUEST['p'],
	'post_type' => 'ims_image',
	'post_status' => $_GET['status'],
	'posts_per_page' => $this->per_page,
	'post_parent' =>(int)$_GET['post'],
));

$status_labels 	= array(
	'trash' 	=> __('Trash',ImStore::domain),
	'publish' 	=> __('Published',ImStore::domain),
);

//errors
$errors[1] = __('Upload failed.',ImStore::domain);
$errors[2] = __('Not a valid URL path',ImStore::domain);
$errors[3] = __('This is not a zip file.',ImStore::domain);
$errors[4] = __('Please enter a folder path.',ImStore::domain);
$errors[5] = __('There was an error extracting the images.',ImStore::domain);
$errors[6] = __('The folder doesn&#8217;t exist,please check your folder path.',ImStore::domain);
?>

<div class="tablenav">
	<ul class="subsubsub"><?php ims_gallery_count_links($status_labels)?></ul>
	<div class="alignright actions">
		<select name="actions">
			<option value="0" selected="selected"><?php _e('Actions',ImStore::domain)?></option>
			<?php if($is_trash){?>
			<option value="publish"><?php _e('Restore',ImStore::domain)?></option> 
			<option value="delete"><?php _e('Delete Permanently',ImStore::domain)?></option>
			<?php }else{?>
			<option value="trash"><?php _e('Move to Trash',ImStore::domain)?></option>
			<?php }?>
		</select>
		<input type="submit" value="<?php _e('Apply',ImStore::domain)?>" name="doactions" class="button action" />
	</div>
</div>

<?php 
if(isset($_GET['error']))
	echo '<div class="error"><p><strong>'.$errors[$_GET['error']].'</strong></p></div>';
?>
<table class="widefat post fixed ims-table sort-images">
	<thead><tr><?php print_column_headers('ims_gallery')?></tr></thead>
	<tr><td colspan="8" id="custom-queue"></td></tr>
	<tbody>
	<?php $row_class = ''; foreach($images as $image):$id = (int)$image->ID?>
		<tr id="item-<?php echo $id?>" class="iedit<?php echo $row_class?>">
		<?php 
		$meta = get_post_meta($id,'_wp_attachment_metadata'); 
		foreach($columns as $key => $column):
		?> 
		<?php switch($key){
			case 'cb':?>
			<th class="column-<?php echo "$key $class check-column"?>">
				<input type="checkbox" name="galleries[]" value="<?php echo $id?>" />
			</th>
			<?php break;
			case 'imthumb':?>
			<td class="column-<?php echo "$key $class"?>">
				<a href="<?php echo WP_CONTENT_URL.$meta[0]['file']?>" class="thickbox" rel="gallery">
				<img src="<?php echo dirname($image->guid).'/'.$meta[0]['sizes']['mini']['file']?>" /></a>
			</td>
			<?php break;
			case 'immetadata':?>
			<td class="column-<?php echo "$key $class"?>"> <?php ?>
				<?php echo __('Format:',ImStore::domain).str_replace('image/','',$image->post_mime_type)?><br />
				<?php echo $meta[0]['width'].' x '.$meta[0]['height'].__(' pixels',ImStore::domain)?><br />
				<?php echo __('Color:',ImStore::domain).$meta[0]['color']?><br />
				<div class="row-actions" id="media-head-<?php echo $id?>">
				<?php if($is_trash):?>
					<a href="<?php echo "#$id"?>" rel="delete" class="imsdelete"><?php _e('Delete',ImStore::domain)?></a> |
					<a href="<?php echo "#$id"?>" rel="publish" class="imsrestore"><?php _e('Restore',ImStore::domain)?></a>
				<?php else:?>
					<a href="<?php echo IMSTORE_ADMIN_URL."image-edit.php?editimage=$id$imgnonce"?>" class="thickbox"><?php _e('Edit',ImStore::domain)?></a> |
					<a href="<?php echo "#$id"?>" rel="update" class="imsupdate"><?php _e('Update',ImStore::domain)?></a> |
					<a href="<?php echo "#$id"?>" rel="trash" class="imstrash"><?php _e('Trash',ImStore::domain)?></a>
				<?php endif?>
				</div>
			</td>
			<?php break;
			case 'imtitle':?>
			<td class="column-<?php echo "$key $class"?>">
				<?php $disable = ($is_trash)?'disabled="disabled"':''?>
				<input type="text" name="img_title[<?php echo $id?>]" value="<?php echo $image->post_title?>" <?php echo $disable?> class="inputxl"/>
				<textarea name="img_excerpt[<?php echo $id?>]" rows="3" <?php echo $disable?> class="inputxl"><?php echo trim($image->post_excerpt)?></textarea>
			</td>
			<?php break;
			case 'imauthor':?>
			<td class="column-<?php echo "$key $class"?>">
				<?php echo $wpdb->get_var("SELECT display_name FROM $wpdb->users WHERE ID = $image->post_author")?>
			</td>
			<?php break;
			case 'imorder':?>
			<td class="column-<?php echo "$key $class"?>">
				<input type="text" name="menu_order[<?php echo $id?>]" <?php echo $disable?> value="<?php if($image->menu_order) echo $image->menu_order?>" class="inputxl" />
			</td>
			<?php break;
			case 'imageid':?>
			<td class="column-<?php echo "$key $class"?>"><?php echo sprintf("%05d",$id)?></td>
			<?php break;
			default:?>
			<td class="column-<?php echo "$key $class"?>">&nbsp;</td>
		<?php }?>	
		<?php endforeach;?>
		</tr>
		<?php $row_class = ' alternate' == $row_class?'':' alternate';?>
	<?php endforeach;?>
	</tbody>
</table>
<?php global $wp_query ?>
<input type="hidden" name="sort_count" value="<?php echo ((($this->per_page*$page)-$this->per_page)+1)?>" class="sort_count" />
<div class="tablenav"><?php $this->imstore_paging($this->per_page,$wp_query->found_posts)?></div>

<?php 

/**
*Display/Return galleries count by status
*
*@since 0.5.0
*return unit
*/
function ims_gallery_count_links($status_labels){
	global $wpdb,$pagenowurl; 
	$post = (int)$_GET['post'];
	$r = $wpdb->get_results(
		"SELECT post_status AS status,count(post_status) AS count 
		FROM $wpdb->posts WHERE post_type = 'ims_image' 
		AND post_status != 'auto-draft' AND post_parent = $post GROUP by post_status"
	);
	if(empty($r)) return $r;
	foreach($r as $obj){
		$count 	 = ($obj->status == $_GET['status'])?$obj->count:0;
		$current = ($obj->status == $_GET['status'])?' class="current"':'';
		if($obj->status == 'publish' && empty($_GET['status'])) $current = ' class="current"';
		$links[] = '<li class="status'.$obj->status.'"><a href="'.$pagenowurl.'&amp;status='.$obj->status.'"'.$current.'>'.$status_labels[$obj->status].' <span class="count">(<em>'.$obj->count.'</em>)</span></a></li>';
		if($obj->status != 'trash') $all += $obj->count;
	}
	echo implode(' | ',$links);
}
?>