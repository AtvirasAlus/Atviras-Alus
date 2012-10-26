<?php

/**
 * Shopping cart page
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0
 */
 
// Stop direct access of the file
if (!defined('ABSPATH'))
	die();

$downlinks = '';
global $user_ID;
$userid = $user_ID;

//normalize nonce field
wp_set_current_user(0);
$nonce = wp_create_nonce("ims_download_img");
wp_set_current_user($userid);

//custom cart data
if ($this->opts['gateway']['custom'] && !empty($this->opts['data_pair'])) {
	$data_pair = array();
	foreach (explode(',', $this->opts['data_pair']) as $input) {
		$vals = explode('|', $input);
		if (isset($vals[1]))
			$data_pair[$vals[0]] = $vals[1];
	};
}

//start form output
$output .= '<form method="' . esc_attr($this->opts['gateway_method']) . '" class="ims-cart-form" action="#' . apply_filters('ims_cart_action', '', $this) . '" >';

//if empty show error
if (empty($this->cart['images']) && apply_filters('ims_empty_car', true, $this->cart)):

	$error = new WP_Error( );
	$error->add('empty', __('Your shopping cart is empty.', 'ims'));
	$output .= $this->error_message($error, true);

else: //else show table
	
	if(!is_singular('ims_gallery'))
		$this->imspage = false;
	
	$output .=
	'<noscript><div class="ims-message ims-error">' . __('Please enable Javascript, it is required to submit payment. ') . '</div></noscript>
		<table class="ims-table" role="grid">
		<thead>
			<tr>
				<th scope="col" class="ims-preview">&nbsp;</th>
				<th colspan="2" class="ims-subrows" >
					<span class="ims-quantity">' . __('Quantity', 'ims') . '</span>
					<span class="ims-size">' . __('Size', 'ims') . '</span>
					<span class="ims-color">' . __('Color', 'ims') . '</span>
					<span class="ims-fisnish">' . __('Finish', 'ims') . '</span>
					<span class="ims-price">' . __('Unit Price', 'ims') . '</span>
					<span class="ims-subtotal">' . __('Subtotal', 'ims') . '</span>
					<span class="ims-delete">' . __('Delete', 'ims') . '</span>
				</th>
			</tr>
		</thead>';
	$output .= '<tbody>';

	$i = 1;
	foreach ((array) $this->cart['images'] as $id => $sizes):

		$image = get_post_meta($id, '_wp_attachment_metadata', true);
		
		if( empty($image) )
			continue;
		
		$mini = $image['sizes']['mini'];
		$size = ' width="' . $mini['width'] . '" height="' . $mini['height'] . '"';

		$output .= '<tr role="row"> <td role="gridcell" class="ims-preview">'; //start row
		$output .= '<img src="' . $this->get_image_url($id, 3) . '" title="' . esc_attr($mini['file']) . '" alt="' . esc_attr($mini['file']) . '"' . $size . ' />';
		$output .= '</td>';

		$output .= '<td role="gridcell" class="ims-subrows" colspan="2">';
		foreach ($sizes as $size => $colors):
			foreach ($colors as $color => $item):
				
				$enc = $this->url_encrypt($id);
				$imgtitle = ( $title = get_the_title($id) ) ? $title : $enc;
				$colorname = !empty($item['color_name']) ? trim($item['color_name'], " + ")  : false;
				
				$output .= '<div class="ims-clear-row">';
				$output .= '<span class="ims-quantity"><input type="text" name="ims-quantity'."[$enc][$size][$color]" . '" value="'.esc_attr($item['quantity']).'" class="input" /></span>';
				$output .= '<span class="ims-size">' . $item['size'] . ' <span class="ims-unit">' . $item['unit'] . '</span></span>';
				$output .= '<span class="ims-color">' . $item['color_name'] . ' ' . $this->format_price($item['color']) . '</span>';
				$output .= '<span class="ims-fisnish">' . $item['finish_name'] . ' ' . $this->format_price($item['finish'])  . '</span>';
				$output .= '<span class="ims-price">' . $this->format_price($item['price']) . '</span>';
				$output .= '<span class="ims-subtotal">' . $this->format_price($item['subtotal']) . '</span>';
				$output .= apply_filters('ims_cart_image_list_column', '', $id, $item, $color, $enc, $i);
				$output .= '<span class="ims-delete"><input name="ims-remove[]" type="checkbox" value="' . esc_attr("{$enc}|{$size}|{$color}") . '" /></span>';

				//load google checkout
				if ($this->opts['gateway']['googlesand'] || $this->opts['gateway']['googleprod']) :
					$output .= '<input type="hidden" name="item_merchant_id_' . $i . '" data-value-ims="' . esc_attr($enc) . '" />';
					$output .= '<input type="hidden" name="item_quantity_' . $i . '" data-value-ims="' . esc_attr($item['quantity']) . '" />';
					$output .= '<input type="hidden" name="item_name_' . $i . '" data-value-ims="' . $imgtitle . '" />';
					$output .= '<input type="hidden" name="item_currency_' . $i . '" data-value-ims="' . esc_attr($this->opts['currency']) . '" />';
					$output .= '<input type="hidden" name="item_description_' . $i . '" data-value-ims="' . esc_attr("$size " . $item['unit'] . ' ' . $colorname) . '" />';
					$output .= '<input type="hidden" name="item_price_' . $i . '" data-value-ims="' . esc_attr($this->format_price($item['price']+$item['color']+$item['finish'], false)) . '"/>';

					if (isset($item['download']))
						$downlinks .=
								"&lt;p&gt;&lt;a href='" . IMSTORE_ADMIN_URL . "/download.php?_wpnonce=$nonce&amp;img=$enc&amp;sz=$size&amp;c=".$item['color_code']."' &gt;" .
								$imgtitle . "&lt;/a&gt;: " . trim($item['color_name'], " + ") . "&lt;/p&gt;";
				endif;

				//load paypal
				if ($this->opts['gateway']['paypalsand'] || $this->opts['gateway']['paypalprod']) :
					if( $colorname ) $output .= '<input type="hidden" name="os0_' . $i . '" data-value-ims="' . $colorname . '"/>';
					$output .= '<input type="hidden" name="on0_' . $i . '" data-value-ims="' . esc_attr("$size " . $item['unit']) . '"/>';
					$output .= '<input type="hidden" name="item_number_' . $i . '" data-value-ims="' . esc_attr($enc) . '"/>';
					$output .= '<input type="hidden" name="quantity_' . $i . '" data-value-ims="' . esc_attr($item['quantity']) . '"/>';
					$output .= '<input type="hidden" name="item_name_' . $i . '" data-value-ims="' . esc_attr($imgtitle) . '"/>';
					$output .= '<input type="hidden" name="amount_' . $i . '" data-value-ims="' . esc_attr($this->format_price($item['price']+$item['color']+$item['finish'], false)) . '" />';
				endif;

				//load custom cart
				if ($this->opts['gateway']['custom'] && !empty($this->opts['data_pair'])) :

					$item_replace = array($enc,
						__('%image_id%', 'ims') => $enc,
						__('%image_name%', 'ims') => get_the_title($id),
						__('%image_value%', 'ims') => esc_attr($this->format_price($item['price']+$item['color']+$item['finish'], false)),
						__('%image_color%', 'ims') => trim($this->color[$color], " + "),
						__('%image_quantity%', 'ims') => $item['quantity'],
					);

					if (isset($item['download']))
						$item_replace[__('%image_download%', 'ims')] = $item['download'];

					foreach ($data_pair as $key => $sub) {
						if (isset($item_replace[$sub]))
							$output .= "\n" . '<input type="hidden" name="' . $key . $i . '" data-value-ims="' . esc_attr($item_replace[$sub]) . '" />';
					}
				endif;

				$output .= apply_filters('ims_cart_item_hidden_fields', '', $id, $item, $color, $enc, $i);
				$output .= '</div><!--.ims-clear-row-->';
				$i++;

			endforeach;
		endforeach;

		$output .= '</td></tr>'; //end row
		$output .= apply_filters('ims_cart_image_list_row', '', $id, $item, $color, $enc, $i);

	endforeach; //end image list

	$output .= apply_filters('ims_cart_image_list', '', $this);
	$output .= '</tbody><tfoot>'; //end tbody - start tfoot
	//display subtotal
	$output .= '<tr role="row"><td role="gridcell">&nbsp;</td><td role="gridcell"><label>' . __('Item subtotal', 'ims') . '</label></td>
	<td role="gridcell" class="total">' . $this->format_price($this->cart['subtotal']) . '</td></tr>';

	//promotional code
	$output .= '<tr role="row">
	<td role="gridcell" >&nbsp;</td><td role="gridcell"><label for="ims-promo-code">' . __('Promotional code', 'ims') . '</label></td>
	<td role="gridcell" class="total promo-code">
	<input name="promocode" id="ims-promo-code" type="text" value="' . ( isset($this->cart['promo']['code']) ? esc_attr($this->cart['promo']['code']) : '' ) . '" />
	<span class="ims-break"></span> <small>' . __('Update cart to apply promotional code.', 'ims') . '</small></td>
	</tr>';

	//display discounted data
	if ($this->cart['promo']['discount'])
		$output .= '<tr role="row"><td role="gridcell">&nbsp;</td><td role="gridcell">' . __('Discount', 'ims') . '</td>
		<td role="gridcell" class="discount">' . $this->format_price($this->cart['promo']['discount'], true, ' - ') . '</td></tr>';
	
	//shipping charge
	if($this->cart['shippingcost'] )
		$output .= '<tr role="row"><td role="gridcell">&nbsp;</td><td role="gridcell"><label for="shipping">' . __('Shipping', 'ims') . '</label></td>
		<td role="gridcell" class="shipping">' .  $this->shipping_options()  . '</td></tr>';
		
	//display tax fields
	if ($this->cart['tax']) 
		$output .= '<tr role="row"><td role="gridcell">&nbsp;</td><td role="gridcell">' . __('Tax', 'ims') . '</td><td role="gridcell" class="tax">' .
		$this->format_price($this->cart['tax'], true, ' + ') . '<input type="hidden" name="tax_cart" data-value-ims="' . $this->format_price($this->cart['tax'],false) . '"/> </td></tr>';

	//display total
	$output .= '<tr role="row"><td role="gridcell">&nbsp;</td> <td role="gridcell"><label>' . __('Total', 'ims') . '</label></td>
	<td role="gridcell" class="total">' . $this->format_price($this->cart['total']) . ' </td></tr>';

	//display notification
	$output .= '<tr role="row"><td role="gridcell">&nbsp;</td><td role="gridcell" colspan="2"><label>' . __('Additional Instructions', 'ims') . '<br />
	<textarea name="instructions" class="ims-instructions">' . esc_textarea(isset($this->cart['instructions']) ? $this->cart['instructions'] : '' ) . '</textarea></label></td></tr>';

	$output .= '<tr role="row" class="ims-checkout-fileds"><td role="gridcell">&nbsp;</td><td role="gridcell" colspan="2">';
	$output .= '<input name="apply-changes" type="submit" value="' . esc_attr__('Update Cart', 'ims') . '" class="secondary" />';

	$output .= '<span class="ims-bk"></span>';
	$output .= '<div class="ims-cart-actions"> <span class="ims-checkout-label">' . esc_attr__('Checkout using:', 'ims') . ' </span>';

	include( IMSTORE_ABSPATH . '/_store/wepaysdk.php');
	
	//render buttons
	foreach ((array)$this->opts['gateway'] as $key => $bol){
		if( $this->in_array($key, array('wepaystage','wepayprod')) && $bol){
			
			$data = array(
				'type' => 'GOODS', 
				'account_id' => $this->opts['wepayaccountid'],
				'amount' => $this->cart['total'],
				'short_description' => __("Image Purchase"),
				'reference_id' => $this->orderid,
				'redirect_uri' => $this->get_permalink('receipt'),
				'callback_uri' =>$this->get_permalink($this->imspage),
			);
			
			if ($this->cart['shippingcost']) 
				$data['require_shipping' ] = true;

			try{ $checkout = $wepay->request('checkout/create', $data );
			}catch(WePayException $e){ }
			
			if(!empty( $checkout->checkout_uri )) 
				$this->gateways[$key]['url'] = $checkout->checkout_uri;
		}
		if ($bol){
			$output .='<input name="' . $key . '" type="submit" value="' . esc_attr($this->gateways[$key]['name']) . 
			'" class="primary ims-google-checkout" data-submit-url="' . esc_attr(urlencode($this->gateways[$key]['url'])) . '" /> ';
		}
	}
	
	$output .= apply_filters('ims_store_cart_actions', '', $this->cart) . '</div></td></tr>';

	$output .= '</tfoot>
	</table><!--.ims-table-->'; //end table
	
	//terms and conditions
	$output .= '<div class="ims-terms-condtitions">' . esc_html(isset($this->opts['termsconds']) ? $this->opts['termsconds'] : '' ) . '</div>';

	//google cart fileds
	if ($this->opts['gateway']['googlesand'] || $this->opts['gateway']['googleprod']) :

		$output .= '<input type="hidden" name="edit-cart-url"  data-value-ims="' . esc_attr($this->get_permalink()) . '" />
		<input type="hidden" name="tax_country"  data-value-ims="' . ( isset($this->opts['taxcountry']) ? esc_attr($this->opts['taxcountry']) : '' ) . '" />
		<input type="hidden" name="tax_rate"  data-value-ims="' . ( isset($this->opts['taxamount']) ? esc_attr($this->opts['taxamount'] / 100) : 0 ) . '" />
		<input type="hidden" name="shopping-cart.merchant-private-data"  data-value-ims="' . esc_attr($this->orderid) . '" />';

		$output .= '<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.edit-cart-url"  data-value-ims="' . esc_attr($this->get_permalink($this->imspage)) . '" />
		<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url"  data-value-ims="' . esc_attr($this->get_permalink('receipt')) . '" />
		<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.shipping-taxed" data-value-ims="true"/>';

		if ($this->cart['shippingcost']) {
			$output .= '<input type="hidden" name="ship_method_name_1"  data-value-ims="' . esc_attr__("shipping", 'ims') . '" />
			<input type="hidden" name="ship_method_price_1"  data-value-ims="' . esc_attr($this->cart['shipping']) . '" />
			<input type="hidden" name="ship_method_currency_1"  data-value-ims="' . esc_attr($this->opts['currency']) . '" />';
		}

		if ($downlinks)
			$output .= '<input type="hidden" name="shopping-cart.items.item-1.digital-content.description"
			 data-value-ims="' . "&lt;p&gt;" . esc_attr__("downloads:", 'ims') . "&lt;/p&gt; $downlinks" . '" />';

		if ($this->cart['promo']['discount']) {
			$output .= '<input type="hidden" name="item_quantity_' . $i . '" data-value-ims="1" />
			<input type="hidden" name="item_name_' . $i . '"  data-value-ims="' . esc_attr__("discount", 'ims') . '" />
			<input type="hidden" name="item_currency_' . $i . '"  data-value-ims="' . esc_attr($this->opts['currency']) . '" />
			<input type="hidden" name="item_merchant_id_' . $i . '"  data-value-ims="' . esc_attr($this->cart['promo']['code']) . '" />
			<input type="hidden" name="item_price_' . $i . '"  data-value-ims="' . "-" . esc_attr($this->cart['promo']['discount']) . '" />
			<input type="hidden" name="item_description_' . $i . '"  data-value-ims="' . esc_attr__("promotion code", 'ims') . '" />';
		}

		$output .= apply_filters('ims_cart_google_hidden_fields', '', $this->cart);

	endif;


	//load paypal
	if ($this->opts['gateway']['paypalsand'] || $this->opts['gateway']['paypalprod']) :

		$output .= '
		<input type="hidden" readonly="readonly" name="rm" data-value-ims="2" />
		<input type="hidden" name="upload" data-value-ims="1" />
		<input type="hidden" name="cmd" data-value-ims="_cart" />
		<input type="hidden" name="lc" data-value-ims="' . get_bloginfo('language') . '" />
		<input type="hidden" name="return" data-value-ims="' . $this->get_permalink('receipt') . '" />
		<input type="hidden" name="page_style" data-value-ims="' . get_bloginfo('name') . '" />
		<input type="hidden" name="custom" data-value-ims="' . esc_attr($this->orderid) . '" />
		<input type="hidden" name="notify_url" data-value-ims="' . $this->get_permalink($this->imspage) . '" />
		<input type="hidden" name="currency_code" data-value-ims="' . esc_attr($this->opts['currency']) . '" />
		<input type="hidden" name="cancel_return" data-value-ims="' . $this->get_permalink($this->imspage) . '" />
		<input type="hidden" name="shipping_1" data-value-ims="' . $this->cart['shipping'] . '" />
		<input type="hidden" name="business" data-value-ims="' . ( isset($this->opts['paypalname']) ? esc_attr($this->opts['paypalname']) : '' ) . '" />
		<input type="hidden" name="discount_amount_cart" data-value-ims="' . ( isset($this->cart['promo']['discount']) ? esc_attr($this->cart['promo']['discount']) : '' ) . '" />
		<input type="hidden" name="cbt" data-value-ims="' . esc_attr(sprintf(__('Return to %s', 'ims'), get_bloginfo('name'))) . '" />';

		$output .= apply_filters('ims_cart_paypal_hidden_fields', '', $this->cart);

	endif;

	//custom
	if ($this->opts['gateway']['custom'] && !empty($this->opts['data_pair'])) :

		if (empty($this->cart['tax']))
			$this->cart['tax'] = '';

		if (empty($this->cart['promo']['code']))
			$this->cart['promo']['code'] = '';

		if (empty($this->cart['promo']['discount']))
			$this->cart['promo']['discount'] = '';

		$cart_replace = array(
			__('%cart_id%', 'ims') => $this->orderid,
			__('%cart_tax%', 'ims') => $this->cart['tax'],
			__('%cart_total%', 'ims') => $this->cart['total'],
			__('%cart_shipping%', 'ims') => $this->cart['shipping'],
			__('%cart_currency%', 'ims') => $this->opts['currency'],
			__('%cart_subtotal%', 'ims') => $this->cart['subtotal'],
			__('%cart_status%', 'ims') => get_post_status($this->orderid),
			__('%cart_discount%', 'ims') => $this->cart['promo']['discount'],
			__('%cart_discount_code%', 'ims') => $this->cart['promo']['code'],
			__('%cart_total_items%', 'ims') => $this->cart['items'],
		);

		foreach ($data_pair as $key => $sub) {
			if (isset($cart_replace[$sub]))
				$output .= "\n" . '<input type="hidden" name="' . $key . '" data-value-ims="' . esc_attr($cart_replace[$sub]) . '" />';
			elseif (!preg_match('/%image_/', $sub))
				$output .= "\n" . '<input type="hidden" name="' . $key . '" data-value-ims="' . esc_attr($sub) . '" />';
		}

	endif;

	$output .= apply_filters('ims_cart_hidden_fields', '', $this->cart);
	$output .= '<input type="hidden" name="_xmvdata" data-value-ims="' . esc_attr( $this->cart['total'] ) . '" />';
	$output .= '<input type="hidden" name="_wpnonce" data-value-ims="' . wp_create_nonce("ims_submit_order") . '" />';

endif; //end if table
$output .= '</form><!--.ims-cart-form-->'; //endform