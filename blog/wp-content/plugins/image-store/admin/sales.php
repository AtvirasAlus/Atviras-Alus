<?php

if (!current_user_can('ims_read_sales'))
	die();

$integrety = '';
$css = ' alternate';
$cdate = isset($_GET['m']) ? $_GET['m'] : 0;
$page = empty($_GET['p']) ? 1 : (int) $_GET['p'];
$status = isset($_GET['status']) ? $_GET['status'] : false;
$osearch = isset($_GET['osearch']) ? $_GET['osearch'] : NULL;
$columns = get_column_headers('ims_gallery_page_ims-sales');
$hidden = get_hidden_columns('ims_gallery_page_ims-sales');
$is_trash = isset($_GET['status']) && ( $_GET['status'] == 'trash');

if (isset($_GET['doaction'])) {
	check_admin_referer('ims_orders');
	if ($_GET['order-action'] == 'delete')
		delete_ims_orders();
	else
		ims_change_status();
}

$order_status = array(
	'trash' => __('Trash', 'ims'),
	'closed' => __('Closed', 'ims'),
	'pending' => __('Pending', 'ims'),
	'shipped' => __('Shipped', 'ims'),
	'publish' => __('Published', 'ims'),
	'cancelled' => __('Cancelled', 'ims'),
	'delete' => __('Delete Permanently', 'ims'),
);

$payment_status = array(
	'void' => __('Void', 'ims'),
	'failed' => __('Failed', 'ims'),
	'expired' => __('Expired', 'ims'),
	'denied' => __('Denied', 'ims'),
	'pending' => __('Pending', 'ims'),
	'denided' => __('Denied', 'ims'),
	'refunded' => __('Refunded', 'ims'),
	'reviewing' => __('Reviewing', 'ims'),
	'processed' => __('Processed', 'ims'),
	'completed' => __('Completed', 'ims'),
	'in_progress' => __('In Progress', 'ims'),
);

$args = array(
	'paged' => $page,
	'post_status' => $status,
	'post_type' => 'ims_order',
	'posts_per_page' => $this->per_page,
);

add_filter('posts_where', 'ims_filter_order_status');
$args = apply_filters('ims_pre_get_sales', $args);

$sales = new WP_Query($args);

$start = ($page - 1) * $this->per_page;
$page_links = paginate_links(array(
	'base' => $this->pageurl . '%_%#ims_images_box',
	'format' => '&p=%#%',
	'prev_text' => __('&laquo;'),
	'next_text' => __('&raquo;'),
	'total' => $sales->max_num_pages,
	'current' => $page,
));

$order_status = apply_filters('ims_order_status', $order_status, $status);
$payment_status = apply_filters('ims_payment_status', $payment_status, $status);
?>

<div class="filter">
	<form id="list-filter" action="" method="get">
		<ul class="subsubsub">
			<?php $this->count_links($order_status, array('type' => 'order')) ?>
		</ul>
	</form>
</div><!--.filter-->

<form class="search-form" action="<?php echo admin_url('edit.php?') ?>" method="get">
	<p class="search-box">
		<label class="screen-reader-text" for="user-search-input"><?php _e('Search Users', 'ims'); ?>:</label>
		<input type="text" name="osearch" id="order-search" value="<?php echo esc_attr($osearch) ?>" />
		<input type="submit" value="<?php esc_attr_e('Search Orders', 'ims'); ?>" class="button" />
		<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']) ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr($_GET['post_type']) ?>" />
	</p>
</form><!--.search-form-->


