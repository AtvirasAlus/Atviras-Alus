<?php 

/**
 * ImStoreGoogleNotice - Google Notification
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 2.0.0
*/

class ImStoreGoogleNotice {
	
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 2.0.0
	 */	
	 function ImStoreGoogleNotice( ){
		$postdata = array();
                
		//dont change array order
		$this->subtitutions = array(
			$_POST['order-total'], $_POST['financial-order-state'], 
			get_the_title( $_POST['shopping-cart_merchant-private-data'] ),
			$_POST['order-adjustment_shipping_flat-rate-shipping-adjustment_shipping-cost'], 
			$this->cart['tracking'], $this->cart['gallery_id'], $_POST['google-order-number'],
			$_POST['buyer-billing-address_contact-name'], '', $_POST['buyer-billing-address_email'],
		);
		
		foreach($_POST as $i => $v)
			$postdata .= $i.'='.$v."\n";
			
		if( $_POST['_type'] == 'new-order-notification' ){
			do_action( 'ims_before_google_notice',  $postdata );
			$this->process_google_notice();
		}
		
		die( ) ;
	}
	
	/**
	 * Process Google Notification
	 *
	 * @return boolean
	 * @since 2.0.0
	 */
	function process_google_notice( ){
		
		if($_POST['order-adjustment_adjustment-total_currency'] != $this->opts['currency'])
			return false;
			
		$cartid =  (int)$_POST['shopping-cart_merchant-private-data'];	
		$data = get_post_meta( $cartid, '_ims_order_data', true );
		$total = ( $data['discounted'] ) ? $data['discounted'] : $data['total'];
		
		if($_POST['order-total'] != number_format($total,2))
			return false;
		
		$_POST['num_cart_items'] 	= $data['items'];
		$_POST['mc_gross'] 			= $_POST['order-total'];
		$_POST['payment_gross'] 	= $_POST['order-total'];
		$_POST['txn_id'] 				= $_POST['google-order-number'];
		$_POST['payment_status'] 	= $_POST['financial-order-state'];
		$_POST['payer_email']		= $_POST['buyer-billing-address_email'];
		$_POST['address_city'] 		= $_POST['buyer-shipping-address_city'];
		$_POST['ims_phone']		 	= $_POST['buyer-shipping-address_phone'];
		$_POST['address_state'] 	= $_POST['buyer-shipping-address_region'];
		$_POST['address_street'] 	= $_POST['buyer-shipping-address_address1'];
		$_POST['address_zip'] 		= $_POST['buyer-shipping-address_postal-code'];
		$_POST['first_name'] 			= $_POST['buyer-billing-address_contact-name'];
		$_POST['address_country'] = $_POST['buyer-shipping-address_country-code'];
		
		wp_update_post(array(
			'post_expire' 	=> '0',
			'ID' 				=> $cartid,
			'post_status' 	=> 'pending',
			'post_date' 	=> current_time('timestamp'), 
		));
		update_post_meta( $cartid , '_response_data', $_POST );
		$this->subtitutions[] = $data['instructions'];
		
		do_action('ims_after_google_notice', $cartid, $data );
		
		$to 			= $this->opts['notifyemail'];
		$subject 	= $this->opts['notifysubj'];
		$message 	= preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['notifymssg']);
		$headers 	= 'From: "Image Store" <imstore@'.$_SERVER['HTTP_HOST'].">\r\n";
		
		wp_mail( $to, $subject, $message, $headers );
		setcookie( 'ims_orderid_' . COOKIEHASH,  false, (time()-315360000), COOKIEPATH, COOKIE_DOMAIN );

		if( empty( $this->opts['emailreceipt']) )
			die( );
		
		//notify buyers
		if( isset( $_POST['buyer-billing-address_email']) && is_email( $_POST['buyer-billing-address_email'] ) 
			&& !get_post_meta( $cartid , '_ims_email_sent', true ) ){
				
			global $ImStore;	
			$nonce	= '_wpnonce=' . wp_create_nonce( "ims_download_img");
			$message = make_clickable( wpautop( stripslashes( preg_replace( $this->opts['tags'], $this->subtitutions, $this->opts['thankyoureceipt'] )) ) );

			foreach( $data['images'] as $id => $sizes ){
				$enc = $ImStore->encrypt_id( $id );	
				foreach( $sizes as $size => $colors){
					foreach( $colors as $color => $item){
						if( isset( $item['download'] ))
						 $downlinks[] = '<a href="'. IMSTORE_ADMIN_URL . "/download.php?$nonce&amp;img=".$enc."&amp;sz=$size&amp;c=$color". '" 
						 class="ims-download">'. get_the_title( $id ) ." ". $labels[$color]." </a>";
					}
				}
			}
			
			if( !empty( $downlinks ) ){
				$message .= $output .= '<div class="imgs-downloads">';
				$message .= $output .= '<h4 class="title">Downloads</h4>';
				$message .= $output .= '<ul class="download-links">';
				foreach( $downlinks as $link )
					$message .= $output .= "<li>$link</li>\n";
				$message .= $output .= "</ul>\n</div>";
			}
				
			$headers = 'From: "Image Store" <imstore@' . $_SERVER['HTTP_HOST'] .">\r\n";
			$headers .= "Content-type: text/html; charset=utf8\r\n";
			wp_mail( $_POST['buyer-billing-address_email'], sprintf( __('%s receipt.', $this->domain ),  get_bloginfo( 'blogname' )), $message , $headers );
			update_post_meta( $cartid, '_ims_email_sent', 1 );
		
		}
		
		/*foreach($_POST as $i => $v)
			$postdata .= $i.'='.$v."\n";
			
		$file = "mytext.txt"; 
		$hd = fopen($file,'w');
		fwrite($hd,$postdata."\n"); 
		fclose($hd);*/

		die();
	}
	
}
new ImStoreGoogleNotice( );