<?php

/**
 * Image store - secure image
 *
 * @package Image Store
 * @author Hafid Trujillo
 * @copyright 20010-2012
 * @since 0.5.0 
 */

//define constants
define('SHORTINIT', true);
define('DOING_AJAX', true);

//load wp
require_once '../../../wp-load.php';

class ImStoreImage {

	private $id = false;
	private $key = false;
	private $watermark = false;

	/**
	 * Constructor
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function __construct() {

		if (empty($_GET['i']))
			die();

		$this->key = apply_filters('ims_image_key', substr(preg_replace("([^a-zA-Z0-9])", '', NONCE_KEY), 0, 15));
		$this->url = explode(':', $this->url_decrypt($_GET['i']), 3);

		if (empty($this->url[0]) || empty($this->url[1]) || !is_numeric($this->url[0]))
			die();

		$this->data = wp_cache_get('ims_meta_image_' . $this->url[0]);

		if (false == $this->data) {
			global $wpdb;
			$this->data = $wpdb->get_row(
				$wpdb->prepare(
				"SELECT meta_value  meta FROM $wpdb->postmeta 
				WHERE meta_key = '_wp_attachment_metadata' 
				AND $wpdb->postmeta.post_id = %d LIMIT 1", $this->url[0]
			));
		}

		if (empty($this->data->meta))
			die();
			
		$this->metadata = maybe_unserialize($this->data->meta);
		$image_path = rtrim(WP_CONTENT_DIR,'/') . '/';

		$this->metadata = maybe_unserialize($this->data->meta);
		$this->path = $image_path . trim(dirname($this->metadata['file']), '/');

		if (!preg_match('/_resized/i', $this->path))
			$this->path .= "/_resized";

		switch ($this->url[1]) {
			case 1: //preview
				$this->image_dir = $this->path . '/' . $this->metadata['sizes']['preview']['file'];
				break;
			case 2: //thumbnail
				$this->image_dir = $this->path . '/' . $this->metadata['sizes']['thumbnail']['file'];
				break;
			case 3: //mini
				$this->image_dir = $this->path . '/' . $this->metadata['sizes']['mini']['file'];
				break;
			case 4: //original
				$this->image_dir = rtrim(WP_CONTENT_DIR, '/') . "/" . $this->metadata['file'];
				break;
		}
		
		$this->display_image();
	}

	/**
	 * Display image
	 *
	 * @return void
	 * @since 0.5.0 
	 */
	function display_image() {

		if (isset($this->url[2]))
			$this->watermark = 1;

		$ext = substr(strrchr($this->image_dir, '.'),1);
		header('Content-Type: image/' . $ext);

		if (isset($_SERVER['MOD_X_SENDFILE_ENABLED']))
			header('X-Sendfile: ' . $this->image_dir);

		$color = isset($_REQUEST['c']) ? $_REQUEST['c'] : false;
		$cache = get_option('ims_cache_time', substr(@filemtime(dirname(__FILE__)), -4));
		$modified = gmdate("D, d M Y H:i:s", ( @filemtime($this->image_dir) + $cache));

		$etag = '"' . md5($this->url[0] . $this->url[1] . $color . $this->watermark . $modified) . '"';
		$client_etag = isset($_SERVER['HTTP_IF_NONE_MATCH']) ? stripslashes($_SERVER['HTTP_IF_NONE_MATCH']) : false;

		header('Last-Modified:' . gmdate('D,d M Y H:i:s') . ' GMT');
		header('Cache-control:no-cache,no-store,must-revalidate,max-age=0');

		header('ETag: ' . $etag);
		header("Last-Modified: $modified GMT");
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 100000000) . ' GMT');
		header('Cache-Control:max-age=' . ( time() + 100000000 ) . ', must-revalidate');

		if (( isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && isset($_SERVER['HTTP_IF_NONE_MATCH'])
		&& ( strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == ( @filemtime($this->image_dir) + $cache ) ) ) 
		|| ( $client_etag == $etag )) {
			header('HTTP/1.1 304 Not Modified');
			die();
		}

		if (empty($color) && !$this->watermark) {
			@readfile($this->image_dir);
			die();
		}

		if (!function_exists('get_site_option')) {
			header("HTTP/1.0 404 Not Found");
			die();
		}
		
		//memory limit
		set_time_limit(0);
		ini_set( 'allow_url_fopen', true );
		ini_set('memory_limit', $this->get_memory_limit());

		if (get_site_option('ims_sync_settings'))
			switch_to_blog(1);

		if (is_multisite())
			restore_current_blog();

		$filetype = wp_check_filetype(basename($this->image_dir));
		$filetype['ext'] = strtolower($filetype['ext']);

		switch ($filetype['ext']) {
			case "jpg":
			case "jpeg":
				$image = imagecreatefromjpeg($this->image_dir);
				break;
			case "gif":
				$image = imagecreatefromgif($this->image_dir);
				break;
			case "png":
				$image = imagecreatefrompng($this->image_dir);
				break;
			default:
				die();
		}

		$opts = get_option('ims_front_options');

		//add water mark		
		if ($opts['watermark']) {
			$local = get_option('ims_wlocal');

			//text watermark
			if ($opts['watermark'] == 1) {

				//backwards compatiblity
				$textcolor = isset($opts['watermark_color']) ? $opts['watermark_color'] : $opts['textcolor'];
				$font_size = isset($opts['watermark_size']) ? $opts['watermark_size'] : $opts['fontsize'];
				$font_text = isset($opts['watermark_text']) ? $opts['watermark_text'] : $opts['watermarktext'];
				$trannsperency = isset($opts['watermark_trans']) ? $opts['watermark_trans'] : $opts['transperency'];

				$rgb = $this->HexToRGB($textcolor);
				$font = dirname(__FILE__) . '/_fonts/arial.ttf';
				$black = imagecolorallocatealpha($image, 0, 0, 0, 90);
				$icolor = imagecolorallocatealpha($image, $rgb['r'], $rgb['g'], $rgb['b'], $trannsperency);

				$info = getimagesize($this->image_dir);
				$tb = imagettfbbox($font_size, 0, $font, $font_text);

				switch ($local) {
					case 1:
						$x = 2;
						$y = abs($tb[5]) + 2;
						break;
					case 2:
						$x = ceil(( $info[0] - $tb[2] ) / 2);
						$y = abs($tb[5]) + 2;
						break;
					case 3:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = abs($tb[5]) + 2;
						break;
					case 4:
						$x = 2;
						$y = $info[1] / 2;
						break;
					case 5:
						$x = ceil(( $info[0] - $tb[2] ) / 2);
						$y = $info[1] / 1.7;
						break;
					case 6:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = $info[1] / 2;
						break;
					case 7:
						$x = 2;
						$y = $info[1] / 1.03;
						break;
					case 9:
						$x = ($info[0] - $tb[2] ) - 4;
						$y = $info[1] / 1.03;
						break;
					default:
						$x = ceil(( $info[0] - $tb[2] ) / 2);
						$y = $info[1] / 1.03;
				}

				imagettftext($image, $font_size, 0, $x, $y, $black, $font, $font_text);
				imagettftext($image, $font_size, 0, $x, $y, $icolor, $font, $font_text);

				//image watermark
			} elseif ($opts['watermark'] == 2 && $opts["watermarkurl"]) {

				$wmpath = $opts["watermarkurl"];
				$wmtype = wp_check_filetype(basename($wmpath));

				if (!preg_match('/(png|jpg|jpeg|gif)$/i', $wmtype['ext']))
					die();

				if (@file_get_contents($wmpath)) {
					switch ($wmtype['ext']) {
						case "jpg":
						case "jpeg":
							$watermark = @imagecreatefromjpeg($wmpath);
							break;
						case "gif":
							$watermark = @imagecreatefromgif($wmpath);
							break;
						case "png":
							$watermark = @imagecreatefrompng($wmpath);
							break;
					}

					$wminfo = getimagesize($wmpath);
					$info = getimagesize($this->image_dir);
					$wmratio = $this->image_ratio($wminfo[0], $wminfo[1], max($info[0], $info[1]));

					switch ($local) {
						case 1:
							$x = $y = 2;
							break;
						case 2:
							$x = ( $info[0] - $wmratio['w'] ) / 2;
							$y = 2;
							break;
						case 3:
							$x = ( $info[0] - $wmratio['w'] ) - 4;
							$y = 2;
							break;
						case 4:
							$x = 2;
							$y = ( $info[1] - $wmratio['h'] ) / 2;
							break;
						case 6:
							$x = ( $info[0] - $wmratio['w'] ) - 4;
							$y = ( $info[1] - $wmratio['h'] ) / 2;
							break;
						case 7:
							$x = 2;
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						case 8:
							$x = ( $info[0] - $wmratio['w'] ) / 2;
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						case 9:
							$x = ( $info[0] - $wmratio['w'] ) - 4;
							$y = ( $info[1] - $wmratio['h'] ) - 4;
							break;
						default:
							$x = ( $info[0] - $wmratio['w'] ) / 2;
							$y = ( $info[1] - $wmratio['h'] ) / 1.7;
					}

					$wmnew = imagecreatetruecolor($wmratio['w'], $wmratio['h']);

					//keep transperancy
					if ($wmtype['ext'] == "png") {
						$background = imagecolorallocate($wmnew, 0, 0, 0);
						ImageColorTransparent($wmnew, $background);
						imagealphablending($wmnew, true);
					}

					//resize watermarl and merge images
					imagecopyresampled($wmnew, $watermark, 0, 0, 0, 0, $wmratio['w'], $wmratio['h'], $wminfo[0], $wminfo[1]);
					imagecopymerge($image, $wmnew, $x, $y, 0, 0, $wmratio['w'], $wmratio['h'], 30);

					@imagedestroy($wmnew);
					@imagedestroy($watermark);
				}
			}
		}

		//apply filter
		$filters = get_option('ims_color_filters');
		if ($color && isset($filters[$color])) {
			if ($filters[$color]['grayscale'])
				imagefilter($image, IMG_FILTER_GRAYSCALE);

			if ($filters[$color]['contrast'])
				imagefilter($image, IMG_FILTER_CONTRAST, $filters[$color]['contrast']);

			if ($filters[$color]['brightness'])
				imagefilter($image, IMG_FILTER_BRIGHTNESS, $filters[$color]['brightness']);

			if ($filters[$color]['brightness'])
				imagefilter($image, IMG_FILTER_BRIGHTNESS, $filters[$color]['brightness']);

			if ($filters[$color]['colorize']) {
				$args = array($image, IMG_FILTER_COLORIZE);
				$args = array_merge($args, explode(',', $filters[$color]['colorize']));
				call_user_func_array('imagefilter', $args);
			}
		}

		$quality = get_option('preview_size_q', 85);

		//create new image
		switch ($filetype['ext']) {
			case "jpg":
			case "jpeg":
				imagejpeg($image, NULL, $quality);
				break;
			case "gif":
				imagegif($image);
				break;
			case "png":
				$quality = ( ceil($quality / 10) > 9 ) ? 9 : ceil($quality / 10);
				imagepng($image, NULL, $quality);
				break;
			default:
				die();
		}

		@imagedestroy($image);
		die();
	}

	/**
	 * Get memory limit
	 *
	 * @return string
	 * @since 3.1.0
	 */
	function get_memory_limit(){
		if(!defined('WP_MAX_MEMORY_LIMIT') )
			return '256M';
		elseif(WP_MAX_MEMORY_LIMIT == false || WP_MAX_MEMORY_LIMIT == '')
			return '256M';
		else return WP_MAX_MEMORY_LIMIT;
	}
	
	/**
	 * Conver hex color to rgb
	 *
	 * @param string $hex
	 * @return unit/string
	 * @since 0.5.0 
	 */
	function HexToRGB($hex) {
		$hex = ereg_replace("#", "", $hex);
		$color = array();

		if (strlen($hex) == 3) {
			$color['r'] = hexdec(str_repeat(substr($hex, 0, 1), 2));
			$color['g'] = hexdec(str_repeat(substr($hex, 1, 1), 2));
			$color['b'] = hexdec(str_repeat(substr($hex, 2, 1), 2));
		} else if (strlen($hex) == 6) {
			$color['r'] = hexdec(substr($hex, 0, 2));
			$color['g'] = hexdec(substr($hex, 2, 2));
			$color['b'] = hexdec(substr($hex, 4, 2));
		}
		return $color;
	}

	/**
	 * Get image ratio
	 *
	 * @param unit $w
	 * @param unit $h
	 * @param unit $immax
	 * @return unit
	 * @since 0.5.0 
	 */
	function image_ratio($w, $h, $immax) {
		$i = array();
		$max = max($w, $h);
		$r = $max > $immax ? ( $immax / $max ) : 1;
		$i['w'] = ceil($w * $r * .8);
		$i['h'] = ceil($h * $r * .8);
		return $i;
	}

	/**
	 * Encrypt url
	 *
	 * @parm string $string 
	 * @return string
	 * @since 2.1.1
	 */
	function url_decrypt($string) {
		$str = '';
		$string = base64_decode(implode('/', explode('::', $string)));
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
			$char = chr(ord($char) - ord($keychar));
			$str.=$char;
		}
		return $str;
	}

}

//do that thing you do 
new ImStoreImage( );