<form id="posts-filter" action="<?php echo $this->pageurl ?>" method="get">
	<div class="tablenav">

		<div class="alignleft actions">
			<select name="order-action">
				<option value=""><?php esc_attr_e('Order Status', 'ims') ?></option>
				<?php
				foreach ($order_status as $key => $label) {
					if ($is_trash && $key == 'trash')
						continue;
					if (!$is_trash && $key == 'delete')
						continue;
					echo '<option value="', esc_attr($key), '">', $label, '</option>';
				}
				?>
			</select>

				<?php if (!$is_trash) { ?>
				<select name="payment-action">
					<option value=""><?php esc_attr_e('Payment Status', 'ims') ?></option>
				<?php
				foreach ($payment_status as $key => $label) {
					echo '<option value="', esc_attr($key), '">', $label, '</option>';
				}
				?>
				</select>
				<?php } ?>

			<input type="submit" name="doaction" value="<?php esc_attr_e('Apply', 'ims') ?>" class="button-secondary action" />
			<select name='m'>
				<option value='0'><?php esc_attr_e('Select order date', 'ims') ?></option>
				<?php
				foreach (ims_order_archive($status) as $archive) {
					$date = strtotime($archive->y . $archive->m);
					$val = date('Ym', $date);
					echo '<option value="', esc_attr($val), '"', selected($val, $cdate, false), '>', date_i18n('F Y', $date), '</option>';
				}
				?>
			</select>
			<input type="submit" value="<?php _e('Filter', 'ims') ?>" class="button" />

			<a href="<?php echo IMSTORE_ADMIN_URL ?>/sales-csv.php" class="button"><?php _e('Download CSV', 'ims') ?></a>
		</div><!--.actions-->

		<br class="clear" />
	</div><!--.tablenav-->


	<table class="widefat post fixed imstore-table">
		<thead>
			<tr class="thead">
			<?php print_column_headers('ims_gallery_page_ims-sales') ?>
			</tr>
		</thead>
		<tbody id="sales" class="list:sales sales-list">
			<?php
			foreach ($sales->posts as $sale) {

				$css = ( ' alternate' == $css ) ? '' : ' alternate';
				$data = get_post_meta($sale->ID, '_response_data', true);
				$cart = get_post_meta($sale->ID, '_ims_order_data', true);

				$payment = ( isset($data['payment_status'])) ? trim(strtolower($data['payment_status'])) : 'pending';
				$integrety = ( empty($data['data_integrity']) && $sale->post_status == 'pending' ) ? '  not-verified' : '';

				$r = "<tr id='order-$sale->ID' class='order-edit{$css}{$integrety}'>";
				foreach ($columns as $column_id => $column_name) {

					$hide = ( $this->in_array($column_id, $hidden) ) ? ' hidden' : '';
					switch ($column_id) {
						case 'cb':
							$r .= "<th scope='row' class='check-column'><input type='checkbox' name='orders[]' value='" . esc_attr($sale->ID) . "' /></th>";
							break;
						case 'ordernum':
							$r .= "<td class='column-{$column_id}{$hide}'>" .
									( ( $is_trash ) ? $data['txn_id'] : '<a href="' . $this->pageurl . "&amp;details=1&amp;id={$sale->ID}" . '">' . $data['txn_id'] . '</a>' ) . "</td>";
							break;
						case 'orderdate':
							$r .= "<td class='column-{$column_id}{$hide}'>" . date_i18n($this->dformat, strtotime($sale->post_date)) . "</td>";
							break;
						case 'amount':
							$r .= "<td class='column-{$column_id}{$hide}'>" . (isset($data['payment_gross']) ? $this->format_price($data['payment_gross']) : '' ) . "</td>";
							break;
						case 'customer':
							$r .= "<td class='column-{$column_id}{$hide}'>" . (isset($data['last_name']) ? $data['last_name'] : '' ) . ' ' . (isset($data['first_name']) ? $data['first_name'] : '' ) . "</td>";
							break;
						case 'images':
							$r .= "<td class='column-{$column_id}{$hide}'>" . (isset($cart['items']) ? $cart['items'] : '' ) . "</td>";
							break;
						case 'paystatus':
							$r .= "<td class='column-{$column_id}{$hide}'>" . ( empty($payment_status[$payment]) ? '' : $payment_status[$payment] ) . "</td>";
							break;
						case 'orderstat':
							$r .= "<td class='column-{$column_id}{$hide}'>" . ( isset($sale->post_status) ? $order_status[$sale->post_status] : '' ) . "</td>";
							break;
					}
				}
				echo $r .= "</tr>";
			}
			?>
		</tbody>
	</table>

	<div class="tablenav">
	<?php if ($page_links) : ?>
		<div class="tablenav-pages"><?php
		$page_links_text = sprintf('<span class="displaying-num">' . __('Displaying %s&#8211;%s of %s') . '</span>%s', number_format_i18n($start + 1), number_format_i18n(min($page * $this->per_page, $sales->found_posts)), '<span class="total-type-count">' . number_format_i18n($sales->found_posts) . '</span>', $page_links
		);
		echo $page_links_text;
		?></div>
