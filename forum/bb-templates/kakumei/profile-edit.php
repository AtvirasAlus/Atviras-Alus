<?php bb_get_header(); ?>
Profilį  radaguoti galite <a href="../brewer/profile">čia</a>
<? if(bb_current_user_can( 'edit_users' )) {?>
<div class="bbcrumb"><a href="<?php bb_uri(); ?>"><?php bb_option('name'); ?></a> &raquo; <a href="<?php user_profile_link( $user_id ); ?>"><?php echo get_user_display_name( $user_id ); ?></a> &raquo; <?php _e('Edit Profile'); ?></div>
<h2 id="userlogin" role="main"><?php echo get_user_display_name( $user->ID ); ?> <small>(<?php echo get_user_name( $user->ID ); ?>)</small></h2>
<form method="post" action="<?php profile_tab_link( $user->ID, 'edit', BB_URI_CONTEXT_FORM_ACTION + BB_URI_CONTEXT_BB_USER_FORMS ); ?>">
<fieldset>
<legend><?php _e('Profile Info'); ?></legend>
<?php bb_profile_data_form(); ?>
</fieldset>

<?php if ( bb_current_user_can( 'edit_users' ) ) : ?>
<fieldset>
<legend><?php _e('Administration'); ?></legend>
<?php bb_profile_admin_form(); ?>
</fieldset>
<?php endif; ?>

<?php if ( bb_current_user_can( 'change_user_password', $user->ID ) ) : ?>

<?php endif; ?>
<p class="submit right">
  <input type="submit" name="Submit" value="<?php echo esc_attr__( 'Update Profile &raquo;' ); ?>" />
</p>
</form>
<form method="post" action="<?php profile_tab_link($user->ID, 'edit');  ?>">
<p class="submit left">
<?php bb_nonce_field( 'edit-profile_' . $user->ID ); ?>
<?php user_delete_button(); ?>
</p>
</form>
<?}?>
<?php bb_get_footer(); ?>
