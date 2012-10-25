<?php 

/**
 *Pricing page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0
*/

if( !current_user_can( 'ims_change_pricing')) 
	die( );
	
//clear cancel post data
if( isset($_POST['cancel']))
	wp_redirect($this->pageurl);
	
//create new pricelist
if( isset($_POST['newpricelist'])){
	check_admin_referer( 'ims_new_pricelist' );
	$errors = $this->create_pricelist( );
}

//update list
if( isset($_POST['updatelist'])){
	check_admin_referer( 'ims_pricelist' );
	$errors = $this->update_pricelist( );
}

//create new package
if( isset($_POST['newpackage'])){
	check_admin_referer( 'ims_new_packages' );
	$errors = $this->create_package( );
}

//update packages
if( isset($_POST['updatepackage'])){
	check_admin_referer( 'ims_update_packages' );
	$errors = $this->update_package( );
}

//new/update promotion
if( isset($_POST['promotion'])){
	check_admin_referer( 'ims_promotion' );
	$errors = $this->add_promotion( );
}

//delete promotion
if( isset( $_GET['delete'] ) && is_numeric($_GET['delete']) ){
	check_admin_referer( 'ims_link_promo' );
	$errors = $this->delete_promotions( );
}

//update images sizes
if( isset($_POST['updateimglist'])){
	check_admin_referer( 'ims_imagesizes' );
	$sizes = (array)$_POST['sizes'];
	update_option( 'ims_sizes',$sizes);
	wp_redirect($this->pageurl."&ms=37");
}

$tabs = apply_filters( 'ims_pricing_tabs', array(
	'price-list' => __( 'Price lists', $this->domain ),
	'packages' => __( 'Packages', $this->domain ),
	'promotions' => __( 'Promotions', $this->domain ),
));

//display error message
if( isset($errors) && is_wp_error( $errors ) )
	$this->error_message( $errors );

add_action( 'ims_pricing_price-list_tab', 'ims_pricelist_tab',1, 2);
add_action( 'ims_pricing_packages_tab', 'ims_packages_tab',1, 2);
add_action( 'ims_pricing_promotions_tab', 'ims_promotions_tab',1, 2);

$moresizes = '<a href="#" class="add-image-size">'. __( 'Add image size', $this->domain ) .'</a>';
add_meta_box( 'image_sizes', __( 'Image sizes', $this->domain ) . $moresizes, 'ims_image_sizes', 'ims_pricelists', 'side' );
add_meta_box( 'image_sizes', __( 'Image sizes', $this->domain ) . $moresizes, 'ims_image_sizes', 'ims_packages', 'side' );

add_meta_box( 'price-list-new', __( 'New pricelist', $this->domain ), 'ims_new_pricelist', 'ims_pricelists', 'normal' );
add_meta_box( 'price-list-box', __( 'Price lists', $this->domain ), 'ims_price_lists', 'ims_pricelists', 'normal' );
add_meta_box( 'price-list-package', __( 'Packages', $this->domain ), 'ims_price_lists_packages', 'ims_pricelists', 'normal' );

add_meta_box( 'new_package', __( 'New Package', $this->domain ), 'ims_new_package', 'ims_packages', 'normal' );
add_meta_box( 'packages', __( 'Packages', $this->domain ), 'ims_package_list', 'ims_packages', 'normal' );
add_meta_box( 'new_promo', __( 'Promotion', $this->domain ), 'ims_new_promotion', 'ims_promotions', 'normal' );
?>

<ul class="ims-tabs add-menu-item-tabs">
<?php foreach( $tabs as $tabid => $tab ){
	echo '<li class="tabs"><a href="#' . $tabid . '">' . $tab . '</a></li>';
} ?>
</ul>

<?php 
foreach( $tabs as $tabid => $tabname ){
	echo '<div id="'.$tabid.'" class="ims-box" >';
		do_action( "ims_pricing_{$tabid}_tab", &$this );
	echo '</div>';
}

function ims_pricelist_tab( $ims ){
	echo '<div class="inside-col2">';
		do_meta_boxes( 'ims_pricelists', 'normal', $ims );
	echo '</div><div class="inside-col1">';
		do_meta_boxes( 'ims_pricelists', 'side', $ims );
	echo '</div><div class="clear"></div>';
}


