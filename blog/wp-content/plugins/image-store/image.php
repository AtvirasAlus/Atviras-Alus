<?php 

/**
*Image store - secure image
*
*@package Image Store
*@author Hafid Trujillo
*@copyright 20010-2012
*@since 0.5.0 
*/

//define constants
define( 'SHORTINIT', true );
define( 'DOING_AJAX', true );
	
//load wp
if( isset( $_REQUEST['c'] ) || isset( $_REQUEST['w'] ) )
    require_once '../../../wp-load.php';

class ImStoreImage{

	var $key = false;
	var $nowatermark = false;
	
	/**
	*Constructor
	 *
	*@return void
	*@since 0.5.0 
	*/
	function __construct( ){		
		
		if( empty( $_GET['i'] ) ) die( );

		$this->key = rand( );
		if( $dh = @opendir( dirname( __FILE__ ) . "/admin/_key" ) ){
			while( false !== ( $obj = readdir( $dh ) ) ){
				if( $obj == '.' || $obj == '..' || !preg_match('/\.(txt)$/i', $obj ) ){ 
					continue;
				}else{ 
					$this->key = current( explode( '.', $obj ) ); 
					break;
				}
			}
			@closedir( $dh );
		}
		
	 	$path = $this->url_decrypt( str_replace( ' ', '+', $_GET['i'] ) );
		if( preg_match( '#^([0-9]{1,2})$#', basename( $path ) )){
			$this->nowatermark = true;
			$path = dirname( $path );
		}
	
		$this->root = implode( '/', explode( '/', str_replace( '\\', '/', dirname( __FILE__ ) ), -3 ) )."/";
		$this->image_dir = "{$this->root}wp-content/$path";
		
		if( !file_exists( $this->image_dir ) || !preg_match( '/\.(png|jpe?g|gif)$/i', $this->image_dir ))
			die( );
		
		$this->display_image();
	}
	
	/**
	*Display image
	 *
	*@return void
	*@since 0.5.0 
	*/
	function display_image( ){
		
		$ext = end( explode( '.', basename( $this->image_dir ) ) );
		header( 'Content-Type: image/'. $ext );
		
		//if ( false === strpos( $_SERVER['SERVER_SOFTWARE'], 'Microsoft-IIS' ) )
			//header( 'Content-Length: ' . filesize( $this->image_dir ) );
		
		$color 		= isset( $_REQUEST['c'] ) ? $_REQUEST['c'] : false;
		$cache 		= substr( @filemtime( dirname( __FILE__ ) . "/admin/_key/{$this->key}.txt" ) , -4 );
		$modified 	= gmdate( "D, d M Y H:i:s", ( @filemtime( $this->image_dir ) + $cache ) ); 
		
		$etag = '"' . md5( $modified . $color ) . '"';
		$client_etag = isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) ? stripslashes( $_SERVER['HTTP_IF_NONE_MATCH'] ) : false;
		
		header( 'Last-Modified:'.gmdate( 'D,d M Y H:i:s').' GMT' );
		header( 'Cache-control:no-cache,no-store,must-revalidate,max-age=0' );
	
		header( 'ETag: ' . $etag );
		header( "Last-Modified: $modified GMT" );
		header( 'Expires: ' . gmdate( 'D, d M Y H:i:s', time( ) + 100000000 ) . ' GMT' );
		header( 'Cache-Control:max-age=' . ( time( ) + 100000000 ).', must-revalidate' );
		
