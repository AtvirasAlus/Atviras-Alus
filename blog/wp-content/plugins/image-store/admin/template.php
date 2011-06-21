<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2011
*@since 2.0.0
*/

// Stop direct access of the file
if(preg_match('#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die();

$screens = array(
	"ims-sales" 	=> array("sales",__('Sales',ImStore::domain)),
	"ims-customers" => array("users",__('Users',ImStore::domain)),
	"user-galleries"=> array("edit",__('Galleries',ImStore::domain)),
	"ims-pricing" 	=> array("pricing",__('Pricing',ImStore::domain)),
	"ims-settings" 	=> array("options-general",__('Settings',ImStore::domain))
);

//Settings
$message[1] = __("Cache cleared.",ImStore::domain);
$message[2] = __('The user was updated.',ImStore::domain);
$message[3] = __('All settings were reseted.',ImStore::domain);
$message[4] = __('The settings were updated.',ImStore::domain);

//customers
$message[10] = __('A new customer was added successfully.',ImStore::domain);
$message[11] = __('Customer updated.',ImStore::domain);
$message[12] = __('Status successfully updated.',ImStore::domain);
$message[13] = __('Customer deleted.',ImStore::domain);
$message[14] = sprintf(__('%d customers updated.',ImStore::domain),$_GET['c']);
$message[15] = sprintf(__('%d customers deleted.',ImStore::domain),$_GET['c']);

//Sales
$message[20] = __('Trash emptied',ImStore::domain);
$message[21] = __('Order deleted.',ImStore::domain);
$message[22] = __('Order status updated.',ImStore::domain);
$message[23] = __('Order moved to trash.',ImStore::domain);
$message[24] = sprintf(__('%d orders deleted.',ImStore::domain),$_GET['c']);
$message[25] = sprintf(__('Status updated on %d orders.',ImStore::domain),$_GET['c']);
$message[26] = sprintf(__('%d orders moved to trash.',ImStore::domain),$_GET['c']);

//pricing
$message[30] = __('Promotion updated.',ImStore::domain);
$message[31] = __('Promotion deleted.',ImStore::domain);
$message[32] = __('New promotion added.',ImStore::domain);
$message[33] = __('The package was updated.',ImStore::domain);
$message[34] = __('The Price list was updated.',ImStore::domain);
$message[35] = __('The new package was created.',ImStore::domain);
$message[36] = __('A new image size was created.',ImStore::domain);
$message[37] = __('Image size list was updated.',ImStore::domain);
$message[38] = __('The new list was created successfully.',ImStore::domain);
$message[39] = sprintf(__('%d promotions deleted.',ImStore::domain),$_GET['c']);

//update screen options
if(!empty($_POST['screen_options'])){
	global $user_ID;
	update_user_meta($user_ID,$_POST['screen_options']['option'],$_POST['screen_options']['value']);
	wp_redirect($pagenowurl);	
};
?>

<div class="wrap imstore">
	
	<?php if($page == 'ims-settings'){?>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post" class="paypal-donate">
		<div class="paypalinfo"> <img src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1" alt="donate">
		<input type="hidden" name="cmd" value="_s-xclick"> <input type="hidden" name="hosted_button_id" value="D64HFDXBBMXEG">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" name="submit" alt="PayPal - The safer,easier way to pay online!">
		<a href="http://imstore.xparkmedia.com/" title="image store wp plugin"><?php _e("or help promote the imstore site.",ImStore::domain)?></a>
		</div>
	</form>
	<?php }?>
	
	<?php screen_icon($screens[$page][0])?>
	<h2><?php echo $screens[$page][1]?></h2>
	
	<div id="poststuff" class="metabox-holder">
		<?php if(!empty($_GET['ms'])){?><div class="updated fade" id="message"><p><?php echo $message[$_GET['ms']]?></p></div><?php }?>
		<?php switch($page){
			case "ims-pricing":
				include_once(dirname(__FILE__).'/pricing.php');
				break;
			case "ims-customers":
				include_once(dirname(__FILE__).'/customers.php');
				break;
			case "ims-settings":
				include_once(dirname(__FILE__).'/settings.php');
				break;
			case "ims-sales":
				if($_GET['details'] == 1) include_once(dirname(__FILE__).'/sales-details.php');
				else include_once(dirname(__FILE__).'/sales.php');
				break;
			case "user-galleries":
				include_once(dirname(__FILE__).'/galleries-read.php');
				break;	
			default:
		}?>
	</div>
</div>