function ims_packages_tab( $ims ){
	echo '<div class="inside-col2">';
		do_meta_boxes( 'ims_packages', 'normal', $ims );
	echo '</div><div class="inside-col1">';
		do_meta_boxes( 'ims_packages', 'side', $ims );
	echo '</div><div class="clear"></div>';
}

function ims_new_pricelist( $ims ){
	echo '<form method="post" action="#price-list" >
		<p><label>'. __( 'Name', $ims->domain ) .' <input type="text" name="pricelist_name" class="regular-text" /></label>
		<input type="submit" name="newpricelist" value="' . esc_attr__( 'Add List', $ims->domain ) . '" class="button" /></p>';
		wp_nonce_field( 'ims_new_pricelist' );	
	echo '</form>';
}

function ims_new_package( $ims ){
	echo '<form method="post" action="#packages" >
		<p><label>'. __( 'Name', $ims->domain ) .' <input type="text" name="package_name" class="regular-text" /></label>
		<input type="submit" name="newpackage" value="' . esc_attr__( 'Add Package', $ims->domain ) . '" class="button" /></p>';
		wp_nonce_field( 'ims_new_packages' );	
	echo '</form>';
}

function ims_price_lists( $ims ){
	$x = 0;
	$dlist = get_option( 'ims_pricelist' );
	?>
	<p><small><?php _e( 'Add options by dragging image sizes or packages into the desired list.', $ims->domain )?>
	<?php _e( 'Check the box next to the price to make size downloadable, or image will have to be shipped.', $ims->domain )?></small></p>
	<?php
	foreach( $ims->get_pricelists( ) as $list ){
		$meta = get_post_meta( $list->ID, '_ims_list_opts', true );
		?>
		<form method="post" id="ims-list-<?php echo $list->ID?>" action="<?php echo $ims->pageurl . '#price-list'?>" >
			<table class="ims-table price-list">
			<thead>
				<tr class="bar">
					<?php if( $list->ID == $dlist ){
						echo '<th class="default"><input name="listid" type="hidden" class="listid" value="' . esc_attr( $list->ID ). '" /></th>';
					}else{
						echo '<th class="trash"><a href="#">x</a><input type="hidden" name="listid" class="listid" value="' . esc_attr( $list->ID ). '" /></th>';
					}?>
					<th colspan="5" class="itemtop inactive"><?php echo $list->post_title?><a href="#">[+]</a></th>
				</tr>
			</thead>
			<tbody class="content">
				<?php 
				if( $sizes = get_post_meta( $list->ID, '_ims_sizes', true) ){
					unset($sizes['random']); 
					foreach( $sizes as $size ){
						if( empty( $size['name'] ) ) continue;
				?>
				<tr class="alternate size">
					<td class="move" title="<?php _e( 'Move to list', $ims->domain )?>">&nbsp;</td>
					<td>
						<?php
							if( isset($size['ID']) ){
								echo $size['name'].': '; $package_sizes = '';
								foreach( (array)get_post_meta($size['ID'], '_ims_sizes', true ) as $package_size => $count){
									if( is_array($count) ) $package_sizes .= $package_size.' '.$count['unit'].'( '.$count['count'].' ), ';
									else $package_sizes .= $package_size.'( '.$count.' ), '; 
								}
								echo rtrim( $package_sizes, ', ' );
							}else {
								echo $size['name'];	
								if( isset( $size['download']) ) 
									echo " <em>".__( 'downloadable.', $ims->domain )."</em>";
							}
						?>
					</td>
					<td align="right">
						<?php 
							if( isset($size['ID']) ){
								printf( $ims->cformat[$ims->loc], get_post_meta( $size['ID'], '_ims_price', true) );
							?><input type="hidden" name="sizes[<?php echo $x?>][ID]" class="id" value="<?php echo esc_attr( $size['ID'] )?>"/>
							<input type="hidden" name="sizes[<?php echo $x?>][name]" class="name"value="<?php echo esc_attr( $size['name'] )?>"/> <?php
							}else{
								printf( $ims->cformat[$ims->loc], $size['price'] );
							?><input type="hidden" name="sizes[<?php echo $x?>][name]" class="name"value="<?php echo esc_attr( $size['name'] )?>"/>
							<input type="hidden" name="sizes[<?php echo $x?>][price]" class="price" value="<?php echo esc_attr( $size['price'] )?>"/><?php
							}
						?>
					</td>
					<td> 
						<?php 
							if( isset( $size['unit'] ) && isset( $ims->units[$size['unit']]) ) {
								echo $ims->units[$size['unit']] , '<input type="hidden" class="unit" name="sizes[',$x, '][unit]" value="', $size['unit'] , '" />';
							}
						?>
					</td>
					<td title="<?php _e( 'Check to make size downloadable', $ims->domain ) ?>" class="download">
					<?php 
						echo '<input type="checkbox" name="sizes[',$x, '][download]" ' . checked( true , isset( $size['download'] ), false ) . ' class="downloadable" value="1" title="', __( 'Check to make size downloadable', $ims->domain ) , '" />'?></td>
					<td class="x" title="<?php _e( 'Delete', $ims->domain )?>">x</td>
				</tr>
				<?php $x++; 
					}
				}?>
				<tr class="filler"><td scope="row" colspan="6"><?php _e( 'Add options by dragging image sizes here', $ims->domain )?></td></tr>
			</tbody>
			<tfoot class="content">
					<tr>
						<td colspan="6"><label><?php _e( 'Name', $ims->domain )?>
							<input type="text" name="list_name" value="<?php echo esc_attr( $list->post_title )?>" class="regular-text" /></label>
						</td>
					</tr>
					<tr class="label">
						<td colspan="6"><label><?php _e( 'BW', $ims->domain )?>
							<input type="text" name="_ims_bw" value="<?php echo $ims->format_price( $meta['ims_bw'] )  ?>"/></label>							
							<label><?php _e( 'Sepia', $ims->domain )?>
								<input type="text" name="_ims_sepia" value="<?php echo $ims->format_price( $meta['ims_sepia'] ) ?>" /></label>						
							<label><?php _e( 'Local Shipping', $ims->domain )?>
								<input type="text" name="_ims_ship_local" value="<?php echo $ims->format_price($meta['ims_ship_local'] )  ?>" /></label>					
							<label><?php _e( 'International Shipping', $ims->domain )?>
								<input type="text" name="_ims_ship_inter" value="<?php echo $ims->format_price( $meta['ims_ship_inter'] ) ?>" /></label>
						</td>
					</tr>
					<?php do_action( 'ims_pricelist_options', $list->ID, &$this )?>
					<tr>
						<td colspan="6" align="right">
							<input type="hidden" name="sizes[random]" value="<?php echo rand(0,3000)?>"/>
							<input type="submit" name="updatelist" value="<?php esc_attr_e( 'Update', $ims->domain )?>" class="button-primary" />
						</td>
					</tr>
				</tfoot>
			</table>
			<?php wp_nonce_field( 'ims_pricelist' )?>
		</form>
		<?php
	}
}

