<?php 

/**
 *Shopping cart page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 0.5.0 
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();

$nonce 	= wp_create_nonce("ims_download_img");
$colors_options = array(
	'ims_sepia' => __('Sepia + ',ImStore::domain),	
	'color' 	=> __('Full Color',ImStore::domain),	
	'ims_bw' 	=> __('B &amp; W + ',ImStore::domain)
);
?>

<form method="post" >

	<?php if(empty($this->cart['images'])){?>
	<div class="ims-message ims-error"><?php _e('Your shopping cart is empty!!',ImStore::domain)?></div>
	<?php }else{?>
	
	<table class="ims-table">
		<thead>
			<tr>
				<th class="preview">&nbsp;</th>
				<th colspan="6" class="subrows">
					<span class="ims-quantity"><?php _e('Quantity',ImStore::domain)?></span>
					<span class="ims-size"><?php _e('Size',ImStore::domain)?></span>
					<span class="ims-color"><?php _e('Color',ImStore::domain)?></span>
					<span class="ims-price"><?php _e('Unit Price',ImStore::domain)?></span>
					<span class="ims-subtotal"><?php _e('Subtotal',ImStore::domain)?></span>
					<span class="ims-delete"><?php _e('Delete',ImStore::domain)?></span>
				</th>
			</tr>
		</thead>
		<tbody>
		<?php $i=1; foreach($this->cart['images'] as $id => $sizes){?>
			<?php $image = get_post_meta($id,'_wp_attachment_metadata',true)?>
			<tr>
				<td class="preview">
				<img src="<?php echo $image['sizes']['mini']['url']?>" 
				width="<?php echo $image['sizes']['mini']['width']?>" 
				height="<?php echo $image['sizes']['mini']['height']?>" 
				alt="<?php echo $image['sizes']['mini']['file']?>"/></td>
				<td colspan="6" class="subrows">
				<?php foreach($sizes as $size => $colors){?>
					<?php 
					foreach($colors as $color => $item){
						$enc = $this->encrypt_id($id);	
					?>
					<div class="clear-row">
						<span class="ims-quantity">
						<input type="text" name="ims-quantity<?php echo "[$id][{$size}][{$color}]"?>" 
						value="<?php echo $item['quantity']?>" class="input" /></span>
						<span class="ims-size"><?php echo $size.' '.$item['unit']?></span>
						<span class="ims-color"><?php echo $colors_options[$color].$item['color'] ?></span>
						<span class="ims-price"><?php printf($this->format[$this->opts['clocal']],number_format($item['price'],2))?></span>
						<span class="ims-subtotal"><?php printf($this->format[$this->opts['clocal']],number_format($item['subtotal'],2))?></span>
						<span class="ims-delete"><input name="ims-remove[]" type="checkbox" 
						value="<?php echo "{$enc}|{$size}|{$color}"?>" /></span>
						
						<?php if($this->opts['gateway'] == 'googlesand' || $this->opts['gateway'] == 'googleprod'){?>
						<input type="hidden" name="item_name_<?php echo $i?>" value="<?php echo get_the_title($id)?>" />
						<input type="hidden" name="item_description_<?php echo $i?>" 
						value="<?php echo "$size ".trim($colors_options[$color]," + ")?>" />
						<input type="hidden" name="item_quantity_<?php echo $i?>" value="<?php echo $item['quantity']?>" />
						<input type="hidden" name="item_currency_<?php echo $i?>" value="<?php echo $this->opts['currency']?>" />
						<input type="hidden" name="item_merchant_id_<?php echo $i?>" value="<?php echo $enc?>" />
						<input type="hidden" name="item_price_<?php echo $i?>"  value="<?php echo $item['price'] + $item['color'];?>"/>
						
						<?php if($item['download']) 
						$downlinks .=  "&lt;p&gt;&lt;a href='".
						IMSTORE_ADMIN_URL."download.php?_wpnonce=$nonce&amp;img=$enc&amp;sz=$size&amp;c=$color' &gt;".
						get_the_title($id)."&lt;/a&gt;: ".trim($colors_options[$color]," + ")."&lt;/p&gt;";
						?>

						<?php }else{ ?>
						<input type="hidden" name="on0_<?php echo $i?>" value="<?php echo $size ?>"/>
						<input type="hidden" name="os0_<?php echo $i?>" value="<?php echo trim($colors_options[$color]," + ")?>"/>
						<input type="hidden" name="quantity_<?php echo $i?>" value="<?php echo $item['quantity']?>"/>
						<input type="hidden" name="item_name_<?php echo $i ?>" value="<?php echo get_the_title($id)?>"/>
						<input type="hidden" name="item_number_<?php echo $i ?>" value="<?php echo $enc?>"/>
						<input type="hidden" name="amount_<?php echo $i?>" value="<?php echo $item['price'] + $item['color'];?>"/>
						<?php }?>
						
					</div>
					<?php $i++; }?>
				<?php }?>
				</td>
			</tr>
		<?php }?>
		</tbody>
		<tfoot>
			<tr>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php _e('Item subtotal',ImStore::domain)?></td>
				<td class="total" colspan="2"><?php printf($this->format[$this->opts['clocal']],number_format($this->cart['subtotal'],2))?></td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td><label for="ims-promo-code"><?php _e('Promotional code',ImStore::domain)?></label></td>
				<td class="total promo-code" colspan="2">
					<input name="promocode" id="ims-promo-code" type="text" value="<?php echo $this->cart['promo']['code']?>" />
					<span class="ims-break"></span>
					<small><?php _e('Update cart to apply promotional code.',ImStore::domain)?></small>
				</td>
			</tr>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td><label for="shipping_1"><?php _e('Shipping',ImStore::domain)?></label></td>
				<td colspan="2" class="shipping">
					<?php $meta = get_post_meta($this->pricelist_id,'_ims_list_opts',true);?>
					<?php if($this->cart['shippingcost']){ ?>
					<select name="shipping_1" id="shipping_1" class="shipping-opt">
						<option value="<?php echo $meta['ims_ship_local']?>"<?php $this->selected($meta['ims_ship_local'],$this->cart['shipping'])?>><?php echo __('Local + ',ImStore::domain).sprintf($this->format[$this->opts['clocal']],$meta['ims_ship_local'])?></option>
						<option value="<?php echo $meta['ims_ship_inter']?>"<?php $this->selected($meta['ims_ship_inter'],$this->cart['shipping'])?>><?php echo __('International + ',ImStore::domain).sprintf($this->format[$this->opts['clocal']],$meta['ims_ship_inter'])?></option>
					</select>
					<?php } ?>
				</td>
			</tr>
			<?php if($this->cart['discounted']){ ?>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td><?php _e('Discount',ImStore::domain)?></td>
				<td colspan="2" class="discount"><?php printf('- ' .$this->format[$this->opts['clocal']],number_format($this->cart['promo']['discount'],2)) ?></td>
			</tr>
			<?php } ?>
			<?php if($this->cart['tax']){ ?>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td><?php _e('Tax',ImStore::domain)?></td>
				<td colspan="2" class="tax">
					<?php printf('+ '.$this->format[$this->opts['clocal']],number_format($this->cart['tax'],2)) ?>
					<input type="hidden" name="tax_cart" value="<?php echo number_format($this->cart['tax'],2) ?>"/>
				</td>
			</tr>
			<?php } ?>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td><?php _e('Total',ImStore::domain)?></td>
				<td colspan="2" class="total"><?php printf($this->format[$this->opts['clocal']],number_format($this->cart['total'],2)) ?></td>
			</tr>
			<?php if($this->opts['gateway'] != 'notification'){?>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td colspan="3"><label><?php _e('Additional Instructions',ImStore::domain)?>
					<br /><textarea name="instructions" class="ims-instructions"><?php echo strip_tags($this->cart['instructions'])?></textarea>
				</label></td>
			</tr>
			<?php }?>
			<tr>
				<td colspan="4">&nbsp;</td>
				<td colspan="3">
					<input name="applychanges" type="submit" value="<?php _e('Update Cart',ImStore::domain)?>" class="secondary" />
					<input name="<?php echo($this->opts['gateway'] == 'notification')?'enotification':'checkout' ?>" 
					type="submit" value="<?php _e('Check out',ImStore::domain)?>" class="primary" />
				</td>
			</tr>
		</tfoot>
	</table>
	
	<div class="ims-terms-condtitions"><?php echo $this->opts['termsconds'] ?></div>
		<?php if($this->opts['gateway'] == 'googlesand' || $this->opts['gateway'] == 'googleprod'){?>
  		
		<input type="hidden" name="edit-cart-url" value="<?php echo $this->get_permalink()?>" />
		<input type="hidden" name="tax_country" value="<?php echo $this->opts['taxcountry']?>" />
		<input type="hidden" name="tax_rate" value="<?php echo($this->opts['taxamount']/100)?>" />
		<input type="hidden" name="shopping-cart.merchant-private-data" value="<?php echo $_COOKIE['ims_orderid_'.COOKIEHASH]?>" />
		
		<input type="hidden" 
		name="checkout-flow-support.merchant-checkout-flow-support.edit-cart-url" 
		value="<?php echo $this->get_permalink(5)?>" />
		<input type="hidden" 
		name="checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url" 
		value="<?php echo $this->get_permalink(6)?>" />
		<input type="hidden" 
		name="checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.shipping-taxed" 
		value="true"/>	
		
		<?php if($this->cart['shippingcost']){ ?>
			<input type="hidden" name="ship_method_name_1" value="<?php _e("Shipping",ImStore::domain)?>" />
			<input type="hidden" name="ship_method_price_1" value="<?php echo $this->cart['shipping'] ?>" />
			<input type="hidden" name="ship_method_currency_1" value="<?php echo $this->opts['currency']?>" />
		<?php }?>
		
		<?php if($downlinks){ ?>
			<input type="hidden" name="shopping-cart.items.item-1.digital-content.description" 
			value="<?php echo "&lt;p&gt;".__("Downloads:",ImStore::domain)."&lt;/p&gt; $downlinks" ?>" />
		<?php }?>
		
		<?php if($this->cart['discounted']){ ?>
			<input type="hidden" name="item_quantity_<?php echo $i?>" value="1" />
			<input type="hidden" name="item_name_<?php echo $i?>" value="<?php _e("Discount",ImStore::domain)?>" />
			<input type="hidden" name="item_currency_<?php echo $i?>" value="<?php echo $this->opts['currency']?>" />
			<input type="hidden" name="item_merchant_id_<?php echo $i?>" value="<?php echo $this->cart['promo']['code']?>" />
			<input type="hidden" name="item_price_<?php echo $i?>"  value="<?php echo "-".$this->cart['promo']['discount']?>" />
			<input type="hidden" name="item_description_<?php echo $i?>" value="<?php echo _e("Promotion Code",ImStore::domain)?>" />
		<?php }?>
		
		<?php }else{?>
		<input type="hidden" name="rm" value="2" />
		<input type="hidden" name="upload" value="1" />
		<input type="hidden" name="cmd" value="_cart" />
		<input type="hidden" name="page_style" value="<?php bloginfo('name')?>" />
		<input type="hidden" name="notify_url" value="<?php echo WP_SITE_URL?>" />
		<input type="hidden" name="lc" value="<?php echo get_bloginfo('language')?>" />
		<input type="hidden" name="return" value="<?php echo $this->get_permalink(6)?>" />
		<input type="hidden" name="ims-total" value="<?php echo $this->cart['total']?>" />
		<input type="hidden" name="business" value="<?php echo $this->opts['paypalname']?>" />
		<input type="hidden" name="currency_code" value="<?php echo $this->opts['currency']?>" />
		<input type="hidden" name="cancel_return" value="<?php echo $this->get_permalink(5)?>" />
		<input type="hidden" name="custom" value="<?php echo $_COOKIE['ims_orderid_'.COOKIEHASH]?>" />
		<input type="hidden" name="discount_amount_cart" value="<?php echo $this->cart['promo']['discount']?>" />
		<input type="hidden" name="cbt" value="<?php printf(__('Return to %s',ImStore::domain),get_bloginfo('name'))?>" />
		<?php }?>
		
		<input type="hidden" name="_wpnonce" id="_wpnonce" value="<?php echo wp_create_nonce("ims_submit_order")?>" />					

	<?php }?>
	
</form>