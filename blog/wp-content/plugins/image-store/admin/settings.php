<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 0.5.0
*/

if(!current_user_can('ims_change_settings')) 
	die();

//Update general settings
if(!empty($_POST['update-general'])){
	check_admin_referer('ims_settings');
	$_POST = array_diff_key($_POST,array('_wpnonce'=>'','_wp_http_referer'=>'','updateoption'=>''));
	foreach(array('deletefiles','imswidget','mediarss','disablestore','stylesheet','hidephoto','hideslideshow') as $box)
		if(empty($_POST[$box])) $_POST[$box] = '';
	
	if(isset($_POST['ims_searchable']))	update_option('ims_searchable',$_POST['ims_searchable']);
	update_option('ims_front_options',wp_parse_args($_POST,$this->opts));
	wp_redirect($pagenowurl.'&ms=4');	
}


//Update settings
if(!empty($_POST['update'])){
	check_admin_referer('ims_settings');
	$_POST = array_diff_key($_POST,array('_wpnonce'=>'','_wp_http_referer'=>'','updateoption'=>''));
	
	if(!preg_match('/^\//',$_POST['galleriespath']) && isset($_POST['galleriespath']))
		$_POST['galleriespath'] = "/{$_POST['galleriespath']}";
	
	foreach(array('securegalleries','colorbox','wplightbox','disablesepia','disablebw',) as $box)
		if(empty($_POST[$box]) && isset($_POST['galleriespath'])) $_POST[$box] = '';
	
	if(isset($_POST['gateway'])){
		unset($this->opts['requiredfields']);
		foreach($this->opts['checkoutfields'] as $key => $label)
			if(!empty($_POST['required'][$key])) $this->opts['requiredfields'][] = $key;
	}
	
	if(empty($_POST['autoStart']) && !isset($_POST['galleriespath'])) $_POST['autoStart'] = '';
	update_option('ims_front_options',wp_parse_args($_POST,$this->opts));
	wp_redirect($pagenowurl.'&ms=4');	
}

//update/add user capabilities
if(!empty($_POST['updateuser']) && !empty($_POST['ims_user'])){
	check_admin_referer('ims_caps_settings');
	foreach($this->useropts['caplist'] as $cap)
		if(!empty($_POST[$cap])) $newcaps[$cap] = 1;
	update_usermeta($_POST['ims_user'],'ims_user_caps',$newcaps);
	wp_redirect($pagenowurl.'&ms=2&userid='.$_GET['userid'].'#caps_settings');	
}

//reset options
if(!empty($_POST['resetsettings'])){
	check_admin_referer('ims_reset_settings');
	include_once(IMSTORE_ABSPATH.'/admin/install.php');
	ImStoreInstaller::imstore_default_options();
	wp_redirect($pagenowurl.'&ms=3#reset_settings');	
}

//uninstall Image Store
if(!empty($_POST['uninstall_ims'])){
	check_admin_referer('ims_reset_settings');
	include_once(IMSTORE_ABSPATH.'/admin/install.php');
	ImStoreInstaller::imstore_uninstall();
}

//save image settings 
if(!empty($_POST['updateimages'])){ 
	check_admin_referer('ims_image_settings');
	
	$sizes 		= get_option('ims_sizes');
	$_POST 		= array_diff_key($_POST,array('_wpnonce'=>'','_wp_http_referer'=>'','updateimages'=>''));
	
	$x=0;
	do{
		if(isset($_POST['imgid_'.$x]))
			unset($_POST['imagesize_'.$x]);
		if($_POST['imagesize_'.$x]['name']){
			$downloads[] = $_POST['imagesize_'.$x];
			update_option($_POST['imagesize_'.$x]['name']."_crop",0);
			update_option($_POST['imagesize_'.$x]['name']."_size_h",$_POST['imagesize_'.$x]['h']);
			update_option($_POST['imagesize_'.$x]['name']."_size_w",$_POST['imagesize_'.$x]['w']);
			$sizes[] = array('name' => $_POST['imagesize_'.$x]['w'].'x'.$_POST['imagesize_'.$x]['h'],'unit' => 'px',);
		}
		unset($_POST['imgid_'.$x]);
		unset($_POST['imagesize_'.$x]);
		$x++;
	}while(!empty($_POST['imagesize_'.$x]));
	
	$preview['preview']['crop'] = 0;
	$preview['preview']['name'] = 'preview';
	$preview['preview'] += $_POST['preview'];
	$quality = ($_POST['preview']['q']>100) ? 100 : $_POST['preview']['q'];
	
	update_option('preview_crop',0);
	update_option('preview_size_q',$quality);
	update_option('preview_size_w',$_POST['preview']['w']);
	update_option('preview_size_h',$_POST['preview']['h']);
	
	$imgsizes = get_option('ims_dis_images');
	$imgsizes['preview'] = array('name' => 'preview','w' => $_POST['preview']['w'],'h' => $_POST['preview']['h'],'q' => $quality,'crop' => 0);
	
	unset($_POST['preview']);
	
	update_option('ims_sizes',$sizes); 
	update_option('ims_dis_images',$imgsizes);
	update_option('ims_download_sizes',$downloads);
	update_option('ims_front_options',wp_parse_args($_POST,$this->opts));
	wp_redirect($pagenowurl.'&ms=4#image-settings');	
}