function ims_price_lists_packages( $ims ){
	$x = 0;
	?>
	<form method="post" action="<?php echo $ims->pageurl.'#price-list'?>" >
		<table class="ims-table package-list"> 
			<tbody>
			<?php foreach( $ims->get_packages( ) as $package ):?>
			<tr class="package size alternate">
				<td class="move" title="<?php _e( 'Move to list', $ims->domain )?>">&nbsp;</td>
				<td><?php echo $package->post_title?>: 
				<?php $sizes = ''; 
					foreach((array) get_post_meta( $package->ID, '_ims_sizes', true) as $size => $count){
						if( is_array($count)) $sizes .= $size.' '.$count['unit'].'( '.$count['count'].' ), ';
						else $sizes .= $size.'( '.$count.' ), '; 
					} echo rtrim($sizes, ', ' );
				?>
				</td>
				<td align="right">
					<?php printf($ims->cformat[$ims->loc], get_post_meta($package->ID, '_ims_price', true) )?>
					<input type="hidden" name="sizes[<?php echo $x?>][ID]" class="id" value="<?php echo esc_attr( $package->ID )?>"/>
					<input type="hidden" name="sizes[<?php echo $x?>][name]" class="name" value="<?php echo esc_attr( $package->post_title )?>"/>
				</td>
				<td class="hidden">&nbsp;</td>
				<td class="hidden">
					<input type="checkbox" name="sizes[<?php echo $x?>][download]" value="1" title="<?php _e( 'downloadable', $ims->domain )?>" class="downloadable"/>
				</td>
				<td class="x" title="<?php _e( 'Delete', $ims->domain )?>">x</td>
			</tr>
			<?php $x++; endforeach?>
			</tbody>
		</table>
	</form>
	<?php

}

