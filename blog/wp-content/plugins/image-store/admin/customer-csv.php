<?php 

define("WP_ADMIN",true);

//load wp
require_once "../../../../wp-load.php";

//make sure that the request came from the same domain	
if(stripos($_SERVER["HTTP_REFERER"],get_bloginfo("siteurl")) === false) 
	die();
	
//check that a user is logged in
if(!is_user_logged_in())
	die();

//check that a user is logged in
if(!current_user_can('ims_manage_customers'))
	die();
	

header('Expires:0');
header('Pragma:no-cache');
header('Cache-control:private');
header('Last-Modified:'.gmdate('D,d M Y H:i:s').' GMT');
header('Cache-Control:no-cache,must-revalidate,max-age=0');
header('Content-Description:File Transfer');
header('Content-type:application/csv');
header('Content-Disposition:attachment; filename=image-store-customers.csv');

set_time_limit(8000);
ini_set('memory_limit','215M');

$results = $wpdb->get_results("SELECT DISTINCT ID FROM $wpdb->users AS u
		INNER JOIN $wpdb->usermeta AS um ON u.ID = um.user_id 
		WHERE um.meta_key = 'ims_status' 
		AND um.meta_value IN('active','inactive',
			(SELECT DISTINCT meta_value 
			 FROM $wpdb->usermeta 
			 WHERE meta_value LIKE '%customer%') 
		)",'ARRAY_N');

if(empty($results))
	die();
	
$colums = array(
	'first_name'	=> __('First Name',ImStore::domain),
	'last_name'		=> __('Last Name',ImStore::domain),
	'user_email'	=> __('E-mail',ImStore::domain),
	'ims_address'	=> __('Address',ImStore::domain),
	'ims_city'		=> __('City',ImStore::domain),
	'ims_state'		=> __('State',ImStore::domain),
	'ims_zip'		=> __('Zip',ImStore::domain),
	'ims_phone' 	=> __('Phone',ImStore::domain),
);


foreach($colums as $colum) echo $colum.","; echo "\n";
foreach($results as $result){
	$customer = get_userdata($result[0]);
	foreach($colums as $key => $colum)
		echo str_replace(',','',$customer->$key).",";
	echo "\n";
}	

die();
?>