		if( ( isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) && isset( $_SERVER['HTTP_IF_NONE_MATCH'] )
		&&( strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) == ( @filemtime( $this->image_dir ) + $cache ) ) ) || ( $client_etag == $etag ) ){
			header( 'HTTP/1.1 304 Not Modified' ); 
			die( );
		}
		
		if( empty( $color ) && !$this->nowatermark ){
			readfile( $this->image_dir );
			die( );
		}
		
		//increase memory resources
		//use to process big images
		ini_set( 'memory_limit', '256M' );
		ini_set( 'allow_url_fopen', true );
		ini_set( 'set_time_limit', '1000' );
		
		if( !function_exists('get_site_option') ){
			header("HTTP/1.0 404 Not Found");
			die( );
		}
		
		if( get_site_option( 'ims_sync_settings' )  ) 
			switch_to_blog( 1 );
			
		$opts = get_option( 'ims_front_options' );
		if( is_multisite() ) restore_current_blog( );
		
		$filetype 	= wp_check_filetype( basename( $this->image_dir ) );
		$filetype['ext'] = strtolower($filetype['ext']);
		
		switch( $filetype['ext'] ){
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
		
		//add water mark		
		if( $opts['watermark'] ){
		
			$local = get_option( 'ims_wlocal' );
			 
			//text watermark
			if( $opts['watermark'] == 1 ){
				
				//backwards compatiblity
				$textcolor = isset( $opts['watermark_color']) ? $opts['watermark_color'] : $opts['textcolor'];
				$font_size = isset( $opts['watermark_size']) ? $opts['watermark_size'] : $opts['fontsize'];
				$font_text = isset( $opts['watermark_text']) ? $opts['watermark_text'] : $opts['watermarktext'];
				$trannsperency = isset( $opts['watermark_trans'] ) ? $opts['watermark_trans'] : $opts['transperency'];
		
				
				$font 	= dirname( __FILE__ ).'/_fonts/arial.ttf';
				$rgb 	= $this->HexToRGB( $textcolor );
				$black 	= imagecolorallocatealpha( $image, 0, 0, 0, 90 );
				$icolor = imagecolorallocatealpha( $image, $rgb['r'], $rgb['g'], $rgb['b'], $trannsperency);
				
				$info = getimagesize( $this->image_dir );
				$tb = imagettfbbox( $font_size, 0, $font, $font_text );
				
				switch( $local ){
					case 1:
						$x = 2;
						$y = abs($tb[5]) + 2;
						 break;
					case 2:
						$x = ceil( ( $info[0] - $tb[2] ) / 2 );
						$y = abs($tb[5]) + 2;
						 break;
					case 3:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = abs($tb[5]) + 2;
						 break;
					case 4:
						$x = 2;
						$y = $info[1]/2;
						 break;
					case 5:
						$x = ceil( ( $info[0] - $tb[2] ) / 2 );
						$y = $info[1]/1.7;
						 break;
					case 6:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = $info[1]/2;
						 break;
					case 7:
						$x = 2;
						$y = $info[1]/1.03;
						 break;
					case 9:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = $info[1]/1.03;
						 break;
					default:
					 $x = ceil( ( $info[0] - $tb[2] ) / 2 );
					 $y = $info[1]/1.03;
				}
					 
				imagettftext( $image, $font_size, 0, $x, $y, $black, $font, $font_text );
				imagettftext( $image, $font_size, 0, $x, $y, $icolor, $font, $font_text );
			
			//die();
			//image watermark
			}elseif( $opts['watermark'] == 2 && $opts["watermarkurl"] ){
				
				$wmpath = $opts["watermarkurl"];
				$wmtype = wp_check_filetype( basename( $opts["watermarkurl"] ) );

				if( !preg_match( '/(png|jpg|jpeg|gif )$/i', $wmtype['ext'] ) )
					die( );
				
				if( file_get_contents( $wmpath ) ){
					switch( $wmtype['ext'] ){
						case "jpg":
						case "jpeg":
							$watermark = @imagecreatefromjpeg( $wmpath );
							break;
						case "gif":
							$watermark = @imagecreatefromgif( $wmpath );
							break;
						case "png":
							$watermark = @imagecreatefrompng( $wmpath );
						 break;
					}
					
					$wminfo 	= getimagesize( $wmpath );
					$info			= getimagesize( $this->image_dir );
					$wmratio 	= $this->image_ratio( $wminfo[0], $wminfo[1], max( $info[0], $info[1] ) );
					
					switch( $local ){
						case 1:
							$x = $y = 2;
							break;
						case 2:
							$x = ( $info[0] - $wmratio['w'] )/2; 
							$y = 2;
							break;
						case 3:
							$x = ( $info[0] - $wmratio['w'] ) - 4; 
							$y = 2;
							break;
						case 4:
							$x = 2; 
							$y = ( $info[1] - $wmratio['h'] )/2;
							break;
						case 6:
							$x = ( $info[0] - $wmratio['w'] ) - 4; 
							$y = ( $info[1] - $wmratio['h'] )/2;
							break;
						case 7:
							$x = 2;
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						case 8:
							$x = ( $info[0] - $wmratio['w'] )/2; 
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						case 9:
							$x = ( $info[0] - $wmratio['w'] ) - 4; 
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						default:
							$x = ( $info[0] - $wmratio['w'] )/2; 
							$y = ( $info[1] - $wmratio['h'] )/1.7;
					}
	
					$wmnew = imagecreatetruecolor( $wmratio['w'], $wmratio['h'] );
					
					//keep transperancy
					if( $wmtype['ext'] == "png" ){
						$background = imagecolorallocate( $wmnew, 0, 0, 0 );
						ImageColorTransparent( $wmnew, $background );
						imagealphablending( $wmnew, true );
					}
					
					//resize watermarl and merge images
					imagecopyresampled( $wmnew, $watermark, 0, 0, 0, 0, $wmratio['w'], $wmratio['h'], $wminfo[0], $wminfo[1] );
					imagecopymerge( $image, $wmnew, $x, $y, 0, 0, $wmratio['w'], $wmratio['h'], 30 );
					
					@imagedestroy( $wmnew );
					@imagedestroy( $watermark );
				}
			}
		}
		
		//gray scale
		if( $color == 'g' ){
			imagefilter( $image, IMG_FILTER_GRAYSCALE );
			imagefilter( $image, IMG_FILTER_BRIGHTNESS, +10 );
		}
		
		//sepia
		if( $color == 's' ){
			imagefilter( $image, IMG_FILTER_GRAYSCALE ); 
			imagefilter( $image, IMG_FILTER_BRIGHTNESS, -10 );
			imagefilter( $image, IMG_FILTER_COLORIZE, 35, 25, 10 );
		}
		
		do_action( 'ims_apply_color_filter', &$image );
		
		$quality = ( $q = get_option( 'preview_size_q' ) ) ? $q : 85;
		
		//create new image
		switch( $filetype['ext'] ){
			case "jpg":
			case "jpeg":
				imagejpeg( $image, NULL, $quality );
				break;
			case "gif":
				imagegif( $image );
				break;
			case "png":
				$quality = ( ceil( $quality/10 )>9 ) ? 9 : ceil( $quality/10 );
				imagepng( $image, NULL, $quality );
				break;
		}
		@imagedestroy( $image );
		die( );
		
	}
	
	/**
	*Conver hex color to rgb
	*
	*@param string $hex
	*@return unit/string
	*@since 0.5.0 
	*/
	function HexToRGB( $hex ){
		$hex = ereg_replace( "#", "", $hex );
		$color = array( );
 
		if( strlen( $hex ) == 3 ){
			$color['r'] = hexdec( substr( $hex, 0, 1 ));
			$color['g'] = hexdec( substr( $hex, 1, 1 ));
			$color['b'] = hexdec( substr( $hex, 2, 1 ));
		}
		else if( strlen( $hex ) == 6 ){
			$color['r'] = hexdec( substr( $hex, 0, 2 ) );
			$color['g'] = hexdec( substr( $hex, 2, 2 ) );
			$color['b'] = hexdec( substr( $hex, 4, 2 ) );
		}
		return $color;
	}
	
	/**
	*Get image ratio
	*
	*@param unit $w
	*@param unit $h
	*@param unit $immax
	*@return unit
	*@since 0.5.0 
	*/
	function image_ratio( $w, $h, $immax ){
                $i=array();
		$max		= max( $w, $h );
		$r		= $max > $immax ? ( $immax / $max ) : 1;
		$i['w']	= ceil( $w *$r * .8 );
		$i['h']	= ceil( $h * $r * .8 );
		return $i;
	}
	
	/**
	 *Encrypt url
	 *
	 *@parm string $string 
	 *@return string
	 *@since 2.1.1
	 */	
	function url_decrypt( $string ){ ;
		$str = '';
		$string = base64_decode( implode('/', explode( '||', $string )));
		for($i=0; $i<strlen($string); $i++) { 
			$char = substr($string, $i, 1); 
			$keychar = substr($this->key, ($i % strlen($this->key))-1, 1); 
			$char = chr(ord($char)-ord($keychar)); 
			$str.=$char; 
		}
		return $str; 
	}
}

//do that thing you do 
new ImStoreImage( );
