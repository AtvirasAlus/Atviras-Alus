<?php 

/**
 *Pricing page
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2011
 *@since 0.5.0
*/

if(!current_user_can('ims_change_pricing')) 
	die();

//clear cancel post data
if(isset($_POST['cancel']))
	wp_redirect($pagenowurl);
	
//create new list	
if(isset($_POST['newpricelist'])){
	check_admin_referer('ims_newpricelist');
	$errors = create_ims_list();
}

//create new package
if(isset($_POST['newpackage'])){
	check_admin_referer('ims_newpackage');
	$errors = create_ims_package();
}

//new/update promotion
if(isset($_POST['newpromotion']) || isset($_POST['updatepromotion'])){
	check_admin_referer('ims_promotion');
	$errors = add_ims_promotion();
}

//update list
if(isset($_POST['updatelist'])){
	check_admin_referer('ims_pricelist');
	$errors = update_ims_list();
}

//update packages
if(isset($_POST['updatepackage'])){
	check_admin_referer('ims_newpackages');
	$errors = update_ims_package();
}

//update images
if(isset($_POST['updateimglist'])){
	check_admin_referer('ims_imagesizes');
	$_POST = array_diff_key($_POST,array('_wpnonce'=>'','_wp_http_referer'=>'','updateimglist'=>''));
	$sizes = $this->array_filter_recursive($_POST['sizes']); 
	update_option('ims_sizes',$sizes);
	wp_redirect($pagenowurl."&ms=37");
}

//bulk action
if(isset($_POST['doaction'])){
	check_admin_referer('ims_promotions');
	switch($_POST['action']){
		case 'delete':
			$errors = delete_ims_promotions();
			break;
		default:
	}
}

$x 			= 0; 
$nonce 		= '_wpnonce='.wp_create_nonce('ims_link_promo');
$columns 	= (array)get_column_headers('ims_gallery_page_ims-pricing');
$hidden 	= implode('|',(array)get_hidden_columns('ims_gallery_page_ims-pricing'));
$format 	= array('',"$this->sym%s","$this->sym %s","%s$this->sym","%s $this->sym"); 

$promos 	= get_ims_promos();
$packages 	= get_ims_packages();
$dlist 		= get_option('ims_pricelist');

$type[1]	= __('Percent',ImStore::domain); 
$type[2] 	= __('Amount',ImStore::domain); 
$type[3] 	= __('Free Shipping',ImStore::domain);
?>

