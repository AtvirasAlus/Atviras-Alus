<?php
/*
Plugin Name: Role Manager
Description: Allows key masters to change the capabilities that each role has and even create new roles.
Version: 0.2.1
Plugin URI: http://nightgunner5.wordpress.com/tag/role-manager/
Author: Ben L.
Author URI: http://nightgunner5.wordpress.com/
*/

function role_manager_admin_menu() {
	bb_admin_add_submenu( __( 'Roles', 'role-manager' ), 'manage_roles', 'role_manager', 'users.php' );
}
add_action( 'bb_admin_menu_generator', 'role_manager_admin_menu' );

function role_manager() {
	require_once dirname( __FILE__ ) . '/role-manager-admin.php';
	switch ( $_GET['action'] ) {
		case 'create':
			role_manager_admin_show_create_role( $_GET['role'] );
			break;
		case 'edit':
			role_manager_admin_show_role( $_GET['role'] );
			break;
		case 'submit':
			role_manager_admin_do_submit();
			break;
		default:
			role_manager_admin_show_main();
	}
}

function role_manager_role_exists( $role ) {
	global $bb_roles;

	return !!$bb_roles->get_role( $role );
}

function role_manager_sanitize_role( $role ) {
	return preg_replace( '/[^a-z]/', '', strtolower( $role ) );
}

function role_manager_init_roles() {
	global $bb_roles;

	$bb_roles->add_cap( 'keymaster', 'manage_roles' );

	$old_roles = array();

	foreach ( $bb_roles->role_objects as $role_name => $role_object ) {
		$old_roles[$role_name] = array( $bb_roles->role_names[$role_name], $role_object->capabilities );
	}

	$new_roles = bb_get_option( 'role_manager_roles' );

	if ( !$old_old_roles = bb_get_option( 'role_manager_default' ) ) { // First install.
		bb_update_option( 'role_manager_default', $old_roles );
		bb_update_option( 'role_manager_roles', $old_roles );
		return;
	} elseif ( $old_old_roles != $old_roles ) {
		$old_role_names = array_unique( array_merge( array_keys( $old_old_roles ), array_keys( $old_roles ) ) );

		$changed = false;

		foreach ( $old_role_names as $role ) {
			if ( !isset( $old_roles[$role] ) && isset( $old_old_roles[$role] ) ) {
				if ( $new_roles[$role] == $old_old_roles[$role] ) {
					unset( $new_roles[$role] );
					$changed = true;
				}
			} elseif ( isset( $old_roles[$role] ) && !isset( $old_old_roles[$role] ) ) {
				if ( !$new_roles[$role] ) {
					$new_roles[$role] = $old_roles[$role];
					$changed = true;
				} elseif ( $diff = array_diff( $old_roles[$role][1], $new_roles[$role][1] ) ) {
					foreach ( $diff as $k => $v )
						$new_roles[$role][1][$k] = $v;
					$changed = true;
				}
			} elseif ( $diff = array_diff( array_diff( $old_roles[$role][1], $old_old_roles[$role][1] ), $new_roles[$role][1] ) ) { // Find all new caps that we don't already have.
				foreach ( $diff as $k => $v )
					$new_roles[$role][1][$k] = $v;
				$changed = true;
			}
		}

		bb_update_option( 'role_manager_default', $old_roles );
		if ( $changed )
			bb_update_option( 'role_manager_roles', $new_roles );
	}

	if ( !$new_roles )
		return;

	foreach ( $new_roles as $role => $data ) {
		// Make sure everything is possible.

		if ( !$bb_roles->add_role( $role, $data[0], $data[1] ) ) {
			if ( $role == 'keymaster' ) { // The master of keys that go in gates cannot be demoted!
				$keymaster = $bb_roles->get_role( 'keymaster' );
				$bb_roles->role_names['keymaster'] = $data[0];
				foreach ( array_keys( array_filter( $data[1] ) ) as $cap ) {
					if ( $cap != 'not_play_nice' ) { // Blocking the highest possible authority? Are you mad?!
						$keymaster->add_cap( $cap );
					}
				}
			} elseif ( $role != 'blocked' ) { // Don't let blocked users have any more capabilities.
				$bb_roles->remove_role( $role );
				$bb_roles->add_role( $role, $data[0], $data[1] );
			} else {
				if ( $data[0] != $bb_roles->get_role('blocked')->name ) {
					$bb_roles->get_role('blocked')->name = $data[0];
					$bb_roles->role_names['blocked'] = $data[0];
				}
			}
		}
	}
}
add_action( 'bb_got_roles', 'role_manager_init_roles', 11 ); // After most plugins

