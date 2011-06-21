<?php
/**
 * Template Name: Log In
 *
 * Displays a "log in" page to your users.
 *
 * @package Suffusion
 * @subpackage Templates
 */

if (isset($_SERVER['REQUEST_METHOD']) && 'POST' == $_SERVER['REQUEST_METHOD'] && !empty( $_POST['action'] ) && $_POST['action'] == 'login' ) {
	global $error;
	$login = wp_signon(array('user_login' => esc_attr($_POST['user-name']), 'user_password' => esc_attr($_POST['password']), 'remember' => esc_attr($_POST['remember-me'])), false);
}

get_header();
?>
    <div id="main-col">
<?php
suffusion_page_navigation();
suffusion_before_begin_content();
?>
  <div id="content">
    <div class="post fix" id="post-<?php the_ID(); ?>">
<?php
suffusion_after_begin_post();
?>
        <div class="entry fix">
<?php
suffusion_content();
if (is_user_logged_in()) {
	global $user_ID;
	$login = get_userdata($user_ID);
	printf(__('You are currently logged in as <a href="%1$s" title="%2$s">%2$s</a>.', 'suffusion'), get_author_posts_url($login->ID), esc_attr($login->display_name));
?>
		<a href="<?php echo wp_logout_url(get_permalink()); ?>" title="<?php _e('Log out', 'suffusion'); ?>"><?php _e('Log out', 'suffusion'); ?></a>
<?php
}
else if (isset($login) && isset($login->ID)) {
	$login = get_userdata($login->ID);
	printf(__('You have successfully logged in as <a href="%1$s" title="%2$s">%2$s</a>.', 'suffusion'), get_author_posts_url($login->ID), esc_attr($login->display_name));
}
else {
	if ($error) {
		echo $error;
	}
	wp_login_form(array(
		'redirect' => site_url(),
		'id_username' => 'user-name',
		'id_password' => 'password',
		'id_submit' => 'submit',
		'id_remember' => 'remember-me',
	));
}
?>
		</div><!--/entry -->
		<?php suffusion_before_end_post(); ?>
	<?php comments_template(); ?>

	</div><!--/post -->
</div></div>
<?php
get_footer();
?>