<?php endif ?>
	</div>
<?php wp_nonce_field('ims_orders') ?>
	<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']) ?>" />
	<input type="hidden" name="post_type" value="<?php echo esc_attr($_GET['post_type']) ?>" />
</form>


<?php

/**
 * Get order archive
 *
 * @param string $current_status 
 * @return array
 * @since 0.5.0
 */
function ims_order_archive($current_status) {
	$r = wp_cache_get('ims_order_archive');
	echo $status = empty($current_status) ? " != 'trash' " : " = '$current_status' ";

	if (false == $r) {
		global $wpdb;

		$r = $wpdb->get_results($wpdb->prepare("
			SELECT distinct YEAR( post_date ) AS y, MONTH ( post_date ) AS m
			FROM $wpdb->posts WHERE post_status $status  AND post_status != 'draft' 
			AND post_type = 'ims_order' AND post_date != 0 "
		));
		wp_cache_set('ims_order_archive', $r);
	}
	return $r;
}

/**
 * Filter post status
 *
 * @return string
 * @since 3.0.0
 */
function ims_filter_order_status($where) {
	global $wpdb;

	$where = str_ireplace("status = 'draft'", "status != 'draft'", $where);

	if (!(isset($_GET['status']) && $_GET['status'] == 'trash'))
		$where .= " AND $wpdb->posts.post_status != 'trash' ";

	if (isset($_GET['osearch'])) {
		$srch = $_GET['osearch'];
		$where .= " AND (  $wpdb->posts.post_title LIKE '%$srch%'
		OR  $wpdb->posts.post_excerpt LIKE '%$srch%' 
		OR $wpdb->postmeta.meta_value LIKE '%$srch%' ) ";
	}

	if (isset($_GET['m'])) {
		$month = (int) substr($_GET['m'], 4);
		$year = (int) substr($_GET['m'], 0, 4);
		$where .= " AND YEAR ( $wpdb->posts.post_date ) = '$year' 
		AND MONTH ( $wpdb->posts.post_date ) = '$month' ";
	}

	return $where;
}

/**
 * change status
 *
 * @return void
 * @since 0.5.0
 */
function ims_change_status() {

	if (empty($_GET['orders']))
		return;

	if (!empty($_GET['payment-action'])) {
		foreach ($_GET['orders'] as $id) {
			$data = get_post_meta($id, '_response_data', true);
			$data['payment_status'] = $_GET['payment-action'];
			update_post_meta($id, '_response_data', $data);
		}
	}

	$s = false;
	global $wpdb, $ImStore;
	if (!empty($_GET['order-action'])) {
		$wpdb->query($wpdb->prepare(
						"UPDATE $wpdb->posts SET post_status = %s 
			WHERE ID IN( " . $wpdb->escape(implode(',', $_GET['orders'])) . ")"
						, $_GET['order-action']));
		$s = ( $_GET['order-action'] == 'trash' ) ? true : false;
	}

	$count = count($_GET['orders']);

	if ($count > 1 && !$s)
		$a = 25;
	elseif ($count > 1 && $s)
		$a = 26;
	elseif ($s)
		$a = 22;
	else
		$a = 23;

	wp_redirect($ImStore->pageurl . "&ms=$a&c=$count");
	die();
}

/**
 * Delete orders
 *
 * @param bool $delete_files
 * @return void
 * @since 0.5.0
 */
function delete_ims_orders() {

	if (empty($_GET['orders']))
		return;

	global $wpdb, $ImStore;

	$wpdb->query(
			"DELETE p, pm FROM $wpdb->posts p 
		LEFT JOIN $wpdb->postmeta pm ON( p.ID = pm.post_id ) 
		WHERE ID IN( " . $wpdb->escape(implode(',', $_GET['orders'])) . ")
		AND post_type = 'ims_order'"
	);

	$count = count($_GET['orders']);
	$a = ( $count < 2) ? 31 : 39;

	wp_redirect($ImStore->pageurl . "&ms=$a&c=$count");
	die();
}
?>