function ims_package_list( $ims ){
	$x=0;
	?>
	<p><small><?php _e( 'Add options by dragging image sizes into the desired package.', $ims->domain )?></small></p>
	<?php
	foreach( $ims->get_packages( ) as $package ){ 
		$price = get_post_meta( $package->ID, '_ims_price', true );
		?>
		<form method="post" id="package-list-<?php echo $package->ID?>" action="<?php echo $ims->pageurl.'#packages'?>" >
		<table class="ims-table package-list"> 
			<thead>
				<tr class="bar">
					<th class="trash"><a href="#">x</a><input type="hidden" name="packageid" class="packageid" value="<?php echo esc_attr( $package->ID )?>" /></th>
					<th colspan="4" class="itemtop inactive"><?php echo $package->post_title?><a href="#">[+]</a></th>
				</tr>
			</thead>
			<tbody class="content">
			<?php 
			if( $sizes = get_post_meta( $package->ID, '_ims_sizes', true ) ) :
				foreach( $sizes as $size => $count ) :
					if( is_numeric($size) ) continue; 
			?>
				<tr class="package size alternate">
					<td class="move">&nbsp;</td>
					<td class="packagename"><?php echo $size?></td>
					<td class="count">
						<input type="hidden" name="sizes[<?php echo $x?>][name]" class="name" value="<?php echo esc_attr( $size )?>" />
						<?php if( is_array( $count) ){?>
						<input type="text" name="sizes[<?php echo $x?>][count]" value="<?php echo esc_attr( $count['count'] )?>" title="<?php _e( 'Quantity', $ims->domain )?>" />
						<?php }else{?>
						<input type="text" name="sizes[<?php echo $x?>][count]" value="<?php echo esc_attr( $count )?>" title="<?php _e( 'Quantity', $ims->domain )?>" />
						<?php }?>
					</td>
					<td> <?php echo $count['unit']?>
						<input type="hidden" name="sizes[<?php echo $x?>][unit]" value="<?php echo esc_attr( $count['unit'] )?>" />
					</td>
					<td class="x">x</td>
				</tr>
			<?php $x++;
				endforeach;
			endif;?>
			<tr class="filler"><td colspan="5"><?php _e( 'Add options by dragging image sizes here', $ims->domain )?></td></tr>
			</tbody>
			<tfoot class="content">
				<tr class="inforow">
					<td>&nbsp;</td>
					<td>
						<label><?php _e( 'Name', $ims->domain )?>
						<input type="text" name="packagename" value="<?php echo esc_attr( $package->post_title )?>" class="inputmd" /></label>
					</td>
					<td colspan="3">
						<label><?php _e( 'Price', $ims->domain )?>
						<input type="text" name="packageprice" value="<?php echo esc_attr( $price )?>" class="inputsm" /></label>
					</td>
				</tr>
				<tr class="inforow">
					<td>&nbsp;</td>
					<td colspan="4" align="right">
						<input type="hidden" vname="sizes[random]" alue="<?php echo rand(0,3000)?>"/>
						<input type="submit" name="updatepackage" value="<?php esc_attr_e( 'Update', $ims->domain )?>" class="button-primary" />
					</td>
				</tr>
			</tfoot>
		</table>
		<?php wp_nonce_field( 'ims_update_packages' )?>
		</form>
		<?php
	}
}