$currencies = array (
	'AUD' =>__('Australian Dollar',ImStore::domain),
	'BRL' =>__('Brazilian Real',ImStore::domain),
	'CAD' =>__('Canadian Dollar',ImStore::domain),
	'CZK' =>__('Czech Koruna',ImStore::domain),
	'DKK' =>__('Danish Krone',ImStore::domain),
	'EUR' =>__('Euro',ImStore::domain),
	'HKD' =>__('Hong Kong Dollar',ImStore::domain),
	'HUF' =>__('Hungarian Forint',ImStore::domain),
	'ILS' =>__('Israeli New Sheqel',ImStore::domain),
	'JPY' =>__('Japanese Yen',ImStore::domain),
	'MYR' =>__('Malaysian Ringgit',ImStore::domain),
	'MXN' =>__('Mexican Peso',ImStore::domain),
	'NOK' =>__('Norwegian Krone',ImStore::domain),
	'NZD' =>__('New Zealand Dollar',ImStore::domain),
	'PHP' =>__('Philippine Peso',ImStore::domain),
	'PLN' =>__('Polish Zloty',ImStore::domain),
	'GBP' =>__('Pound Sterling',ImStore::domain),
	'SGD' =>__('Singapore Dollar',ImStore::domain),
	'ZAR' =>__('South African Rands',ImStore::domain),
	'SEK' =>__('Swedish Krona',ImStore::domain),
	'CHF' =>__('Swiss Franc',ImStore::domain),
	'TWD' =>__('Taiwan New Dollar',ImStore::domain),
	'THB' =>__('Thai Baht',ImStore::domain),
	'TRY' =>__('Turkish Lira',ImStore::domain),
	'USD' =>__('U.S.Dollar',ImStore::domain),
);

?>