<ul class="ims-tabs add-menu-item-tabs">
	<li class="tabs"><a href="#price-list"><?php _e('Price lists',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#packages"><?php _e('Packages',ImStore::domain)?></a></li>
	<li class="tabs"><a href="#promotions"><?php _e('Promotions',ImStore::domain)?></a></li>
</ul>

<div id="price-list" class="ims-box" >
	<div class="inside-col2">
		<div class="postbox">
			<div class="handlediv" ><br /></div>
			<h3 class='hndle'><span><?php _e('New Price List',ImStore::domain)?></span></h3> 
			<div class="inside">
				<form method="post" action="<?php echo $pagenowurl?>" >
					<p><label><?php _e('Name',ImStore::domain)?> <input name="list_name" type="text" class="inputlg" /></label> 
					<input type="submit" name="newpricelist" value="<?php _e('Add List',ImStore::domain)?>" class="button" /></p>
					<?php wp_nonce_field('ims_newpricelist')?>
			 </form>
			</div>
		</div>
		<p><small><?php _e('Add options by dragging image sizes or packages into the desired list.',ImStore::domain)?></small></p>
		<div class="postbox price-list-box">
			<div class="handlediv" ><br /></div>
			<h3 class='hndle'><span><?php _e('Price Lists',ImStore::domain)?></span></h3>
			<div class="inside">
			<?php 
			foreach((array)get_ims_pricelists() as $list):
			$meta = get_post_meta($list->ID,'_ims_list_opts',true)
			?>
			<form method="post" id="ims-list-<?php echo $list->ID?>" action="<?php echo $pagenowurl.'#price-list'?>" >
			<table class="ims-table price-list"> 
				<thead>
					<tr class="bar">
						<?php if($list->ID == $dlist){?>
						<th class="default"><input name="listid" type="hidden" class="listid" value="<?php echo $list->ID?>" /></th> 
						<?php }else{?>
						<th class="trash"><a href="#">x</a><input type="hidden" name="listid" class="listid" value="<?php echo $list->ID?>" /></th>
						<?php }?>
						<th colspan="5" class="itemtop inactive"><?php echo $list->post_title?><a href="#">[+]</a></th>
					</tr>
				</thead>
				<tbody class="content">
				<?php 
				if($sizes = get_post_meta($list->ID,'_ims_sizes',true)){
					 unset($sizes['random']); foreach($sizes as $size){ 
				?>
					<tr class="alternate size">
						<td class="x" scope="row" title="<?php _e('Delete',ImStore::domain)?>">x</td>
						<td>
						<?php
							if($size['ID']){
								echo $size['name'].':'; $package_sizes = '';
								foreach((array)get_post_meta($size['ID'],'_ims_sizes',true) as $package_size => $count){
									if(is_array($count)) $package_sizes .= $package_size.' '.$count['unit'].'('.$count['count'].'),';
									else $package_sizes .= $package_size.'('.$count.'),'; 
								}
								echo rtrim($package_sizes,',');
							}else echo $size['name'];	
						?>
						</td>
						<td width="15%" align="right">
						<?php 
							if($size['ID']){
								printf($format[$this->loc],get_post_meta($size['ID'],'_ims_price',true));
							?><input type="hidden" name="sizes[<?php echo $x?>][ID]" value="<?php echo $size['ID']?>"/>
							<input type="hidden" name="sizes[<?php echo $x?>][name]" value="<?php echo $size['name']?>"/> <?php
							}else{
								printf($format[$this->loc],$size['price']);
							?><input type="hidden" name="sizes[<?php echo $x?>][name]" value="<?php echo $size['name']?>"/>
							<input type="hidden" name="sizes[<?php echo $x?>][price]" value="<?php echo $size['price']?>"/><?php
							}
						?>
						</td>
						<td> 
							<?php echo $this->units[$size['unit']]?>
							<input type="hidden" name="sizes[<?php echo $x?>][unit]" value="<?php echo $this->units[$size['unit']]?>" />
						</td>
						<td>
							<input type="checkbox" name="sizes[<?php echo $x?>][download]" value="1" <?php checked('1',$size['download'])?> title="<?php _e('downloadable',ImStore::domain)?>" />
						</td>
						<td class="move" title="<?php _e('Sort',ImStore::domain)?>">&nbsp;</td>
					</tr>
				<?php $x++; }}?>
					<tr class="filler"><td scope="row" colspan="6"><?php _e('Add options by dragging image sizes here',ImStore::domain)?></td></tr>
				</tbody>
				<tfoot class="content">
					<tr>
						<td scope="row" colspan="6"><label><?php _e('Name',ImStore::domain)?>
							<input type="text" name="list_name" value="<?php echo $list->post_title?>" class="inputmd" /></label>
						</td>
					</tr>
					<tr class="label">
						<td colspan="6" scope="row"><label><?php _e('BW',ImStore::domain)?>
							<input type="text" name="_ims_bw" value="<?php echo $meta['ims_bw']?>"/></label>							
							<label><?php _e('Sepia',ImStore::domain)?>
								<input type="text" name="_ims_sepia" value="<?php echo $meta['ims_sepia']?>" /></label>						
							<label><?php _e('Local Shipping',ImStore::domain)?>
								<input type="text" name="_ims_ship_local" value="<?php echo $meta['ims_ship_local']?>" /></label>					
							<label><?php _e('Internacional Shipping',ImStore::domain)?>
								<input type="text" name="_ims_ship_inter" value="<?php echo $meta['ims_ship_inter']?>" /></label>
						</td>
					</tr>
					<tr class="submit">
						<td scope="row" colspan="5" align="right">
							<input type="hidden" name="sizes[random]" value="<?php echo rand(0,3000)?>"/>
							<input type="submit" name="updatelist" value="<?php _e('Update',ImStore::domain)?>" class="button-primary" />
						</td>
						<td>&nbsp;</td>
					</tr>
				</tfoot>
			</table>
			<?php wp_nonce_field('ims_pricelist')?>
			</form>
			<?php endforeach?>
			</div>
		</div>
		<div class="postbox">
			<div class="handlediv"><br /></div>
			<h3 class='hndle'><span><?php _e('Packages',ImStore::domain)?></span></h3> 
			<div class="inside">
				<form method="post" action="<?php echo $pagenowurl.'#price-list'?>" >
					<table class="ims-table package-list"> 
						<tbody>
						<?php foreach((array)$packages as $package):?>
						<tr class="package size alternate">
							<td class="x" scope="row" title="<?php _e('Delete',ImStore::domain)?>">x</td>
							<td><?php echo $package->post_title?>:
							<?php $sizes = ''; 
								foreach((array)get_post_meta($package->ID,'_ims_sizes',true) as $size => $count){
									if(is_array($count)) $sizes .= $size.' '.$count['unit'].'('.$count['count'].'),';
									else $sizes .= $size.'('.$count.'),'; 
								} echo rtrim($sizes,',');
							?>
							</td>
							<td align="right">
								<?php printf($format[$this->loc],get_post_meta($package->ID,'_ims_price',true))?>
								<input type="hidden" name="sizes[<?php echo $x?>][ID]" value="<?php echo $package->ID?>"/>
								<input type="hidden" name="sizes[<?php echo $x?>][name]" value="<?php echo $package->post_title?>"/>
							</td>
							<td class="hidden">&nbsp;</td>
							<td class="hidden">
								<input type="checkbox" name="sizes[<?php echo $x?>][download]" value="1" title="<?php _e('downloadable',ImStore::domain)?>" />
							</td>
							<td class="move" title="<?php _e('Move to list',ImStore::domain)?>">&nbsp;</td>
						</tr>
						<?php $x++; endforeach?>
						</tbody>
					</table>
				</form>
			</div>
		</div>
	</div>
	<div class="inside-col1">
		<div class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class='hndle'>
				<span><?php _e('Image Sizes',ImStore::domain)?></span>
				<a href="#" class="add-image-size"><?php _e('Add image size',ImStore::domain)?></a>
			</h3>
			<div class="inside">
				<form method="post" action="<?php echo $pagenowurl.'#price-list'?>" >
				<table class="ims-table sizes-list"> 
					<thead>
						<tr class="alternate">
							<td scope="row">&nbsp;</td>
							<td><?php _e('Name',ImStore::domain)?></td>
							<td><?php _e('Price',ImStore::domain)?></td>
							<td><?php _e('Unit',ImStore::domain)?></td>
							<td class="col-hide">&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
					</thead>
					<tbody>
					<?php foreach((array)get_option('ims_sizes') as $size):$price = $size['price']?>
						<tr class="imgsize size alternate">
							<td scope="row" class="x" title="<?php _e('Delete',ImStore::domain)?>">x</td>
							<td><span class="hidden"><?php echo $size['name']?></span>
								<input type="text" name="sizes[<?php echo $x?>][name]" value="<?php echo $size['name']?>" />
							</td>
							<td align="right">
								<span class="hidden"><?php printf($format[$this->loc],$size['price'])?></span>
								<input type="text" name="sizes[<?php echo $x?>][price]" value="<?php echo $size['price']?>" />
							</td>
							<td><?php $this->dropdown_units("sizes[$x][unit]",$size['unit'])?><span class="hidden"><?php echo $this->units[$size['unit']]?></span></td>
							<td class="col-hide"><input type="checkbox" name="sizes[<?php echo $x?>][download]" value="1" title="<?php _e('downloadable',ImStore::domain)?>" /></td>
							<td class="move" title="<?php _e('Move to list',ImStore::domain)?>">&nbsp;</td>
						</tr>
					<?php $x++; endforeach?>
					</tbody>
					<tfoot>
						<tr class="copyrow">
							<td scope="row">&nbsp;</td>
							<td><input type="text" value="<?php echo $x?>" class="name"/></td>
							<td><input type="text" class="price" /></td>
							<td><?php $this->dropdown_units('','')?></td>
							<td class="col-hide">&nbsp;</td>
							<td>&nbsp;</td>
						</tr>
						<tr>
							<td colspan="6"><small><?php _e('in:inches &bull; cm:centimeters &bull; px:pixels',ImStore::domain)?></small></td>
						</tr>
						<tr class="addrow">
							<td scope="row" colspan="4" align="right">
								<input type="submit" name="updateimglist" value="<?php _e('Update sizes',ImStore::domain)?>" class="button-primary" />
							</td>
							<td colspan="2">&nbsp;</td>
						</tr>
					</tfoot>
				</table>
				<?php wp_nonce_field('ims_imagesizes')?>
				</form>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Packages -->
<div id="packages" class="ims-box" >
	<div class="inside-col2">
		<div class="postbox">
			<div class="handlediv"><br /></div>
			<h3 class='hndle'><span><?php _e('New Package',ImStore::domain)?></span></h3> 
			<div class="inside">
				<form method="post" action="<?php echo $pagenowurl.'#packages'?>" >
					<p><label><?php _e('Name',ImStore::domain)?> <input name="package_name" type="text" class="inputlg" /></label> 
					<input type="submit" name="newpackage" value="<?php _e('Add Package',ImStore::domain)?>" class="button" /></p>
					<?php wp_nonce_field('ims_newpackage')?>
				 </form>
			</div>
		</div>
		<p><small><?php _e('Add options by dragging image sizes into the desired package.',ImStore::domain)?></small></p>
		<div class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class='hndle'><span><?php _e('Packages',ImStore::domain)?></span></h3>
			<div class="inside">
			<?php foreach((array)$packages as $package){ $price = get_post_meta($package->ID,'_ims_price',true);?>
			<form method="post" id="package-list-<?php echo $package->ID?>" action="<?php echo $pagenowurl.'#packages'?>" >
				<table class="ims-table package-list"> 
					<thead>
						<tr class="bar">
							<th class="trash">
								<a href="#">x</a><input type="hidden" name="packageid" class="packageid" value="<?php echo $package->ID?>" />								</th>
							<th colspan="4" class="itemtop inactive"><?php echo $package->post_title?><a href="#">[+]</a></th>
						</tr>
					</thead>
					<tbody class="content">
					<?php $sizes = get_post_meta($package->ID,'_ims_sizes',true); if($sizes):; 
						foreach($sizes as $size => $count):if(is_numeric($size)) continue;?>
						<tr class="package size alternate">
							<td scope="row" class="x">x</td>
							<td><?php echo $size?></td>
							<td>
								<input type="hidden" name="sizes[<?php echo $x?>][name]" value="<?php echo $size?>" class="inputsm" />
								<?php if(is_array($count)){?>
								<input type="text" name="sizes[<?php echo $x?>][count]" value="<?php echo $count['count']?>" class="inputsm" title="<?php _e('Quantity',ImStore::domain)?>" />
								<?php }else{?>
								<input type="text" name="sizes[<?php echo $x?>][count]" value="<?php echo $count?>" class="inputsm" title="<?php _e('Quantity',ImStore::domain)?>" />
								<?php }?>
							</td>
							<td>
								<?php echo $count['unit']?>
								<input type="hidden" name="sizes[<?php echo $x?>][unit]" value="<?php echo $count['unit']?>" />
							</td>
							<td class="move" title="<?php _e('Sort',ImStore::domain)?>">&nbsp;</td>
						</tr>
					<?php $x++; endforeach; endif?>
						<tr class="filler">
							<td scope="row" colspan="5"><?php _e('Add options by dragging image sizes here',ImStore::domain)?></td>
						</tr>
					</tbody>
					<tfoot class="content">
						<tr class="inforow">
							<td scope="row">&nbsp;</td>
							<td>
								<label><?php _e('Name',ImStore::domain)?>
								<input type="text" name="packagename" value="<?php echo $package->post_title?>" class="inputmd" /></label>
							</td>
							<td colspan="3">
								<label><?php _e('Price',$ImStore->domain)?>
								<input type="text" name="packageprice" value="<?php echo $price?>" class="inputsm" /></label>
							</td>
						</tr>
						<tr class="inforow submit">
							<td scope="row" colspan="4" align="right">
								<input type="hidden" vname="sizes[random]" alue="<?php echo rand(0,3000)?>"/>
								<input type="submit" name="updatepackage" value="<?php _e('Update',ImStore::domain)?>" class="button-primary" />
							</td>
							<td>&nbsp;</td>
						</tr>
					</tfoot>
				</table>
			<?php wp_nonce_field('ims_newpackages')?>
			</form>
			<?php }?>
			</div>
		</div>
	</div>
	<div class="inside-col1">
		<div class="postbox">
			<div class="handlediv" title="Click to toggle"><br /></div>
			<h3 class='hndle'>
				<span><?php _e('Image Sizes',ImStore::domain)?></span>
				<a href="#" class="add-image-size"><?php _e('Add image size',ImStore::domain)?></a>
			</h3>
			<div class="inside">
				<form method="post" action="<?php echo $pagenowurl.'#packages'?>" >
				<?php wp_nonce_field('ims_imagesizes')?>
				<table class="ims-table sizes-list"> 
					<tbody>
						<tr class="alternate">
							<td scope="row">&nbsp;</td>
							<td><?php _e('Name',ImStore::domain)?></td>
							<td><?php _e('Price',ImStore::domain)?></td>
							<td><?php _e('Unit',ImStore::domain)?></td>
							<td>&nbsp;</td>
						</tr>
					<?php foreach((array)get_option('ims_sizes') as $size):$price = $size['price']?>
						<tr class="imgsize size alternate">
							<td scope="row" class="x">x</td>
							<td><span class="hidden"><?php echo $size['name']?></span>
								<input type="text" name="sizes[<?php echo $x?>][name]" value="<?php echo $size['name']?>" class="input" />
							</td>
							<td>
								<input type="text" name="sizes[<?php echo $x?>][count]" class="inputsm hidden" />
								<input type="text" name="sizes[<?php echo $x?>][price]" value="<?php echo $size['price']?>" class="price" />
							</td>
							<td><?php $this->dropdown_units("sizes[$x][unit]",$size['unit'])?><span class="hidden"><?php echo $this->units[$size['unit']]?></span></td>
							<td class="move" title="<?php _e('Move to list',ImStore::domain)?>">&nbsp;</td>
						</tr>
					<?php $x++; endforeach?>
					</tbody>
					<tfoot>
						<tr class="copyrow">
							<td scope="row">&nbsp;</td>
							<td><input type="text" value="<?php echo $x?>" class="name" /></td>
							<td><input type="text" class="price" /></td>
							<td><?php $this->dropdown_units('','')?></td>
							<td colspan="2">&nbsp;</td>
						</tr>
						<tr>
							<td colspan="6"><small><?php _e('in:inches &bull; cm:centimeters &bull; px:pixels',ImStore::domain)?></small></td>
						</tr>
						<tr class="addrow">
							<td scope="row" colspan="4" align="right">
								<input type="submit" name="updateimglist" value="<?php _e('Update sizes',ImStore::domain)?>" class="button-primary" />
							</td>
							<td colspan="2">&nbsp;</td>
						</tr>
					</tfoot>
				</table>
				</form>
			</div>
		</div>
	</div>
	<div class="clear"></div>
</div>

<!-- Promotions -->
<div id="promotions" class="ims-box" >
<?php if(isset($_GET['newpromo']) || isset($_GET['edit']) || isset($_POST['newpromotion'])){?>
	<div class="postbox">
		<div class="handlediv"><br /></div>
		<h3 class='hndle'><span><?php 
			if($_GET['edit']) _e('Promotion Information',ImStore::domain); 
			else _e('New Promotion',ImStore::domain);?>
		</span></h3> 
		<div class="inside<?php echo $css?>">
			<form method="post" class="new-promo" action="<?php echo $pagenowurl.'#promotions'?>" >
			<?php wp_nonce_field('ims_promotion')?>
			<?php if(isset($_GET['edit'])){
				foreach($promos as $promo){
					if($promo->ID == $_GET['edit']){
						$_POST = (array)$promo;
						$_POST['start_date'] = date_i18n('Y-m-d',$promo->starts);
						$_POST['starts'] = date_i18n($this->dformat,$promo->starts);
						$_POST['expires'] = date_i18n($this->dformat,$promo->expires);
						$_POST['expiration_date'] = date_i18n('Y-m-d',$promo->expires);
					}
				}
			}?>
				<table class="ims-table"> 
					<tbody>
						<tr>
							<td colspan="7">
								<label><?php _e('Type',ImStore::domain)?>
								<select name="promo_type" id="promo_type">
									<?php foreach($type as $key => $label){?>
									<option value="<?php echo $key?>"<?php selected($_POST['promo_type'],$key)?>><?php echo $label?></option>
									<?php }?>
								</select>
								</label>
							</td>
						</tr>
						<tr>
							<td>
								<label><?php _e('Name',ImStore::domain)?>
									<input name="promo_name" type="text" class="inputxl" value="<?php echo $_POST['promo_name']?>"/>
								</label>
							</td>
							<td>
								<label> <?php _e('Code',ImStore::domain)?>
									<input name="promo_code" type="text" class="inputxl" value="<?php echo $_POST['promo_code']?>" />
								</label>
							</td>
							<td>
								<label><?php _e('starts',ImStore::domain)?> 
									<input type="text" name="starts" id="starts" class="inputxl" value="<?php echo $_POST['starts']?>" />
								</label>
								<input type="hidden" name="start_date" id="start_date" value="<?php echo $_POST['start_date']?>" />
							</td>
							<td>
								<label><?php _e('Expire',ImStore::domain)?> 
									<input type="text" name="expires" id="expires" class="inputxl" value="<?php echo $_POST['expires']?>" />
								</label>
								<input type="hidden" name="expiration_date" id="expiration_date" value="<?php echo $_POST['expiration_date']?>" />
							</td>
							<td>
								<label class="hide-free"> <?php _e('Discount',ImStore::domain)?>
									<input type="text" name="discount" class="inputxl" value="<?php echo $_POST['discount']?>" <?php echo($_POST['promo_type'] == 3) ?'disabled="disabled"':''?> /> 
								</label>

							</td>
						</tr>
						<tr>
							<td colspan="4">
								<?php _e('Conditions',ImStore::domain)?> 
								<select name="rules[property]">
									<option value="items"<?php selected($_POST['rules']['property'],'items')?>><?php _e('Item quantity',ImStore::domain)?></option>
									<option value="total"<?php selected($_POST['rules']['property'],'total')?>><?php _e('Total amount',ImStore::domain)?></option>
									<option value="subtotal"<?php selected($_POST['rules']['property'],'subtotal')?>><?php _e('Subtotal amount',ImStore::domain)?></option>
									</select>
								<select name="rules[logic]">
									<option value="equal"<?php selected($_POST['rules']['logic'],'equal')?>><?php _e('Is equal to',ImStore::domain)?></option>
									<option value="more"<?php selected($_POST['rules']['logic'],'more')?>><?php _e('Is greater than',ImStore::domain)?></option>
									<option value="less"<?php selected($_POST['rules']['logic'],'less')?>><?php _e('Is less than',ImStore::domain)?></option>
									</select>
								<input name="rules[value]" type="text" class="inpsm" value="<?php echo $_POST['rules']['value']?>"/>
							</td>
							<td colspan="3" align="right">
								<input type="submit" name="cancel" value="<?php _e('Cancel',$ImStore->domain)?>" class="button" />
								<?php if(isset($_GET['edit'])):?>
								<input type="hidden" name="promotion_id" value="<?php echo $_GET['edit']?>"/>
								<input type="submit" name="updatepromotion" value="<?php _e('Update',$ImStore->domain)?>" class="button-primary" />
								<?php else:?>
								<input type="submit" name="newpromotion" value="<?php _e('Add promotion',$ImStore->domain)?>" class="button-primary" />
								<?php endif;?>
							</td>
						</tr>
					</tbody>
				</table>
			</form>
		</div>
	</div>
<?php }?>
<form method="post" action="<?php echo $pagenowurl.'#promotions'?>" >
	<div class="tablenav">
		<div class="alignleft actions">
		<select name="action">
			<option value="" selected="selected"><?php _e('Bulk Actions',ImStore::domain)?></option>
			<option value="delete"><?php _e('Delete',ImStore::domain)?></option>
		</select>
		<input type="submit" value="<?php _e('Apply');?>" name="doaction" class="button-secondary" /> |
		<a href="<?php echo $pagenowurl ."&amp;$nonce&amp;newpromo=1#promotions"?>" class="button"><?php _e('New Promotion');?></a>
		</div>
	</div>
	<table class="widefat post fixed imstore-table">
		<thead><tr><?php print_column_headers('ims_gallery_page_ims-pricing')?></tr></thead>
		<tbody>
			<?php $counter = 0; foreach($promos as $promo){?>
			<tr id="item-<?php echo $id?>" class="iedit<?php if(!$counter%2){?> alternate<?php }?>">
			<?php foreach($columns as $key => $column){?> 
			<?php if($hidden) $class = (preg_match("/($hidden)/i",$key))?' hidden':'';?>
			<?php switch($key){
					case 'cb':?>
					<th scope="row" class="column-<?php echo $key.$class?> check-column">
					<input type="checkbox" name="promo[]" value="<?php echo $promo->ID?>" /> </th>
					<?php break;
					case 'name':?>
					<td class="column-<?php echo $key?>" > 
						<?php echo $promo->promo_name?>
						<div class="row-actions">
							<span><a href="<?php echo $pagenowurl ."&amp;$nonce&amp;edit=$promo->ID#promotions"?>"><?php _e("Edit",ImStore::domain)?></a></span> |
							<span class="delete"><a href="<?php echo $pagenowurl ."&amp;$nonce&amp;delete=$promo->ID#promotions"?>"><?php _e("Delete",ImStore::domain)?></a></span>
						</div>
					</td>
					<?php break;
					case 'code':?>
					<td class="column-<?php echo $key?>" > <?php echo $promo->promo_code?></td>
					<?php break;
					case 'starts':?>
					<td class="column-<?php echo $key?>" > <?php echo date_i18n($this->dformat,$promo->starts)?></td>
					<?php break;
					case 'expires':?>
					<td class="column-<?php echo $key?>" > <?php echo date_i18n($this->dformat,$promo->expires)?></td>
					<?php break;
					case 'type':?>
					<td class="column-<?php echo $key?>" > <?php echo $type[$promo->promo_type]?></td>
					<?php break;
					case 'discount':?>
					<td class="column-<?php echo $key?>" > <?php echo $promo->discount.$promo->items?></td>
					<?php break;
					default:?>
<td class="column-<?php echo $key?>" >&nbsp;</td>
				<?php }?>
			<?php }?>
			</tr>
			<?php }?>
		</tbody>
	</table>
	<?php wp_nonce_field('ims_promotions')?>
	</form>
</div>
<?php 

/**
*Get all packages
*
*@return array
*@since 0.5.0
*/
function get_ims_packages(){
	global $wpdb;
	return $wpdb->get_results("SELECT DISTINCT ID,post_title FROM $wpdb->posts WHERE post_type = 'ims_package'");
}

/**
*Get all price list
*
*@return array
*@since 0.5.0
*/
function get_ims_pricelists(){
	global $wpdb;
	return $wpdb->get_results("SELECT DISTINCT ID,post_title FROM $wpdb->posts WHERE post_type = 'ims_pricelist'");
}

/**
*Get promotions
*
*@return array
*@since 0.5.0
*/
function get_ims_promos(){
	global $wpdb;
	$r = $wpdb->get_results(
		"SELECT ID,post_title AS promo_name,UNIX_TIMESTAMP(post_expire) AS expires,
		UNIX_TIMESTAMP(post_date) AS starts FROM $wpdb->posts WHERE post_type = 'ims_promo' " 
	);
	if(empty($r)) return $r;
	foreach($r as $promo){
		foreach(get_post_meta($promo->ID,'_ims_promo_data',true) as $akey => $aval)
			$promo->{$akey} = $aval;
		$promos[] = $promo;
	}
	return $promos;
}

/**
*Create package
*
*@return array on error
*@since 0.5.0
*/
function create_ims_package(){
	global $wpdb,$pagenowurl;
	
	$errors = new WP_Error();
	if(empty($_POST['package_name'])){
		$errors->add('empty_name',__('A name is required.',ImStore::domain));
		return $errors;
	}
	$price_list = array(
			'post_status'	=> 'publish',
			'post_type' 	=> 'ims_package',
			'post_title' 	=> $_POST['package_name'],
	);
	$list_id = wp_insert_post($price_list);
	if(empty($list_id)){
		$errors->add('list_error',__('There was a problem creating the package.',ImStore::domain));
		return $errors;
	}
	wp_redirect($pagenowurl."&ms=35#packages");
}


/**
*Create new list
*
*@return array on error
*@since 0.5.0
*/
function create_ims_list(){
	global $wpdb,$pagenowurl;
	$errors = new WP_Error();
	if(empty($_POST['list_name'])){
		$errors->add('empty_name',__('A name is required.',ImStore::domain));
		return $errors;
	}
	$price_list = array(
			'post_status'	=> 'publish',
			'post_type' 	=> 'ims_pricelist',
			'post_title' 	=> $_POST['list_name'],
	);
	$list_id = wp_insert_post($price_list);
	
	if(empty($list_id)){
		$errors->add('list_error',__('There was a problem creating the list.',ImStore::domain));
		return $errors;
	}
	wp_redirect($pagenowurl."&ms=38");
}

/**
*Update list
*
*@return array on error
*@since 0.5.0
*/
function update_ims_list(){
	global $wpdb,$pagenowurl;
	$errors = new WP_Error();
	if(empty($_POST['list_name'])){
		$errors->add('empty_name',__('A name is required.',ImStore::domain));
		return $errors;
	}
	// price list
	$options = array(
		'ims_bw' 		=> $_POST['_ims_bw'],
		'ims_sepia' 	=> $_POST['_ims_sepia'],
		'ims_ship_local' => $_POST['_ims_ship_local'],
		'ims_ship_inter' => $_POST['_ims_ship_inter']
	);
	update_post_meta($_POST['listid'],'_ims_list_opts',$options);
	update_post_meta($_POST['listid'],'_ims_sizes',$_POST['sizes']);
	wp_update_post(array('ID' => $_POST['listid'],'post_title' => $_POST['list_name']));
	wp_redirect($pagenowurl."&ms=34");
}

/**
*Update package
*
*@return array on error
*@since 0.5.0
*/
function update_ims_package(){
	global $wpdb,$pagenowurl;
	$errors = new WP_Error();
	if(empty($_POST['packagename'])){
		$errors->add('empty_name',__('A name is required.',ImStore::domain));
		return $errors;
	}
	foreach($_POST['sizes'] as $size){
		$sizes[$size['name']]['unit'] = $size['unit'];
		$sizes[$size['name']]['count'] = $size['count'];
	}
	$id = intval($_POST['packageid']);
	update_post_meta($id,'_ims_sizes',$sizes);
	update_post_meta($id,'_ims_price',$_POST['packageprice']);
	wp_update_post(array('ID' => $id,'post_title' => $_POST['packagename']));
	wp_redirect($pagenowurl."&ms=33#packages");
}

/**
*Add/update promotions
*
*@return void
*@since 0.5.0
*/
function add_ims_promotion(){
	global $wpdb,$pagenowurl;
	
	$errors = new WP_Error();
	if(empty($_POST['promo_name']))
		$errors->add('empty_name',__('A promotion name is required.',ImStore::domain));
		
	if(empty($_POST['discount']) && $_POST['promo_type'] != 3)
		$errors->add('discount',__('A discount is required',ImStore::domain));	
		
	if(!empty($errors->errors)) return $errors;
		
	$promotion = array(
			'post_status'	=> 'publish',
			'post_type' 	=> 'ims_promo',
			'post_title' 	=> $_POST['promo_name'],
			'post_date'		=> $_POST['start_date'],	 
			'post_expire'	=> $_POST['expiration_date'],
	);
	
	if(isset($_POST['updatepromotion']))
		$promotion['ID'] = intval($_POST['promotion_id']);
	$promo_id = wp_update_post($promotion);
	if(empty($promo_id)){
		$errors->add('promo_error',__('There was a problem creating the promotion.',ImStore::domain));
		return $errors;
	}

	$data = array(
		'promo_code' => $_POST['promo_code'],
		'promo_type' => $_POST['promo_type'],
		'free-type'	 => $_POST['free-type'],
		'discount' 	 => intval($_POST['discount']),
		'items' 	 => $_POST['items'],
		'rules' 	 => $_POST['rules'],
	);
	update_post_meta($promo_id,'_ims_promo_data',$data);
	update_post_meta($promo_id,'_ims_promo_code',$_POST['promo_code']);
	$a = ($_POST['updatepromotion'])?30:32;
	wp_redirect($pagenowurl."&ms=$a#promotions");
}

/**
*delete promotions
*
*@return void
*@since 0.5.0
*/
function delete_ims_promotions(){
	global $wpdb,$pagenowurl;
	$errors = new WP_Error();
	if(empty($_POST['promo'])){
		$errors->add('nothing_checked',__('Please select a promo to be deleted.',ImStore::domain));
		return $errors;
	}
	$ids = $wpdb->escape(implode(',',$wpdb->escape($_POST['promo'])));
	$count = $wpdb->query("DELETE FROM $wpdb->posts WHERE ID IN($ids)");
	if(!empty($deleted))
		$wpdb->query("DELETE FROM $wpdb->postmeta WHERE post_id IN($ids)");
	$a = ($count< 2)?31:39;
	wp_redirect($pagenowurl."&ms=$a&c=$count#promotions");
}
?>