function ims_image_sizes( $ims ){
	$x = 0;
	?>
	<form method="post" action="<?php echo $ims->pageurl.'#price-list'?>" >
		<table class="ims-table sizes-list"> 
			<thead>
				<tr class="alternate">
					<td>&nbsp;</td>
					<td class="name"><?php _e( 'Name', $ims->domain )?></td>
					<td class="price"><?php _e( 'Price', $ims->domain )?></td>
					<td><?php _e( 'Width', $ims->domain )?></td>
					<td><?php _e( 'Height', $ims->domain )?></td>
					<td><?php _e( 'Unit', $ims->domain )?></td>
					<td class="col-hide">&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			</thead>
			<tbody>
			<?php 
			foreach((array)get_option( 'ims_sizes') as $size ): 
				$sizedata = array( );
				$price = isset( $size['price'] ) ? $size['price'] : false;
				$sizedata = isset( $size['w'] ) ? array( $size['w'], $size['h'] ) : explode("x",strtolower($size['name']));
			?>
				<tr class="imgsize size alternate">
					<td class="move" title="<?php _e( 'Move to list', $ims->domain )?>">&nbsp;</td>
					<td><span class="hidden"><?php echo $size['name']?></span>
					<input type="text" name="sizes[<?php echo $x ?>][name]" class="name" value="<?php echo esc_attr( $size['name'] )?>" />
					</td>
					<td align="right" class="count">
						<span class="hidden price"><?php printf( $ims->cformat[$ims->loc], $price ) ?></span>
						<input type="text" name="sizes[<?php echo $x?>][count]" value="<?php echo $size['name']?>" class="hidden" />
						<input type="text" name="sizes[<?php echo $x ?>][price]" value="<?php echo esc_attr( $price )?>" class="price" />
					</td>
					<td class="d"><input type="text" name="sizes[<?php echo $x ?>][w]" value="<?php echo esc_attr( $sizedata[0] )?>" /></td>
					<td class="d"><input type="text" name="sizes[<?php echo $x ?>][h]" value="<?php echo esc_attr( $sizedata[1] )?>" /></td>
					<td><?php $ims->dropdown_units( "sizes[$x][unit]", $size['unit'] )?><span class="hidden"><?php echo $ims->units[$size['unit']]?></span></td>
					<td class="col-hide"><input type="checkbox" name="sizes[<?php echo $x?>][download]" value="1" title="<?php _e( 'downloadable', $ims->domain )?>" class="downloadable" /></td>
					<td class="x" title="<?php _e( 'Delete', $ims->domain )?>">x</td>
				</tr>
			<?php $x++; endforeach?>
			</tbody>
			<tfoot>
				<tr class="copyrow">
					<td>&nbsp;</td>
					<td><input type="text" value="" class="name"/></td>
					<td><input type="text" class="price" /></td>
					<td><input type="text" class="width" /></td>
					<td><input type="text" class="height" /></td>
					<td><?php $ims->dropdown_units( '', '')?></td>
					<td class="col-hide"></td>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<td colspan="8"><small><?php _e( 'in:inches &bull; cm:centimeters &bull; px:pixels', $ims->domain )?></small></td>
				</tr>
				<tr class="addrow">
					<td scope="row" colspan="8" align="center">
						<input type="submit" name="updateimglist" value="<?php esc_attr_e( 'Update sizes', $ims->domain )?>" class="button-primary" />
					</td>
				</tr>
			</tfoot>
		</table>
		<?php wp_nonce_field( 'ims_imagesizes' )?>
	</form>
	<?php
}

