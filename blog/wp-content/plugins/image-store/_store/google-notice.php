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
	function ImStoreGoogleNotice() {
		
		global $ImStore;
		$postdata = array();

		//dont change array order
		$this->subtitutions = array(
			$_POST['order-total'], $_POST['financial-order-state'],
			get_the_title($_POST['shopping-cart_merchant-private-data']),
			$ImStore->format_price($_POST['order-adjustment_shipping_flat-rate-shipping-adjustment_shipping-cost']),
			$_POST['google-order-number'], '', $_POST['buyer-billing-address_contact-name'], $_POST['buyer-billing-address_email'],
		);

		foreach ($_POST as $i => $v)
			$postdata .= $i . '=' . $v . "\n";

		if ($_POST['_type'] == 'new-order-notification') {
			
			do_action('ims_before_google_notice', $postdata);
			
			$data['last_name'] = '';
			$data['method'] = 'Google Checkout';
			$data['num_cart_items'] = $cart['items'];
			$data['mc_gross'] = $_POST['order-total'];
			$data['payment_gross'] = $_POST['order-total'];
			$data['txn_id'] = $_POST['google-order-number'];
			$data['mc_currency'] = $_POST['order-total_currency'];
			$data['payment_status'] = $_POST['financial-order-state'];
			$data['payer_email'] = $_POST['buyer-billing-address_email'];
			$data['address_city'] = $_POST['buyer-shipping-address_city'];
			$data['ims_phone'] = $_POST['buyer-shipping-address_phone'];
			$data['address_state'] = $_POST['buyer-shipping-address_region'];
			$data['address_street'] = $_POST['buyer-shipping-address_address1'];
			$data['address_zip'] = $_POST['buyer-shipping-address_postal-code'];
			$data['first_name'] = $_POST['buyer-billing-address_contact-name'];
			$data['address_country'] = $_POST['buyer-shipping-address_country-code'];
			
			global $ImStore;
			$ImStore->checkout( (int)$_POST['shopping-cart_merchant-private-data'], $data);
			
			do_action('ims_after_google_notice', $cartid, $data);
		}

		die();
	}

}

new ImStoreGoogleNotice( );