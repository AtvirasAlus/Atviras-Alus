<?php 
/**
 *Ajax events for admin area
 *
 *@package Image Store
 *@author Hafid Trujillo
 *@copyright 20010-2012
 *@since 0.5.0
*/

//dont cache file
header('Last-Modified:'.gmdate('D,d M Y H:i:s').' GMT');
header('Cache-control:no-cache,no-store,must-revalidate,max-age=0');

//define constants
define('WP_ADMIN',true);
define('DOING_AJAX',true);
$_SERVER['PHP_SELF']  = "/wp-admin/tinymce.php";

//load wp
require_once '../../../../../wp-load.php';

if( !current_user_can( "ims_manage_galleries") 
|| empty( $_GET['nonce'] ))  
	die( );
	
global $ImStore;
$wpjs_url =  site_url( "/wp-includes/js/" );
$admin_body_class = ' admin-color-' . sanitize_html_class( get_user_option( 'admin_color' ), 'fresh' );
?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width" />
<title><?php _e( 'Attach Gallery',  $ImStore->domain )?></title>
<meta name="robots" content="index,follow,noodp,noydir" />

<?php 
wp_admin_css( );
do_action('admin_print_styles');
?>

<link rel="stylesheet" href="<?php echo IMSTORE_URL ?>/_css/admin.css?ver=300-1235">
<link rel="stylesheet" href="<?php echo $wpjs_url ?>tinymce/themes/advanced/skins/wp_theme/dialog.css?ver=300-1235">
<script type="text/javascript">
	var imslocal = {
		tinyurl 	: '<?php echo IMSTORE_URL . "/_js/tinymce/"?>',
		imsajax 	: '<?php echo IMSTORE_ADMIN_URL . "/ajax.php"?>',
		nonceajax 	: '<?php echo wp_create_nonce( 'ims_ajax' )  ?>'
	}
</script>
<script type="text/javascript" src="<?php echo $wpjs_url ?>jquery/jquery.js?ver=300-1235"></script>
<script type="text/javascript" src="tinymce.js?ver=321-300"></script>
</head>
<body id="ims-galleries" class="hide no-js <?php echo apply_filters( 'admin_body_class', '' ) . " $admin_body_class"; ?>">

<form  method="post" tabindex="-1">

	<div id="gal-selector">
		<div id="gal-options"><br>
			<div>
				<label><span><?php _e( 'Images', $ImStore->domain  ); ?></span>
				<input id="number" type="text" tabindex="30" name="number" value="all" /> 
				<small><?php _e( 'How many images to display.', $ImStore->domain  ); ?></small></label>
			</div>
						
			<div>
				<label><span><?php _e( 'Gallery id', $ImStore->domain  ); ?></span> 
				<input id="galid" type="text" tabindex="20" class="regular-text" name="galid" /></label>
			</div>
			
			<div>
				<label><span><?php _e( 'Show as', $ImStore->domain ); ?></span></label>
				<label><input type="radio" name="layout" value="lightbox" id="lightbox" checked="checked" /><?php _e( 'Lightbox', $ImStore->domain  ); ?></label>
				<label><input type="radio" name="layout" value="slideshow" id="slideshow" /><?php _e( 'Slideshow', $ImStore->domain  ); ?></label>
				<label><input type="radio" name="layout"  value="list" id="list" /><?php _e( 'List', $ImStore->domain  ); ?></label>
			</div>
			
			<div>
				<label><span><?php _e( 'Sort by', $ImStore->domain  ); ?></span> <select name="orderby" id="order">
					<option value="0"><?php _e( 'Default', $ImStore->domain  ); ?></option>
					<option value="date"><?php _e( 'Date', $ImStore->domain  ); ?></option>
					<option value="title"><?php _e( 'Title', $ImStore->domain  ); ?></option>
					<option value="custom"><?php _e( 'Custom', $ImStore->domain  ); ?></option>
					<option value="caption"><?php _e( 'Caption', $ImStore->domain  ); ?></option>
				</select></label>
				
				<label><?php _e( 'Order', $ImStore->domain  ); ?> <select name="orderby" id="orderby">
					<option value="0"><?php _e( 'Default', $ImStore->domain  ); ?></option>
					<option value="asc"><?php _e( 'Ascending', $ImStore->domain  ); ?></option>
					<option value="desc"><?php _e( 'Descending', $ImStore->domain  ); ?></option>
				</select></label>
				
			</div>	
			
			<div>
				<label><span><?php _e( 'Link to:', $ImStore->domain  ); ?></span></label>
				<label><input type="radio" name="linkto" value="file" id="file" checked="checked" /><?php _e( 'File', $ImStore->domain  ); ?></label>
				<label><input type="radio" name="linkto" value="attachment" id="attachment" /><?php _e( 'Attachment', $ImStore->domain  ); ?></label>
			</div>
			
			<div>
				<label><span>&nbsp;</span><input id="caption" type="checkbox" name="href" />
				<?php _e( 'Show caption', $ImStore->domain  ); ?></label>
			</div>
	
		</div>
		<?php $show_internal = '1' == get_user_setting( 'imsattach', '0' ); ?>
		<p class="howto toggle-arrow <?php if ( $show_internal ) echo 'toggle-arrow-active'; ?>" id="internal-toggle">
		<?php _e( 'or search galleries', $ImStore->domain ); ?></p>
		<div id="search-panel"<?php if ( !$show_internal ) echo ' class="hide"'; ?>>
			<div class="link-search-wrapper">
				<label>
					<span><?php _e( 'Search' ); ?></span>
					<input type="text" id="search-field" class="link-search-field regular-text" tabindex="60" autocomplete="off" />
					<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				</label>
			</div>
			<div id="search-results" class="query-results">
				<ul></ul>
				<div class="river-waiting">
					<img class="waiting" src="<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>" alt="" />
				</div>
			</div>
		</div>
	</div>
	
	<div class="submitbox mceActionPanel">
		<div id="ims-link-cancel" style="float:left">
			<input type="button" id="cancel" name="cancel" value="<?php esc_attr_e( 'Cancel', $ImStore->domain )?>" class="button" />
		</div>
	
		<div id="ims-link-insert" style="float:right">
			<input type="submit" id="insert" name="insert" value="<?php esc_attr_e( 'Insert', $ImStore->domain )?>" class="button-primary" />
		</div>
	</div>
	
	<?php wp_nonce_field( 'internal-linking', '_ajax_linking_nonce', false ); ?>

</form>

</body>
</html>