function ims_new_promotion( $ims ){
	 
	 if( $_GET['action'] != 'new' ) {
		$promo = get_post( $_GET['action'] );
		$meta 	= get_post_meta( $_GET['action'] , '_ims_promo_data' , true );
		$date 	= strtotime( $promo->post_date );
		$expire	= strtotime( $promo->post_expire );
		
		$_POST['promo_name'] = $promo->post_title;
		$_POST['startdate'] = date_i18n( 'Y-m-d', $date );
		$_POST['starts'] = date_i18n( $ims->dformat, $date );
		$_POST['expires'] = date_i18n( $ims->dformat, $expire );
		$_POST['expiration_date'] = date_i18n( 'Y-m-d', $expire );
		foreach( (array)$meta as $key => $val ) 	$_POST[$key] = $val; 
	 }
	
        $data = array();
	foreach( array( 'promo_name', 'promo_code', 'starts', 'startdate', 'expires', 'expiration_date', 'promo_type', 'discount') as $key )
		$data[$key] = ( isset($_POST[$key]) ) ? esc_attr($_POST[$key] ) : false;
	extract( $data );
	?>
	<form method="post" class="new-promo" action="#promotions" >
		<?php if( $_GET['action'] != 'new' ) ;?>
		<table class="ims-table">
			<tbody><tr>
				<td colspan="5">
					<label><?php _e( 'Type', $ims->domain )?>
						<select name="promo_type" id="promo_type">
							<?php foreach( $ims->promo_types as $key => $label ){?>
							<option value="<?php echo esc_attr( $key ) ?>"<?php selected( $promo_type, $key )?>><?php echo $label?></option>
							<?php }?>
						</select>
					</label>
				</td>
			</tr>
			<tr>
				<td>
					<label><?php _e( 'Name',$ims->domain )?> <input name="promo_name" type="text" class="regular-text" value="<?php echo esc_attr( $promo_name ) ?>"/></label>
				</td>
				<td>
					<label> <?php _e( 'Code',$ims->domain )?>	 <input name="promo_code" type="text" class="regular-text" value="<?php echo esc_attr( $promo_code ) ?>" /></label>
				</td>
				<td>
					<label><?php _e( 'Starts',$ims->domain )?> <input type="text" name="starts" id="starts" class="regular-text" value="<?php echo esc_attr( $starts )?>" /></label>
					<input type="hidden" name="start_date" id="start_date" value="<?php echo $startdate?>" />
				</td>
				<td>
					<label><?php _e( 'Expire',$ims->domain )?> <input type="text" name="expires" id="expires" class="regular-text" value="<?php echo esc_attr( $expires )?>" /></label>
					<input type="hidden" name="expiration_date" id="expiration_date" value="<?php echo esc_attr( $expiration_date ) ?>" />
				</td>
				<td>
					<label class="hide-free"> <?php _e( 'Discount', $ims->domain )?>
						<input type="text" name="discount" class="regular-text" value="<?php echo esc_attr( $discount ) ?>" <?php if( $promo_type == 3) echo ' disabled="disabled"' ?> /> 
					</label>
				</td>
			</tr>
			<tr>
				<td colspan="4">
					<?php 
					$logic = ( isset( $_POST['rules']['logic'] ) ) ? $_POST['rules']['logic'] : false ;
					$property = ( isset( $_POST['rules']['property'] ) ) ? $_POST['rules']['property'] : false;
					?>
					<?php _e( 'Conditions', $ims->domain )?> 
					<select name="rules[property]">
						<?php foreach( $ims->rules_property as $val => $label ) 
							echo '<option value="', esc_attr( $val ), '"', selected( $property, $val, false ), '>',$label, '</option>';
						?>
					</select>
					<select name="rules[logic]">
							<?php foreach( $ims->rules_logic as $val => $label ) 
							echo '<option value="', esc_attr( $val ), '"', selected( $logic, $val, false ), '>',$label, '</option>';
						?>
					</select>
					<input name="rules[value]" type="text" class="inpsm" value="<?php if( isset($_POST['rules']['value']) ) echo esc_attr( $_POST['rules']['value'] ) ?>"/>
				</td>
				<td width="25%" align="right">
					<?php $action = ( $_GET['action'] == 'new' ) ? __( 'Add promotion', $ims->domain ) : __( 'Update', $ims->domain ) ?>
					<input type="submit" name="cancel" value="<?php esc_attr_e( 'Cancel', $ims->domain )?>" class="button" />
					<input type="hidden" name="promotion_id" value="<?php if( $_GET['action'] != 'new' ) echo esc_attr($_GET['action'])?>"/>
					<input type="submit" name="promotion" value="<?php echo esc_attr( $action )?>" class="button-primary" />
				</td>
			</tr></tbody>
		</table>
		<?php wp_nonce_field( 'ims_promotion' )?>
	</form>
	<?php
}

