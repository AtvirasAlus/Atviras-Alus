<?php 
/**
 * Image store - download image
 * 
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0
*/

//dont cache file
header( 'Last-Modified:'.gmdate( 'D,d M Y H:i:s').' GMT' );
header( 'Cache-control:no-cache,no-store,must-revalidate,max-age=0' );

//define constants
define( 'WP_ADMIN',true);
define( 'DOING_AJAX',true);
$_SERVER['PHP_SELF'] = "/wp-admin/download.php";

//load wp
require_once '../../../../wp-load.php';

class ImStoreDownloadImage{
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function ImStoreDownloadImage( ){
	
		if( empty($_REQUEST['img']) ||  empty( $_REQUEST["_wpnonce"] ) ||
		!wp_verify_nonce( $_REQUEST["_wpnonce"], "ims_download_img") ) {
			die( );
		}
		
		global $ImStore;
		
		$this->clean = false;
		$this->image_dir = '';
		$this->id =  (int) $ImStore->decrypt_id( $_REQUEST['img'] );
		$imgsize = empty( $_REQUEST['sz'] ) ? 'preview' : $_REQUEST['sz'];
		
		$dimentions = array();
		$sizes = get_option( 'ims_sizes', true );
		
		foreach( $sizes as $size ){
			if( $size['name'] == $imgsize ){
				$dimentions = $size;
				break;
			}
		}
		
		if( empty($dimentions['w']) || empty( $dimentions['h'] )  ){
			$size = explode( 'x', strtolower( $imgsize ) );
			if( count( $size ) == 2 && is_numeric( $size[0] ) ){
				$dimentions['w'] = $size[0] ;
				$dimentions['h'] = $size[1] ;
			}else{
				$dimentions['w'] = $dimentions['h'] = false;
			}
		}
		
		$this->attachment = get_post_meta( $this->id, '_wp_attachment_metadata', true );
		
		if( empty( $this->attachment  ) )
			wp_die( __('Sorry, we could find the image') );
		
		if( isset( $this->attachment['sizes'][$imgsize]['path'] ) ){
			$this->image_dir = $this->attachment['sizes'][$imgsize]['url'];
			
		}elseif( $dimentions['w'] && $dimentions['h'] && empty( $this->store->opts['downloadorig'] ) ){ 
			
			$this->clean = true;
			$this->image_dir = image_resize( 
				$ImStore->content_dir . "/". $this->attachment['file'], 
				$dimentions['w'], $dimentions['h'], 0, 0, 0, 100
			 );
			
			if( is_wp_error($this->image_dir)  &&  isset( $this->attachment['sizes']['preview']['url'] ) ){
				$this->clean = false;
				$this->image_dir = $this->attachment['sizes']['preview']['url'] ;
				
			}elseif( is_wp_error($this->image_dir) ){
				$this->clean = false;
				$this->image_dir = $ImStore->content_dir . "/". $this->attachment['file'];
				
			}
		}elseif( !empty( $this->store->opts['downloadorig'] ) ){
			$this->image_dir = $ImStore->content_dir . "/". $this->attachment['file'];

		}else{
			$this->image_dir = $this->attachment['sizes']['preview']['path'];
			
		}
		$this->display_image( );
	}
	
	
	
	/**
	*Display image
	 *
	*@return void
	*@since 0.5.0 
	*/
	function display_image( ){
		
		global $wpdb, $ImStore;
		$ext = end( explode( '.', basename( $this->image_dir ) ) );
		$filename = $wpdb->get_var("SELECT post_title FROM $wpdb->posts WHERE ID = " . $this->id) ;
	
		header( 'Content-Type: image/'.$ext );
		
		if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) )
			header( 'Content-Length: ' . filesize( $this->image_dir ) );
		
		$color = isset( $_REQUEST['c'] ) ? $_REQUEST['c'] : false;
		$modified 	= gmdate( "D, d M Y H:i:s", @filemtime( $this->image_dir ) ); $etag = '"' . md5( $modified . $color ) . '"';
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;
				
		header( 'ETag: ' . $etag );
		header( 'Cache-control: private');
		header( "Last-Modified: $modified GMT" );
		header( 'Content-Description: File Transfer');
		header( "Content-Transfer-Encoding: binary");
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time( ) + 100000000 ) . ' GMT' );
		header( 'Cache-Control:max-age=' . ( time( ) + 100000000 ).', must-revalidate' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		
		if( empty( $_REQUEST['c'] ) || $_REQUEST['c'] == 'ims_color' ){
			readfile( $this->image_dir ); 
			die();
		}
		
		switch( $ext ){
			case "jpg":
			case "jpeg":
				$image = imagecreatefromjpeg( $this->image_dir );
				break;
			case "gif":
				$image = imagecreatefromgif( $this->image_dir );
				break;
			case "png":
				$image = imagecreatefrompng( $this->image_dir );
				break;
			default:
				die( );
		}
		
		$color = $_REQUEST['c'];
		
		//gray scale
		if( $color == 'ims_bw' ){
			imagefilter( $image, IMG_FILTER_GRAYSCALE );
			imagefilter( $image, IMG_FILTER_BRIGHTNESS, +10 );
		}
		
		//sepia
		if( $color == 'ims_sepia' ){
			imagefilter( $image, IMG_FILTER_GRAYSCALE ); 
			imagefilter( $image, IMG_FILTER_BRIGHTNESS, -10 );
			imagefilter( $image, IMG_FILTER_COLORIZE, 35, 25, 10 );
		}
		
		do_action( 'ims_apply_color_filter', &$image );
		
		//create new image
		switch( $ext ) {
			case "jpg":
			case "jpeg":
				imagejpeg($image,NULL,100);
				break;
			case "gif":
				imagegif($image);
				break;
			case "png":
				imagepng($image,NULL,9);
				break;
		}
		
		@imagedestroy( $image );
		if( $this->clean ) 
			@unlink( $this->image_dir );
			
		die( );
	}
	
	
}
//do that thing you do 
new ImStoreDownloadImage();