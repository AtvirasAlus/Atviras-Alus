<?php 

/**
 *Sales details page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0
*/

if( !current_user_can('ims_read_sales') ) 
	die();

$this->orderid 	= intval($_GET['id']);
$this->order		= get_post( $this->orderid ); 
$this->cart 		= get_post_meta( $this->orderid,'_ims_order_data',true);
$this->data 		= get_post_meta( $this->orderid,'_response_data',true); 
?>

<form method="get" action="">
	<table class="widefat post fixed imstore-table store-detail">
		<thead>
			<tr>
				<th scope="col" class="column-thumb">
					<input type="button" onclick="javascript:window.print()" class="print-bt button" value="<?php _e('Print', $this->domain)?>"  />
				</th>
				<th scope="col" colspan="6">
					<span class="quantity"><?php _e('Quantity', $this->domain)?></span>
					<span class="size"><?php _e('Size', $this->domain)?></span>
					<span class="color"><?php _e('Color', $this->domain)?></span>
					<span class="price"><?php _e('Unit Price', $this->domain)?></span>
					<span class="subtotal"><?php _e('Subtotal', $this->domain)?></span>
					<span class="title"><?php _e('Title', $this->domain)?></span>
					<span class="imageid"><?php _e('Image ID', $this->domain)?></span>
					<span class="gallery"><?php _e('Gallery', $this->domain)?></span>
				</th>
			</tr>
		</thead>
		<tbody id="details" class="list:details sales-details">
		<?php
			foreach( $this->cart['images'] as $id => $sizes ){
				
				$image = get_post_meta( $id, '_wp_attachment_metadata', true );
				$mini = $image['sizes']['mini'];
				
				$r = '<tr><td class="column-thumb"><img src="' . $mini['url'] . '" width="'. $mini['width']. ' " height="' . $mini ['height'] . '" alt="' . $mini['file'] .'"/></td>';
				$r .= '<td colspan="6">';
				
				foreach( $sizes as $size => $colors ){
					foreach( $colors as $color => $item){
						$r .= '<div class="clear-row">';
						$r .= '<span class="quantity">' .  $item['quantity'] . '</span>';
						$r .= '<span class="size">' .  $size . '</span>';
						$r .= '<span class="color">' .  $this->color[$color] . $this->format_price( $item['color'], ' + ' ) . '</span>';
						$r .= '<span class="price">' . $this->format_price( $item['price'] ) . '</span>';
						$r .= '<span class="subtotal">' . $this->format_price( $item['subtotal'] ) . '</span>';
						$r .= '<span class="title">' .  get_the_title($id) . '</span>';
						$r .= '<span class="imageid">' .  sprintf( "%05d", $id ) . '</span>';
						$r .= '<span class="gallery"><a href="' .  get_edit_post_link( $item['gallery'] )  . '">';
						$r .= '' .  get_the_title($item['gallery']) . '</a></span>';
						$r .= '</div>';	
					}
				}
				
				echo $r .= '</td></tr>';
				
			}
		?>
		<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Date', $this->domain)?></td>
				<td><?php echo date_i18n( $this->dformat, strtotime( $this->order->post_date ) )?></td>
				<td>&nbsp;</td>
				<td><?php _e('Item subtotal', $this->domain)?></td>
				<td><span class="total"><?php echo $this->format_price( $this->cart['subtotal'] )?></span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Order number', $this->domain)?></td>
				<td><?php echo $this->data['txn_id']?></td>
				<td >&nbsp;</td>
				<td ><?php _e('Promotional code', $this->domain)?></td>
				<td><span class="total promo-code"><?php if( isset( $this->cart['promo']['code'] ) ) echo $this->cart['promo']['code'] ?></span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Customer', $this->domain)?></td>
				<td><?php echo $this->data['last_name'].' '.$this->data['first_name']?></td>
				<td>&nbsp;</td>
				<td><?php _e('Shipping', $this->domain)?></td>
				<td><span class="shipping"><?php echo $this->format_price( $this->cart['shipping'] )?></span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Shipping Adress', $this->domain)?></td>
				<td><?php if( isset( $this->data['address_street'] ) ) echo $this->data['address_street'] ?></td>
				<td>&nbsp;</td>
				<td><?php _e('Discount', $this->domain)?></td>
				<td> <?php if ( isset($this->cart['promo']['discount']) ) echo $this->format_price( $this->cart['promo']['discount'] ) ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Shipping Adress 2', $this->domain)?></td>
				<td colspan="2">
				<?php
				foreach( array( 'address_city', 'address_state', 'address_zip' , 'address_country', 'ims_city', 'ims_state', 'ims_zip' , 'ims_address', 'ims_contry' ) as $key  ){
					if( !empty( $this->data[$key]  ) ) 
						echo  $this->data[$key].", ";
				}
				?>
				</td>
				<td><?php _e('Tax', $this->domain)?></td>
	
				<td><?php if( isset( $this->cart['tax'] ) ) echo $this->format_price( $this->cart['tax'] , ' + ' ) ?></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
                <td scope="row">&nbsp;</td>
                <td><?php _e( 'E-mail', $this->domain)?></td>
                <td colspan="2">
                <?php
				if ( isset( $this->data['payer_email'])  ) 
					echo $this->data['payer_email'];
				elseif( isset( $this->data['user_email']) ) 
					echo $this->data['user_email']
				?>
                </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>

              <tr>
                <td scope="row">&nbsp;</td>
                <td><?php _e('Phone', $this->domain)?></td>
                <td colspan="2">
                <?php if( isset(  $this->data['ims_phone']) ) echo  $this->data['ims_phone']?>
                </td>
                <td>&nbsp; </td>
                <td>&nbsp;</td>
                <td>&nbsp;</td>
            </tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php //_e('Gallery ID', $this->domain)?>&nbsp;</td>
				<td><?php //echo get_post_meta($order->post_parent,'_ims_gallery_id',true)?>&nbsp;</td>
				<td>&nbsp;</td>
				<td><?php _e('Total', $this->domain)?></td>
				<td><span class="total"><?php echo   $this->format_price( $this->cart['total'] )?></span></td>
				<td>&nbsp;</td>
			</tr>
			<tr>
				<td class="column-thumb" scope="row">&nbsp;</td>
				<td><?php _e('Additional Instructions', $this->domain)?></td>
				<td scope="row" colspan="5"><?php if( isset( $this->cart['instructions'] ) ) echo strip_tags( $this->cart['instructions'] ) ?></td>
			</tr>
			<tr><td scope="row" colspan="7">&nbsp;</td></tr>

		</tbody>
	</table>