function ims_promotions_tab( $ims ){
	$nonce 		= '_wpnonce='.wp_create_nonce( 'ims_link_promo' );
	if( isset($_GET['action']) ) do_meta_boxes( 'ims_promotions', 'normal', $ims );
	
	$css			= ' alternate';
	$page		= ( isset($_GET['p'] ) ) ? $_GET['p'] : 1;
	$columns 	= (array)get_column_headers( 'ims_gallery_page_ims-pricing' );
	$hidden 	= (array)get_hidden_columns( 'ims_gallery_page_ims-pricing' );
	$promos 	= new WP_Query( array( 'post_type' => 'ims_promo', 'paged' => $page, 'posts_per_page' => $ims->per_page ) );
	
	$start = ($page - 1) * $ims->per_page;
	$page_links = paginate_links( array(
		'base' => $ims->pageurl . '%_%#promotions',
		'format' => '&p=%#%',
		'prev_text' => __( '&laquo;' ),
		'next_text' => __( '&raquo;' ),
		'total' => $promos->max_num_pages,
		'current' => $page,
	));
	
	?>
	<form method="post" action="#promotions" >
		<div class="tablenav">
			<div class="alignleft actions">
			<select name="action">
				<option value="" selected="selected"><?php _e( 'Bulk Actions', $ims->domain )?></option>
				<option value="delete"><?php _e( 'Delete', $ims->domain )?></option>
			</select>
			<input type="submit" value="<?php esc_attr_e( 'Apply', $ims->domain );?>" name="doaction" class="button-secondary" /> |
			<a href="<?php echo $ims->pageurl ."&amp;$nonce&amp;action=new#promotions"?>" class="button"><?php _e( 'New Promotion', $ims->domain )?></a>
			</div>
		</div>
		
		<table class="widefat post fixed imstore-table">
			<thead><tr><?php print_column_headers( 'ims_gallery_page_ims-pricing' )?></tr></thead>
			<tbody>
			<?php foreach( $promos->posts as $promo){
				$css = ( $css == ' alternate') ? '' : ' alternate';
				$r = '<tr id="item-' . $promo->ID . '" class="iedit' . $css . '">';
				foreach( $columns as $column_id => $column_name ){
					$hide = ( $ims->in_array( $column_id, $hidden ) ) ? ' hidden':'' ;
					$meta = get_post_meta( $promo->ID , '_ims_promo_data', true );
					switch( $column_id ){
						case 'cb':
							$r .= '<th scope="row" class="column-' . $column_id . ' check-column">';
							$r .= '<input type="checkbox" name="promo[]" value="' . esc_attr( $promo->ID ) . '" /> </th>';
							break;
						case 'name':
							$r .= '<td class="column-' . $column_id . '" > ' . $promo->post_title . '<div class="row-actions">' ;
							$r .= '<span><a href="' . $ims->pageurl . "&amp;$nonce&amp;action=$promo->ID#promotions" . '">' . __( "Edit", $ims->domain ) . '</a></span> |';
							$r .= '<span class="delete"><a href="' . $ims->pageurl . "&amp;$nonce&amp;delete=$promo->ID#promotions" . '"> ' . __( "Delete", $ims->domain ) . '</a></span>';
							$r .= '</div></td>';
							break;
						case 'code':
							$r .= '<td class="column-' . $column_id . $hide . '" > ' ;
							if( isset( $meta['promo_code'] ) ) $r .= $meta['promo_code'];
							$r .= '</td>' ;
							break;
						case 'starts':
							$r .= '<td class="column-' . $column_id . $hide .'" > ' . date_i18n( $ims->dformat, strtotime( $promo->post_date ) ) . '</td>' ;
							break;
						case 'expires':
							$r .= '<td class="column-' . $column_id . $hide . '" > ';
							if( isset( $promo->post_expire ) ) $r .= date_i18n( $ims->dformat, strtotime( $promo->post_expire ) );
							$r .= '</td>' ;
							break;
						case 'type':
							$r .= '<td class="column-' . $column_id . $hide . '" > ' ;
							if( isset( $meta['promo_type'] ) ) $r .= $ims->promo_types[$meta['promo_type'] ] ;
							$r .= '</td>' ;
							break;
						case 'discount':
							$r .= '<td class="column-' . $column_id . $hide . '" > ' ;
							if( isset( $meta['discount'] ) ) $r .= $meta['discount'];
							if( isset( $meta['items'] ) ) $r .= $meta['items'];
							$r .= '</td>' ;
							break;
						}
				}
				echo $r .= '</tr>';
			}?>
			</tbody>
		</table>
	</form>
	<div class="tablenav">
		<?php if ( $page_links ) : ?>
		<div class="tablenav-pages"><?php $page_links_text = sprintf( '<span class="displaying-num">' . __( 'Displaying %s&#8211;%s of %s' ) . '</span>%s',
			number_format_i18n( $start + 1 ),
			number_format_i18n( min( $page * $ims->per_page, $promos->found_posts ) ),
			'<span class="total-type-count">' . number_format_i18n( $promos->found_posts ) . '</span>',
			$page_links
		); echo $page_links_text; ?></div>
		<?php endif ?>
	</div>
	<?php 
}