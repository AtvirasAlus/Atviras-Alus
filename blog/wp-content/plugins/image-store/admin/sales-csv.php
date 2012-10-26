<?php 


//define constants
define( 'DOING_AJAX', true );
define( 'WP_ADMIN', true );
$_SERVER['PHP_SELF'] = "/wp-admin/sales-csv.php";

//load wp
require_once '../../../../wp-admin/admin.php';

//check that a user is logged in
if ( !current_user_can( 'ims_read_sales' ) )
	die( );

//dont cache file
$enco = get_bloginfo( 'charset' );

header( 'Expires:0' );
header( 'Pragma:no-cache' );
header( 'Cache-control:private' );
header( 'Last-Modified:'.gmdate( 'D,d M Y H:i:s').' GMT' );
header( 'Cache-Control:no-cache,must-revalidate,max-age=0' );

header( 'Content-Description:File Transfer' );
header( 'Content-Transfer-Encoding: binary' ); 
header( 'Content-type: application/csv;  charset=' . "$enco; encoding=$enco" );
header( 'Content-Disposition:attachment; filename=image-store-sales.csv' );

$query = apply_filters( 'ims_sales_csv_query', 
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

$results = $wpdb->get_results( $query );

if( empty( $results ) )
	die( );

$columns = apply_filters( 'ims_sales_csv_columns', array(
	'txn_id'		=> __( 'Order number', 'ims'), 
	'post_date'		=> __( 'Date', 'ims'), 
	'payment_gross' => __( 'Amount', 'ims'), 
	'tax' 			=> __( 'Tax', 'ims'), 
	'first_name' 	=> __( 'Firstname', 'ims'),
	'last_name' 	=> __( 'Lastname', 'ims'), 
	'num_cart_items'=> __( 'Images', 'ims'), 
	'payment_status'=> __( 'Payment status', 'ims'),
	'post_status' 	=> __( 'Order Status', 'ims'),
	'address_street'=> __( 'Address', 'ims'),
	'address_city'	=> __( 'City', 'ims'),
	'address_state'	=> __( 'State', 'ims'),
	'address_zip'	=> __( 'Zip', 'ims'),
	'address_country'=> __( 'Country', 'ims'), 
));

$str = '';
foreach( $columns as $column ) $str .= $column ."\t"; $str .= "\n";
foreach( $results as $result ){
	$data = unserialize( $result->meta_value );
 	foreach( $columns as $key => $column ){
		if( preg_match( "/(post_date|post_status)/i", $key ) ) {
			$str .= isset( $result->$key ) ? str_replace( ',', '', $result->$key ) . "\t" : "\t";
		}else{
			$str .= isset( $data[$key] ) ? str_replace( ',', '', $data[$key] ) . "\t" : "\t";
		}
	}
	$str .= "\n";
}	
echo  chr(255) . chr(254) . mb_convert_encoding( $str,  'UTF-16LE', $enco ) ;
die( );