<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 0.5.0
*/

if(!current_user_can('ims_manage_customers')) 
	die();

//clear cancel post data
if(isset($_POST['cancel']))
	wp_redirect($pagenowurl);	

//add/update customer
if(isset($_POST['add_customer']) || isset($_POST['update_customer'])){
	check_admin_referer('ims_new_customer');
	$errors = create_ims_customer();
}

//update screen options
if(!empty($_POST['screen_options'])){
	update_user_meta($user_ID,$_POST['screen_options']['option'],$_POST['screen_options']['value']);
	wp_redirect($pagenowurl);	
};

//view customer information
if(!empty($_GET['edit'])){
	check_admin_referer('ims_link_customer');
	$_GET['newcustomer'] = 1;
	$_POST = get_object_vars(get_userdata($_GET['edit']));
}

//update user statuts
if(!empty($_GET['inactive'])){
	check_admin_referer('ims_link_customer');
	update_user_meta($_GET['inactive'],'ims_status','inactive');
	wp_redirect($pagenowurl.'&ms=12');	
}

//delete single user
if(!empty($_GET['user_delete'])){
	check_admin_referer('ims_link_customer');
	wp_delete_user((int) $_GET['user_delete']);
	wp_redirect($pagenowurl.'&ms=13');	
}

//bulk actions
if(!empty($_GET['doaction'])){
	if(empty($_GET['customer'])) wp_redirect($pagenowurl);
	check_admin_referer('ims_customers');
	switch($_GET['action']){
		case 'delete':
			delete_ims_customers();
			break;
		default:
			update_ims_customer_status();
	}
}

$customers 	= get_ims_customers($this->per_page);
$nonce 		= '_wpnonce='.wp_create_nonce('ims_link_customer');
$columns 	= get_column_headers('ims_gallery_page_ims-customers');
$hidden 	= implode('|',get_hidden_columns('ims_gallery_page_ims-customers'));

if(isset($errors) && is_wp_error($errors)){
	echo '<div class="error">'; 
	foreach($errors->get_error_messages() as $err)
		echo "<p><strong>$err</strong></p>\n";
	echo '</div>';
} 
?>

<?php 
if(!empty($_GET['newcustomer']) || !empty($_GET['edit'])):
$link = ($_GET['newcustomer']) ?'&newcustomer=1':'&edit='.$_GET['edit'];
?>

<form method="POST" action="<?php echo "$pagenowurl{$link}"?>" >
	<div class="postbox" >
		<div class="handlediv"><br /></div>
		<h3 class='hndle'><span><?php if($_GET['edit']) _e('Edit Customer',ImStore::domain); else _e('New Customer',ImStore::domain);?></span></h3>
		<div class="inside">
			<table class="ims-table">
				<tr><td width="33" colspan="4" scope="row">&nbsp;</td></tr>
				<tr>
					<td scope="row"><label for="first_name"><?php _e('First Name',ImStore::domain)?></label></td>
					<td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($_POST['first_name'])?>" class="inputxl" /></td>
					<td><label for="last_name"><?php _e('Last Name',ImStore::domain)?></label></td>
					<td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($_POST['last_name'])?>" class="inputxl"/></td>
				</tr>
				<tr class="alternate">
					<td scope="row"><label for="ims_address"><?php _e('Address',ImStore::domain)?></label></td>
					<td><input type="text" name="ims_address" id="ims_address" value="<?php echo esc_attr($_POST['ims_address'])?>" class="inputxl" /></td>
					<td><label for="ims_city"><?php _e('City',ImStore::domain)?></label></td>
					<td><input type="text" name="ims_city" id="ims_city" value="<?php echo esc_attr($_POST['ims_city'])?>" class="inputxl" /></td>
				</tr>
				<tr>
					<td scope="row"><label for="ims_state"><?php _e('State',ImStore::domain)?></label></td>
					<td><input type="text" name="ims_state" id="ims_state" value="<?php echo esc_attr($_POST['ims_state'])?>" class="input" />
						<label for="ims_zip"><?php _e('Zip',ImStore::domain)?></label>
						<input type="text" name="ims_zip" id="ims_zip" value="<?php echo esc_attr($_POST['ims_zip'])?>" class="inputsm" /></td>
					<td><label for="ims_phone"><?php _e('Phone',ImStore::domain)?></label></td>
					<td><input type="text" name="ims_phone" id="ims_phone" value="<?php echo esc_attr($_POST['ims_phone'])?>" class="inputxl" /></td>
				</tr>
				<tr class="alternate">
					<td scope="row"><label for="user_email"><?php _e('Email',ImStore::domain)?></label></td>
					<td><input type="text" name="user_email" id="user_email" value="<?php echo esc_attr($_POST['user_email'])?>" class="inputxl" /></td>
					<td><?php if(class_exists('MailPress')&&$_GET['x']):?>
						<label for="_MailPress_sync_wordpress_user"><?php _e('Added to eNewsletter',ImStore::domain)?></label>
						<?php endif?>
					</td>
					<td><?php if(class_exists('MailPress')&&$_GET['x']):?>
						<input type="checkbox" name="_MailPress_sync_wordpress_user" id="_MailPress_sync_wordpress_user" disabled="disabled" value="1" <?php checked($_GET['edit'],$_POST['_MailPress_sync_wordpress_user'])?> />
						<?php endif?>
					</td>
				</tr>
				<tr>
					<td colspan="4" align="right"><input type="submit" name="cancel" value="<?php _e('Cancel',ImStore::domain)?>" class="button" />
						<?php if(empty($_GET['edit'])):?>
						<input type="submit" name="add_customer" value="<?php _e('Add New Customer',ImStore::domain)?>" class="button-primary" />
						<?php else:?>
						<input type="hidden" name="ims_status" id="ims_status" value="<?php echo esc_attr($_POST['ims_status'])?>" />
						<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($_POST['ID'])?>" />
						<input type="submit" name="update_customer" value="<?php _e('Update',ImStore::domain)?>" class="button-primary" />
						<?php endif;?></td>
				</tr>
			</table>
		</div>
	</div>
	<?php wp_nonce_field('ims_new_customer')?>
