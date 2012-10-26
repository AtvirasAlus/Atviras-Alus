<?php

if (!current_user_can('ims_manage_customers'))
	die();

//clear cancel post data
if (isset($_POST['cancel'])) {
	wp_redirect($this->pageurl);
	die();
}

//add/update customer
if (isset($_POST['add_customer']) || isset($_POST['update_customer'])) {
	check_admin_referer('ims_update_customer');
	$errors = ims_create_customer($this->pageurl);
}

//update user status
if (!empty($_GET['action'])) {
	check_admin_referer('ims_update_customer');
	$errors = ims_update_customer_status($this->pageurl);
}

//display error message
if (isset($errors) && is_wp_error($errors))
	$this->error_message($errors);

$role = 'customer';
$nonce = '_wpnonce=' . wp_create_nonce('ims_update_customer');
$userspage = isset($_GET['userspage']) ? $_GET['userspage'] : NULL;
$usersearch = isset($_GET['usersearch']) ? $_GET['usersearch'] : NULL;
$current_status = isset($_GET['status']) ? $_GET['status'] : 'active';
$user_action = empty($_GET['useraction']) ? false : $_GET['useraction'];
$edit_userid = empty($_GET['userid']) ? false : (int) $_GET['userid'];

$columns = get_column_headers('ims_gallery_page_ims-customers');
$hidden = get_hidden_columns('ims_gallery_page_ims-customers');

$user_status = array(
	'active' => __('Active', 'ims'),
	'inative' => __('Inative', 'ims'),
);

$user_box_title = array(
	'new' => __('New Customer', 'ims'),
	'edit' => __('Edit Customer', 'ims'),
);

//search users
$wp_user_search = new WP_User_Search($usersearch, $userspage, $role);
cache_users($wp_user_search->get_results());

if ($current_status == 'inative')
	$user_status['delete'] = __('Delete', 'ims');

$user_status = apply_filters('ims_user_status', $user_status, $current_status);


if ($user_action):

	//edit user
	if ($user_action == 'edit' && $user_action && empty($_POST)) {
		$userdata = get_metadata('user', $edit_userid);
		foreach ($userdata as $meta => $value)
			$_POST[$meta] = maybe_unserialize($value[0]);

		$user = $wpdb->get_row($wpdb->prepare(
			"SELECT * FROM $wpdb->users WHERE ID = %s", $edit_userid
		));
		$_POST = array_merge($_POST, (array) $user);
	}

	foreach (array('first_name', 'last_name', 'ims_address', 'ims_city', 'ims_state', 'ims_phone', 'ims_zip', 'user_email', '_MailPress_sync_wordpress_user') as $key)
		$data[$key] = ( isset($_POST[$key]) ) ? esc_attr($_POST[$key]) : false;
	extract($data);
	?>

	<form id="list-filter" action="" method="post">
		<div class="postbox">
			<div class="handlediv"><br /></div>
			<h3 class='hndle'><span><?php echo $user_box_title[$user_action] ?></span></h3>
			<div class="inside">
				<table class="ims-table">
					<tr>
						<td width="15%"><label for="first_name"><?php _e('First Name', 'ims') ?></label></td>
						<td><input type="text" name="first_name" id="first_name" class="widefat" value="<?php echo esc_attr($first_name) ?>" /></td>
						<td width="15%"><label for="last_name"><?php _e('Last Name', 'ims') ?></label></td>
						<td><input type="text" name="last_name" id="last_name" class="widefat" value="<?php echo esc_attr($last_name) ?>" /></td>
					</tr>

					<tr class="alternate">
						<td><label for="ims_address"><?php _e('Address', 'ims') ?></label></td>
						<td><input type="text" name="ims_address" id="ims_address" class="widefat" value="<?php echo esc_attr($ims_address) ?>" /></td>
						<td><label for="ims_city"><?php _e('City', 'ims') ?></label></td>
						<td><input type="text" name="ims_city" id="ims_city" class="widefat" value="<?php echo esc_attr($ims_city) ?>" /></td>
					</tr>

					<tr>
						<td><label for="ims_state"><?php _e('State', 'ims') ?></label></td>
						<td><input type="text" name="ims_state" id="ims_state" class="widefat" value="<?php echo esc_attr($ims_state) ?>" /></td>
						<td><label for="ims_phone"><?php _e('Phone', 'ims') ?></label></td>
						<td><input type="text" name="ims_phone" id="ims_phone" class="widefat" value="<?php echo esc_attr($ims_phone) ?>"/></td>
					</tr>

					<tr class="alternate">
						<td><label for="ims_zip"><?php _e('Zip', 'ims') ?></label></td>
						<td><input type="text" name="ims_zip" id="ims_zip" class="widefat" value="<?php echo esc_attr($ims_zip) ?>" /></td>
						<td scope="row"><label for="user_email"><?php _e('Email', 'ims') ?></label></td>
						<td><input type="text" name="user_email" id="user_email" class="widefat" value="<?php echo esc_attr($user_email) ?>" /></td>
					</tr>

					<?php do_action('ims_cutomer_data_row', $edit_userid) ?>
					<tr>
						<td colspan="3"><?php if (class_exists('MailPress')) : ?>
								<input type="checkbox" name="_MailPress_sync_wordpress_user" id="_MailPress_sync_wordpress_user" value="<?php echo $_MailPress_sync_wordpress_user ?>" />
								<label for="_MailPress_sync_wordpress_user"><?php _e('Include user in the eNewsletters', 'ims') ?></label> <?php endif ?>
						</td>
						<td class="textright">
							<input type="submit" name="cancel" value="<?php esc_attr_e('Cancel', 'ims') ?>" class="button" />
							<input type="submit" name="update_customer" value="<?php esc_attr_e('Save', 'ims') ?>" class="button-primary" />
							<input type="hidden" name="userid" value="<?php echo esc_attr($edit_userid) ?>" />
							<input type="hidden" name="useraction" value="<?php echo esc_attr($user_action) ?>" />
							<?php wp_nonce_field('ims_update_customer') ?>
						</td>
					</tr>
				</table><!--.ims-table-->
			</div><!--.inside-->
		</div><!--.postbox-->
	</form>

