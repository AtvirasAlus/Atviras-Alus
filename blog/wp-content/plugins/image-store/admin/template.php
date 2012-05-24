<?php 

/**
*Settings page
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 2.0.0
*/

// Stop direct access of the file
if( preg_match( '#'.basename(__FILE__).'#',$_SERVER['PHP_SELF'])) 
	die( );

$count = (isset($_GET['c'])) ? $_GET['c'] : false;
$msnum	= (!empty($_GET['ms'])) ? $_GET['ms'] : false;

$screens = array(
	"ims-sales" 	=> array("sales",__( 'Sales', $this->domain ) ),
	"ims-customers" => array("users",__( 'Users', $this->domain ) ),
	"user-galleries"=> array("edit",__( 'Galleries', $this->domain ) ),
	"ims-pricing" 	=> array("pricing",__( 'Pricing', $this->domain ) ),
	"ims-settings" 	=> array("options-general",__( 'Settings', $this->domain ))
);

//Settings
$message[1] = __("Cache cleared.", $this->domain );
$message[2] = __( 'The user was updated.', $this->domain );
$message[3] = __( 'All settings were reseted.', $this->domain );
$message[4] = __( 'The settings were updated.', $this->domain );

//customers
$message[10] = __( 'A new customer was added successfully.', $this->domain );
$message[11] = __( 'Customer updated.', $this->domain );
$message[12] = __( 'Status successfully updated.', $this->domain );
$message[13] = __( 'Customer deleted.', $this->domain );
$message[14] = sprintf(__( '%d customers updated.', $this->domain ),$count);
$message[15] = sprintf(__( '%d customers deleted.', $this->domain ),$count);

//Sales
$message[20] = __( 'Trash emptied', $this->domain );
$message[21] = __( 'Order deleted.', $this->domain );
$message[22] = __( 'Order status updated.', $this->domain );
$message[23] = __( 'Order moved to trash.', $this->domain );
$message[24] = sprintf(__( '%d orders deleted.', $this->domain ),$count);
$message[25] = sprintf(__( 'Status updated on %d orders.', $this->domain ),$count);
$message[26] = sprintf(__( '%d orders moved to trash.', $this->domain ),$count);

//pricing
$message[30] = __( 'Promotion updated.', $this->domain );
$message[31] = __( 'Promotion deleted.', $this->domain );
$message[32] = __( 'New promotion added.', $this->domain );
$message[33] = __( 'The package was updated.', $this->domain );
$message[34] = __( 'The price list was updated.', $this->domain );
$message[35] = __( 'The new package was created.', $this->domain );
$message[36] = __( 'A new image size was created.', $this->domain );
$message[37] = __( 'Image size list was updated.', $this->domain );
$message[38] = __( 'The new list was created successfully.', $this->domain );
$message[39] = sprintf(__( '%d promotions deleted.', $this->domain ),$count);

$message[40] = __( 'Options saved.', $this->domain );
$message[41] = __( "You do not have sufficient permissions to access this page.", $this->domain );

?>
<div class="wrap imstore">
	<?php screen_icon($screens[$this->page][0])?>
	<h2><?php echo $screens[$this->page][1]?></h2>

	<?php if( $this->page == 'ims-settings'): //display ads?>
		<div class="adunitbox postbox">
			<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&amp;hosted_button_id=8SJEQXK5NK4ES" class="donate" title="Like the plugin? Please Donate">
				<img src="<?php echo IMSTORE_URL ?>/_img/donate.jpg" alt="donate">
			</a>
			
			<script type="text/javascript">
			/* <![CDATA[ */
    		(function() {  var s = document.createElement('script'), t = document.getElementsByTagName('script')[0];  s.type = 'text/javascript';  s.async = true; s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto'; t.parentNode.insertBefore(s, t); })();
			/* ]]> */</script>
			<a class="FlattrButton" style="display:none;" href="http://imstore.xparkmedia.com"></a>
<noscript><a href="http://flattr.com/thing/329337/Image-Store-Plugin-for-WordPress" target="_blank">
<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a></noscript>
			
			<div id="adunit">
				<iframe allowtransparency="true" src="http://xparkmedia.com/_rsc/ad.html" frameborder="0" marginheight="0" marginwidth="0" scrolling="no" height="60" width="468"></iframe>
			</div>
		</div>
	<?php endif; //end ads ?>
	
	<div id="poststuff" class="metabox-holder">
		<?php if( $msnum) echo '<div class="updated fade" id="message"><p>',$message[$msnum], '</p></div>' ?>
		<?php switch($this->page){
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
				if( isset( $_GET['details']  ) ) include_once( IMSTORE_ABSPATH . '/admin/sales-details.php');
				else include_once( IMSTORE_ABSPATH . '/admin/sales.php');
				break;
			case "user-galleries":
				include_once( IMSTORE_ABSPATH . '/admin/galleries-read.php' );
				break;	
			default:
		}?>
	</div>
</div>
