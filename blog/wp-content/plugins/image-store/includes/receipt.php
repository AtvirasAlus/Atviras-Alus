<?php 

/**
 *Complete - Thank you page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 0.5.0 
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();

$sym 	= $this->opts['symbol']; 
$loc 	= $this->opts['clocal'];
$format = array('',"$sym%s","$sym %s","%s$sym","%s $sym"); 
$nonce 	= "_wpnonce=".wp_create_nonce("ims_download_img");

$cart = get_post_meta($_POST['custom'],'_ims_order_data',true);
$labels = array(
		'color' 	=> __('Color',ImStore::domain),
		'ims_bw'	=> __('Black & White',ImStore::domain),
		'ims_sepia' => __('Sepia',ImStore::domain),
);


?>

<div class="ims-innerbox">
	 <div class="thank-you-message">
		<?php echo make_clickable(wpautop(stripslashes(preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['thankyoureceipt'])))); ?>
	 </div>
</div>

<?php if($_POST['mc_gross'] == number_format($cart['total'],2)){	
	foreach($cart['images'] as $id => $sizes){
		$enc = $this->encrypt_id($id);	
		foreach($sizes as $size => $colors){
			foreach($colors as $color => $item){
				if($item['download'])
				 $downlinks[] = '<a href="'.IMSTORE_ADMIN_URL."download.php?$nonce&amp;img=".$enc."&amp;sz=$size&amp;c=$color". '" class="ims-download">'. get_the_title($id)." ". $labels[$color]." </a>";
			}
		}
	}
	
	if(!empty($downlinks)){
		echo '<div class="imgs-downloads">';
		echo '<h4 class="title">Downloads</h4>';
		echo '<ul class="download-links">';
		foreach($downlinks as $link)
			echo "<li>$link</li>\n";
		echo "</ul>\n</div>";
	}
	
} ?>

<?php setcookie('ims_orderid_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN); //destroy cookie cart ?>
<div class="cl"></div>