<?php

/**
 * Settings page
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 2.0.0
 */

// Stop direct access of the file
if (!defined('ABSPATH'))
	die();

$count = (isset($_GET['c'])) ? $_GET['c'] : false;
$msnum = (!empty($_GET['ms'])) ? $_GET['ms'] : false;

$screens = array(
	"ims-sales" => array("sales", __('Sales', 'ims')),
	"ims-customers" => array("users", __('Users', 'ims')),
	"user-galleries" => array("edit", __('Galleries', 'ims')),
	"ims-pricing" => array("pricing", __('Pricing', 'ims')),
	"ims-settings" => array("options-general", __('Settings', 'ims'))
);

//Settings
$message[1] = __("Cache cleared.", 'ims');
$message[2] = __('The user was updated.', 'ims');
$message[3] = __('All settings were reseted.', 'ims');
$message[4] = __('The settings were updated.', 'ims');

//customers
$message[10] = __('A new customer was added successfully.', 'ims');
$message[11] = __('Customer updated.', 'ims');
$message[12] = __('Status successfully updated.', 'ims');
$message[13] = __('Customer deleted.', 'ims');
$message[14] = sprintf(__('%d customers updated.', 'ims'), $count);
$message[15] = sprintf(__('%d customers deleted.', 'ims'), $count);

//Sales
$message[20] = __('Trash emptied', 'ims');
$message[21] = __('Order deleted.', 'ims');
$message[22] = __('Order status updated.', 'ims');
$message[23] = __('Order moved to trash.', 'ims');
$message[24] = sprintf(__('%d orders deleted.', 'ims'), $count);
$message[25] = sprintf(__('Status updated on %d orders.', 'ims'), $count);
$message[26] = sprintf(__('%d orders moved to trash.', 'ims'), $count);

//pricing
$message[30] = __('Promotion updated.', 'ims');
$message[31] = __('Promotion deleted.', 'ims');
$message[32] = __('New promotion added.', 'ims');
$message[33] = __('The package was updated.', 'ims');
$message[34] = __('The price list was updated.', 'ims');
$message[35] = __('The new package was created.', 'ims');
$message[36] = __('A new image size was created.', 'ims');
$message[37] = __('Image size list was updated.', 'ims');
$message[38] = __('The new list was created successfully.', 'ims');
$message[39] = sprintf(__('%d promotions deleted.', 'ims'), $count);

$message[40] = __('Options saved.', 'ims');
$message[41] = __("You do not have sufficient permissions to access this page.", 'ims');

$message[42] = __('Color option list was updated.', 'ims');
$message[43] = __('Shipping option list was updated.', 'ims');
$message[44] = __('Finish list was updated.', 'ims');
$message[45] = __('Filter list was updated.', 'ims');
?>
<div class="wrap imstore">

<?php screen_icon($screens[$this->page][0]) ?>
	<h2><?php echo $screens[$this->page][1] ?></h2>

	<?php if ($this->page == 'ims-settings'): //display ads?>
		<div class="ims-social-box">
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=8SJEQXK5NK4ES" class="ims-donate" title="Like the plugin? Please Donate"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_LG.gif" alt="donate" height="20"></a>
			<a href="https://twitter.com/xparkmedia" class="twitter-follow-button" data-show-count="false">Follow @xparkmedia</a>
			<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>
		</div><!--.ims-social-box-->

		<div class="adunitbox postbox">
			<script type="text/javascript">
				/* <![CDATA[ */
				(function() {  var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];  s.type = 'text/javascript';  s.async = true; s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto'; t.parentNode.insertBefore(s, t); })();
				/* ]]> */</script>
			<a class="FlattrButton" style="display:none;" href="http://imstore.xparkmedia.com"></a>

			<div id="adunit">
				<iframe allowtransparency="true" src="http://xparkmedia.com/_rsc/ad.html" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" height="60" width="468"></iframe>
			</div><!--.adunit-->
		</div><!--.adunitbox-->
<?php endif; //end ads  ?>

	<div id="poststuff" class="metabox-holder">
	<?php if ($msnum) echo '<div class="updated fade" id="message"><p>', $message[$msnum], '</p></div>' ?>
<?php
switch ($this->page) {
	case "ims-settings":
		include_once( IMSTORE_ABSPATH . '/admin/settings.php' );
		break;
	case "ims-customers":
		include_once( IMSTORE_ABSPATH . '/admin/customers.php' );
		break;
	case "ims-pricing":
		include_once( IMSTORE_ABSPATH . '/admin/pricing.php' );
		break;
	case "ims-sales":
		if (isset($_GET['details']))
			include_once( IMSTORE_ABSPATH . '/admin/sales-details.php');
		else
			include_once( IMSTORE_ABSPATH . '/admin/sales.php');
		break;
	case "user-galleries":
		include_once( IMSTORE_ABSPATH . '/admin/galleries-read.php' );
		break;
	default:
}
?>
	</div>
</div><!--.wrap .imstore-->
