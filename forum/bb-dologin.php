<?php
// Load bbPress.
require('./bb-load.php');

// Redirect to an SSL page if required.
bb_ssl_redirect();

$user = bb_login( @$_POST['user_name'], @$_POST['user_password'], @$_POST['remember'] );

if ( $user && !is_wp_error( $user ) ) {
echo "0";//login successfully
exit;
}else{
echo "1"; //login fail
exit;
}
?>



