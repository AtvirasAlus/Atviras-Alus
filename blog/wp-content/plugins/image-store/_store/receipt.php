<?php 

/**
 *Complete - Thank you page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0 
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die( );

$this->orderid = isset( $_POST['custom'] ) ?  $_POST['custom'] : $this->orderid;
$nonce 	= "_wpnonce=" . wp_create_nonce( "ims_download_img" );
$cart = get_post_meta( $this->orderid, '_ims_order_data', true );
$data = get_post_meta( $this->orderid, '_response_data', true);

$this->subtitutions = array(
	$data['mc_gross'], $data['payment_status'], get_the_title( $this->orderid ),
	$cart['shipping'], $cart['tracking'], $cart['gallery_id'], $data['txn_id'],
	$data['last_name'], $data['first_name'], $data['payer_email'],
);
		
$output .= '<div class="ims-innerbox">
	 <div class="thank-you-message">' .
		(  make_clickable( wpautop( stripslashes( preg_replace( $this->opts['tags'], $this->subtitutions, $this->opts['thankyoureceipt'] )) ) ) )
	 . '</div>
</div>';

if( isset( $data['mc_gross'] ) && $data['mc_gross'] == number_format( $cart['total'], 2 ) && empty( $_POST['enoticecheckout'] ) ){	
	foreach( $cart['images'] as $id => $sizes ){
		$enc = $this->encrypt_id( $id );	
		foreach( $sizes as $size => $colors){
			foreach( $colors as $color => $item){
				if( isset( $item['download'] ))
				 $downlinks[] = '<a href="' . IMSTORE_ADMIN_URL ."/download.php?$nonce&amp;img=".$enc."&amp;sz=$size&amp;c=$color". '" 
				 class="ims-download">'. get_the_title( $id ) ." ". $this->color[$color] . " </a>";
			}
		}
	}
	
	if( !empty( $downlinks ) ){
		$output .= '<div class="imgs-downloads">';
		$output .= '<h4 class="title">Downloads</h4>';
		$output .= '<ul class="download-links">';
		foreach( $downlinks as $link )
			$output .= "<li>$link</li>\n";
		$output .= "</ul>\n</div>";
	}

}

setcookie( 'ims_orderid_' . COOKIEHASH,  false, (time()-315360000), COOKIEPATH, COOKIE_DOMAIN );
$output .= '<div class="cl"></div>';