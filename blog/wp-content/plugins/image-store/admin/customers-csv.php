<?php 

//load wp
require_once ("../../../../wp-load.php");

check_admin_referer( 'ims_update_customer' );

//check that a user is logged in
if( !is_user_logged_in( ))
	die( );

//check that a user is logged in
if( !current_user_can( 'ims_manage_customers'))
	die( );

$enco = get_bloginfo( 'charset' );

header( 'Expires:0' );
header( 'Pragma:no-cache' );
header( 'Cache-control:private' );
header( 'Last-Modified:'.gmdate( 'D,d M Y H:i:s').' GMT' );
header( 'Cache-Control:no-cache,must-revalidate,max-age=0' );

header( 'Content-Description:File Transfer' );
header( 'Content-Transfer-Encoding: binary' ); 
header( 'Content-type: application/vnd.ms-excel;  charset=' . "$enco; encoding=$enco" );
header( 'Content-Disposition:attachment; filename=image-store-customers.csv' );

//give script resources
set_time_limit(8000);
ini_set( 'memory_limit', '215M' );
			
$query = apply_filters( 'ims_customers_csv_query', 
	"SELECT DISTINCT ID FROM $wpdb->users AS u
	INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id 
	WHERE um.meta_key = '{$wpdb->prefix}capabilities' AND um.meta_value LIKE '%customer%' 
	GROUP BY u.ID"
);

$results = $wpdb->get_results( $query , 'ARRAY_N' );
if( empty( $results ) ) die( );

global $ImStore;
$columns = apply_filters( 'ims_customers_csv_columns',
	array(
		'first_name'	=> __( 'First Name', $ImStore->domain ),
		'last_name'	=> __( 'Last Name', $ImStore->domain ),
		'user_email'	=> __( 'E-mail', $ImStore->domain ),
		'ims_address'	=> __( 'Address', $ImStore->domain ),
		'ims_city'		=> __( 'City', $ImStore->domain ),
		'ims_state'		=> __( 'State', $ImStore->domain ),
		'ims_zip'		=> __( 'Zip', $ImStore->domain ),
		'ims_phone' 	=> __( 'Phone', $ImStore->domain ),
		'ims_status' 	=> __( 'Status', $ImStore->domain ),
	)
);

$str = '';
foreach( $columns as $column ) $str .= $column ."\t"; $str .= "\n";
foreach( $results as $result ){
	$customer = get_userdata( $result[0] );
	foreach( $columns as $key => $column )
		$str .= isset( $customer->$key ) ? str_replace( ', ', '', $customer->$key ) . "\t" : "\t";
	echo  chr(255) . chr(254) . mb_convert_encoding( $str . "\n",  'UTF-16LE', $enco ) ;
}

die( );

?>