</form>
<?php endif;?>


<!-- Customer actions -->
<ul class="subsubsub"><?php $count = ims_customers_count_links(); if($_GET['s'])?></ul>
<form method="get" action="<?php echo $pagenowurl?>">
	<div class="tablenav">
		<div class="alignleft actions">
		<select name="action">
			<option selected="selected"><?php _e('Bulk Actions',ImStore::domain)?></option>
			<?php if($_GET['status'] == 'inactive'){?>
			<option value="active"><?php _e('Active',ImStore::domain)?></option>
			<option value="delete"><?php _e('Delete',ImStore::domain)?></option>
			<?php }else{?>
			<option value="inactive"><?php _e('Inactive',ImStore::domain)?></option>
			<?php }?>
		</select>
		<input type="submit" value="<?php _e('Apply');?>" name="doaction" class="button-secondary" />
		| <a href="<?php echo IMSTORE_ADMIN_URL?>customer-csv.php" class="button"><?php _e('Download CSV',ImStore::domain);?></a> 
		<a href="<?php echo $pagenowurl."&amp;$nonce&amp;newcustomer=1"?>" class="button"><?php _e('New Customer',ImStore::domain);?></a>
		</div>
	</div>
	<table class="widefat post fixed imstore-table">
		<thead><tr><?php print_column_headers('ims_gallery_page_ims-customers')?></tr></thead>
		<tbody>
		 <?php foreach($customers as $id):$customer = get_userdata($id);?>
		 <tr id="item-<?php echo $id?>" class="iedit<?php echo $alternate?>">
		 	<?php 
			foreach($columns as $key => $column):
				if($hidden) $class = (preg_match("/($hidden)/i",$key))?' hidden':'';
		 		switch($key){ 
					case 'cb':?>
				<th scope="row" class="column-<?php echo "$key $class"?> check-column">
					<input type="checkbox" name="customer[]" value="<?php echo $id?>" />
				</th>
				<?php break;
				case 'name':?>
				<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->first_name?>
					<div class="row-actions">
						<a href="<?php echo "$pagenowurl&amp;$nonce&amp;edit=$id"?>" title="<?php _e("Edit information",ImStore::domain)?>"><?php _e("Edit",ImStore::domain)?></a> |
						<?php if($_GET['status'] == 'inactive'):?>
						<a href="<?php echo "$pagenowurl&amp;$nonce&amp;active=$id"?>" title="<?php _e("Make entry active",ImStore::domain)?>"><?php _e("Active",ImStore::domain)?></a> | 
						<span class="delete"><a href="<?php echo $pagenowurl."&amp;$nonce&amp;user_delete=$id"?>" title="<?php _e("Delete entry permanently",ImStore::domain)?>"><?php _e("Delete",ImStore::domain)?></a></span>
						<?php else:?>
						<a href="<?php echo "$pagenowurl&amp;$nonce&amp;inactive=$id"?>" title="<?php _e("Make entry inactive",ImStore::domain)?>"><?php _e("Inactive",ImStore::domain)?></a>
						<?php endif;?>
					</div>
				</td>
				<?php break;
				case 'lastname':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->last_name?></td>
				<?php break;
				case 'email':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->user_email?></td>
				<?php break;
				case 'phone':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->ims_phone?></td>
				<?php break;
				case 'city':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->ims_city?></td>
				<?php break;
				case 'state':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $customer->ims_state?></td>
				<?php break;
				case 'newsletter':?>
					<?php if(class_exists('MailPress')):?>
					<td class="column-<?php echo "$key $class"?>" >
					<?php echo($customer->_MailPress_sync_wordpress_user)?__("Yes",ImStore::domain):__("No",ImStore::domain);?>
					</td><?php endif;?>
					<?php break;
				default:?>
				<td class="column-<?php echo "$key $class"?>">&nbsp;</td>	
			<?php }
			endforeach?>
		 </tr>
		 <?php $alternate = ($alternate==' alternate')?'':' alternate';?>
		 <?php endforeach?>
		</tbody>
		<tfoot><tr><?php print_column_headers('ims_gallery_page_ims-customers')?></tr></tfoot>
	</table>
	<div class="tablenav"><?php $this->imstore_paging($this->per_page,$count)?></div>
	<?php wp_nonce_field('ims_customers')?>
	<input type="hidden" name="page" value="<?php echo $_GET['page']?>" />
	<input type="hidden" name="post_type" value="<?php echo $_GET['post_type']?>" />
