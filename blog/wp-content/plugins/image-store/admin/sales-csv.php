<?php 


define( "WP_ADMIN", true );

//load wp
require_once "../../../../wp-load.php";

//make sure that the request came from the same domain	
if ( stripos( $_SERVER["HTTP_REFERER"], get_bloginfo("siteurl")) === false ) 
	die();
	
//check that a user is logged in
if ( !is_user_logged_in() )
	die();

//check that a user is logged in
if ( !current_user_can( 'ims_read_sales' ) )
	die( );
	

header( 'Expires: 0' );
header( 'Pragma: no-cache' );
header( 'Cache-control: private' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-cache, must-revalidate, max-age=0' );
header( 'Content-Description: File Transfer' );
header( 'Content-type: application/csv');
header( 'Content-Disposition: attachment; filename=image-store-sales.csv' );

set_time_limit( 5000 );
ini_set( 'memory_limit', '215M' );


$results = $wpdb->get_results(
	"SELECT ID, post_title, 
	post_status, post_date, meta_value
	FROM $wpdb->posts p 
	JOIN $wpdb->postmeta pm 
	ON ( p.ID = pm.post_id )
	WHERE post_type = 'ims_order' 
	AND post_status != 'trash'
	AND post_status != 'draft'
	AND meta_key = '_response_data'
	GROUP BY ID
	ORDER BY post_date DESC"
);


if( empty( $results ) )
	die( );

$object = 'post_date|post_status';
$colums = array(
	'txn_id'		=> __( 'Order number', ImStore::domain ), 
	'post_date'		=> __( 'Date', ImStore::domain ), 
	'payment_gross' => __( 'Amount', ImStore::domain ), 
	'tax' 			=> __( 'Tax', ImStore::domain ), 
	'first_name' 	=> __( 'Firstname', ImStore::domain ),
	'last_name' 	=> __( 'Lastname', ImStore::domain ), 
	'num_cart_items'=> __( 'Images', ImStore::domain ), 
	'payment_status'=> __( 'Payment status', ImStore::domain),
	'post_status' 	=> __( 'Order Status', ImStore::domain),
	'address_street'=> __( 'Address', ImStore::domain ),
	'address_city'	=> __( 'City', ImStore::domain ),
	'address_state'	=> __( 'State', ImStore::domain ),
	'address_zip'	=> __( 'Zip', ImStore::domain ),
	'address_country'=> __( 'Country', ImStore::domain ), 
);

foreach( $colums as $colum ) echo $colum . ","; echo "\n";
foreach( $results as $result ){
	$data = unserialize( $result->meta_value );
 	foreach( $colums as $key => $colum ){
		if( preg_match( "/($object)/i", $key ) ) echo str_replace( ',', '', $result->$key ) . ",";
		else echo str_replace( ',', '', $data[$key] ) . ",";
	}
	echo "\n";
}	

die( );
?>