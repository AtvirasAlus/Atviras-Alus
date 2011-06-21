<?php 

if(!current_user_can('ims_read_sales')) 
	die();

//bulk actions
if(!empty($_GET['doaction'])){
	check_admin_referer('ims_orders');
	switch($_GET['action']){
		case 'delete':
			delete_ims_orders();
			break;
		default:
		ims_change_status();
	}
}

// empty trash
if(isset($_GET['deleteall'])){
	check_admin_referer('ims_orders');
	empty_orders_trash();
}

$sym 		= $this->opts['symbol']; 
$loc 		= $this->opts['clocal'];	
$orders 	= get_ims_orders($this->per_page);
$columns 	= (array)get_column_headers('ims_gallery_page_ims-sales');	
$is_trash	= (isset($_GET['status'])) &&($_GET['status'] == 'trash');
$hidden 	= implode('|',(array)get_hidden_columns('ims_gallery_page_ims-sales'));
$format 	= array('',"$this->sym%s","$this->sym %s","%s$this->sym","%s $this->sym"); 
?>

<ul class="subsubsub"><?php $count = ims_order_count_links()?></ul>
<form method="get" action="<?php echo $pagenowurl?>">
<div class="tablenav">
	<div class="alignleft actions">
		<select name="action">
			<option value=""><?php _e('Bulk Actions',ImStore::domain)?></option>
			<?php if($is_trash):?>
			<option value="pending"><?php _e('Restore',ImStore::domain)?></option> 
			<option value="delete"><?php _e('Delete Permanently',ImStore::domain)?></option>
			<?php else:?>
			<option value="pending"><?php _e('Pending',ImStore::domain)?></option>
			<option value="shipped"><?php _e('Order Shipped',ImStore::domain)?></option>
			<option value="closed"><?php _e('Closed Order',ImStore::domain)?></option>
			<option value="trash"><?php _e('Move to Trash',ImStore::domain)?></option>
			<?php endif?>
		</select>
		<input type="submit" value="<?php _e('Apply',ImStore::domain)?>" name="doaction" class="button-secondary action" />
		<select name='m'>
			<option value='0'><?php _e('Select date created',ImStore::domain)?></option>
			<?php foreach(ims_order_archive() as $archive):$date = strtotime($archive->y.'-'. $archive->m)?>
			<option value="<?php echo date('Ym',$date)?>" <?php selected(date('Ym',$date),$_GET['m'])?> >
			<?php echo date_i18n($this->dformat,$date)?></option>
			<?php endforeach?>
		</select>
		<input type="submit" value="<?php _e('Filter',ImStore::domain)?>" class="button" />
		<?php if($is_trash):?>
		<input type="submit" name="deleteall" value="<?php _e('Empty Trash',ImStore::domain)?>" class="button" /> |
		<?php endif?>
		<a href="<?php echo IMSTORE_ADMIN_URL?>sales-csv.php" class="button"><?php _e('Download CSV',ImStore::domain);?></a>
	</div>
	<p class="search-box">
		<input type="text" id="media-search-input" name="s" value="<?php echo esc_attr($_GET['s'])?>" />
		<input type="submit" value="<?php _e('Search Orders',ImStore::domain)?>" class="button" />
	</p>
