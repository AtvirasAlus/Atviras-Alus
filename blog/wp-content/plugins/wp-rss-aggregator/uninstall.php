<?php
// If uninstall not called from WordPress exit
if( ! defined( 'WP_UNINSTALL_PLUGIN' ) )
exit ();

// Delete option from options table
delete_option( 'wprss_options' );
delete_option( 'wprss_settings' );
delete_option( 'wprss_db_version' );

