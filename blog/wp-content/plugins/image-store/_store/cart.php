<?php 

/**
 *Shopping cart page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0 
*/

// Stop direct access of the file
if( preg_match( '#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die( );

$downlinks	= '';
$method		= 'post';
$nonce			= wp_create_nonce( "ims_download_img");

//custom cart data
if( $this->opts['gateway'] == 'custom' ){
	$data_pair = array();
	foreach( explode(',', $this->opts['data_pair']) as $input ){
		$vals = explode( '|', $input );
		if( isset($vals[1]) )
		$data_pair[$vals[0]] = $vals[1] ;
	};
}

//set form method
if( isset( $this->opts['gateway_method'] ) && $this->opts['gateway'] == 'custom' )
	$method = $this->opts['gateway_method'];

$output .= '<form method="'. esc_attr($method) .'" class="ims-cart-form" >';

if( empty($this->cart['images']) && apply_filters( 'ims_empty_car', true, $this->cart ) ){
	
	$error = new WP_Error( );
	$error->add( 'empty', __( 'Your shopping cart is empty.', $this->domain ) );
	$output .= "\t". $this->error_message( $error, true );

}else{
	
	$output .= 
	'<table class="ims-table">
		<thead>
			<tr>
				<th class="ims-preview">&nbsp;</th>
				<th colspan="6" class="ims-subrows">
					<span class="ims-quantity">' . __( 'Quantity', $this->domain ) . '</span>
					<span class="ims-size">' . __( 'Size', $this->domain ) . '</span>
					<span class="ims-color">' . __( 'Color', $this->domain ) . '</span>
					<span class="ims-price">' . __( 'Unit Price', $this->domain ) . '</span>
					<span class="ims-subtotal">' . __( 'Subtotal', $this->domain ) . '</span>
					<span class="ims-delete">' . __( 'Delete', $this->domain ) . '</span>
				</th>
			</tr>
		</thead>';
		
	$output .= '<tbody>';
	
	$i=1; 
	foreach( (array)$this->cart['images'] as $id => $sizes ){
		
		$image 	= get_post_meta( $id, '_wp_attachment_metadata', true );
			
		$mini 	= $image['sizes']['mini'];		
		$size 	= ' width="' . $mini['width'] . '" height="' . $mini['height'] . '"';
		
		$output .= '<tr> <td class="ims-preview">';
		$output .= '<img src="' . $this->get_image_url( $image, 'mini' ) . '" title="' . esc_attr($mini['file']) . '" alt="' . esc_attr($mini['file']) . '"'. $size . ' />'; 
		$output .= '</td>';
		
		$output .= '<td class="ims-subrows" colspan="6">';
		 foreach($sizes as $size => $colors){
			foreach($colors as $color => $item){
				$enc = $this->encrypt_id( $id );
				$output .= '
				<div class="ims-clear-row">
					<span class="ims-quantity"><input type="text" name="ims-quantity'. "[$id][$size][$color]" . '" value="' . esc_attr( $item['quantity'] ). '" class="input" /></span>
					<span class="ims-size">' . $size . ' ' . $item['unit'] . '</span>
					<span class="ims-color">' . $this->color[$color] . ( empty( $item['color'] ) ? '' : $item['color'] ) . '</span>
					<span class="ims-price">' . $this->format_price( $item['price'] ) . '</span>
					<span class="ims-subtotal">' . $this->format_price( $item['subtotal'] ) . '</span>
					' . apply_filters( 'ims_cart_image_list_column', '', $id, $item, $color, $enc , $i ) . '
					<span class="ims-delete"><input name="ims-remove[]" type="checkbox" value="' . esc_attr("{$enc}|{$size}|{$color}") . '" /></span>';
					
					if( $this->in_array( $this->opts['gateway'] , array( 'googlesand', 'googleprod' )) ){
						$output .= '<input type="hidden" name="item_merchant_id_' . $i . '" value="' . esc_attr( $enc ) . ' " />';
						$output .= '<input type="hidden" name="item_quantity_' . $i . '" value="' . esc_attr( $item['quantity'] ). '" />';
						$output .= '<input type="hidden" name="item_name_' . $i . '" value="' . get_the_title( $id ) . '" />';
						$output .= '<input type="hidden" name="item_currency_' . $i . '" value="' . esc_attr( $this->opts['currency'] ) . '" />';
						$output .= '<input type="hidden" name="item_description_' . $i . '" value="' . "$size " . trim( $this->color[$color], " + " ) . '" />';
						$output .= '<input type="hidden" name="item_price_' . $i . '" value="' . esc_attr($item['price'] + $item['color']) . '"/>';
						
						if( isset($item['download']) ) $downlinks .= 
						 "&lt;p&gt;&lt;a href='" . IMSTORE_ADMIN_URL ."/download.php?_wpnonce=$nonce&amp;img=$enc&amp;sz=$size&amp;c=$color' &gt;". 
						 get_the_title($id) . "&lt;/a&gt;: ". trim( $this->color[$color]," + ") . "&lt;/p&gt;";
						 
					}elseif( $this->in_array( $this->opts['gateway'] , array( 'paypalsand', 'paypalprod' )) ){
						$output .= '<input type="hidden" name="on0_' . $i . '" value="' . esc_attr( $size ). '"/>';
						$output .= '<input type="hidden" name="item_number_' . $i . '" value="' . esc_attr( $enc ) . '"/>';
						$output .= '<input type="hidden" name="quantity_' . $i . '" value="' . esc_attr( $item['quantity'] ) . '"/>';
						$output .= '<input type="hidden" name="item_name_' . $i . '" value="' . get_the_title( $id ) . '"/>';
						$output .= '<input type="hidden" name="os0_' . $i . '" value="' . trim( $this->color[$color] , " + " ) . '"/>';
						$output .= '<input type="hidden" name="amount_' . $i . '" value="' . esc_attr( $item['price'] + $item['color'] ) . '" />';
						
					}elseif( $this->opts['gateway'] == 'custom' ){
							
							$item_replace = array( $enc,
								__( '%image_id%', $this->domain ) => $enc,
								__( '%image_name%', $this->domain ) => get_the_title( $id ),
								__( '%image_value%', $this->domain ) => esc_attr( $item['price'] + $item['color'] ),
								__( '%image_color%', $this->domain ) => trim( $this->color[$color] , " + " ),
								__( '%image_quantity%', $this->domain ) => $item['quantity'],
							);
							
							if( isset($item['download']) )
								$item_replace[__( '%image_download%', $this->domain )] = $item['download'];
							
							//required for data validation
							$output .= '<input type="hidden" name="quantity_' . $i . '" value="' . esc_attr( $item['quantity'] ) . '"/>';
							$output .= '<input type="hidden" name="amount_' . $i . '" value="' . esc_attr( $item['price'] + $item['color'] ) . '" />';
							
							foreach( $data_pair as $key => $sub ){
								if( isset($item_replace[$sub]) )
								$output .= "\n" . '<input type="hidden" name="'. $key . $i . '" value="' . esc_attr( $item_replace[$sub] ) . '" />';
							}
					}						
					$output .= apply_filters( 'ims_cart_item_hidden_fields', '', $id, $item, $color, $enc , $i ) ;
				$output .= '</div><!--.ims-clear-row-->';
				$i++;
			}	
		} 
		$output .= '</td></tr>';
		$output .=  apply_filters( 'ims_cart_image_list_row', '', $id, $item, $color, $enc , $i ) ;
	}
	
	$output .=  apply_filters( 'ims_cart_image_list', '', $this ); 

	$output .= '</tbody><tfoot>';
	
	$output .= '<tr><td>&nbsp;</td><td><label>' . __( 'Item subtotal', $this->domain ) . '</label></td>
	<td class="total" colspan="5">' . $this->format_price( $this->cart['subtotal'] ) . '</td></tr>';
	
	$output .= '<tr>
	<td>&nbsp;</td><td><label for="ims-promo-code">' . __( 'Promotional code', $this->domain ) . '</label></td>
	<td class="total promo-code" colspan="5">
	<input name="promocode" id="ims-promo-code" type="text" value="' . ( isset( $this->cart['promo']['code'] ) ? esc_attr( $this->cart['promo']['code'] ) : '' ) . '" />
	<span class="ims-break"></span> <small>' . __( 'Update cart to apply promotional code.', $this->domain ) . '</small></td>
	</tr>';
	
	//display discounted data
	if( !empty( $this->cart['discounted'] ) ){
		$output .= '<tr><td>&nbsp;</td><td>' . __( 'Discount', $this->domain ) . '</td>
		<td colspan="5" class="discount">' . $this->format_price( $this->cart['promo']['discount'], ' - ' ) . '</td></tr>';
	}
	
	$output .= '<tr><td>&nbsp;</td><td><label for="shipping">' . __( 'Shipping', $this->domain ) . '</label></td>
	<td colspan="5" class="shipping">' . ( isset( $this->cart['shippingcost'] ) ? $this->shipping_options( ) : '' ) . '</td></tr>';
	
	//display tax fields
	if( isset( $this->cart['tax'] ) ){
		$output .= '<tr><td>&nbsp;</td><td>' . __( 'Tax', $this->domain ) . '</td><td colspan="2" class="tax">' . 
		$this->format_price( $this->cart['tax'], ' + ' ) . '<input type="hidden" name="tax_cart" value="' . esc_attr( number_format( $this->cart['tax'], 2 ) ). '"/></td></tr>';
	}
	
	//display total
	$output .= '<tr><td>&nbsp;</td> <td><label>' . __( 'Total', $this->domain ) . '</label></td>
	<td colspan="5" class="total">' . $this->format_price( $this->cart['total'] ) . ' </td></tr>';
	
	//display notification
	if( $this->opts['gateway'] != 'notification' ){
		$output .= '<tr><td>&nbsp;</td><td colspan="6"><label>' . __( 'Additional Instructions', $this->domain ) . '<br />
		<textarea name="instructions" class="ims-instructions">' . esc_textarea( isset($this->cart['instructions']) ? $this->cart['instructions'] : '' ) . '</textarea></label></td></tr>';
	}
	
	$output .= '<tr><td>&nbsp;</td><td colspan="6"><input name="apply-changes" type="submit" value="' . esc_attr__( 'Update Cart', $this->domain ) . '" class="secondary" />
	<input name="' . (( $this->opts['gateway'] == 'notification') ? 'enotification' : 'checkout' ) . '" type="submit" value="' . esc_attr__( 'Check out', $this->domain ) . '" class="primary" />
	'. 	apply_filters( 'ims_store_cart_actions', '', $this->cart ) . '</td></tr>';
	
	$output .= '</tfoot>
	</table><!--.ims-table-->';
	
	$output .= '<div class="ims-terms-condtitions">' . ( isset($this->opts['termsconds']) ? $this->opts['termsconds'] : '' ) . '</div>';
	
	//google checkout fileds
	if( $this->in_array( $this->opts['gateway'] , array( 'googlesand', 'googleprod' )) ){
		$output .=	'<input type="hidden" name="edit-cart-url" value="' . esc_attr( $this->get_permalink( ) ) . '" />
		<input type="hidden" name="tax_country" value="' . ( isset( $this->opts['taxcountry'] ) ? esc_attr( $this->opts['taxcountry'] ) : '' ) . '" />
		<input type="hidden" name="tax_rate" value="' . ( isset( $this->opts['taxamount'] ) ? esc_attr($this->opts['taxamount']/100 ) : 0 ) . '" />
		<input type="hidden" name="shopping-cart.merchant-private-data" value="' . esc_attr( $this->orderid ) . '" />';
		
		$output .=	 '<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.edit-cart-url" value="' . esc_attr( $this->get_permalink( 'shopping-cart' ) ) . '" />
		<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.continue-shopping-url" value="' . esc_attr( $this->get_permalink( 'receipt' ) ) . '" />
		<input type="hidden" name="checkout-flow-support.merchant-checkout-flow-support.tax-tables.default-tax-table.tax-rules.default-tax-rule-1.shipping-taxed" value="true"/>';
		
		if( isset( $this->cart['shippingcost'] ) ){ 
			$output .=	 '<input type="hidden" name="ship_method_name_1" value="' . esc_attr__("shipping", $this->domain ) . '" />
			<input type="hidden" name="ship_method_price_1" value="' . esc_attr( $this->cart['shipping'] ). '" />
			<input type="hidden" name="ship_method_currency_1" value="' . esc_attr( $this->opts['currency'] ) . '" />';
	 	}
		
		 if( $downlinks ) 
			$output .=	'<input type="hidden" name="shopping-cart.items.item-1.digital-content.description" 
			value="' . "&lt;p&gt;" . esc_attr__("downloads:", $this->domain ) . "&lt;/p&gt; $downlinks" . '" />';
		
		if( isset( $this->cart['discounted'] )){
			'<input type="hidden" name="item_quantity_' . $i . '" value="1" />
			<input type="hidden" name="item_name_' . $i . '" value="' . esc_attr__("discount", $this->domain ) . '" />
			<input type="hidden" name="item_currency_' . $i . '" value="' . esc_attr( $this->opts['currency'] ) . '" />
			<input type="hidden" name="item_merchant_id_' . $i . '" value="' . esc_attr( $this->cart['promo']['code'] ) . '" />
			<input type="hidden" name="item_price_' . $i . '" value="' . "-". esc_attr( $this->cart['promo']['discount'] ) . '" />
			<input type="hidden" name="item_description_' . $i . '" value="' . esc_attr__("promotion code", $this->domain ) . '" />';
		}
		
		$output .= apply_filters( 'ims_cart_google_hidden_fields', '', $this->cart ) ;
		
	}elseif( $this->in_array( $this->opts['gateway'] , array( 'paypalsand', 'paypalprod' )) ){
	
		$output .= '<input type="hidden" name="rm" value="2" />
		<input type="hidden" name="upload" value="1" />
		<input type="hidden" name="cmd" value="_cart" />
		<input type="hidden" name="lc" value="' . get_bloginfo( 'language') . '" />
		<input type="hidden" name="return" value="' . $this->get_permalink( 'receipt' ) . '" />
		<input type="hidden" name="page_style" value="' . get_bloginfo( 'name') . '" />
		<input type="hidden" name="custom" value="' . esc_attr( $this->orderid ) . '" />
		<input type="hidden" name="notify_url" value="' . $this->get_permalink( 'photos' ) . '" />
		<input type="hidden" name="currency_code" value="' . esc_attr( $this->opts['currency'] ) . '" />
		<input type="hidden" name="cancel_return" value="' . $this->get_permalink( 'shopping-cart' ) . '" />
		<input type="hidden" name="shipping_1" value="' .  $this->cart['shipping'] . '" />
		<input type="hidden" name="business" value="' . ( isset( $this->opts['paypalname'] ) ? esc_attr( $this->opts['paypalname'] ) : '' ). '" />
		<input type="hidden" name="discount_amount_cart" value="' . ( isset( $this->cart['promo']['discount'] ) ? esc_attr( $this->cart['promo']['discount'] ) : '' ) . '" />
		<input type="hidden" name="cbt" value="' . esc_attr( sprintf( __( 'Return to %s', $this->domain ), get_bloginfo( 'name') ) ) . '" />';
		
		$output .= apply_filters( 'ims_cart_paypal_hidden_fields', '', $this->cart );
	
	}elseif( $this->opts['gateway'] == 'custom' ){
		
		if(  empty($this->cart['tax'] ) ) 
			$this->cart['tax'] = '';
		
		if(  empty($this->cart['promo']['code'] ) ) 
			$this->cart['promo']['code']  = '';
		
		if(  empty($this->cart['promo']['discount'] ) ) 
			$this->cart['promo']['discount']  = '';
			
		$cart_replace = array(
				__( '%cart_id%', $this->domain ) => $this->orderid,
				__( '%cart_tax%', $this->domain ) => $this->cart['tax'],
				__( '%cart_total%', $this->domain ) => $this->cart['total'], 
				__( '%cart_shipping%', $this->domain ) => $this->cart['shipping'], 
				__( '%cart_currency%', $this->domain ) => $this->opts['currency'],
				__( '%cart_subtotal%', $this->domain ) => $this->cart['subtotal'],
				__( '%cart_status%', $this->domain ) => get_post_status( $this->orderid ), 
				__( '%cart_discount%', $this->domain ) => $this->cart['promo']['discount'],
				__( '%cart_discount_code%', $this->domain ) => $this->cart['promo']['code'],
				__( '%cart_total_items%', $this->domain ) => $this->cart['items'],
			);

		foreach( $data_pair as $key => $sub ){
			if( isset($cart_replace[$sub]) )
				$output .= "\n" . '<input type="hidden" name="'. $key  . '" value="' . esc_attr( $cart_replace[$sub] ) . '" />';
			elseif( !preg_match('/%image_/', $sub ) )
				$output .= "\n" . '<input type="hidden" name="'. $key . '" value="'. esc_attr( $sub ) . '" />';
		}
	
	}
	
	$output .= apply_filters( 'ims_cart_hidden_fields', '', $this->cart );
	$output .= '<input type="hidden" name="_wpnonce" value="' . wp_create_nonce( "ims_submit_order" ) . '" />';
}

$output .= '</form><!--.ims-cart-form-->';