</div>
<table class="widefat post fixed imstore-table">
	<thead><tr><?php print_column_headers('ims_gallery_page_ims-sales')?></tr></thead>
		<tbody>
			<?php 
			foreach($orders as $order):$id = $order->ID;
			$data = get_post_meta($id,'_response_data',true);
			?>
			<tr>
			<?php foreach($columns as $key => $column):?>
			<?php if($hidden) $class = (preg_match("/($hidden)/i",$key))?' hidden':'';?>
			<?php switch($key){
				case 'cb':?>
				<th scope="row" class="column-<?php echo "$key $class"?> check-column">
					<input type="checkbox" name="orders[]" value="<?php echo $id?>" />
				</th>
				<?php break;
				case 'ordernum':?>
				<td class="column-<?php echo "$key $class"?>">
				<?php 
					if(!$is_trash) echo '<a href="'.$pagenowurl."&amp;details=1&amp;id=$id".'">'. $data['txn_id'].'</a>';
					else echo $data['txn_id'];
				?>
				</td>
				<?php break;
				case 'orderdate':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo date_i18n($this->dformat,strtotime($order->post_date))?></td>
				<?php break;
				case 'amount':?>
					<td class="column-<?php echo "$key $class"?>" ><?php printf($format[$loc],$data['payment_gross'])?></td>
				<?php break;
				case 'customer':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $data['last_name'].' '.$data['first_name']?></td>
				<?php break;
				case 'images':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $data['num_cart_items']?></td>
				<?php break;
				case 'paystatus':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $data['payment_status']?></td>
				<?php break;
				case 'orderstat':?>
					<td class="column-<?php echo "$key $class"?>" ><?php echo $order->post_status?></td>
				<?php break;
				default:?>
				<td class="column-<?php echo "$key $class"?>" >&nbsp;</td>
			<?php }?>
			<?php endforeach?>
			</tr>
		<?php $alternate = ($alternate==' alternate')?'':' alternate';?>
		<?php endforeach?>
		</tbody>
	<tfoot><tr><?php print_column_headers('ims_gallery_page_ims-sales')?></tr></tfoot>
</table>
<div class="tablenav"><?php $this->imstore_paging($this->per_page,$count)?></div>
<input type="hidden" name="page" value="<?php echo $_GET['page']?>" />
<input type="hidden" name="post_type" value="<?php echo $_GET['post_type']?>" />
<?php wp_nonce_field('ims_orders')?>
</form>

<?php 
/**
*Get all orders
*
*@param unit $perpage 
*@since 0.5.0
*return array
*/
function get_ims_orders($perpage){
	global $wpdb; 

	$srch 	= $wpdb->escape($_GET['s']);	
	$month 	= (int)substr($_GET['m'],4);
	$year 	= (int)substr($_GET['m'],0,4);
	$limit	= ($_GET['p']) ?(((int)$_GET['p'] - 1)*$perpage):0;
	$page	= (empty($_GET['p'])) ?'1':$wpdb->escape((int)$_GET['p']);
	$status = (empty($_GET['status']))?" != 'trash' " :" = '{$_GET['status']}' ";
	$join	= ($srch)?" JOIN $wpdb->postmeta AS pm ON(p.ID = pm.post_id) ":'';
	$datef 	= (!empty($_GET['m']))?" AND YEAR(post_date) = '$year' AND MONTH(post_date) = '$month'":'';
	$search = ($srch)?" AND(post_title LIKE '%$srch%' OR post_excerpt LIKE '%$srch%' OR pm.meta_value LIKE '%$srch%') ":'';
	
	$r = $wpdb->get_results(
		"SELECT ID,post_title,
		post_status,post_date
		FROM $wpdb->posts AS p $join
		WHERE post_type = 'ims_order' 
		AND post_status $status
		AND post_status != 'draft'
		$datef $search GROUP BY ID
		ORDER BY post_date DESC LIMIT $limit,$perpage"
	);
	
	if(empty($r)) return $r;
	foreach($r as $post){
		$custom_fields = get_post_custom($post->ID);
		foreach($custom_fields as $key => $value)
			$post->$key = $value[0];
		$orders[] = $post;
	}
	return $orders;
}

