<?php 

/**
 * ImStoreGoogleNotice - Google Notification
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2011
 * @since 2.0.0
*/

class ImStoreGoogleNotice {
	
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 2.0.0
	 */	
	 function __construct(){
		global $ImStore;
		
		$postdata = '';
		$this->opts = $ImStore->store->opts;
		$this->subtitutions = $ImStore->store->subtitutions;
		$url = $ImStore->store->gateway[$this->opts['gateway']];
		
		if($_POST['_type'] == 'new-order-notification')
		$this->process_google_notice();
		
		die();
	}
	
	/**
	 * Process Google Notification
	 *
	 * @return boolean
	 * @since 2.0.0
	 */
	function process_google_notice(){
		
		if($_POST['order-adjustment_adjustment-total_currency'] != $this->opts['currency'])
			return false;
			
		$data = get_post_meta($_POST['shopping-cart_merchant-private-data'],'_ims_order_data',true);
		$total = ($data['discounted'])?$data['discounted']:$data['total'];
		
		if($_POST['order-total'] != number_format($total,2))
			return false;
			
		$_POST['num_cart_items'] 	= $data['items'];
		$_POST['mc_gross'] 			= $_POST['order-total'];
		$_POST['txn_id'] 			= $_POST['google-order-number'];
		$_POST['payment_status'] 	= $_POST['financial-order-state'];
		$_POST['address_city'] 		= $_POST['buyer-shipping-address_city'];
		$_POST['address_state'] 	= $_POST['buyer-shipping-address_region'];
		$_POST['address_street'] 	= $_POST['buyer-shipping-address_address1'];
		$_POST['address_zip'] 		= $_POST['buyer-shipping-address_postal-code'];
		$_POST['first_name'] 		= $_POST['buyer-billing-address_contact-name'];
		$_POST['address_country'] 	= $_POST['buyer-shipping-address_country-code'];
		
		wp_update_post(array(
			'post_expire' 	=> '0',
			'post_status' 	=> 'pending',
			'post_date' 	=> current_time('timestamp'), 
			'ID' 			=> $_POST['shopping-cart_merchant-private-data'],
		));
		update_post_meta($_POST['shopping-cart_merchant-private-data'],'_response_data',$_POST);
		$this->subtitutions[] = $data['instructions'];
		
		$to 		= $this->opts['notifyemail'];
		$subject 	= $this->opts['notifysubj'];
		$message 	= preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['notifymssg']);
		$headers 	= 'From: "Image Store" <imstore@'.$_SERVER['HTTP_HOST'].">\r\n";
		
		wp_mail($to,$subject,$message,$headers);
		setcookie('ims_orderid_'.COOKIEHASH,' ',time() - 31536000,COOKIEPATH,COOKIE_DOMAIN);
		die();
	}

}

$ImStoreGoogleNotice = new ImStoreGoogleNotice
?>