</form>


<?php 
/**
*Get all customers
*
*@param unit $perpage 
*@since 0.5.0
*return array
*/
function get_ims_customers($perpage){
	global $wpdb; 
	
	$srch 	= ($_GET['s'])?$_GET['s']:'';	
	$limit	= ($_GET['p'])?(((int)$_GET['p'] - 1)*$perpage):0;
	$page	= (empty($_GET['pagenum']))?'1':(int)$_GET['pagenum'];
	$status = (empty($_GET['status']))?'active':$_GET['status'];
	$search	= ($srch)?" AND(user_login LIKE '%$srch%' OR user_email LIKE '%$srch%' OR um.meta_value LIKE '%$srch%') ":'';

	$users = $wpdb->get_results(
		"SELECT ID FROM $wpdb->users AS u 
		INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id 
		WHERE um.meta_key = 'ims_status' 
		AND um.meta_value IN('$status',
			(SELECT DISTINCT meta_value 
			 FROM $wpdb->usermeta 
			 WHERE meta_value LIKE '%%customer%%'
			 AND meta_key = 'ims_capabilities')
		) $search LIMIT $limit,$perpage"
	);
	foreach($users as $user) $users_ids[] = $user->ID;
	return(array)$users_ids;
}

/**
*Display/Return customer count by status
*
*@since 0.5.0
*return unit
*/
function ims_customers_count_links(){
	global $wpdb,$pagenowurl; 
	
	$r = $wpdb->get_results(
		"SELECT meta_value AS status,count(meta_key) AS count 
		FROM $wpdb->usermeta
		WHERE meta_key = 'ims_status' 
		GROUP by meta_value"
	);
	
	if(empty($r)) return;
	$labels = array('active' => __('Active',ImStore::domain),'inactive' => __('Inactive',ImStore::domain));
	
	foreach($r as $obj){
		$count 	 = (($obj->status == $_GET['status']) ||($obj->status == 'active' && empty($_GET['status']))) ?$obj->count:0;
		$current = (($obj->status == $_GET['status']) ||($obj->status == 'active' && empty($_GET['status']))) ?' class="current"':'';
		$links[] = '<li><a href="'.$pagenowurl.'&amp;status='.$obj->status.'"'.$current.'>'.$labels[$obj->status].' <span class="count">('.$obj->count.')</span></a></li>';
	}
	echo implode(' | ',$links);
	
	if($s = $_GET['s']){
		$search	= $wpdb->escape($s);	
		$count = $wpdb->get_var(
			"SELECT count(ID)FROM $wpdb->users AS u
			INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id 
			WHERE um.meta_key = 'ims_status' 
			AND um.meta_value IN('active',
				(SELECT DISTINCT meta_value 
				 FROM $wpdb->usermeta 
				 WHERE meta_value LIKE '%customer%'
				 AND meta_key = 'ims_capabilities')) 
			AND(user_login LIKE '%$search%' OR user_email LIKE '%$search%' OR um.meta_value LIKE '%$search%')"
		);
	}
	return $count;
}