<ul class="ims-tabs add-menu-item-tabs">
	<li class="tabs"><a href="#general"><?php _e('General',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#gallery-settings"><?php _e('Gallery',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#image-settings"><?php _e('Image',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#slideshow-settings"><?php _e('Slideshow',ImStore::domain)?></a></li>
	<?php if(!$this->opts['disablestore']){?>
	<li class="tabs"><a href="#payment-settings"><?php _e('Payment',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#checkout-settings"><?php _e('Checkout',ImStore::domain)?></a></li>
	<?php } if(current_user_can('ims_change_permissions')){?>
	<li class="tabs"><a href="#caps_settings"><?php _e('User permissions',ImStore::domain)?></a></li>
	<?php }?>
	<li class="tabs"><a href="#reset_settings"><?php _e('Reset',ImStore::domain)?></a></li>
</ul>

<!-- General Settings -->
<div id="general" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl?>" >
	<table class="ims-table"> 
		<tbody>
		<tr>
			<td scope="row"><label for="deletefiles"> <?php _e('Delete image files',ImStore::domain)?> </label></td>
			<td><input type="checkbox" name="deletefiles" id="deletefiles" value="1" <?php checked('1',$this->_vr('deletefiles'))?> />
			<small> <?php _e('Delete files from server,when deleting a gallery/images',ImStore::domain)?> </small></td>
		</tr>
		<tr class="alternate">
			<td scope="row" width="22%"><label for="mediarss"><?php _e('Media RSS feed',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="mediarss" id="mediarss" value="1" <?php checked('1',$this->_vr('mediarss'))?>/>
			<small><?php _e('Add RSS feed the blog header for unsecured galleries.Useful for CoolIris/PicLens',ImStore::domain)?></small>
			</td>
		</tr>
		<tr>
			<td scope="row" width="22%"><label for="stylesheet"><?php _e('Use CSS',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="stylesheet" id="stylesheet" value="1" <?php checked('1',$this->_vr('stylesheet'))?>/>
			<small><?php _e('Use the Image Store stylesheet?',ImStore::domain)?></small>
			</td>
		</tr>
		<tr class="alternate">
			<td scope="row" width="22%"><label for="imswidget"><?php _e('Widget',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="imswidget" id="imswidget" value="1" <?php checked('1',$this->_vr('imswidget'))?>/>
			<small><?php _e('Enable the use of the Image Store Widget',ImStore::domain)?></small>
			</td>
		</tr>
		<tr>
			<td scope="row"><label for="disablestore"><?php _e('Disable store features',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="disablestore" id="disablestore" value="1" <?php checked('1',$this->_vr('disablestore'))?> />
				<small><?php _e('Use as a gallery manager only,not a store.',ImStore::domain)?></small></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="hidephoto"><?php _e('Hide "photo" link',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="hidephoto" id="hidephoto" value="1" <?php checked('1',$this->_vr('hidephoto'))?> />
				<small><?php _e('Hide photo link from store navigation.',ImStore::domain)?></small></td>
		</tr>
		<tr>
			<td scope="row"><label for="hideslideshow"><?php _e('Hide "slideshow" link',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="hideslideshow" id="hideslideshow" value="1" <?php checked('1',$this->_vr('hideslideshow'))?> />
				<small><?php _e('Hide slideshow link from store navigation.',ImStore::domain)?></small></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="ims_searchable"><?php _e('Searchable Galleries',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="ims_searchable" id="ims_searchable" value="1" <?php checked('1',get_option('ims_searchable'))?> />
				<small><?php _e('Allow galleries to show in search results.',ImStore::domain)?></small></td>
		</tr>
		<tr>
			<td scope="row"><label for="album_template"><?php _e('Album Template',ImStore::domain)?></label></td>
			<td><select name="album_template" id="album_template"><option value=""><?php _e('Default Template',ImStore::domain); ?></option>
			<option value="page.php" <?php selected('page.php',$this->_vr('album_template'))?>><?php _e('Page template',ImStore::domain); ?></option>
			</select></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="album_per_page"><?php _e('Albums per page',ImStore::domain)?></label></td>
			<td><input type="text" name="album_per_page" id="album_per_page" value="<?php $this->_v('album_per_page')?>" /></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="update-general" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/></td>
		</tr>
		</tbody>
	</table>
	<?php wp_nonce_field('ims_settings')?>
	</form>
</div>
			
<!-- Gallery Settings -->
<div id="gallery-settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'#gallery-settings'?>" >
	<table class="ims-table"> 
		<tbody>
		<tr> 
			<td scope="row" width="22%"><label for="galleriespath"><?php _e('Gallery folder path',ImStore::domain)?></label></td>
			<td>
			<input type="text" name="galleriespath" id="galleriespath" class="inputlg" value="<?php $this->_v('galleriespath')?>" ><br />
			<small><?php _e('Default folder path for all the galleries images',ImStore::domain)?></small>
			</td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="securegalleries"><?php _e('Secure galleries',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="securegalleries" id="securegalleries" value="1" <?php checked('1',$this->_vr('securegalleries'))?>/>
				<small><?php _e('Secure all new galleries with a password by default.',ImStore::domain)?></small></td>
		</tr>
		<tr>
			<td scope="row"><label for="colorbox"><?php _e('Colorbox',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="colorbox" id="colorbox" value="1" <?php checked('1',$this->_vr('colorbox'))?>/>
				<small><?php _e('Use the default ligthbox feature',ImStore::domain)?></small></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="wplightbox"><?php _e('Ligthbox for WP galleries',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="wplightbox" id="wplightbox" value="1" <?php checked('1',$this->_vr('wplightbox'))?>/>
				<small><?php _e('Use lightbox on WordPress Galleries.',ImStore::domain)?></small></td>
		</tr>
		<tr>
			<td scope="row"><label for="disablebw"><?php _e('Disable B and W',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="disablebw" id="disablebw" value="1" <?php checked('1',$this->_vr('disablebw'))?> />
				<small><?php _e('Disable black and white color option.',ImStore::domain)?></small></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="disablesepia"><?php _e('Disable Sepia ',ImStore::domain)?></label></td>
			<td><input type="checkbox" name="disablesepia" id="disablesepia" value="1" <?php checked('1',$this->_vr('disablesepia'))?> />
				<small><?php _e('Disable sepia color option.',ImStore::domain)?></small></td>
		</tr>
		<tr>
			<td scope="row"><label for="gallery_template"><?php _e('Gallery Template',ImStore::domain)?></label></td>
			<td><select name="gallery_template" id="gallery_template"><option value=""><?php _e('Default Template',ImStore::domain); ?></option>
			<?php page_template_dropdown($this->_vr('gallery_template'))?></select></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="imgs_per_page"><?php _e('Images per page',ImStore::domain)?></label></td>
			<td><input type="text" name="imgs_per_page" id="imgs_per_page" value="<?php $this->_v('imgs_per_page')?>" /></td>
		</tr>
		<tr>
			<td scope="row"><label for="galleryexpire"><?php _e('Galleries expire after ',ImStore::domain)?></label></td>
			<td><input type="text" name="galleryexpire" id="galleryexpire" class="inputxm" value="<?php $this->_v('galleryexpire')?>"/>
				(<?php _e('days')?>)</td>
		</tr>
		<tr class="alternate">
			<td valign="top"><?php _e('Sort images',ImStore::domain)?></td>
			<td><label><input name="imgsortorder" type="radio" value="menu_order" <?php checked('menu_order',$this->_vr('imgsortorder'))?> />
				<?php _e('Custom order',ImStore::domain)?></label><br />
				<label><input name="imgsortorder" type="radio" value="post_excerpt" <?php checked('post_excerpt',$this->_vr('imgsortorder'))?> />
				<?php _e('Caption',ImStore::domain)?></label><br />
				<label><input name="imgsortorder" type="radio" value="post_title" <?php checked('post_title',$this->_vr('imgsortorder'))?> />
				<?php _e('Image title',ImStore::domain)?></label><br />
				<label><input name="imgsortorder" type="radio" value="post_date" <?php checked('post_date',$this->_vr('imgsortorder'))?>/>
				<?php _e('Image date',ImStore::domain)?></label></td>
		</tr>
		<tr>
			<td><?php _e('Sort direction',ImStore::domain)?>:</td>
			<td><label><input name="imgsortdirect" type="radio" value="ASC" <?php checked('ASC',$this->_vr('imgsortdirect'))?>/>
				<?php _e('Ascending',ImStore::domain)?></label>
				<label><input name="imgsortdirect" type="radio" value="DESC" <?php checked('DESC',$this->_vr('imgsortdirect'))?>/>
				<?php _e('Descending',ImStore::domain)?></label></td>
		</tr>
		<tr>
			<td>&nbsp;</td>
			<td><input type="submit" name="update" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/></td>
		</tr>
		</tbody> 
	</table>
	<?php wp_nonce_field('ims_settings')?>
	</form>
</div>

<!-- Image Settings -->
<div id="image-settings" class="ims-box">
<form method="post" action="<?php echo $pagenowurl.'#image-settings'?>" >
	<table class="ims-table"> 
		<tbody>
			<tr class="t alternate">
				<td colspan="2" scope="row"><?php _e('Image preview size(pixels)')?></td>
				<td colspan="5"><label><?php _e('Max Width',ImStore::domain)?>
					<input type="text" name="preview[w]" class="inputsm" value="<?php echo get_option('preview_size_w')?>" /></label>
					<label><?php _e('Max Height',ImStore::domain)?>
					<input type="text" name="preview[h]" class="inputsm" value="<?php echo get_option('preview_size_h')?>" /></label>
					<label><?php _e('Quality',ImStore::domain)?>
					<input type="text" name="preview[q]" class="inputsm" value="<?php echo get_option('preview_size_q')?>" /></label>
					(1-100) </td>
			</tr>
			<tr><td scope="row" colspan="7">&nbsp;</td></tr>
			<tr>
				<td colspan="2" scope="row">&nbsp;</td>
				<td colspan="5">
				<label><input type="radio" name="watermark" value="0" <?php checked('0',$this->_vr('watermark'))?> />
				<?php _e('No watermark',ImStore::domain)?></label> &nbsp;
				<label><input type="radio" name="watermark" value="1" <?php checked('1',$this->_vr('watermark'))?> /> 
				<?php _e('Use text as watermark',ImStore::domain)?></label> &nbsp;
				<label><input type="radio" name="watermark" value="2" <?php checked('2',$this->_vr('watermark'))?> />
				<?php _e('Use image as watermark',ImStore::domain)?></label></td>
			</tr>
			<tr class="t alternate">
				<td colspan="2" scope="row"><label for="watermarktext"><?php _e('Watermark text',ImStore::domain)?></label></td>
				<td colspan="5">
				<input type="text" name="watermarktext" id="watermarktext" class="input" value="<?php $this->_v('watermarktext')?>"/>
				<label><?php _e('Color',ImStore::domain)?>
				<input type="text" name="textcolor" class="inputxm" value="<?php $this->_v('textcolor')?>" /> <small>Hex </small></label>
				<label><?php _e('Font size',ImStore::domain)?>
				<input type="text" name="fontsize" class="inputxm" value="<?php $this->_v('fontsize')?>" /></label>
				<label><?php _e('Transparency',ImStore::domain)?>
				<input type="text" name="transperency" class="inputxm" value="<?php $this->_v('transperency')?>" />(0-127)</label></td>
			</tr>
			<tr>
				<td colspan="2" scope="row">
				<label for="watermarkurl"><a id="addwatermarkurl"><?php _e('Watermark URL',ImStore::domain)?></a></label></td>
				<td colspan="5">
				<input type="text" name="watermarkurl" id="watermarkurl" class="inputlg" value="<?php $this->_v('watermarkurl')?>"/></td>
			</tr>
			<tr><td scope="row" colspan="7">&nbsp;</td></tr>
			<tr class="alternate"><td scope="row" colspan="7"><?php _e('Downloadable image sizes',ImStore::domain)?></td></tr>
			<tr class="t">
				<td scope="row"><?php _e('Delete',ImStore::domain)?></td>
				<td scope="row"><?php _e('Image Size',ImStore::domain)?></td>
				<td scope="row"><?php _e('Width',ImStore::domain)?></td>
				<td scope="row"><?php _e('Height',ImStore::domain)?></td>
				<td scope="row"><?php _e('Quality',ImStore::domain)?></td>
				<td scope="row"><?php _e('Unit',ImStore::domain)?></td>
				<td scope="row"><input type="button" id="addimagesize" value="<?php _e('Add image size',ImStore::domain)?>" class="button" /></td>
			</tr>
			<?php if($sizes = get_option('ims_download_sizes')):for($x=0; $x<count($sizes); $x++):?>
			<tr class="t image-size">
			<td scope="row"><input type="checkbox" name="imgid_<?php echo $x?>" class="inputmd" value="1" /></td>
				<td><input type="text" name="imagesize_<?php echo $x?>[name]" class="inputmd" value="<?php echo $sizes[$x]['name']?>" /></td>
				<td><label><input type="text" name="imagesize_<?php echo $x?>[w]" class="inputsm" value="<?php echo $sizes[$x]['w']?>" /></label></td>
				<td><label><input type="text" name="imagesize_<?php echo $x?>[h]" class="inputsm" value="<?php echo $sizes[$x]['h']?>" /></label></td>
				<td><label><input type="text" name="imagesize_<?php echo $x?>[q]" class="inputsm" value="<?php echo $sizes[$x]['q']?>" />(%)</label></td>
				<td><?php _e('Pixels',ImStore::domain)?></td>
				<td>&nbsp;</td>
			</tr>
			<?php endfor; endif;?>
			<tr class="t ims-image-sizes">
				<td colspan="2" scope="row">&nbsp;</td>
				<td colspan="5" class="submit">
					<input type="submit" name="updateimages" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/>
				</td>
			</tr>
			</tbody> 
		</table>
	<?php wp_nonce_field('ims_image_settings')?>
</form>
</div>

<!-- Slideshow Settings -->
<div id="slideshow-settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'#slideshow-settings'?>" >
	<table class="ims-table"> 
		<tbody>
			<tr>
				<td scope="row" width="25%"> <label for="numThumbs"><?php _e('Number of thumbnails to show',ImStore::domain)?></label></td>
				<td><input type="text" name="numThumbs" id="numThumbs" class="input" value="<?php $this->_v('numThumbs')?>" /></td>
				<td width="25%"><label for="maxPagesToShow"><?php _e('Maximun number of pages',ImStore::domain)?></label></td>
				<td><input type="text" name="maxPagesToShow" id="maxPagesToShow" class="input" value="<?php $this->_v('maxPagesToShow')?>" /></td>
			</tr>
			<tr class="alternate">
				<td scope="row"> <label for="transitionTime"><?php _e('Transition time',ImStore::domain)?></label></td>
				<td>
					<input type="text" name="transitionTime" id="transitionTime" class="input" value="<?php $this->_v('transitionTime')?>" />
					<small><?php _e('1000 = 1 second',ImStore::domain)?></small>
				</td>
				<td><label for="slideshowSpeed"><?php _e('Slideshow speed',ImStore::domain)?></label></td>
				<td>
					<input type="text" name="slideshowSpeed" id="slideshowSpeed" class="input" value="<?php $this->_v('slideshowSpeed')?>" />
					<small><?php _e('1000 = 1 second',ImStore::domain)?></small>
				</td>
			</tr>
			<tr>
				<td scope="row" width="25%"> <label for="playLinkText"><?php _e('Play link text',ImStore::domain)?></label></td>
				<td><input type="text" name="playLinkText" id="playLinkText" class="inputmd" value="<?php $this->_v('playLinkText')?>" /></td>
				<td width="25%"><label for="pauseLinkTex"><?php _e('Pause link text',ImStore::domain)?></label></td>
				<td><input type="text" name="pauseLinkTex" id="pauseLinkTex" class="inputmd" value="<?php $this->_v('pauseLinkTex')?>" /></td>
			</tr>
			<tr class="alternate">
				<td scope="row" width="25%"> <label for="nextLinkText"><?php _e('Next link text',ImStore::domain)?></label></td>
				<td><input type="text" name="nextLinkText" id="nextLinkText" class="inputmd" value="<?php $this->_v('nextLinkText')?>" /></td>
				<td width="25%"><label for="prevLinkText"><?php _e('Previous link text',ImStore::domain)?></label></td>
				<td><input type="text" name="prevLinkText" id="prevLinkText" class="inputmd" value="<?php $this->_v('prevLinkText')?>" /></td>
			</tr>
			<tr>
				<td scope="row" width="25%"> <label for="nextPageLinkText"><?php _e('Next page link text',ImStore::domain)?></label></td>
				<td><input type="text" name="nextPageLinkText" id="nextPageLinkText" class="inputmd" value="<?php $this->_v('nextPageLinkText')?>" /></td>
				<td width="25%"><label for="prevPageLinkText"><?php _e('Previous page link text',ImStore::domain)?></label></td>
				<td><input type="text" name="prevPageLinkText" id="prevPageLinkText" class="inputmd" value="<?php $this->_v('prevPageLinkText')?>" /></td>
			</tr>
			<tr class="alternate">
				<td scope="row" width="25%"> <label for="closeLinkText"><?php _e('Close link text',ImStore::domain)?></label></td>
				<td><input type="text" name="closeLinkText" id="closeLinkText" class="inputmd" value="<?php $this->_v('closeLinkText')?>" /></td>
				<td width="25%"><label for="autoStart"><?php _e('Auto start?',ImStore::domain)?></label></td>
				<td><input type="checkbox" name="autoStart" id="autoStart" value="true" <?php checked('true',$this->_vr('autoStart'))?> /></td>
			</tr>
			<tr >
				<td scope="row" width="25%">&nbsp;</td>
				<td colspan="3"><input type="submit" name="update" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/></td>
				</tr>
		</tbody>
	</table>
	<?php wp_nonce_field('ims_settings')?>
	</form>
</div>

<?php if(!$this->opts['disablestore']){?>

<!-- Payment Settings -->
<div id="payment-settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'#payment-settings'?>" >
	<?php wp_nonce_field('ims_payment_settings')?>
	<table class="ims-table"> 
		<tbody>
		<tr>
			<td scope="row" width="20%"> <label for="symbol"><?php _e('Currency Symbol',ImStore::domain)?></label></td>
			<td colspan="2"><input type="text" name="symbol" id="symbol" class="inputxm" value="<?php $this->_v('symbol')?>" /></td>
		</tr>
		<tr class="t alternate">
			<td scope="row"> <label><?php _e('Currency Symbol Location',ImStore::domain)?></label></td>
			<td colspan="2">
				<label><input type="radio" value="1" name="clocal"<?php checked('1',$this->_vr('clocal'))?> />
				<?php _e('&#036;100',ImStore::domain)?></label>
				<label><input type="radio" value="2" name="clocal"<?php checked('2',$this->_vr('clocal'))?> />
				<?php _e('&#036; 100',ImStore::domain)?></label>
				<label><input type="radio" value="3" name="clocal"<?php checked('3',$this->_vr('clocal'))?> 		/>
				<?php _e('100&#036;',ImStore::domain)?></label>
				<label><input type="radio" value="4" name="clocal"<?php checked('4',$this->_vr('clocal'))?> 		/>
				<?php _e('100 &#036;',ImStore::domain)?></label>
			</td>
		</tr>
		<tr>
			<td><label for="currency"><?php _e('Default Currency:',ImStore::domain)?></label></td>
			<td colspan="2"><select name="currency" id="currency">	
				<option value="">Please Choose Default Currency</option>
				<?php foreach($currencies as $code => $currency){ ?>
				<option value="<?php echo $code?>"<?php selected($code,$this->_vr('currency'))?>><?php echo $currency?></option>
				<?php } ?>
			</select></td>
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="gateway"><?php _e('Gateway',ImStore::domain)?></label></td>
			<td scope="row" colspan="2">
			<select name="gateway" id="gateway">
				<option value="notification"<?php selected('notification',$this->_vr('gateway'))?>><?php _e('Email notification only',ImStore::domain)?> </option>
				<option value="paypalsand"<?php selected('paypalsand',$this->_vr('gateway'))?>><?php _e('Paypal Cart Sanbox',ImStore::domain)?> </option>
				<option value="paypalprod"<?php selected('paypalprod',$this->_vr('gateway'))?>><?php _e('Paypal Cart Production',ImStore::domain)?></option>
				<option value="googlesand"<?php selected('googlesand',$this->_vr('gateway'))?>><?php _e('Google Checkout Sandbox',ImStore::domain)?></option>
				<option value="googleprod"<?php selected('googleprod',$this->_vr('gateway'))?>><?php _e('Google Checkout Production',ImStore::domain)?></option>
			</select></td>
		</tr>
		<?php if($this->opts['gateway'] == 'googlesand' || $this->opts['gateway'] == 'googleprod'){?>
		<tr>
			<td scope="row"><label for="taxcountry"><a href="http://goes.gsfc.nasa.gov/text/web_country_codes.html"><?php _e('Country Code',ImStore::domain)?></a></label></td>
			<td colspan="3"><input type="text" name="taxcountry" id="taxcountry" class="inputlg" value="<?php echo stripslashes($this->_vr('taxcountry'))?>" /></td>
		</tr>
		<tr class="alternate">
			<td><label for="googleid"><?php _e('Google merchant ID',ImStore::domain)?></label></td>
			<td><input type="text" name="googleid" id="googleid" class="inputxl" value="<?php $this->_v('googleid')?>"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%"><label for="googlekey"><?php _e('Merchant key',ImStore::domain)?></label></td>
			<td><input type="text" name="googlekey" id="googlekey" class="inputxl" value="<?php $this->_v('googlekey')?>" /></td>
			<td>&nbsp;</td>
		</tr>
		
		<?php }elseif($this->opts['gateway'] == 'paypalsand' || $this->opts['gateway'] == 'paypalprod'){?>
		
		<tr>
			<td scope="row"><label for="paypalname"><?php _e('PayPal account email',ImStore::domain)?></label></td>
			<td><input type="text" name="paypalname" id="paypalname" class="inputxl" value="<?php $this->_v('paypalname')?>" /></td>
			<td>&nbsp;</td>
		</tr>
		<tr class="alternate">
			<td><label for="paypalsig"><?php _e('PayPal API signature',ImStore::domain)?></label></td>
			<td><input type="text" name="paypalsig" id="paypalsig" class="inputxl" value="<?php $this->_v('paypalsig')?>"/></td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td width="20%"><label for="paypalpass"><?php _e('PayPal API password',ImStore::domain)?></label></td>
			<td><input type="text" name="paypalpass" id="paypalpass" class="inputxl" value="<?php $this->_v('paypalpass')?>" /></td>
			<td>&nbsp;</td>
		</tr>
		
		<?php }elseif($this->opts['gateway'] == 'notification'){?>
		<tr>
			<td valign="top"><label for="shippingmessage"><?php _e('Shipping Message',ImStore::domain)?></label></td>
			<td colspan="3"><textarea name="shippingmessage" id="shippingmessage" rows="6" class="inputlg" ><?php echo stripslashes($this->_v('shippingmessage'))?></textarea></td>
		</tr>
		<tr class="alternate">
			<td valign="top"><?php _e('Required Fields',ImStore::domain)?></td>
			<td colspan="3" class="required">
			<?php 
			$req = implode(' ',(array)$this->opts['requiredfields']); 
			foreach($this->opts['checkoutfields'] as $key => $label){ 
				$checked = (preg_match("/$key/i",$req))?' checked="checked"':''?>
				<label><input name="required[<?php echo $key?>]" type="checkbox" value="1" <?php echo $checked?> /> <?php echo $label?></label>
			<?php }?>
			</td>
		</tr>
		<?php }?>
		
		<tr><td scope="row" colspan="3">&nbsp;</td></tr>
		<tr><td scope="row" colspan="3">&nbsp;</td></tr>
		<tr class="alternate">
			<td scope="row">&nbsp;</td>
			<td class="submit" colspan="2">
				<input type="submit" name="update" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/>
			</td>
		</tr>		
		</tbody>
	</table>
	<?php wp_nonce_field('ims_settings')?>
	</form>
</div>

<!-- Checkout Settings -->
<div id="checkout-settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'#checkout-settings'?>" >
	<table class="ims-table">
		<tbody>
		<tr>
			<td scope="row" width="24%"><label for="taxamount"><?php _e('Tax',ImStore::domain)?></label></td> 
			<td colspan="3"><input type="text" name="taxamount" id="taxamount" class="inputsm" value="<?php $this->_v('taxamount')?>" />
				<select name="taxtype" id="taxtype">
				<option value="percent"><?php _e('Percent',ImStore::domain)?></option>
				<option value="amount"><?php _e('Amount',ImStore::domain)?></option>
				</select> <small><?php _e('Set tax to 0 to remove tax calculation.',ImStore::domain)?></small></td> 
		</tr>
		<tr class="alternate">
			<td scope="row"><label for="notifyemail"><?php _e('Order Notification email(s)',ImStore::domain)?></label></td>
			<td colspan="3"><input type="text" name="notifyemail" id="notifyemail" class="inputlg" value="<?php echo stripslashes($this->_vr('notifyemail'))?>" /></td>
		</tr>
		<tr>
			<td scope="row"><label for="notifysubj"><?php _e('Order Notification subject',ImStore::domain)?></label></td>
			<td colspan="3"><input type="text" name="notifysubj" id="notifysubj" class="inputlg" value="<?php echo stripslashes($this->_vr('notifysubj'))?>" /></td>
		</tr>
		<tr class="alternate">
			<td valign="top"><label for="notifymssg"><?php _e('Order Notification message',ImStore::domain)?></label></td>
			<td colspan="3"><textarea name="notifymssg" id="notifymssg" rows="5" class="inputlg" ><?php echo stripslashes($this->_vr('notifymssg'))?></textarea><br />
			<small><?php _e('Tags:',ImStore::domain); echo str_replace('/','',implode(', ',(array)$this->opts['tags']))?> </small>
		</td>
		</tr>
		<tr><td scope="row" colspan="2">&nbsp;</td></tr>
		<tr>
			<td valign="top"><label for="thankyoureceipt"><?php _e('Purchase Receipt',ImStore::domain)?></label></td>
			<td colspan="3">
				<textarea name="thankyoureceipt" id="thankyoureceipt" rows="4" class="inputlg" ><?php echo stripslashes($this->_vr('thankyoureceipt'))?></textarea><br />
				<small><?php _e('Thank you message and receipt information',ImStore::domain)?></small>
			</td>
		</tr>
		<tr class="alternate">
			<td valign="top"><label for="termsconds"><?php _e('Terms and Conditions',ImStore::domain)?></label></td>
			<td colspan="3"><textarea name="termsconds" id="termsconds" rows="6" class="inputlg" ><?php echo stripslashes($this->_v('termsconds'))?></textarea></td>
		</tr>
		<tr>
			<td scope="row">&nbsp;</td>
			<td class="submit">
				<input type="submit" name="update" class="button-primary" value="<?php _e('Save',ImStore::domain)?>"/>
			</td>
		</tr>
		</tbody>
	</table>
	<?php wp_nonce_field('ims_settings')?>
	</form>
	</div>
<?php }?>

<?php if(current_user_can("ims_change_permissions")){?>
	
	<!-- Set User Permissions -->
	<div id="caps_settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'&userid='.$_GET['userid'].'#caps_settings'?>" >
	<?php wp_nonce_field('ims_caps_settings')?>
	<h4><label><?php ims_dowpdown_users($_GET['userid'])?></label></h4>
	<div class="permissions">
		<?php if($_GET['userid'])	:?>
		<?php $ims_user_caps = get_usermeta($_GET['userid'],'ims_user_caps')?>
		<?php foreach($this->useropts['caplist'] as $imscap):?>
			<label><input name="<?php echo $imscap?>" type="checkbox" value="1" <?php checked('1',$ims_user_caps[$imscap])?> />
			<?php echo ucwords(preg_replace('/(^ims_)|(_)/',' ',$imscap))?></label>
		<?php endforeach?>
		<?php endif?><div class="clear"></div>
	</div>
		<input type="submit" name="updateuser" class="button-primary" value="<?php _e('Save User',ImStore::domain)?>"/>
	</form>
	</div>
<?php }?>

<!-- Remove Plugin Data -->
<div id="reset_settings" class="ims-box">
	<form method="post" action="<?php echo $pagenowurl.'#reset_settings'?>" >
	<table class="ims-table">
		<tbody>
		<tr><td scope="row">&nbsp;</td></tr>
		<tr>
			<td scope="row">
			<input type="submit" name="resetsettings" value="<?php _e('Reset All Settings to defaults',ImStore::domain)?>" class="button"/>
			</td>
		</tr>
		<tr><td scope="row">&nbsp;</td></tr>
		<tr>
			<td scope="row" class="form-invalid error">
			<p><strong><?php _e('UNINSTALL IMAGE STORE WARNING',ImStore::domain)?>:</strong> </p>
			<?php _e('Once uninstalled,this cannot be undone.<strong> You should backup your database </strong> and image files before doing this,Just in case!!.',ImStore::domain)?>
			<?php _e("If you are not sure what are your doing,please don't do anything",ImStore::domain)?> !!!!
			<p><input name="uninstall_ims" type="submit" value="<?php _e('Uninstall Image Store',ImStore::domain)?>" class="button" id="uninstallImStore" /></p>
			</td>
		</tr>
		</tbody>
	</table>
	<?php wp_nonce_field('ims_reset_settings')?>
	</form>
</div>

<?php
/**
*Create a dropdown menu of the ImStore users
*
*@return void
*@since 0.5.0 
*/
function ims_dowpdown_users($selected = ''){
	global $wpdb;

	$q = "SELECT ID,user_login,meta_key,meta_value 
			FROM $wpdb->users 
			JOIN $wpdb->usermeta 
			ON $wpdb->users.ID = $wpdb->usermeta.user_id 
			WHERE meta_key = '{$wpdb->prefix}capabilities'
			AND meta_value NOT LIKE '%customer%' ";
			
	$output.= '<select name="ims_user" id="ims_user" >';
	$output.= '<option value="">&mdash; '.__('Select User',ImStore::domain).' &mdash;</option>';
	foreach($wpdb->get_results($q,'ARRAY_A') as $user):
		$roles = @unserialize($user['meta_value']);
		if(!$roles['administrator']):$userCount ++; 
			$output.= '<option value="'.$user['ID'].'" '.selected($user['ID'],$selected,false).' >'.$user['user_login'].'</option>';
		endif;
	endforeach;
	$output.= "</select>";
	
	if($userCount > 0) echo $output; 
	else echo __('No users to manage',ImStore::domain);
}
?>