<?php endif; // new/edit user ?>


<div class="filter">
	<form id="list-filter" action="" method="get">
		<ul class="subsubsub">
			<?php $this->count_links($user_status, array('type' => 'customer')) ?>
		</ul>
	</form>
</div><!--.filter-->

<form class="search-form" action="<?php echo admin_url('edit.php?') ?>" method="get">
	<p class="search-box">
		<label class="screen-reader-text" for="user-search-input"><?php _e('Search Users', 'ims'); ?>:</label>
		<input type="text" name="usersearch" id="user-search" value="<?php echo esc_attr($wp_user_search->search_term); ?>" />
		<input type="submit" value="<?php esc_attr_e('Search Users', 'ims'); ?>" class="button" />
		<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']) ?>" />
		<input type="hidden" name="post_type" value="<?php echo esc_attr($_GET['post_type']) ?>" />
		<?php wp_nonce_field('ims_update_customer') ?>
	</p>
</form><!--.search-form-->

<form id="posts-filter" action="<?php echo $this->pageurl ?>" method="get">
	<div class="tablenav">

		<select name="action">
			<option selected="selected"><?php _e('Bulk Actions', 'ims') ?></option>
			<?php
			foreach ($user_status as $status => $label) {
				if ($current_status == $status)
					continue;
				echo '<option value="', esc_attr($status), '">', esc_html($label), '</option>';
			}
			?>
		</select>
		<input type="submit" value="<?php esc_attr_e('Apply', 'ims'); ?>" name="doaction" class="button-secondary" /> |

		<a href="<?php echo IMSTORE_ADMIN_URL, "/customers-csv.php?$nonce" ?>" class="button"><?php _e('Download CSV', 'ims'); ?></a> 
		<a href="<?php echo $this->pageurl . "&amp;$nonce&amp;useraction=new" ?>" class="button"><?php _e('New Customer', 'ims'); ?></a>

		<br class="clear" />
	</div><!--.tablenav-->


	<!--User List-->
	<?php if ($wp_user_search->get_results()) : ?>
	<?php if ($wp_user_search->is_search()) : ?>
			<p><a href="<?php echo $this->pageurl ?>"><?php _e('&larr; Back to all customers', 'ims'); ?></a></p>
	<?php endif; ?>

		<table class="widefat post fixed imstore-table">
			<thead>
				<tr class="thead">
				<?php print_column_headers('ims_gallery_page_ims-customers') ?>
				</tr>
			</thead>
			<tbody id="users" class="list:user user-list">
				<?php
				$style = '';
				foreach ($wp_user_search->get_results() as $userid) {
					$user_object = new WP_User($userid);
					$roles = $user_object->roles;
					$role = array_shift($roles);
					$customer = get_userdata($userid);

					if (is_multisite() && empty($role))
						continue;

					$style = ( ' alternate' == $style ) ? '' : ' alternate';
					$r = "<tr id='user-$user_object->ID' class='u-edit{$style}'>";
					foreach ($columns as $column_id => $column_name) {

						$hide = ( $this->in_array($column_id, $hidden) ) ? ' hidden' : '';

						switch ($column_id) {
							case 'cb':
								$r .= "<th scope='row' class='check-column'><input type='checkbox' name='customer[]' value='" . esc_attr($userid) . "' /></th>";
								break;
							case 'name':
								$r .= "<td class='column-{$column_id}{$hide}'>$user_object->first_name";
								$r .= "\t<div class='row-actions'>";
								$r .= "\t\t<a href='$this->pageurl&amp;$nonce&amp;useraction=edit&amp;userid=$userid' title='" . __("Edit information", 'ims') . "'>" . __("Edit", 'ims') . "</a> | ";
								$stat = ($current_status == 'inative') ? 'active' : 'inative';
								$r .= "<a href='$this->pageurl&amp;$nonce&amp;action={$stat}&amp;customer=$userid' title='" . $user_status[$stat] . "'>" . $user_status[$stat] . "</a>";
								if ($current_status == 'inative')
									$r .= " | <span class='delete'><a href='$this->pageurl&amp;$nonce&amp;action=delete&amp;customer=$userid' title='" . $user_status['delete'] . "'>" . $user_status['delete'] . "</a></span>";
								$r .= "\t</div>";
								$r .= "</td>";
								break;
							case 'lastname':
								$r .= "<td class='column-{$column_id}{$hide}'>" . (empty($customer->last_name) ? '&nbsp;' : $customer->last_name) . "</td>";
								break;
							case 'email':
								$r .= "<td class='column-{$column_id}{$hide}'>" . (empty($customer->user_email) ? '&nbsp;' : $customer->user_email) . "</td>";
								break;
							case 'phone':
								$r .= "<td class='column-{$column_id}{$hide}'>" . (empty($customer->ims_phone) ? '&nbsp;' : $customer->ims_phone) . "</td>";
								break;
							case 'city':
								$r .= "<td class='column-{$column_id}{$hide}'>" . (empty($customer->ims_city) ? '&nbsp;' : $customer->ims_city) . "</td>";
								break;
							case 'state':
								$r .= "<td class='column-{$column_id}{$hide}'>" . (empty($customer->ims_state) ? '&nbsp;' : $customer->ims_state) . "</td>";
								break;
							case 'newsletter':
								$r .= "<td class='column-{$column_id}{$hide}'>" .
										((class_exists('MailPress') && $customer->_MailPress_sync_wordpress_user) ? __("Yes", 'ims') : __("no", 'ims')) . "</td>";
								break;
							default:
								$r .= "<td class='column-{$column_id}{$hide}'>" . apply_filters('manage_ims_customers_custom_column', '', $column_name, $customer) . "</td>";
						}
					}
					echo $r .= "</tr>";
				}
				?>
			</tbody>
		</table>

		<?php endif; ?>
	<div class="tablenav">