/**
*Insert a customer
*
*@since 0.5.0
*return array errors
*/
function create_ims_customer(){
	global $wpdb,$pagenowurl;
	
	$errors = new WP_Error();
	
	if(empty($_POST['first_name']))
		$errors->add('empty_first_name',__('The first name is required.',ImStore::domain));
	
	if(empty($_POST['last_name']))
		$errors->add('empty_last_name',__('The last name is required.',ImStore::domain));
	
	if(empty($_POST['user_email']))
		$errors->add('empty_last_name',__('The email is required.',ImStore::domain));
		
	if(!is_email($_POST['user_email']))
		$errors->add('empty_last_name',__('Wrong email format. That doesn&#8217;t look like an email to me.',ImStore::domain));
		
	$user_name = sanitize_user($_POST['first_name'].' '. $_POST['last_name']);
	if(username_exists($user_name) && !isset($_POST['update_customer'])) 
		$errors->add('customer_exists',__('That customer already exists.',ImStore::domain));
		
	if(!empty($errors->errors))
		return $errors;
		
	$new_user = array(
		'ID' 			=> $_POST['user_id'],
		'user_pass' 	=> wp_generate_password(12,false),
		'user_login' 	=> $user_name,
		'user_nicename' => $user_name,
		'user_email' 	=> $_POST['user_email'],
		'first_name' 	=> $_POST['first_name'],
		'last_name' 	=> $_POST['last_name'],
		'role' 			=> 'customer'
	);

	if(isset($_POST['update_customer'])) $user_id = wp_update_user($new_user);
	else $user_id = wp_insert_user($new_user);
		
	if(is_wp_error($user_id) && !isset($_POST['update_customer']))
		return $user_id;

	$meta_keys = array('ims_zip','ims_city','ims_phone','ims_state','ims_address','_MailPress_sync_wordpress_user');
	foreach($meta_keys as $key){
		if(!empty($_POST[$key])) update_user_meta($user_id,$key,$_POST[$key]);
	}
	
	$status = ($_POST['ims_status']) ?$_POST['ims_status']:'active';
	update_user_meta($user_id,'ims_status',$status);
	
	if(isset($_POST['update_customer'])) wp_redirect($pagenowurl.'&ms=2');	
	else wp_redirect($pagenowurl.'&ms=10');	
}

/**
*Update user status
*
*@since 0.5.0
*return void
*/
function update_ims_customer_status(){
	global $wpdb,$pagenowurl;
	$updated = $wpdb->query($wpdb->prepare(
		"UPDATE $wpdb->usermeta SET meta_value = '%s' 
		WHERE meta_key = 'ims_status' AND user_id IN(".implode(',',$_GET['customer']) .")"
		,$_GET['action']));
	if($updated) wp_redirect($pagenowurl."&ms=14&c=$updated");	
}


/**
*delete users
*
*@since 0.5.0
*return void
*/
function delete_ims_customers(){
	global $wpdb,$pagenowurl;
	$customer_ids = implode(',',$_GET['customer']);	
	$deleted = $wpdb->query($wpdb->prepare("DELETE FROM $wpdb->users WHERE ID IN(%s) ",$customer_ids));
	if($deleted){
		$wpdb->query($wpdb->prepare("DELETE FROM $wpdb->usermeta WHERE user_id IN(%s) ",$customer_ids));
		wp_redirect($pagenowurl."&ms=15&c=$deleted");	
	}
}
?>