<?php

function role_manager_admin_do_submit() {
	if ( !bb_verify_nonce( $_REQUEST['_wpnonce'], 'role-manager_' . $_GET['submit'] . ( in_array( $_GET['submit'], array( 'edit', 'delete' ) ) ? '-' . $_GET['role'] : '' ) ) ) {
		bb_die( __( 'Nothing to see here.', 'role-manager' ) );
	}

	switch ( $_GET['submit'] ) {
		case 'delete':
			$old_roles = bb_get_option( 'role_manager_default' );

			if ( isset( $old_roles[$_GET['role']] ) ) { // Exists by default, probably a bad idea to get rid of it.
				printf( __( '<h2>Role <strong>%s</strong> is a default and cannot be deleted.</h2>', 'role-manager' ), $old_roles[$_GET['role']][0] );
				return;
			}

			$new_roles = bb_get_option( 'role_manager_roles' );
			$role = $new_roles[$_GET['role']][0];
			unset( $new_roles[$_GET['role']] );
			bb_update_option( 'role_manager_roles', $new_roles );
			printf( __( '<h2>Role <strong>%s</strong> deleted.</h2>', 'role-manager' ), $role ); 
			break;
		case 'edit':
			$_roles = bb_get_option( 'role_manager_roles' );
			if ( !isset( $_roles[$_GET['role']] ) )
				return;

			$role  = $_roles[$_GET['role']];
			$caps  = array_keys( array_filter( $role[1] ) );
			$roles = array_filter( $caps, 'role_manager_is_possible_role' );
			$caps  = array();

			foreach ( array_keys($_POST) as $cap ) {
				if ( role_manager_is_possible_cap($cap) ) {
					$caps[] = $cap;
				}
			}

			$new_role = array( $role[0], array_fill_keys( array_merge( $roles, $caps ), true ) );

			if ( $new_role != $role ) {
				$_roles[$_GET['role']] = $new_role;
				bb_update_option( 'role_manager_roles', $_roles );
			}
?>
<h2>Role <strong><?php echo $role[0]; ?></strong> updated.</h2>
<?php		break;
		case 'create':
			$role_name = role_manager_sanitize_role( $_POST['role'] );

			if ( role_manager_role_exists( $role_name ) ) {
				printf( __( 'The role %s already exists.', 'role-manager' ), $_POST['role'] );
				return;
			}
			$old_roles = bb_get_option( 'role_manager_default' );
			if ( $_POST['based_on'] == 'blank' )
				$new_role = array( $_POST['role'], array() );
			else
				$new_role = array( $_POST['role'], $old_roles[$_POST['based_on']][1] );

			$roles = bb_get_option( 'role_manager_roles' );
			$roles[$role_name] = $new_role;
			bb_update_option( 'role_manager_roles', $roles );
			printf( __( '<h2>Role <strong>%s</strong> created.</h2>', 'role-manager' ), $_POST['role'] );
			break;
	}
}

function role_manager_admin_show_create_role( $role ) {
	$templates = role_manager_get_possible_roles();
?>
<h2><?php if ( $role )
	printf( __( 'Create role %s', 'role-manager' ), $role );
else
	_e( 'New role', 'role-manager' ); ?></h2>
<form class="settings" action="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'create' ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>" method="post">
<fieldset>
<div id="option-based_on">
	<label for="based_on"><?php _e( 'Base role on:', 'role-manager' ); ?></label>
	<div class="inputs">
		<select id="based_on" name="based_on" class="select">
<?php foreach ( $templates as $template ) { ?>
			<option value="<?php echo esc_attr( $template[1] ); ?>"><?php echo esc_html( $template[0] ); ?></option>
<?php } ?>
			<option value="blank" selected="selected"><?php esc_html_e( 'Start with a blank slate', 'role-manager' ); ?></option>
		</select>
	</div>
</div>
<div id="option-role">
	<label for="role"><?php _e( 'Role name:', 'role-manager' ); ?></label>
	<div class="inputs">
		<input id="role" name="role" type="text" class="text long" />
	</div>
</div>
<fieldset class="submit">
<?php bb_nonce_field( 'role-manager_create' ); ?>
	<input type="submit" class="submit" name="submit" value="<?php esc_attr_e( 'Create role', 'role-manager' ); ?>" />
</fieldset>
</fieldset>
</form>
<?php
}

