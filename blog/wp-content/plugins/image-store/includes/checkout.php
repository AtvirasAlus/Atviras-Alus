<?php 

/**
 *Checkout information page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 1.0.2
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();

$req  = implode(' ',(array)$this->opts['requiredfields']); 

?>

<form method="post" class="shipping-info">
		<fieldset>
			<legend><?php echo __("Shipping Information",ImStore::domain) ?></legend>
			<div class="ims-p user-info">
				<label for="first_name"><?php _e('First Name',ImStore::domain); if(preg_match("/first_name/i",$req)) echo'<span class="req">*</span>'?>
				</label>
				<input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($_POST['first_name'])?>" class="ims-input" />
				<span class="ims-break"></span>
				<label for="last_name"><?php _e('Last Name',ImStore::domain); if(preg_match("/last_name/i",$req)) echo'<span class="req">*</span>'?>
				</label>
				<input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($_POST['last_name'])?>" class="ims-input"/>
			</div>
			<div class="ims-p email-info">
				<label for="user_email"><?php _e('Email',ImStore::domain); if(preg_match("/user_email/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="user_email" id="user_email" value="<?php echo esc_attr($_POST['user_email'])?>" class="ims-input" />
			</div>
			<div class="ims-p adress-info">
				<label for="address_street"><?php _e('Address',ImStore::domain); if(preg_match("/address_street/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="address_street" id="address_street" value="<?php echo esc_attr($_POST['address_street'])?>" class="ims-input" />
				<span class="ims-break"></span>
				<label for="address_city"><?php _e('City',ImStore::domain); if(preg_match("/address_city/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="address_city" id="address_city" value="<?php echo esc_attr($_POST['address_city'])?>" class="ims-input" />
				<span class="ims-break"></span>
				<label for="address_state"><?php _e('State',ImStore::domain); if(preg_match("/address_state/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="address_state" id="address_state" value="<?php echo esc_attr($_POST['address_state'])?>" class="ims-input" />
				<span class="ims-break"></span>
				<label for="address_zip"><?php _e('Zip Code',ImStore::domain); if(preg_match("/address_zip/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="address_zip" id="address_zip" value="<?php echo esc_attr($_POST['address_zip'])?>" class="ims-input" />
				<span class="ims-break"></span>
				<label for="ims_phone"><?php _e('Phone',ImStore::domain); if(preg_match("/ims_phone/i",$req)) echo'<span class="req">*</span>' ?>
				</label>
				<input type="text" name="ims_phone" id="ims_phone" value="<?php echo esc_attr($_POST['ims_phone'])?>" class="ims-input" />
			</div>
			<div class="ims-p">
				<label for="ims_instructions"><?php _e('Additional Instructions',ImStore::domain)?></label>
				<textarea name="instructions" id="ims_instructions" class="ims-instructions"><?php echo strip_tags($this->cart['instructions'])?></textarea>
			</div>
			<div class="ims-p"><small><span class="req">*</span> <?php _e("Required fields",ImStore::domain)?></small></div>
		</fieldset>
		<fieldset>
			<legend><?php _e("Order Information",ImStore::domain) ?></legend>
			<div class="ims-p order-info">
				<span class="ims-items"><strong><?php _e("Total items: ",ImStore::domain)?></strong> <?php echo $this->cart['items'] ?></span>
				<span class="ims-total"><strong><?php _e("Order total: ",ImStore::domain)?></strong> <?php printf($this->format[$this->opts['clocal']],number_format($this->cart['total'],2)) ?></span>
			</div>
		</fieldset>
		<div class="shipping-message">
		 	<?php echo make_clickable(wpautop(stripslashes(preg_replace($this->opts['tags'],$this->subtitutions,$this->opts['shippingmessage'])))); ?>
		</div>
		<div class="ims-p submit-buttons">
			<input name="cancelcheckout" type="submit" value="<?php _e('Cancel',ImStore::domain)?>" class="secundary" />
			<input name="enoticecheckout" type="submit" value="<?php _e('Submit Order',ImStore::domain)?>" class="primary" />
		</div>
		<input type="hidden" name="custom" value="<?php echo $_COOKIE['ims_orderid_'.COOKIEHASH]?>"/>
		<input type="hidden" name="mc_currency" id="mc_currency" value="<?php echo $this->opts['currency'] ?>" />
		<input type="hidden" name="payment_total" id="payment_total" value="<?php echo $this->cart['total']?>" />
		<input type="hidden" name="num_cart_items" id="num_cart_items" value="<?php echo $this->cart['items'] ?>" />
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo wp_create_nonce("ims_submit_order")?>" />
		<input type="hidden" name="payment_status" id="payment_status" value="<?php _e('Pending',ImStore::domain)?>" />
		<input type="hidden" name="payment_gross" id="payment_gross" value="<?php echo number_format($this->cart['total'],2)?>" />
		<input type="hidden" name="txn_id" id="txn_id" value="<?php echo sprintf("%017d",$_COOKIE['ims_orderid_'.COOKIEHASH]) ?>" />
	</form>