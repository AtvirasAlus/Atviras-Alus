<?php 

/**
 * ImStorePaypalIPN - Paypal Notification
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0 
*/

class ImStorePaypalIPN {
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function ImStorePaypalIPN( ){
		global $ImStore;
		
		$postdata = ''; 
		$this->opts = $ImStore->opts;
	
		$url = $ImStore->gateway[$this->opts['gateway']];
		$log = array( 'REQUEST_TIME','REMOTE_ADDR','REQUEST_METHOD','HTTP_USER_AGENT','REMOTE_PORT' );

		foreach($_POST as $i => $v) 
			$postdata .= $i.'='.urlencode($v).'&';
		$postdata .= 'cmd=_notify-validate';
		
		$web = parse_url($url);
		if($web['scheme'] == 'https' || 
		strpos( $url ,'sandbox') !== false ) { 
			$web['port'] = 443; 
			$ssl = 'ssl://'; 
		} else { 
			$web['port'] = 80;
			$ssl = ''; 
		}
		$fp = fsockopen( $ssl . $web['host'], $web['port'], $errnum, $errstr, 30 );
		
		if( !$fp ) { 
			die();
		} else {
			fputs( $fp, "POST " . $web['path'] ." HTTP/1.1\r\n");
			fputs( $fp, "Host: ".$web['host']."\r\n");
			fputs( $fp, "Content-type: application/x-www-form-urlencoded\r\n");
			fputs( $fp, "Content-length: ". strlen($postdata) ."\r\n");
			fputs( $fp, "Connection: close\r\n\r\n");
			fputs( $fp, $postdata."\r\n\r\n");
		
			while( !feof($fp) )
				$info[] = @fgets( $fp, 1024 ); 
			
			fclose( $fp );
			$info = implode( ',', $info );
			
			/*$file = IMSTORE_ABSPATH . "/mytext.txt"; 
			$hd = fopen($file,'w');
			fwrite($hd,$info."\n"); 
			fclose($hd);*/
							
			if( eregi( 'VERIFIED', $info ) ) {
				
				do_action( 'ims_before_paypal_ipn',  $postdata );
				
				// information was verified
				$this->process_paypal_IPN( );
		 		die( );
			} else {
				$logtext = '';
				$file = IMSTORE_ABSPATH."/ipn_log.txt"; 
				$hd = fopen( $file, 'a' );
				foreach( $log as $key )
					$logtext .= $key.'='.$_SERVER[$key].',';
					
				fwrite( $hd, $web['host'] .",". $logtext . "\n" ); 
				fclose( $hd );
				die( );
			}
		}
	}
	
	/**
	 * Process Paypal IPN
	 *
	 * @return boolean
	 * @since 0.5.0 
	 */
	function process_paypal_IPN( ){
		
		if( $_POST['business'] != $this->opts['paypalname'])
			return false;
			
		if($_POST['mc_currency'] != $this->opts['currency'])
			return false;
		
		$cartid = (int)  $_POST['custom'];
		$cart = get_post_meta( $cartid , '_ims_order_data', true );
		$total = (isset($cart['discounted'])) ? $cart['discounted'] : $cart['total'];
		
		if($_POST['mc_gross'] != number_format($total,2))
			return false;
		
		wp_update_post(array(
			'post_expire' 	=> '0',
			'ID' 				=> $cartid,
			'post_status' 	=> 'pending',
			'post_date' 	=> current_time('timestamp') 
		));
		
		$_POST['num_cart_items'] = $cart['items'];
		$_POST['payment_gross']	= $_POST['mc_gross'];
		
		update_post_meta( $cartid, '_response_data' , $_POST );
		$this->subtitutions[] = $cart['instructions'];
		
		//dont change array order
		$this->subtitutions = array(
			$_POST['mc_gross'], $_POST['payment_status'], get_the_title( $cartid ),
			$cart['shipping'], $cart['tracking'], $cart['gallery_id'], $_POST['txn_id'],
			$_POST['last_name'], $_POST['first_name'], $_POST['payer_email'],
		);
		
		do_action('ims_after_paypal_ipn', $cartid, $cart );
		
		$to 		= $this->opts['notifyemail'];
		$subject 	= $this->opts['notifysubj'];
		$message 	= preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['notifymssg']);
		$headers 	= 'From: "Image Store" <imstore@'.$_SERVER['HTTP_HOST'].">\r\n";
		
		wp_mail( $to ,$subject, $message, $headers );
		setcookie( 'ims_orderid_' . COOKIEHASH,  false, (time()-315360000), COOKIEPATH, COOKIE_DOMAIN );

		if( empty( $this->opts['emailreceipt']) )
			die( );
		
		//notify buyers
		if( isset($_POST['payer_email']) && is_email( $_POST['payer_email'] ) 
			&& !get_post_meta( $cartid , '_ims_email_sent', true ) ){
			
			global $ImStore;
			$nonce	= '_wpnonce=' . wp_create_nonce( "ims_download_img");
			$message = make_clickable( wpautop( stripslashes( preg_replace( $this->opts['tags'], $this->subtitutions, $this->opts['thankyoureceipt'] )) ) );
			
			foreach( $cart['images'] as $id => $sizes ){
				$enc = $ImStore->encrypt_id( $id );	
				foreach( $sizes as $size => $colors){
					foreach( $colors as $color => $item){
						if( isset( $item['download'] ))
						 $downlinks[] = '<a href="'. IMSTORE_ADMIN_URL . "/download.php?$nonce&amp;img=" . $enc . "&amp;sz=$size&amp;c=$color" . '" 
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
			wp_mail( $_POST['payer_email'], sprintf( __('%s receipt.', $ImStore->domain ),  get_bloginfo( 'blogname' )), $message , $headers );
			update_post_meta( $cartid, '_ims_email_sent', 1 );
		}
		
		/*foreach($_POST as $i => $v)
			$postdata .= $i.'='.$v."\n";
			
		$file = "mytext.txt"; 
		$hd = fopen($file,'w');
		fwrite($hd,$postdata."\n"); 
		fclose($hd);*/
		
		die( );
	
	}
}
new ImStorePaypalIPN( );