<?php if ($wp_user_search->results_are_paged()) : ?>
			<div class="tablenav-pages"><?php $wp_user_search->page_links(); ?></div>
	<?php endif; ?>
	</div>

<?php wp_nonce_field('ims_update_customer') ?>
	<input type="hidden" name="page" value="<?php echo esc_attr($_GET['page']) ?>" />
	<input type="hidden" name="post_type" value="<?php echo esc_attr($_GET['post_type']) ?>" />
</form><!--.posts-filter-->


<?php

/**
 * Insert/update a customer
 *
 * @since 3.0.0
 * return array errors
 */
function ims_create_customer($pagenowurl) {
	global $ImStore;

	$userid = (int) $_POST['userid'];
	$errors = new WP_Error( );
	$user_action = empty($_POST['useraction']) ? false : $_POST['useraction'];

	if (empty($_POST['first_name']))
		$errors->add('empty_first_name', __('The first name is required.', 'ims'));

	if (empty($_POST['last_name']))
		$errors->add('empty_last_name', __('The last name is required.', 'ims'));

	if (empty($_POST['last_name']) || !is_email($_POST['user_email']))
		$errors->add('valid_email', __('A valid email is required.', 'ims'));

	$user = get_userdata($userid);

	if (( email_exists($_POST['user_email']) && $user_action != 'edit' ) ||
			( isset($user->user_email) && $user->user_email != $_POST['user_email'] && $user_action == 'edit' && email_exists($_POST['user_email']) ))
		$errors->add('email_exists', __('This email is already registered, please choose another one.', 'ims'));

	$errors = apply_filters('ims_save_user_errors', $errors, $_POST);
	$user_name = sanitize_user($_POST['first_name'] . ' ' . $_POST['last_name']);

	if (username_exists($user_name) && $user_action != 'edit' ||
			( isset($user->user_login) && $user->user_login != $user_name && $user_action == 'edit' && username_exists($user_name) ))
		$errors->add('customer_exists', __('That customer already exists.', 'ims'));

	if (!empty($errors->errors))
		return $errors;

	$userdata = array(
		'user_nicename' => $user_name,
		'user_login' => $user_name,
		'role' => 'customer',
		'ID' => $userid,
		'user_email' => $_POST['user_email'],
		'first_name' => $_POST['first_name'],
		'last_name' => $_POST['last_name'],
		'user_pass' => wp_generate_password(12, false),
	);

	$user_id = wp_insert_user($userdata);

	if (is_wp_error($user_id))
		return $user_id;

	if ($user_action == 'new' || !get_user_meta($user_id, 'ims_status'))
		update_user_meta($user_id, 'ims_status', 'active');

	$meta_keys = array('ims_zip', 'ims_city', 'ims_phone', 'ims_state', 'ims_address', '_MailPress_sync_wordpress_user');
	foreach ($meta_keys as $key) {
		$_POST[$key] = isset($_POST[$key]) ? $_POST[$key] : false;
		update_user_meta($user_id, $key, $_POST[$key]);
	}

	do_action('ims_update_user', $user_id, $user_action);

	$msid = ( $user_action == 'new' ) ? 10 : 2;
	wp_redirect($pagenowurl . "&ms=$msid");
	die();
}

/**
 * Update user status
 *
 * @since 3.0.0
 * return void
 */
function ims_update_customer_status($pagenowurl) {
	global $wpdb;

	$count = count((array) $_GET['customer']);
	echo $customers = implode(', ', (array) $_GET['customer']);

	if (empty($customers))
		return false;

	$ms = '';
	$action = $_GET['action'];
	if ($action == 'delete') {
		$updated = $wpdb->query($wpdb->prepare(
						"DELETE u, um FROM $wpdb->users u JOIN $wpdb->usermeta um 
			ON ( u.id = um.user_id ) AND u.id IN ( $customers ) "
				)
		);
		$ms = "15&status=inative";
	} else {
		$updated = $wpdb->query($wpdb->prepare(
						"UPDATE $wpdb->usermeta SET meta_value = '%s' 
			WHERE meta_key = 'ims_status' AND user_id IN( $customers )"
						, $action)
		);
		$ms = 14;
	}

	if (empty($updated))
		return false;

	do_action('ims_update_users', $customers);
	wp_redirect($pagenowurl . "&ms={$ms}&c=$count");
	die();
}
?>