function role_manager_is_possible_role( $role ) {
	$role_manager_possible_roles = role_manager_get_possible_roles();
	return isset( $role_manager_possible_roles[$role] );
}

function role_manager_get_possible_roles() {
	$roles = array(
		'administrate' => array( __( 'Administrator' ), 'administrator' ),
		'moderate' => array( __( 'Moderator' ), 'moderator' ),
		'participate' => array( __( 'Member' ), 'member' )
	);

	return apply_filters( 'role_manager_get_possible_roles', $roles );
}

function role_manager_is_possible_cap( $cap ) {
	$role_manager_possible_caps = role_manager_get_possible_caps();
	return isset( $role_manager_possible_caps[$cap] );
}

function role_manager_get_possible_caps() {
	$caps = array(
		'import_export' => __('Import and export data', 'role-manager'),
		'recount' => __('Use the recount feature', 'role-manager'),
		'manage_themes' => __('Switch the theme', 'role-manager'),
		'manage_plugins' => __('Activate and deactivate plugins', 'role-manager'),
		'manage_options' => __('Edit the settings of the forum', 'role-manager'),
		'edit_users' => __('Edit other users\' profiles', 'role-manager'),
		'manage_tags' => __('Rename, merge and destroy tags', 'role-manager'),
		'edit_others_favorites' => __('Edit other users\' list of favorite topics', 'role-manager'),
		'manage_forums' => __('Add and rename forums', 'role-manager'),
		'delete_forums' => __('Delete forums', 'role-manager'),
		'delete_topics' => __('Delete topics', 'role-manager'),
		'close_topics' => __('Close and open topics', 'role-manager'),
		'stick_topics' => __('Make topics sticky', 'role-manager'),
		'move_topics' => __('Move topics to other forums', 'role-manager'),
		'view_by_ip' => __('View posts made by users of an IP address', 'role-manager'),
		'edit_closed' => __('Edit closed topics', 'role-manager'),
		'edit_deleted' => __('Edit deleted topics and posts', 'role-manager'),
		'browse_deleted' => __('Use the "deleted" view', 'role-manager'),
		'edit_others_tags' => __('Remove tags from a topic', 'role-manager'),
		'edit_others_topics' => __('Edit topics by other users', 'role-manager'),
		'delete_posts' => __('Delete posts', 'role-manager'),
		'throttle' => __('Post back to back arbitrarily quickly', 'role-manager'),
		'ignore_edit_lock' => __('Edit posts on locked topics', 'role-manager'),
		'edit_others_posts' => __('Edit posts of other users', 'role-manager'),
		'edit_favorites' => __('Edit their list of favorite topics', 'role-manager'),
		'edit_tags' => __('Add tags to a topic', 'role-manager'),
		'edit_topics' => __('Edit the title and resolution status of their own topics', 'role-manager'),
		'edit_posts' => __('Edit their own posts', 'role-manager'),
		'edit_profile' => __('Edit their profile settings', 'role-manager'),
		'write_topics' => __('Make new topics', 'role-manager'),
		'write_posts' => __('Make new posts', 'role-manager'),
		'change_password' => __('Change their own password', 'role-manager'),
		'read' => __('Read the forums', 'role-manager')
	);

	$caps = apply_filters( 'role_manager_get_possible_caps', $caps );

	// Sanity check
	if ( isset( $caps['use_keys'] ) )
		unset( $caps['use_keys'] );
	if ( isset( $caps['keep_gate'] ) )
		unset( $caps['keep_gate'] );
	if ( isset( $caps['not_play_nice'] ) )
		unset( $caps['not_play_nice'] );

	return $caps;
}