/**
 *Return order count by status
 *
 *@since 0.5.0
 *return unit
*/
function ims_order_count_links(){
	global $wpdb,$pagenowurl; 
	
	$r = $wpdb->get_results(
		"SELECT post_status AS status,count(post_status) AS count 
		FROM $wpdb->posts WHERE post_type = 'ims_order'
		AND post_status != 'draft'
		GROUP by post_status"
	);
	if(empty($r)) return $r;
	$labels = array(
		'trash' 	=> __('Trash',ImStore::domain),
		'closed' 	=> __('Closed',ImStore::domain),
		'pending' 	=> __('Pending',ImStore::domain),
		'shipped' 	=> __('Shipped',ImStore::domain),
		'publish' 	=> __('Published',ImStore::domain),
	);
	
	foreach($r as $obj){
		$count 	 = ($obj->status == $_GET['status'])?$obj->count:0;
		$current = ($obj->status == $_GET['status'])?' class="current"':'';
		$links[] = '<li><a href="'.$pagenowurl.'&amp;status='.$obj->status.'"'.$current.'>'.$labels[$obj->status].' <span class="count">('.$obj->count.')</span></a></li>';
		if($obj->status != 'trash') $all += $obj->count;
	}
	
	$style = (empty($_GET['status']))?' class="current"':'';
	if($all){
		array_unshift($links,'<li><a href="'.$pagenowurl.'"'.$style.'>'.__('All',ImStore::domain).' <span class="count">('.$all.')</span></a></li>');
		$count = $all; 
	} echo implode(' | ',$links);
	
	if($s = $_GET['s']){
		$search	= $wpdb->escape($s);
		$status = (empty($_GET['status']))?' != "trash" ':' = "'.$wpdb->escape($_GET['status']).'" ';
		$count = $wpdb->get_var(
			"SELECT COUNT(ID)
			FROM $wpdb->posts AS p $join
			WHERE post_type = 'ims_order' 
			AND post_status $status
			AND post_status != 'draft'
			GROUP BY ID "
		);
	}
	return $count;
}

/**
 *Get order archive
 *
 *@return array
 *@since 0.5.0
*/
function ims_order_archive(){
	global $wpdb;
	$status = (empty($_GET['status']))?" != 'trash' ":" = '{$_GET['status']}' ";
	$r = $wpdb->get_results($wpdb->prepare("
		SELECT distinct YEAR(post_date) AS y,MONTH(post_date) AS m
		FROM $wpdb->posts WHERE post_status $status AND post_status != 'draft' 
		AND post_type = 'ims_order' AND post_date != 0 "
	));
	return $r;
}

/**
 *Empty trash
 *
 *@param bool $delete_files
 *@return void
 *@since 0.5.0
*/
function empty_orders_trash(){
	global $wpdb,$pagenowurl;
	$wpdb->query(
		"DELETE p,pm FROM $wpdb->posts p 
		LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id) 
		WHERE post_type = 'ims_order'
		AND post_status = 'trash'"
	);
	wp_redirect($pagenowurl."&ms=20");
}


/**
 *change status
 *
 *@return void
 *@since 0.5.0
*/
function ims_change_status(){
	global $wpdb,$pagenowurl;
	if(empty($_GET['orders'])) return;
	$wpdb->query(
		"UPDATE $wpdb->posts 
		SET post_status = '".$wpdb->escape($_GET['action'])."' 
		WHERE ID IN(".$wpdb->escape(implode(',',$_GET['orders'])).")"
	);
	$count = count($_GET['orders']);
	$s = ($_GET['action'] == 'trash')?1:2;
	
	if($count < 2 && $s == 2) $a = 22;
	elseif($count < 2 && $s == 1) $a = 23;
	elseif($s == 1) $a = 26;
	else $a = 26;
	wp_redirect($pagenowurl."&ms=$a&c=$count");
}

/**
 *Delete orders
 *
 *@param bool $delete_files
 *@return void
 *@since 0.5.0
*/
function delete_ims_orders(){
	global $wpdb,$pagenowurl;
	if(empty($_GET['orders'])) return;
	$orderids = $wpdb->escape(implode(',',$_GET['orders']));
	$wpdb->query(
		"DELETE p,pm FROM $wpdb->posts p 
		LEFT JOIN $wpdb->postmeta pm ON(p.ID = pm.post_id) 
		WHERE ID IN($orderids)
		AND post_type = 'ims_order'"
	);
	$count = count($_GET['orders']);
	$a = ($count < 2)?31:39;
	wp_redirect($pagenowurl."&ms=$a&c=$count");
}

?>