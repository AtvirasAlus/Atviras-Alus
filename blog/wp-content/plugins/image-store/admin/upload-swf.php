<form enctype="multipart/form-data" method="post" action="<?php echo admin_url( 'media-upload.php?inline=&amp;upload-page-form=' ); ?>" class="media-upload-form type-form validate" id="file-form">

<?php 

if ( !current_user_can( 'ims_manage_galleries') )
	return;
	
$flash = true;
if ( false !== stripos($_SERVER['HTTP_USER_AGENT'], 'mac')
&& apache_mod_loaded( 'mod_security') )
	$flash = false;

global $post;
$flash = apply_filters( 'flash_uploader', $flash );
$post_id = ( empty( $this->galid ) ) ? $post->ID : $this->galid ;
 
// Check quota for this blog if multisite
if ( is_multisite( ) && !is_upload_space_available( ) ){
	echo '<p>' . sprintf( __( 'Sorry, you have filled your storage quota (%s MB).', 'ims'), get_space_allowed( ) ) . '</p>';
	return;
}

do_action( 'pre-upload-ui' );

if ( $flash ) :
$post_params = array(
	"post_id" => $post_id,
	"folderpath" => $this->galpath,
	"_wpnonce" => wp_create_nonce( 'media-form' ), 
	"logged_in_cookie" => $_COOKIE[LOGGED_IN_COOKIE],
	"auth_cookie" => (is_ssl( ) ? $_COOKIE[SECURE_AUTH_COOKIE] : $_COOKIE[AUTH_COOKIE] ),
 );

$post_params = apply_filters( 'swfupload_post_params', $post_params );
foreach ( $post_params as $param => $val )
	$p[] = "\t\t'$param' : '$val'";
$post_params_str = implode( ", \n", (array)$p );
$upload_image_path = includes_url( 'images/upload.png?ver=20100531' );

?>
<script type="text/javascript">
//<![CDATA[
var swfu;
SWFUpload.onload = function( ){
	var settings = {
			button_text: '<span class="button"><?php _e( 'Select Files' ); ?><\/span>',
			button_text_style: '.button { text-align: center; font-weight: bold; font-family:"Lucida Grande",Verdana,Arial,"Bitstream Vera Sans",sans-serif; font-size: 11px; text-shadow: 0 1px 0 #FFFFFF; color:#464646; }',
			button_height: "23",
			button_width: "132",
			button_text_top_padding: 3,
			button_window_mode : "opaque",
			button_image_url: '<?php echo $upload_image_path; ?>',
			button_placeholder_id: "flash-browse-button",
			upload_url : "<?php echo IMSTORE_URL . "/admin/upload-img.php" ?>",
			flash_url : "<?php echo includes_url( 'js/swfupload/swfupload.swf' ); ?>",
			file_post_name: "async-upload",
			file_types: "<?php echo apply_filters( 'upload_file_glob', '*.*' ); ?>",
			post_params : {<?php echo $post_params_str; ?>},
			file_size_limit : "<?php echo wp_max_upload_size( ); ?>b",
			file_dialog_start_handler : imsFileDialogStart,
			file_queued_handler : imsFileQueued,
			upload_start_handler : uploadStart,
			upload_progress_handler : uploadProgress,
			upload_error_handler : uploadError,
			upload_success_handler : <?php echo apply_filters( 'swfupload_success_handler', 'imsUploadSuccess' ); ?>,
			upload_complete_handler : uploadComplete,
			file_queue_error_handler : imsFileQueueError,
			file_dialog_complete_handler : fileDialogComplete,
			swfupload_pre_load_handler: swfuploadPreLoad,
			swfupload_load_failed_handler: swfuploadLoadFailed,
			custom_settings : {
				degraded_element_id : "html-upload-ui", // id of the element displayed when swfupload is unavailable
				swfupload_element_id : "flash-upload-ui" // id of the element displayed when swfupload is available
			},
			debug: false
		};
		swfu = new SWFUpload(settings );
};
//]]>
</script>
<div id="flash-upload-ui" class="hide-if-no-js">
	<?php do_action( 'pre-flash-upload-ui' ); ?>
	<div>
		<div id="flash-browse-button"></div>
		<span><input id="cancel-upload" disabled="disabled" onclick="cancelUpload( )" type="button" value="<?php esc_attr_e( 'Cancel Upload', 'ims'); ?>" class="button" /></span>
	</div>
	<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %s', 'ims'), $this->get_max_file_upload( true ) ); ?></p>
	<?php do_action( 'post-flash-upload-ui' ); ?>
</div>

<?php //do_action( 'post-upload-ui' ); ?>
<?php endif //flash upload ?>

	<div id="html-upload-ui" <?php if ( $flash ) echo 'class="hide-if-js"'; ?>>
	<?php do_action( 'pre-html-upload-ui' ); ?>
		<p id="async-upload-wrap">
			<label class="screen-reader-text" for="async-upload"><?php _e( 'Upload' ); ?></label>
			<input type="file" name="async-upload" id="async-upload" />
			<input type="submit" class="button" name="html-upload" value="<?php esc_attr_e( 'Upload', 'ims') ?>">
			<a href="#" onclick="try{top.tb_remove( );}catch(e){}; return false;"><?php _e( 'Cancel', 'ims') ?></a>
		</p>
		<div class="clear"></div>
		<p class="media-upload-size"><?php printf( __( 'Maximum upload file size: %s', 'ims'), $this->get_max_file_upload( true ) ); ?></p>
		<?php if ( is_lighttpd_before_150( ) ): ?>
		<p><?php _e( 'If you want to use all capabilities of the uploader, like uploading multiple files at once, please update to lighttpd 1.5.', 'ims'); ?></p>
		<?php endif;?>
		<?php do_action( 'post-html-upload-ui', true ); ?>
	</div>
	
	<?php do_action( 'post-upload-ui' ); ?>
	
	<input type="hidden" name="post_id" id="post_id" value="0" />
</form>