function role_manager_admin_show_role( $_role ) {
	global $bb_roles;

	$_role = role_manager_sanitize_role( $_role );

	if ( !role_manager_role_exists( $_role ) ) {
?>
<h2>Error: Role <strong><?php echo $_role; ?></strong> does not exist.</h2>
<?php	return;
	}

	$role = array( $bb_roles->role_names[$_role], $bb_roles->get_role( $_role )->capabilities );
?>
<h2><?php printf( __( 'Editing role <strong>%s</strong>', 'role-manager' ), esc_html( $role[0] ) ); ?></h2>
<form method="post" action="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'edit', 'role' => $_role ), BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_ADMIN ); ?>" class="settings">
<fieldset>
<table class="widefat">
<thead>
<tr>
	<th scope="col"><?php _e( 'Grant', 'role-manager' ); ?></th>
	<th scope="col"><?php _e( 'Description' ); ?></th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col"><?php _e( 'Grant', 'role-manager' ); ?></th>
	<th scope="col"><?php _e( 'Description' ); ?></th>
</tr>
</tfoot>
<tbody>
<?php

$caps        = array_filter( $role[1] );
$all_caps    = role_manager_get_possible_caps();
$all_roles   = role_manager_get_possible_roles();
$desc_length = max( array_map( 'strlen', $all_caps ) ) + 1;

foreach ( $all_roles as $cap => $desc ) { ?>
<tr<?php alt_class( 'role-manager_caps' ); ?>>
	<td style="width: 5%"><input type="checkbox"<?php if ( isset( $caps[$cap] ) ) echo ' checked="checked"'; ?> disabled="disabled" /></td>
	<td><strong><?php _e( 'Role', 'role-manager' ); ?>:</strong> <big><?php echo esc_html( $desc[0] ); ?></big></td>
</tr>
<?php }

foreach ( $all_caps as $cap => $desc ) { ?>
<tr<?php alt_class( 'role-manager_caps' ); ?>>
	<td style="width: 5%"><input type="checkbox"<?php if ( isset( $caps[$cap] ) ) echo ' checked="checked"'; ?> id="<?php echo $cap; ?>" name="<?php echo $cap; ?>" /></td>
	<td><?php echo esc_html( $desc ); ?></td>
</tr>
<?php } ?>
</tbody>
</table>
<fieldset class="submit">
<input type="submit" class="submit" name="submit" value="<?php _e( 'Save', 'role-manager' ); ?>" />
<?php bb_nonce_field( 'role-manager_edit-' . $_role ); ?>
</fieldset>
</fieldset>
</form>
<?php
}

function role_manager_admin_show_main() {
	global $bb_roles;

	$old_roles = bb_get_option( 'role_manager_default' );
	$all_caps  = role_manager_get_possible_caps();
	$all_roles = role_manager_get_possible_roles();
	$names     = $bb_roles->role_names;
	ksort( $names );
?>
<h2><?php _e( 'Roles', 'role-manager' ); ?></h2>


<table id="topics-list" class="widefat">
<thead>
<tr>
	<th scope="col"><?php _e( 'Name', 'role-manager' ); ?> &mdash; <a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'create' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Add new &raquo;', 'role-manager' ); ?></a></th>
	<th scope="col"><?php _e( 'Roles', 'role-manager' ); ?></th>
	<th scope="col"><?php _e( 'Capabilities', 'role-manager' ); ?></th>
</tr>
</thead>
<tfoot>
<tr>
	<th scope="col"><?php _e( 'Name', 'role-manager' ); ?> &mdash; <a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'create' ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Add new &raquo;', 'role-manager' ); ?></a></th>
	<th scope="col"><?php _e( 'Roles', 'role-manager' ); ?></th>
	<th scope="col"><?php _e( 'Capabilities', 'role-manager' ); ?></th>
</tr>
</thead>

<tbody>
<?php
foreach ( $names as $key => $name ) {
	if ( $key == 'blocked' ) // This one should never be edited.
		continue;
?>
<tr id="role-<?php echo $key; ?>"<?php alt_class( 'roles' ); ?>>
	<td class="topic" style="width: 10%">
		<span class="row-title">
			<a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'edit', 'role' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php echo $name; ?></a>
		</span>
		<div>
			<span class="row-actions">
				<a href="<?php bb_uri( 'bb-admin/users.php', array( 'userrole[]' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'View', 'role-manager' ); ?></a>
				| <a href="<?php bb_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'edit', 'role' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ); ?>"><?php _e( 'Edit', 'role-manager' ); ?></a>
				<?php if ( !isset( $old_roles[$key] ) ) { ?>
				| <a href="<?php echo esc_attr( bb_nonce_url( bb_get_uri( '/bb-admin/admin-base.php', array( 'plugin' => 'role_manager', 'action' => 'submit', 'submit' => 'delete', 'role' => $key ), BB_URI_CONTEXT_A_HREF + BB_URI_CONTEXT_BB_ADMIN ), 'role-manager_delete-' . $key ) ); ?>"><?php _e( 'Delete', 'role-manager' ); ?></a>
				<?php } ?>
			</span>&nbsp;
		</div>
	</td>
	<td style="width: 10%; font: 1.25em monospace">
		<?php
			$role = $bb_roles->get_role( $key );
			$caps = $role->capabilities;
			foreach ( $all_roles as $cap => $desc ) { ?>
		<span title="<?php echo esc_attr( $desc[0] ); ?>" style="color: <?php
		if ( !empty( $caps[$cap] ) )
			echo '#070">&#x2714;';
		else
			echo '#700">&#x2718;'; ?></span>
		<?php } ?>
	</td>
	<td style="font: 1.25em monospace">
		<?php foreach ( $all_caps as $cap => $desc ) { ?>
		<span title="<?php echo esc_attr( $desc ); ?>" style="color: <?php
		if ( !empty( $caps[$cap] ) )
			echo '#070">&#x2714;';
		else
			echo '#700">&#x2718;'; ?></span>
		<?php } ?>
	</td>
</tr>
<?php } ?>
</tbody>
</table>

<?php
}
